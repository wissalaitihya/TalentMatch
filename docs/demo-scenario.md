# Scénario de démonstration — TalentMatch AI

Ce document décrit un parcours utilisateur complet, étape par étape, pour tester l'ensemble des fonctionnalités de TalentMatch.

---

## Prérequis

- L'application est installée et accessible (`php artisan serve`)
- Le worker de queue tourne (`php artisan queue:work`)
- Les migrations ont été exécutées (`php artisan migrate`)

---

## Étape 1 : Inscription

1. Ouvrir `http://localhost:8000/register`
2. Remplir :
   - **Nom** : `Jean Dupont`
   - **Email** : `jean@example.com`
   - **Mot de passe** : `password` (min. 8 caractères)
   - **Confirmer le mot de passe** : `password`
3. Cliquer sur **S'inscrire**
4. ✅ Redirigé vers le **Dashboard** avec le message "You're logged in!"

---

## Étape 2 : Créer une offre d'emploi

1. Dans le menu, cliquer sur **Mes offres**
2. Cliquer sur **Créer une offre**
3. Remplir le formulaire :

   | Champ | Valeur |
   |---|---|
   | Titre | Développeur Full Stack Laravel |
   | Description | Nous recherchons un développeur Laravel expérimenté pour rejoindre notre équipe. Vous serez en charge du développement backend et frontend. |
   | Compétences requises (une par ligne) | Laravel<br>PHP<br>JavaScript<br>Vue.js<br>MySQL<br>Git |
   | Expérience minimum (années) | 3 |

4. Cliquer sur **Créer**
5. ✅ Redirigé vers la liste des offres avec un message de succès
6. ✅ L'offre apparaît dans le tableau avec le titre, l'expérience min et "0" candidat

---

## Étape 3 : Soumettre un CV

1. Cliquer sur le titre de l'offre pour voir le détail
2. Dans la section **Soumettre un CV**, remplir :

   | Champ | Valeur |
   |---|---|
   | Nom du candidat | Marie Martin |
   | Texte du CV | Marie Martin est une développeuse full stack avec 5 ans d'expérience. Elle maîtrise Laravel, PHP, JavaScript, Vue.js, MySQL, Git, Docker et AWS. Elle est titulaire d'un Master en Informatique. Langues : français (natif), anglais (courant). |

3. Cliquer sur **Soumettre le CV**
4. ✅ Le candidat apparaît dans la section **Candidats soumis** avec le statut "En attente" (jaune)

---

## Étape 4 : Voir l'analyse

1. Attendre quelques secondes que le worker de queue traite l'analyse
2. Recharger la page — le statut passe à **Terminé** (vert)
3. Cliquer sur le nom du candidat pour voir l'analyse complète
4. ✅ La page affiche :
   - **Statut** : Terminé
   - **Score** : 85% (ou selon l'analyse réelle)
   - **Recommandation** : À convoquer (vert)
   - **Expérience** : 5 ans
   - **Niveau d'études** : Master en Informatique
   - **Compétences extraites** : Laravel, PHP, JavaScript, Vue.js, MySQL, Git, Docker, AWS
   - **Langues** : français, anglais
   - **Points forts** : plusieurs atouts listés
   - **Lacunes** : éventuelles lacunes identifiées
   - **Compétences manquantes** : éventuelles compétences requises manquantes
   - **Justification** : explication détaillée du score

---

## Étape 5 : Discuter avec l'assistant

1. Depuis la page d'analyse, faire défiler jusqu'à la section **Assistant RH**
2. Saisir une question dans le champ "Posez une question sur ce candidat..."
3. Cliquer sur **Envoyer**
4. Exemples de questions :
   - « Quelles sont les compétences de ce candidat ? »
   - « Quels sont ses points forts ? »
   - « Quelle est sa recommandation ? »
   - « Pourquoi ce score ? »
   - « Quelles compétences lui manquent ? »
5. ✅ L'assistant répond en utilisant les données réelles de l'analyse (il ne fabrique pas d'informations)

---

## Étape 6 : Ajouter un second candidat

1. Revenir sur le détail de l'offre (lien "Retour à l'offre")
2. Soumettre un second CV :

   | Champ | Valeur |
   |---|---|
   | Nom du candidat | Pierre Durand |
   | Texte du CV | Pierre Durand est un développeur backend avec 2 ans d'expérience. Il connaît PHP et MySQL mais n'a pas d'expérience avec Laravel ou Vue.js. Il a un Bachelor en Informatique. Langues : français (natif). |

3. Attendre le traitement par la queue
4. ✅ Le second candidat apparaît avec son analyse terminée

---

## Étape 7 : Comparer des candidats

1. Depuis le détail de l'offre, cliquer sur **Comparer des candidats** (le bouton apparaît quand au moins 2 analyses sont terminées)
2. Sélectionner **Marie Martin** comme premier candidat et **Pierre Durand** comme second
3. Cliquer sur **Comparer**
4. ✅ La page affiche les deux candidats côte à côte avec :
   - Score (avec code couleur : vert ≥ 70, orange 40–69, rouge < 40)
   - Recommandation
   - Expérience
   - Niveau d'études
   - Compétences extraites
   - Points forts
   - Lacunes
   - Compétences manquantes
   - Justification
5. ✅ Une **Conclusion** comparative est affichée sous les deux colonnes

---

## Résumé des fonctionnalités testées

| Étape | Fonctionnalité |
|---|---|
| 1 | Inscription / Auth (Breeze) |
| 2 | CRUD Offres (création) |
| 3 | Soumission de CV |
| 4 | Analyse IA (queue + structured output) |
| 5 | Assistant conversationnel (tools + mémoire) |
| 6 | Second candidat |
| 7 | Comparaison de candidats |
