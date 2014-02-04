<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Parse NL and BE addresses',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Parse NL and BE addresses',
      'description' => 'Parse all addresses in NL and BE into Dutch address format',
      'api_entity' => 'AddressNl',
      'api_action' => 'Parse',
      'run_frequency' => 'Daily',
      'is_active' => 0,  
      'parameters' => '',
    ),
  ),
);