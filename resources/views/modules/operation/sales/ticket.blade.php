<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Ticket {{ $sale->sale_number }}
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            padding: 20px;
        }

        .ticket {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .brand {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: #4b5563;
        }

        .divider {
            border-top: 1px dashed #6b7280;
            margin: 12px 0;
        }

        .sale-info {
            font-size: 11px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            padding-bottom: 6px;
            border-bottom: 1px solid #111827;
            font-size: 10px;
        }

        td {
            padding: 5px 0;
            vertical-align: top;
        }

        .product-name {
            font-weight: 700;
        }

        .sku {
            margin-top: 2px;
            color: #6b7280;
            font-size: 9px;
        }

        .qty {
            width: 13%;
            text-align: center;
        }

        .price {
            width: 25%;
            text-align: right;
        }

        .total {
            width: 25%;
            text-align: right;
        }

        .summary {
            margin-top: 8px;
            font-size: 11px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .grand-total {
            margin-top: 6px;
            padding-top: 7px;
            border-top: 1px solid #111827;
            font-size: 16px;
            font-weight: 900;
        }

        .payment {
            margin-top: 12px;
            font-size: 11px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10px;
            line-height: 1.5;
            color: #4b5563;
        }

        .status {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 8px;
            border: 1px solid #111827;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .print-button {
            display: block;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto 15px;
            padding: 10px;
            border: 0;
            border-radius: 8px;
            background: #111827;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        @media print {

            body {
                padding: 0;
            }

            .print-button {
                display: none;
            }

            .ticket {
                width: 80mm;
                max-width: 80mm;
            }

        }

    </style>

</head>


<body>

    <button
        type="button"
        class="print-button"
        onclick="window.print()"
    >
        Imprimir ticket
    </button>


    <div class="ticket">

        {{-- Encabezado --}}
        <div class="center">

            <div class="brand">
                ABARROTESBASE
            </div>

            <div class="subtitle">
                Ticket de venta
            </div>

            <div class="status">
                {{ match ($sale->status) {
                    'confirmed' => 'Venta confirmada',
                    'cancelled' => 'Venta cancelada',
                    'returned' => 'Venta devuelta',
                    'partially_returned' => 'Devolución parcial',
                    default => ucfirst($sale->status),
                } }}
            </div>

        </div>


        <div class="divider"></div>


        {{-- Información de venta --}}
        <div class="sale-info">

            <div>
                <strong>Folio:</strong>
                {{ $sale->sale_number }}
            </div>

            <div>
                <strong>Fecha:</strong>
                {{ $sale->created_at->format('d/m/Y H:i:s') }}
            </div>

            <div>
                <strong>Cliente:</strong>
                {{ $sale->customer?->name ?? 'Público general' }}
            </div>

        </div>


        <div class="divider"></div>


        {{-- Productos --}}
        <table>

            <thead>

                <tr>

                    <th class="left">
                        Producto
                    </th>

                    <th class="qty">
                        Cant.
                    </th>

                    <th class="price">
                        Precio
                    </th>

                    <th class="total">
                        Importe
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($sale->lines as $line)

                    <tr>

                        <td>

                            <div class="product-name">
                                {{ $line->description }}
                            </div>

                            @if ($line->sku)

                                <div class="sku">
                                    SKU: {{ $line->sku }}
                                </div>

                            @endif

                        </td>

                        <td class="qty">
                            {{ number_format((float) $line->quantity, 3) }}
                        </td>

                        <td class="price">
                            ${{ number_format((float) $line->unit_price, 2) }}
                        </td>

                        <td class="total">
                            ${{ number_format((float) $line->line_total, 2) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <div class="divider"></div>


        {{-- Totales --}}
        <div class="summary">

            <div class="summary-row">

                <span>
                    Subtotal
                </span>

                <strong>
                    ${{ number_format((float) $sale->subtotal, 2) }}
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Descuento
                </span>

                <strong>
                    ${{ number_format((float) $sale->discount_total, 2) }}
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Impuestos
                </span>

                <strong>
                    ${{ number_format((float) $sale->tax_total, 2) }}
                </strong>

            </div>


            <div class="summary-row grand-total">

                <span>
                    TOTAL
                </span>

                <span>
                    ${{ number_format((float) $sale->total, 2) }}
                </span>

            </div>

        </div>


        {{-- Pagos --}}
        <div class="payment">

            <div
                style="
                    font-weight: 900;
                    margin-bottom: 5px;
                "
            >
                Forma de pago
            </div>


            @foreach ($sale->payments as $payment)

                <div class="payment-row">

                    <span>
                        {{ $payment->paymentMethod?->name ?? 'Pago' }}
                    </span>

                    <strong>
                        ${{ number_format((float) $payment->amount_applied, 2) }}
                    </strong>

                </div>


                @if ($payment->amount_received > $payment->amount_applied)

                    <div class="payment-row">

                        <span>
                            Recibido
                        </span>

                        <span>
                            ${{ number_format((float) $payment->amount_received, 2) }}
                        </span>

                    </div>

                    <div class="payment-row">

                        <span>
                            Cambio
                        </span>

                        <strong>
                            ${{ number_format(
                                (float) $payment->amount_received -
                                (float) $payment->amount_applied,
                                2
                            ) }}
                        </strong>

                    </div>

                @endif

            @endforeach

        </div>


        <div class="divider"></div>


        {{-- Pie --}}
        <div class="footer">

            <strong>
                Gracias por su compra
            </strong>

            <br>

            Conserve este ticket para cualquier aclaración.

            <br><br>

            {{ $sale->created_at->format('d/m/Y H:i') }}

        </div>

    </div>


    <script>

        window.addEventListener('load', () => {

            /*
             * Dejamos la ventana lista para impresión.
             * No imprimimos automáticamente para permitir
             * que el usuario revise antes de mandar a la impresora.
             */

        });

    </script>

</body>

</html>
