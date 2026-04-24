# Site Ecomm

Boutique PHP de bougies avec catalogue, panier, livraison et confirmation de commande.

## Structure

- `config/` : configuration locale et exemple de connexion a la base de donnees
- `css/` : feuille de style principale
- `database/` : schema SQL pour creer les tables
- `image/` : images des bougies et favicon
- `page/` : pages du site, actions POST et fichiers partages
- `index.php` : redirection vers la page d'accueil

## Lecture rapide pour debutant

- `page/includes/site.php` : coeur du projet. On y trouve les fonctions PHP reutilisees partout.
- `page/includes/header.php` et `page/includes/footer.php` : morceaux HTML communs a toutes les pages.
- `page/index.php` : page d'accueil.
- `page/catalogue-bougies.php` : liste des bougies avec filtres.
- `page/produit-bougie.php` : fiche detail d'une bougie.
- `page/panier.php` : panier avec modification des quantites.
- `page/livraison.php` : formulaire pour l'adresse de livraison.
- `page/confirmation-commande.php` : resume final apres la commande.
- `page/actions/` : petits scripts qui traitent les formulaires ou les requetes AJAX.

## Comment lire le projet

1. Commencer par `page/includes/site.php` pour comprendre les fonctions de base.
2. Lire ensuite `page/index.php` puis `page/catalogue-bougies.php`.
3. Continuer avec `page/panier.php` et `page/actions/`.
4. Finir avec `page/livraison.php` et `page/confirmation-commande.php`.

## Notes

- `config/database.php` est ignore par Git.
- `config/database.example.php` sert de modele pour configurer la base en local.
- Le panier et les messages temporaires sont stockes dans la session PHP.
