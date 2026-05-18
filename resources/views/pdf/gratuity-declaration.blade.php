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
            text-align: {{ $firm->firm_logo_position ?? 'center' }};
            margin-bottom: 20px;
        }
        .header img {
            max-width: 100%;
            max-height: 120px;
        }
        .content {
            text-align: justify;
            line-height: 1.7;
        }
        .content p {
            margin-bottom: 12px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table td, table th {
            border: 1px solid #333;
            padding: 4px 8px;
        }
    </style>
</head>
<body>
    
    <div class="content">
        {!! $body !!}
    </div>
</body>
</html>