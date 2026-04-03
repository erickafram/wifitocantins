<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F8F9FA;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:20px auto;background:#fff;border-radius:12px;border:1px solid #E5E5E5;overflow:hidden">
    <tr>
        <td style="background:linear-gradient(135deg,#007A28,#00A335);padding:24px;text-align:center">
            <p style="color:#fff;font-size:20px;font-weight:bold;margin:0">🚌 WiFi Tocantins Transporte</p>
            <p style="color:rgba(255,255,255,0.7);font-size:12px;margin:6px 0 0">Starlink · Internet a bordo</p>
        </td>
    </tr>
    <tr>
        <td style="padding:24px">
            <p style="color:#111;font-size:15px;margin:0 0 16px">Olá <strong>{{ $userName }}</strong>,</p>
            <p style="color:#333;font-size:14px;margin:0 0 20px">Seu PIX de <strong style="color:#00A335">R$ {{ $amount }}</strong> foi gerado. Copie o código abaixo e cole no app do seu banco em <strong>PIX Copia e Cola</strong>.</p>

            <div style="background:#F8F9FA;border:2px dashed #00A335;border-radius:8px;padding:16px;margin:0 0 20px">
                <p style="color:#888;font-size:10px;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;font-weight:bold">Código PIX Copia e Cola</p>
                <p style="color:#111;font-size:11px;word-break:break-all;margin:0;font-family:monospace;line-height:1.5">{{ $pixCode }}</p>
            </div>

            <p style="color:#D32F2F;font-size:13px;font-weight:bold;margin:0 0 16px">⏱️ Válido por 3 minutos</p>

            <p style="color:#888;font-size:12px;margin:0">Após o pagamento, sua internet será liberada automaticamente.</p>
        </td>
    </tr>
    <tr>
        <td style="background:#F8F9FA;padding:16px;text-align:center;border-top:1px solid #E5E5E5">
            <p style="color:#888;font-size:10px;margin:0">© {{ date('Y') }} Tocantins Transporte WiFi</p>
        </td>
    </tr>
</table>
</body>
</html>
