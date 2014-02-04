<?php


/**
 * AddressNl.Parse API
 * 
 * Function parses all addresses from NL or BE that have an empty street_name.
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 4 Feb 2014
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @license Academic Free License V.3.0 (http://opensource.org/licenses/academic.php)
 */
function civicrm_api3_address_nl_parse($params) {
    ini_set('max_execution_time', 0);
    $count_addresses = 0;
    $query = 
"SELECT id, street_address FROM civicrm_address WHERE (country_id = 1152 OR country_id = 1020) AND street_name IS NULL";
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
        $adres_elements = _splitStreetAddressNl($dao->street_address);
        $update_fields = array();
        if (isset($adres_elements['street_name']) && !empty($adres_elements['street_name'])) {
            $street_name = CRM_Core_DAO::escapeString($adres_elements['street_name']);
            $update_fields[] = "street_name = '$street_name'";
        }
        if (isset($adres_elements['street_number']) && !empty($adres_elements['street_number'])) {
            $update_fields[] = "street_number = {$adres_elements['street_number']}";
        }
        if (isset($adres_elements['street_unit']) && !empty($adres_elements['street_unit'])) {
            $street_unit = CRM_Core_DAO::escapeString($adres_elements['street_unit']);
            $update_fields[] = "street_unit = '$street_unit'";
        }
        if (!empty($update_fields)) {
            $count_addresses++;
            $update = "UPDATE civicrm_address SET ".implode(", ", $update_fields)." WHERE id = {$dao->id}";
            CRM_Core_DAO::executeQuery($update);
        }
    }
    $returnValues = array('message'   =>  $count_addresses.' addresses succesfully parsed.'
    );
    return civicrm_api3_create_success($returnValues, array(), 'AddressNl', 'Parse');
}

