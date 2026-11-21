<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function mostrar()
    {
        $metodosDePago = DB::table('metodos_pago')->select('id_metodo_pago', 'nombre')->get();

        $promociones = DB::table('promociones')
            ->where('estado', 'activo')
            ->get();

        foreach ($promociones as $promo) {
            $promo->productos = DB::table('promocion_productos as pp')
                ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                ->where('pp.id_promocion', $promo->id_promocion)
                ->select('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.precio_venta', 'p.stock', 'pp.cantidad')
                ->get();
        }

        return view('welcome', ['metodosDePago' => $metodosDePago, 'promociones' => $promociones]);
    }

    public function obtenerClientesCorrientes()
    {
        $clientesCorrientes = DB::table('clientes_corrientes')
            ->select('id_cliente', 'nombre_y_apellido', 'dni')
            ->where('estado', 'activo')
            ->get();
        
        return response()->json($clientesCorrientes);
    }

    public function buscarProductos(Request $request)
    {
        $queryStr = trim($request->get('q', ''));

        if (empty($queryStr)) {
            return response()->json([]);
        }

        $consultaBase = DB::table('productos as p')
            ->leftJoin('marcas as m', 'p.id_marca', '=', 'm.id_marca')
            ->leftJoin('categorias as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->where('p.estado', 'activo')
            ->select(
                'p.id_producto',
                'p.nombre',
                'p.codigo',
                'p.codigo_barra',
                'p.precio_venta',
                'p.precio_lista',
                'p.stock',
                'm.nombre as marca',
                'c.categoria as categoria'
            );

        // 1. PRIORIDAD EXACTA (Lector de barra o código idéntico)
        $productoExacto = (clone $consultaBase)
            ->where(function ($q) use ($queryStr) {
                $q->where('p.codigo_barra', $queryStr)
                  ->orWhere('p.codigo', $queryStr);
            })
            ->get();

        if ($productoExacto->isNotEmpty()) {
            return response()->json($productoExacto);
        }

        // 2. BÚSQUEDA GENERAL: Nombre, Marca, Categoría o Códigos que empiezan con el texto
        $palabras = array_filter(explode(' ', $queryStr));

        $consulta = clone $consultaBase;
        $consulta->where(function ($q) use ($palabras) {
            foreach ($palabras as $palabra) {
                $q->where(function ($subQ) use ($palabra) {
                    $subQ->where('p.nombre', 'LIKE', '%' . $palabra . '%')
                         ->orWhere('m.nombre', 'LIKE', '%' . $palabra . '%')
                         ->orWhere('c.categoria', 'LIKE', '%' . $palabra . '%')
                         ->orWhere('p.codigo', 'LIKE', $palabra . '%')
                         ->orWhere('p.codigo_barra', 'LIKE', $palabra . '%');
                });
            }
        });

        $productos = $consulta->take(30)->get();

        return response()->json($productos);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'pagos' => 'required|array|min:1',
            'pagos.*.id_metodo_pago' => 'required|exists:metodos_pago,id_metodo_pago',
            'pagos.*.monto' => 'required|numeric|min:0.01',
            'descuento' => 'nullable|numeric|min:0',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'promociones' => 'nullable|array',
            'promociones.*.id_promocion' => 'required|exists:promociones,id_promocion',
            'promociones.*.cantidad' => 'required|integer|min:1',
            'promociones.*.precio' => 'required|numeric|min:0',
            'id_cliente' => 'nullable|exists:clientes_corrientes,id_cliente',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_telefono' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // Validar que se haya enviado al menos un producto o una promoción
        if (empty($request->productos) && empty($request->promociones)) {
            return redirect()->back()->with('error', 'Debes agregar al menos un producto o una promoción a la venta.');
        }

        // Calcular monto total antes de descuento
        $montoTotal = 0;
        if (!empty($request->productos)) {
            foreach ($request->productos as $p) {
                $montoTotal += ((float) $p['precio'] * (int) $p['cantidad']);
            }
        }
        if (!empty($request->promociones)) {
            foreach ($request->promociones as $pr) {
                $montoTotal += ((float) $pr['precio'] * (int) $pr['cantidad']);
            }
        }

        $descuento = (float) ($request->descuento ?? 0);
        if ($descuento > $montoTotal) {
            return redirect()->back()->with('error', 'El descuento no puede superar el monto total de la venta.');
        }

        $montoTotal = round($montoTotal - $descuento, 2);

        // Validar pagos y cuenta corriente
        $pagos = $request->input('pagos', []);
        $sumaPagos = 0;
        $montoCuentaCorriente = 0;
        $metodoPrincipal = null;
        $mayorMontoPago = -1;

        foreach ($pagos as $pago) {
            $idMetodo = (int) $pago['id_metodo_pago'];
            $montoPago = round((float) $pago['monto'], 2);

            $sumaPagos += $montoPago;

            if ($idMetodo === 3) {
                $montoCuentaCorriente += $montoPago;
            }

            if ($montoPago > $mayorMontoPago) {
                $mayorMontoPago = $montoPago;
                $metodoPrincipal = $idMetodo;
            }
        }

        $sumaPagos = round($sumaPagos, 2);
        $montoCuentaCorriente = round($montoCuentaCorriente, 2);

        if (abs($sumaPagos - $montoTotal) > 0.01) {
            return redirect()->back()->with(
                'error',
                'La suma de los pagos ($' . number_format($sumaPagos, 2) . ') no coincide con el total de la venta ($' . number_format($montoTotal, 2) . ').'
            );
        }

        if ($montoCuentaCorriente > 0) {
            $esCombinado = count($pagos) > 1 || ($montoCuentaCorriente < $montoTotal);
            $conceptoCta = $esCombinado
                ? "Venta #{$idVenta} (pago parcial/combinado a cta. cte.)"
                : "Venta #{$idVenta}";

            DB::table('movimientos_cuenta_corriente')->insert([
                'id_cliente' => $idClienteSeleccionado,
                'id_venta'   => $idVenta,
                'tipo'       => 'venta',
                'concepto'   => $conceptoCta,
                'monto'      => $montoCuentaCorriente,
                'fecha'      => now()->format('Y-m-d H:i:s'),
            ]);
        }

        DB::beginTransaction();

        try {
            $promosDesglosadas = [];
            if (!empty($request->promociones)) {
                foreach ($request->promociones as $promoItem) {
                    $idPromo = $promoItem['id_promocion'];
                    $cantCombos = $promoItem['cantidad'];
                    $precioComboIngresado = $promoItem['precio'];

                    $itemsPromoDB = DB::table('promocion_productos as pp')
                        ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                        ->where('pp.id_promocion', $idPromo)
                        ->select('p.id_producto', 'p.nombre', 'p.precio_venta', 'pp.cantidad as cant_unitario')
                        ->get();

                    $sumaPreciosLista = 0;
                    foreach ($itemsPromoDB as $ip) {
                        $sumaPreciosLista += ($ip->precio_venta * $ip->cant_unitario);
                    }
                    $factorDescuento = ($sumaPreciosLista > 0) ? ($precioComboIngresado / $sumaPreciosLista) : 1;

                    foreach ($itemsPromoDB as $ip) {
                        $cantTotalItem = $ip->cant_unitario * $cantCombos;

                        $promosDesglosadas[] = [
                            'id_producto' => $ip->id_producto,
                            'cantidad'    => $cantTotalItem,
                            'precio'      => $ip->precio_venta * $factorDescuento,
                        ];
                    }
                }
            }

            $tieneCuentaCorriente = ($montoCuentaCorriente > 0);

            $idClienteSeleccionado = !empty($request->id_cliente) ? (int) $request->id_cliente : null;

            // 1. Crear la venta en `ventas`
            $idVenta = DB::table('ventas')->insertGetId([
                'id_usuario'       => Auth::id(),
                'fecha_venta'      => now()->format('Y-m-d H:i:s'),
                'monto_total'      => $montoTotal,
                'id_metodo_pago'   => $metodoPrincipal,
                'descuento'        => $descuento,
                'id_cliente'       => $idClienteSeleccionado,
                'cliente_nombre'   => !$idClienteSeleccionado ? $request->cliente_nombre : null,
                'cliente_telefono' => !$idClienteSeleccionado ? $request->cliente_telefono : null,
                'observaciones'    => $request->observaciones,
            ]);

            // 2. Registrar desglose de pagos en `ventas_pagos`
            foreach ($pagos as $pago) {
                DB::table('ventas_pagos')->insert([
                    'id_venta'       => $idVenta,
                    'id_metodo_pago' => (int) $pago['id_metodo_pago'],
                    'monto'          => round((float) $pago['monto'], 2),
                    'created_at'     => now(),
                ]);
            }

            // 3. Registrar movimiento en cuenta corriente si corresponde
            if ($tieneCuentaCorriente) {
                $esCombinado = count($pagos) > 1 || ($montoCuentaCorriente < $montoTotal);
                $conceptoCta = $esCombinado
                    ? "Venta #{$idVenta} (pago parcial/combinado a cta. cte.)"
                    : "Venta #{$idVenta}";

                DB::table('movimientos_cuenta_corriente')->insert([
                    'id_cliente' => $request->id_cliente,
                    'id_venta'   => $idVenta,
                    'tipo'       => 'venta',
                    'concepto'   => $conceptoCta,
                    'monto'      => $montoCuentaCorriente,
                    'fecha'      => now()->format('Y-m-d H:i:s'),
                ]);
            }

            // 4. Insertar productos individuales en `ventas_productos` y descontar stock
            if (!empty($request->productos)) {
                foreach ($request->productos as $p) {
                    $precioListaDB = DB::table('productos')->where('id_producto', $p['id_producto'])->value('precio_lista');

                    DB::table('ventas_productos')->insert([
                        'id_venta'     => $idVenta,
                        'id_producto'  => $p['id_producto'],
                        'cantidad'     => $p['cantidad'],
                        'precio'       => $p['precio'],
                        'precio_lista' => $precioListaDB ?? 0.00,
                    ]);

                    DB::table('productos')->where('id_producto', $p['id_producto'])->decrement('stock', $p['cantidad']);
                }
            }

            // 5. Insertar productos desglosados de promociones en `ventas_productos` y descontar stock
            foreach ($promosDesglosadas as $itemDesglosado) {
                $precioListaDB = DB::table('productos')->where('id_producto', $itemDesglosado['id_producto'])->value('precio_lista');

                DB::table('ventas_productos')->insert([
                    'id_venta'     => $idVenta,
                    'id_producto'  => $itemDesglosado['id_producto'],
                    'cantidad'     => $itemDesglosado['cantidad'],
                    'precio'       => $itemDesglosado['precio'],
                    'precio_lista' => $precioListaDB ?? 0.00,
                ]);

                DB::table('productos')->where('id_producto', $itemDesglosado['id_producto'])->decrement('stock', $itemDesglosado['cantidad']);
            }

            DB::commit();

            return redirect()->route('views.ventas')
                ->with('success', 'Venta guardada con éxito')
                ->with('nueva_venta_id', $idVenta);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'No se pudo guardar la venta: ' . $e->getMessage());
        }
    }
}