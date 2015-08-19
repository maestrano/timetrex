<?php

/**
 * Unit tests for AuthN Request
 */
class Maestrano_Sso_GroupTest extends PHPUnit_Framework_TestCase
{
    private $samlResp;
    private $subject;

    /**
    * Initializes the Test Suite
    */
    public function setUp()
    {
      Maestrano::configure(array('environment' => 'production'));
    	$this->samlResp = new SamlMnoRespStub();
    	$this->subject = new Maestrano_Sso_Group($this->samlResp);
    }


    public function testAttributeParsing() {
  		$att = $this->samlResp->getAttributes();

  		$this-> assertEquals($att["group_uid"], $this->subject->getUid());
  		$this-> assertEquals($att["group_name"], $this->subject->getName());
  		$this-> assertEquals($att["group_email"], $this->subject->getEmail());
  		$this-> assertEquals(new DateTime($att["group_end_free_trial"]), $this->subject->getFreeTrialEndAt());
  		$this-> assertEquals($att["company_name"], $this->subject->getCompanyName());
  		$this-> assertEquals(($att["group_has_credit_card"] == "true"), $this->subject->hasCreditCard());

  		$this-> assertEquals($att["group_currency"], $this->subject->getCurrency());
  		$this-> assertEquals(new DateTimeZone($att["group_timezone"]), $this->subject->getTimezone());
  		$this-> assertEquals($att["group_country"], $this->subject->getCountry());
  		$this-> assertEquals($att["group_city"], $this->subject->getCity());
    }
}
?>
