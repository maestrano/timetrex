<?php

// Helper Class
// Simulate implementation of MnoSsoUser
class MnoSsoUserStub extends MnoSsoBaseUser {
  public $_stub_getLocalIdByUid = 1234;
  public $_stub_getLocalIdByEmail = 1234;
  public $_stub_setLocalUid = true;
  public $_stub_setInSession = true;
  public $_stub_createLocalUser = 1234;
  public $_stub_syncLocalDetails = true;
  
  public $_called_getLocalIdByUid = 0;
  public $_called_getLocalIdByEmail = 0;
  public $_called_setLocalUid = 0;
  public $_called_setInSession = 0;
  public $_called_createLocalUser = 0;
  public $_called_syncLocalDetails = 0;
  
  protected function getLocalIdByUid() 
  { 
    $this->_called_getLocalIdByUid++;
    return $this->_stub_getLocalIdByUid;
  }
  
  protected function getLocalIdByEmail() 
  { 
    $this->_called_getLocalIdByEmail++;
    return $this->_stub_getLocalIdByEmail;
  }
  
  protected function setLocalUid()
  {
    $this->_called_setLocalUid++;
    return $this->_stub_setLocalUid;
  }
  
  protected function setInSession()
  {
    $this->_called_setInSession++;
    return $this->_stub_setInSession;
  }
  
  protected function createLocalUser()
  {
    $this->_called_createLocalUser++;
    return $this->_stub_createLocalUser;
  }
  
  protected function syncLocalDetails()
  {
    $this->_called_syncLocalDetails++;
    return $this->_stub_syncLocalDetails;
  }
}

// Class Test
class MnoSsoBaseUserTest extends PHPUnit_Framework_TestCase
{
    private $_saml_settings;

    public function setUp()
    {
      $settings = new OneLogin_Saml_Settings;
      $settings->idpSingleSignOnUrl = 'http://localhost:3000/api/v1/auth/saml';

      // The certificate for the users account in the IdP
      $settings->idpPublicCertificate = <<<CERTIFICATE
-----BEGIN CERTIFICATE-----
MIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD
VQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT
EU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ
KoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5
WhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP
MA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG
A1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz
dHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu
P2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I
5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2
28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo
eHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ
jYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE
BxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN
bWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u
Y29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc
MPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc
2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES
Rkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==
-----END CERTIFICATE-----
CERTIFICATE;

      // The URL where to the SAML Response/SAML Assertion will be posted
      $settings->spReturnUrl = 'http://localhost:8888/maestrano/auth/saml/consume.php';

      // Name of this application
      $settings->spIssuer = 'bla.app.dev.maestrano.io';

      // Tells the IdP to return the email address of the current user
      $settings->requestedNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
      
      $this->_saml_settings = $settings;
    }
    
    // Used to test protected methods
    protected static function getMethod($name) {
      $class = new ReflectionClass('MnoSsoBaseUser');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }
    
    public function testUserContruction()
    {
        $session = array('some_session_var' => 12456);
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $response = new OneLogin_Saml_Response($this->_saml_settings, $assertion);
        $response_attr = $response->getAttributes();
        $sso_user = new MnoSsoBaseUser($response,$session);
        
        // Test user attributes have the right value
        $this->assertEquals($session, $sso_user->session);
        $this->assertEquals($response_attr['mno_uid'][0], $sso_user->uid);
        $this->assertEquals($response_attr['mno_session'][0], $sso_user->sso_session);
        $this->assertEquals(new DateTime($response_attr['mno_session_recheck'][0]), $sso_user->sso_session_recheck);
        $this->assertEquals($response_attr['email'][0], $sso_user->email);
        $this->assertEquals($response_attr['name'][0], $sso_user->name);
        $this->assertEquals($response_attr['surname'][0], $sso_user->surname);
        $this->assertEquals($response_attr['app_owner'][0], $sso_user->app_owner);
        $this->assertEquals(json_decode($response_attr['organizations'][0],true), $sso_user->organizations);
    }
    
    public function testFunctionMatchLocalWhenFoundByUid()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->_stub_getLocalIdByUid = 1234;
      
      // Test user has the right local_id
      $sso_user->matchLocal();
      $this->assertEquals($sso_user->_stub_getLocalIdByUid, $sso_user->local_id);
      $this->assertEquals(0, $sso_user->_called_getLocalIdByEmail);
      $this->assertEquals(0, $sso_user->_called_setLocalUid);
      $this->assertEquals(1, $sso_user->_called_syncLocalDetails);
    }
    
    
    public function testFunctionMatchLocalWhenFoundByEmail()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->_stub_getLocalIdByUid = null;
      $sso_user->_stub_getLocalIdByEmail = 1236;
      
      // Test user has the right local_id
      $this->assertEquals($sso_user->_stub_getLocalIdByEmail, $sso_user->matchLocal());
      $this->assertEquals($sso_user->_stub_getLocalIdByEmail, $sso_user->local_id);
      $this->assertEquals(1, $sso_user->_called_setLocalUid);
      $this->assertEquals(1, $sso_user->_called_syncLocalDetails);
    }
    
    public function testFunctionMatchLocalWhenNotFound()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->_stub_getLocalIdByUid = null;
      $sso_user->_stub_getLocalIdByEmail = null;
      
      // Test user has the right local_id
      $this->assertEquals(null, $sso_user->matchLocal());
      $this->assertEquals(null, $sso_user->local_id);
      $this->assertEquals(1, $sso_user->_called_getLocalIdByUid);
      $this->assertEquals(1, $sso_user->_called_getLocalIdByEmail);
      $this->assertEquals(0, $sso_user->_called_setLocalUid);
      $this->assertEquals(0, $sso_user->_called_syncLocalDetails);
    }
    
    public function testFunctionAccessScopeWhenUserFound()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = 1234;
      
      // Test that accessScope returns 'private'
      $this->assertEquals('private', $sso_user->accessScope());
    }
    
    public function testFunctionAccessScopeWhenUserNotFoundButAppOwner()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = true;
      
      // Test that accessScope returns 'private'
      $this->assertEquals('private', $sso_user->accessScope());
    }
    
    public function testFunctionAccessScopeWhenUserNotFoundButInOrganization()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->organizations = array('org-xyz' => array('name' => 'AnOrga', 'role' => 'member'));
      
      // Test that accessScope returns 'private'
      $this->assertEquals('private', $sso_user->accessScope());
    }
    
    public function testFunctionAccessScopeWhenUserNotFoundAndExternal()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = null;
      $sso_user->organizations = array();
      
      // Test that accessScope returns 'public'
      $this->assertEquals('public', $sso_user->accessScope());
    }
    
    public function testFunctionCreateLocalUserOrDenyAccessWhenCreated()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion),$session);
      
      // Stub createLocalUser
      $sso_user->_stub_createLocalUser = 123456;
      
      // Test that createLocalUser has been called as well as return value
      $this->assertEquals(123456, $sso_user->createLocalUserOrDenyAccess());
      $this->assertEquals(123456, $sso_user->local_id);
      $this->assertEquals(1, $sso_user->_called_createLocalUser);
      $this->assertEquals(1, $sso_user->_called_setLocalUid);
    }
    
    public function testFunctionCreateLocalUserOrDenyAccessWhenNotCreated()
    {
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion),$session);
      
      // Stub createLocalUser
      $sso_user->_stub_createLocalUser = null;
      
      // Test that createLocalUser has been called as well as return value
      $this->assertEquals(null, $sso_user->createLocalUserOrDenyAccess());
      $this->assertEquals(null, $sso_user->local_id);
      $this->assertEquals(1, $sso_user->_called_createLocalUser);
      $this->assertEquals(0, $sso_user->_called_setLocalUid);
    }
    
    public function testFunctionGeneratePassword()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('generatePassword');
      
      // Build user
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      
      // Test return value
      $this->assertEquals(20, strlen($protected_method->invokeArgs($sso_user,array())));
    }
    
    public function testFunctionSignIn()
    {
      // Build Session
      $session = array();
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUserStub(new OneLogin_Saml_Response($this->_saml_settings, $assertion),$session);
      
      // Stub setInSession
      $sso_user->_stub_setInSession = true;
      
      // Test that session variables have been set correctly
      $sso_user->signIn();
      $this->assertEquals(1, $sso_user->_called_setInSession);
      $this->assertEquals($sso_user->uid, $session['mno_uid']);
      $this->assertEquals($sso_user->sso_session, $session['mno_session']);
      $this->assertEquals($sso_user->sso_session_recheck->format(DateTime::ISO8601), $session['mno_session_recheck']);
    }
    
    public function testDummyImplementationForSyncLocalDetails()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('syncLocalDetails');
      
      // Build user
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      
      // Test it returns true
      $this->assertEquals(true, $protected_method->invokeArgs($sso_user,array()));
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Function createLocalUser must be overriden in MnoSsoUser class!
     */
    public function testImplementationErrorForCreateLocalUser()
    {
        // Specify which protected method get tested
        $protected_method = self::getMethod('createLocalUser');
        
        // Build user
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
        
        // Test that exception is raised
        $protected_method->invokeArgs($sso_user,array());
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Function getLocalIdByUid must be overriden in MnoSsoUser class!
     */
    public function testImplementationErrorForGetLocalIdByUid()
    {
        // Specify which protected method get tested
        $protected_method = self::getMethod('getLocalIdByUid');
      
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
        
        $protected_method->invokeArgs($sso_user,array());
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Function getLocalIdByEmail must be overriden in MnoSsoUser class!
     */
    public function testImplementationErrorForGetLocalIdByEmail()
    {
        // Specify which protected method get tested
        $protected_method = self::getMethod('getLocalIdByEmail');
      
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
        
        $protected_method->invokeArgs($sso_user,array());
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Function setLocalUid must be overriden in MnoSsoUser class!
     */
    public function testImplementationErrorForSetLocalUid()
    {
        // Specify which protected method get tested
        $protected_method = self::getMethod('setLocalUid');
      
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
        
        $protected_method->invokeArgs($sso_user,array());
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Function setInSession must be overriden in MnoSsoUser class!
     */
    public function testImplementationErrorForSetInSession()
    {
        // Specify which protected method get tested
        $protected_method = self::getMethod('setInSession');
      
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $sso_user = new MnoSsoBaseUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
        
        $protected_method->invokeArgs($sso_user,array());
    }
}