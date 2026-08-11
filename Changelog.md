# Version 4.0.0 (2026-08-10)

- [FEATURE] TYPO3 v14 compatibility
- [BREAKING] Raise dependencies to `typo3/cms-core: ^14.3` and `derhansen/sf_event_mgt: ^9.0`
- [BREAKING] `Classes/Event/BaseEvent.php`: `$request`/`getRequest()` now use `TYPO3\CMS\Extbase\Mvc\RequestInterface` instead of the concrete `TYPO3\CMS\Extbase\Mvc\Request`, matching `ActionController::$request`'s own type in current TYPO3 core. The actual object dispatched at runtime is unchanged (still a real `Request` instance) - only the declared type widened. Third-party PSR-14 listeners on `CreateActionBeforeSaveEvent`/`CreateActionAfterPersistEvent`/`UpdateActionBeforeSaveEvent`/`DeleteActionBeforeDeleteEvent` that type-hint `getRequest()`'s return value as the concrete `Request` (e.g. to call PSR-7-only methods like `getAttribute()`/`getUri()`) will need to widen their own type-hint to `RequestInterface`.
- [BREAKING] `deleteAction` now only accepts POST requests; a GET request is rejected and redirected back to the list. <br>**Any sitepackage overriding this template must do the same** - a GET-based delete link will now silently redirect back to the list instead of deleting the event
- [FEATURE] Introduce Site Set configuration (`Configuration/Sets/MdEventmgtFrontend/`) with classic TypoScript constants kept as a fallback, including Site Settings for the template/partial/layout root paths
- [FEATURE] Add a Unit and Functional test suite (PHPUnit + `typo3/testing-framework`), covering ownership/access-control for the edit/update/delete actions
- [TASK] Rename all Fluid templates/layouts/partials in `Resources/Private/` from `*.html` to the new `*.fluid.html` extension introduced by Fluid 5 (TYPO3 v14)
- [TASK] Resolve TYPO3 v14.3 deprecations: `ext_emconf.php` metadata (#108345), `ext_tables.php` (#109438), TCA `internal_type` (#96983)
- [TASK] Add PHPStan, Rector, Fractor and PHP-CS-Fixer tooling and fix all findings surfaced by them

All changes
https://github.com/cdaecke/md_eventmgt_frontend/compare/3.0.1...4.0.0>

# Version 3.0.1 (2026-07-30)

- [BUGFIX] check access correctly

All changes
<https://github.com/cdaecke/md_eventmgt_frontend/compare/3.0.0...3.0.1>

# Version 3.0.0 (2026-01-07)

- [FEATURE] TYPO3 v13 compatibility
- [BREAKING] Migrate `list_type` plugins to `CType` and resolve core deprecation #105076. Use upgrade wizard `EXT:md_eventmgt_frontend: Migrate plugins` after upgrading!

All changes
<https://github.com/cdaecke/md_eventmgt_frontend/compare/2.0.1...3.0.0>

# Version 2.0.1 (2024-08-22)

- [FEATURE] Add controller request in events

All changes
<https://github.com/cdaecke/md_eventmgt_frontend/compare/2.0.0...2.0.1>

# Version 2.0.0 (2024-07-15)

- [FEATURE] TYPO3 v12 compatibility
- [BREAKING] Remove `addQueryStringMethod` from links. This was used in the paginator.

All changes
<https://github.com/cdaecke/md_eventmgt_frontend/compare/1.0.0...2.0.0>

# Version 1.0.0 (2022-03-08)

Stable release of version 1.0.0

- [TASK] add some more date to views and add settings to psr-14 events
- [TASK] flush cache, if event was edited

All changes
<https://github.com/cdaecke/md_eventmgt_frontend/compare/0.0.1...1.0.0>

# Version 0.0.1 (2022-01-18)

Initial release of beta version
