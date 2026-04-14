<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content p {
            margin: 0 0 16px 0;
            line-height: 1.6;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 24px 0;
            transition: opacity 0.2s;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .warning {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 14px;
            color: #991b1b;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .token-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
            font-size: 12px;
            word-break: break-all;
            margin: 16px 0;
            font-family: monospace;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HealthFlow</h1>
            <p>Réinitialisation de votre mot de passe</p>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p>Vous avez demandé une réinitialisation de votre mot de passe pour votre compte HealthFlow associé à cet email.</p>

            <p><strong>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :</strong></p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="cta-button">Réinitialiser mon mot de passe</a>
            </div>

            <p>Ou copiez et collez ce lien dans votre navigateur :</p>
            <div class="token-box">{{ $resetUrl }}</div>

            <div class="warning">
                <strong>⏰ Important :</strong> Ce lien de réinitialisation expire dans 60 minutes. Si vous ne réinitialisez pas votre mot de passe dans ce délai, vous devrez refaire une demande.
            </div>

            <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email. Votre mot de passe reste sécurisé.</p>

            <p style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 14px; color: #6b7280;">
                Cordialement,<br>
                L'équipe HealthFlow
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">© 2026 HealthFlow. Tous droits réservés.</p>
            <p style="margin: 8px 0 0 0;">Cet email a été envoyé à <strong>{{ $email }}</strong></p>
        </div>
    </div>
</body>
</html>
