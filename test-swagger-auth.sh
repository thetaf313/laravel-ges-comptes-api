#!/bin/bash

# Script de test pour l'authentification avec Swagger
# Ce script teste l'authentification et montre comment utiliser les tokens avec Swagger

echo "🔐 Test d'authentification avec Swagger"
echo "======================================="

# Configuration
BASE_URL="http://localhost:8000"
API_BASE="$BASE_URL/api/v1"

echo "📋 Étape 1: Ouverture de Swagger UI"
echo "URL Swagger: $BASE_URL/api/documentation"
echo ""

echo "📋 Étape 2: Test de connexion via API"
echo "Tentative de connexion avec admin@test.com..."

# Test de connexion
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
    echo "✅ Connexion réussie!"
    echo "🔑 Token d'accès: ${ACCESS_TOKEN:0:50}..."
    
    # Affichage des scopes
    SCOPES=$(echo "$LOGIN_RESPONSE" | jq -r '.data.scopes[]' 2>/dev/null | tr '\n' ', ' | sed 's/,$//')
    echo "🎯 Scopes admin: $SCOPES"
    
    echo ""
    echo "📋 Étape 3: Test d'accès aux ressources protégées"
    
    # Test d'accès aux comptes
    COMPTES_RESPONSE=$(curl -s -X GET "$API_BASE/comptes" \
      -H "Authorization: Bearer $ACCESS_TOKEN" \
      -H "Accept: application/json")
    
    echo "Réponse des comptes:"
    COMPTES_COUNT=$(echo "$COMPTES_RESPONSE" | jq '.data | length' 2>/dev/null || echo "0")
    COMPTES_SUCCESS=$(echo "$COMPTES_RESPONSE" | jq -r '.success' 2>/dev/null || echo "false")
    
    if [ "$COMPTES_SUCCESS" = "true" ]; then
        echo "✅ Accès aux comptes réussi - $COMPTES_COUNT comptes trouvés"
    else
        echo "❌ Erreur d'accès aux comptes:"
        echo "$COMPTES_RESPONSE" | jq '.'
    fi
    
    echo ""
    echo "🎯 Instructions pour Swagger UI:"
    echo "==============================="
    echo "1. Ouvrez: $BASE_URL/api/documentation"
    echo "2. Cliquez sur le bouton 'Authorize' (🔒)"
    echo "3. Dans le champ 'bearerAuth', entrez:"
    echo "   Bearer $ACCESS_TOKEN"
    echo "4. Cliquez 'Authorize' puis 'Close'"
    echo "5. Testez les endpoints protégés !"
    echo ""
    echo "🔍 Vérification des scopes:"
    echo "- Admin dispose de tous les scopes: $SCOPES"
    echo "- Les endpoints sont protégés par les policies Laravel"
    echo ""
    echo "⚠️  Note: Les tokens expirent après 15 jours"
    echo "💡 Tip: Copiez le token complet depuis la réponse JSON"
    
else
    echo ""
    echo "❌ Échec de la connexion!"
    echo "Vérifiez que:"
    echo "- Le serveur Laravel fonctionne sur $BASE_URL"
    echo "- L'admin de test existe (php artisan db:seed --class=AdminTestSeeder)"
    echo "- Les scopes Passport sont configurés dans AuthServiceProvider"
    echo "- Les credentials sont corrects"
    
    # Diagnostic supplémentaire
    echo ""
    echo "🔧 Diagnostic:"
    
    # Test de ping
    PING_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null "$BASE_URL")
    if [ "$PING_RESPONSE" = "200" ]; then
        echo "✅ Serveur accessible (HTTP $PING_RESPONSE)"
    else
        echo "❌ Serveur non accessible (HTTP $PING_RESPONSE)"
    fi
    
    # Vérification des erreurs communes
    ERROR_MSG=$(echo "$LOGIN_RESPONSE" | jq -r '.error.message // .message // empty' 2>/dev/null)
    if [ -n "$ERROR_MSG" ]; then
        echo "⚠️  Erreur détectée: $ERROR_MSG"
        
        if [[ "$ERROR_MSG" == *"scope"* ]]; then
            echo "💡 Solution: Vérifiez la configuration des scopes dans AuthServiceProvider"
        elif [[ "$ERROR_MSG" == *"credentials"* ]]; then
            echo "💡 Solution: Vérifiez les credentials ou exécutez AdminTestSeeder"
        fi
    fi
fi

echo ""
echo "🔧 Commandes utiles:"
echo "php artisan serve --host=0.0.0.0 --port=8000  # Démarrer le serveur"
echo "php artisan l5-swagger:generate               # Regénérer Swagger"
echo "php artisan db:seed --class=AdminTestSeeder   # Créer admin de test"
echo "php artisan config:clear && php artisan cache:clear  # Vider le cache"