<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace de Pago - Hostelking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .logo {
            max-width: 180px;
            height: auto;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 20px 0;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .order-info h2 {
            font-size: 18px;
            color: #495057;
            margin-top: 0;
        }
        .order-info p {
            margin: 8px 0;
        }
        .order-items {
            margin: 20px 0;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th,
        .order-items td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .order-items th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
        }
        .total-row td {
            border-top: 2px solid #333;
        }
        .payment-button {
            display: inline-block;
            background: #28a745;
            color: #fff !important;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .payment-button:hover {
            background: #218838;
        }
        .center {
            text-align: center;
        }
        .payment-link {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            word-break: break-all;
            font-size: 12px;
            color: #666;
            margin-top: 15px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ config('app.url') }}/images/logo.png" alt="Hostelking" class="logo">
            <h1>Tu pedido est&aacute; listo para pagar</h1>
        </div>

        <p>Hola <strong>{{ $order->customer_name }}</strong>,</p>

        <p>Gracias por tu pedido de productos personalizados. Hemos preparado tu presupuesto y est&aacute; listo para proceder con el pago.</p>

        <div class="order-info">
            <h2>Detalles del Pedido</h2>
            <p><strong>N&uacute;mero de pedido:</strong> {{ $order->order_number }}</p>
            <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
            @if($order->company_name)
            <p><strong>Empresa:</strong> {{ $order->company_name }}</p>
            @endif
        </div>

        <div class="order-items">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="text-align: right;">Cantidad</th>
                        <th style="text-align: right;">Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td style="text-align: right;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($item->total_price, 2, ',', '.') }} &euro;</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" style="text-align: right;">Subtotal:</td>
                        <td style="text-align: right;">{{ number_format($order->subtotal, 2, ',', '.') }} &euro;</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right;">IVA ({{ $order->tax_rate }}%):</td>
                        <td style="text-align: right;">{{ number_format($order->tax_amount, 2, ',', '.') }} &euro;</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;">Total:</td>
                        <td style="text-align: right;">{{ number_format($order->total_amount, 2, ',', '.') }} &euro;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="center">
            <a href="{{ $paymentUrl }}" class="payment-button">Realizar Pago Seguro</a>
        </div>

        <div class="note">
            <strong>Nota:</strong> Al hacer clic en el bot&oacute;n ser&aacute;s redirigido a nuestra pasarela de pago segura donde podr&aacute;s completar la transacci&oacute;n con tarjeta de cr&eacute;dito/d&eacute;bito.
        </div>

        <p style="font-size: 14px; color: #666;">Si el bot&oacute;n no funciona, copia y pega este enlace en tu navegador:</p>
        <div class="payment-link">
            {{ $paymentUrl }}
        </div>

        <div class="footer">
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p><strong>Hostelking Personalizados</strong></p>
            <p>
                <a href="mailto:info@hostelking.com">info@hostelking.com</a> |
                <a href="https://hostelking.com">www.hostelking.com</a>
            </p>
        </div>
    </div>
</body>
</html>
