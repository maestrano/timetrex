<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec Employee representation to/from TimeTrex Employee
*/
class EmployeeMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Employee';
    $this->local_entity_name = 'Employee';
    $this->connec_resource_name = 'employees';
    $this->connec_resource_endpoint = 'employees';
  }

  private function employeeFactory() {
    return TTnew('EmployeeFactory');
  }

  public function getId($employee) {
    return $employee->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $cf = $this->employeeFactory();
    return $cf->getByID($local_id);
  }

  // TODO
  protected function matchLocalModel($employee_hash) {
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex Employee
  protected function mapConnecResourceToModel($employee_hash, $employee) {
    // Map hash attributes to Employee

    if($this->is_set($employee_hash['first_name'])) { $employee->setFirstName($employee_hash['first_name']); }
    if($this->is_set($employee_hash['last_name'])) { $employee->setLastName($employee_hash['last_name']); }

    // TODO
  }

  // Map the TimeTrex Employee to a Connec resource hash
  protected function mapModelToConnecResource($employee) {
    $employee_hash = array();

    if($employee->getFirstName()) { $employee_hash['first_name'] = $employee->getFirstName(); }
    if($employee->getLastName()) { $employee_hash['last_name'] = $employee->getLastName(); }

    //TODO

    return $employee_hash;
  }

  // Persist the TimeTrex Employee
  protected function persistLocalModel($employee, $resource_hash) {
    if($employee->isValid()) {
      $employee->Save(true, false, false);
    } else {
      error_log("cannot save entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=" . $employee->Validator->getTextErrors());
    }
  }
}
