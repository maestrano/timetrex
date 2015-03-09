<?php

class Maestrano_Account_User extends Maestrano_Api_Resource
{
  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the Bill class
   */
  public static function classUrl($class)
  {
    return "/api/v1/account/users";
  }
  
  /**
   * @param string $id The ID of the bill to retrieve.
   * @param string|null $apiToken
   *
   * @return Maestrano_Billing_Bill
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
   * @return array An array of Maestrano_Billing_Bills.
   */
  public static function all($params=null, $apiToken=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $apiToken);
  }
  
	public function getId() {
		return $this->id;
	}
  
	public function getName() {
		return $this->name;
	}
	public function getFirstName() {
		return $this->name;
	}
  
	public function getSurname() {
		return $this->surname;
	}
	public function getLastname() {
		return $this->surname;
	}
  
	public function getEmail() {
		return $this->email;
	}
	public function getCompanyName() {
		return $this->company_name;
	}
	public function getCountry() {
		return $this->country;
	}
	public function getSsoSession() {
		return $this->sso_session;
	}
	public function getCreatedAt() {
		return $this->created_at;
	}
	public function getUpdatedAt() {
		return $this->updated_at;
	}
}