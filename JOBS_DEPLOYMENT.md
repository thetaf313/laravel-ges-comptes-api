# Gestion des Jobs en Production

## 🚀 Jobs Implémentés

-   **ArchiveExpiredBlockedAccounts** : Archive automatiquement les comptes épargne bloqués expirés
-   **UnarchiveExpiredBlockedAccounts** : Désarchive automatiquement les comptes prêts à être réactivés

## 📋 Configuration

### Variables d'environnement

```bash
QUEUE_CONNECTION=database  # Utilise la base de données pour la queue
```

### Migrations requises

```bash
php artisan migrate  # Table jobs
php artisan migrate --database=pgsql_archive  # Tables d'archivage
php artisan db:seed --class=CreateArchiveTablesSeeder
```

## 🛠️ Déploiement selon la plateforme

### Option 1: Avec Supervisor (Recommandé pour serveurs dédiés)

Le script `start.sh` inclut automatiquement un worker Supervisor.

**Configuration Supervisor :** `conf/supervisor-worker.conf`

### Option 2: Plateformes sans Supervisor (Render.com, Heroku, etc.)

Utilisez le script alternatif :

```bash
# Dans votre configuration de déploiement
CMD ["script/start-with-worker.sh", "php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
```

### Option 3: Service séparé (Kubernetes, Docker Swarm)

Créez un service séparé pour les workers :

```yaml
# docker-compose.worker.yml
version: "3.8"
services:
    worker:
        build: .
        command: php artisan queue:work --verbose --tries=3 --timeout=90
        environment:
            - APP_ENV=production
        depends_on:
            - app
```

## 📊 Monitoring des Jobs

### Logs

```bash
# Logs des workers
tail -f storage/logs/worker.log

# Logs Laravel
tail -f storage/logs/laravel.log
```

### État des jobs

```bash
# Voir les jobs en attente
php artisan queue:failed

# Statistiques des jobs
php artisan queue:status
```

### Commandes de gestion

```bash
# Redémarrer les workers
php artisan queue:restart

# Vider la queue
php artisan queue:clear

# Traiter manuellement les jobs archivage
php artisan app:archive-expired-accounts --sync
php artisan app:unarchive-expired-accounts --sync
```

## ⚠️ Points d'attention

1. **Mémoire** : Les workers peuvent consommer beaucoup de mémoire
2. **Timeout** : Configurez des timeouts appropriés selon vos jobs
3. **Monitoring** : Surveillez les échecs de jobs (`queue:failed`)
4. **Scalabilité** : Augmentez `numprocs` dans Supervisor si nécessaire

## 🔧 Dépannage

### Jobs qui ne se lancent pas

```bash
# Vérifier la configuration
php artisan config:show queue

# Tester un job manuellement
php artisan app:archive-expired-accounts --sync
```

### Jobs qui échouent

```bash
# Voir les jobs échoués
php artisan queue:failed

# Relancer un job échoué
php artisan queue:retry {id}

# Supprimer un job échoué
php artisan queue:forget {id}
```
