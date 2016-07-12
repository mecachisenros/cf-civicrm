# Caldera Forms CiviCRM Integration - ALPHA version
Integrates [Caldera Forms](https://en-gb.wordpress.org/plugins/caldera-forms/ "Caldera Forms Wordpress plugin") plugin with [CiviCRM](https://civicrm.org/ "Open Source CRM")

A set of processor that interact with CiviCRM's API to retrieve/create/update CiviCRM data.
The main purpose of this integration is to facilitate the creation of responsive forms and allow exposing CiviCRM fields and entities that are not supported out of the box in Wordpress, like Activities, Relationships, Tags, Groups, etc.

This plugins is in development (alpha version), currently tested with CiviCRM 4.6.X and 4.7.X

Currently supports:

### + Contact entity
* Up to **10 Contact** prcessors on the same form
* **Auto-populate form** if the user is logged in
* **Checksum support** to auto-populate form with URLs like **example.com/support?cid={contact.contact_id}&{contact.checksum}**
* **Contact Type**: Organization, Individual, Household, Custom Type
* **Contact Subtypes**
* **Custom Fields**

### + Activity entity
### + Relationship entity
### + Group entity
### + Tag entity

### Coming soon
+ Address processor
+ Email processor
+ Phone processor
+ IM processor
+ Note processor
