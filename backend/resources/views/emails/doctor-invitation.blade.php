<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation médecin - Assistant Santé</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif; color: #1a202c; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px 12px; }
        .card { background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        .header { padding: 48px 32px; background: linear-gradient(135deg, #0D47A1 0%, #00897B 100%); color: #ffffff; text-align: center; }
        .header-badge { font-size: 12px; letter-spacing: 1px; text-transform: uppercase; opacity: 0.9; margin: 0 0 12px; font-weight: 600; }
        .header-title { font-size: 32px; line-height: 1.3; margin: 0; font-weight: 700; }
        .content { padding: 48px 32px; }
        .greeting { font-size: 16px; line-height: 1.6; margin: 0 0 20px; color: #2d3748; }
        .message { font-size: 16px; line-height: 1.7; margin: 0 0 24px; color: #3a4958; }
        .message strong { color: #0D47A1; font-weight: 600; }
        .accent-box { background-color: #e8f4f8; border-left: 4px solid #00897B; padding: 20px; border-radius: 6px; margin: 24px 0; font-size: 15px; line-height: 1.6; color: #294e5f; }
        .cta-button { display: inline-block; padding: 16px 32px; background: linear-gradient(135deg, #00897B 0%, #0D47A1 100%); color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 700; border-radius: 8px; margin: 32px 0; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(13, 71, 161, 0.3); }
        .footer { padding: 24px 32px; background-color: #f9fafb; border-top: 1px solid #e2e8f0; font-size: 13px; color: #718096; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <p class="header-badge">Assistant Santé</p>
                <h1 class="header-title">Invitation médicale sécurisée</h1>
            </div>
            <div class="content">
                <p class="greeting">Bonjour,</p>
                <p class="message"><strong>{{ $patientName }}</strong> vous a invité à accéder à ses données de santé dans <strong>Assistant Santé</strong>.</p>
                <div class="accent-box">
                    <strong style="color: #0D47A1;">Étapes à suivre :</strong><br>
                    1. Connectez-vous ou créez un compte avec cette adresse email<br>
                    2. Accédez à votre tableau de bord médecin<br>
                    3. Ouvrez la section "Invitations de patients"<br>
                    4. Acceptez ou refusez l'invitation
                </div>
                <p style="text-align: center; margin: 0;">
                    <a href="{{ $applicationUrl }}" class="cta-button">Accéder à l'application</a>
                </p>
            </div>
            <div class="footer">
                <p style="margin: 0; color: #718096;">Cet email a été envoyé en toute sécurité. Assistant Santé protège vos données avec les normes les plus élevées.</p>
                <p style="margin: 8px 0 0; color: #a0aec0; font-size: 12px;">© 2026 Assistant Santé – Plateforme de gestion médicale</p>
            </div>
        </div>
    </div>
</body>
</html>
