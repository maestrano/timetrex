<?php

class Maestrano_Account_Group extends Maestrano_Api_Resource
{
  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the Bill class
   */
  public static function classUrl($class)
  {
    return "/api/v1/account/groups";
  }

  /**
   * @param string $preset
   * @param string $id The ID of the group to instantiate.
   *
   * @return Maestrano_Account_Group
   */
  public static function newWithPreset($preset,$id=null)
  {
    return new Maestrano_Account_Group($id,$preset);
  }

  /**
   * @param string|null $preset
   * @param string $id The ID of the bill to retrieve.
   *
   * @return Maestrano_Billing_Bill
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
   * @return array An array of Maestrano_Billing_Bills.
   */
  public static function allWithPreset($preset,$params=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $preset);
  }

	public function getId() {
		return $this->id;
	}

  public function getName() {
		return $this->name;
	}

  public function getEmail() {
		return $this->email;
	}

  public function getCurrency() {
		return $this->currency;
	}

  public function getCountry() {
		return $this->country;
	}

  public function getCity() {
		return $this->city;
	}

  public function getUpdatedAt() {
		return $this->updated_at;
	}

  public function getHasCreditCard() {
		return $this->has_credit_card;
	}

  public function hasCreditCard() {
		return $this->has_credit_card;
	}

	public function getStatus() {
		return $this->status;
	}

  public function getTimezone() {
		return $this->timezone;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

  /**
	 * Return the main accounting package used by this
	 * group
	 * @return String group city
	 */
	public function getMainAccounting() {
		return $this->main_accounting;
	}
}
