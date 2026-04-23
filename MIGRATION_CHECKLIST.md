# EduSync MVC Migration Checklist

This checklist tracks the move from the legacy PHP entry files to the MVC app under `app/`.

## Foundation

- [x] Front controller exists in `index.php`
- [x] Router exists in `app/Core/Router.php`
- [x] Base controller exists in `app/Core/Controller.php`
- [x] Session handling exists in `app/Core/Session.php`
- [x] Added `app/Controllers/PageController.php` so the fallback `/:page` route no longer points to a missing class
- [x] Added `app/Views/dashboard/analytics.php` so `/analytics` has a matching MVC view

## Legacy Page Mapping

| Legacy file | MVC route | MVC status | Notes |
| --- | --- | --- | --- |
| `pages/login.php` | `/login` | Done | Served by `AuthController@login` |
| `pages/signup.php` | `/signup` | Done | Served by `AuthController@signup` |
| `pages/logout.php` | `/logout` | Done | Served by `AuthController@logout` |
| `pages/dashboard.php` | `/dashboard` | Done | Served by `DashboardController@index` |
| `pages/analytics.php` | `/analytics` | Partial | Route and view exist; old study-log form is not migrated yet |
| `pages/subjects.php` | `/subjects` | Done | Served by `DashboardController@subjects` |
| `pages/tasks.php` | `/tasks` | Done | Served by `DashboardController@tasks` |
| `pages/grades.php` | `/grades` | Done | Served by `DashboardController@grades` |
| `pages/attendance.php` | `/attendance` | Done | Served by `DashboardController@attendance` |
| `pages/calendar.php` | `/calendar` | Done | Served by `DashboardController@calendar` |
| `pages/announcements.php` | `/announcements` | Done | Served by `AnnouncementController@index` |
| `pages/fees.php` | `/fees` | Done | Served by `FeeController@index` |
| `pages/flashcards.php` | `/flashcards` | Done | Served by `FlashcardController@index` |
| `pages/groups.php` | `/groups` | Done | Served by `GroupController@index` |
| `pages/jobs.php` | `/jobs` | Done | Served by `JobController@index` |
| `pages/partners.php` | `/partners` | Done | Served by `JobController@partners` |
| `pages/learn.php` | `/learn` | Done | Served by `LearnController@index` |
| `pages/ai.php` | `/ai` | Done | Served by `AiController@index` |
| `pages/playground.php` | `/playground` | Done | Served by `AiController@playground` |
| `pages/suggestions.php` | `/suggestions` | Done | Served by `AiController@suggestions` |
| `pages/question-bank.php` | `/question-bank` | Done | Served by `QuestionBankController@index` with DB-backed listing and sample fallback |
| `pages/submit-question.php` | `/question-bank/submit` | Done | Served by `QuestionBankController@submit` |
| `pages/question-detail.php` | `/question-bank/:id` | Done | Served by `QuestionBankController@detail` with MVC JSON actions for bookmark, upvote, and compact answer |
| `pages/prospectus.php` | `/prospectus` | Done | Served by `MiscController@prospectus` |
| `pages/result-lookup.php` | `/result-lookup` | Done | Served by `MiscController@resultLookup` |

## Legacy Admin Mapping

| Legacy file | MVC route | MVC status | Notes |
| --- | --- | --- | --- |
| `admin/index.php` | `/admin` | Done | Served by `AdminController@index` |
| `admin/manage-attendance.php` | `/admin/attendance` | Done | Served by `AdminController@manageAttendance` |
| `admin/api-settings.php` | `/admin/api-settings` | Done | Served by `AdminController@apiSettings` |
| `admin/ajax/ai-suggest.php` | `/api/ai/suggest` | Done | Covered by `AiController@suggest` |

## Legacy AJAX Mapping

| Legacy file | MVC route | MVC status | Notes |
| --- | --- | --- | --- |
| `ajax/ai-suggest.php` | `/api/ai/suggest` | Done | Moved into `AiController@suggest` |
| `ajax/ai-compact.php` | `/api/question-bank/compact-answer` | Done | Moved into `QuestionBankController@compactAnswer` |
| `ajax/bookmark.php` | `/api/question-bank/bookmark` | Done | Moved into `QuestionBankController@bookmark` |
| `ajax/mu-result.php` | `/api/result-lookup` | Done | Moved into `MiscController@lookupOfficialResult` |
| `ajax/run-python.php` | `/api/playground/run-python` | Done | Moved into `AiController@runPython` and wired to the MVC playground |
| `ajax/run-sql.php` | None | Missing | Needs an MVC JSON action with strong auth/admin checks |
| `ajax/upvote.php` | `/api/question-bank/upvote` | Done | Moved into `QuestionBankController@upvote` |

## Structure Cleanup

- [ ] Move SQL still living in controllers into model methods
- [ ] Replace remaining direct links to `pages/*.php`, `admin/*.php`, and `ajax/*.php`
- [ ] Decide whether to keep or remove the catch-all `/:page` route once all links are clean
- [ ] Block direct access to legacy PHP entry files after MVC replacements are verified
- [ ] Remove unused legacy folders once feature parity is confirmed

## Suggested Next Steps

1. Convert the remaining `ajax/run-sql.php` endpoint into a controller action with strict auth/admin protection.
2. Move direct SQL out of `DashboardController` and `AdminController` into models to complete MVC separation.
3. Replace remaining legacy links and JS calls, then disable direct access to the old `pages/`, `admin/`, and `ajax/` files.
4. Decide whether the catch-all `/:page` route is still needed after all direct legacy links are removed.
