# 📋 Instructions de déploiement Swagger Production

## 🎯 Résumé du problème résolu

**Problème :** Swagger affichait `http://my-default-host.com` au lieu de l'URL correcte  
**Solution :** Configuration de `L5_SWAGGER_CONST_HOST` dans les variables d'environnement

## 🚀 Déploiement Production

### 1. **Copier la configuration**

```bash
# Sur le serveur de production
cp .env.production .env
```

### 2. **Optimiser et générer**

```bash
php artisan config:cache
php artisan l5-swagger:generate
```

### 3. **Test automatique**

```bash
./test-production-swagger.sh
```

## 🔍 Vérification

### URLs attendues :

-   **Production API :** https://sayande-moustapha-gestion-comptes-api.onrender.com
-   **Production Swagger :** https://sayande-moustapha-gestion-comptes-api.onrender.com/api/documentation

### Test d'authentification :

```json
POST /api/v1/auth/login
{
  "email": "admin@test.com",
  "password": "admin123",
  "code": "000000"
}
```

## ✅ Configuration finale

### Local (`.env`)

```env
L5_SWAGGER_CONST_HOST=http://localhost:8000
```

### Production (`.env.production`)

```env
L5_SWAGGER_CONST_HOST=https://sayande-moustapha-gestion-comptes-api.onrender.com
```

**Prêt pour les tests Swagger en production ! 🎯**
