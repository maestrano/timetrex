<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec WorkLocation representation to/from TimeTrex WorkLocation
*/
class WorkLocationMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'WorkLocation';
    $this->local_entity_name = 'Branch';
    $this->connec_resource_name = 'work_locations';
    $this->connec_resource_endpoint = 'work_locations';
  }

  public function getId($branch) {
    return $branch->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $blf = new BranchListFactory();
    $blf->getById($local_id);
    return $blf->getCurrent();
  }

  // Match by Name
  protected function matchLocalModel($work_location_hash) {
    if($this->is_set($work_location_hash['name'])) {
      $company = CompanyMapper::getDefaultCompany();
      $blf = new BranchListFactory();
      $branches = $blf->getByCompanyId($company->getId());
      foreach ($branches as $branch) {
        if($branch->getName() == $work_location_hash['name']) { return $branch; }
      }
    }
    return null;
  }

  // Map the Connec resource attributes onto the TimeTrex WorkLocation
  protected function mapConnecResourceToModel($work_location_hash, $work_location) {
    // Map hash attributes to WorkLocation

    // Set default values
    $company = $work_location->getCompany();
    if(!$company) {
      $company = CompanyMapper::getDefaultCompany();
      $work_location->setCompany($company->getId());
      $work_location->setStatus(10); //Active
    }

    // Map address shipping otherwise billing
    $address = null;
    if(array_key_exists('address', $work_location_hash)) {
      if(array_key_exists('shipping', $work_location_hash['address'])) {
        $address = $work_location_hash['address']['shipping'];
      } else if(array_key_exists('billing', $work_location_hash['address'])) {
        $address = $work_location_hash['address']['billing'];
      }
    }
    if(!is_null($address)) {
      if($this->is_set($address['country'])) { $work_location->setCountry($address['country']); }
      if($this->is_set($address['region'])) { $work_location->setProvince($address['region']); }
      if($this->is_set($address['line1'])) { $work_location->setAddress1($address['line1']); }
      if($this->is_set($address['line2'])) { $work_location->setAddress2($address['line2']); }
      if($this->is_set($address['city'])) { $work_location->setCity($address['city']); }
      if($this->is_set($address['postal_code'])) { $work_location->setPostalCode($address['postal_code']); }
    }

    if($this->is_set($work_location_hash['phone'])) {
      if($this->is_set($work_location_hash['phone']['landline'])) { $work_location->setWorkPhone($work_location_hash['phone']['landline']); }
      if($this->is_set($work_location_hash['phone']['fax'])) { $work_location->setFaxPhone($work_location_hash['phone']['fax']); }
    }

    if($this->is_set($work_location_hash['name'])) {
      $work_location->setName($work_location_hash['name']);
    } else {
      $work_location->setName($work_location->getCity() . '-' . $work_location->getCountry());
    }
  }

  // Map the TimeTrex WorkLocation to a Connec resource hash
  protected function mapModelToConnecResource($work_location) {
    $work_location_hash = array();

    // Address
    $work_location_hash['address'] = array('street' => array());
    if($work_location->getCountry()) { $work_location_hash['address']['street']['country'] = $work_location->getCountry(); }
    if($work_location->getProvince()) { $work_location_hash['address']['street']['region'] = $work_location->getProvince(); }
    if($work_location->getAddress1()) { $work_location_hash['address']['street']['line1'] = $work_location->getAddress1(); }
    if($work_location->getAddress2()) { $work_location_hash['address']['street']['line2'] = $work_location->getAddress2(); }
    if($work_location->getCity()) { $work_location_hash['address']['street']['city'] = $work_location->getCity(); }
    if($work_location->getPostalCode()) { $work_location_hash['address']['street']['postal_code'] = $work_location->getPostalCode(); }

    if($work_location->getWorkPhone()) { $work_location_hash['phone']['landline'] = $work_location->getWorkPhone(); }
    if($work_location->getFaxPhone()) { $work_location_hash['phone']['fax'] = $work_location->getFaxPhone(); }

    return $work_location_hash;
  }

  // Persist the TimeTrex WorkLocation
  protected function persistLocalModel($work_location, $resource_hash) {
    if($work_location->isValid()) {
      $work_location->Save(false, false, false);
    } else {
      error_log("cannot save entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=" . $work_location->Validator->getTextErrors());
    }
  }
}
