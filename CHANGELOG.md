# Changelog

## v6.0.6 - 2026-09-23
### Security
* **security:** declare the tool key on this module's controllers (audit item 7.0)

## v6.0.5 - 2026-08-20
### Fixed
* **marketplace-react:** code icon on the New view toggle
### Docs
* **melisai:** React back-office AI documentation for MelisMarketPlace

## v6.0.4 - 2026-08-14
### Fixed
* **marketplace:** legacy tool shows the same real latest version as the React view
* **marketplace:** match installed modules case-insensitively (Melis Marketplace showed as not installed)
* **marketplace:** hide version and download count for private packages
* **marketplace:** drop modules removed from packagist + show full detail image
* **marketplace:** list shows the first cover image + the real latest version
* **marketplace:** React view shows the React screenshots (not the old ones)

## v6.0.2 - 2026-08-10
### Dependencies & build
* **composer:** update docs/homepage links, swap zf2 keyword for laminas, bump php constraint to ^8.3|^8.5

## v6.0.1 - 2026-08-10
* Maintenance release.

## v6.0.0 - 2026-08-10
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
* Fix audit findings
* Added user rights
### Added
* **marketplace:** add React back-office screenshots (etc/MarketPlace/images/react)
* **marketplace:** responsive mobile de la brique React
* **marketplace:** listing polish + locale fix
* **marketplace:** legacy group logo on tiles, full-width detail, persist brick across tab switches
* **marketplace:** group logo per tile, 3-per-row grid, keep list mounted on back
* **react:** add a "Reset filters" button to the tool list page(s)
* Add React brick for the Market Place tool (hybrid list + legacy iframe)
* **MelisAI:** add MelisMarketPlace AI documentation
### Fixed
* Fix search functionality
### Changed
* Continue detail page
* Market Place: show sidebar on the package detail page
* Market Place brick: native list, detail & install/update/remove flow
* Drop deprecated curl_close() call (PHP 8.5)
### Dependencies & build
* **deps:** require melisplatform/melis-core ^6.0
### Docs
* **MelisAI:** wire MelisMarketPlace screenshots into doc

## v5.3.5 - 2025-07-09
### Changed
* Update site creation for dynamic dnd

## v5.3.4 - 2025-02-12
### Changed
* Updated module svg icons
* Edits related to jquery migration
### Dependencies & build
* Rebuilt the asset bundle

## v5.3.3 - 2024-09-26
* Maintenance release.

## v5.3.2 - 2024-09-25
### Changed
* IsMarketplaceAccessible return issue fixed

## v5.3.1 - 2024-09-25
### Changed
* IsMarketplaceAccessible action added

## v5.3.0 - 2024-09-25
### Added
* Updated logic
### Fixed
* Fix issue related to jquery migration
* Fix platform crash when market place is down
* Fix deprecation
### Changed
* Edit on melis-market-place.js

## v5.2.0 - 2024-06-06
* Maintenance release.

## v5.1.2 - 2024-05-16
### Fixed
* Fixed substr deprecated problem

## v5.1.1 - 2024-02-13
### Changed
* Remove temporary branch set when developing

## v5.1.0 - 2024-02-13
### Fixed
* Fixed deprecated problem when downloading sites
### Changed
* Temp change branch when downloading module

## v5.0.0 - 2022-06-22
### Added
* Added php8 as one of the required php versions
### Changed
* Removed session_write_close func
* Escape only if not subject is not array
### Dependencies & build
* Updated function that were affected with php8 upgrade
