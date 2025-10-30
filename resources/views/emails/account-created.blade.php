<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - Votre compte bancaire</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #ffffff;
            margin: 20px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .welcome-message {
            font-size: 18px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .content {
            margin-bottom: 30px;
        }

        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            display: block;
            margin-bottom: 5px;
        }

        .info-value {
            color: #007bff;
            font-size: 16px;
            font-weight: bold;
        }

        .password-alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .warning-icon {
            font-size: 18px;
            margin-right: 10px;
        }

        .steps {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
        }

        .step {
            margin-bottom: 10px;
            padding-left: 20px;
        }

        .step-number {
            display: inline-block;
            background-color: #007bff;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }

        .footer {
            text-align: center;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }

        .contact-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 20px;
            }

            .info-box {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏦 Ges-Comptes</div>
            <div class="welcome-message">Bienvenue dans votre espace bancaire !</div>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $client->nom }} {{ $client->prenom }}</strong>,</p>

            <p>Nous avons le plaisir de vous informer que votre compte bancaire a été créé avec succès. Voici vos informations de connexion :</p>

            <div class="info-box">
                <div class="info-label">📧 Email :</div>
                <div class="info-value">{{ $client->email }}</div>

                <div class="info-label">📱 Téléphone :</div>
                <div class="info-value">{{ $client->telephone }}</div>

                <div class="info-label">🏠 Adresse :</div>
                <div class="info-value">{{ $client->adresse }}</div>

                <div class="info-label">🆔 CNI :</div>
                <div class="info-value">{{ $client->cni }}</div>
            </div>

            <div class="password-alert">
                <span class="warning-icon">⚠️</span>
                <strong>Important :</strong> Voici votre mot de passe temporaire. Vous devez le changer lors de votre première connexion.
            </div>

            <div class="info-box">
                <div class="info-label">🔐 Mot de passe temporaire :</div>
                <div class="info-value" style="font-size: 20px; letter-spacing: 2px;">{{ $password }}</div>

                <div class="info-label">🔢 Code de vérification :</div>
                <div class="info-value" style="font-size: 20px; letter-spacing: 2px;">{{ $code }}</div>
            </div>

            <div class="steps">
                <h3>📋 Prochaines étapes :</h3>
                <div class="step">
                    <span class="step-number">1</span>
                    Connectez-vous à votre espace client avec votre email et le mot de passe temporaire
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    Changez immédiatement votre mot de passe pour un mot de passe sécurisé
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    Vérifiez vos informations personnelles dans votre profil
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    Explorez les fonctionnalités de votre compte bancaire
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="#" class="btn">Accéder à mon compte</a>
            </div>

            <div class="contact-info">
                <h4>📞 Besoin d'aide ?</h4>
                <p>Notre équipe est à votre disposition pour vous accompagner :</p>
                <p><strong>📧 Email :</strong> support@ges-comptes.com</p>
                <p><strong>📱 Téléphone :</strong> +221 33 123 45 67</p>
                <p><strong>🕒 Horaires :</strong> Lundi au Vendredi, 8h00 - 18h00</p>
            </div>
        </div>

        <div class="footer">
            <p>Cette adresse email est générée automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; 2025 Ges-Comptes - Tous droits réservés</p>
            <p><small>Pour votre sécurité, ne partagez jamais vos informations de connexion.</small></p>
        </div>
    </div>
</body>

</html>