<?php
  
/**
 * Unit tests for AuthN Request
 */
class Maestrano_Connec_ClientIntegrationTest extends PHPUnit_Framework_TestCase
{
  private $subject;
  
  /**
  * Initializes the Test Suite
  */
  public function setUp()
  {
      Maestrano::configure(array(
        'environment' => 'test', 
        'api' => array(
          'id' => 'app-1',
          'key' => 'gfcmbu8269wyi0hjazk4t7o1sndpvrqxl53e1'
        )
      ));
      $this->subject = new Maestrano_Connec_Client("cld-3");
  }
  
  public function testRetrieveCollection() {
    $resp = $this->subject->get("/organizations");
    $parsed = json_decode($resp['body'],true);
    
    $this->assertEquals('200',$resp['code']);
    $this->assertNotNull($parsed['organizations']);
  }
  
  public function testCreateResource() {
    $resource = array('name' => "Doe Corp Inc.");
    $body = array( 'organizations' => $resource);
    
    $resp = $this->subject->post("/organizations",$body);
    $parsed = json_decode($resp['body'],true);
    
    $this->assertEquals('201',$resp['code']);
    $this->assertNotNull($parsed['organizations']['id']);
  }
  
  public function testUpdateResource() {
    // Create Resource
    $resource = array('name' => "Doe Corp Inc.");
    $body = array('organizations' => $resource);
    $resp = $this->subject->post("/organizations",$body);
    $parsed = json_decode($resp['body'],true);
    
    // Update Resource
    $resource = array('is_customer' => true);
    $body = array('organizations' => $resource);
    $resp = $this->subject->put("/organizations/" . $parsed['organizations']['id'],$body);
    $parsed = json_decode($resp['body'],true);
    
    $this->assertEquals('200',$resp['code']);
    $this->assertTrue($parsed['organizations']['is_customer']);
  }
}