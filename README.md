org.civicoop.streetformatnl
===========================

CiviCRM extension to change the street_address parsing to Dutch format (street_name, street_number, all others)

It only makes sense to enable this extension if the CiviCRM setting 'street address parsing' is checked. This setting can be found at Administer/Localization/Address Settings (or in Dutch"Beheren/Lokalisatie/Adresformaten) AND if the local language is set to Dutch (nl_NL)
Also, the assumption is that either Netherlands or Belgium is set as the default country. The parsing in Dutch format will happen only for those contacts that are located in either Netherlands or Belgium.
There will not be an error when Netherlands or Belgium is not the default country, and nothing will go wrong. It just means that there might be addresses in your database that actually would need parsing in Dutch format, but that will not happen because the country is empty.
So if you have addresses in your database that need formatting in the Dutch way but have no country_id, make sure that they have the expected country id (1152 for Netherlands and 1020 for Belgium)

What does it do?
-----------------
<ol>
<li>It changes the sequence of the street address fields in the inline edit and edit forms from street_number, street_name and all other street fields after that to the Dutch format of street_name, street_number and all other street fields after that.
<br /><strong>Technical</strong>
Modified template <em>CRM/Contact/Form/Edit/Address/street_address.tpl</em>. Also, nl_NL is added as a possible language in <em>CRM/Core/BAO/Address.php</em>.
</li>

<li>It changed the way street_address is stored in the dabase to reflect the Dutch format of street_name, street_number and all other street fields after that.
<br /><strong>Technical</strong>
Implementation of <em>hook_civicrm_pre</em>
</li>
</ol>

Future wishes
-------------

Here are a couple of things I would like to do to make this better and more generic. If you are interested in funding some of this, please contact us (helpdesk@civicoop.org, erik.hommel@civicoop.org)
<ul>
<li>Introduce a local file holding settings for this extension. This would include what languages to apply this for (with Dutch as default) and which countries to test for (with Netherlands and Belgium as default)</li>
<li>Change CRM/Core/BAO/Address.php to add possible languages based on the local settings file</li>
<li>For the even longer term: investigate how CiviCRM is going to cope with the various address formats based on internationally recognised standards and working with standard open source tools</li>

Any more suggestions, drop us a line!
