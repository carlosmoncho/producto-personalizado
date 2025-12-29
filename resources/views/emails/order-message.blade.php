<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $order->order_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #9a7420;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-height: 50px;
        }
        .header h1 {
            color: #ffffff;
            margin: 10px 0 0 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .order-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .order-info h3 {
            margin: 0 0 10px 0;
            color: #9a7420;
            font-size: 14px;
            text-transform: uppercase;
        }
        .order-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .order-number {
            font-weight: bold;
            color: #9a7420;
            font-size: 16px;
        }
        .message-body {
            margin: 20px 0;
        }
        .message-body p {
            margin: 10px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .footer a {
            color: #9a7420;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #9a7420;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 15px;
        }
        .btn:hover {
            background-color: #7a5a18;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>Hostelking</h1>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Order Info --}}
            <div class="order-info">
                <h3>Referencia del pedido</h3>
                <p class="order-number">{{ $orderNumber }}</p>
                <p>Cliente: {{ $customerName }}</p>
            </div>

            {{-- Message Body --}}
            <div class="message-body">
                {!! $messageBody !!}
            </div>

            <div class="divider"></div>

            {{-- Call to action --}}
            <p style="text-align: center; color: #666666; font-size: 14px;">
                Si tienes alguna pregunta, puedes responder directamente a este email.
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                <strong>Hostelking - Productos Personalizados para Hostelería</strong>
            </p>
            <p>
                Este email fue enviado a {{ $order->customer_email }} en relación al pedido {{ $orderNumber }}.
            </p>
            <p>
                <a href="{{ config('app.url') }}">www.hostelking.es</a>
            </p>
            <p style="margin-top: 15px; font-size: 11px; color: #999999;">
                &copy; {{ date('Y') }} Hostelking. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
