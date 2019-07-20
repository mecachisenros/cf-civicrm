=== Caldera Forms CiviCRM ===
Contributors: mecachisenros, needle
Tags: civicrm, caldera, forms, integration
Requires at least: 4.7
Tested up to: 5.2.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate CiviCRM entities with Caldera Forms.


== Description ==

The Caldera Forms CiviCRM plugin contains a set of form processors that interact with CiviCRM's API to retrieve, create and update data in CiviCRM. With this plugin, you can create responsive forms that expose CiviCRM fields and entities like Activities, Relationships, Tags, Groups and more.

### Requirements

This plugin requires a minimum of *CiviCRM 4.6* and *Caldera Forms 1.7.3*.

### Plugin Development

This plugin is in active development. For feature requests and bug reports (or if you're a plugin author and want to contribute) please visit the plugin's [GitHub repository](https://github.com/mecachisenros/cf-civicrm).



== Installation ==

1. Extract the plugin archive
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress



== Changelog ==

= 1.0.3 =
* Published to WP plugin directory, text domain has changed from 'caldera-forms-civicrm' to 'cf-civicrm'
* Added 'cfc_participant_pre_processor_config' filter
* Added 'cfc_participant_pre_processor_event_config' filter
* Added 'cfc_participant_processor_config_template_before_link' action
* Added 'cfc_participant_pre_processor_return' filter
* Participant processor can handle multiple registrations for the same contact through the added hooks
* Fix - Re-registration based on 'Allow same participant email'
* Date pickers auto-population respects the date format, thanks @kirk-circle
* Added 'cfc_membership_pre_processor_config' filter
* Added 'cfc_membership_pre_processor_return' filter
* Added 'cfc_membership_processor_config_template_before_link' action
* Added Campaign field for Membership processor
* Fix - Auto-population checkbox ignored, the form would always be autopopulated with the current user data
* Case processor now supports custom fields, and custom fields autopopulation/presets
* Contact Reference can be mapped for Organization Name
* FIX - jQuery could not be defined breaking processor templates
* Added 'cfc_case_processor_case_create' action
* Added 'cfc_case_pre_processor_return' filter
* Added 'cfc_case_processor_config_template_before_link' action

= 1.0.2 =
* Added Participant processor for free and paid events
* Price sets improvements
* Tax support
* CiviDiscount integration for Participant registration and option based discounts (if extension is enabled)
* Discount field for CiviDiscount integration (if extension is enabled)
* Added support for Premium and a Premium field
* Autopopulate options are now available to use a field or processor conditionals
* Line Item processor now supports multiple choices options (ie checkbox type price field)
* Support free entry amounts (ie Civi's Other Amount) for Memberships and Participants
* Other minor improvements


= 0.4.4 =
* Bug fixes
* Added option to submit empty/blank values for Address entity
* Moved scripts and styles to it's own Assets class
* Added $helper property as a replacement for CiviCRM_Caldera_Forms_Helper static methods and properties
* Moved Bulk insert/presets and Autopopulate options to their own classes
* Fixed usability issue in #61 https://github.com/mecachisenros/cf-civicrm/issues/61
* Contact Reference custom field (with option to add new Organization)
* Membership processor (for paid and free Memberships)
* Order processor to process Contributions for Donations and Memberships (to process live transactions a payment add-on is needed see https://calderaforms.com/caldera-forms-add-ons/#/payment it currently integrates with the Stripe and Authorize.net add-ons)
* Line Item processor (adds Line Items to Contributions through the Order processor)
* Added pluggable template after ajax submission (the idea is to serve as a replacement for Civi's Thank you page)

= 0.4.3 =
* Fix to prevent select2 conflicts if different vesions are present
* Added Activity Target, Source, and Assignee fields as select2 widgets (entityRef-like field)
* Show CiviCRM API errors in form
* Added Contribution processor (code contributed by Agileware) needs documentation
* Added Case Id magic tag (code contributed by Agileware)

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
