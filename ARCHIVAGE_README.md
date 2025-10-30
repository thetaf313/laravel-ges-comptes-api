# Commandes disponibles pour l'archivage :
php artisan comptes:archive-expired-blocked          # Dispatch en queue
php artisan comptes:archive-expired-blocked --sync    # Exécution immédiate

# Commandes disponibles pour le désarchivage :
php artisan comptes:unarchive-expired-blocked         # Dispatch en queue  
php artisan comptes:unarchive-expired-blocked --sync  # Exécution immédiate

# Planification automatique :
# - Archivage : Tous les jours à 02:00
# - Désarchivage : Tous les jours à 02:30
