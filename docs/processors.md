# CiviCRM Processors

The following Processors are added when the CiviCRM Caldera Forms plugin is activated. Multiple processors of each type can be used to accomplish for each Caldera form.

### Contact entity

This processor is the one that needs to be used before others can be used, as it defines that contact the information is being applied to in CiviCRM. Rquired fields for using this processor are Contact Type and Dedupe Rule.

![CiviCRM Contact Processor](./images/caldera-contact-processor.jpg)

Magic Tags are used to map the fields created in the Caldera form with fields in CiviCRM, see [overview of Caldera Forms](./overview.md) for more detail on Magic Tags.

![Caldera Forms Magic Tags](./images/contact-processor-magic-tags.jpg)

The contact processor has some of the processors outlined below built in, so that more information about the contact can be mapped in single processor. By checking more options, need selections appear.

![CiviCRM Contact additional Checked Processors](./images/caldera-contact-checked-selections.jpg)

However, keep in mind that if some of the fields are not required separate processors may need to be used with condition set, see [overview of Caldera Forms](./overview.md).

### Activity entity

The Activity Processor provides the ability to record an activity for one more contacts on form submission. The activities available are all the activities available in CiviCRM and custom fields for those Activity Types can also be processed.

![CiviCRM Activity Processor](./images/civicrm-activity-processor.jpg)

### Relationship entity

The Relationship processor provides a way to assign a relationship to two contacts on form submission. The Relationship Types are selected from the ones avaialbe in your CiviCRM instance.

![CiviCRM Relationships Processor](./images/civicrm-relationship-processor.jpg)

### Group entity

The Group processor allows for a contact to be added to a group on form submission.

![CiviCRM Group Processor](./images/civicrm-group-processor.jpg)

### Tag entity

The Tag processor allows for a tag to be added to a contact on on form submission.

![CiviCRM Tag Processor](./images/civicrm-tag-processor.jpg)

### Address entity

The Address processor provides the ability for a specific address type to be added or update for that contact in CiviCRM.

![CiviCRM Address Processor](./images/civicrm-address-processor.jpg)

### Email entity

The Email processor provides the ability for a specific email address type to be added or update for that contact in CiviCRM.

![CiviCRM Email Processor](./images/civicrm-email-processor.jpg)

### Phone entity

The Phone processor provides the ability for a specific phone number and type to be added or update for that contact in CiviCRM.

![CiviCRM Phone Processor](./images/civicrm-phone-processor.jpg)

### Note entity

The Note processor allows for a subject and note to be added to a contact on on form submission.

![CiviCRM Note Processor](./images/civicrm-note-processor.jpg)

### Website entity

The Website processor provides the ability for a specific URL and type to be added or update for that contact in CiviCRM.

![CiviCRM Website Processor](./images/civicrm-website-processor.jpg)

### IM (Instant Messenger) entity

The IM processor provides the ability for a specific IM and type to be added or update for that contact in CiviCRM.

![CiviCRM IM (Instant Messenger) Processor](./images/civicrm-im-processor.jpg)
