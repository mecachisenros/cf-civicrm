# Data fields

There are a couple  of options to be aware of when syncing data to CiviCRM. This plugin adds two Special Field Types and integrates the Select Bulk/Insert to Custom Fields created in CiviCRM

## Special fields

When adding new fields to your Caldera form there are two new options available in the Special field type:

* CiviCRM Country
* CiviCRM State/Provice

![Caldera CiviCRM Special Fields Types](/images/caldera-civicrm-special-fields.jpg)

Similar to using a Profile form in CiviCRM, the Country field should be place prior to the State/Provide field so the correct options are displayed.

The default Country and State/Province used in the field will be taken from the settings in **Administer > Localization > Languages, Currency, Locations**

## Custom Data with Select fields

You can create custom fields in CiviCRM that are:

* Dropdown Select
* Checkbox
* Radio
* Date Picker

And have then select those options when you setup your Caldera Form. Start by adding a new field to the form and giving it a name. Then scroll down to the Bulk Insert/Preset option and select the field creating in CiviCRM you want to use and click Insert Options.

![Bulk Insert/Preset](/images/caldera-select-bulk-insert.jpg)

When using the Date Picker you must use the same settings that are in **Administer > Localization > Date Formats** in the **Date Input Fields** section. And adjusted by customized in **Administer > Customize Data and Screens > Date Preferences**

![Date Picker Field Format](/images/caldera-civicrm-date-format.jpg)
