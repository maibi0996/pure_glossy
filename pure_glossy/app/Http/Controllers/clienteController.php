<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function mostrar()
    {
        $clientes = DB::table('clientes_corrientes as c')
            ->leftJoin('movimientos_cuenta_corriente as m', 'c.id_cliente', '=', 'm.id_cliente')
            ->select(
                'c.id_cliente',
                'c.nombre_y_apellido',
                'c.DNI',
                'c.telefono',
                'c.direccion',
                'c.localidad',
                'c.email',
                'c.estado',
                DB::raw("COALESCE(SUM(
                    CASE 
                        WHEN m.tipo = 'venta' THEN m.monto
                        WHEN m.tipo = 'pago' THEN -m.monto
                        WHEN m.tipo = 'ajuste' AND m.concepto LIKE '[Cargo]%' THEN m.monto
                        WHEN m.tipo = 'ajuste' THEN -m.monto
                        ELSE 0
                    END
                ), 0) as saldo_actual")
            )
            ->groupBy(
                'c.id_cliente',
                'c.nombre_y_apellido',
                'c.DNI',
                'c.telefono',
                'c.direccion',
                'c.localidad',
                'c.email',
                'c.estado'
            )
            ->orderBy('c.nombre_y_apellido', 'asc')
            ->get();

        return view('clientes', ['clientes' => $clientes]);
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'nombre_y_apellido' => 'required|string|max:255',
            'dni'               => 'required|digits_between:5,12',
            'telefono'          => 'required|digits_between:8,15',
            'direccion'         => 'nullable|string|max:255',
            'localidad'         => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
        ]);

        DB::table('clientes_corrientes')->insert([
            'nombre_y_apellido' => $request->input('nombre_y_apellido'),
            'DNI'               => $request->input('dni'),
            'telefono'          => $request->input('telefono'),
            'direccion'         => $request->input('direccion'),
            'localidad'         => $request->input('localidad'),
            'email'             => $request->input('email'),
            'estado'            => 'activo',
        ]);

        return redirect()->route('views.clientes')->with('success', 'Cliente agregado correctamente.');
    }

    public function modificar(Request $request)
    {
        $request->validate([
            'modal_id_cliente'  => 'required|exists:clientes_corrientes,id_cliente|int',
            'nombre_y_apellido' => 'required|string|max:255',
            'dni'               => 'required|digits_between:5,12',
            'telefono'          => 'required|digits_between:8,15',
            'direccion'         => 'nullable|string|max:255',
            'localidad'         => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
            'estado'            => 'required|in:activo,desactivado',
        ]);

        DB::table('clientes_corrientes')
            ->where('id_cliente', $request->input('modal_id_cliente'))
            ->update([
                'nombre_y_apellido' => $request->input('nombre_y_apellido'),
                'DNI'               => $request->input('dni'),
                'telefono'          => $request->input('telefono'),
                'direccion'         => $request->input('direccion'),
                'localidad'         => $request->input('localidad'),
                'email'             => $request->input('email'),
                'estado'            => $request->input('estado'),
            ]);

        return redirect()->route('views.clientes')->with('success', 'Cliente modificado correctamente.');
    }

    public function borrar($id_cliente)
    {
        DB::table('clientes_corrientes')
            ->where('id_cliente', $id_cliente)
            ->update(['estado' => 'desactivado']);

        return redirect()->route('views.clientes')->with('success', 'Cliente desactivado correctamente.');
    }

    public function movimientos($id_cliente)
    {
        $cliente = DB::table('clientes_corrientes')
            ->where('id_cliente', $id_cliente)
            ->first();

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $movimientos = DB::table('movimientos_cuenta_corriente')
            ->where('id_cliente', $id_cliente)
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento')
            ->get();

        $saldo = 0;
        foreach ($movimientos as $mov) {
            if ($mov->tipo === 'venta') {
                $saldo += (float) $mov->monto;
            } elseif ($mov->tipo === 'pago') {
                $saldo -= (float) $mov->monto;
            } elseif ($mov->tipo === 'ajuste') {
                if (str_starts_with((string) $mov->concepto, '[Cargo]')) {
                    $saldo += (float) $mov->monto;
                } else {
                    $saldo -= (float) $mov->monto;
                }
            }
        }

        return response()->json([
            'cliente'     => $cliente,
            'saldo'       => round($saldo, 2),
            'estado_cta'  => $saldo > 0 ? 'Con saldo pendiente' : 'Al día',
            'movimientos' => $movimientos,
        ]);
    }

    public function registrarMovimiento(Request $request, $id_cliente)
    {
        $request->validate([
            'tipo'             => 'required|in:pago,ajuste',
            'monto'            => 'required|numeric|min:0.01',
            'sentido_ajuste'   => 'nullable|in:cargo,descuento',
            'concepto'         => 'nullable|string|max:240',
        ]);

        $cliente = DB::table('clientes_corrientes')->where('id_cliente', $id_cliente)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        $tipo = $request->input('tipo');
        $conceptoTexto = trim((string) $request->input('concepto', ''));

        if ($tipo === 'pago') {
            $conceptoFinal = $conceptoTexto !== '' ? $conceptoTexto : 'Pago en cuenta corriente';
        } else {
            $prefijo = ($request->input('sentido_ajuste') === 'cargo') ? '[Cargo]' : '[Descuento]';
            $conceptoFinal = $conceptoTexto !== '' ? "{$prefijo} {$conceptoTexto}" : "{$prefijo} Ajuste de saldo";
        }

        DB::table('movimientos_cuenta_corriente')->insert([
            'id_cliente' => $id_cliente,
            'id_venta'   => null,
            'tipo'       => $tipo,
            'concepto'   => $conceptoFinal,
            'monto'      => abs((float) $request->input('monto')),
            'fecha'      => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => $tipo === 'pago' ? 'Pago registrado correctamente.' : 'Ajuste registrado correctamente.',
        ]);
    }
}