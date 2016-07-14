Yii Framework 2 bootstrap extension Change Log
==============================================

2.0.6 March 17, 2016
--------------------

- Bug #68: Fixed `yii\bootstrap\Nav` handling empty items (freezy-sk)
- Bug #81: Fixed `yii\bootstrap\ActiveField::radioList()` and `yii\bootstrap\ActiveField::checkboxList()` ignore `itemOptions` (mikehaertl)
- Bug #98: Fixed `yii\bootstrap\ButtonDropdown` setting `href` attribute for non `a` tags (13nightevil)
- Bug #124: Fixed `yii\bootstrap\Tabs` to use `tag` configuration option for item container (arturf)
- Enh #45: Added support for Bootstrap checkbox/radio toggle buttons (RomeroMsk, klimov-paul)
- Enh #92: Allow overriding `data-toggle` in `yii\bootstrap\Tabs` (machour)


2.0.5 September 23, 2015
------------------------

- Enh #15: Allowed overriding default Bootstrap CSS classes added by widgets (klimov-paul)
- Enh #38: Added object support for `content` option in `Collapse` class (pana1990, ItsReddi)
- Enh #40: Added `visible` option to `yii\bootstrap\Tab` widget items (klimov-paul)
- Enh #41: Added `submenuOptions` support at `yii\bootstrap\Dropdown` (spikyjt, klimov-paul)
- Enh #42: Added support for the glyphicons via `yii\bootstrap\Html::icon()` (klimov-paul)
- Enh #43: Added support for the static form controls via `yii\bootstrap\Html` (klimov-paul)
- Enh #44: Fixed `yii\bootstrap\ButtonDropdown` renders two buttons with the same id, if 'split' is enabled (klimov-paul)
- Enh #50: Added `dropDownOptions` that is passed to `yii\bootstrap\Nav` dropdown items (fbau123)


2.0.4 May 10, 2015
------------------

- Bug #18: `label` option ignored by `yii\bootstrap\Activefield::checkbox()` and `yii\bootstrap\Activefield::radio()` (mikehaertl)
- Bug #5984: `yii\bootstrap\Activefield::checkbox()` caused browser to link label to the wrong input (cebe)
- Bug #7894: Fixed incorrect URL config processing at `yii\bootstrap\Nav::items` if route element is not a first one (nkovacs, klimov-paul)
- Bug #8231: Configuration of Alert, ButtonDropdown, Modal widget where not preserved when used multiple times (cebe, idMolotov)
- Bug (CVE-2015-3397): Using `Json::htmlEncode()` for safer JSON data encoding in HTML code (samdark, Tomasz Tokarski)
- Enh #29: Added support to list-groups for Collapse class (pana1990, skullcrasher)
- Enh #2546: Added `visible` option to `yii\bootstrap\ButtonGroup::$buttons` (samdark, lukBarros)
- Enh #7633: Added `ActionColumn::$buttonOptions` for defining HTML options to be added to the default buttons (cebe)
- Enh: Added `Nav::$dropDownCaret` to allow customization of the dropdown caret symbol (cebe)
- Enh: Added support for using external URLs for `Tabs`. (dynasource, qiangxue)


2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- Bug #6672: `yii\bootstrap\Dropdown` should register client event handlers (qiangxue)


2.0.1 December 07, 2014
-----------------------

- Bug #5570: `yii\bootstrap\Tabs` would throw an exception if `content` is not set for one of its `items` (RomeroMsk)
- Bug #6150: `yii\bootstrap\Tabs` dropdown IDs were generated incorrectly (samdark)
- Enh #4146: Added `yii\bootstrap\ButtonDropdown::$containerOptions` (samdark)
- Enh #4181: Added `yii\bootstrap\Modal::$headerOptions` and `yii\bootstrap\Modal::$footerOptions` (tuxoff, samdark)
- Enh #4450: Added `yii\bootstrap\Nav::renderDropdown()` (qiangxue)
- Enh #5494: Added support for specifying a menu header as a configuration array in `yii\bootstrap\Dropdown` (hiltonjanfield, qiangxue)
- Enh #5735: Added `yii\bootstrap\Tabs::renderTabContent` to support manually rendering tab contents (RomeroMsk)
- Enh #5799: `yii\bootstrap\ButtonGroup::buttons` can take all options that are supported by `yii\bootstrap\Button` (aleksanderd)
- Chg #5874: Upgraded Twitter Bootstrap to 3.3.x (samdark)


2.0.0 October 12, 2014
----------------------

- Bug #5323: Nested dropdown does not work for `yii\bootstrap\DropDown` (aryraditya)
- Bug #5336: `yii\bootstrap\DropDown` should register bootstrap plugin asset (zelenin)
- Chg #5231: Collapse `items` property uses `label` element instead of array key for headers (nkovacs)
- Chg #5232: Collapse encodes headers by default (nkovacs)
- Chg #5217: Tabs no longer requires content since empty tab could be used dynamically (damiandennis)


2.0.0-rc September 27, 2014
---------------------------

- Bug #3292: Fixed dropdown widgets rendering incorrect HTML (it3rmit)
- Bug #3740: Fixed duplicate error message when client validation is enabled (tadaszelvys)
- Bug #3749: Fixed invalid plugin registration and ensure clickable links in dropdown (kartik-v)
- Enh #4024: Added ability to `yii\bootstrap\Tabs` to encode each `Tabs::items['label']` separately (creocoder, umneeq)
- Enh #4120: Added ability for each item to choose it's encoding option in `Dropdown` and `Nav` (Alex-Code)
- Enh #4363: Added `showIndicators` property to make Carousel indicators optional (sdkiller)
- Chg #3036: Upgraded Twitter Bootstrap to 3.1.x (qiangxue)
- Chg #4595: The following properties are now taking `false` instead of `null` for "don't use" case (samdark)
  - `yii\bootstrap\NavBar::$brandLabel`.
  - `yii\bootstrap\NavBar::$brandUrl`.
  - `yii\bootstrap\Modal::$closeButton`.
  - `yii\bootstrap\Modal::$toggleButton`.
  - `yii\bootstrap\Alert::$closeButton`.

2.0.0-beta April 13, 2014
-------------------------

- Bug #2361: `yii\bootstrap\NavBar::brandUrl` should default to the home URL of application (qiangxue)
- Enh #1474: Added option to make NavBar 100% width (cebe)
- Enh #1552: It is now possible to use multiple bootstrap NavBar in a single page (Alex-Code)
- Enh #1553: Only add navbar-default class to NavBar when no other class is specified (cebe)
- Enh #1562: Added `yii\bootstrap\Tabs::linkOptions` (kartik-v)
- Enh #1601: Added support for tagName and encodeLabel parameters in ButtonDropdown (omnilight)
- Enh #1881: Improved `yii\bootstrap\NavBar` with `containerOptions`, `innerContainerOptions` and `renderInnerContainer` (creocoder)
- Enh #2425: Tabs widget now selects first tab if no active tab is specified (samdark)
- Enh #2634: Submenus will now be checked for being active (Alex-Code)
- Enh #2643: Add size attribute to Modal (tof06)
- Chg #1459: Update Collapse to use bootstrap 3 classes (tonydspaniard)
- Chg #1820: Update Progress to use bootstrap 3 markup (samdark)
- New #3029: Added `yii\bootstrap\ActiveForm` and `yii\bootstrap\ActiveField` (mikehaertl)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
