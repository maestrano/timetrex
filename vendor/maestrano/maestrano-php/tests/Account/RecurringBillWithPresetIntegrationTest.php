<?php

/**
 * Unit tests for AuthN Request
 */
class Maestrano_Account_RecurringBillWithPresetIntegrationTest extends PHPUnit_Framework_TestCase
{

  /**
  * Initializes the Test Suite
  */
  public function setUp()
  {
      Maestrano::with('some-preset')->configure(array(
        'environment' => 'test',
        'api' => array(
          'id' => 'app-1',
          'key' => 'gfcmbu8269wyi0hjazk4t7o1sndpvrqxl53e1'
        )
      ));
  }

  public function testRetrieveAllBills() {
    $recBillList = Maestrano_Account_RecurringBill::with('some-preset')->all();
    $recBill = $recBillList[0];

    $this->assertEquals('rbill-1',$recBill->getId());
    $this->assertEquals('some-preset',$recBill->getPreset());
    $this->assertEquals('cld-3',$recBill->getGroupId());
    $this->assertEquals("year",$recBill->getPeriod());
    $this->assertEquals("1",$recBill->getFrequency());
    $this->assertEquals(1190,$recBill->getPriceCents());
    $this->assertEquals('2014-06-19T12:29:25+0000',$recBill->getCreatedAt()->format(DateTime::ISO8601));
  }

  public function testRetrieveSelectedBills() {
    $recBillList = Maestrano_Account_RecurringBill::with('some-preset')->all(array('status' => 'cancelled'));

    $this->assertTrue(count($recBillList) > 0);

    foreach ($recBillList as $recBill) {
      $this->assertEquals('cancelled',$recBill->getStatus());
      $this->assertEquals('some-preset',$recBill->getPreset());
    }
  }

  public function testRetrieveSingleBill() {
    $recBill = Maestrano_Account_RecurringBill::with('some-preset')->retrieve("rbill-1");

    $this->assertEquals('rbill-1',$recBill->getId());
    $this->assertEquals('some-preset',$recBill->getPreset());
    $this->assertEquals('cld-3',$recBill->getGroupId());
    $this->assertEquals("year",$recBill->getPeriod());
    $this->assertEquals("1",$recBill->getFrequency());
    $this->assertEquals(1190,$recBill->getPriceCents());
    $this->assertEquals('2014-06-19T12:29:25+0000',$recBill->getCreatedAt()->format(DateTime::ISO8601));
  }

  public function testCreateNewBill() {
    $attrs = array('groupId' => 'cld-3','priceCents' => 2000, 'description' => 'Product Purchase');
    $recBill = Maestrano_Account_RecurringBill::with('some-preset')->create($attrs);

    $this->assertFalse($recBill->getId() == null);
    $this->assertEquals('some-preset',$recBill->getPreset());
    $this->assertEquals('cld-3',$recBill->getGroupId());
    $this->assertEquals(2000,$recBill->getPriceCents());
    $this->assertFalse($recBill->getCreatedAt() == null);
  }

  public function testCancelABill() {
    $attrs = array('groupId' => 'cld-3','priceCents' => 2000, 'description' => 'Product Purchase');
    $recBill = Maestrano_Account_RecurringBill::with('some-preset')->create($attrs);

    $this->assertTrue($recBill->cancel());
    $this->assertEquals('cancelled',$recBill->getStatus());
  }
}
