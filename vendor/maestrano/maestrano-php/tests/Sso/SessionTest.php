<?php
  
class Maestrano_Sso_SessionTest extends PHPUnit_Framework_TestCase
{
    private $httpSession;
    private $subject;
    private $httpClient;
    
    /**
    * Initializes the Test Suite
    */
    public function setUp()
    {
      Maestrano::configure(array('environment' => 'production', 'sso' => array('slo_enabled' => true)));
      
      $this->mnoSession = array(
    		"uid" => "usr-1",
        "group_uid" => "cld-1",
        "session" => "sessiontoken",
        "session_recheck" => "2014-06-22T01:00:00Z"
      );
      
      $this->httpSession = array();
      SessionTestHelper::setMnoEntry($this->httpSession,$this->mnoSession);
    		
      
      $this->httpClient = new MnoHttpClientStub();
    }
    
    
  	
  	public function testContructsAnInstanceFromHttpSession()
  	{
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);

  		$this->assertEquals($this->httpSession, $this->subject->getHttpSession());
  		$this->assertEquals($this->mnoSession["uid"], $this->subject->getUid());
  		$this->assertEquals($this->mnoSession["group_uid"], $this->subject->getGroupUid());
  		$this->assertEquals($this->mnoSession["session"], $this->subject->getSessionToken());
  		$this->assertEquals(new DateTime($this->mnoSession["session_recheck"]), $this->subject->getRecheck());
  	}

  	
  	public function testContructsAnInstanceFromHttpSessionAndSsoUser()
  	{
  		$samlResp = new SamlMnoRespStub();
  		$user = new Maestrano_Sso_User($samlResp);
  		$this->subject = new Maestrano_Sso_Session($this->httpSession, $user);

  		$this->assertEquals($this->httpSession, $this->subject->getHttpSession());
  		$this->assertEquals($user->getUid(), $this->subject->getUid());
  		$this->assertEquals($user->getGroupUid(), $this->subject->getGroupUid());
  		$this->assertEquals($user->getSsoSession(), $this->subject->getSessionToken());
  		$this->assertEquals($user->getSsoSessionRecheck(), $this->subject->getRecheck());
  	}

  	
  	public function testIsRemoteCheckRequiredReturnsTrueIfRecheckIsBeforeNow()
  	{
  		$date = new DateTime();
      $date->sub(new DateInterval('PT1M'));
  		$this->mnoSession["session_recheck"] = $date->format(DateTime::ISO8601);
  		SessionTestHelper::setMnoEntry($this->httpSession,$this->mnoSession);
      
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);

  		// test
  		$this->assertTrue($this->subject->isRemoteCheckRequired());
  	}

  	
  	public function testRemoteCheckRequiredReturnsFalseIfRecheckIsAfterNow()
  	{
  		$date = new DateTime();
      $date->add(new DateInterval('PT1M'));
  		$this->mnoSession["session_recheck"] = $date->format(DateTime::ISO8601);
  		SessionTestHelper::setMnoEntry($this->httpSession,$this->mnoSession);
      
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);

  		// test
  		$this->assertFalse($this->subject->isRemoteCheckRequired());
  	}

  	
  	public function testperformRemoteCheckWhenValidReturnsTrueAndAssignRecheckIfValid()
  	{   
  		// Response preparation
  		$date = new DateTime();
      $date->add(new DateInterval('PT1M'));
      
  		$resp = array();
  		$resp["valid"] = "true";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);
		  
  		$this->httpClient->setResponseStub($resp);
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);

  		// Tests
  		$this->assertTrue($this->subject->performRemoteCheck($this->httpClient));
  		$this->assertEquals($date->format(DateTime::ISO8601), $this->subject->getRecheck()->format(DateTime::ISO8601));
  	}

  	
  	public function testPerformRemoteCheckWhenInvalidReturnsFalseAndLeaveRecheckUnchanged()
  	{
  		// Response preparation
  		$date = new DateTime();
      $date->add(new DateInterval('PT1M'));
  		$resp = array();
  		$resp["valid"] = "false";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);

  		$this->httpClient->setResponseStub($resp);
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$recheck = $this->subject->getRecheck();
		
  		$this->assertFalse($this->subject->performRemoteCheck($this->httpClient));
  		$this->assertEquals($recheck, $this->subject->getRecheck());
  	}


  	
  	public function testSaveSavesTheMaestranoSessionInHttpSession()
  	{
  		
  		$oldSubject = new Maestrano_Sso_Session($this->httpSession);
  		$oldSubject->setUid($oldSubject->getUid() + "aaa");
  		$oldSubject->setGroupUid($oldSubject->getGroupUid() + "aaa");
  		$oldSubject->setSessionToken($oldSubject->getSessionToken() + "aaa");
      
  		$date = new DateTime();
      $date->add(new DateInterval('PT100M'));
  		$oldSubject->setRecheck($date);
  		$oldSubject->save();
		
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$this->assertEquals($oldSubject->getUid(), $this->subject->getUid());
  		$this->assertEquals($oldSubject->getGroupUid(), $this->subject->getGroupUid());
  		$this->assertEquals($oldSubject->getSessionToken(), $this->subject->getSessionToken());
  		$this->assertEquals($oldSubject->getRecheck(), $this->subject->getRecheck());
  	}

  	
  	public function testisValidWhenSloDisabled_ItShouldReturnTrue()
  	{
  		// Disable SLO
  		Maestrano::configure(array('environment' => 'production', 'sso' => array('slo_enabled' => false)));
		
  		// Response preparation (session not valid)
  		$date = new DateTime();
      $date->add(new DateInterval('PT1M'));
  		$resp = array();
  		$resp["valid"] = "false";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);
  		$this->httpClient->setResponseStub($resp);
		
  		// Set local recheck to force remote recheck
  		$localRecheck = new DateTime();
      $localRecheck->sub(new DateInterval('PT1M'));
      
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$this->subject->setRecheck($localRecheck);
		
  		$this->assertTrue($this->subject->isValid(false,$this->httpClient));
  	}

  	
  	public function testIsValidWhenIfSessionSpecifiedAndNoMaestranoSsoSessionReturnsTrue()
  	{
  		// Http context
  		$this->httpSession["maestrano"] = null;

  		// test
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$this->assertTrue($this->subject->isValid(true));
  	}

  	
  	public function testIsValidWhenNoRecheckRequiredReturnsTrue()
  	{	
  		// Make sure any remote response is negative
  		$date = new DateTime();
      $date->add(new DateInterval('PT100M'));
  		$resp = array();
  		$resp["valid"] = "false";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);
  		$this->httpClient->setResponseStub($resp);
		
  		// Set local recheck in the future
  		$localRecheck = new DateTime();
      $localRecheck->add(new DateInterval('PT1M'));
      
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$this->subject->setRecheck($localRecheck);

  		// test
  		$this->assertTrue($this->subject->isValid(false,$this->httpClient));
  	}

  	
  	public function testIsValidWhenRecheckRequiredAndValidReturnsTrueAndSaveTheSession()
  	{
  		// Make sure any remote response is negative
  		$date = new DateTime();
      $date->add(new DateInterval('PT100M'));
  		$resp = array();
  		$resp["valid"] = "true";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);
  		$this->httpClient->setResponseStub($resp);

  		// Set local recheck in the past
  		$localRecheck = new DateTime();
      $localRecheck->sub(new DateInterval('PT1M'));
  		$oldSubject = new Maestrano_Sso_Session($this->httpSession);
  		$oldSubject->setRecheck($localRecheck);
		
  		// test 1 - validity
  		$this->assertTrue($oldSubject->isValid(false,$this->httpClient));
		
  		// Create a new subject to test session persistence
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
		
  		// test 2 - session persistence
  		$this->assertEquals($date->format(DateTime::ISO8601), $this->subject->getRecheck()->format(DateTime::ISO8601));
  	}

  	
  	public function isValid_WhenRecheckRequiredAndInvalid_ItShouldReturnFalse()
  	{
  		// Make sure any remote response is negative
  		$date = new DateTime();
      $date->add(new DateInterval('PT100M'));
  		$resp = array();
  		$resp["valid"] = "false";
  		$resp["recheck"] = $date->format(DateTime::ISO8601);
  		$this->httpClient->setResponseStub($resp);

  		// Set local recheck in the past
  		$localRecheck = new DateTime();
      $localRecheck->sub(new DateInterval('PT1M'));
      
  		$this->subject = new Maestrano_Sso_Session($this->httpSession);
  		$this->subject->setRecheck($localRecheck);

  		// test 1 - validity
  		$this->assertFalse($this->subject->isValid(false,$this->httpClient));
  	}
}
?>