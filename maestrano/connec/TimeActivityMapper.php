<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec TimeActivity representation to/from TimeTrex PunchControl
*/
class TimeActivityMapper extends BaseMapper {
  private $punches = array();

  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'TimeActivity';
    $this->local_entity_name = 'PunchControl';
    $this->connec_resource_name = 'time_activities';
    $this->connec_resource_endpoint = 'time_activities';
  }

  public function getId($punch_control) {
    return $punch_control->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $pcl = new PunchControlListFactory();
    $pcl->getById($local_id);
    return $pcl->getCurrent();
  }

  // Match by User and Timestamp
  protected function matchLocalModel($time_activity_hash) {
    $employee_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($time_activity_hash['employee_id'], 'Employee');
    if($employee_id_map) {
      $employee_id = intval($employee_id_map['app_entity_id']);
      $date_stamp = strtotime($time_activity_hash['transaction_date']);
      $pclf = new PunchControlListFactory();
      $pclf->getByUserIdAndDateStamp($employee_id, $date_stamp);
      return $pclf->getCurrent();
    }
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex PunchControl
  protected function mapConnecResourceToModel($time_activity_hash, $punch_control) {
    // Map hash attributes to PunchControl

    if($punch_control->isNew()) {
      $punch_control->setEnableCalcUserDateID( TRUE );
      $punch_control->setEnableCalcTotalTime( $calc_total_time );
      $punch_control->setEnableCalcSystemTotalTime( $calc_total_time );
      $punch_control->setEnableCalcWeeklySystemTotalTime( $calc_total_time );
      $punch_control->setEnableCalcUserDateTotal( $calc_total_time );
      $punch_control->setEnableCalcException( $calc_total_time );
    }

    // Map Employee
    $employee_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($time_activity_hash['employee_id'], 'Employee');
    if($employee_id_map) { $punch_control->setUser(intval($employee_id_map['app_entity_id'])); }

    // Map date
    $punch_control->setDateStamp(strtotime($time_activity_hash['transaction_date']));

    // TODO
    // $punch_control->setBranch();
    // $punch_control->setDepartment();
    // $punch_control->setJob();
    // $punch_control->setJobItem();
    // $punch_control->setQuantity();
    // $punch_control->setBadQuantity();
  }

  // Map the TimeTrex PunchControl to a Connec resource hash
  protected function mapModelToConnecResource($punch_control) {
    $time_activity_hash = array();

    return $time_activity_hash;
  }

  // Persist the TimeTrex PunchControl
  protected function persistLocalModel($punch_control, $time_activity_hash) {
    // Save PunchControl

    if(!$punch_control->isNew()) { $punch_control_id = $punch_control->getId(); }
    $local_id = $punch_control->Save(false, false);
    if(is_null($punch_control_id)) { $punch_control_id = $local_id; }

    // Create/Update Punch In and Punch Out

    // Punch In
    $pf_in = new PunchListFactory();
    if(!$punch_control->isNew()) {
      $pf_in->getByPunchControlIdAndStatusId($punch_control_id, 10);
      $pf_in = $pf_in->getCurrent();
    }

    $pf_in->setTransfer(false);
    $pf_in->setType(10);
    $pf_in->setStatus(10);
    
    // Set time activity start time or default to 8:00am
    $start_date = $this->is_set($time_activity_hash['start_time']) ? strtotime($time_activity_hash['start_time']) : $punch_control->getDateStamp() - (4*60*60);
    $pf_in->setTimeStamp($start_date);
    $pf_in->setActualTimeStamp($pf_in->getTimeStamp());
    $pf_in->setOriginalTimeStamp($pf_in->getTimeStamp());
    $pf_in->setPunchControlID($punch_control_id);
    $pf_in->Save();

    // Punch Out
    $pf_out = new PunchListFactory();
    if(!$punch_control->isNew()) {
      $pf_out->getByPunchControlIdAndStatusId($punch_control_id, 20);
      $pf_out = $pf_out->getCurrent();
    }

    $pf_out->setTransfer(false);
    $pf_out->setType(10);
    $pf_out->setStatus(20);
    
    // Set time activity end time or default to start_time + duration
    $hours = ($this->is_set($time_activity_hash['hours']) ? intval($time_activity_hash['hours']) : 0);
    $minutes = ($this->is_set($time_activity_hash['minutes']) ? intval($time_activity_hash['minutes']) : 0);
    $end_date = $this->is_set($time_activity_hash['end_time']) ? strtotime($time_activity_hash['end_time']) : $start_date + ($hours*60*60) + ($minutes*60);
    $pf_out->setTimeStamp($end_date);
    $pf_out->setActualTimeStamp($pf_out->getTimeStamp());
    $pf_out->setOriginalTimeStamp($pf_out->getTimeStamp());
    $pf_out->setPunchControlID($punch_control_id);
    $pf_out->Save();

    // Calculate total time
    $pcl = new PunchControlListFactory();
    $pcl->getById($punch_control_id);
    $pcl = $pcl->getCurrent();
    $pcl->calcTotalTime();
    $pcl->Save(false, false);
  }
}
