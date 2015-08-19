<?php

class Maestrano_Account_ResellerWithPresetIntegrationTest extends PHPUnit_Framework_TestCase
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

  public function testRetrieveAllResellers() {
    $resellerList = Maestrano_Account_Reseller::with('some-preset')->all();

    foreach ($resellerList as $u) {
      if ($u->getId() == 'rsl-449s8fsd') $reseller = $u;
    }

    $this->assertEquals('rsl-449s8fsd',$reseller->getId());
    $this->assertEquals('some-preset',$reseller->getPreset());
    $this->assertEquals('Blue Consulting',$reseller->getName());
    $this->assertEquals('US-204512-BCL',$reseller->getCode());
    $this->assertEquals('AU',$reseller->getCountry());
  }

  public function testRetrieveSingleReseller() {
    $reseller = Maestrano_Account_Reseller::with('some-preset')->retrieve("rsl-449s8fsd");

    $this->assertEquals('rsl-449s8fsd',$reseller->getId());
    $this->assertEquals('some-preset',$reseller->getPreset());
    $this->assertEquals('Blue Consulting',$reseller->getName());
    $this->assertEquals('US-204512-BCL',$reseller->getCode());
    $this->assertEquals('AU',$reseller->getCountry());
  }

  public function testRetrieveAllResellerGroups() {
    $reseller = Maestrano_Account_Reseller::with('some-preset')->retrieve("rsl-449s8fsd");
    $groupList = $reseller->groups();
    $group = $groupList[0];

    $this->assertEquals('cld-4',$group->getId());
    $this->assertEquals('some-preset',$group->getPreset());
    $this->assertEquals('2014-05-21T04:04:53+0000',$group->getCreatedAt()->format(DateTime::ISO8601));
  }

  public function testRetrieveSelectedResellerGroups() {
    $reseller = Maestrano_Account_Reseller::with('some-preset')->retrieve("rsl-449s8fsd");
    $dateAfter = new DateTime('2014-06-21T00:31:26+0000');
    $dateBefore = new DateTime('2014-06-21T00:31:30+0000');
    $groupList = $reseller->groups(array(
      'freeTrialEndAtAfter' => $dateAfter,
      'freeTrialEndAtBefore' => $dateBefore,
    ));

    $this->assertTrue(count($groupList) == 1);
    $this->assertEquals('some-preset',$reseller->getPreset());
    $this->assertEquals('cld-3',$groupList[0]->getId());
  }
}
