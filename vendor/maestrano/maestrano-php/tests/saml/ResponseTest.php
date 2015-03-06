<?php

class Maestrano_Saml_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $settings;

    public function setUp()
    {
        $this->settings = SamlTestHelper::getXmlSecSamlTestSettings();
    }

    public function testReturnNameId()
    {
        $response = SamlTestHelper::buildSamlResponse('response1.xml.base64', $this->settings);

        $this->assertEquals('support@onelogin.com', $response->getNameId());
    }

    public function testGetAttributes()
    {
        $response = SamlTestHelper::buildSamlResponse('response1.xml.base64', $this->settings);
        
        $expectedAttributes = array(
          'uid' => 'demo',
          'another_value' =>'value'
        );
        $this->assertEquals($expectedAttributes, $response->getAttributes());

        // An assertion that has no attributes should return an empty array when asked for the attributes
        $response = SamlTestHelper::buildSamlResponse('response2.xml.base64', $this->settings);

        $this->assertEmpty($response->getAttributes());
    }

    public function testOnlyRetrieveAssertionWithIDThatMatchesSignatureReference()
    {
        $response = SamlTestHelper::buildSamlResponse('wrapped_response_2.xml.base64', $this->settings);
        
        try {
            $nameId = $response->getNameId();
            $this->assertNotEquals('root@example.com', $nameId);
        } catch (Exception $e) {
            $this->assertNotEmpty($e->getMessage(), 'Trying to get NameId on an unsigned assertion fails');
        }
    }

    public function testDoesNotAllowSignatureWrappingAttack()
    {
        $response = SamlTestHelper::buildSamlResponse('response4.xml.base64', $this->settings);

        $this->assertEquals('test@onelogin.com', $response->getNameId());
    }
}