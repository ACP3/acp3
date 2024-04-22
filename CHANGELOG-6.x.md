# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased]

### Fixed

-   [Filemanager] Fix a faulty DB migration file

## [6.28.0] - 2024-03-16

### Changed

-   [All] Updated the Doctrine DBAL to version 4.x

## [6.27.0] - 2024-03-15

### Changed

-   [All] Updated the NPM libraries
-   [All] Updated the Composer libraries

### Fixed

-   [Core] Fixed up the "Do not log some types of exceptions, when the ACP3 is in production mode"-solution from the previous release

## [6.26.3] - 2024-03-13

### Changed

-   [Core] Do not log some types of exceptions, when the ACP3 is in production mode
-   [Contact] Use the `replyTo`-field for storing the contact form senders e-mail address

## [6.26.2] - 2024-01-06

### Fixed

-   [Gallery] Fixed a fatal error, when the Gallerycomments module isn't present

## [6.26.1] - 2024-01-06

-   No user visible changes

## [6.26.0] - 2024-01-06

### Added

-   [Base] Added a PHP 8.3 based docker image
-   [Base] Integrated Node.js into the PHP docker image.

### Changed

-   [Base] Slightly optimized the `copy-assets` `gulp`-task
-   [Base] Removed Code Climate

## [6.25.1] - 2024-01-03

### Fixed

-   [Tests] Fixed executing php-cs-fixer with a custom config

## [6.25.0] - 2024-01-03

### Changed

-   [Base] Replaced `sass` with `sass-embedded` to improve the `*.scss`-file transpilation performance
-   [Base] Replace `babel` with `esbuild` to improve the `.(m)js`-files translation performance
-   [Base] Optimized the pre-commit-hook
-   [Base] [Tests] Moved all the code quality tool dependencies into the `acp3/tests`-package
-   [Base] [Tests] Moved all `composer`-scripts into the `acp3/tests`-package, to make them more reusable
    -   To make use of it, please enable the `merge-scripts`-option within your project's root-`composer.json`!

## [6.24.2] - 2024-01-02

### Fixed

-   [System] Fixed the missing animation of the floating label form groups

## [6.24.1] - 2024-01-02

-   No user-visible changes

## [6.24.0] - 2024-01-02

### Added

-   [System] Added a new layout variant: `layout.simple.tpl`
-   [System] Added basic support for floating labels to the various form-group templates

### Changed

-   [ALL] Replaced all `.btn-outline-secondary`-selectors with `.btn-light`
-   [Users] Improved the visuals of the login page

## [6.23.0] - 2024-01-02

### Added

-   [Articles] [Categories] [Contact] [Files] [Gallery] [News] Add links to the corresponding listing and detail pages to allow easier content editing

### Changed

-   [Acp] Improved the visuals of the Admin panel homepage

## [6.22.3] - 2024-01-02

### Fixed

-   [System] Fixed the form submition

## [6.22.2] - 2024-01-02

### Fixed

-   [System] Fixed the duplicate modal instances when inserting page breaks

## [6.22.1] - 2023-12-31

### Fixed

-   [Base] Re-enable the rewrite module for the Apache webserver

## [6.22.0] - 2023-12-31

### Added

-   [Core] Added a plugin system for registering lazy foreign keys between modules based on route-paths

### Changed

-   [Base] Updated the docker-compose environment

### Fixed

-   [Core] Correctly reset the `Validator`'s state after validating a form
-   [Core] Prevent the `nl2p`-method from modifying the supplied content, if it contains HTML
-   [Emoticons] Prevent the emoticons `ContentDecorator`-method from modifying the supplied content, if it contains HTML

### Deprecations

-   The following have been deprecated, i.e. they are not needed for ACP3 versions >= 6.22.0 anymore:
    -   acp3/module-articles-menus
    -   acp3/module-articles-share
    -   acp3/module-files-share
    -   acp3/module-gallery-share
    -   acp3/module-news-share

## [6.21.0] - 2023-11-12

### Added

-   [Core] Made it possible to combine multiple `package.json`-files into a single one, i.e. each ACP3 module can bring their own set of dependencies. Use `console.php acp3:components:merge-package-json` for that.
-   [System] Made it possible to give tabs "names"

### Fixed

-   [Base] Fixed webpack when in watch mode
-   [Core] Fixed a possible infinite redirect loop, when trying to access a protected page, but the current user isn't authenticated

## [6.20.2] - 2023-10-22

### Fixed

-   [Core] Fixed building the JS assets, when the ACP3-component is residing within the vendor dir
-   [Theme-Default] Fixed exporting the SCSS-files
-   [Theme-Installer] Fixed exporting the SCSS-files

## [6.20.1] - 2023-10-15

### Changed

-   [WYSIWYGCKEditor] Constrain the CKEditor version to 4.22.x for now, as versions >= 4.23.x require a paid license

## [6.20.0] - 2023-09-09

### Changed

-   [ALL] Applied various minor UI/UX improvements to the dropdown handling

## [6.19.0] - 2023-09-03

### Added

-   [Menus] Made menu item headlines usable as sub-menu entrypoints

### Changed

-   [Menus] Exclude menu item headlines from the breadcrumbs

### Fixed

-   [Menus] Fix showing/hiding the "link target" form field when toggling between making a menu item visible or not

## [6.18.1] - 2023-06-10

### Added

-   [Core] Added a "trim" Smarty-modifier to fix deprecation warnings

### Fixed

-   [Gallery] Fixed adding new gallery images
-   [Menus] Fixed creating menu items

## [6.18.0] - 2023-06-10

### Changed

-   [ALL] Updated the Symfony components to version 6.3.0
-   [ALL] Updated the PHPUnit to version 10.2
-   [System] Updated bootstrap to version 5.3.0

## [6.17.1] - 2023-05-10

### Fixed

-   [System] Fixed the data-grid styling

## [6.17.0] - 2023-05-10

### Added

-   [Core] Allowed webpack to optimize/bundle some more JS-files
-   [Menus] Made it possible to add "headlines" as menu items

### Changed

-   [Core] Improved the discoverability of the data grid actions
-   [ALL] Updated some code which was deprecated by recent Doctrine DBAL updates

### Fixed

-   [Guestboot] [System] Fixed a JS error, when bootstrap's JS-file has been customized

## [6.16.0] - 2023-04-02

### Added

-   [Wysiwygckeditor] Added the missing h4-h6 format tags

### Changed

-   [ALL] Updated the 3rd party composer and NPM dependencies

## [6.15.1] - 2023-03-06

### Fixed

-   [Users] fix not being able to log in with the new password, when a user account has been locked by too many failed login-attempts

## [6.15.0] - 2023-02-26

### Changed

-   [Base] Bumped the minimally required Node.js version to 18.14
-   [Core] Display the 404 page, when an invalid controller action argument has been given in the request
-   [System] Updated the NPM dependencies

## [6.14.2] - 2023-02-22

### Changed

-   [ALL] Indent `*.mjs`-file with 2 spaces, too

### Fixed

-   [Core] Fix self-referencing canonical URIs

## [6.14.1] - 2023-02-04

### Fixed

-   [CI] Force to use Composer 2.4 in the CI pipeline for now, as running it with Composer 2.5 did break it

## [6.14.0] - 2023-02-03

### Added

-   [Core] Added official support for PHP 8.2
-   [Core] Added a `json_encode`-modifier for Smarty to fix a deprecation notice with Smarty version ^4.3.0

### Changed

-   [ALL] Fixed some PHPStan level 7 errors, but there is still some way to go before we can enable this level by default
-   [Core] ESMified the scripts related to gulp and webpack

### Fixed

-   [Core] Fixed not throwing the `AfterModelSaveEvent` by itself

## [6.13.0] - 2022-11-07

### Added

-   [Base] Add a gulp task for optimizing PNG-images
-   [Base] Add a gulp task for converting JPG-, GIF- and PNG-images to WEBP-images
-   [System] Add a new partial template which creates a `<picture`>-element with support for WEBP- and PNG-images

## [6.12.0] - 2022-11-06

### Added

-   [Base] Added `cssnano` to compress the CSS files a little better

## [6.11.2] - 2022-10-30

### Fixed

-   [Core] Fixed forwarding to other controller actions

## [6.11.1] - 2022-10-29

### Fixed

-   [Core] Fixed the area specific static asset delivery

## [6.11.0] - 2022-10-29

### Deprecations

-   [Core] Deprecated the `ControllerActionRequestEvent::NAME` constant, which will be removed with ACP3 version 7.0.0. Use `ControllerActionRequestEvent::class` instead.
-   [Core] Deprecated the `ModelSaveEvent`, which will be removed with ACP3 version 7.0.0. Use or subscribe to the `BeforeModelSaveEvent`, `AfterModelSaveEvent`, `BeforeModelDeleteEvent` or `AfterModelDeleteEvent` classes instead.

### Added

-   [Core] Added the possibility to deliver different static assets for the frontend, admin and when the user is logged in.

    Just prefix the corresponding library files with `admin-` or `logged-in-`, i.e. `admin-bootstrap.scss`, which will result (after the transpilation) to `admin-bootstrap.min.css`

-   [Core] Added the possibility to have and deliver special `admin.css` files and use it for the permission and system module

### Changed

-   [System] Reworked the update check, so that it performs the actual update check when the response has already been sent to improve the response times.

### Fixed

-   [Captcha] Fixed rendering the captchas

## [6.10.3] - 2022-10-27

### Fixed

-   [Core] Fixed the missing inclusion of the theme specific translation customizations - 2nd attempt...

## [6.10.2] - 2022-10-27

### Fixed

-   [Core] Fixed the `PageCssClasses` output filter, if there is already an existing `class` attribute at the `<body>`-tag
-   [Core] Fixed the missing inclusion of the theme specific translation customizations

## [6.10.1] - 2022-10-26

### Fixed

-   [CI] Fixed the CI-pipeline

## [6.10.0] - 2022-10-26

### Changed

-   [System] Updated the NPM- and Composer dependencies

### Fixed

-   [Articles] Fixed fetching the available layout files

## [6.9.0] - 2022-09-11

### Added

-   [Core] Added a CLI-command `acp3:cache:warmup` which allows warming up the caches.

    This CLI-task works around the shortcoming of the `acp3:http-cache:warmup`-command, i.e. it isn't doing any webserver based requests and therefore will not hurt any analytics metrics by cloudflare, etc.

    `acp3:http-cache:warmup` can/should still be used AFTER running `acp3:cache:warmup`

### Changed

-   [Filemanager] Include the `rich-filemanager` via the FileResolver
-   [Wysiwygckeditor] Include the `ckeditor.js` via the FileResolver

## [6.8.1] - 2022-09-01

### Fixed

-   [Base] [Installer] Fixed a bug with the static assets generation

## [6.8.0] - 2022-08-31

### Added

-   [Core] Added support for webp images
-   [Base] Handle the not-existing `.component-paths.json`-file gracefully, when trying to run the gulp tasks
-   [Articles] [Gallery] [System] Added a `pager.tpl` partial template and use it in the articles and gallery module

### Changed

-   [ALL] Unified the folder structure between ACP3 modules and themes. The new structure is `<my-theme>/<my-module>/Resources/{Assets,View}/**/*`
-   [ALL] Compiled static assets will now be located in `uploads/assets/`
-   [ALL] Replaced obsolete CSS selectors with Bootstrap utility classes
-   [ALL] Replaced browserify with webpack
-   [Core] Improved the cacheability of static assets
-   [System] Made the loading-indicator CSS deferrable
-   [theme-default] [theme-installer] force Bootstrap not to binf to jQuery's event system, even if jQuery is present
-   [WYSIWYGTinymce] version TinyMCE's assets within the `Resources/Assets/js/`-directory
-   [WYSIWYGCKEditor] version CKEditor's assets within the `Resources/Assets/js/`-directory

### Fixed

-   [Core] Do not render non-existent static assets anymore
-   [Emoticons] Fixed inserting emoticons

## [6.7.0] - 2022-08-23

### Changed

-   [Core] [System] Dropped the SVG sprites in favour of the separate SVG icons. The SVG icons will now be included directly, which saves network-bandwidth and -requests

## [6.6.0] - 2022-08-21

### Deprecations

-   [ALL] Deprecated the `set*`-methods in all `*FormValidation`-classes, which will be removed in ACP3 7.0.0

### Changed

-   [ALL] Made all `*FormValidation`-classes immutable via `with*`-methods
-   [Categories] [Menus] [Permissions] Use an SQL-only approach for determining the first and last items in a nested set

### Fixed

-   [Core] Fixed an error when trying to change the sort order of a nested set item
-   [Menus] Fixed the incorrectly displayed navbar when there is a hidden menu item with visible sub-items

## [6.5.1] - 2022-08-20

### Fixed

-   [Menus] Fixed the bug that the page-URI was missing after saving a menu item
-   [Menus] Fixed the bug that the parent-menu-item-form-field was mandatory

## [6.5.0] - 2022-08-20

### Added

-   [Files] made it possible to add a subtitle to downloads
-   [Files] enabled the full WYSIWYG editor when creating/editing a download
-   [Gallery] made it possible to add a subtitle to galleries
-   [Gallery] enabled the full WYSIWYG editor when creating/editing a gallery
-   [News] made it possible to add a subtitle to news

### Changed

-   [ALL] replaced some magic numbers with the newly introduced enums `YesNoEnum`, `LinkTargetEnum` and `NewsletterSendingStatusEnum`
-   [Files] "un-boxed" the files details page
-   [Files] increased the maximum length of the title to 255 characters
-   [News] "un-boxed" the news details page
-   [News] increased the maximum length of the title to 255 characters

### Fixed

-   [Core] Removed the `strict_types` declaration from the `ControllerActionDispatcher` to remedy some hard to fix possible `TypeError`s

## [6.4.0] - 2022-08-16

### Added

-   [System] [Gallery] Added photoswipe as a replacement for fancybox

### Changed

-   [Gallery] Removed the `overlay` setting from the gallery module

### Fixed

-   [User] Fixed the user login form

### Deprecated

-   [System] Deprecated fancybox, use photoswipe instead.

## [6.3.1] - 2022-08-15

### Fixed

-   [System] Add a missing space before the `$formGroupSelector`

## [6.3.0] - 2022-08-14

### Added

-   [System] Added some new form-group partials template and extended the existing ones with new functionality, to make them even more reusable

### Changed

-   [System] Display readonly and disabled form inputs as plaintext
-   [All] Changed the default breakpoints from "sm" (576px) to "md" (768px). The default form-breakpoint can now be configured with the variable `$formBreakpoint`.
-   [Core] Changed the repo-URI of the forked RichFileManager-NPM-package, so that it doesn't require Git to be present anymore
-   [Base] Remove the PHP-docker-container again, when it's finished linting and testing as part of the pre-commit-hook

### Fixed

-   [System] Fixed the missing alert, when page cache purging is set to "manual" and the page cache is not valid.
-   [All] Fixed various minor visual glitches

## [6.2.1] - 2022-08-08

### Fixed

-   [SEO] Fixed saving result sets, when the SEO module integration is active

## [6.2.0] - 2022-08-07

### Added

-   [Core] [SEO] Added the possibility to specify canonical URIs via the UI
-   [Core] Added the `MetaRobotsEnum`
-   [Menus] Added the `PageTypeEnum`
-   [Permissions] Added the `ProtectedRolesEnum`
-   [System] Added the `SiteSubtitleModeEnum`
-   [Users] Added the `GenderEnum`

### Changed

-   [Articles] [Files] [News] Moved the fulltext index creating into the corresponding ``*search`-modules
-   [Core] Removed the deprecated `StreamedResponseListener` as it is not needed anymore

### Fixed

-   [Articlessearch] [Filessearch] [Newssearch] Fixed missing fulltext indexes when only searching for `title` or `content`
-   [SEO] Fixed the missing `structured_data`-column after installing the ACP3
-   [System] Fixed the missing "extensions" and "maintenance" menu items in the user menu

## [6.1.5] - 2022-08-04

-   [Core] Fixed the `Concat*Renderer`-Strategies

## [6.1.4] - 2022-07-31

### Fixed

-   [Core] Fixed a minor bug in the breadcrumb generation

## [6.1.3] - 2022-07-31

### Fixed

-   [Core] Reverted an overly strict type annotation within the `Validator::is()`-method

## [6.1.2] - 2022-07-31

### Fixed

-   [Core] Fixed a bug within the `PictureValidationRule`

## [6.1.1] - 2022-07-31

### Changed

-   [Core] Improved a type annotation in the table-of-contents generator

## [6.1.0] - 2022-07-30

### Changed

-   [Core] Updated symfony components to version 6.1
-   [Core] Deprecated the `BaseEnum`-class
-   [Core] Converted the `PermissionEnum` into a native PHP-enum
-   [Core] Converted the `PrivilegeEnum` into a native PHP-enum
-   [Core] Converted the `AreaEnum` into a native PHP-enum
-   [Core] Converted the `PluginTypeEnum` into a native PHP-enum
-   [Core] Converted the `ApplicationModeEnum` into a native PHP-enum
-   [SEO] Converted the `IndexPaginatedContentEnum` into a native PHP-enum
-   [System] Updated Bootstrap to version 5.2.0

### Breaking

-   [Core] Dropped support for PHP 8.0.x. PHP 8.1.x. is required now.

## [6.0.4] - 2022-05-23

### Changed

-   [System] Update the NPM dependencies

### Fixed

-   [Core] Fix a possible PHP error, when providing an empty path to the `ControllerActionExists::controllerActionExists`-method

## [6.0.3] - 2022-05-16

### Fixed

-   [Core] Harden the `ExternalLinkValidationRule` against non-existing form values
-   [Categories] Harden the `CategoryExistsValidationRule` against non-existing form values

## [6.0.2] - 2022-05-15

### Fixed

-   [Emoticons] Harden the EmoticonService against non-existing files

## [6.0.1] - 2022-05-15

### Fixed

-   [Installer] Fixed updating existing installation from version 5.x to 6.0.0

## [6.0.0] - 2022-05-14

### BC breaks

-   [All] Dropped support for PHP versions < 8.0
-   [All] Reworked the DB-Migrations. Implement the new `ACP3\Core\Migration\MigrationInterface`
-   [All] Removed all deprecated code
-   [Core] Renamed `ACP3\Core\Controller\Context\WidgetContext` to `ACP3\Core\Controller\Context\Context`
-   [System] Updated Bootstrap to version 5.1.3
-   [System] Updated Fontawesome to version 6
-   [System] Dropped flatpickr. The system uses native HTML5 datetime-local inputs now

### Added

-   [All] Added many type declarations and made use of new language features coming with PHP version 7.4 and 8.0
-   [Base] Added official support for PHP 8.1

### Changed

-   [All] Reworked most of the JavaScript assets to not be dependent on jQuery anymore
-   [Base] Bumped PHPstan level from 5 to 6
-   [Permissions] Removed the `privilege_id` concept

### Fixed

-   [All] Fixed many bugs

[unreleased]: https://gitlab.com/ACP3/cms/compare/v6.28.0...6.x
[6.28.0]: https://gitlab.com/ACP3/cms/compare/v6.27.0...v6.28.0
[6.27.0]: https://gitlab.com/ACP3/cms/compare/v6.26.3...v6.27.0
[6.26.3]: https://gitlab.com/ACP3/cms/compare/v6.26.2...v6.26.3
[6.26.2]: https://gitlab.com/ACP3/cms/compare/v6.26.1...v6.26.2
[6.26.1]: https://gitlab.com/ACP3/cms/compare/v6.26.0...v6.26.1
[6.26.0]: https://gitlab.com/ACP3/cms/compare/v6.25.1...v6.26.0
[6.25.1]: https://gitlab.com/ACP3/cms/compare/v6.25.0...v6.25.1
[6.25.0]: https://gitlab.com/ACP3/cms/compare/v6.24.2...v6.25.0
[6.24.2]: https://gitlab.com/ACP3/cms/compare/v6.24.1...v6.24.2
[6.24.1]: https://gitlab.com/ACP3/cms/compare/v6.24.0...v6.24.1
[6.24.0]: https://gitlab.com/ACP3/cms/compare/v6.23.0...v6.24.0
[6.23.0]: https://gitlab.com/ACP3/cms/compare/v6.22.3...v6.23.0
[6.22.3]: https://gitlab.com/ACP3/cms/compare/v6.22.2...v6.22.3
[6.22.2]: https://gitlab.com/ACP3/cms/compare/v6.22.1...v6.22.2
[6.22.1]: https://gitlab.com/ACP3/cms/compare/v6.22.0...v6.22.1
[6.22.0]: https://gitlab.com/ACP3/cms/compare/v6.21.0...v6.22.0
[6.21.0]: https://gitlab.com/ACP3/cms/compare/v6.20.2...v6.21.0
[6.20.2]: https://gitlab.com/ACP3/cms/compare/v6.20.1...v6.20.2
[6.20.1]: https://gitlab.com/ACP3/cms/compare/v6.20.0...v6.20.1
[6.20.0]: https://gitlab.com/ACP3/cms/compare/v6.19.0...v6.20.0
[6.19.0]: https://gitlab.com/ACP3/cms/compare/v6.18.1...v6.19.0
[6.18.1]: https://gitlab.com/ACP3/cms/compare/v6.18.0...v6.18.1
[6.18.0]: https://gitlab.com/ACP3/cms/compare/v6.17.1...v6.18.0
[6.17.1]: https://gitlab.com/ACP3/cms/compare/v6.17.0...v6.17.1
[6.17.0]: https://gitlab.com/ACP3/cms/compare/v6.16.0...v6.17.0
[6.16.0]: https://gitlab.com/ACP3/cms/compare/v6.15.1...v6.16.0
[6.15.1]: https://gitlab.com/ACP3/cms/compare/v6.15.0...v6.15.1
[6.15.0]: https://gitlab.com/ACP3/cms/compare/v6.14.2...v6.15.0
[6.14.2]: https://gitlab.com/ACP3/cms/compare/v6.14.1...v6.14.2
[6.14.1]: https://gitlab.com/ACP3/cms/compare/v6.14.0...v6.14.1
[6.14.0]: https://gitlab.com/ACP3/cms/compare/v6.13.0...v6.14.0
[6.13.0]: https://gitlab.com/ACP3/cms/compare/v6.12.0...v6.13.0
[6.12.0]: https://gitlab.com/ACP3/cms/compare/v6.11.2...v6.12.0
[6.11.2]: https://gitlab.com/ACP3/cms/compare/v6.11.1...v6.11.2
[6.11.1]: https://gitlab.com/ACP3/cms/compare/v6.11.0...v6.11.1
[6.11.0]: https://gitlab.com/ACP3/cms/compare/v6.10.3...v6.11.0
[6.10.3]: https://gitlab.com/ACP3/cms/compare/v6.10.2...v6.10.3
[6.10.2]: https://gitlab.com/ACP3/cms/compare/v6.10.1...v6.10.2
[6.10.1]: https://gitlab.com/ACP3/cms/compare/v6.10.0...v6.10.1
[6.10.0]: https://gitlab.com/ACP3/cms/compare/v6.9.0...v6.10.0
[6.9.0]: https://gitlab.com/ACP3/cms/compare/v6.8.1...v6.9.0
[6.8.1]: https://gitlab.com/ACP3/cms/compare/v6.8.0...v6.8.1
[6.8.0]: https://gitlab.com/ACP3/cms/compare/v6.7.0...v6.8.0
[6.7.0]: https://gitlab.com/ACP3/cms/compare/v6.6.0...v6.7.0
[6.6.0]: https://gitlab.com/ACP3/cms/compare/v6.5.1...v6.6.0
[6.5.1]: https://gitlab.com/ACP3/cms/compare/v6.5.0...v6.5.1
[6.5.0]: https://gitlab.com/ACP3/cms/compare/v6.4.0...v6.5.0
[6.4.0]: https://gitlab.com/ACP3/cms/compare/v6.3.1...v6.4.0
[6.3.1]: https://gitlab.com/ACP3/cms/compare/v6.3.0...v6.3.1
[6.3.0]: https://gitlab.com/ACP3/cms/compare/v6.2.1...v6.3.0
[6.2.1]: https://gitlab.com/ACP3/cms/compare/v6.2.0...v6.2.1
[6.2.0]: https://gitlab.com/ACP3/cms/compare/v6.1.5...v6.2.0
[6.1.5]: https://gitlab.com/ACP3/cms/compare/v6.1.4...v6.1.5
[6.1.4]: https://gitlab.com/ACP3/cms/compare/v6.1.3...v6.1.4
[6.1.3]: https://gitlab.com/ACP3/cms/compare/v6.1.2...v6.1.3
[6.1.2]: https://gitlab.com/ACP3/cms/compare/v6.1.1...v6.1.2
[6.1.1]: https://gitlab.com/ACP3/cms/compare/v6.1.0...v6.1.1
[6.1.0]: https://gitlab.com/ACP3/cms/compare/v6.0.4...v6.1.0
[6.0.4]: https://gitlab.com/ACP3/cms/compare/v6.0.3...v6.0.4
[6.0.3]: https://gitlab.com/ACP3/cms/compare/v6.0.2...v6.0.3
[6.0.2]: https://gitlab.com/ACP3/cms/compare/v6.0.1...v6.0.2
[6.0.1]: https://gitlab.com/ACP3/cms/compare/v6.0.0...v6.0.1
[6.0.0]: https://gitlab.com/ACP3/cms/compare/v5.21.0...v6.0.0
