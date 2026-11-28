<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presupuesto #{{ $presupuesto->id_presupuesto }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #FFF0F8;
            color: #70405D;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 25px 12px;
        }

        .no-print {
            max-width: 380px;
            margin: 0 auto 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #F38ECA;
            font-family: inherit;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn-volver {
            background: #FFF9FC;
            color: #70405D;
        }

        .btn-imprimir {
            background: #F38ECA;
            color: #70405D;
            font-weight: bold;
        }

        .ticket {
            width: 100%;
            max-width: 380px;
            margin: auto;
            padding: 24px 19px;
            background: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(112, 64, 93, .10);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 14px;
        }

        .logo {
            width: 150px;
            max-width: 75%;
            height: auto;
            display: inline-block;
            border-radius: 8px;
        }

        .redes {
            background: #FFF0F8;
            border-radius: 7px;
            padding: 10px 7px;
            text-align: center;
            font-size: 10px;
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .separador {
            border: none;
            border-top: 1px dashed #C783AA;
            margin: 15px 0;
        }

        .aviso {
            background: #FFF0F8;
            border-radius: 7px;
            padding: 11px 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            text-align: center;
        }

        .aviso-x {
            font-size: 38px;
            font-weight: bold;
            line-height: 1;
            color: #70405D;
            padding: 0 10px;
        }

        .aviso-texto {
            font-size: 10.5px;
            font-weight: bold;
            line-height: 1.6;
            letter-spacing: .4px;
            border-left: 1px solid #C783AA;
            padding-left: 12px;
        }

        .comprobante {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 11px;
        }

        .comprobante strong {
            font-size: 12px;
        }

        .fecha {
            text-align: right;
            line-height: 1.7;
            font-size: 10.5px;
        }

        .datos-cliente {
            font-size: 11px;
            line-height: 1.85;
            overflow-wrap: anywhere;
        }

        .seccion-titulo {
            background: #FCE0EF;
            color: #70405D;
            font-weight: bold;
            font-size: 10px;
            letter-spacing: .5px;
            padding: 6px 8px;
            border-radius: 6px;
            margin: 12px 0 8px;
            text-transform: uppercase;
        }

        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        .tabla-productos th {
            background: #F38ECA;
            color: #FFFFFF;
            padding: 8px 3px;
            text-align: center;
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        .tabla-productos th:first-child {
            border-radius: 7px 0 0 7px;
        }

        .tabla-productos th:last-child {
            border-radius: 0 7px 7px 0;
        }

        .tabla-productos td {
            padding: 8px 3px;
            border-bottom: 1px solid #F8D9EA;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .tabla-productos tbody tr:nth-child(even) {
            background: #FFF0F8;
        }

        .tabla-productos td:nth-child(2) {
            text-align: left;
        }

        .observaciones-box {
            background: #FFF0F8;
            border: 1px dashed #C783AA;
            border-radius: 7px;
            padding: 9px 10px;
            margin-top: 12px;
            font-size: 10.5px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .observaciones-box p {
            margin-top: 4px;
            white-space: pre-line;
        }

        .totales {
            width: 100%;
            margin-top: 10px;
        }

        .totales table {
            width: 100%;
            border-collapse: collapse;
        }

        .totales td {
            padding: 6px 5px;
            font-size: 11px;
        }

        .totales td:first-child {
            text-align: left;
        }

        .totales td:last-child {
            text-align: right;
        }

        .fila-total td {
            background: #FCE0EF;
            color: #70405D;
            font-weight: bold;
            font-size: 14px;
            padding: 11px 9px;
        }

        .fila-total td:first-child {
            border-radius: 7px 0 0 7px;
        }

        .fila-total td:last-child {
            border-radius: 0 7px 7px 0;
        }

        .avisos {
            background: #FFF0F8;
            border-radius: 7px;
            padding: 9px 10px;
            margin-top: 14px;
            font-size: 10px;
            line-height: 1.6;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            font-weight: bold;
            color: #B52F72;
            overflow-wrap: anywhere;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 3mm;
            }

            body {
                background: #FFFFFF;
                padding: 0;
                font-size: 11px;
            }

            .no-print {
                display: none !important;
            }

            .ticket {
                width: 100%;
                max-width: none;
                padding: 8px 3px;
                border-radius: 0;
                box-shadow: none;
            }

            .tabla-productos thead tr,
            .seccion-titulo,
            .observaciones-box,
            .avisos,
            .aviso,
            .redes,
            .fila-total td {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button type="button" class="btn btn-volver" onclick="history.back()">
            ← Volver
        </button>

        <button type="button" class="btn btn-imprimir" onclick="window.print()">
            Imprimir presupuesto
        </button>
    </div>

    <div class="ticket">

        <!-- LOGO -->
        <div class="logo-container">
            <img src="{{ asset('logo_se.jpeg') }}" alt="Logo Empresa" class="logo">
        </div>

        <!-- DATOS DEL LOCAL -->
        <div class="redes">
            <div><strong>{{ $local['nombre'] }}</strong></div>
            @if(!empty($local['facebook']))
                <div>Facebook: {{ $local['facebook'] }}</div>
            @endif
            @if(!empty($local['instagram']))
                <div>Instagram: {{ $local['instagram'] }}</div>
            @endif
            @if(!empty($local['telefono']))
                <div>Teléfono: {{ $local['telefono'] }}</div>
            @endif
            @if(!empty($local['direccion']))
                <div>{{ $local['direccion'] }}</div>
            @endif
        </div>

        <hr class="separador">

        <!-- AVISO -->
        <div class="aviso">
            <div class="aviso-x">X</div>
            <div class="aviso-texto">
                PRESUPUESTO
                <br>
                NO VÁLIDO COMO FACTURA
            </div>
        </div>

        <hr class="separador">

        <!-- COMPROBANTE Y FECHA -->
        <div class="comprobante">
            <strong>
                Presupuesto N° {{ str_pad($presupuesto->id_presupuesto, 8, '0', STR_PAD_LEFT) }}
            </strong>

            <div class="fecha">
                Fecha: {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y') }}
                <br>
                Hora: {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('H:i') }}
            </div>
        </div>

        <hr class="separador">

        <!-- DATOS DEL CLIENTE Y TÍTULO -->
        <div class="datos-cliente">
            @if (!empty($presupuesto->titulo))
                <div><strong>Título / Obra:</strong> {{ $presupuesto->titulo }}</div>
            @endif
            <div><strong>Cliente:</strong> {{ $presupuesto->nombre_cliente ?: 'Consumidor final' }}</div>
            <div><strong>Teléfono:</strong> {{ $presupuesto->telefono_cliente ?: '-' }}</div>
        </div>

        <hr class="separador">

        @php
            $serviciosCategorias = [13, 14];
            $articulos = $productos->filter(function($item) use ($serviciosCategorias) {
                $cat = strtolower($item->nombre_categoria ?? '');
                return !in_array($item->id_categoria ?? 0, $serviciosCategorias)
                    && !str_starts_with($cat, 's -')
                    && !str_starts_with($cat, 'in -')
                    && !str_contains($cat, 'servicio')
                    && !str_contains($cat, 'instalac');
            });

            $servicios = $productos->filter(function($item) use ($serviciosCategorias) {
                $cat = strtolower($item->nombre_categoria ?? '');
                return in_array($item->id_categoria ?? 0, $serviciosCategorias)
                    || str_starts_with($cat, 's -')
                    || str_starts_with($cat, 'in -')
                    || str_contains($cat, 'servicio')
                    || str_contains($cat, 'instalac');
            });
        @endphp

        @if($articulos->count() > 0 || (isset($promociones) && count($promociones) > 0))
            @if($servicios->count() > 0)
                <div class="seccion-titulo">Productos</div>
            @endif

            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th style="width: 16%;">Código</th>
                        <th style="width: 30%;">Descripción</th>
                        <th style="width: 10%;">Cant.</th>
                        <th style="width: 22%;">P. Unit.</th>
                        <th style="width: 22%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articulos as $producto)
                        <tr>
                            <td>{{ $producto->codigo ?: $producto->codigo_barra ?: '-' }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>${{ number_format($producto->precio, 2, ',', '.') }}</td>
                            <td>${{ number_format($producto->cantidad * $producto->precio, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    @if(isset($promociones) && count($promociones) > 0)
                        @foreach ($promociones as $promo)
                            <tr>
                                <td>PROMO</td>
                                <td><strong>Promo:</strong> {{ $promo->nombre }}</td>
                                <td>{{ $promo->cantidad }}</td>
                                <td>${{ number_format($promo->precio, 2, ',', '.') }}</td>
                                <td>${{ number_format($promo->cantidad * $promo->precio, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        @endif

        @if($servicios->count() > 0)
            <div class="seccion-titulo">Servicios e instalaciones</div>
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th style="width: 16%;">Código</th>
                        <th style="width: 30%;">Descripción</th>
                        <th style="width: 10%;">Cant.</th>
                        <th style="width: 22%;">P. Unit.</th>
                        <th style="width: 22%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $servicio)
                        <tr>
                            <td>{{ $servicio->codigo ?: $servicio->codigo_barra ?: '-' }}</td>
                            <td>{{ $servicio->nombre }}</td>
                            <td>{{ $servicio->cantidad }}</td>
                            <td>${{ number_format($servicio->precio, 2, ',', '.') }}</td>
                            <td>${{ number_format($servicio->cantidad * $servicio->precio, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($presupuesto->observaciones))
            <div class="observaciones-box">
                <strong>Observaciones:</strong>
                <p>{{ $presupuesto->observaciones }}</p>
            </div>
        @endif

        <hr class="separador">

        <!-- TOTALES -->
        <div class="totales">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>${{ number_format($presupuesto->monto_total + $presupuesto->descuento, 2, ',', '.') }}</td>
                </tr>

                @if ($presupuesto->descuento > 0)
                    <tr>
                        <td>Descuento:</td>
                        <td>${{ number_format($presupuesto->descuento, 2, ',', '.') }}</td>
                    </tr>
                @endif

                <tr class="fila-total">
                    <td>TOTAL:</td>
                    <td>${{ number_format($presupuesto->monto_total, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- AVISOS -->
        <div class="avisos">
            <p>* Precios sujetos a cambios sin previo aviso.</p>
            <p>* Consultar stock al momento de realizar el pedido.</p>
        </div>

        <!-- PIE -->
        <div class="footer">
            {{ $local['nombre'] }} — {{ $local['telefono'] }}
        </div>

    </div>

</body>
</html>