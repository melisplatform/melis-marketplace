---
title: MelisMarketPlace module
package: melisplatform/melis-marketplace
doc_type: module-documentation
audience: [users, developers, ai]
language: en
module_version: unversioned
last_reviewed: 2026-06-08
maintainer: Melis Technology
keywords: [marketplace, market place, modules, packages, packagist, update, download, install, remove, composer, plug, unplug, site product, melis, core, foundation]
screenshots_dir: ./images
---

# MelisMarketPlace — Functional & Technical Documentation (for AI)

> **What this is.** MelisMarketPlace is the back-office **module store**: it lists every Melis
> Platform module published on the **Melis Packagist** server, shows whether each installed module
> is **up to date**, and lets you **download, update or remove** modules — and **install site
> products** — without leaving the back-office. It drives [MelisComposerDeploy](../../../melis-composerdeploy/etc/MelisAI/doc/MelisComposerDeploy.md)
> under the hood to do the actual Composer work, and reads module versions through
> [MelisAssetManager](../../../melis-asset-manager/etc/MelisAI/doc/MelisAssetManager.md).
>
> **Two parts:** **[Part A — Functional Guide](#part-a--functional-guide)** ·
> **[Part B — Technical Reference](#part-b--technical-reference)** (developers/AI, with examples).
> Consumed by the **MelisAI** MCP. Reviewed 2026-06-08.

---

## 0. Where MelisMarketPlace sits (the MelisCore foundation)

> MelisMarketPlace is a **core-category** back-office tool that sits on top of the platform
> foundation — collectively referred to as **"MelisCore"**. It is the **user-facing front-end** to
> the deploy machinery: where MelisComposerDeploy *runs* Composer and MelisDbDeploy *applies*
> migrations, MarketPlace is the **store UI** that decides *what* to install/update and calls them.

- **MelisCore** — back-office foundation (login, rights, tools framework, events, base services).
  MarketPlace requires it. → [MelisCore doc](../../../melis-core/etc/MelisAI/doc/MelisCore.md)
- **MelisAssetManager** — module discovery & versions. MarketPlace reads installed versions and
  toggles the active-module list through `MelisAssetManagerModulesService`.
  → [MelisAssetManager doc](../../../melis-asset-manager/etc/MelisAI/doc/MelisAssetManager.md)
- **MelisComposerDeploy** — runs Composer from inside the platform. MarketPlace calls
  `MelisComposerService::download / update / remove`. → [MelisComposerDeploy doc](../../../melis-composerdeploy/etc/MelisAI/doc/MelisComposerDeploy.md)
- **MelisDbDeploy** — applies a freshly installed module's database deltas (MarketPlace exposes an
  `execDbDeploy` step after download). → [MelisDbDeploy doc](../../../melis-dbdeploy/etc/MelisAI/doc/MelisDbDeploy.md)
- **MelisInstaller** — the first-run wizard; uses the same composer/dbdeploy tools at install time.
  → [MelisInstaller doc](../../../melis-installer/etc/MelisAI/doc/MelisInstaller.md)

**Protected modules.** The six foundation modules — **MelisCore, MelisEngine, MelisFront,
MelisAssetManager, MelisComposerDeploy, MelisDbDeploy** — are listed as **exceptions**: MarketPlace
will not let you download or remove them, and they are excluded from the update-checker. They are
managed by Composer at the project level, never from the store.

---
---

# PART A — Functional Guide

## A1. What MelisMarketPlace is for

MelisMarketPlace is the **app store for the platform**. Open it and you get a catalogue of all
official Melis modules (and any published as Melis packages), grouped and searchable. For each
module you can see whether the version you have installed is **up to date**, and act on it directly:

- **Download** a module you don't have yet (Composer fetches it, then it's plugged in).
- **Update** a module to the latest published version.
- **Remove** a module you no longer need (with a dependency-safety check first).
- **Plug / unplug** a module — turn an installed module on or off without deleting its files.
- **Install a site product** — a special "site" package that scaffolds a whole website (CMS pages,
  templates, database tables) in one go.

Everything happens from the back-office; you never touch a terminal.

## A2. Where to find it

- **Left menu:** the **Market Place** entry (shopping-cart icon, `fa fa-shopping-cart`).
- **Header icon:** a small store/download icon in the top bar that lights up with a **badge count**
  when one or more installed modules have a newer version available. Click it for the shortlist of
  what needs updating, then jump into the store to update.

## A3. Reading a module's status

Each listed module shows one of three states, computed by comparing your **installed** version
against the **latest published** version:

| Badge | Meaning |
|---|---|
| **Up to date** | Installed version is the latest (or newer). Nothing to do. |
| **Needs update** | A newer version exists on the server — an **Update** button appears, and the header badge counts it. |
| **In advance** | You're running a **dev** build (a `dev-…` version) — ahead of the published release; left as-is. |

## A4. How do I…?

**…install a new module?**
Open **Market Place**, find the module (browse the groups or use the search box), open it and click
**Download**. Composer pulls the package and its dependencies; if the module ships a database delta
it is applied; the module is then plugged in and ready.

**…update a module that's out of date?**
Either click the **header icon** (its badge tells you how many are behind) and update from the
shortlist, or open the module in the store and click **Update**.

**…remove a module?**
Open the module and choose **Remove**. MarketPlace first checks that **no other active module still
depends on it** (and that it isn't required by your project's root `composer.json`); only then does
it deactivate and uninstall it. The six foundation modules can never be removed here.

**…temporarily turn a module off without deleting it?**
Use **unplug** (and **plug** to turn it back on). This rewrites the active-module list — the files
stay on disk, the module just stops loading.

**…install a full website in one click?**
Some packages are **site products**: choosing one runs the **setup form** (name, domain, scheme),
then scaffolds the site — pages, templates and the module's CMS tables — and registers it as a new
site on the platform.

**…why is the store empty or greyed out?**
MarketPlace needs to reach the **Melis Packagist** server, and your platform must be allowed to
update (a per-platform flag). If the server is unreachable or your platform is locked from updating,
the store shows a "not accessible" message and the action buttons are hidden — browsing still works
but downloads/updates are disabled. See B6.

> **Screenshots** (recommended, to capture, under `./images/`): the store landing page with the
> module groups and search, a single module's product view (with Download/Update/Remove), the
> header update-badge dropdown, and the site-product setup form. None are captured for the AI doc
> yet — the images under `etc/MarketPlace/` are the public promo shots, not this doc's screenshots.

---
---

# PART B — Technical Reference

## B1. Metadata & dependencies

| Item | Value |
|---|---|
| Package | `melisplatform/melis-marketplace` · type `melisplatform-module` · category `core` |
| Namespace | `MelisMarketPlace\` (PSR-4 → `src/`) · module name `MelisMarketPlace` |
| Requires | `melisplatform/melis-core: ^5.1` · PHP `^8.1\|^8.3` · `dbdeploy: true` |
| Runtime deps (services) | `MelisComposerService` (composer-deploy), `MelisAssetManagerModulesService` (asset-manager), `MelisConfig`, `MelisCoreTablePlatform` |
| Asset alias | `MelisMarketPlace/` → `public/` (slick carousel, axios, `melis-market-place.js`, build bundle) |

## B2. Routes

```php
// config/module.config.php
'application-MelisMarketPlace'  // /melis/MelisMarketPlace[/:controller[/:action]]
    → controller 'MelisMarketPlace', default action 'toolContainer'
'setup-melis-marketplace'       // /MelisMarketPlace[/:controller[/:action]]  and  /MelisMarketPlace/setup
    → controller 'MelisSetup', action 'setup-form'   // standalone module setup outside the BO tree
```

Controllers (invokables): `MelisMarketPlace\Controller\MelisMarketPlace` →
`MelisMarketPlaceController`, and `MelisMarketPlace\Controller\MelisSetup` → `MelisSetupController`.

## B3. Back-office wiring (`config/app.interface.php`)

MarketPlace plugs into the standard MelisCore interface tree by `melisKey`:

- **Left menu:** `melismarketplace_toolstree_section` → opens the tool whose display node is
  `melis_market_place_tool_display` (`forward` → `MelisMarketPlace/tool-container`, with
  `jscallback: 'fetchPackages();'`). Icon `fa-shopping-cart`, label `tr_market_place`.
- **Header icon:** `market_place_header_icon` → `market_place_header_conf`
  (`forward` → `MelisMarketPlace/market-place-module-header`) renders the update badge.
- **Single product view:** `melis_market_place_tool_package_display`
  (`forward` → `tool-container-product-view`).
- **Modals:** `melis_market_place_tool_package_modal_container` →
  `…_modal_content` (`tool-product-modal-content`) and `melis_market_place_module_setup_form_content`
  (`tool-module-form-setup-content`) — the per-module post-download/update setup form.

Two values live under `melismarketplace_toolstree_section/datas`:

```php
'melis_packagist_server' => 'http://marketplace.melisplatform.com/melis-packagist',
'exceptions' => ['MelisCore','MelisEngine','MelisFront','MelisAssetManager','MelisComposerDeploy','MelisDbDeploy'],
```

`app.tools.php` is intentionally empty — MarketPlace is **not** a DataTable tool; its catalogue is
an AJAX/JS UI (axios + slick) that reads the Packagist JSON endpoints.

## B4. The Packagist server contract

The controller talks to the configured `melis_packagist_server` over plain HTTP `GET`:

| Endpoint | Used by | Returns |
|---|---|---|
| `/get-most-downloaded-packages` | `toolContainerAction` | landing-page package list |
| `/get-package-group` | `toolContainerAction` | group/category metadata |
| `/get-packages/page/{n}/search/…/status/2/group/` | `marketPlaceModuleHeaderAction` | latest versions for update-check |

The server is the official **Melis Packagist** mirror that indexes every Composer package of type
`melisplatform-module` / `melisplatform-site`. All version comparison is done locally afterwards.

## B5. Services (with examples)

### `MelisMarketPlaceService` — versioning, plug/unplug, post-setup forms

Extends `MelisGeneralService`. Key constants and methods:

```php
$mp = $sm->get('MelisMarketPlaceService');

// Compare installed vs latest → one of the status constants.
$status = $mp->compareLocalVersionFromRepo('MelisCmsSlider', 'v5.1.3');
// MelisMarketPlaceService::NEED_UPDATE (-1) | ::UP_TO_DATE (1) | ::IN_ADVANCE (2, a "dev-" build)

// Turn an installed module on/off (rewrites the active-module loader via asset-manager).
$mp->plugModule('MelisCmsSlider');     // loadModule(...)
$mp->unplugModule('MelisCmsSlider');   // unloadModule(...)

// Does this module want to show a setup form after download/update?
$needsForm = $mp->hasPostSetup('MyModule', MelisMarketPlaceService::ACTION_DOWNLOAD);
```

`compareLocalVersionFromRepo()` fires the trio events
`melismarketplace_compare_local_version_from_repo_start` / `…_end` (subscribe to override the
computed status).

**Per-module post-setup convention.** A module can ship a `MelisSetupPostDownloadController` and/or
`MelisSetupPostUpdateController` (in its `…\Controller\` namespace) exposing a public property
`showOnMarketplacePostSetup = true` and the actions `getFormAction`, `validateFormAction`,
`submitAction`. When present, MarketPlace forwards into them to render and process the setup form in
a modal after the package is downloaded/updated:

```php
$mp->getForm('MyModule');               // renders MyModule\Controller\MelisSetupPostDownload::getFormAction
$mp->validateForm('MyModule', $post);   // → validateFormAction, returns JsonModel vars
$mp->submitForm('MyModule', $post);     // → submitAction
```

### `MelisMarketPlaceSiteService` — installing a "site product"

Extends `MelisGeneralService`. Scaffolds an entire website from a `melisplatform-site` package:
creates the `melis_cms_site` / home / langs rows, allocates a fresh **page id**, **platform id** and
**template id** ranges, and creates the module's CMS tables (`Support\MelisMarketPlaceCmsTables`,
`Support\MelisMarketPlaceSiteInstall`).

```php
$site = $sm->get('MelisMarketPlaceSiteService');
$site->marketplaceInstallSite($request);   // reads POST: name, scheme, domain, module, action
```

Guards (thrown as typed exceptions in `src/Exception/`):
`EmptySiteException` (missing site data), `PlatformIdMaxRangeReachedException` /
`TemplateIdMaxRangeReachedException` (no free id range left), `ArrayKeyNotFoundException`,
`FileNotFoundException`.

## B6. Access gating

Two checks decide whether the store is usable (both in `MelisMarketPlaceController`):

```php
$this->isMarketplaceAccessible();   // can we reach the Packagist server / is the feature on?
$this->allowUpdate();               // platform flag: melis_core_platform.plf_update_marketplace
```

`allowUpdate()` reads the current platform row (`MelisCoreTablePlatform` keyed on
`getenv('MELIS_PLATFORM')`) — a platform with `plf_update_marketplace = 0` can browse but not
download/update. When `isMarketplaceAccessible()` is false the view sets `marketNotAccessible = true`
and hides the action UI.

## B7. The download / update / remove flow

`melisMarketPlaceProductDoAction()` is the single endpoint that performs catalogue actions. It
reads `action`, `package`, `module` from POST, fires
`melis_marketplace_product_do_start`, then switches on the `MelisComposerService` constants:

```php
$composer = $sm->get('MelisComposerService');
switch ($action) {
    case $composer::DOWNLOAD:  // skip the 6 protected modules, else $composer->download($package)
    case $composer::UPDATE:    // $composer->download($package)
    case $composer::REMOVE:    // dependency-safety check, rewrite module.load, $composer->remove($package)
}
// fires 'melis_marketplace_product_do_finish' → flash-messenger feedback
```

**Remove safety.** Before removing, the controller walks the target module's dependencies and the
other *active* modules' dependencies, plus the project root `composer.json` `require` block, so a
shared dependency is never deactivated out from under another module. Then it rebuilds
`config/module.load` via `MelisAssetManagerModulesService::createModuleLoader()` and calls
`$composer->remove()`. After a download, `execDbDeployAction()` applies the new module's DB deltas.

## B8. Controller action map

`MelisMarketPlaceController` (catalogue UI + actions):

```
toolContainer / toolContainerProductView      → store landing & single-product views
moduleList / bundleList                        → installed-module & bundle listings
marketPlaceModuleHeader                        → header update-badge (counts NEED_UPDATE modules)
marketPlaceDashboard                           → dashboard widget
melisMarketPlaceProductDo                      → DOWNLOAD / UPDATE / REMOVE entry point (B7)
execDbDeploy / reDumpAutoload / executeComposerScripts → post-download deploy steps
plugModule / unplugModule / isModuleActive / activateModule → enable/disable installed modules
getSetupModuleForm / validateSetupForm / submitSetupForm   → per-module post-setup form
toolProductModalContainer / toolProductModalContent / toolModuleFormSetupContent → modals
getModuleTables / exportTables                 → inspect / export a module's CMS tables
isPackageDirectoryRemovable / changePackageDirectoryPermission → filesystem pre-checks for remove
isModuleExists / isMarketplaceAccessible       → AJAX guards
siteInstall                                    → install a site product (delegates to SiteService)
```

`MelisSetupController` handles the **standalone** `/MelisMarketPlace/setup` route (setting up a
single module outside the BO interface tree).

## B9. Events

| Event | Fired by | Purpose |
|---|---|---|
| `melismarketplace_compare_local_version_from_repo_start` / `_end` | `compareLocalVersionFromRepo` | hook/override version-status computation |
| `melis_marketplace_product_do_start` | `melisMarketPlaceProductDo` | before a download/update/remove |
| `melis_marketplace_product_do_finish` | `melisMarketPlaceProductDo` | after — drives flash-messenger feedback |

## B10. Quick code map

```
melis-marketplace/
├── composer.json                 → melisplatform-module, category core, requires melis-core ^5.1
├── config/
│   ├── module.config.php         → routes (tool + standalone setup), services, controllers, assets
│   ├── app.interface.php         → left-menu, header icon, product view, modals + packagist datas/exceptions
│   ├── app.forms.php             → search & setup forms
│   └── app.tools.php             → empty (not a DataTable tool)
├── src/
│   ├── Controller/  MelisMarketPlaceController · MelisSetupController
│   ├── Service/     MelisMarketPlaceService (versions/plug/post-setup) · MelisMarketPlaceSiteService (site install)
│   ├── Support/     MelisMarketPlace · MelisMarketPlaceCmsTables · MelisMarketPlaceSiteInstall
│   ├── Exception/   Empty/PlatformIdMaxRange/TemplateIdMaxRange/ArrayKeyNotFound/FileNotFound
│   └── Listener/    MelisMarketPlaceTestListener
├── view/ · public/ (slick, axios, melis-market-place.js, build bundle) · language/
└── etc/  MarketPlace (promo) + MelisAI/doc (this doc)
```

---

*Document for AI consumption (MelisAI MCP) — `melisplatform/melis-marketplace`. Part A = functional;
Part B = technical with examples. The store front-end to the MelisCore deploy foundation. Last
reviewed 2026-06-08.*
