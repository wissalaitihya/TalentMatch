## Why

Les agents RH ont besoin de gérer leurs offres d'emploi directement dans TalentMatch — créer, lister, voir, modifier et supprimer des offres. Actuellement, le système n'a pas de module de gestion d'offres, ce qui bloque le flux de recrutement AI (l'analyse CV et le scoring ont besoin d'offres pour fonctionner).

## What Changes

- **Nouveau modèle `Offre`** avec titre, description, competences_requises, experience_minimum, user_id
- **CRUD complet** via 5 routes REST authentifiées (`GET/POST/PUT/DELETE /offres`)
- **StoreOffreRequest & UpdateOffreRequest** — validation avec règles métier
- **Relation avec candidats** : l'index affiche le count de candidats (withCount)
- **Scoping par utilisateur** : un agent RH ne voit que ses propres offres

## Capabilities

### New Capabilities
- `offres-crud`: Gestion complète des offres d'emploi (CRUD) pour agents RH authentifiés

### Modified Capabilities

<!-- No existing capabilities are modified -->

## Impact

- **Nouveau modèle Eloquent** : `Offre` avec migration, factory, seeder
- **Nouveau controller** : `OffreController` (thin, utilise un FormRequest)
- **Nouvelles routes** API REST dans `routes/web.php` (auth requise)
- **Nouveaux FormRequests** : `StoreOffreRequest`, `UpdateOffreRequest`
- **Tests** : Feature tests pour chaque endpoint
- Aucun impact sur les specs existantes
