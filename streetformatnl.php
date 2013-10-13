<?php
ini_set('display_errors', '1');
require_once 'streetformatnl.civix.php';

/**
 * Implementation of hook_civicrm_buildForm
 * 
 * change sequence for street elements if config language is NL_nl
 * @author Erik Hommel (erik.hommel@civicoop.org)
 * 
 */
function streetformatnl_civicrm_buildForm($formName, &$form) {
//    if ( $formName == "CRM_Contact_Form_Contact" || $formName == "CRM_Contact_Form_Inline_Address") {
        /*
         * check the language of the current installation and only do something if set to NL_nl
         */
//        $settingParams = array('return'=>'lcMessages');
//        try{
//            $settingApi = civicrm_api3('setting', 'getsingle', $settingParams);
//        }
//        catch (CiviCRM_API3_Exception $e) {
//            $apiError = $e->getMessage();
//            if (!isset($session)) {
//                $session = CRM_Core_Session::singleton();
//            }
//            $session->setStatus("Unable to retrieve CiviCRM lcMessage setting with Setting API, check your configuration! Error from the API: $apiError", "Unable to retrieve CiviCRM language", 'error');
//        }
//        if (isset($settingApi['lcMessages'])) {
//            if ($settingApi['lcMessages'] == "nl_NL") {
//                
//            }
//        }
//    }
}

/**
 * Implementation of hook_civicrm_config
 */
function streetformatnl_civicrm_config(&$config) {
  _streetformatnl_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function streetformatnl_civicrm_xmlMenu(&$files) {
  _streetformatnl_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function streetformatnl_civicrm_install() {
  return _streetformatnl_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function streetformatnl_civicrm_uninstall() {
  return _streetformatnl_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 * 
 * All addresses that are in the Netherlands have to be re-formatted
 * to name/number/unit sequence
 * 
 * @author Erik Hommel (CiviCooP, erik.hommel@civicoop.org)
 */

function streetformatnl_civicrm_enable() {
    $toBeParsedAddresses = array();
    /*
     * first select addresses in Netherlands
     */
    $addressNlParams = array('country_id' => 1152);
    try {
        $apiAddressesNl = civicrm_api3('address', 'get', $addressNlParams);
    }
    catch (CiviCRM_API3_Exception $e) {
    }
    if (isset($apiAddressesNl['values'])) {
        $toBeParsedAddresses = $apiAddressesNl['values'];
    }
    /*
     * then add addresses in Belgium
     */
    $addressBeParams = array('country_id' => 1020);
    try {
        $apiAddressesBe = civicrm_api3('address', 'get', $addressBeParams);
    }
    catch (CiviCRM_API3_Exception $e) {
    }
    if (isset($apiAddressesBe['values'])) {
        array_merge($toBeParsedAddresses, $apiAddressesBe['values']);
    }
    if (!empty($toBeParsedAddresses)) {
        foreach ($toBeParsedAddresses as $addressKey => $address) {
            /*
             * check if street_name and street_number both have values. If not, 
             * and street_address does have more than one unbroken string, split the address first
             */
            if (isset($address['street_address']) && !empty($address['street_address'])) {
                if (!isset($address['street_name']) || empty($address['street_name'])) {
                    if (!isset($address['street_number']) || empty($address['street_number'])) {                    
                        $apiAddressParts = explode(" ", $address['street_address']);
                        if (isset($apiAddressParts[1])) {
                            $parsedParts = _splitStreetAddressNl($address['street_address']);
                            if (isset($parsedParts['street_name'])) {
                                $address['street_name'] = $parsedParts['street_name'];
                            }
                            if (isset($parsedParts['street_number'])) {
                                $address['street_number'] = $parsedParts['street_number'];
                            }
                            if (isset($parsedParts['street_unit'])) {
                                $address['street_unit'] = $parsedParts['street_unit'];
                            }
                        }
                    }
                }
                /*
                 * glue the address together in Dutch format if applicable
                 */
                $glueParams = array();
                $apiUpdateParams = array('id' => $addressKey);
                if (isset($address['street_name'])) {
                    $glueParams['street_name'] = $address['street_name'];
                    $apiUpdateParams['street_name'] = $address['street_name'];
                    }
                if (isset($address['street_number'])) {
                    $glueParams['street_number'] = $address['street_number'];
                    $apiUpdateParams['street_number'] = $address['street_number'];
                }
                if (isset($address['street_unit'])) {
                    $glueParams['street_unit'] = $address['street_unit'];
                    $apiUpdateParams['street_unit'] = $address['street_unit'];
                }
                if (!empty($glueParams)) {
                    $apiUpdateParams['street_address'] = _glueStreetAddressNl($glueParams);
                }
                /*
                 * update address with new values using API
                 */
                try {
                    civicrm_api3('address', 'update', $apiUpdateParams);                    
                }
                catch (CiviCRM_API3_Exception $e) {
                }
            }
        }
    }
  return _streetformatnl_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function streetformatnl_civicrm_disable() {
  return _streetformatnl_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function streetformatnl_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _streetformatnl_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function streetformatnl_civicrm_managed(&$entities) {
  return _streetformatnl_civix_civicrm_managed($entities);
}
/**
 * function to glue street address from components in params
 * @param array, expected street_name, street_number and possibly street_unit
 * @return $parsedStreetAddressNl
 */
function _glueStreetAddressNl($params) {
    $parsedStreetAddressNl = "";
    /*
     * do nothing if no street_name in params
     */
    if (isset($params['street_name'])) {
        $parsedStreetAddressNl = trim($params['street_name']);
        if (isset($params['street_number']) && !empty($params['street_number'])) {
            $parsedStreetAddressNl .= " ".trim($params['street_number']);
        }
        if (isset($params['street_unit']) && !empty($params['street_unit'])) {
            $parsedStreetAddressNl .= " ".trim($params['street_unit']);
        }
    }
    return $parsedStreetAddressNl;
}
/**
 * function to split street_address into components according to Dutch formats.
 * @param streetAddress, containing parsed address in possible sequence
 *        street_number, street_name, street_unit
 *        street_name, street_number, street_unit
 * @return $result, array holding street_number, street_name and street_unit
 */
function _splitStreetAddressNl($streetAddress) {
    $result = array();
    /*
     * do nothing if streetAddress is empty
     */
    if (!empty($streetAddress)) {
        /*
         * split into parts separated by spaces
         */
        $addressParts = explode(" ", $streetAddress);
        $foundStreetNumber = false;
        $streetName = null;
        $streetNumber = null;
        $streetUnit = null;
        foreach($addressParts as $partKey => $addressPart) {
            /*
             * if the part is numeric, there are several possibilities:
             * - if the partKey is 0 so it is the first element, it is
             *   assumed it is part of the street_name to cater for 
             *   situation like 2e Wormenseweg
             * - if not the first part and there is no street_number yet (foundStreetNumber
             *   is false), it is assumed this numeric part contains the street_number
             * - if not the first part but we already have a street_number (foundStreetNumber
             *   is true) it is assumed this is part of the street_unit
             */
            if (is_numeric($addressPart)) {
                if ($foundStreetNumber == false) {
                    $streetNumber = $addressPart;
                    $foundStreetNumber = true;
                } else {
                    $streetUnit .= " ".$addressPart;
                }
            } else {
                /*
                 * if part is not numeric, there are several possibilities:
                 * - if the street number is found, set the whole part to streetUnit
                 * - if there is no streetNumber yet and it is the first part, set the
                 *   whole part to streetName
                 * - if there is no streetNumber yet and it is not the first part,
                 *   check all digits:
                 *   - if the first digit is numeric, put the numeric part in streetNumber
                 *     and all non-numerics to street_unit
                 *   - if the first digit is not numeric, put the lot into streetName
                 */
                if ($foundStreetNumber == true) {
                    $streetUnit .= " ".$addressPart;
                } else {
                    if ($partKey == 0) {
                        $streetName .= $addressPart;
                    } else {
                        $partLength = strlen($addressPart);
                        if (is_numeric(substr($addressPart, 0, 1))) {
                            for ($i=0; $i<$partLength; $i++) {
                                if (is_numeric(substr($addressPart, $i, 1))) {
                                    $streetNumber .= substr($addressPart, $i, 1);
                                    $foundStreetNumber = true;
                                } else {
                                    $streetUnit .= " ".substr($addressPart, $i, 1);
                                }
                            }
                        } else {
                            $streetName .= " ".$addressPart;
                        }
                    }
                }
            }
        }
        $result['street_name'] = trim($streetName);
        $result['street_number'] = $streetNumber;
        $result['street_unit'] = trim($streetUnit);
    }
    return $result;
}


