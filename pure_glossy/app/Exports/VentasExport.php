<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasExport implements FromCollection, WithHeadings
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->whereNull('ventas_anuladas.id_venta');

        if (!empty($this->filtros['vendedor'])) {
            $query->where('users.id', $this->filtros['vendedor']);
        }

        if (!empty($this->filtros['id_cliente'])) {
            $query->where('ventas.id_cliente', $this->filtros['id_cliente']);
        }

        if (!empty($this->filtros['fechainicio']) && !empty($this->filtros['fechafin'])) {
            $query->whereBetween('ventas.fecha_venta', [
                $this->filtros['fechainicio'] . ' 00:00:00',
                $this->filtros['fechafin'] . ' 23:59:59'
            ]);
        } elseif (!empty($this->filtros['fechainicio'])) {
            $query->where('ventas.fecha_venta', '>=', $this->filtros['fechainicio'] . ' 00:00:00');
        } elseif (!empty($this->filtros['fechafin'])) {
            $query->where('ventas.fecha_venta', '<=', $this->filtros['fechafin'] . ' 23:59:59');
        }

        $ventas = $query->select(
            'ventas.id_venta',
            'ventas.fecha_venta',
            'ventas.monto_total',
            'ventas.descuento',
            'metodos_pago.nombre as metodo_pago',
            'users.name as vendedor',
            DB::raw("COALESCE(clientes_corrientes.nombre_y_apellido, 'Consumidor Final') as cliente_nombre")
        )->orderBy('ventas.fecha_venta', 'desc')->get();

        // Buscar pagos detallados en ventas_pagos
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
            $pagos = $pagosPorVenta->get($venta->id_venta);
            if ($pagos && $pagos->isNotEmpty()) {
                if ($pagos->count() > 1) {
                    $venta->metodo_pago = $pagos->map(function ($p) {
                        return $p->metodo . ' ($' . number_format((float) $p->monto, 2, '.', '') . ')';
                    })->implode(' + ');
                } else {
                    $venta->metodo_pago = $pagos->first()->metodo;
                }
            }
        }

        $sumaTotal = $ventas->sum('monto_total');

        $ventas->push((object)[
            'id_venta'       => '',
            'fecha_venta'    => '',
            'monto_total'    => '',
            'descuento'      => '',
            'metodo_pago'    => '',
            'vendedor'       => '',
            'cliente_nombre' => ''
        ]);

        $ventas->push((object)[
            'id_venta'       => '',
            'fecha_venta'    => 'TOTAL RECAUDADO:',
            'monto_total'    => $sumaTotal,
            'descuento'      => '',
            'metodo_pago'    => '',
            'vendedor'       => '',
            'cliente_nombre' => ''
        ]);

        return $ventas;
    }

    public function headings(): array
    {
        return [
            'ID Venta',
            'Fecha y Hora',
            'Monto Total ($)',
            'Descuento ($)',
            'Métodos de Pago',
            'Vendedor',
            'Cliente Corriente'
        ];
    }
}