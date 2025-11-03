# 🌐 Configuration des serveurs Swagger

## Problème résolu

En production, le champ "Select servers" de Swagger affichait uniquement `http://localhost:8000`, rendant impossible de tester l'API directement sur le serveur de production.

## Solution

Nous avons ajouté plusieurs serveurs dans la documentation Swagger pour permettre de basculer facilement entre les environnements.

### Configuration actuelle

Dans `app/Http/Controllers/Controller.php` :

```php
/**
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Serveur de développement local"
 * )
 *
 * @OA\Server(
 *      url="https://sayande-moustapha-gestion-comptes-api.onrender.com",
 *      description="Serveur de production (Render)"
 * )
 */
```

### Résultat

Dans Swagger UI, le dropdown "Servers" affiche maintenant :
- 🖥️ **Serveur de développement local** - http://localhost:8000
- 🚀 **Serveur de production (Render)** - https://sayande-moustapha-gestion-comptes-api.onrender.com

## Ajouter un nouvel environnement

Pour ajouter un serveur staging, ajoutez cette annotation dans `Controller.php` :

```php
/**
 * @OA\Server(
 *      url="https://staging-api.example.com",
 *      description="Serveur de staging"
 * )
 */
```

Puis régénérez la documentation :
```bash
php artisan l5-swagger:generate
```

## Utilisation

1. **Ouvrez Swagger UI** : `https://votre-api.com/api/documentation`
2. **Cliquez sur le dropdown "Servers"** en haut de la page
3. **Sélectionnez l'environnement** souhaité
4. **Testez vos endpoints** sur l'environnement sélectionné

## Avantages

✅ **Basculement facile** entre les environnements  
✅ **Tests en production** directement depuis Swagger  
✅ **Documentation claire** des différents environnements  
✅ **Pas besoin de déployer** pour tester localement  

## Notes importantes

- Les cookies HTTP-only fonctionnent correctement avec tous les serveurs
- Le serveur sélectionné est sauvegardé dans le localStorage du navigateur
- Chaque serveur peut avoir sa propre configuration (CORS, etc.)

## Alternative : Configuration dynamique

Si vous voulez utiliser des variables d'environnement plutôt que des URLs hardcodées, vous pouvez créer une route qui génère dynamiquement la spec OpenAPI en fonction de `APP_URL`.

Cependant, l'approche actuelle (URLs hardcodées) est plus simple et fonctionne parfaitement pour la plupart des cas d'usage.
