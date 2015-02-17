<?php

class Maestrano_Account_Bill extends Maestrano_Api_Resource
{
  
  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the Bill class
   */
  public static function classUrl($class)
  {
    return "/api/v1/account/bills";
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

  /**
   * @param array|null $params
   * @param string|null $apiToken
   *
   * @return Maestrano_Billing_Bill The created bill.
   */
  public static function create($params=null, $apiToken=null)
  {
    $class = get_class();
    return self::_scopedCreate($class, $params, $apiToken);
  }

  /**
   * @return Maestrano_Billing_Bill The saved bill.
   */
  public function save()
  {
    $class = get_class();
    return self::_scopedSave($class);
  }
  
  /**
   * @return Maestrano_Billing_Bill The cancelled bill.
   */
  public function cancel($params=null)
  {
    $class = get_class();
    self::_scopedDelete($class, $params);
    return $this->getStatus() == 'cancelled';
  }
  
  
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}
	
	public function getUpdatedAt() {
		return $this->updated_at;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getUnits() {
		return $this->units;
	}
	
	public function setUnits($units) {
    $this->units = $units;
	}
	
	public function getPeriodStartedAt() {
		return $this->period_started_at;
	}
	
	public function setPeriodStartedAt($periodStartedAt) {
    $this->period_started_at = $periodStartedAt;
	}
	
	public function getPeriodEndedAt() {
		return $this->period_ended_at;
	}
	
	public function setPeriodEndedAt($periodEndedAt) {
    $this->period_ended_at = $periodEndedAt;
	}
	
	public function getGroupId() {
		return $this->group_id;
	}
	
	public function setGroupId($groupId) {
    $this->group_id = $groupId;
	}
	
	public function getPriceCents() {
		return $this->price_cents;
	}
	
	public function setPriceCents($priceCents) {
    $this->price_cents = $priceCents;
	}
	
	public function getCurrency() {
		return $this->currency;
	}
	
	public function setCurrency($currency) {
    $this->currency = $currency;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
    $this->description = $description;
	}
}
