<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 | Modified version as part of org.civicoop.streetformatnl            |
 | Adds street address parsing support to the event location form.    |
 | erik.hommel@civicoop.org / kevin.levie@civicoop.org                |
 | License : Free Academic License V3.0                               |
 +--------------------------------------------------------------------+
 */

/**
 *
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */

/**
 * This class generates form components for processing Event Location
 * civicrm_event_page.
 */
class CRM_Event_Form_ManageEvent_Location extends CRM_Event_Form_ManageEvent {

  /**
   * How many locationBlocks should we display?
   *
   * @var int
   * @const
   */
  const LOCATION_BLOCKS = 1;

  /**
   * The variable, for storing the location array
   *
   * @var array
   */
  protected $_locationIds = array();

  /**
   * The variable, for storing location block id with event
   *
   * @var int
   */
  protected $_oldLocBlockId = 0;

  /**
   * Get the db values for this form.
   */
  public $_values = array();

  /**
   * Set variables up before form is built.
   */
  public function preProcess() {
    parent::preProcess();

    $this->_values = $this->get('values');
    if ($this->_id && empty($this->_values)) {
      //get location values.
      $params = array(
        'entity_id' => $this->_id,
        'entity_table' => 'civicrm_event',
      );
      $this->_values = CRM_Core_BAO_Location::getValues($params);

      //get event values.
      $params = array('id' => $this->_id);
      CRM_Event_BAO_Event::retrieve($params, $this->_values);
      $this->set('values', $this->_values);
    }

    /* -- START CHANGE: Add address parsing support -- */
    $parseStreetAddress = $this->get('parseStreetAddress');
    if (!isset($parseStreetAddress)) {
      $addressOptions = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'address_options');
      $parseStreetAddress = FALSE;
      if (!empty($addressOptions['street_address']) && !empty($addressOptions['street_address_parsing'])) {
        $parseStreetAddress = TRUE;
      }
      $this->set('parseStreetAddress',  $parseStreetAddress);
    }
    $this->assign('parseStreetAddress', $parseStreetAddress);
    /* -- END CHANGE -- */

    //location blocks.
    CRM_Contact_Form_Location::preProcess($this);
  }

  /**
   * Set default values for the form.
   *
   * Note that in edit/view mode the default values are retrieved from the database.
   */
  public function setDefaultValues() {
    $defaults = $this->_values;

    if (!empty($defaults['loc_block_id'])) {
      $defaults['loc_event_id'] = $defaults['loc_block_id'];
      $countLocUsed = CRM_Event_BAO_Event::countEventsUsingLocBlockId($defaults['loc_block_id']);
      /* -- START CHANGE: Location use shows '1' even when this is the only event using this location (may be a general bug? -- */
      if (!empty($defaults['id']) && $countLocUsed > 0) {
        $countLocUsed --;
      }
      /* -- END CHANGE -- */
      $this->assign('locUsed', $countLocUsed);
    }

    $config = CRM_Core_Config::singleton();
    if (!isset($defaults['address'][1]['country_id'])) {
      $defaults['address'][1]['country_id'] = $config->defaultContactCountry;
    }

    if (!isset($defaults['address'][1]['state_province_id'])) {
      $defaults['address'][1]['state_province_id'] = $config->defaultContactStateProvince;
    }

    $defaults['location_option'] = $this->_oldLocBlockId ? 2 : 1;

    /* -- START CHANGE: Add address parsing default values from CRM_Contact_Form_Edit_Address -- */
    if ($this->get('parseStreetAddress')) {
      $parseFields = array('street_address', 'street_number', 'street_name', 'street_unit');
      foreach ($defaults['address'] as $cnt => & $address) {
        $streetAddress = NULL;
        foreach (
          array(
            'street_name',
            'street_number',
            'street_number_suffix',
            'street_unit',
          ) as $fld
        ) {
          if (in_array($fld, array(
            'street_number',
            'street_unit',
          ))) {
            $streetAddress .= ' ';
          }
          $streetAddress .= CRM_Utils_Array::value($fld, $address);
        }
        $streetAddress = trim($streetAddress);
        if (!empty($streetAddress)) {
          $address['street_address'] = $streetAddress;
        }
        if (isset($address['street_number'])) {
          $address['street_number'] .= CRM_Utils_Array::value('street_number_suffix', $address);
        }

        // build array for set default.
        foreach ($parseFields as $field) {
          $addressValues["{$field}_{$cnt}"] = CRM_Utils_Array::value($field, $address);
        }
        // don't load fields, use js to populate.
        foreach (array('street_number', 'street_name', 'street_unit') as $f) {
          if (isset($address[ $f ])) {
            unset($address[ $f ]);
          }
        }
      }
      $this->assign('allAddressFieldValues', json_encode($addressValues));
    }
    /* -- END CHANGE -- */

    return $defaults;
  }

  /**
   * Add local and global form rules.
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Event_Form_ManageEvent_Location', 'formRule'));
  }

  /**
   * Global validation rules for the form.
   *
   * @param array $fields
   *   Posted values of the form.
   *
   * @return array
   *   list of errors to be posted back to the form
   */
  public static function formRule($fields) {
    // check for state/country mapping
    $errors = CRM_Contact_Form_Edit_Address::formRule($fields, CRM_Core_DAO::$_nullArray, CRM_Core_DAO::$_nullObject);

    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Function to build location block.
   */
  public function buildQuickForm() {
    //load form for child blocks
    if ($this->_addBlockName) {
      $className = "CRM_Contact_Form_Edit_{$this->_addBlockName}";
      return $className::buildQuickForm($this);
    }

    $this->applyFilter('__ALL__', 'trim');

    //build location blocks.
    CRM_Contact_Form_Location::buildQuickForm($this);

    //fix for CRM-1971
    $this->assign('action', $this->_action);

    if ($this->_id) {
      $this->_oldLocBlockId = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event',
        $this->_id, 'loc_block_id'
      );
    }

    // get the list of location blocks being used by other events

    $locationEvents = CRM_Event_BAO_Event::getLocationEvents();
    // remove duplicates and make sure that the duplicate entry with key as
    // loc_block_id of this event (this->_id) is preserved
    if (!empty($locationEvents[$this->_oldLocBlockId])) {
      $possibleDuplicate = $locationEvents[$this->_oldLocBlockId];
      $locationEvents = array_flip(array_unique($locationEvents));
      if (!empty($locationEvents[$possibleDuplicate])) {
        $locationEvents[$possibleDuplicate] = $this->_oldLocBlockId;
      }
      $locationEvents = array_flip($locationEvents);
    }
    else {
      $locationEvents = array_unique($locationEvents);
    }

    $events = array();
    if (!empty($locationEvents)) {
      $this->assign('locEvents', TRUE);
      $optionTypes = array(
        '1' => ts('Create new location'),
        '2' => ts('Use existing location'),
      );

      $this->addRadio('location_option', ts("Choose Location"), $optionTypes);

      if (!isset($locationEvents[$this->_oldLocBlockId]) || (!$this->_oldLocBlockId)) {
        $locationEvents = array('' => ts('- select -')) + $locationEvents;
      }
      $this->add('select', 'loc_event_id', ts('Use Location'), $locationEvents);
    }
    $this->addElement('advcheckbox', 'is_show_location', ts('Show Location?'));
    parent::buildQuickForm();
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    $params = $this->exportValues();
    $deleteOldBlock = FALSE;

    // if 'use existing location' option is selected -
    if (CRM_Utils_Array::value('location_option', $params) == 2 && !empty($params['loc_event_id']) &&
        ($params['loc_event_id'] != $this->_oldLocBlockId)
    ) {
      // if new selected loc is different from old loc, update the loc_block_id
      // so that loc update would affect the selected loc and not the old one.
      $deleteOldBlock = TRUE;
      CRM_Core_DAO::setFieldValue('CRM_Event_DAO_Event', $this->_id,
        'loc_block_id', $params['loc_event_id']
      );
    }

    // if 'create new loc' option is selected, set the loc_block_id for this event to null
    // so that an update would result in creating a new loc.
    if ($this->_oldLocBlockId && (CRM_Utils_Array::value('location_option', $params) == 1)) {
      $deleteOldBlock = TRUE;
      CRM_Core_DAO::setFieldValue('CRM_Event_DAO_Event', $this->_id,
        'loc_block_id', 'null'
      );
    }

    // if 'create new loc' optioin is selected OR selected new loc is different
    // from old one, go ahead and delete the old loc provided thats not being
    // used by any other event
    if ($this->_oldLocBlockId && $deleteOldBlock) {
      CRM_Event_BAO_Event::deleteEventLocBlock($this->_oldLocBlockId, $this->_id);
    }

    // get ready with location block params
    $params['entity_table'] = 'civicrm_event';
    $params['entity_id'] = $this->_id;

    $defaultLocationType = CRM_Core_BAO_LocationType::getDefault();
    foreach (array(
      'address',
      'phone',
      'email',
    ) as $block) {
      if (empty($params[$block]) || !is_array($params[$block])) {
        continue;
      }
      foreach ($params[$block] as $count => & $values) {
        if ($count == 1) {
          $values['is_primary'] = 1;
        }
        $values['location_type_id'] = ($defaultLocationType->id) ? $defaultLocationType->id : 1;
      }
    }

    // create/update event location
    $location = CRM_Core_BAO_Location::create($params, TRUE, 'event');
    $params['loc_block_id'] = $location['id'];

    // finally update event params
    $params['id'] = $this->_id;
    CRM_Event_BAO_Event::add($params);

    // Update tab "disabled" css class
    $this->ajaxResponse['tabValid'] = TRUE;
    parent::endPostProcess();
  }

  /**
   * Return a descriptive name for the page, used in wizard header
   *
   * @return string
   */
  public function getTitle() {
    return ts('Event Location');
  }

}
