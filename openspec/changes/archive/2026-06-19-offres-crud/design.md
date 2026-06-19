## Context

TalentMatch gère des offres d'emploi via l'IA conversationnelle, mais n'a pas encore de CRUD standard pour les agents RH. Le modèle `Offre` sera le pivot entre les agents RH et l'analyse CV. Actuellement, aucune table `offres` n'existe.

## Goals / Non-Goals

**Goals:**
- Modèle `Offre` avec migration, factory, seeder
- 5 endpoints REST authentifiés (GET/POST/PUT/DELETE)
- Scoping automatique par `user_id` (auth()->id())
- `withCount('candidatures')` sur l'index pour éviter N+1
- FormRequests dédiés avec validation des compétences

**Non-Goals:**
- Scoring IA (couche séparée)
- Pagination (hors scope)
- Tags / catégories
- Publication publique

## Decisions

1. **Controller unique vs séparé** → `OffreController` unique avec 5 méthodes (pas de API Resource, car l'UI est interne et blade-first). Rationale : pas assez complexe pour justifier un contrôleur API.
2. **FormRequest unique par action** → `StoreOffreRequest` et `UpdateOffreRequest` distincts. `UpdateOffreRequest` vérifie que l'offre appartient à l'utilisateur connecté via `authorize()`.
3. **Competences_requises cast array** → cast natif Laravel `array` sur le modèle. Stockage JSON en MySQL.
4. **Routes web** (pas api.php) → l'outil est interne, pas d'API publique. Middleware `auth` appliqué via route group.

## Risks / Trade-offs

- Cast array = pas de validation au niveau DB (mais le FormRequest valide avant). Mitigation : validation stricte dans le FormRequest (min:1 item, array).
- Pas de soft deletes → suppression physique simple. Si besoin futur, migration additionnelle.
- Routes web sans inertia → utilisation de `@if(session)` pour flash messages. Acceptable pour un outil interne.
