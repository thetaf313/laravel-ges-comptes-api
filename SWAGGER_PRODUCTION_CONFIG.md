# 🌐 Configuration Swagger pour Production

## 🎯 Problème résolu

**Problème initial :** Swagger affichait `http://my-default-host.com` au lieu de l'URL correcte.

**Cause :** La variable `L5_SWAGGER_CONST_HOST` n'était pas définie dans les variables d'environnement.

## 🔧 Solution implémentée

### 1. **Configuration des variables d'environnement**

#### Local (`.env`)

```env
APP_URL=http://localhost:8000
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

#### Production (`.env.production`)

```env
APP_URL=https://sayande-moustapha-gestion-comptes-api.onrender.com
L5_SWAGGER_CONST_HOST=https://sayande-moustapha-gestion-comptes-api.onrender.com
```

### 2. **Localisation du problème**

Le problème se trouve dans plusieurs fichiers :

#### `config/l5-swagger.php` (ligne ~160)

```php
'constants' => [
    'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
],
```

#### `app/Http/Controllers/Controller.php` (ligne ~22)

```php
/**
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 */
```

## 🚀 Déploiement en production

### Étapes de déploiement :

1. **Copier la configuration de production**

    ```bash
    cp .env.production .env
    ```

2. **Optimiser la configuration**

    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

3. **Générer la documentation Swagger**

    ```bash
    php artisan l5-swagger:generate
    ```

4. **Migrations et données de test**
    ```bash
    php artisan migrate --force
    php artisan db:seed --class=AdminTestSeeder
    ```

### Test automatique :

```bash
./test-production-swagger.sh
```

## 🧪 URLs de test

### Local

-   **API :** http://localhost:8000
-   **Swagger :** http://localhost:8000/api/documentation

### Production

-   **API :** https://sayande-moustapha-gestion-comptes-api.onrender.com
-   **Swagger :** https://sayande-moustapha-gestion-comptes-api.onrender.com/api/documentation

## 🔐 Test d'authentification

### Credentials de test

```json
{
    "email": "admin@test.com",
    "password": "admin123",
    "code": "000000"
}
```

### Workflow Swagger

1. Ouvrir Swagger UI
2. Tester `/api/v1/auth/login` avec les credentials
3. Copier le `access_token` de la réponse
4. Cliquer sur **Authorize** 🔒
5. Entrer : `Bearer {access_token}`
6. Tester les endpoints protégés

## 🎛️ Configuration avancée

### Sécurité en production

```env
# Cookies sécurisés
SESSION_SECURE_COOKIES=true
SANCTUM_STATEFUL_DOMAINS=sayande-moustapha-gestion-comptes-api.onrender.com
SESSION_DOMAIN=.onrender.com

# Désactiver le debug
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

### Support multi-environnement

La configuration s'adapte automatiquement selon l'environnement :

-   **Local :** `http://localhost:8000`
-   **Staging :** `https://staging.example.com`
-   **Production :** `https://sayande-moustapha-gestion-comptes-api.onrender.com`

## 🚨 Points importants

### Régénération obligatoire

Après chaque modification de `L5_SWAGGER_CONST_HOST` :

```bash
php artisan l5-swagger:generate
```

### Cache de configuration

En production, n'oubliez pas de vider le cache :

```bash
php artisan config:clear
php artisan config:cache
```

### Vérification

Pour vérifier que la bonne URL est utilisée :

```bash
curl -s https://sayande-moustapha-gestion-comptes-api.onrender.com/api/documentation | grep -o 'https://[^"]*'
```

## ✅ Résultat

✅ **Local :** Swagger fonctionne sur `http://localhost:8000`  
✅ **Production :** Swagger fonctionne sur `https://sayande-moustapha-gestion-comptes-api.onrender.com`  
✅ **Authentification :** Bearer tokens compatibles avec Swagger UI  
✅ **Cookies :** HTTP-only cookies pour les navigateurs  
✅ **Tests :** Scripts automatisés pour validation

L'API est maintenant **entièrement testable via Swagger** en local et en production ! 🎯
