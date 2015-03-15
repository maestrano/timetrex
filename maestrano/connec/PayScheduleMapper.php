<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec PaySchedule representation to/from TimeTrex PayPeriodSchedule
*/
class PayScheduleMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'PaySchedule';
    $this->local_entity_name = 'PayPeriodSchedule';
    $this->connec_resource_name = 'pay_schedules';
    $this->connec_resource_endpoint = 'pay_schedules';
  }

  public function getId($pay_period_schedule) {
    return $pay_period_schedule->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $blf = new PayPeriodScheduleListFactory();
    $blf->getById($local_id);
    return $blf->getCurrent();
  }

  // Map the Connec resource attributes onto the TimeTrex PaySchedule
  protected function mapConnecResourceToModel($pay_schedule_hash, $pay_schedule) {
    // Map hash attributes to PaySchedule

    // Set default values
    $company = $pay_schedule->getCompany();
    if(!$company) {
      $company = CompanyMapper::getDefaultCompany();
      $pay_schedule->setCompany($company->getId());
    }

    if(!$pay_schedule->getTransactionDateBusinessDay()) { $pay_schedule->setTransactionDateBusinessDay(true); }
    if(!$pay_schedule->getDayStartTime()) { $pay_schedule->setDayStartTime(0); }
    if(!$pay_schedule->getNewDayTriggerTime()) { $pay_schedule->setNewDayTriggerTime(4 * 3600); }
    if(!$pay_schedule->getMaximumShiftTime()) { $pay_schedule->setMaximumShiftTime(16 * 3600); }

    if($this->is_set($pay_schedule_hash['name'])) { $pay_schedule->setName($pay_schedule_hash['name']); }
    if($this->is_set($pay_schedule_hash['description'])) { $pay_schedule->setDescription($pay_schedule_hash['description']); }
    if($this->is_set($pay_schedule_hash['start_date'])) { $pay_schedule->setAnchorDate(strtotime($pay_schedule_hash['start_date'])); }

    if($this->is_set($pay_schedule_hash['schedule_type'])) {
      if($pay_schedule_hash['schedule_type'] == 'WEEKLY') {
        $pay_schedule->setType(10);
      } else if($pay_schedule_hash['schedule_type'] == 'BIWEEKLY') {
        $pay_schedule->setType(20);
      } else if($pay_schedule_hash['schedule_type'] == 'MONTHLY') {
        $pay_schedule->setType(50);
      } else if($pay_schedule_hash['schedule_type'] == 'SEMIMONTHLY') {
        $pay_schedule->setType(30);
      }
    }

    if($this->is_set($pay_schedule_hash['first_week_day'])) { $pay_schedule->setStartDayOfWeek($pay_schedule_hash['first_week_day']); }
    if($this->is_set($pay_schedule_hash['transaction_week_day'])) { $pay_schedule->setTransactionDate($pay_schedule_hash['transaction_week_day']); }
    if($this->is_set($pay_schedule_hash['shift_assigned_day'])) { $pay_schedule->setShiftAssignedDay($pay_schedule_hash['shift_assigned_day']); }

    if($this->is_set($pay_schedule_hash['first_month_day'])) { $pay_schedule->setPrimaryDayOfMonth($pay_schedule_hash['first_month_day']); }
    if($this->is_set($pay_schedule_hash['transaction_month_day'])) { $pay_schedule->setPrimaryTransactionDayOfMonth($pay_schedule_hash['transaction_month_day']); }
  }

  // Map the TimeTrex PaySchedule to a Connec resource hash
  protected function mapModelToConnecResource($pay_schedule) {
    $pay_schedule_hash = array();

    if($pay_schedule->getName()) { $pay_schedule_hash['name'] = $pay_schedule->getName(); }
    if($pay_schedule->getDescription()) { $pay_schedule_hash['description'] = $pay_schedule->getDescription(); }
    if($pay_schedule->getAnchorDate()) { $pay_schedule_hash['start_date'] = date('Y-m-d', $pay_schedule->getAnchorDate()); }
    
    if($pay_schedule->getType()) {
      if($pay_schedule->getType() == 10) { $pay_schedule_hash['schedule_type'] = 'WEEKLY'; }
      else if($pay_schedule->getType() == 20) { $pay_schedule_hash['schedule_type'] = 'BIWEEKLY'; }
      else if($pay_schedule->getType() == 30) { $pay_schedule_hash['schedule_type'] = 'SEMIMONTHLY'; }
      else if($pay_schedule->getType() == 50) { $pay_schedule_hash['schedule_type'] = 'MONTHLY'; }
    }

    if($pay_schedule->getStartDayOfWeek()) { $pay_schedule_hash['first_week_day'] = $pay_schedule->getStartDayOfWeek(); }
    if($pay_schedule->getTransactionDate()) { $pay_schedule_hash['transaction_week_day'] = $pay_schedule->getTransactionDate(); }
    if($pay_schedule->getShiftAssignedDay()) { $pay_schedule_hash['shift_assigned_day'] = $pay_schedule->getShiftAssignedDay(); }

    if($pay_schedule->getPrimaryDayOfMonth()) { $pay_schedule_hash['first_month_day'] = $pay_schedule->getPrimaryDayOfMonth(); }
    if($pay_schedule->getPrimaryTransactionDayOfMonth()) { $pay_schedule_hash['transaction_month_day'] = $pay_schedule->getPrimaryTransactionDayOfMonth(); }

    // Pay Schedule Employees
    if($pay_schedule->getUser()) {
      $pay_schedule_hash['employees'] = array();
      foreach ($pay_schedule->getUser() as $user_id) {
        $id_map = MnoIdMap::findMnoIdMapByLocalIdAndEntityName($user_id, 'Employee');
        if($id_map) { $pay_schedule_hash['employees'][] = array('id' => $id_map['mno_entity_guid']); }
      }
    }

    return $pay_schedule_hash;
  }

  // Persist the TimeTrex PaySchedule
  protected function persistLocalModel($pay_schedule, $resource_hash) {
    $insert_id = $pay_schedule->Save(false, false, false);

    // Map employees assigned to this pay schedule
    $user_ids = array();
    foreach ($resource_hash['employees'] as $employee_hash) {
      $id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($employee_hash['id'], 'User');
      if($id_map) { $user_ids[] = intval($id_map['app_entity_id']); }
    }

    // Dont create pay periods twice
    $pay_schedule->setEnableInitialPayPeriods(false);
    $pay_schedule->setUser($user_ids);
    $pay_schedule->Save(true, false, false);
    $pay_schedule->setId($insert_id);
  }
}
