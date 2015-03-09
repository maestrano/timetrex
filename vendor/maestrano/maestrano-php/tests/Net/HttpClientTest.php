<?php
  
/**
 * Unit tests for AuthN Request
 */
class Maestrano_Net_HttpClientTest extends PHPUnit_Framework_TestCase
{
  
  public function testGetRequest() {
    $url = "http://api-sandbox.maestrano.io/api/v1/auth/saml/usr-1";
    $client = new Maestrano_Net_HttpClient();
    $resp = $client->get($url);
    
    $this->assertFalse(empty($resp));
    $this->assertTrue(is_array(json_decode($resp,true)));
  }
}