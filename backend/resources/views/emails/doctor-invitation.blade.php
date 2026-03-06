<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation medecin</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 16px;background:linear-gradient(135deg,#0f766e,#1d4ed8);color:#ffffff;">
                            <p style="margin:0 0 8px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.9;">Assistant Sante</p>
                            <h1 style="margin:0;font-size:28px;line-height:1.2;">Vous avez ete invite par un patient a suivre ses donnees de sante.</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">Bonjour,</p>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.7;"><strong>{{ $patientName }}</strong> vous a invite a acceder a ses donnees de sante dans Assistant Sante.</p>
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.7;">Connectez-vous a l'application ou creez votre compte avec cette adresse email, puis ouvrez la section <strong>Invitations de patients</strong> dans votre tableau de bord medecin pour accepter ou refuser.</p>
                            <p style="margin:0 0 28px;">
                                <a href="{{ $applicationUrl }}" style="display:inline-block;padding:14px 24px;border-radius:999px;background-color:#0f172a;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">Acceder a l'application</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
