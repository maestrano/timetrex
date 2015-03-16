<?php

require_once 'BaseMapper.php';
require_once 'MnoIdMap.php';

/**
* Map Connec Company representation to/from TimeTrex Company
*/
class CompanyMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'Company';
    $this->local_entity_name = 'Company';
    $this->connec_resource_name = 'company';
    $this->connec_resource_endpoint = 'company';
  }

  public function getId($company) {
    return $company->getId();
  }

  // Find by local id
  public function loadModelById($local_id) {
    $clf = TTnew('CompanyListFactory');
    $clf->getById($local_id);
    return $clf->getCurrent();
  }

  // Return the first Company
  protected function matchLocalModel($company_hash) {
    return CompanyMapper::getDefaultCompany();
  }

  // Map the Connec resource attributes onto the TimeTrex Company
  protected function mapConnecResourceToModel($company_hash, $company) {
    // Map hash attributes to Company

    // Default values
    if($company->isNew()) {
      $company->setStatus(10); //Active
      $company->setProductEdition(getTTProductEdition());
      $company->setEnableAddCurrency(FALSE);
      $company->setSetupComplete(TRUE);
    }

    // Company name
    if($this->is_set($company_hash['name'])) { $company->setName($company_hash['name']); }
    if($this->is_set($company_hash['employer_id'])) { $company->setBusinessNumber($company_hash['employer_id']); }
    
    // Map the industry type
    if(!is_null($company_hash['industry'])) {
      $industries = $company->getOptions('industry');
      $industry_id = array_search($company_hash['industry'], $industries);
      if($this->is_set($industry_id)) { $company->setIndustry($industry_id); }
    }

    // Address
    if(!is_null($company_hash['address']) && !is_null($company_hash['address']['billing'])) {
      if($this->is_set($company_hash['address']['billing']['line1'])) { $company->setAddress1($company_hash['address']['billing']['line1']); }
      if($this->is_set($company_hash['address']['billing']['line2'])) { $company->setAddress2($company_hash['address']['billing']['line2']); }
      if($this->is_set($company_hash['address']['billing']['city'])) { $company->setCity($company_hash['address']['billing']['city']); }
      if($this->is_set($company_hash['address']['billing']['postal_code'])) { $company->setPostalCode($company_hash['address']['billing']['postal_code']); }
      if($this->is_set($company_hash['address']['billing']['country'])) { $company->setCountry($company_hash['address']['billing']['country']); }
      if($this->is_set($company_hash['address']['billing']['region'])) { $company->setProvince($company_hash['address']['billing']['region']); }
    }

    // Phone
    if(!is_null($company_hash['phone'])) {
      if($this->is_set($company_hash['phone']['landline'])) { $company->setWorkPhone($company_hash['phone']['landline']); }
      if($this->is_set($company_hash['phone']['fax'])) { $company->setFaxPhone($company_hash['phone']['fax']); }
    }

    // Logo
    if(!is_null($company_hash['logo']) && $this->is_set($company_hash['logo']['logo'])) {
      $allowed_upload_content_types = array(FALSE, 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png');
      $company->cleanStoragePath($company->getId());
      $dir = $company->getStoragePath($company->getId());
      $max_upload_file_size = 5000000;

      // Save logo into company storage directory
      if(isset($dir)) {
        @mkdir($dir, 0700, TRUE);

        $file_name = $dir . DIRECTORY_SEPARATOR . 'logo.img';
        $file_data = file_get_contents("http:" . $company_hash['logo']['logo']);
        $file_size = strlen($file_data);

        // Verify mime type is accepted
        if(in_array(Misc::getMimeType($file_data, TRUE), $allowed_upload_content_types)) {
          if($file_size <= $max_upload_file_size) {
            // Write logo content
            $success = file_put_contents($file_name, $file_data);
            if($success == FALSE) {
              error_log("cannot save logo entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=Unable to write data to: ". $file_name);
            }
          } else {
            error_log("cannot save logo entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=File too large: ". $file_size);
          }
        } else {
          error_log("cannot save logo entity_name=$this->connec_entity_name, entity_id=" . $resource_hash['id'] . ", error=Incorrect mime_type");
        }
      }
    }
  }

  // Map the TimeTrex Company to a Connec resource hash
  protected function mapModelToConnecResource($company) {
    $company_hash = array();

    // Map Company to Connec hash
    if($company->getName()) { $company_hash['name'] = $company->getName(); }
    if($company->getBusinessNumber()) { $company_hash['employer_id'] = $company->getBusinessNumber(); }
    if($company->getIndustry()) {
      $industries = $company->getOptions('industry');
      $industry = $industries[$company->getIndustry()];
      $company_hash['industry'] = $industry;
    }

    // Address
    if($company->getAddress1()) { $company_hash['address']['billing']['line1'] = $company->getAddress1(); }
    if($company->getAddress2()) { $company_hash['address']['billing']['line2'] = $company->getAddress2(); }
    if($company->getCity()) { $company_hash['address']['billing']['city'] = $company->getCity(); }
    if($company->getPostalCode()) { $company_hash['address']['billing']['postal_code'] = $company->getPostalCode(); }
    if($company->getCountry()) { $company_hash['address']['billing']['country'] = $company->getCountry(); }
    if($company->getProvince()) { $company_hash['address']['billing']['region'] = $company->getProvince(); }

    // Phone
    if($company->getWorkPhone()) { $company_hash['phone']['landline'] = $company->getWorkPhone(); }
    if($company->getFaxPhone()) { $company_hash['phone']['fax'] = $company->getFaxPhone(); }

    return $company_hash;
  }

  // Persist the TimeTrex Company
  protected function persistLocalModel($company, $resource_hash) {
    $company->Save(true, false, false);
  }

  // Returns the first company available
  public static function getDefaultCompany() {
    $clf = TTnew('CompanyListFactory');
    $clf->getAll(1);
    foreach ($clf as $company) {
      $company_id = $company->getId();
      $clf->getById($company_id);
      return $clf->getCurrent();
    }
    return null;
  }

  // Returns the company default currency
  public static function getDefaultCurrency() {
    $company_id = CompanyMapper::getDefaultCompany()->getId();
    $clf = TTnew('CurrencyListFactory');
    $clf->getByCompanyIdAndDefault($company_id, true);
    foreach ($clf as $currency) {
      $currency_id = $currency->getId();
      $clf->getById($currency_id);
      return $clf->getCurrent();
    }

    // Create default currency
    $cf = TTnew('CurrencyFactory');
    $cf->setCompany($company_id);
    $cf->setStatus(10);
    $cf->setName('US Dollar');
    $cf->setISOCode('USD');
    $cf->setConversionRate('1.000000000');
    $cf->setAutoUpdate(false);
    $cf->setBase(true);
    $cf->setDefault(true);
    $currency_id = $cf->Save();

    $clf->getById($currency_id);
    return $clf->getCurrent();
  }
}
