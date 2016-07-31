org.civicoop.streetformatnl
===========================

**The current version of this extension is only compatible with CiviCRM 4.6 and 4.7.  
Please use the 1.0 release (see the Releases tab) for CiviCRM 4.4.**

CiviCRM extension to change the street_address parsing to Dutch format (street_name, street_number, all others)

It only makes sense to enable this extension if the CiviCRM setting 'street address parsing' is checked. This setting can be found at Administer/Localization/Address Settings (or in Dutch Beheren/Lokalisatie/Adresformaten) AND if the local language is set to Dutch (nl_NL).
Also, the assumption is that either Netherlands or Belgium is set as the default country. The parsing in Dutch format will happen only for those contacts that are located in either Netherlands or Belgium.

Please note that the extension does not convert existing street_addresses in your database! It assumes you either install this extension in a 
clean install or update your existing street_addresses in your database using MySQL. At least, if you have switched on street_parsing
with an empty CiviCRM database, you will have street_names, street_numbers etc. in your database. You can just add those together in 
the right sequence to update your existing street_addresses.

What does it do?
-----------------
<ol>
<li>It changes the sequence of the street address fields in the inline edit and edit forms from street_number, street_name and all other street fields after that to the Dutch format of street_name, street_number and all other street fields after that.
<br /><strong>Technical</strong>
Modified template <em>CRM/Contact/Form/Edit/Address/street_address.tpl</em>. Also, nl_NL is added as a possible language in <em>CRM/Core/BAO/Address.php</em>.
Additionally, street address parsing is added to <em>CRM/Event/Form/ManageEvent/Location.php</em>.
And the hook <em>hook_civicrm_buildForm</em> is used to split and glue street_address before displaying.
</li>

<li>It changes the way street_address is stored in the database to reflect the Dutch format of street_name, street_number and all other street fields after that.
<br /><strong>Technical</strong>
Implementation of <em>hook_civicrm_pre</em>
</li>

<li>There is a <strong>scheduled job</strong> called 'Parse NL and BE addresses' that will select all Dutch or Belgian addresses in the database where the <em>street_name</em> field is empty, and parse those. This will enable you to parse your addresses after an import action. The scheduled job is set to inactive when installed, you will have to make it active and then run it from the scheduled job list. You could leave it to run daily, but that should not be necessary. Advise is to set it back to inactive once it has run.
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
