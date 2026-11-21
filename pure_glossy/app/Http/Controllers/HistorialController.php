<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    public function mostrar(Request $request)
    {
        $query = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->select(
                'ventas.id_venta',
                'users.id as vendedor_id',
                'users.name as vendedor_name',
                'ventas.monto_total',
                'ventas.descuento',
                'metodos_pago.nombre as metodo_pago',
                'ventas.fecha_venta',
                'clientes_corrientes.id_cliente as clientec',
                'clientes_corrientes.nombre_y_apellido as nombre_clientec',
                'clientes_corrientes.telefono as telefono_clientec', 
                'ventas.cliente_nombre',
                'ventas.cliente_telefono',
                'ventas.observaciones'
            )
            ->whereNull('ventas_anuladas.id_venta');
    
        // 1. Filtro por Vendedor
        if ($request->filled('vendedor')) {
            $query->where('users.id', $request->input('vendedor'));
        }

        // 2. Filtro por Cliente
        if ($request->filled('id_cliente')) {
            $query->where('ventas.id_cliente', $request->input('id_cliente'));
        }
    
        // 3. Filtro de Fechas
        if ($request->filled('fechainicio') && $request->filled('fechafin')) {
            $query->whereBetween('ventas.fecha_venta', [
                $request->input('fechainicio') . ' 00:00:00', 
                $request->input('fechafin') . ' 23:59:59'
            ]);
        } elseif ($request->filled('fechainicio')) {
            $query->where('ventas.fecha_venta', '>=', $request->input('fechainicio') . ' 00:00:00');
        } elseif ($request->filled('fechafin')) {
            $query->where('ventas.fecha_venta', '<=', $request->input('fechafin') . ' 23:59:59');
        }
    
        $ventas = $query->orderBy('ventas.fecha_venta', 'desc')->get();

        // Adjuntar desglose de pagos a cada venta
        $idsVentas = $ventas->pluck('id_venta')->toArray();
        $pagosPorVenta = collect();

        if (!empty($idsVentas)) {
            $pagosPorVenta = DB::table('ventas_pagos as vp')
                ->join('metodos_pago as mp', 'vp.id_metodo_pago', '=', 'mp.id_metodo_pago')
                ->whereIn('vp.id_venta', $idsVentas)
                ->select('vp.id_venta', 'mp.nombre as metodo', 'vp.monto')
                ->orderBy('vp.id_venta_pago', 'asc')
                ->get()
                ->groupBy('id_venta');
        }

        foreach ($ventas as $venta) {
            $pagosVenta = $pagosPorVenta->get($venta->id_venta);
            if ($pagosVenta && $pagosVenta->isNotEmpty()) {
                $venta->pagos = $pagosVenta;
            } else {
                // Fallback para ventas anteriores a ventas_pagos
                $venta->pagos = collect([
                    (object) [
                        'id_venta' => $venta->id_venta,
                        'metodo'   => $venta->metodo_pago,
                        'monto'    => $venta->monto_total,
                    ]
                ]);
            }
        }

        $vendedores = DB::table('users')->select('id', 'name')->where('estado', 'activo')->get();
        $clientes = DB::table('clientes_corrientes')->where('estado', 'activo')->get(); 
        $totalVentas = $ventas->sum('monto_total');

        return view('historial', [
            'ventas'      => $ventas,
            'vendedores'  => $vendedores,
            'clientes'    => $clientes, 
            'totalVentas' => $totalVentas, 
        ]);
    }

    public function obtenerDetalleVenta($idVenta)
    {
        $productos = DB::table('ventas_productos')
            ->join('productos', 'ventas_productos.id_producto', '=', 'productos.id_producto')
            ->leftJoin('marcas', 'productos.id_marca', '=', 'marcas.id_marca')
            ->leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->select(
                'productos.nombre as producto',
                'marcas.nombre as marca',
                'categorias.categoria as categoria',
                'ventas_productos.cantidad',
                'ventas_productos.precio',
                'ventas_productos.precio_lista'
            )
            ->where('ventas_productos.id_venta', $idVenta)
            ->get();

        foreach ($productos as $p) {
            if (isset($p->precio_lista) && $p->precio < $p->precio_lista) {
                $p->producto .= ' - Promo';
            }
        }

        $pagos = DB::table('ventas_pagos as vp')
            ->join('metodos_pago as mp', 'vp.id_metodo_pago', '=', 'mp.id_metodo_pago')
            ->where('vp.id_venta', $idVenta)
            ->select('mp.nombre as metodo', 'vp.monto')
            ->orderBy('vp.id_venta_pago', 'asc')
            ->get();

        if ($pagos->isEmpty()) {
            $ventaBase = DB::table('ventas')
                ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
                ->where('ventas.id_venta', $idVenta)
                ->select('metodos_pago.nombre as metodo', 'ventas.monto_total as monto')
                ->first();

            if ($ventaBase) {
                $pagos = collect([$ventaBase]);
            }
        }

        return response()->json([
            'productos' => $productos,
            'pagos'     => $pagos,
        ]);
    }

    public function anularVenta(Request $request)
{
    $validated = $request->validate([
        'id_venta'            => 'required|exists:ventas,id_venta',
        'id_usuario_anulador' => 'required|exists:users,id',
        'descripcion'         => 'required|string|max:255',
    ]);

    $idVenta = $validated['id_venta'];

    DB::beginTransaction();

    try {
        $yaAnulada = DB::table('ventas_anuladas')
            ->where('id_venta', $idVenta)
            ->exists();

        if ($yaAnulada) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Esta venta ya fue anulada anteriormente.');
        }

        // 1. Devolver stock de productos vendidos
        $productos = DB::table('ventas_productos')
            ->select('id_producto', 'cantidad')
            ->where('id_venta', $idVenta)
            ->get();

        foreach ($productos as $producto) {
            DB::table('productos')
                ->where('id_producto', $producto->id_producto)
                ->increment('stock', $producto->cantidad);
        }

        // 2. Compensar movimiento en cuenta corriente si existía cargo por esta venta
        $movVenta = DB::table('movimientos_cuenta_corriente')
            ->where('id_venta', $idVenta)
            ->where('tipo', 'venta')
            ->first();

        if ($movVenta) {
            DB::table('movimientos_cuenta_corriente')->insert([
                'id_cliente' => $movVenta->id_cliente,
                'id_venta'   => $idVenta,
                'tipo'       => 'ajuste',
                'concepto'   => "[Descuento] Anulación de venta #{$idVenta}: {$validated['descripcion']}",
                'monto'      => abs((float) $movVenta->monto),
                'fecha'      => now()->format('Y-m-d H:i:s'),
            ]);
        }

        // 3. Registrar anulación
        DB::table('ventas_anuladas')->insert([
            'id_venta'            => $idVenta,
            'id_usuario_anulador' => $validated['id_usuario_anulador'],
            'descripcion'         => $validated['descripcion'],
        ]);

        DB::commit();

        return redirect()->back()->with('success', 'Venta anulada correctamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No se pudo anular la venta: ' . $e->getMessage());
    }
}

    public function generarTicket($idVenta)
    {
        $venta = DB::table('ventas')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->select(
                'ventas.id_venta',
                'users.name as vendedor_name',
                'ventas.monto_total',
                'ventas.descuento',
                'metodos_pago.nombre as metodo_pago',
                'ventas.fecha_venta',
                'clientes_corrientes.id_cliente as clientec',
                'clientes_corrientes.nombre_y_apellido as nombre_clientec',
                'clientes_corrientes.telefono as telefono_clientec', 
                'ventas.cliente_nombre',
                'ventas.cliente_telefono'
            )
            ->where('ventas.id_venta', $idVenta)
            ->first();

        $pagos = DB::table('ventas_pagos as vp')
            ->join('metodos_pago as mp', 'vp.id_metodo_pago', '=', 'mp.id_metodo_pago')
            ->where('vp.id_venta', $idVenta)
            ->select('mp.nombre as metodo', 'vp.monto')
            ->orderBy('vp.id_venta_pago', 'asc')
            ->get();

        if ($pagos->isEmpty() && $venta) {
            $pagos = collect([
                (object) [
                    'metodo' => $venta->metodo_pago,
                    'monto'  => $venta->monto_total,
                ]
            ]);
        }

        $productos = DB::table('ventas_productos')
            ->join('productos', 'ventas_productos.id_producto', '=', 'productos.id_producto')
            ->leftJoin('marcas', 'productos.id_marca', '=', 'marcas.id_marca')
            ->leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->select(
                'productos.nombre as producto',
                'productos.codigo',
                'productos.codigo_barra',
                'marcas.nombre as nombre_marca',
                'categorias.categoria as nombre_categoria',
                'ventas_productos.cantidad',
                'ventas_productos.precio',
                'ventas_productos.precio_lista'
            )
            ->where('ventas_productos.id_venta', $idVenta)
            ->get();

        $local = [
            'nombre'    => 'Pure Glossy',
            'telefono'  => '+54 9 3704 09-0295',
            'instagram' => '@pure.glossy',
        ];

        return view('ticket', compact('venta', 'productos', 'pagos', 'local'));
    }
}