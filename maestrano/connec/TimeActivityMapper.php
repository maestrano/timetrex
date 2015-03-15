<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec TimeActivity representation to/from TimeTrex Punch
*/
class TimeActivityMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'TimeActivity';
    $this->local_entity_name = 'Punch';
    $this->connec_resource_name = 'time_activities';
    $this->connec_resource_endpoint = 'time_activities';
  }

  public function getId($punch) {
    return $punch->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $pse = new PunchListFactory();
    $pse->getById($local_id);
    return $pse->getCurrent();
  }

  // TODO: Match by ?
  protected function matchLocalModel($time_activity_hash) {
    // if($this->is_set($time_activity_hash['name'])) {
    //   $company = CompanyMapper::getDefaultCompany();
    //   $pse = new PunchListFactory();
    //   $punchs = $pse->getByCompanyId($company->getId());
    //   foreach ($punchs as $punch) {
    //     if($punch->getName() == $time_activity_hash['name']) { return $punch; }
    //   }
    // }
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex Punch
  protected function mapConnecResourceToModel($time_activity_hash, $punch) {
    // Map hash attributes to Punch

    // Set default values
    if(!$punch->getTransfer()) { $punch->setTransfer(false); }
    if(!$punch->getType()) { $punch->setType(10); }
    if(!$punch->getStatus()) { $punch->setStatus(10); }
    if($punch->isNew()) { $punch->setPunchControlID($punch->findPunchControlID()); }
    
    // Map Employee
    $employee_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($time_activity_hash['employee_id'], 'Employee');
    if($employee_id_map) { $punch->setUser(intval($employee_id_map['app_entity_id'])); }

    // Map timestamps
    $punch->setTimeStamp(strtotime($time_activity_hash['transaction_date']));
    if($punch->isNew()) {
      $punch->setActualTimeStamp($punch->getTimeStamp());
      $punch->setOriginalTimeStamp($punch->getTimeStamp());
    }
  }

  // Map the TimeTrex Punch to a Connec resource hash
  protected function mapModelToConnecResource($punch) {
    $time_activity_hash = array();

    return $time_activity_hash;
  }

  // Persist the TimeTrex Punch
  protected function persistLocalModel($punch, $resource_hash) {
    $local_id = $punch->Save(false, false, false);

    // Create a PunchControl entry
    $pcf = TTnew('PunchControlFactory');
    $pcf->setId($punch->getPunchControlID());
    $pcf->setPunchObject($punch);
    $pcf->setEnableCalcUserDateID(true);
    $pcf->setEnableCalcTotalTime(true);
    $pcf->setEnableCalcSystemTotalTime(true);
    $pcf->setEnableCalcWeeklySystemTotalTime(true);
    $pcf->setEnableCalcUserDateTotal(true);
    $pcf->setEnableCalcException(true);

    if($pcf->isValid() == true) {
      $pcf->Save(true, true);
    } else {
      error_log("cannot save entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=" . $pcf->Validator->getTextErrors());
    }
  }
}
