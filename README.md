org.civicoop.streetformatnl
===========================

CiviCRM extension to change the street_address parsing to Dutch format (street_name, street_number, all others)

It only makes sense to enable this extension if the CiviCRM setting 'street address parsing' is checked. This setting can be found at Administer/Localization/Address Settings (or in Dutch"Beheren/Lokalisatie/Adresformaten) AND if the local language is set to Dutch (NL_nl)

What does it do?
-----------------
<ol>
<li>It changes the sequence of the street address fields in the inline edit and edit forms from street_number, street_name and all other street fields after that to the Dutch format of street_name, street_number and all other street fields after that.
<strong>Technical</strong>
Implementation of hook_civicrm_buildForm to make this happen. Also, NL_nl is added as a possible language in CRM/Core/BAO/Address.php.
</li>

<li>It changed the way street_address is stored in the dabase to reflect the Dutch format of street_name, street_number and all other street fields after that.
<strong>Technical</strong>
Implementation of hook_civicrm_pre
</li>
</ol>

Future wishes
-------------

Here are a couple of things I would like to do to make this better and more generic. If you are interested in funding some of this, please contact us (helpdesk@civicoop.org, erik.hommel@civicoop.org)
<ul>
<li>Introduce a local file holding settings for this extension. This would include what languages to apply this for (with Dutch as default) and which countries to test for (with Netherlands as default)</li>
<li>Change CRM/Core/BAO/Address.php to add possible languages based on the local settings file</li>
<li>For the even longer term: investigate how CiviCRM is going to cope with the various address formats based on internationally recognised standards and working with standard open source tools</li>

Any more suggestions, drop us a line!
