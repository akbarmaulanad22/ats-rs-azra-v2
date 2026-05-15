<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: #1a5c3a; padding: 20px 30px; }
        .header span { color: #fff; font-size: 18px; font-weight: bold; }
        .content { padding: 30px; white-space: pre-line; line-height: 1.7; }
        .footer { background: #f5f5f5; padding: 16px 30px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <span>RS Azra</span>
        </div>
        <div class="content">{{ $body }}</div>
        <div class="footer">
            Email ini dikirim otomatis oleh Sistem Rekrutmen RS Azra. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
