YOURUNI COMPLETE PROJECT — INSTALLATION AND ADMIN GUIDE
=====================================================

THIS PACKAGE USES ONE DATABASE ONLY: youruni_db
------------------------------------------------
Import ONLY `youruni_complete.sql` for a clean installation. Do not also import the old
`universities.sql` or `youruni_db.sql` files from previous versions.

IMPORTANT — BACK UP FIRST
-------------------------
The clean-install SQL intentionally drops and recreates project tables inside `youruni_db`.
Before importing it, export your current `youruni_db` and `universities` databases in
phpMyAdmin and make a separate copy of your current project folder. Data edited only in
your running local database is not automatically merged into this package.

FRESH XAMPP INSTALL
-------------------
1. Extract this ZIP to a temporary folder.
2. Put the extracted `test` folder at `C:\xampp\htdocs\test` (or use your chosen folder
   name). Keep the included `photos` and `assets` folders intact.
3. Start Apache and MySQL in XAMPP.
4. After backing up your existing databases, rename or remove the old `youruni_db` and
   `universities` databases if you want a clean replacement. The new project uses one DB.
5. In phpMyAdmin, choose Import with no database selected and import `youruni_complete.sql`.
   The SQL creates `youruni_db`, its tables, the catalogue data, category cards, initial
   engineering specializations, and starter program-availability records.
6. Open `http://localhost/test/setup_admin.php` and create your own admin username and
   unique password of at least 12 characters. There is no shared default password.
7. Sign in at `http://localhost/test/admin_login.php`.
8. Test the homepage, university directory and search, comparison page, Opportunities,
   About Us, Donate Us page, theme/language controls, admin add/edit/delete, and logout.

DATABASE CONFIGURATION
----------------------
`config.php` uses local development defaults: host=localhost, port=3306, database=youruni_db,
user=root, blank password. For public hosting, configure YOURUNI_DB_HOST, YOURUNI_DB_PORT,
YOURUNI_DB_NAME, YOURUNI_DB_USER, and YOURUNI_DB_PASS with the credentials from the host.
Never publish production credentials. Keep `.htaccess` on Apache hosting; it disables directory
listing and blocks direct downloads of SQL/README/config files.

ADMIN FEATURES
--------------
- Homepage Featured: manage cards on `index.php` (stored in `courses`).
- University Catalogue: manage the catalogue used by `Universities.php` and Smart Search (`uni`).
- Opportunities: manage the main category cards (the `opportunities` table).
- Majors & Specializations: add, edit, publish/hide, reorder, and delete specialization entries
  under an Opportunities category. The first engineering starter entries were migrated from
  the existing Engineering page so they can be managed without editing HTML.
- University mappings: associate a specialization with a university and set Offered,
  Not offered, or Unverified, including source URL, verification date, and notes. Confirmed
  statuses require a source URL in the dashboard. Mappings are in `specialization_universities`
  and are separate from whether a specialization is published.
- Optional Burmese fields: each specialization can store a Burmese title, description, and context.
  If a Burmese version is blank, the English source remains as a fallback unless the shared
  translation dictionary already has a translation for that text.
- Admin accounts / password: use Manage admins and Change password from the dashboard.

ADDING A SPECIALIZATION
-----------------------
1. Open Admin Dashboard > Majors & Specializations.
2. Fill in category, English specialization name/description, optional Burmese title/description,
   optional context in either language, icon/badge and order; check Published to show it on the
   category detail page.
3. Save the specialization.
4. In Link a university to a specialization, choose the specialization and university.
5. Choose Unverified unless you have reliable official evidence. Add the official source URL,
   date, and notes. Use Offered or Not offered only when the source confirms that status.
6. Open the matching Opportunities category page and refresh. The new entry is retrieved from
   the database; you don't need to hand-edit an HTML file for the specialization card.

The starter `Precision Engineering` record is published under Engineering & Tech and is linked
to UTYCC as UNVERIFIED, as requested for the example. It must not be treated as a confirmed
UTYCC program until you add a reliable official source and update its mapping status.

PROGRAM AVAILABILITY VS ADMISSION
---------------------------------
Program availability means whether an institution offers a particular field. It does not mean
that admissions are currently open or that a student meets the admission requirements.
Minimum marks of 0 are treated as unknown unless a verified pass-based status is set; zero does
not mean automatic admission. The search presents unclear records separately. Verify all current
admissions information with official institutions before relying on it.

SPECIALTY FILTERS
-----------------
The category detail pages can load published specializations from `specializations_api.php`.
When a visitor clicks a specialization card, `programs_api.php` uses `specialization_universities`
first. Confirmed offerings are highlighted, unverified associations receive a separate pending
style, and confirmed non-offerings are dimmed. Existing static starter mappings remain for legacy
career cards where there is no matching database mapping. These starter mappings are not an
official verified prospectus database; verify them with official sources before public release.

DONATIONS
---------
`donate.php` is information-only until an authorized local payment QR is configured. No fake QR
is included and the page does not collect payment PINs/passwords or claim a transfer succeeded.

SECURITY AND PRODUCTION
-----------------------
- Admin accounts use password_hash() and require first-run setup. On a public host, configure
  YOURUNI_SETUP_KEY in the hosting environment before first-admin setup, then disable or remove
  `setup_admin.php` once your first admin account is created.
- Use HTTPS, a non-root database account, a strong unique production DB password, and regular
  backups before deploying publicly.
- All administrator accounts have full admin permissions; only create accounts for trusted people.

TESTING LIMITATION
------------------
PHP/JavaScript syntax and file references are checked offline, but this project has not been run
against your local MySQL database or tested in an actual mobile browser from this environment.
Test the installation and key workflows in XAMPP before replacing your only working copy.
University program mappings and admission criteria still need verification from official sources.
