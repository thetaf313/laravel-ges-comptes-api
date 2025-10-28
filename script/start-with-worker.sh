#!/usr/bin/env bash
# Script alternatif pour les plateformes sans Supervisor (Render.com, etc.)

echo "🚀 Starting Ges-Comptes API with Queue Worker"

# Démarrer le worker de queue en arrière-plan
echo "📋 Starting queue worker..."
php artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3 --max-jobs=1000 > storage/logs/worker.log 2>&1 &

# Attendre un moment pour s'assurer que le worker démarre
sleep 2

# Démarrer l'application principale
echo "🌐 Starting web application..."
exec "$@"