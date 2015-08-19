<?php
class Maestrano_Account_Reseller extends Maestrano_Api_Resource
{

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getName() {
		return $this->name;
	}

  public function getCode() {
		return $this->code;
	}

  public function getCountry() {
		return $this->country;
	}

  /**
  * @param string $class
  *
  * @returns string The endpoint URL for the Reseller class
  */
  public static function classUrl($class)
  {
    return "/api/v1/account/resellers";
  }

  /**
   * @param string $preset
   * @param string $id The ID of the reseller to instantiate.
   *
   * @return Maestrano_Account_Reseller
   */
  public static function newWithPreset($preset,$id=null)
  {
    return new Maestrano_Account_Reseller($id,$preset);
  }

  /**
   * @param string|null $preset
   * @param string $id The ID of the reseller to retrieve.
   *
   * @return Maestrano_Account_Reseller
   */
  public static function retrieveWithPreset($preset,$id)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $preset);
  }

  /**
   * @param string|null $preset
   * @param array|null $params
   *
   * @return array An array of Maestrano_Account_Reseller.
   */
  public static function allWithPreset($preset,$params=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $preset);
  }

  /**
  * @param array|null $params
  *
  * @return array An array of Maestrano_Account_Group belonging to the reseller.
  */
  public function groups($params=null) {
    return $this->getRelated('/groups',$params,$this->_preset);
  }
}
