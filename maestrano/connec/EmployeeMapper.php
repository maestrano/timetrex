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
    $this->local_entity_name = 'User';
    $this->connec_resource_name = 'employees';
    $this->connec_resource_endpoint = 'employees';
  }

  public function getId($employee) {
    return $employee->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $ulf = new UserListFactory();
    $ulf->getById($local_id);
    return $ulf->getCurrent();
  }

  // Match by Email
  protected function matchLocalModel($employee_hash) {
    if($this->is_set($employee_hash['email']['address'])) {
      $ulf = new UserListFactory();
      $ulf->getByHomeEmailOrWorkEmail($employee_hash['email']['address']);
      return $ulf->getCurrent();
    }
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex Employee
  protected function mapConnecResourceToModel($employee_hash, $employee) {
    // Map hash attributes to Employee

    // Set default values
    $company = $employee->getCompanyObject();
    if(!$company) {
      $company = CompanyMapper::getDefaultCompany();
      $employee->setCompany($company->getId());
      $employee->setStatus(10); //Active
    }

    if(!$employee->getEmployeeNumber()) {
      if($this->is_set($employee_hash['employee_id'])) {
        $employee->setEmployeeNumber($employee_hash['employee_id']);
      } else {
        // Employee Number is mandatory, get the next available or default to code
        $employee_id = UserFactory::getNextAvailableEmployeeNumber($company->getId());
        if(is_null($employee_id)) { $employee_id = $employee_hash['code']; }
        $employee->setEmployeeNumber($employee_id);
      }
    }
    if(!$employee->getCurrency()) {
      $employee->setCurrency(CompanyMapper::getDefaultCurrency()->getId());
    }
    
    if(!$employee->getUserName()) {
      $employee->setUserName(strtolower($employee_hash['first_name']) . '.' . strtolower($employee_hash['last_name']));
    }
    if(!$employee->getPassword()) {
      $employee->setPassword($this->generatePassword());
    }

    if($this->is_set($employee_hash['first_name'])) { $employee->setFirstName($employee_hash['first_name']); }
    if($this->is_set($employee_hash['last_name'])) { $employee->setLastName($employee_hash['last_name']); }
    
    if($this->is_set($employee_hash['email']['address'])) { $employee->setWorkEmail($employee_hash['email']['address']); }
    if($this->is_set($employee_hash['email']['address2'])) { $employee->setHomeEmail($employee_hash['email']['address2']); }

    if($this->is_set($employee_hash['address']['shipping'])) {
      if($this->is_set($employee_hash['address']['shipping']['country'])) { $employee->setCountry($employee_hash['address']['shipping']['country']); }
      if($this->is_set($employee_hash['address']['shipping']['region'])) { $employee->setProvince($employee_hash['address']['shipping']['region']); }
      if($this->is_set($employee_hash['address']['shipping']['line1'])) { $employee->setAddress1($employee_hash['address']['shipping']['line1']); }
      if($this->is_set($employee_hash['address']['shipping']['line2'])) { $employee->setAddress2($employee_hash['address']['shipping']['line2']); }
      if($this->is_set($employee_hash['address']['shipping']['city'])) { $employee->setCity($employee_hash['address']['shipping']['city']); }
      if($this->is_set($employee_hash['address']['shipping']['postal_code'])) { $employee->setPostalCode($employee_hash['address']['shipping']['postal_code']); }
    }

    if($this->is_set($employee_hash['phone'])) {
      if($this->is_set($employee_hash['phone']['landline'])) { $employee->setWorkPhone($employee_hash['phone']['landline']); }
      if($this->is_set($employee_hash['phone']['landline2'])) { $employee->setHomePhone($employee_hash['phone']['landline2']); }
      if($this->is_set($employee_hash['phone']['mobile'])) { $employee->setMobilePhone($employee_hash['phone']['mobile']); }
      if($this->is_set($employee_hash['phone']['fax'])) { $employee->setFaxPhone($employee_hash['phone']['fax']); }
    }

    if(!$this->is_set($employee_hash['gender'])) {
      $employee->setSex(5);
    } else if($employee_hash['gender'] == 'M') {
      $employee->setSex(10);
    } else {
      $employee->setSex(20);
    }

    if($this->is_set($employee_hash['birth_date'])) { $employee->setBirthDate(strtotime($employee_hash['birth_date'])); }
    if($this->is_set($employee_hash['hired_date'])) { $employee->setHireDate(strtotime($employee_hash['hired_date'])); }
    if($this->is_set($employee_hash['released_date'])) { $employee->setTerminationDate(strtotime($employee_hash['released_date'])); }

    if($this->is_set($employee_hash['social_security_number']) && !$this->startsWith($employee_hash['social_security_number'], '***')) {
      $employee->setSIN($employee_hash['ssn']);
    }

    if($this->is_set($employee_hash['note'])) { $employee->setNote($employee_hash['note']); }
    if($this->is_set($employee_hash['job_title'])) { $employee->setTitle($this->findOrCreateTitle($company, $employee_hash['job_title'])); }
    
    // TODO
    // 'default_branch_id' => 'DefaultBranch',
    // 'default_department_id' => 'DefaultDepartment',
    // 'default_job_id' => 'DefaultJob',
    // 'default_job_item_id' => 'DefaultJobItem',
    // 'pay_period_schedule_id' => 'PayPeriodSchedule',
    // 'policy_group_id' => 'PolicyGroup',
  }

  // Map the TimeTrex Employee to a Connec resource hash
  protected function mapModelToConnecResource($employee) {
    $employee_hash = array();

    if($employee->getFirstName()) { $employee_hash['first_name'] = $employee->getFirstName(); }
    if($employee->getLastName()) { $employee_hash['last_name'] = $employee->getLastName(); }
    if($employee->getFullName()) { $employee_hash['full_name'] = $employee->getFullName(); }

    if($employee->getWorkEmail()) { $employee_hash['email']['address'] = $employee->getWorkEmail(); }
    if($employee->getHomeEmail()) { $employee_hash['email']['address2'] = $employee->getHomeEmail(); }

    if($employee->getCountry()) { $employee_hash['address']['shipping']['country'] = $employee->getCountry(); }
    if($employee->getProvince()) { $employee_hash['address']['shipping']['region'] = $employee->getProvince(); }
    if($employee->getAddress1()) { $employee_hash['address']['shipping']['line1'] = $employee->getAddress1(); }
    if($employee->getAddress2()) { $employee_hash['address']['shipping']['line2'] = $employee->getAddress2(); }
    if($employee->getCity()) { $employee_hash['address']['shipping']['city'] = $employee->getCity(); }
    if($employee->getPostalCode()) { $employee_hash['address']['shipping']['postal_code'] = $employee->getPostalCode(); }

    if($employee->getWorkPhone()) { $employee_hash['phone']['landline'] = $employee->getWorkPhone(); }
    if($employee->getHomePhone()) { $employee_hash['phone']['landline2'] = $employee->getHomePhone(); }
    if($employee->getMobilePhone()) { $employee_hash['phone']['mobile'] = $employee->getMobilePhone(); }
    if($employee->getFaxPhone()) { $employee_hash['phone']['fax'] = $employee->getFaxPhone(); }

    if($employee->getSex()) {
      if($employee->getSex() == 10) {
        $employee_hash['gender'] = 'M';
      } else if($employee->getSex() == 20) {
        $employee_hash['gender'] = 'F';
      }
    }

    if($employee->getBirthDate()) { $employee_hash['birth_date'] = date('Y-m-d', $employee->getBirthDate()); }
    if($employee->getHireDate()) { $employee_hash['hired_date'] = date('Y-m-d', $employee->getHireDate()); }
    if($employee->getTerminationDate()) { $employee_hash['released_date'] = date('Y-m-d', $employee->getTerminationDate()); }

    if($employee->getSIN()) { $employee_hash['social_security_number'] = $employee->getSIN(); }
    if($employee->getNote()) { $employee_hash['note'] = $employee->getNote(); }
    if($employee->getTitle()) { $employee_hash['job_title'] = $employee->getTitleObject()->getName(); }

    // EmployeeSalary
    $employee_hash['employee_salaries'] = array();
    $employeeSalaryMapper = new EmployeeSalaryMapper($employee->getId());
    $uwlf = TTnew('UserWageListFactory');
    $employee_salaries = $uwlf->getByUserId($employee->getId());
    foreach ($employee_salaries as $employee_salary) {
      $employee_hash['employee_salaries'][] = $employeeSalaryMapper->mapModelToConnecResource($employee_salary);
    }

    return $employee_hash;
  }

  // Persist the TimeTrex Employee
  protected function persistLocalModel($employee, $employee_hash) {
    $employee_id = $employee->Save(false, false, false);

    // Employee Salary
    if($employee_id && !is_null($employee_hash['employee_salaries']) && !empty($employee_hash['employee_salaries'])) {
      $employeeSalaryMapper = new EmployeeSalaryMapper($employee_id);
      foreach ($employee_hash['employee_salaries'] as $employee_salary_hash) {
        $employee_salary = $employeeSalaryMapper->saveConnecResource($employee_salary_hash, true);
      }
    }
  }

  private function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }

  private function findOrCreateTitle($company, $job_title) {
    $utlf = TTNew('UserTitleListFactory');
    $utlf->getByCompanyId($company->getId());
    $title_options = (array)$utlf->getArrayByListFactory($utlf, FALSE, TRUE);

    // Find existing Job Title
    $title = Misc::findClosestMatch($job_title, $title_options, 100);
    if(!$title === FALSE) { return $title; }

    // Or create a new one
    $utf = TTnew('UserTitleFactory');
    $utf->setCompany($company->getId());
    $utf->setName($job_title);
    $title = $utf->Save();

    return $title;
  }

  private function generatePassword() {
    $length = 20;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }
}
