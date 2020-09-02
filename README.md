# Caldera Forms CiviCRM

A WordPress plugin that integrates the [Caldera Forms](https://wordpress.org/plugins/caldera-forms/ "Caldera Forms WordPress plugin") plugin with [CiviCRM](https://civicrm.org/ "Open Source CRM").

The Caldera Forms CiviCRM plugin contains a set of form processors that interact with CiviCRM's API to retrieve, create and update data in CiviCRM. With this plugin, you can create responsive forms that expose CiviCRM fields and entities like Activities, Relationships, Tags, Groups and more.

**Note**: _this is the development repository for Caldera Forms CiviCRM integration. The plugin can be found in the [WordPress plugin directory](https://wordpress.org/plugins/cf-civicrm/), unless you are developing or testing (`dev` branch) it's encouraged to install it from the WordPress plugin directory._

This plugin is in active development and is currently being tested with the latest CiviCRM version (5.x).

### Features

-   Add up to **10 Contacts** on the same form
-   Auto-populate form if the user is logged in
-   Define Contact Type: Organization, Individual, Household, and Custom Contact Subtypes
-   Map Custom Fields data
-   Add Relationships to each contact
-   Create Activities on form submission
-   Select Email Template for notification (requires [Email API Extension](https://civicrm.org/extensions/e-mail-api))
-   CiviDiscount integration for Participant Registration and special field (requires [CiviDiscount Extension](https://civicrm.org/extensions/cividiscount))
-   Open a Case on form submission
-   Checksum support to auto-populate form with URLs like **example.com/some-page?cid={contact.contact_id}&{contact.checksum}**
-   Add Memberships (CiviMember)
-   Add Participants (CiviEvent)
-   Add Contributions with Line Items (for live transactions a [Caldera Forms Payment add-on](https://calderaforms.com/caldera-forms-add-ons/#/payment) is needed)

### Requirements

To use this plugin, the following is needed:

-   WordPress
-   CiviCRM 5.x
-   [Caldera Forms](https://wordpress.org/plugins/caldera-forms/ "Caldera Forms WordPress plugin") v1.8.1 or greater to be installed

### Using Caldera Forms CiviCRM

-   Get an [overview of Caldera Forms](/docs/overview.md)
-   Setup [CiviCRM Processors](/docs/processors.md) on your form
-   Use [Custom Fields](/docs/custom-fields.md)
-   [Examples](/docs/examples.md)

### Contribute

Please help improve this plugin by using the extension issue queue to report any troubles and to make requests for feature improvements. The issue queue is here: https://github.com/mecachisenros/cf-civicrm/issues

Issues submitted to the issue queue will be addressed based on time and interest. If you are a developer contributions are welcome.
