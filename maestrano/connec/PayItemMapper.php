<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec PayItem representation to/from TimeTrex PayStubEntryAccount
*/
class PayItemMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'PayItem';
    $this->local_entity_name = 'PayStubEntryAccount';
    $this->connec_resource_name = 'pay_items';
    $this->connec_resource_endpoint = 'pay_items';
  }

  public function getId($pay_stub_entry) {
    return $pay_stub_entry->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $pse = new PayStubEntryAccountListFactory();
    $pse->getById($local_id);
    return $pse->getCurrent();
  }

  // Match by Name
  protected function matchLocalModel($pay_item_hash) {
    if($this->is_set($pay_item_hash['name'])) {
      $company = CompanyMapper::getDefaultCompany();
      $pse = new PayStubEntryAccountListFactory();
      $pay_stub_entrys = $pse->getByCompanyId($company->getId());
      foreach ($pay_stub_entrys as $pay_stub_entry) {
        if($pay_stub_entry->getName() == $pay_item_hash['name']) { return $pay_stub_entry; }
      }
    }
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex PayStubEntryAccount
  protected function mapConnecResourceToModel($pay_item_hash, $pay_stub_entry) {
    // Map hash attributes to PayStubEntryAccount

    // Set default values
    $company = $pay_stub_entry->getCompany();
    if(!$company) {
      $company = CompanyMapper::getDefaultCompany();
      $pay_stub_entry->setCompany($company->getId());
    }

    if(!$pay_stub_entry->getStatus()) { $pay_stub_entry->setStatus(10); }
    if(!$pay_stub_entry->getType()) { $pay_stub_entry->setType(10); }
    if($this->is_set($pay_item_hash['type'])) {
      switch ($pay_item_hash['type']) {
        case "TIMEOFF":
          $pay_stub_entry->setType(10);
          break;
        case "REIMBURSEMENT":
          $pay_stub_entry->setType(10);
          break;
        case "DEDUCTION":
          $pay_stub_entry->setType(20);
          break;
        case "BENEFIT":
          $pay_stub_entry->setType(30);
          break;
        case "EARNINGS":
          $pay_stub_entry->setType(10);
          break;
      }
    }

    if($this->is_set($pay_item_hash['name'])) { $pay_stub_entry->setName($pay_item_hash['name']); }
    if(!$pay_stub_entry->getOrder()) { $pay_stub_entry->setOrder(10 * $pay_stub_entry->getType()); }
    if(!$pay_stub_entry->getAccrualType()) { $pay_stub_entry->setAccrualType(10); }
  }

  // Map the TimeTrex PayStubEntryAccount to a Connec resource hash
  protected function mapModelToConnecResource($pay_stub_entry) {
    $pay_item_hash = array();

    if($pay_stub_entry->getName()) { $pay_item_hash['name'] = $pay_stub_entry->getName(); }

    if($pay_stub_entry->getType()) {
      switch ($pay_stub_entry->getType()) {
        case 10:
          $pay_item_hash['name'] = 'EARNINGS';
          break;
        case 20:
          $pay_item_hash['name'] = 'DEDUCTION';
          break;
        case 30:
          $pay_item_hash['name'] = 'BENEFIT';
          break;
      }
    }

    return $pay_item_hash;
  }

  // Persist the TimeTrex PayStubEntryAccount
  protected function persistLocalModel($pay_stub_entry, $resource_hash) {
    $local_id = $pay_stub_entry->Save(false, false, false);

    // Create a default PayCode if none exists
    $pay_code = $this->getPayCodeByPayStubEntry($local_id) ;
    if(is_null($pay_code)) {
      $pay_code = TTnew('PayCodeListFactory');
      $pay_code->setCompany($pay_stub_entry->getCompany());
      $pay_code->setName($pay_stub_entry->getName());
      $pay_code->setPayStubEntryAccountId($local_id);
      $pay_code->setCode(strtoupper($pay_stub_entry->getName()));

      if(!is_null($resource_hash['category']) && $resource_hash['category'] == 'UNPAID') {
        $pay_code->setType(20);
      } else {
        $pay_code->setType(10);
      }

      $pay_code->Save();
    }
  }

  private function getPayCodeByPayStubEntry($pay_stub_entry_account_id) {
    $company = CompanyMapper::getDefaultCompany();
    $pcl = new PayCodeListFactory();
    $pay_codes = $pcl->getByCompanyId($company->getId());
    foreach ($pay_codes as $pay_code) {
      if($pay_code->getPayStubEntryAccountId() == $pay_stub_entry_account_id) { return $pay_code; }
    }
    return null;
  }
}
