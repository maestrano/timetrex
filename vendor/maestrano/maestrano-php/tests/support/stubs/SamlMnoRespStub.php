<?php

/**
 * Parse the SAML response and maintain the XML for it.
 */
class SamlMnoRespStub extends Maestrano_Saml_Response
{
    public function __construct()
    {
  		$this->cachedAttributes = array();
		  $date = new DateTime();
      
  		$this->cachedAttributes["mno_session"] = "7ds8f9789a7fd7x0b898bvb8vc9h0gg";
  		$this->cachedAttributes["mno_session_recheck"] = $date->format(DateTime::ISO8601);
  		$this->cachedAttributes["group_uid"] = "cld-1";
  		$this->cachedAttributes["group_name"] = "SomeGroupName";
  		$this->cachedAttributes["group_email"] = "email@example.com";
  		$this->cachedAttributes["group_role"] = "Admin";
  		$this->cachedAttributes["group_end_free_trial"] = $date->format(DateTime::ISO8601);
  		$this->cachedAttributes["group_has_credit_card"] = "true";
  		$this->cachedAttributes["group_currency"] = "USD";
  		$this->cachedAttributes["group_timezone"] = "America/Los_Angeles";
  		$this->cachedAttributes["group_country"] = "US";
  		$this->cachedAttributes["group_city"] = "Los Angeles";
  		$this->cachedAttributes["uid"] = "usr-1";
  		$this->cachedAttributes["virtual_uid"] = "user-1.cld-1";
  		$this->cachedAttributes["email"] = "j.doe@doecorp.com";
  		$this->cachedAttributes["virtual_email"] = "user-1.cld-1@mail.maestrano.com";
  		$this->cachedAttributes["name"] = "John";
  		$this->cachedAttributes["surname"] = "Doe";
  		$this->cachedAttributes["country"] = "AU";
  		$this->cachedAttributes["company_name"] = "DoeCorp";
    }
}
