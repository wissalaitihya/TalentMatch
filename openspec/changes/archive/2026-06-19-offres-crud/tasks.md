## 1. Modèle et base de données

- [x] 1.1 Créer la migration `create_offres_table` avec titre, description, competences_requises (json), experience_minimum, user_id (FK)
- [x] 1.2 Créer le modèle `Offre` avec fillable, casts (array pour competences_requises), relation `user` et `candidatures`
- [x] 1.3 Créer la factory `OffreFactory` avec les champs par défaut
- [x] 1.4 Créer le seeder `OffreSeeder` en utilisant la factory

## 2. Form Requests

- [x] 2.1 Créer `StoreOffreRequest` avec règles de validation : titre required|max:255, description required, competences_requises required|array|min:1, experience_minimum required|integer|min:0
- [x] 2.2 Créer `UpdateOffreRequest` avec mêmes règles + méthode `authorize()` qui vérifie que l'offre appartient à auth()->id()

## 3. Contrôleur et routes

- [x] 3.1 Créer `OffreController` avec index, store, show, update, destroy
- [x] 3.2 Implémenter `index` : scope par auth()->id() + withCount('candidatures')
- [x] 3.3 Implémenter `store` : créer une offre avec auth()->id()
- [x] 3.4 Implémenter `show` : trouver l'offre avec scope utilisateur + withCount
- [x] 3.5 Implémenter `update` : utiliser UpdateOffreRequest, scoper et mettre à jour
- [x] 3.6 Implémenter `destroy` : scoper et supprimer
- [x] 3.7 Ajouter les routes dans `routes/web.php` : group auth + resource partielle

## 4. Vues (Blade)

- [x] 4.1 Créer `views/offres/index.blade.php` — liste des offres du RH avec count candidats
- [x] 4.2 Créer `views/offres/show.blade.php` — détail d'une offre
- [x] 4.3 Créer `views/offres/create.blade.php` — formulaire de création
- [x] 4.4 Créer `views/offres/edit.blade.php` — formulaire de modification
- [x] 4.5 Ajouter les liens de navigation vers /offres dans le layout

## 5. Tests

- [x] 5.1 Feature test : lister ses propres offres (ignorer celles des autres)
- [x] 5.2 Feature test : créer une offre valide
- [x] 5.3 Feature test : échec création avec competences_requises vide
- [x] 5.4 Feature test : voir le détail d'une offre (autorisé / non autorisé)
- [x] 5.5 Feature test : modifier une offre (succès / non autorisé)
- [x] 5.6 Feature test : supprimer une offre (succès / non autorisé)
- [x] 5.7 Feature test : utilisateur non connecté redirigé vers login
