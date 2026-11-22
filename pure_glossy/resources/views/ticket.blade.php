
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $venta->id_venta }}</title>

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
            margin-bottom: 16px;
        }

        .logo {
            width: 180px;
            max-width: 75%;
            height: auto;
            display: inline-block;
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
            margin: 16px 0;
        }

        .aviso {
            background: #FFF0F8;
            border-radius: 7px;
            padding: 12px 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 13px;
            text-align: center;
        }

        .aviso-x {
            font-size: 42px;
            font-weight: bold;
            line-height: 1;
            color: #70405D;
            padding: 0 12px;
        }

        .aviso-texto {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.6;
            letter-spacing: .5px;
            border-left: 1px solid #C783AA;
            padding-left: 13px;
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
        }

        .datos-cliente {
            font-size: 11px;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .datos-cliente strong {
            font-weight: bold;
        }

        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }

        .tabla-productos th {
            background: #F38ECA;
            color: #FFFFFF;
            padding: 9px 3px;
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
            padding: 10px 3px;
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

        .resumen-inferior {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .tabla-pagos {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .tabla-pagos th {
            background: #FCE0EF;
            padding: 9px;
            text-align: left;
            color: #70405D;
            border-radius: 6px;
        }

        .tabla-pagos td {
            padding: 8px 9px;
            border-bottom: 1px solid #F8D9EA;
        }

        .tabla-pagos td:last-child {
            text-align: right;
        }

        .totales {
            width: 100%;
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
            font-size: 15px;
            padding: 12px 9px;
        }

        .fila-total td:first-child {
            border-radius: 7px 0 0 7px;
        }

        .fila-total td:last-child {
            border-radius: 0 7px 7px 0;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            font-weight: bold;
            color: #B52F72;
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
            .aviso,
            .redes,
            .tabla-pagos th,
            .fila-total td {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn btn-volver" onclick="history.back()">
            ← Volver
        </button>

        <button class="btn btn-imprimir" onclick="window.print()">
            Imprimir ticket
        </button>
    </div>

    <div class="ticket">

        <!-- LOGO -->
        <div class="logo-container">
            <img
                src="{{ asset('images/logo-pure-glossy.png') }}"
                alt="Pure Glossy"
                class="logo"
            >
        </div>

        <!-- REDES Y CONTACTO -->
        <div class="redes">
            Instagram: {{ $local['instagram'] }}
            <br>
            Teléfono: {{ $local['telefono'] }}
        </div>

        <hr class="separador">

        <!-- AVISO LEGAL -->
        <div class="aviso">
            <div class="aviso-x">X</div>
            <div class="aviso-texto">
                TICKET NO VÁLIDO
                <br>
                COMO FACTURA
            </div>
        </div>

        <hr class="separador">

        <!-- COMPROBANTE -->
        <div class="comprobante">
            <strong>
                Comprobante N° {{ str_pad($venta->id_venta, 8, '0', STR_PAD_LEFT) }}
            </strong>

            <div class="fecha">
                Fecha:
                {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}
                <br>
                Hora:
                {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('H:i') }}
            </div>
        </div>

        <hr class="separador">

        <!-- DATOS DEL CLIENTE -->
        <div class="datos-cliente">

            <div>
                <strong>Cliente:</strong>
                @if($venta->clientec)
                    {{ $venta->nombre_clientec }}
                @elseif(!empty($venta->cliente_nombre))
                    {{ $venta->cliente_nombre }}
                @else
                    Consumidor final
                @endif
            </div>

            <div>
                <strong>Teléfono:</strong>
                @if($venta->clientec)
                    {{ $venta->telefono_clientec ?? 'No proporcionado' }}
                @elseif(!empty($venta->cliente_telefono))
                    {{ $venta->cliente_telefono }}
                @else
                    No proporcionado
                @endif
            </div>

            <div>
                <strong>Vendedor:</strong>
                {{ $venta->vendedor_name }}
            </div>

        </div>

        <hr class="separador">

        <!-- PRODUCTOS -->
        <table class="tabla-productos">

            <thead>
                <tr>
                    <th style="width: 16%;">Código</th>
                    <th style="width: 30%;">Descripción</th>
                    <th style="width: 10%;">Cant.</th>
                    <th style="width: 22%;">P. Unit.</th>
                    <th style="width: 22%;">Importe</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($productos as $producto)
                    <tr>
                        <td>{{ $producto->codigo ?: $producto->codigo_barra }}</td>

                        <td>
                            {{ $producto->producto }}

                            @if(isset($producto->precio_lista) && $producto->precio < $producto->precio_lista)
                                <strong style="font-size: 9px;">- Promo</strong>
                            @endif
                        </td>

                        <td>{{ $producto->cantidad }}</td>

                        <td>
                            ${{ number_format($producto->precio, 2, ',', '.') }}
                        </td>

                        <td>
                            ${{ number_format($producto->precio * $producto->cantidad, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

        <hr class="separador">

        <!-- PAGOS Y TOTALES -->
        <div class="resumen-inferior">

            @if(isset($pagos) && $pagos->count() > 0)

                <table class="tabla-pagos">

                    <thead>
                        <tr>
                            <th colspan="2">Forma de pago</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($pagos as $pago)
                            <tr>
                                <td>{{ $pago->metodo }}</td>
                                <td>
                                    ${{ number_format($pago->monto, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            @endif

            <div class="totales">

                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td>
                            ${{ number_format($venta->monto_total + $venta->descuento, 2, ',', '.') }}
                        </td>
                    </tr>

                    @if ($venta->descuento > 0)
                        <tr>
                            <td>Descuento:</td>
                            <td>
                                ${{ number_format($venta->descuento, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif

                    <tr class="fila-total">
                        <td>TOTAL:</td>
                        <td>
                            ${{ number_format($venta->monto_total, 2, ',', '.') }}
                        </td>
                    </tr>
                </table>

            </div>

        </div>

        <hr class="separador">

        <!-- CIERRE -->
        <div class="footer">
            Gracias por tu compra
        </div>

    </div>

</body>
</html>
