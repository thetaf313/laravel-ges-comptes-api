#!/bin/bash

# Script de déploiement et test Swagger pour la production
echo "🚀 Script de déploiement et test Swagger Production"
echo "=================================================="

# Configuration
PROD_URL="https://sayande-moustapha-gestion-comptes-api.onrender.com"
API_BASE="$PROD_URL/api/v1"

echo "📋 Environnement de production:"
echo "Base URL: $PROD_URL"
echo "API Base: $API_BASE"
echo "Swagger UI: $PROD_URL/api/documentation"
echo ""

echo "🔧 Étape 1: Préparation du déploiement"
echo "======================================="
echo "1. Copier .env.production vers .env sur le serveur"
echo "2. Exécuter les commandes de déploiement:"
echo "   php artisan config:cache"
echo "   php artisan l5-swagger:generate"
echo "   php artisan migrate --force"
echo ""

echo "🧪 Étape 2: Test de l'API en production"
echo "======================================="

# Test de ping de l'API
echo "Test de connectivité..."
PING_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null "$PROD_URL")

if [ "$PING_RESPONSE" = "200" ]; then
    echo "✅ Serveur accessible (HTTP $PING_RESPONSE)"
else
    echo "❌ Serveur non accessible (HTTP $PING_RESPONSE)"
    echo "Vérifiez que le déploiement s'est bien passé"
    exit 1
fi

echo ""
echo "🔐 Étape 3: Test d'authentification"
echo "==================================="

# Test de connexion avec les credentials admin
LOGIN_RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "admin123",
    "code": "000000"
  }')

echo "Réponse de connexion:"
echo "$LOGIN_RESPONSE" | jq '.'

# Extraction du token d'accès
ACCESS_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token // empty')

if [ -n "$ACCESS_TOKEN" ] && [ "$ACCESS_TOKEN" != "null" ]; then
    echo ""
    echo "✅ Authentification réussie en production!"
    echo "🔑 Token: ${ACCESS_TOKEN:0:50}..."
    
    echo ""
    echo "🧪 Étape 4: Test des endpoints protégés"
    echo "======================================="
    
    # Test d'accès aux comptes
    COMPTES_RESPONSE=$(curl -s -X GET "$API_BASE/comptes" \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -H "Accept: application/json")
    
    echo "Test de récupération des comptes:"
    echo "$COMPTES_RESPONSE" | jq '.success, .message, (.data | length)'
    
    echo ""
    echo "🎯 Instructions pour tester Swagger en production:"
    echo "================================================="
    echo "1. Ouvrez: $PROD_URL/api/documentation"
    echo "2. Cliquez sur 'Authorize' (🔒)"
    echo "3. Bearer Token: $ACCESS_TOKEN"
    echo "4. Testez les endpoints!"
    echo ""
    echo "📱 URLs importantes:"
    echo "- API: $PROD_URL"
    echo "- Swagger: $PROD_URL/api/documentation"
    echo "- Health: $PROD_URL/api/health (si disponible)"
    
else
    echo ""
    echo "❌ Échec de l'authentification en production!"
    echo "Causes possibles:"
    echo "- AdminTestSeeder non exécuté en production"
    echo "- Base de données non migrée"
    echo "- Configuration incorrecte"
    echo ""
    echo "Solutions:"
    echo "1. Exécuter: php artisan db:seed --class=AdminTestSeeder"
    echo "2. Vérifier la configuration de la base de données"
    echo "3. Vérifier les logs: php artisan log:clear && php artisan tinker"
fi

echo ""
echo "🔧 Commandes de déploiement à exécuter sur le serveur:"
echo "====================================================="
echo "# Copier les variables d'environnement"
echo "cp .env.production .env"
echo ""
echo "# Optimiser la configuration"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo "php artisan view:cache"
echo ""
echo "# Générer la documentation Swagger"
echo "php artisan l5-swagger:generate"
echo ""
echo "# Migrations et seeders"
echo "php artisan migrate --force"
echo "php artisan db:seed --class=AdminTestSeeder"
echo ""
echo "# Permissions (si nécessaire)"
echo "chmod -R 775 storage bootstrap/cache"
echo "chown -R www-data:www-data storage bootstrap/cache"

echo ""
echo "✨ Test terminé!"