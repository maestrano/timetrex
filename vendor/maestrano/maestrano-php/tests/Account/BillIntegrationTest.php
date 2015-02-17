<?php
  
/**
 * Unit tests for AuthN Request
 */
class Maestrano_Account_BillIntegrationTest extends PHPUnit_Framework_TestCase
{
  
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
  }
  
  public function testRetrieveAllBills() {
    $billList = Maestrano_Account_Bill::all();
    $bill = $billList[0];
    
    $this->assertEquals('bill-1',$bill->getId());
    $this->assertEquals('cld-3',$bill->getGroupId());
    $this->assertEquals(2300,$bill->getPriceCents());
    $this->assertEquals('2014-05-29T05:57:10+0000',$bill->getCreatedAt()->format(DateTime::ISO8601));
  }
  
  public function testRetrieveSelectedBills() {
    $billList = Maestrano_Account_Bill::all(array('status' => 'cancelled'));
    
    $this->assertTrue(count($billList) > 0);
    
    foreach ($billList as $bill) {
      $this->assertEquals('cancelled',$bill->getStatus());
    }
  }
  
  public function testRetrieveSingleBill() {
    $bill = Maestrano_Account_Bill::retrieve("bill-1");
    
    $this->assertEquals('bill-1',$bill->getId());
    $this->assertEquals('cld-3',$bill->getGroupId());
    $this->assertEquals('2300',$bill->getPriceCents());
    $this->assertEquals('2014-05-29T05:57:10+0000',$bill->getCreatedAt()->format(DateTime::ISO8601));
  }
  
  public function testCreateNewBill() {
    $attrs = array('groupId' => 'cld-3','priceCents' => 2000, 'description' => 'Product Purchase');
    $bill = Maestrano_Account_Bill::create($attrs);
    
    $this->assertFalse($bill->getId() == null);
    $this->assertEquals('cld-3',$bill->getGroupId());
    $this->assertEquals(2000,$bill->getPriceCents());
    $this->assertFalse($bill->getCreatedAt() == null);
  }
  
  public function testCancelABill() {
    $attrs = array('groupId' => 'cld-3','priceCents' => 2000, 'description' => 'Product Purchase');
    $bill = Maestrano_Account_Bill::create($attrs);
    
    $this->assertTrue($bill->cancel());
    $this->assertEquals('cancelled',$bill->getStatus());
  }
}