<?php

class Maestrano_Saml_XmlSecTest extends PHPUnit_Framework_TestCase
{
    private $_settings;
    
    public function setUp()
    {
        Maestrano::configure(array('environment' => 'production'));
        $this->settings = SamlTestHelper::getXmlSecSamlTestSettings();
    }

    public function testValidateNumAssertions()
    {
        $response = SamlTestHelper::buildSamlResponse('response1.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);

        $this->assertTrue($xmlSec->validateNumAssertions());
    }

    public function testValidateTimestampsInvalid()
    {
        $response = SamlTestHelper::buildSamlResponse('not_before_failed.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        $this->assertFalse($xmlSec->validateTimestamps());
        
        $response2 = SamlTestHelper::buildSamlResponse('not_after_failed.xml.base64', $this->settings);
        $xmlSec2 = new Maestrano_Saml_XmlSec($this->settings, $response2);

        $this->assertFalse($xmlSec2->validateTimestamps());
    }

    public function testValidateTimestampsValid()
    {
        $response = SamlTestHelper::buildSamlResponse('valid_response.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);

        $this->assertTrue($xmlSec->validateTimestamps());
    }

    public function testValidateAssertionUnsigned()
    {
        $response = SamlTestHelper::buildSamlResponse('no_signature.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        
        try {
            $this->assertFalse($xmlSec->isValid());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertContains('Cannot locate Signature Node', $e->getMessage());
        }
    }

    public function testValidateAssertionBadReference()
    {
        $response = SamlTestHelper::buildSamlResponse('bad_reference.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        
        try {
            $this->assertFalse($xmlSec->isValid());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Reference Validation Failed', $e->getMessage());
        }
    }

    public function testValidateAssertionMultiple()
    {
        $response = SamlTestHelper::buildSamlResponse('multiple_assertions.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        
        try {
            $this->assertFalse($xmlSec->isValid());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertContains('Multiple assertions are not supported', $e->getMessage());
        }
    }

    public function testValidateAssertionExpired()
    {
        $response = SamlTestHelper::buildSamlResponse('expired_response.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        
        try {
            $this->assertFalse($xmlSec->isValid());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertContains('Timing issues (please check your clock settings)', $e->getMessage());
        }
    }

    public function testValidateAssertionNoKey()
    {
        $response = SamlTestHelper::buildSamlResponse('no_key.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);
        
        try {
            $this->assertFalse($xmlSec->isValid());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertContains('We have no idea about the key', $e->getMessage());
        }
    }

    public function testValidateAssertionValid()
    {
        $response = SamlTestHelper::buildSamlResponse('valid_response.xml.base64', $this->settings);
        $xmlSec = new Maestrano_Saml_XmlSec($this->settings, $response);

        $this->assertTrue($xmlSec->isValid());
    }
}