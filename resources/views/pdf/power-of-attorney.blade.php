<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Procuração</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 100%;
            max-height: 120px;
        }
        h2 {
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
            line-height: 1.7;
        }
        .content p {
            margin-bottom: 12px;
        }
        .city-date {
            text-align: center;
            margin-top: 30px;
        }
        .signature {
            width: 50%;
            margin: 40px auto 0;
            border-top: 1px solid #111;
            text-align: center;
            padding-top: 6px;
            font-size: 11px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="footer">
        {{ $firm->firm_address }}{{ $firm->firm_city ? ', ' . $firm->firm_city : '' }}{{ $firm->firm_state ? '/' . $firm->firm_state : '' }}{{ $firm->firm_zipcode ? ' — CEP: ' . $firm->firm_zipcode : '' }}<br>
        {{ $firm->firm_phone }}{{ $firm->firm_email ? ' | ' . $firm->firm_email : '' }}
    </div>

    @if($logoBase64)
        <div class="header">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
    @endif

    <h2>Procuração</h2>

    <div class="content">
        {!! $body !!}

        <p class="city-date">{{ $firmCity }}, {{ $currentDate }}.</p>

        <div class="signature">
            {{ $client->name ?? '' }}
        </div>
    </div>
</body>
</html>