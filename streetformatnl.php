<?php
ini_set('display_errors', '1');
require_once 'streetformatnl.civix.php';

/**
 * Implementation of hook_civicrm_pre
 * 
 * make sure street_address, street_name, street_number and street_unit are displayed
 * correctly when address in The Netherlands or Belgium
 */
function streetformatnl_civicrm_pre($op, $objectName, $objectId, &$objectRef) {
    if ($objectName == "Address") {
        if (isset($objectRef['country_id'])) {
            if ($objectRef['country_id'] == 1152 || $objectRef['country_id'] == 1020) {
                /*
                 * glue if street_name <> empty and street_number <> empty, split otherwise if street_address not empty
                 */
                if (!empty($objectRef['street_name']) && !empty($objectRef['street_number'])) {
                    $glueParams['street_name'] = $objectRef['street_name'];
                    $glueParams['street_number'] = $objectRef['street_number'];
                    if (isset($objectRef['street_unit'])) {
                        $glueParams['street_unit'] = $objectRef['street_unit'];
                    }
                    $objectRef['street_address'] = _glueStreetAddressNl($glueParams);
                } else {
                    if (isset($objectRef['street_address']) && !empty($objectRef['street_address'])) {
                        $streetParts = _splitStreetAddressNl($objectRef['street_address']);
                        $objectRef['street_name'] = $streetParts['street_name'];
                        if (isset($streetParts['street_number']) && !empty($streetParts['street_number'])) {
                            $objectRef['street_number'] = $streetParts['street_number'];
                        }
                        if (isset($streetParts['street_unit']) && !empty($streetParts['street_unit'])) {
                            $objectRef['street_unit'] = $streetParts['street_unit'];
                        }
                        $objectRef['street_address'] = _glueStreetAddressNl($streetParts);
                    }
                }                
            }
        }
    }
}
/**
 * Implementation of hook_civicrm_buildForm
 * 
 * @author Erik Hommel (erik.hommel@civicoop.org)
 * 
 */
function streetformatnl_civicrm_buildForm($formName, &$form) {
    if ( $formName == "CRM_Contact_Form_Contact" || $formName == "CRM_Contact_Form_Inline_Address") {
        /*
         * check if any of the addresses of the contact are in Netherlands or Belgium and
         * change the street_address sequence for those contact
         */
        $formValues = $form->getVar('_values');
        $preEditValues = $form->getVar('_preEditValues');
        if (isset($formValues['address'])) {
            $formAddresses = $formValues['address'];
        }
        if (isset($preEditValues['address'])) {
            $preEditAddresses['address'] = &$preEditValues['address'];
        }
        unset($formValues);
        if (!empty($formAddresses)) {
            foreach ($formAddresses as $addressElementId => $formAddress) {
                $glueParams = array();
                if (isset($formAddress['country_id'])) {
                    if ($formAddress['country_id'] == 1152 || $formAddress['country_id'] == 1020) {
                        /*
                         * if only street_address filled, split and then glue
                         */
                        if (isset($formAddress['street_name'])) {
                            $glueParams['street_name'] = $formAddress['street_name'];
                        }
                        if (isset($formAddress['street_number'])) {
                            $glueParams['street_number'] = $formAddress['street_number'];
                        }
                        if (isset($formAddress['street_unit'])) {
                            $glueParams['street_unit'] = $formAddress['street_unit'];
                        }
                        if (!empty($glueParams)) {
                            $streetAddress = _glueStreetAddressNl($glueParams);
                            if (!empty($streetAddress)) {
                                $defaults['address'][$addressElementId]['street_address'] = $streetAddress;
                                $preEditValues['address'][$addressElementId]['street_address'] = $streetAddress;
                                $form->setDefaults( $defaults );
                                $form->setVar('_preEditValues', $preEditValues);
                            }
                        }
                    }
                }
            }
        }
    }
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
                    if (!empty($streetName)) {
                        $streetUnit .= " ".$addressPart;
                    } else {
                        $streetName .= " ".$addressPart;
                    }
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
        /*
         * if we still have no street_number, add contact to checkgroup
         */
        
    }
    return $result;
}


