<?php

/**
 * Unit tests for AuthN Request
 */
class Maestrano_Account_GroupWithPresetIntegrationTest extends PHPUnit_Framework_TestCase
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

  public function testRetrieveAllGroups() {
    $groupList = Maestrano_Account_Group::with('some-preset')->all();
    $group = $groupList[0];

    $this->assertEquals('cld-4',$group->getId());
    $this->assertEquals('some-preset',$group->getPreset());
    $this->assertEquals('2014-05-21T04:04:53+0000',$group->getCreatedAt()->format(DateTime::ISO8601));
  }

  public function testRetrieveSelectedGroups() {
    $dateAfter = new DateTime('2014-06-21T00:31:26+0000');
    $dateBefore = new DateTime('2014-06-21T00:31:30+0000');
    $groupList = Maestrano_Account_Group::with('some-preset')->all(array(
      'freeTrialEndAtAfter' => $dateAfter,
      'freeTrialEndAtBefore' => $dateBefore,
    ));

    $this->assertTrue(count($groupList) == 1);
    $this->assertEquals('cld-3',$groupList[0]->getId());
    $this->assertEquals('some-preset',$groupList[0]->getPreset());
  }

  public function testRetrieveSingleGroup() {
    $group = Maestrano_Account_Group::with('some-preset')->retrieve("cld-3");

    $this->assertEquals('cld-3',$group->getId());
    $this->assertEquals('some-preset',$group->getPreset());
    $this->assertEquals('2014-05-21T00:31:26+0000',$group->getCreatedAt()->format(DateTime::ISO8601));
    $this->assertEquals('quickbooks', $group->getMainAccounting());
  }

}
