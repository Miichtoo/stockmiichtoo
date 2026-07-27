# Stock RP — PHP + MySQL

Petit panneau de gestion de ressources inspiré de la capture fournie.

## Fonctions

- Ajouter une ressource
- Rechercher une ressource
- Augmenter ou diminuer le stock
- Modifier le nom, l'icône et la quantité
- Supprimer une ressource
- Données enregistrées dans MySQL
- Interface sombre et responsive

## Installation avec XAMPP / WAMP

1. Copie le dossier `stock-rp` dans le dossier web :
   - XAMPP : `htdocs/stock-rp`
   - WAMP : `www/stock-rp`
2. Ouvre phpMyAdmin.
3. Importe le fichier `database.sql`.
4. Modifie `config.php` si ton utilisateur ou mot de passe MySQL est différent.
5. Ouvre `http://localhost/stock-rp/`.

## Configuration SQL

Par défaut :

- hôte : `127.0.0.1`
- port : `3306`
- base : `stock_rp`
- utilisateur : `root`
- mot de passe : vide

## Sécurité

Le bouton **Admin** est visuel dans cette version. Pour une mise en ligne publique, ajoute une vraie authentification par session, un mot de passe hashé avec `password_hash()`, un contrôle CSRF et des permissions.
