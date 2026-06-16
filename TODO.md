# TODO

## Phase 1 — Data model & API
- [ ] Update `config/db.php` to add `posts.tesda_category` column (with ALTER TABLE fallback)
- [ ] Implement `api/tesda_list_by_category.php` (or update existing API) to return posts filtered by `tesda_category`

## Phase 2 — Admin UI
- [ ] Update `admin/dashboard.php` (TESDA Assessment/Training) to include a `tesda_category` dropdown
- [ ] Update `admin/save.php` to store `tesda_category` when saving TESDA posts

## Phase 3 — Frontend UI
- [ ] Redesign `tesda.html` cards to be category-only (UTPRAS / Assessment Certification & Accreditation / Community-Based Training)
- [ ] Remove static NC II / qualifications counts and fixed program labels
- [ ] Implement “View” modal on `tesda.html` to fetch & show admin-posted programs for that category

## Phase 4 — Verification
- [ ] Seed check: existing TESDA seed content may need category defaults (optional)
- [ ] Manual test in browser:
  - [ ] Load `tesda.html` and confirm only category cards appear
  - [ ] Click View on each card and confirm modal shows only posts for that category
  - [ ] Add a new TESDA post in admin with category and verify it appears

