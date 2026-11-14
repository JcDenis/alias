# alias

Notes for AI assistants working on the Dotclear plugin alias.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Create aliases of your blog's URLs.

Admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Alias.php`: main class.
* `src/AliasRow.php`: decriptor class.
* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/Install.php`: install and update, database structure (a change needs a migration here).
* `src/Manage.php`: admin page of the module.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/PluginImportExportBehaviors.php`: behaviors for the plugin ImportExport.
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `js/`: `dragndrop.js`.
* Database table `alias`.
* Behaviors used: `urlHandlerGetArgsDocument`, `exportFullV2`, `exportSingleV2`, `importInitV2`, `importFullV2`, `importSingleV2`.

## Pitfalls

* Table structure changes need a migration in `src/Install.php` (version compare), and must work on MySQL, PostgreSQL and SQLite.

## Testing

* Check the admin page, the admin help, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
