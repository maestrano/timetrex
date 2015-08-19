<?php

class Maestrano_Account_RecurringBill extends Maestrano_Api_Resource
{

  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the RecurringBill class
   */
  public static function classUrl($class)
  {
    return "/api/v1/account/recurring_bills";
  }

  /**
   * @param string $preset
   * @param string $id The ID of the recurring bill to instantiate.
   *
   * @return Maestrano_Account_RecurringBill
   */
  public static function newWithPreset($preset,$id=null)
  {
    return new Maestrano_Account_RecurringBill($id,$preset);
  }

  /**
   * @param string|null $preset
   * @param string $id The ID of the bill to retrieve.
   *
   * @return Maestrano_Account_RecurringBill
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
   * @return array An array of Maestrano_Account_RecurringBills.
   */
  public static function allWithPreset($preset,$params=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $preset);
  }

  /**
   * @param string|null $preset
   * @param array|null $params
   *
   * @return Maestrano_Account_RecurringBill The created bill.
   */
  public static function createWithPreset($preset,$params=null)
  {
    $class = get_class();
    return self::_scopedCreate($class, $params, $preset);
  }

  /**
   * @return Maestrano_Account_RecurringBill The saved bill.
   */
  public function save()
  {
    $class = get_class();
    return self::_scopedSave($class);
  }

  /**
   * @return Maestrano_Account_RecurringBill The cancelled bill.
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
	public function getPeriod() {
		return $this->period;
	}
	public function setPeriod($period) {
		$this->period = $period;
	}
	public function getFrequency() {
		return $this->frequency;
	}
	public function setFrequency($frequency) {
		$this->frequency = $frequency;
	}
	public function getCycles() {
		return $this->cycles;
	}
	public function setCycles($cycles) {
		$this->cycles = $cycles;
	}
	public function getStartDate() {
		return $this->start_date;
	}
	public function setStartDate($startDate) {
		$this->start_date = $startDate;
	}
	public function getInitialCents() {
		return $this->initial_cents;
	}
	public function setInitialCents($initialCents) {
		$this->initial_cents = $initialCents;
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
		$this->description = $this->description;
	}

}
