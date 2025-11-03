# 🚀 Guide de déploiement en production

## 🔑 Génération des clés OAuth2 Laravel Passport

### Problème

Laravel Passport utilise des clés RSA pour signer les tokens JWT. Ces clés **doivent être générées** en production.

### Solution implémentée

#### 1. **Génération automatique dans le Dockerfile**

```dockerfile
# Générer les clés OAuth2 pour Laravel Passport
RUN php artisan passport:keys --force
```

#### 2. **Vérification au démarrage**

Le script de démarrage vérifie si les clés existent et les génère si nécessaire :

```bash
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    echo "Generating Passport keys..."
    php artisan passport:keys --force
fi
```

### Fichiers générés

-   `storage/oauth-private.key` : Clé privée RSA pour signer les tokens
-   `storage/oauth-public.key` : Clé publique RSA pour vérifier les tokens

### ⚠️ Important

-   ✅ Les clés sont générées automatiquement à chaque build Docker
-   ✅ Les clés locales ne sont **pas** copiées dans le conteneur (via `.dockerignore`)
-   ✅ Chaque environnement a ses propres clés
-   🔒 Les clés sont stockées dans `storage/` qui a les bonnes permissions

## 📦 Déploiement

### Méthode 1 : Script automatique

```bash
./deploy.sh
```

### Méthode 2 : Manuel

#### Étape 1 : Build de l'image

```bash
docker build -t ges-comptes-api:latest .
```

#### Étape 2 : Vérification

```bash
docker run --rm ges-comptes-api:latest ls -la storage/oauth-*.key
```

Vous devriez voir :

```
-rw-r--r-- 1 www-data www-data 1704 oauth-private.key
-rw-r--r-- 1 www-data www-data  451 oauth-public.key
```

#### Étape 3 : Déploiement sur Render.com

Render.com construit automatiquement l'image à partir du Dockerfile.

### Variables d'environnement requises

Copiez ces variables depuis `.env.production` dans la configuration Render :

```env
APP_NAME=Ges-Comptes
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sayande-moustapha-gestion-comptes-api.onrender.com

L5_SWAGGER_CONST_HOST=https://sayande-moustapha-gestion-comptes-api.onrender.com

DATABASE_URL=postgresql://...
```

## 🧪 Tests post-déploiement

### 1. Vérifier que les clés OAuth existent

```bash
# Dans le conteneur
ls -la storage/oauth-*.key
```

### 2. Tester l'authentification

```bash
curl -X POST "https://votre-api.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"admin123","code":"000000"}'
```

Si vous obtenez un `access_token`, l'authentification OAuth fonctionne ! ✅

### 3. Tester Swagger

Accédez à : `https://votre-api.com/api/documentation`

## 🔧 Dépannage

### Erreur : "The encryption keys are missing"

**Cause :** Les clés OAuth n'ont pas été générées.

**Solution :**

```bash
# Dans le conteneur
php artisan passport:keys --force
```

### Erreur : "Permission denied" sur les clés

**Cause :** Permissions incorrectes sur le dossier `storage/`

**Solution :**

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### Les clés sont régénérées à chaque déploiement

**Comportement normal** : Les clés sont générées à chaque build Docker.

**Impact :** Les anciens tokens deviennent invalides après un redéploiement.

**Solution (optionnelle)** : Utiliser des volumes Docker pour persister les clés entre les déploiements.

## 📋 Checklist de déploiement

-   [ ] ✅ Fichier `.env.production` configuré
-   [ ] ✅ Variables d'environnement sur Render.com
-   [ ] ✅ Build Docker réussi
-   [ ] ✅ Clés OAuth générées automatiquement
-   [ ] ✅ Migrations exécutées
-   [ ] ✅ Seeders exécutés (AdminTestSeeder)
-   [ ] ✅ Documentation Swagger générée
-   [ ] ✅ Test d'authentification réussi
-   [ ] ✅ Swagger accessible

## 🎯 Résultat attendu

Après le déploiement :

1. L'API est accessible sur l'URL de production
2. Swagger UI est disponible à `/api/documentation`
3. L'authentification fonctionne avec OAuth2 + JWT
4. Les cookies HTTP-only sont correctement configurés
5. Les tokens peuvent être rafraîchis

**L'authentification est maintenant prête pour la production ! 🎉**
