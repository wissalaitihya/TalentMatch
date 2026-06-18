# Analyse IA

## User Stories

- US5 Submit CV
- US6 Structured analysis
- US7 View analysis
- US8 Recommendation

## Requirements

User submits:

- candidate name
- CV text

System:

- extracts skills
- extracts experience
- calculates score
- generates recommendation

## Output Contract

{
  "competences_extraites": [],
  "annees_experience": 0,
  "niveau_etudes": "",
  "langues": [],
  "matching_score": 0,
  "points_forts": [],
  "lacunes": [],
  "competences_manquantes": [],
  "recommandation": "",
  "justification": ""
}

## Constraints

Score between 0 and 100

Recommendation:

- convoquer
- attente
- rejeter

Analysis runs in queue

## Acceptance Criteria

Analysis stored in database
Recommendation visible