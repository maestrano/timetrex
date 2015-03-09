<?php
  
/**
 * Unit tests for AuthN Request
 */
class Maestrano_Saml_RequestTest extends PHPUnit_Framework_TestCase
{
    private $settings;
    
    /**
    * Initializes the Test Suite
    */
    public function setUp()
    {
        $this->settings = SamlTestHelper::getXmlSecSamlTestSettings();
    }
    
    /**
    * Tests the Maestrano_Saml_Request Constructor and
    * the getRedirectUrl method
    * The creation of a deflated SAML Request
    *
    * @covers Maestrano_Saml_Request
    * @covers Maestrano_Saml_Request::getRedirectUrl
    */
    public function testCreateDeflatedSAMLRequestURLParameter()
    {
        $request = new Maestrano_Saml_Request(array(),$this->settings);
        $authUrl = $request->getRedirectUrl();
        $this->assertRegExp('#^http://idp\.example\.com\/SSOService\.php\?SAMLRequest=#', $authUrl);
        parse_str(parse_url($authUrl, PHP_URL_QUERY), $exploded);
        
        // parse_url already urldecode de params so is not required.
        $payload = $exploded['SAMLRequest'];
        $decoded = base64_decode($payload);
        $inflated = gzinflate($decoded);
        $this->assertRegExp('#^<samlp:AuthnRequest#', $inflated);
        
        
        $request2 = new Maestrano_Saml_Request(array(),$this->settings);
        $authUrl2 = $request2->getRedirectUrl('http://sp.example.com');
        
        $this->assertRegExp('#^http://idp\.example\.com\/SSOService\.php\?SAMLRequest=#', $authUrl2);
        parse_str(parse_url($authUrl2, PHP_URL_QUERY), $exploded2);
        
        $payload2 = $exploded2['SAMLRequest'];
        $decoded2 = base64_decode($payload2);
        $inflated2 = gzinflate($decoded2);
        $this->assertRegExp('#^<samlp:AuthnRequest#', $inflated2);
    }
    
    public function testSamlRequestWithAdditionalParameters() {
      $request = new Maestrano_Saml_Request(array('hello' => 'there'),$this->settings);
      $authUrl = $request->getRedirectUrl();
      $this->assertRegExp('#hello=there#', $authUrl);
    }
    
    public function testSamlRequestParametersEncoding() {
      $request = new Maestrano_Saml_Request(array('hello' => 'hi there'),$this->settings);
      $authUrl = $request->getRedirectUrl();
      $this->assertRegExp('#hello=hi\+there#', $authUrl);
    }
    
    /**
    * Tests the protected method _getTimestamp of the Maestrano_Saml_Request
    *
    * @covers Maestrano_Saml_Request::_getTimestamp
    */
    public function testGetMetadataValidTimestamp()
    {
        if (class_exists('ReflectionClass')) {
            $reflectionClass = new ReflectionClass("Maestrano_Saml_Request");
            $method = $reflectionClass->getMethod('_getTimestamp');
 
            if (method_exists($method, 'setAccessible')) {
                $method->setAccessible(true);
                
                $metadata = new Maestrano_Saml_Request(array(),SamlTestHelper::getXmlSecSamlTestSettings());
                
                $time = time();
                $timestamp = $method->invoke($metadata);
                $this->assertEquals(strtotime($timestamp), $time);
            }
        }
    }
    
    /**
    * Tests the protected method _generateUniqueID of the Maestrano_Saml_Request
    *
    * @covers Maestrano_Saml_Request::_generateUniqueID
    */
    public function testGenerateUniqueID()
    {
        if (class_exists('ReflectionClass')) {
            $reflectionClass = new ReflectionClass("Maestrano_Saml_Request");
            $method = $reflectionClass->getMethod('_generateUniqueID');
            
            if (method_exists($method, 'setAccessible')) {
                $method->setAccessible(true);
                
                $metadata = new Maestrano_Saml_Request(array(),SamlTestHelper::getXmlSecSamlTestSettings());
                
                $id = $method->invoke($metadata);
                $id2 = $method->invoke($metadata);
                $this->assertNotEmpty($id);
                $this->assertNotEmpty($id2);
                $this->assertNotEquals($id, $id2);
                $this->assertContains('Maestrano', $id);
            }
        }
    }
}
?>