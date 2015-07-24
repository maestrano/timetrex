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
  * @param string $id The ID of the reseller to retrieve.
  * @param string|null $apiToken
  *
  * @return Maestrano_Account_Reseller
  */
  public static function retrieve($id, $apiToken=null)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $apiToken);
  }

  /**
  * @param array|null $params
  * @param string|null $apiToken
  *
  * @return array An array of Maestrano_Account_Reseller.
  */
  public static function all($params=null, $apiToken=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $apiToken);
  }

  /**
  * @param array|null $params
  * @param string|null $apiToken
  *
  * @return array An array of Maestrano_Account_Group belonging to the reseller.
  */
  public function groups($params=null, $apiToken=null) {
    return $this->getRelated('/groups',$params,$apiToken);
  }
}
