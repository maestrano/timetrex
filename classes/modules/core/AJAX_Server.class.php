<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * $Revision: 8717 $
 * $Id: AJAX_Server.class.php 8717 2012-12-28 22:30:24Z ipso $
 * $Date: 2012-12-28 14:30:24 -0800 (Fri, 28 Dec 2012) $
 */

/**
 * @package Core
 */
class AJAX_Server {

	function getCurrentUserFullName() {
		global $current_user;

		return $current_user->getFullName();
	}

	function getCurrentCompanyName() {
		global $current_company;

		return $current_company->getName();
	}

	function getProvinceOptions( $country ) {
		Debug::Arr($country, 'aCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($country) AND $country == '' ) {
			return FALSE;
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		Debug::Arr($country, 'bCountry: ', __FILE__, __LINE__, __METHOD__, 10);

		$cf = TTnew( 'CompanyFactory' );

		$province_arr = $cf->getOptions('province');

		$retarr = array();

		foreach( $country as $tmp_country ) {
			if ( isset($province_arr[strtoupper($tmp_country)]) ) {
				//Debug::Arr($province_arr[strtoupper($tmp_country)], 'Provinces Array', __FILE__, __LINE__, __METHOD__, 10);

				$retarr = array_merge( $retarr, $province_arr[strtoupper($tmp_country)] );
				//$retarr = array_merge( $retarr, Misc::prependArray( array( -10 => '--' ), $province_arr[strtoupper($tmp_country)] ) );
			}
		}

		if ( count($retarr) == 0 ) {
			$retarr = array('00' => '--');
		}

		return $retarr;
	}

	function getProvinceDistrictOptions( $country, $province) {
		if ( $country == '' ) {
			return FALSE;
		}

		if ( $province == '' ) {
			return FALSE;
		}
		Debug::text('Country: '. $country .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

		$cf = TTnew( 'CompanyFactory' );

		$district_arr = $cf->getOptions('district');

		if ( isset($district_arr[strtoupper($country)][strtoupper($province)]) ) {
			Debug::Arr($district_arr[strtoupper($country)][strtoupper($province)], 'District Array', __FILE__, __LINE__, __METHOD__, 10);
			return $district_arr[strtoupper($country)][strtoupper($province)];
		}

		return array();
	}

	function getProvinceInvoiceDistrictOptions( $country, $province) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_array($country) AND $country == '' ) {
			return FALSE;
		}

		if ( !is_array($province) AND $province == '' ) {
			return FALSE;
		}

		if ( !is_array($country) ) {
			$country = array($country);
		}

		if ( !is_array($province) ) {
			$province = array($province);
		}

		Debug::text('Country: '. $country .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

		$idlf = TTnew( 'InvoiceDistrictListFactory' );
		$idlf->getByCompanyIdAndProvinceAndCountry( $current_company->getId(), $province, $country);

		$district_arr = $idlf->getArrayByListFactory($idlf, FALSE);

		if ( is_array($district_arr) ) {
			Debug::Arr($district_arr, 'District Array', __FILE__, __LINE__, __METHOD__, 10);
			return $district_arr;
		}

		return array();
	}

	function getHourlyRate( $wage, $weekly_hours, $wage_type_id = 10 ) {
		if ( $wage == '' ) {
			return '0.00';
		}

		if ( $weekly_hours == '' ) {
			return '0.00';
		}

		if ( $wage_type_id == '' ) {
			return '0.00';
		}

		$uwf = TTnew( 'UserWageFactory' );
		$uwf->setType( $wage_type_id );
		$uwf->setWage( $wage );
		$uwf->setWeeklyTime( TTDate::parseTimeUnit($weekly_hours) );
		$hourly_rate = $uwf->calcHourlyRate();

		return $hourly_rate;
	}

	function getUserHourlyRate( $user_id, $date ) {
		Debug::text('User ID: '. $user_id .' Date: '. $date, __FILE__, __LINE__, __METHOD__, 10);
		if ( $user_id == '' ) {
			return '0.00';
		}

		if ( $date == '' ) {
			$date = TTDate::getTime();
		}

		$epoch = TTDate::parseDateTime($date);

		$uwlf = TTnew( 'UserWageListFactory' );
		$uwlf->getByUserIdAndDate( $user_id, $epoch);
		if ( $uwlf->getRecordCount() > 0 ) {
			$hourly_rate = $uwlf->getCurrent()->getHourlyRate();

			return $hourly_rate;
		}

		return '0.00';
	}

	function getUserLaborBurdenPercent( $user_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return '0.00';
		}

		$retval = UserWageFactory::calculateLaborBurdenPercent( $current_company->getId(), $user_id );

		if ( $retval == '' ) {
			return '0.00';
		}

		return $retval;
	}

	function getJobOptions( $user_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('User ID: '. $user_id .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		$jlf = TTnew( 'JobListFactory' );
		return $jlf->getByCompanyIdAndUserIdAndStatusArray( $current_company->getId(),  $user_id, array(10,20,30,40), TRUE );
	}

	function getJobItemOptions( $job_id, $include_disabled = TRUE ) {
		//Don't check for current company as this needs to work when we are not fully authenticated.
		/*
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}
		*/

		Debug::text('Job ID: '. $job_id .' Include Disabled: '. (int)$include_disabled, __FILE__, __LINE__, __METHOD__, 10);

		$jilf = TTnew( 'JobItemListFactory' );
		//$jilf->getByCompanyIdAndJobId( $current_company->getId(), $job_id );
		$jilf->getByJobId( $job_id );
		$job_item_options = $jilf->getArrayByListFactory( $jilf, TRUE, $include_disabled );
		if ( $job_item_options != FALSE AND is_array($job_item_options) ) {
				return $job_item_options;
		}

		Debug::text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);

		$retarr = array( '00' => '--');

		return $retarr;
	}

	function getJobItemData( $job_item_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('Job Item ID: '. $job_item_id .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $job_item_id == '' ) {
			return FALSE;
		}

		if ( $current_company->getID() == '' ) {
			return FALSE;
		}

		$jilf = TTnew( 'JobItemListFactory' );
		$jilf->getByIdAndCompanyId( $job_item_id, $current_company->getId() );
		if ( $jilf->getRecordCount() > 0 ) {
			foreach( $jilf as $item_obj ) {
				$retarr = array(
									'id' => $item_obj->getId(),
									'product_id' => $item_obj->getProduct(),
									'group_id' => $item_obj->getGroup(),
									'type_id' => $item_obj->getType(),
									'other_id1' => $item_obj->getOtherID1(),
									'other_id2' => $item_obj->getOtherID2(),
									'other_id3' => $item_obj->getOtherID3(),
									'other_id4' => $item_obj->getOtherID4(),
									'other_id5' => $item_obj->getOtherID5(),
									'manual_id' => $item_obj->getManualID(),
									'name' => $item_obj->getName(),
									'description' => $item_obj->getDescription(),
									'estimate_time' => $item_obj->getEstimateTime(),
									'estimate_time_display' => TTDate::getTimeUnit( $item_obj->getEstimateTime() ),
									'estimate_quantity' => $item_obj->getEstimateQuantity(),
									'estimate_bad_quantity' => $item_obj->getEstimateBadQuantity(),
									'bad_quantity_rate' => $item_obj->getBadQuantityRate(),
									'billable_rate' => $item_obj->getBillableRate(),
									'minimum_time' => $item_obj->getMinimumTime(),
									'minimum_time_display' => TTDate::getTimeUnit( $item_obj->getMinimumTime() ),
									'created_date' => $item_obj->getCreatedDate(),
									'created_by' => $item_obj->getCreatedBy(),
									'updated_date' => $item_obj->getUpdatedDate(),
									'updated_by' => $item_obj->getUpdatedBy(),
									'deleted_date' => $item_obj->getDeletedDate(),
									'deleted_by' => $item_obj->getDeletedBy()
								);

				Debug::text('Returning Data...', __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			}
		}

		Debug::text('Returning False...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getProductQuantityUnitPrice( $product_id, $quantity, $currency_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$plf = TTnew( 'ProductListFactory' );
		$plf->getByIdAndCompanyId($product_id, $current_company->getId() );
		if ( $plf->getRecordCount() > 0 ) {
			$p_obj = $plf->getCurrent();

			Debug::text('Product ID: '. $product_id .' Quantity: '. $quantity .' SRC Currency: '. $p_obj->getCurrency() .' DST Currency: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);

			return CurrencyFactory::convertCurrency( $p_obj->getCurrency(), $currency_id, $p_obj->getQuantityUnitPrice( $quantity ) );
		}

		Debug::text('Returning FALSE', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function getProductLockData($product_id, $part_number = NULL, $product_name = NULL, $product_upc = NULL, $currency_id = NULL ) {
		return $this->getProductData($product_id, $part_number, $product_name, $product_upc, $currency_id );
	}
	function getProductData( $product_id, $part_number = NULL, $product_name = NULL, $product_upc = NULL, $currency_id = NULL ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		Debug::text('Product ID: '. $product_id .' Part Number: '. $part_number .' Product Name: '. $product_name .' UPC: '. $product_upc .' Company ID: '. $current_company->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $product_id == '' AND $part_number == '' AND $product_name == '' AND $product_upc == '') {
			return FALSE;
		}

		if ( $current_company->getID() == '' ) {
			return FALSE;
		}

		$plf = TTnew( 'ProductListFactory' );

		if ( $product_id != '' ) {
			$plf->getByIdAndCompanyId($product_id, $current_company->getId() );
		} elseif ( $part_number != '' ) {
			Debug::text('Getting by Part Number ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByPartNumberAndCompanyId($part_number, $current_company->getId() );
		} elseif( $product_name != '' ) {
			Debug::text('Getting by Name ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByNameAndCompanyId($product_name, $current_company->getId() );
		} elseif( $product_upc != '' ) {
			Debug::text('Getting by UPC ', __FILE__, __LINE__, __METHOD__, 10);
			$plf->getByUPCAndCompanyId($product_upc, $current_company->getId() );
		}

		if ( $plf->getRecordCount() > 0 ) {
			$p_obj = $plf->getCurrent();

			$retarr = array(
								'id' => $p_obj->getId(),
								'name' => $p_obj->getName(),
								'description' => $p_obj->getDescription(),
								'type_id' => $p_obj->getType(),
								'status_id' => $p_obj->getStatus(),
								'part_number' => $p_obj->getPartNumber(),

								'currency_id' => $p_obj->getCurrency(),
								'unit_cost' => $p_obj->getUnitCost(),
								'unit_price' => CurrencyFactory::convertCurrency( $p_obj->getCurrency(), $currency_id, $p_obj->getQuantityUnitPrice( 1 ) ),
								//'unit_price' => $p_obj->getUnitPrice(),

								'weight_unit_id' => $p_obj->getWeightUnit(),
								'weight' => $p_obj->getWeight(),

								'dimension_unit_id' => $p_obj->getDimensionUnit(),
								'length' => $p_obj->getLength(),
								'width' => $p_obj->getWidth(),
								'height' => $p_obj->getHeight(),

								'price_locked' => $p_obj->getPriceLocked(),
								'description_locked' => $p_obj->getDescriptionLocked(),
							);

			Debug::text('Returning Data...', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		Debug::text('Returning False...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function convertRawInvoiceDataToTransactionArray( $data ) {
		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			foreach( $data as $transaction_key => $transaction_data ) {
				if ( isset($transaction_data[0]) AND $transaction_data[0] == 10 ) {
					//Debug::Text('Transaction Product ID: '. $transaction_data[0] .' Unit Price: '. $transaction_data[2] .' Quantity: '. $transaction_data[3], __FILE__, __LINE__, __METHOD__, 10);
					$transaction_arr[] = array(
									'id' => NULL,
									'type_id' => 10,
									'product_id' => $transaction_data[1],
									'product_type_id' => $transaction_data[2],
									'unit_price' => $transaction_data[3],
									'quantity' => $transaction_data[4],
									'currency_id' => $transaction_data[5],
									'pro_rate_numerator' => $transaction_data[6],
									'pro_rate_denominator' => $transaction_data[7],
									//'amount' => bcmul( $transaction_data[3], $transaction_data[4] )
									'amount' => bcmul( bcdiv( $transaction_data[6], ( $transaction_data[7] < 1 ) ? 1 : $transaction_data[7] ), bcmul( $transaction_data[3], $transaction_data[4] ) ),
									);
				} elseif ( isset($transaction_data[0]) AND $transaction_data[0] == 20 ) {
					$transaction_arr[] = array(
									'id' => NULL,
									'type_id' => 20,
									'status_id' => $transaction_data[1],
									'amount' => $transaction_data[2]
									);
				}
			}
		}

		return $transaction_arr;
	}
	function getInvoiceTotalData( $data, $invoice_data, $include_unconfirmed_transactions = FALSE ) {
		//Debug::Arr($data, 'Input Transaction Data...', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($invoice_data, 'Input Invoice Data...', __FILE__, __LINE__, __METHOD__, 10);

		$if = TTnew( 'InvoiceFactory' );
		$if->setClient( $invoice_data[4] ); //Required to get shipment packing information and therefore shipping costs.

		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			$transaction_arr = $this->convertRawInvoiceDataToTransactionArray( $data );
			//Debug::Arr($data, 'bInput Transaction Data...', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($transaction_arr) AND is_array($transaction_arr) ) {
				//Calc taxes first, add those in as transactions.
				if ( !isset($invoice_data[0]) ) {
					$invoice_data[0] = NULL;
				}
				if ( !isset($invoice_data[1]) ) {
					$invoice_data[1] = NULL;
				}

				$tmp_taxes_arr = $if->calcTaxes( $transaction_arr, $invoice_data[0], $invoice_data[1] );
				if ( is_array($tmp_taxes_arr) ) {
					foreach( $tmp_taxes_arr as $ptp_id => $ptp_data ) {
						$transaction_arr[] = array(
										'id' => NULL,
										'type_id' => 10,
										'product_id' => $ptp_data['product_id'],
										'product_name' => $ptp_data['product_name'],
										'product_type_id' => 50,
										'amount' => $ptp_data['amount']
										);
					}
				}
			}

			if ( isset($transaction_arr) AND is_array($transaction_arr) ) {
				$tmp_shipping_arr = $if->calcShipping( $transaction_arr, $invoice_data[1], $invoice_data[2], $invoice_data[3] );
				$tmp_shipping_arr['type_id'] = 10;
				$tmp_shipping_arr['product_type_id'] = 60;

				if ( isset($tmp_shipping_arr['amount']) AND $tmp_shipping_arr['amount'] > 0 ) {
					$transaction_arr[] = $tmp_shipping_arr;
				}
			}
		}

		$retval = $if->getTotalArray( $transaction_arr, $include_unconfirmed_transactions );

		//Debug::Arr($retval, 'Invoice getTotalArray()', __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getShippingOptions( $data, $invoice_data ) {
		$if = TTnew( 'InvoiceFactory' );
		$if->setClient( $invoice_data[4] ); //Required to get shipment packing information and therefore shipping costs.

		$transaction_arr = FALSE;
		if ( is_array($data) ) {
			$transaction_arr = $this->convertRawInvoiceDataToTransactionArray( $data );

			$shipment_packages = $if->getShipmentPackages( $transaction_arr );
			$customs_data = $if->getCustomsData( $transaction_arr );
			$shipping_option_data = $if->getShippingOptionData( $invoice_data[1], $shipment_packages, $customs_data, $invoice_data[3] );

			$shipping_options = $if->getShippingOptions( $shipping_option_data, $invoice_data[3], TRUE );
		}

		if ( isset($shipping_options) ) {
			//Debug::Arr($shipping_options, 'Shipping Options: ', __FILE__, __LINE__, __METHOD__, 10);
			return $shipping_options;
		}

		return FALSE;
	}

	function getCurrencyData( $currency_id ) {
		Debug::Text('Getting Currency Data for ID: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);

		$clf = TTnew( 'CurrencyListFactory' );
		$clf->getById( $currency_id );
		if ( $clf->getRecordCount() > 0 ) {
			$c_obj = $clf->getCurrent();

			$retarr = array(
							'id' => $c_obj->getId(),
							'conversion_rate' => $c_obj->getConversionRate(),
							'iso_code' => $c_obj->getISOCode()
							);

			return $retarr;
		}

		return FALSE;
	}

	function convertCurrency( $src_currency_id, $dst_currency_id, $amount ) {
		return CurrencyFactory::convertCurrency( $src_currency_id, $dst_currency_id, $amount );
	}

	function getScheduleTotalTime( $start, $end, $schedule_policy_id ) {
		$sf = TTnew( 'ScheduleFactory' );
		$sf->setStartTime( TTDate::parseDateTime($start) );
		$sf->setEndTime( TTDate::parseDateTime($end) );
		$sf->setSchedulePolicyId( $schedule_policy_id );
		$sf->preSave();

		return TTDate::getTimeUnit( $sf->getTotalTime() );
	}

	function getAbsencePolicyData( $absence_policy_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getByIdAndCompanyId( $absence_policy_id, $current_company->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();

			$ap_data = $ap_obj->getObjectAsArray();

			$aplf = TTnew( 'AccrualPolicyListFactory' );
			$aplf->getByIdAndCompanyId( $ap_obj->getAccrualPolicyID(), $current_company->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				$ap_data['accrual_policy_name'] = $aplf->getCurrent()->getName();
			} else {
				$ap_data['accrual_policy_name'] = 'None';
			}

			return $ap_data;
		}

		return FALSE;
	}

	function getAbsencePolicyBalance( $absence_policy_id, $user_id ) {
		global $current_company;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getByIdAndCompanyId( $absence_policy_id, $current_company->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			if ( $ap_obj->getAccrualPolicyID() != '' ) {
				return $this->getAccrualBalance( $ap_obj->getAccrualPolicyID(), $user_id );
			}
		}

		return FALSE;
	}

	function getAccrualBalance( $accrual_policy_id, $user_id ) {
		if ( $accrual_policy_id == '' ) {
			return FALSE;
		}
		if ( $user_id == '' ) {
			return FALSE;
		}

		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		return TTDate::getTimeUnit($accrual_balance);
	}

	function getNextPayStubAccountOrderByTypeId( $type_id ) {
		global $current_company;

		Debug::Text('Type ID: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( $type_id == '' ) {
			return FALSE;
		}

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getHighestOrderByCompanyIdAndTypeId( $current_company->getId(), $type_id );
		if ( $psealf->getRecordCount() > 0 ) {
			foreach( $psealf as $psea_obj ) {
				return ($psea_obj->getOrder()+1);
			}
		}

		return FALSE;
	}

	function strtotime($str) {
		return TTDate::strtotime($str);
	}

	function parseDateTime($str) {
		return TTDate::parseDateTime( $str );
	}

	function getDate( $format, $epoch ) {
		return TTDate::getDate( $format, $epoch);
	}

	function getBeginMonthEpoch( $epoch ) {
		return TTDate::getBeginMonthEpoch( $epoch );
	}

	function getTimeZoneOffset( $time_zone ) {
		TTDate::setTimeZone( $time_zone );
		return TTDate::getTimeZoneOffset();
	}

	function test($str) {
		sleep(2);
		return $str;
	}

	function vardump($arr) {
		Debug::Arr($arr, 'vardump!', __FILE__, __LINE__, __METHOD__, 10);

		foreach( $arr as $key => $value ) {
			Debug::text('Key: '. $key .' Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array($value)  ) {
				foreach( $value as $keyb => $valueb ) {
					Debug::text('bKey: '. $keyb .' bValue: '. $valueb, __FILE__, __LINE__, __METHOD__, 10);
				}

			}
		}

	}

}
?>
