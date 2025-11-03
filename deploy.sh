#!/bin/bash

# Script de déploiement pour Laravel API Gestion Comptes
echo "🚀 Déploiement de l'application Laravel API Gestion Comptes"
echo "============================================================"

# Configuration
IMAGE_NAME="ges-comptes-api"
TAG=${1:-latest}

echo ""
echo "📋 Étape 1: Vérification des fichiers requis"
echo "============================================="

if [ ! -f ".env.production" ]; then
    echo "❌ Fichier .env.production manquant"
    exit 1
fi

echo "✅ Fichiers de configuration présents"

echo ""
echo "🔨 Étape 2: Build de l'image Docker"
echo "===================================="

docker build -t $IMAGE_NAME:$TAG . || {
    echo "❌ Échec du build Docker"
    exit 1
}

echo "✅ Image Docker construite avec succès"

echo ""
echo "🔍 Étape 3: Vérification de l'image"
echo "===================================="

docker images | grep $IMAGE_NAME

echo ""
echo "🧪 Étape 4: Test de l'image (optionnel)"
echo "========================================"

read -p "Voulez-vous tester l'image localement ? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Démarrage du conteneur de test..."
    docker run -d --name test-ges-comptes -p 8080:80 $IMAGE_NAME:$TAG
    
    echo "Attente du démarrage du conteneur..."
    sleep 10
    
    echo "Test de l'endpoint de santé..."
    curl -s http://localhost:8080/api/v1/auth/login | jq '.' || echo "API démarrée (le endpoint login retourne une erreur car pas de credentials)"
    
    read -p "Voulez-vous arrêter le conteneur de test ? (Y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        docker stop test-ges-comptes
        docker rm test-ges-comptes
        echo "✅ Conteneur de test supprimé"
    fi
fi

echo ""
echo "📦 Étape 5: Commandes de déploiement"
echo "====================================="

echo ""
echo "Pour déployer sur Render.com ou un autre service :"
echo ""
echo "1. Tag l'image pour votre registry :"
echo "   docker tag $IMAGE_NAME:$TAG your-registry/$IMAGE_NAME:$TAG"
echo ""
echo "2. Push l'image :"
echo "   docker push your-registry/$IMAGE_NAME:$TAG"
echo ""
echo "3. Ou utilisez le déploiement direct depuis Git sur Render.com"
echo ""
echo "📝 Variables d'environnement importantes à configurer :"
echo "   - APP_KEY (généré avec: php artisan key:generate)"
echo "   - DATABASE_URL (URL PostgreSQL de production)"
echo "   - L5_SWAGGER_CONST_HOST (URL de l'API en production)"
echo "   - MAIL_* (configuration email)"
echo ""
echo "🔐 Notes de sécurité :"
echo "   - Les clés OAuth seront générées automatiquement au démarrage"
echo "   - Assurez-vous que APP_DEBUG=false en production"
echo "   - Vérifiez que tous les secrets sont en place"
echo ""

echo "✅ Déploiement préparé avec succès !"
