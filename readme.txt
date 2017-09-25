=== Caldera Forms CiviCRM ===
Contributors: mecachisenros, needle
Tags: civicrm, caldera, forms, integration
Requires at least: 4.5
Tested up to: 4.6
Stable tag: 0.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate CiviCRM entities with Caldera Forms.


== Description ==

The Caldera Forms CiviCRM plugin contains a set of form processors that interact with CiviCRM's API to retrieve, create and update data in CiviCRM. With this plugin, you can create responsive forms that expose CiviCRM fields and entities like Activities, Relationships, Tags, Groups and more.

### Requirements

This plugin requires a minimum of *CiviCRM 4.6* and *Caldera Forms 1.4.2*.

### Plugin Development

This plugin is in active development. For feature requests and bug reports (or if you're a plugin author and want to contribute) please visit the plugin's [GitHub repository](https://github.com/mecachisenros/caldera-forms-civicrm).



== Installation ==

1. Extract the plugin archive
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress



== Changelog ==

= 0.4.2 =
* Added support for CiviCRM file uploads, Advanced File field (allowing multiple uploads for Activities) and File field (for custom fields and notes)
* Added CiviCRM Preferred Language as autopopulate option
* Added Contact Reference (select2 widget) for the Case Created by field in the Case processor
* Fixed pre-populate form issue

= 0.4.1 =
* Case processor - creates/adds case to contact
* Send Email processor (requires Email API - https://civicrm.org/extensions/e-mail-api)

= 0.4 =
* Refactored processors fields mapping
* Added documentation. Big thanks to @danaskallman!!!
* Fixed magic tags not being parsed
* Support CiviCRM on multisite (hopefully)
* Fixed Contact type being overridden


= 0.3 =
* Form Templates
* Contact processor UI improvements
* IM processor

= 0.2 =

* Plugin refactored
* Translation enabled

= 0.1.1 =

* Custom fields are filtered by Contact Type

= 0.1 =

* Initial release
