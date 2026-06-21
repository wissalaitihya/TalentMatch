## 1. Candidate Ranking on Offer Detail Page

- [x] 1.1 Update OfferController show method: eager load candidats with analyse, order completed scored analyses first by matching_score DESC, then unscored
- [x] 1.2 Update offer show Blade view to display ranked candidates with status badge, score, recommendation color-coding, justification, and link to detail/chat
- [x] 1.3 Add a "Comparer" button/link from offer detail to the comparison page

## 2. Comparison Route and Controller

- [x] 2.1 Add GET and POST routes for `/offres/{offre}/comparaison` in routes/web.php
- [x] 2.2 Create ComparisonRequest FormRequest with validation: same offre, both candidates exist, both analyses completed, ownership check
- [x] 2.3 Create ComparisonController with index() (show form) and compare() (process and display results)
- [x] 2.4 Create comparison Blade view with candidate selection form and side-by-side results display

## 3. Update compareCandidates Assistant Tool

- [x] 3.1 Update CompareCandidates tool to enforce same-offre, ownership, and completed status checks
- [x] 3.2 Return safe error messages for invalid comparison scenarios (different offers, incomplete analysis, missing candidate, unauthorized)

## 4. Tests

- [x] 4.1 Test that offer show ranks completed scored candidates by score descending
- [x] 4.2 Test that candidates without score appear after scored candidates
- [x] 4.3 Test guest cannot access comparison page
- [x] 4.4 Test user cannot compare candidates from another user's offer
- [x] 4.5 Test user cannot compare candidates from different offers
- [x] 4.6 Test user can compare two completed analyses from their own offer
- [x] 4.7 Test comparison page displays both candidates' saved data
- [x] 4.8 Test compareCandidates tool returns saved comparison data
- [x] 4.9 Test compareCandidates tool refuses unsafe comparisons
- [x] 4.10 Test no real AI call is made during comparison
