<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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


/**
 * @package Modules\Users
 */

class UserDeductionFactory extends Factory {
	protected $table = 'user_deduction';
	protected $pk_sequence_name = 'user_deduction_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $company_deduction_obj = NULL;
	var $pay_stub_entry_account_link_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-type' => TTi18n::gettext('Type'),
										'-1030-name' => TTi18n::gettext('Tax / Deduction'),
										'-1040-calculation' => TTi18n::gettext('Calculation'),

										'-1110-first_name' => TTi18n::gettext('First Name'),
										'-1120-last_name' => TTi18n::gettext('Last Name'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'type',
								'name',
								'calculation',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
										'company_deduction_id' => 'CompanyDeduction',

										//CompanyDeduction
										'name' => FALSE, //CompanyDeduction Name
										'status_id' => FALSE,
										'status' => FALSE,
										'type_id' => FALSE,
										'type' => FALSE,
										'calculation_id' => FALSE,
										'calculation' => FALSE,

										'first_name' => FALSE,
										'last_name' => FALSE,

										'user_value1' => 'UserValue1',
										'user_value2' => 'UserValue2',
										'user_value3' => 'UserValue3',
										'user_value4' => 'UserValue4',
										'user_value5' => 'UserValue5',
										'user_value6' => 'UserValue6',
										'user_value7' => 'UserValue7',
										'user_value8' => 'UserValue8',
										'user_value9' => 'UserValue9',
										'user_value10' => 'UserValue10',
										
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getCompanyDeductionObject() {
		return $this->getGenericObject( 'CompanyDeductionListFactory', $this->getCompanyDeduction(), 'company_deduction_obj' );
	}

	//Do not replace this with getGenericObject() as it uses the CompanyID not the ID itself.
	function getPayStubEntryAccountLinkObject() {
		if ( is_object($this->pay_stub_entry_account_link_obj) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
			$pseallf->getByCompanyID( $this->getUserObject()->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();
				return $this->pay_stub_entry_account_link_obj;
			}

			return FALSE;
		}
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueCompanyDeduction($deduction_id) {
		$ph = array(
					'user_id' => (int)$this->getUser(),
					'deduction_id' => (int)$deduction_id,
					);

		$query = 'select id from '. $this->getTable() .' where user_id = ? AND company_deduction_id = ? AND deleted = 0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Company Deduction: '. $deduction_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getCompanyDeduction() {
		if ( isset($this->data['company_deduction_id']) ) {
			return (int)$this->data['company_deduction_id'];
		}

		return FALSE;
	}
	function setCompanyDeduction($id) {
		$id = trim($id);

		Debug::Text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$cdlf = TTnew( 'CompanyDeductionListFactory' );

		if (	(
					$id != 0
					OR
					$this->Validator->isResultSetWithRows(	'company_deduction',
															$cdlf->getByID($id),
															TTi18n::gettext('Tax/Deduction is invalid')
														)
				) ) {

			$this->data['company_deduction_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1() {
		if ( isset($this->data['user_value1']) ) {
			return $this->data['user_value1'];
		}

		return FALSE;
	}
	function setUserValue1($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value1',
												$value,
												TTi18n::gettext('User Value 1 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue2() {
		if ( isset($this->data['user_value2']) ) {
			return $this->data['user_value2'];
		}

		return FALSE;
	}
	function setUserValue2($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value2',
												$value,
												TTi18n::gettext('User Value 2 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue3() {
		if ( isset($this->data['user_value3']) ) {
			return $this->data['user_value3'];
		}

		return FALSE;
	}
	function setUserValue3($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value3',
												$value,
												TTi18n::gettext('User Value 3 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue4() {
		if ( isset($this->data['user_value4']) ) {
			return $this->data['user_value4'];
		}

		return FALSE;
	}
	function setUserValue4($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value4',
												$value,
												TTi18n::gettext('User Value 4 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue5() {
		if ( isset($this->data['user_value5']) ) {
			return $this->data['user_value5'];
		}

		return FALSE;
	}
	function setUserValue5($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value5',
												$value,
												TTi18n::gettext('User Value 5 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value5'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue6() {
		if ( isset($this->data['user_value6']) ) {
			return $this->data['user_value6'];
		}

		return FALSE;
	}
	function setUserValue6($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value6',
												$value,
												TTi18n::gettext('User Value 6 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value6'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue7() {
		if ( isset($this->data['user_value7']) ) {
			return $this->data['user_value7'];
		}

		return FALSE;
	}
	function setUserValue7($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value7',
												$value,
												TTi18n::gettext('User Value 7 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value7'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue8() {
		if ( isset($this->data['user_value8']) ) {
			return $this->data['user_value8'];
		}

		return FALSE;
	}
	function setUserValue8($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value8',
												$value,
												TTi18n::gettext('User Value 8 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value8'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue9() {
		if ( isset($this->data['user_value9']) ) {
			return $this->data['user_value9'];
		}

		return FALSE;
	}
	function setUserValue9($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value9',
												$value,
												TTi18n::gettext('User Value 9 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value9'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue10() {
		if ( isset($this->data['user_value10']) ) {
			return $this->data['user_value10'];
		}

		return FALSE;
	}
	function setUserValue10($value) {
		$value = trim($value);

		if	(	$value == ''
				OR
				$this->Validator->isLength(		'user_value10',
												$value,
												TTi18n::gettext('User Value 10 is too short or too long'),
												1,
												20) ) {

			$this->data['user_value10'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Primarily used to display marital status/allowances/claim amounts on pay stubs.
	function getDescription( $transaction_date = FALSE ) {
		$retval = FALSE;

		//Calculates the deduction.
		$cd_obj = $this->getCompanyDeductionObject();

		if ( $this->getUserValue1() == '' ) {
			$user_value1 = $cd_obj->getUserValue1();
		} else {
			$user_value1 = $this->getUserValue1();
		}

		if ( $this->getUserValue2() == '' ) {
			$user_value2 = $cd_obj->getUserValue2();
		} else {
			$user_value2 = $this->getUserValue2();
		}

		if ( $this->getUserValue3() == '' ) {
			$user_value3 = $cd_obj->getUserValue3();
		} else {
			$user_value3 = $this->getUserValue3();
		}

		if ( $transaction_date == '' ) {
			$transaction_date = time();
		}

		if ( strtolower( $cd_obj->getCountry() ) == 'ca' ) {
			require_once( Environment::getBasePath(). DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'payroll_deduction'. DIRECTORY_SEPARATOR .'PayrollDeduction.class.php');
			$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), $cd_obj->getProvince() );
			$pd_obj->setDate( $transaction_date );
		}

		//Debug::Text('UserDeduction ID: '. $this->getID() .' Calculation ID: '. $cd_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $cd_obj->getCalculation() ) {
			case 100: //Federal
				$country_label = strtoupper( $cd_obj->getCountry() );

				if ( strtolower( $cd_obj->getCountry() ) == 'ca' ) {
					//Filter Claim Amount through PayrollDeduction class so it can be automatically adjusted if necessary.
					$pd_obj->setFederalTotalClaimAmount( $user_value1 );
					$retval = $country_label .' - '. TTI18n::getText('Claim Amount').': $'. Misc::MoneyFormat( $pd_obj->getFederalTotalClaimAmount() );
				} elseif ( strtolower( $cd_obj->getCountry() ) == 'us' ) {
					$retval = $country_label .' - '. TTI18n::getText('Filing Status').': '. Option::getByKey( $user_value1, $cd_obj->getOptions('federal_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. $user_value2;
				}
				break;
			case 200:
				$province_label = strtoupper( $cd_obj->getProvince() );

				if ( strtolower( $cd_obj->getCountry() ) == 'ca' ) {
					//Filter Claim Amount through PayrollDeduction class so it can be automatically adjusted if necessary.
					$pd_obj->setProvincialTotalClaimAmount( $user_value1 );
					$retval = $province_label.' - '. TTI18n::getText('Claim Amount').': $'. Misc::MoneyFormat( $pd_obj->getProvincialTotalClaimAmount() );
				} elseif ( $cd_obj->getCountry() == 'US' ) {
					switch( strtolower( $cd_obj->getProvince() ) ) {
						case 'az': //Percent
							$retval = $province_label.' - '. TTI18n::getText('Percent', $province_label ).': '. (float)$user_value1 .'%';
							break;
						case 'md': //County Rate
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_filing_status') ) .' '. TTI18n::getText('County Rate') .': '. (float)$user_value2 .'%';
							break;
						case 'il':
							$retval = $province_label.' - '.  TTI18n::getText('IL-W-4 Line 1') .': '. (int)$user_value1 .' '. TTI18n::getText('IL-W-4 Line 2') .': '. (int)$user_value2;
							break;
						case 'oh':
						case 'va':
						case 'ar':
						case 'ia':
						case 'ky':
						case 'mi':
						case 'mt':
							$retval = $province_label.' - '. TTI18n::getText('Allowances', $province_label ).': '. (int)$user_value2;
							break;
						case 'in':
							$retval = $province_label.' - '. TTI18n::getText('Allowances', $province_label ).': '. (int)$user_value1 .' '. TTI18n::getText('Dependents') .': '. (int)$user_value2;
							break;
						case 'ga':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_ga_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2 .' '. TTI18n::getText('Dependent Allowances') .': '. (int)$user_value3;
							break;
						case 'nj':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_nj_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'nc':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_nc_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'ma':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_ma_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'al':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_al_filing_status') ) .' '. TTI18n::getText('Dependents') .': '. (int)$user_value2;
							break;
						case 'ct':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_ct_filing_status') );
							break;
						case 'wv':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_wv_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'me':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_me_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'de':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_de_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'dc':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_dc_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
						case 'la':
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value3, $cd_obj->getOptions('state_la_filing_status') ) .' '. TTI18n::getText('Dependents') .': '. (int)$user_value2 .' '. TTI18n::getText('Exemptions') .': '. (int)$user_value1;
							break;
						default:
							$retval = $province_label.' - '. TTI18n::getText('Filing Status', $province_label ).': '. Option::getByKey( $user_value1, $cd_obj->getOptions('state_filing_status') ) .' '. TTI18n::getText('Allowances') .': '. (int)$user_value2;
							break;
					}
				}
				break;
		}

		return $retval;
	}

	//function getDeductionAmount( $user_id, $pay_stub_id, $annual_pay_periods, $date = NULL ) {
	function getDeductionAmount( $user_id, $pay_stub_obj, $pay_period_obj ) {
		if ( $user_id == '' ) {
			Debug::Text('Missing User ID: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( !is_object($pay_stub_obj) ) {
			Debug::Text('Missing Pay Stub Object: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( !is_object($pay_period_obj) ) {
			Debug::Text('Missing Pay Period Object: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Calculates the deduction.
		$cd_obj = $this->getCompanyDeductionObject();

		$annual_pay_periods = $pay_period_obj->getPayPeriodScheduleObject()->getAnnualPayPeriods();
		if ( $annual_pay_periods <= 0 ) {
			$annual_pay_periods = 1;
		}

		if ( !is_object($cd_obj) ) {
			return FALSE;
		}

		require_once( Environment::getBasePath(). DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .'payroll_deduction'. DIRECTORY_SEPARATOR .'PayrollDeduction.class.php');

		$retval = 0;

		Debug::Text('Company Deduction: ID: '. $cd_obj->getID() .' Name: '. $cd_obj->getName() .' Calculation ID: '. $cd_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $cd_obj->getCalculation() ) {
			case 10: //Basic Percent
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}
				$percent = $this->Validator->stripNonFloat( $percent );

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				$retval = bcmul($amount, bcdiv($percent, 100) );

				break;
			case 15: //Advanced Percent
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}
				$percent = $this->Validator->stripNonFloat( $percent );

				if ( $this->getUserValue2() == '' ) {
					$wage_base = $cd_obj->getUserValue2();
				} else {
					$wage_base = $this->getUserValue2();
				}
				$wage_base = $this->Validator->stripNonFloat( $wage_base );

				if ( $this->getUserValue3() == '' ) {
					$exempt_amount = $cd_obj->getUserValue3();
				} else {
					$exempt_amount = $this->getUserValue3();
				}
				$exempt_amount = $this->Validator->stripNonFloat( $exempt_amount );

				//Annual Wage Base is the maximum earnings that an employee can earn before they are no longer eligible for this deduction
				//Annual Deduction Amount

				Debug::Text('Percent: '. $percent .' Wage Base: '. $wage_base .' Exempt Amount: '. $exempt_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $percent != 0 ) {
				
					if ( $exempt_amount > 0 ) {
						$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), bcdiv( $exempt_amount, $annual_pay_periods ) );
						Debug::Text('Amount After Exemption: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
					} else {
						$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );
						Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
					}

					if ( $wage_base > 0 ) {
						//*NOTE: If the first pay stub in TimeTrex is near the end of the year, and the employee has already exceeded the wage base amount
						//the payroll admin needs to make sure they add a YTD Adjustment for each Include PS Accounts that this calculation is based on,
						//NOT the total amount they have paid for the resulting calculation, as that has no effect whatsoever.

						//getCalculationYTDAmount is the previous pay stub YTD amount, but it includes any YTD Adjustments in the current pay stub too.
						$ytd_amount = $cd_obj->getCalculationYTDAmount( $pay_stub_obj );
						Debug::Text('Wage Base is set: '. $wage_base .' Amount: '. $amount .' Current YTD: '. $ytd_amount, __FILE__, __LINE__, __METHOD__, 10);

						//Possible calcations:
						//
						//Wage Base: 3000
						//Amount: 500 YTD: 0		= 500
						//Amount: 500 YTD: 2900		= 100
						//Amount: 500 YTD: 3100		= 0
						//Amount: 3500 YTD: 0		= 3000
						//AMount: 3500 YTD: 2900	= 100
						//Amount: 3500 YTD: 3100	= 0

						//Check to see if YTD is less than wage base.
						$remaining_wage_base = bcsub($wage_base, $ytd_amount);
						Debug::Text('Remaining Wage Base to be calculated: '. $remaining_wage_base, __FILE__, __LINE__, __METHOD__, 10);
						if ( $remaining_wage_base > 0 ) {
							if ( $amount > $remaining_wage_base ) {
								$amount = $remaining_wage_base;
							}
						} else {
							$amount = 0; //Exceeded wage base, nothing to calculate.
						}
						unset($remaining_wage_base);
					} else {
						Debug::Text('Wage Base is NOT set: '. $wage_base, __FILE__, __LINE__, __METHOD__, 10);
					}

					$retval = bcmul($amount, bcdiv($percent, 100) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $ytd_amount, $percent, $wage_base);

				break;
			case 17: //Advanced Percent (Range Bracket)
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}
				$percent = $this->Validator->stripNonFloat( $percent );

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				if ( $this->getUserValue5() == '' ) {
					$annual_fixed_amount = $cd_obj->getUserValue5();
				} else {
					$annual_fixed_amount = $this->getUserValue5();
				}
				$annual_fixed_amount = $this->Validator->stripNonFloat( $annual_fixed_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text('Percent: '. $percent .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = bcadd( bcmul($amount, bcdiv($percent, 100) ), $annual_fixed_amount);
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount);

				break;
			case 18: //Advanced Percent (Tax Bracket)
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}
				$percent = $this->Validator->stripNonFloat( $percent );

				if ( $this->getUserValue2() == '' ) {
					$wage_base = $cd_obj->getUserValue2();
				} else {
					$wage_base = $this->getUserValue2();
				}
				$wage_base = $this->Validator->stripNonFloat( $wage_base );

				if ( $this->getUserValue3() == '' ) {
					$exempt_amount = $cd_obj->getUserValue3();
				} else {
					$exempt_amount = $this->getUserValue3();
				}
				$exempt_amount = $this->Validator->stripNonFloat( $exempt_amount );

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				Debug::Text('Percent: '. $percent .' Wage Base: '. $wage_base .' Exempt Amount: '. $exempt_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $percent != 0 ) {
					if ( $exempt_amount > 0 ) {
						$pp_exempt_amount = bcdiv( $exempt_amount, $annual_pay_periods );
					} else {
						$pp_exempt_amount = 0;
					}
					//Debug::Text('PP Exempt Amount: '. $pp_exempt_amount, __FILE__, __LINE__, __METHOD__, 10);

					if ( $wage_base > 0 ) {
						$pp_wage_base_amount = bcdiv( $wage_base, $annual_pay_periods );
					} else {
						$pp_wage_base_amount = 0;
					}

					if ( $annual_deduction_amount > 0 ) {
						$pp_annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
					} else {
						$pp_annual_deduction_amount = 0;
					}

					//Debug::Text('PP Wage Base Base Amount: '. $pp_wage_base_amount, __FILE__, __LINE__, __METHOD__, 10);
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $pp_annual_deduction_amount );

					//Debug::Text('Calculation Pay Stub Amount: '. $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), __FILE__, __LINE__, __METHOD__, 10);
					if (  $pp_wage_base_amount > 0
							AND $amount > $pp_wage_base_amount ) {
						//Debug::Text('Exceeds Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__, 10);
						$amount = bcsub( $pp_wage_base_amount, $pp_exempt_amount );
					} else {
						//Debug::Text('Under Wage Base...'. $amount, __FILE__, __LINE__, __METHOD__, 10);
						$amount = bcsub( $amount, $pp_exempt_amount );
					}
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

					$retval = bcmul($amount, bcdiv($percent, 100) );
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $wage_base, $pp_wage_base_amount, $pp_exempt_amount, $annual_deduction_amount, $pp_annual_deduction_amount);

				break;
			case 19: //Advanced Percent (Tax Bracket Alternate)
				/*
					This is designed to be used for single line item tax calculations, in that the formula looks like this,
					where only ONE bracket would be applied to the employee, NOT all:
					Wage between 0 - 10, 000 calculate 10%
					Wage between 10, 001 - 20, 000 calculate 15% + $1000 (10% of 10, 000 as per above)
					Wage between 20, 001 - 30, 000 calculate 20% + $2500 (10% of 10, 000 as first bracket, and 15% of 10, 000 as per 2nd bracket)
				*/
				if ( $this->getUserValue1() == '' ) {
					$percent = $cd_obj->getUserValue1();
				} else {
					$percent = $this->getUserValue1();
				}
				$percent = $this->Validator->stripNonFloat( $percent );

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				if ( $this->getUserValue5() == '' ) {
					$annual_fixed_amount = $cd_obj->getUserValue5();
				} else {
					$annual_fixed_amount = $this->getUserValue5();
				}
				$annual_fixed_amount = $this->Validator->stripNonFloat( $annual_fixed_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );
				$annual_fixed_amount = bcdiv( $annual_fixed_amount, $annual_pay_periods );

				Debug::Text('Percent: '. $percent .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $percent != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = bcadd( bcmul( bcsub( $amount, $min_wage ), bcdiv($percent, 100) ), $annual_fixed_amount);
					}
				} else {
					$retval = 0;
				}

				if ( $percent >= 0 AND $retval < 0 ) {
					$retval = 0;
				}

				unset($amount, $percent, $min_wage, $max_wage, $annual_deduction_amount, $annual_fixed_amount);

				break;
			case 20: //Fixed amount
				if ( $this->getUserValue1() == '' ) {
					$amount = $cd_obj->getUserValue1();
				} else {
					$amount = $this->getUserValue1();
				}
				$amount = $this->Validator->stripNonFloat( $amount );

				$retval = $amount;
				unset($amount);

				break;
			case 30: //Fixed Amount (Range Bracket)
				if ( $this->getUserValue1() == '' ) {
					$fixed_amount = $cd_obj->getUserValue1();
				} else {
					$fixed_amount = $this->getUserValue1();
				}
				$fixed_amount = $this->Validator->stripNonFloat( $fixed_amount );

				if ( $this->getUserValue2() == '' ) {
					$min_wage = $cd_obj->getUserValue2();
				} else {
					$min_wage = $this->getUserValue2();
				}
				$min_wage = $this->Validator->stripNonFloat( $min_wage );

				if ( $this->getUserValue3() == '' ) {
					$max_wage = $cd_obj->getUserValue3();
				} else {
					$max_wage = $this->getUserValue3();
				}
				$max_wage = $this->Validator->stripNonFloat( $max_wage );

				if ( $this->getUserValue4() == '' ) {
					$annual_deduction_amount = $cd_obj->getUserValue4();
				} else {
					$annual_deduction_amount = $this->getUserValue4();
				}
				$annual_deduction_amount = $this->Validator->stripNonFloat( $annual_deduction_amount );

				$min_wage = bcdiv( $min_wage, $annual_pay_periods);
				$max_wage = bcdiv( $max_wage, $annual_pay_periods);
				$annual_deduction_amount = bcdiv( $annual_deduction_amount, $annual_pay_periods );

				Debug::Text('Amount: '. $fixed_amount .' Min Wage: '. $min_wage .' Max Wage: '. $max_wage .' Annual Deduction: '. $annual_deduction_amount, __FILE__, __LINE__, __METHOD__, 10);

				if ( $fixed_amount != 0 ) {
					$amount = bcsub( $cd_obj->getCalculationPayStubAmount( $pay_stub_obj ), $annual_deduction_amount );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);

					if ( $amount >= $min_wage AND $amount <= $max_wage ) {
						$retval = $fixed_amount;
					}
				} else {
					$retval = 0;
				}

				unset($fixed_amount, $amount, $percent, $min_wage, $max_wage, $annual_deduction_amount);

				break;
			case 52: //Fixed Amount (w/Limit)
				if ( $this->getUserValue1() == '' ) {
					$fixed_amount = $cd_obj->getUserValue1();
				} else {
					$fixed_amount = $this->getUserValue1();
				}
				$fixed_amount = $this->Validator->stripNonFloat( $fixed_amount );

				if ( $this->getUserValue2() == '' ) {
					$target_amount = $cd_obj->getUserValue2();
				} else {
					$target_amount = $this->getUserValue2();
				}
				$target_amount = $this->Validator->stripNonFloat( $target_amount );

				Debug::Text('Amount: '. $fixed_amount .' Target Amount: '. $target_amount, __FILE__, __LINE__, __METHOD__, 10);

				$retval = 0;
				if ( $fixed_amount != 0 ) {
					$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );
					Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
					if ( $amount !== $target_amount ) {
						if ( abs($fixed_amount) < abs(bcsub($amount, $target_amount)) ) {
							//Use full fixed amount
							Debug::Text('Not within reach of target, use full fixed amount...', __FILE__, __LINE__, __METHOD__, 10);
							$retval = $fixed_amount;
						} else {
							Debug::Text('Within reach of target, use partial fixed amount...', __FILE__, __LINE__, __METHOD__, 10);
							//Use partial fixed_amount
							$retval = bcadd( abs($amount), $target_amount);
						}
					}
				}

				$retval = abs($retval);

				unset($fixed_amount, $tmp_amount, $amount, $min_limit, $max_limit);

				break;
			case 69: // Custom Formulas
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$user_value3 = $cd_obj->getUserValue3();
				} else {
					$user_value3 = $this->getUserValue3();
				}

				if ( $this->getUserValue4() == '' ) {
					$user_value4 = $cd_obj->getUserValue4();
				} else {
					$user_value4 = $this->getUserValue4();
				}

				if ( $this->getUserValue5() == '' ) {
					$user_value5 = $cd_obj->getUserValue5();
				} else {
					$user_value5 = $this->getUserValue5();
				}

				if ( $this->getUserValue6() == '' ) {
					$user_value6 = $cd_obj->getUserValue6();
				} else {
					$user_value6 = $this->getUserValue6();
				}

				if ( $this->getUserValue7() == '' ) {
					$user_value7 = $cd_obj->getUserValue7();
				} else {
					$user_value7 = $this->getUserValue7();
				}

				if ( $this->getUserValue8() == '' ) {
					$user_value8 = $cd_obj->getUserValue8();
				} else {
					$user_value8 = $this->getUserValue8();
				}

				if ( $this->getUserValue9() == '' ) {
					$user_value9 = $cd_obj->getUserValue9();
				} else {
					$user_value9 = $this->getUserValue9();
				}

				if ( $this->getUserValue10() == '' ) {
					$user_value10 = $cd_obj->getUserValue10();
				} else {
					$user_value10 = $this->getUserValue10();
				}
				// evaluate math expressions as the company_value1 and user_value1-10 defined by user.
				$company_value1 = $cd_obj->getCompanyValue1(); // Custom Formula

				$variables = array();
				$formula_variables = array_keys( (array)TTMath::parseColumnsFromFormula( $company_value1 ) );
				Debug::Arr( $formula_variables, 'Formula Variables: ', __FILE__, __LINE__, __METHOD__, 10 );

				if ( is_array($formula_variables) ) {
					$udtlf = TTnew( 'UserDateTotalListFactory' );

					if ( in_array('currency_conversion_rate', $formula_variables) AND is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCurrencyObject() ) ) {
						$currency_iso_code = $this->getUserObject()->getCurrencyObject()->getISOCode();
						$currency_conversion_rate = $this->getUserObject()->getCurrencyObject()->getConversionRate();
						Debug::Text( 'Currency Variables: Rate: '. $currency_conversion_rate .' ISO: '. $currency_iso_code, __FILE__, __LINE__, __METHOD__, 10 );
					}

					//First pass to gather any necessary data based on variables
					if ( in_array('employee_hourly_rate', $formula_variables) OR in_array('employee_annual_wage', $formula_variables ) OR in_array( 'employee_wage_average_weekly_hours', $formula_variables ) ) {
						$uwlf = TTnew('UserWageListFactory');
						$uwlf->getWageByUserIdAndPayPeriodEndDate( $this->getUser(), $pay_period_obj->getEndDate() );
						if ( $uwlf->getRecordCount() > 0 ) {
							$uwf = $uwlf->getCurrent();
							$employee_hourly_rate = $uwf->getHourlyRate();
							$employee_annual_wage = $uwf->getAnnualWage();
							$employee_wage_average_weekly_hours = TTDate::getHours( $uwf->getWeeklyTime() );
						} else {
							$employee_hourly_rate = 0;
							$employee_annual_wage = 0;
							$employee_wage_average_weekly_hours = 0;
						}
						Debug::Text( 'Employee Hourly Rate: '. $employee_hourly_rate, __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( in_array('pay_period_worked_days', $formula_variables) OR in_array('pay_period_paid_days', $formula_variables ) ) {
						$pay_period_days_worked = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
						$pay_period_days_absence = (array)$udtlf->getDaysPaidAbsenceByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
					}					
					if ( in_array('pay_period_worked_time', $formula_variables) OR in_array('pay_period_paid_time', $formula_variables ) ) {
						$pay_period_worked_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
						$pay_period_absence_time = $udtlf->getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $pay_period_obj->getStartDate(), $pay_period_obj->getEndDate() );
					}

					if ( $cd_obj->getCompanyValue2() != '' AND $cd_obj->getCompanyValue2() > 0 AND $cd_obj->getCompanyValue3() != '' AND $cd_obj->getCompanyValue3() > 0 ) {
						Debug::Text( 'Formula Lookback enable: '. $cd_obj->getCompanyValue2(), __FILE__, __LINE__, __METHOD__, 10 );
						foreach( $formula_variables as $formula_variable ) {
							if ( strpos( $formula_variable, 'lookback_' ) !== FALSE ) {
								Debug::Text( 'Lookback variables exist...', __FILE__, __LINE__, __METHOD__, 10 );
								$lookback_dates = $cd_obj->getLookbackStartAndEndDates( $pay_period_obj );
								$lookback_pay_stub_dates = $cd_obj->getLookbackPayStubs( $this->getUser(), $pay_period_obj );
								//Debug::Arr( $lookback_dates, 'Lookback Dates...', __FILE__, __LINE__, __METHOD__, 10 );
								//Debug::Arr( $lookback_pay_stub_dates, 'Lookback PayStub Dates...', __FILE__, __LINE__, __METHOD__, 10 );
								break;
							}
						}
					}

					if ( isset($lookback_pay_stub_dates['first_pay_stub_start_date']) AND isset($lookback_pay_stub_dates['last_pay_stub_end_date'])
							AND in_array('lookback_pay_stub_worked_days', $formula_variables) OR in_array('lookback_pay_stub_paid_days', $formula_variables ) ) {
						Debug::Text( 'Lookback Pay Stub Dates... Start: '. TTDate::getDate('DATE', $lookback_pay_stub_dates['first_pay_stub_start_date'] ) .' End: '. TTDate::getDate('DATE', $lookback_pay_stub_dates['last_pay_stub_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						$lookback_pay_stub_days_worked = (array)$udtlf->getDaysWorkedByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
						$lookback_pay_stub_days_absence = (array)$udtlf->getDaysPaidAbsenceByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
					}
					if ( isset($lookback_pay_stub_dates['first_pay_stub_start_date']) AND isset($lookback_pay_stub_dates['last_pay_stub_end_date'])
							AND in_array('lookback_pay_stub_worked_time', $formula_variables) OR in_array('lookback_pay_stub_paid_time', $formula_variables ) ) {
						Debug::Text( 'Lookback Pay Stub Dates... Start: '. TTDate::getDate('DATE', $lookback_pay_stub_dates['first_pay_stub_start_date'] ) .' End: '. TTDate::getDate('DATE', $lookback_pay_stub_dates['last_pay_stub_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						$lookback_pay_stub_worked_time = $udtlf->getWorkedTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
						$lookback_pay_stub_absence_time = $udtlf->getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $this->getUser(), $lookback_pay_stub_dates['first_pay_stub_start_date'], $lookback_pay_stub_dates['last_pay_stub_end_date'] );
					}

					//Second pass to define variables.
					foreach( $formula_variables as $formula_variable ) {
						if ( !isset($variables[$formula_variable]) ) {
							switch ($formula_variable) {
								case 'custom_value1':
									$variables[$formula_variable] = $user_value1;
									break;
								case 'custom_value2':
									$variables[$formula_variable] = $user_value2;
									break;
								case 'custom_value3':
									$variables[$formula_variable] = $user_value3;
									break;
								case 'custom_value4':
									$variables[$formula_variable] = $user_value4;
									break;
								case 'custom_value5':
									$variables[$formula_variable] = $user_value5;
									break;
								case 'custom_value6':
									$variables[$formula_variable] = $user_value6;
									break;
								case 'custom_value7':
									$variables[$formula_variable] = $user_value7;
									break;
								case 'custom_value8':
									$variables[$formula_variable] = $user_value8;
									break;
								case 'custom_value9':
									$variables[$formula_variable] = $user_value9;
									break;
								case 'custom_value10':
									$variables[$formula_variable] = $user_value10;
									break;

								case 'employee_hourly_rate':
									$variables[$formula_variable] = $employee_hourly_rate;
									break;
								case 'employee_annual_wage':
									$variables[$formula_variable] = $employee_annual_wage;
									break;
								case 'employee_wage_average_weekly_hours':
									$variables[$formula_variable] = $employee_wage_average_weekly_hours;
									break;

								case 'annual_pay_periods':
									$variables[$formula_variable] = $annual_pay_periods;
									break;

								case 'pay_period_start_date':
									$variables[$formula_variable] = $pay_period_obj->getStartDate();
									break;
								case 'pay_period_end_date':
									$variables[$formula_variable] = $pay_period_obj->getEndDate();
									break;
								case 'pay_period_transaction_date':
									$variables[$formula_variable] = $pay_period_obj->getTransactionDate();
									break;
								case 'pay_period_total_days':
									$variables[$formula_variable] = round( TTDate::getDays( ( TTDate::getEndDayEpoch( $pay_period_obj->getEndDate() ) - TTDate::getBeginDayEpoch( $pay_period_obj->getStartDate() ) ) ) );
									break;
								case 'pay_period_worked_days':
									$variables[$formula_variable] = count( array_unique( $pay_period_days_worked ) );
									break;
								case 'pay_period_paid_days':
									$variables[$formula_variable] = count( array_unique( array_merge( $pay_period_days_worked, $pay_period_days_absence ) ) );
									break;
								case 'pay_period_worked_time':
									$variables[$formula_variable] = $pay_period_worked_time;
									break;
								case 'pay_period_paid_time':
									$variables[$formula_variable] = ( $pay_period_worked_time + $pay_period_absence_time );
									break;

								case 'employee_hire_date':
									$variables[$formula_variable] = $this->getUserObject()->getHireDate();
									break;
								case 'employee_termination_date':
									$variables[$formula_variable] = $this->getUserObject()->getTerminationDate();
									break;
								case 'employee_birth_date':
									$variables[$formula_variable] = $this->getUserObject()->getBirthDate();
									break;

								case 'currency_iso_code':
									$variables[$formula_variable] = $currency_iso_code;
									break;
								case 'currency_conversion_rate':
									$variables[$formula_variable] = $currency_conversion_rate;
									break;

								case 'include_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 10 );
									break;
								case 'include_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 30 );
									break;
								case 'include_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 20 );
									break;
								case 'include_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 40 );
									break;
								case 'exclude_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, NULL, 10 );
									break;
								case 'exclude_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, NULL, 30 );
									break;
								case 'exclude_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, NULL, 20 );
									break;
								case 'exclude_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, NULL, 40 );
									break;
								case 'pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 10, 10 );
									break;
								case 'pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 30, 30 );
									break;
								case 'pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 20, 20 );
									break;
								case 'pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj, 40, 40 );
									break;

								//Lookback variables.
								case 'lookback_total_pay_stubs':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['total_pay_stubs']) ) ? $lookback_pay_stub_dates['total_pay_stubs'] : 0;
									break;
								case 'lookback_start_date':
									$variables[$formula_variable] = ( isset($lookback_dates['start_date']) ) ? $lookback_dates['start_date'] : 0;
									break;
								case 'lookback_end_date':
									$variables[$formula_variable] = ( isset($lookback_dates['end_date']) ) ? $lookback_dates['end_date'] : 0;
									break;
								case 'lookback_total_days':
									if ( isset($lookback_dates['start_date']) AND isset($lookback_dates['end_date']) ) {
										$variables[$formula_variable] = round( TTDate::getDays( ( TTDate::getEndDayEpoch( $lookback_dates['end_date'] ) - TTDate::getBeginDayEpoch( $lookback_dates['start_date'] ) ) ) );
									} else {
										$variables[$formula_variable] = 0;
									}
									break;
								case 'lookback_first_pay_stub_start_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['first_pay_stub_start_date']) ) ? $lookback_pay_stub_dates['first_pay_stub_start_date'] : 0;
									break;
								case 'lookback_first_pay_stub_end_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['first_pay_stub_end_date']) ) ? $lookback_pay_stub_dates['first_pay_stub_end_date'] : 0;
									break;
								case 'lookback_first_pay_stub_transaction_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['first_pay_stub_transaction_date']) ) ? $lookback_pay_stub_dates['first_pay_stub_transaction_date'] : 0;
									break;
								case 'lookback_last_pay_stub_start_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['last_pay_stub_start_date']) ) ? $lookback_pay_stub_dates['last_pay_stub_start_date'] : 0;
									break;
								case 'lookback_last_pay_stub_end_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['last_pay_stub_end_date']) ) ? $lookback_pay_stub_dates['last_pay_stub_end_date'] : 0;
									break;
								case 'lookback_last_pay_stub_transaction_date':
									$variables[$formula_variable] = ( isset($lookback_pay_stub_dates['last_pay_stub_transaction_date']) ) ? $lookback_pay_stub_dates['last_pay_stub_end_date'] : 0;
									break;

								case 'lookback_pay_stub_total_days':
									if ( isset($lookback_pay_stub_dates['first_pay_stub_start_date']) AND isset($lookback_pay_stub_dates['last_pay_stub_end_date']) ) {
										$variables[$formula_variable] = round( TTDate::getDays( ( ( TTDate::getEndDayEpoch( $lookback_pay_stub_dates['last_pay_stub_end_date'] ) - TTDate::getBeginDayEpoch( $lookback_pay_stub_dates['first_pay_stub_start_date'] ) ) ) ) );
									} else {
										$variables[$formula_variable] = 0;
									}
									break;
								case 'lookback_pay_stub_worked_days':
									$variables[$formula_variable] = count( array_unique( $lookback_pay_stub_days_worked ) );
									break;
								case 'lookback_pay_stub_paid_days':
									$variables[$formula_variable] = count( array_unique( array_merge( $lookback_pay_stub_days_worked, $lookback_pay_stub_days_absence ) ) );
									break;
								case 'lookback_pay_stub_worked_time':
									$variables[$formula_variable] = $lookback_pay_stub_worked_time;
									break;
								case 'lookback_pay_stub_paid_time':
									$variables[$formula_variable] = ( $lookback_pay_stub_worked_time + $lookback_pay_stub_absence_time );
									break;

								case 'lookback_include_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 10 );
									break;
								case 'lookback_include_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount(  30 );
									break;
								case 'lookback_include_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 20 );
									break;
								case 'lookback_include_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 40 );
									break;
								case 'lookback_exclude_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( NULL, 10 );
									break;
								case 'lookback_exclude_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( NULL, 30 );
									break;
								case 'lookback_exclude_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( NULL, 20 );
									break;
								case 'lookback_exclude_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( NULL, 40 );
									break;
								case 'lookback_pay_stub_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 10, 10 );
									break;
								case 'lookback_pay_stub_ytd_amount':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 30, 30 );
									break;
								case 'lookback_pay_stub_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 20, 20 );
									break;
								case 'lookback_pay_stub_ytd_units':
									$variables[$formula_variable] = $cd_obj->getLookbackCalculationPayStubAmount( 40, 40 );
									break;
							}
						}
					}

					unset( $uwlf, $uwf, $employee_hourly_rate, $employee_annual_wage, $employee_wage_average_weekly_hours, $annual_pay_periods, $lookback_dates, $lookback_pay_stub_dates, $currency_iso_code, $currency_conversion_rate, $pay_period_worked_time, $pay_period_absence_time, $lookback_pay_stub_worked_time, $lookback_pay_stub_absence_time, $pay_period_days_worked, $pay_period_days_absence, $lookback_pay_stub_days_worked, $lookback_pay_stub_days_absence );
				}

				//Debug::Arr( $variables, 'Formula Variable values: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( array( str_replace("\r", '; ', $company_value1 ), str_replace("\r", '; ', TTMath::translateVariables( $company_value1, $variables ) ) ), 'Original/Translated Formula: ', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = TTMath::evaluate( TTMath::translateVariables( $company_value1, $variables ) );

				Debug::Text( 'Formula Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10 );
				break;

			case 80: //US Earning Income Credit (EIC). Repealed as of 31-Dec-2010.
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__, 10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( 'US', NULL );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text('User Currency ID: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);
				}

				$pd_obj->setEICFilingStatus( $user_value1 );
				$pd_obj->setGrossPayPeriodIncome( $amount );

				//Allow negative value, infact it always should be.
				$retval = $pd_obj->getEIC();

				break;

			case 82: //US - Medicare - Employee
			case 83: //US - Medicare - Employer
			case 84: //US - Social Security - Employee
			case 85: //US - Social Security - Employer
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__, 10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( 'US', NULL );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text('User Currency ID: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );
				
				switch ( $cd_obj->getCalculation() ) {
					case 82: //US - Medicare - Employee
						$pd_obj->setMedicareFilingStatus( $user_value1 );
						$pd_obj->setYearToDateGrossIncome( $cd_obj->getCalculationYTDAmount( $pay_stub_obj ) ); //Make sure YTD amount is specified for all calculation types.
						$retval = $pd_obj->getEmployeeMedicare();
						break;
					case 83: //US - Medicare - Employer
						$retval = $pd_obj->getEmployerMedicare();
						break;
					case 84: //US - Social Security - Employee
						$pd_obj->setYearToDateSocialSecurityContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
						$retval = $pd_obj->getEmployeeSocialSecurity();
						break;
					case 85: //US - Social Security - Employer
						$pd_obj->setYearToDateSocialSecurityContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
						$retval = $pd_obj->getEmployerSocialSecurity();
						break;
				}

				break;
			case 90: //Canada - CPP
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( 'CA', NULL);
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setEnableCPPAndEIDeduction(TRUE);

				if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
					Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__, 10);

					$pd_obj->setYearToDateCPPContribution( $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
					/*
					$previous_ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
					$current_ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
					Debug::text('YTD CPP Contribution: Previous Amount: '. $previous_ytd_cpp_arr['ytd_amount'] .' Current Amount: '. $current_ytd_cpp_arr['amount'], __FILE__, __LINE__, __METHOD__, 10);

					$pd_obj->setYearToDateCPPContribution( bcadd($previous_ytd_cpp_arr['ytd_amount'], $current_ytd_cpp_arr['ytd_amount'] ) );
					unset($previous_ytd_cpp_arr, $current_ytd_cpp_arr);
					*/
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeCPP();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 91: //Canada - EI
				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( 'CA', NULL);
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setEnableCPPAndEIDeduction(TRUE);

				if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
					Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__, 10);

					$pd_obj->setYearToDateEIContribution(  $cd_obj->getPayStubEntryAccountYTDAmount( $pay_stub_obj ) );
					/*
					$previous_ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
					$current_ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
					Debug::text('YTD EI Contribution: Previous Amount: '. $previous_ytd_ei_arr['ytd_amount'] .' Current Amount: '. $current_ytd_ei_arr['amount'], __FILE__, __LINE__, __METHOD__, 10);

					$pd_obj->setYearToDateEIContribution( bcadd($previous_ytd_ei_arr['ytd_amount'], $current_ytd_ei_arr['ytd_amount'] ) );
					unset($previous_ytd_ei_arr, $current_ytd_ei_arr);
					*/
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getEmployeeEI();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 100: //Federal Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__, 10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), NULL );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				if ( is_object( $this->getUserObject() ) ) {
					$currency_id = $this->getUserObject()->getCurrency();
					$pd_obj->setUserCurrency( $currency_id );
					Debug::Text('User Currency ID: '. $currency_id, __FILE__, __LINE__, __METHOD__, 10);
				}

				if ( $this->getCompanyDeductionObject()->getCountry() == 'CA' ) {
					//CA
					$pd_obj->setFederalTotalClaimAmount( $user_value1 );

					$pd_obj->setEnableCPPAndEIDeduction(TRUE);

					//$pself = TTnew( 'PayStubEntryListFactory' );
					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
						Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__, 10);

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Single this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						if ( isset($current_cpp['amount']) AND $current_cpp['amount'] == 0 ) {
							Debug::Text('Current CPP: '. $current_cpp['amount'] .' Setting CPP exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10);
							$pd_obj->setCPPExempt( TRUE );
						}

						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );

						Debug::text('YTD CPP Contribution: '. $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10);

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset($ytd_cpp_arr, $current_cpp );
					}

					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
						Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__, 10);

						//See comment above regarding CPP exempt.
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						if ( isset($current_ei['amount']) AND $current_ei['amount'] == 0 ) {
							Debug::Text('Current EI: '. $current_ei['amount'] .' Setting EI exempt in Federal Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10);
							$pd_obj->setEIExempt( TRUE );
						}

						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );

						Debug::text('YTD EI Contribution: '. $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10);

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset($ytd_ei_arr, $current_ei);
					}
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'US' ) {
					//US
					$pd_obj->setFederalFilingStatus( $user_value1 );
					$pd_obj->setFederalAllowance( $user_value2 );
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'CR' ) {
					//CR
					$pd_obj->setFederalFilingStatus( $user_value1 ); //Single/Married
					$pd_obj->setFederalAllowance( $user_value2 );	 //Allownces/Children
				}

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getFederalPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 200: //Province Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$user_value3 = $cd_obj->getUserValue3();
				} else {
					$user_value3 = $this->getUserValue3();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('UserValue3: '. $user_value3, __FILE__, __LINE__, __METHOD__, 10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), $this->getCompanyDeductionObject()->getProvince() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				if ( $this->getCompanyDeductionObject()->getCountry() == 'CA' ) {
					Debug::Text('Canada Pay Period Deductions...', __FILE__, __LINE__, __METHOD__, 10);
					$pd_obj->setProvincialTotalClaimAmount( $user_value1 );

					$pd_obj->setEnableCPPAndEIDeduction(TRUE);

					//$pself = TTnew( 'PayStubEntryListFactory' );
					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() != '' ) {
						Debug::Text('Found Employee CPP account link!: ', __FILE__, __LINE__, __METHOD__, 10);

						//Check to see if CPP was calculated on the CURRENT pay stub, if not assume they are CPP exempt.
						//Single this calculation formula doesn't know directly if the user was CPP exempt or not, we have to assume it by
						//the calculate CPP on the current pay stub. However if the CPP calculation is done AFTER this, it may mistakenly assume they are exempt.
						//Make sure we handle the maximum CPP contribution cases properly as well.
						$current_cpp = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );
						if ( isset($current_cpp['amount']) AND $current_cpp['amount'] == 0 ) {
							Debug::Text('Current CPP: '. $current_cpp['amount'] .' Setting CPP exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10);
							$pd_obj->setCPPExempt( TRUE );
						}

						$ytd_cpp_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeCPP() );

						Debug::text('YTD CPP Contribution: '. $ytd_cpp_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10);

						$pd_obj->setYearToDateCPPContribution( $ytd_cpp_arr['ytd_amount'] );
						unset($ytd_cpp_arr, $current_cpp);
					}

					if ( $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() != '' ) {
						Debug::Text('Found Employee EI account link!: ', __FILE__, __LINE__, __METHOD__, 10);

						//See comment above regarding CPP exempt.
						$current_ei = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'current', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );
						if ( isset($current_ei['amount']) AND $current_ei['amount'] == 0 ) {
							Debug::Text('Current EI: '. $current_ei['amount'] .' Setting EI exempt in Provincial Income Tax calculation...', __FILE__, __LINE__, __METHOD__, 10);
							$pd_obj->setEIExempt( TRUE );
						}

						$ytd_ei_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( 'previous', NULL, $this->getPayStubEntryAccountLinkObject()->getEmployeeEI() );

						Debug::text('YTD EI Contribution: '. $ytd_ei_arr['ytd_amount'], __FILE__, __LINE__, __METHOD__, 10);

						$pd_obj->setYearToDateEIContribution( $ytd_ei_arr['ytd_amount'] );
						unset($ytd_ei_arr, $current_ei);
					}

					$retval = $pd_obj->getProvincialPayPeriodDeductions();
				} elseif ( $this->getCompanyDeductionObject()->getCountry() == 'US' ) {
					Debug::Text('US Pay Period Deductions...', __FILE__, __LINE__, __METHOD__, 10);

					//Need to set Federal settings here.
					$udlf = TTnew( 'UserDeductionListFactory' );
					$udlf->getByUserIdAndCountryID( $user_id, $this->getCompanyDeductionObject()->getCountry() );
					if ( $udlf->getRecordCount() > 0 ) {
						Debug::Text('Found Federal User Deduction...', __FILE__, __LINE__, __METHOD__, 10);

						$tmp_ud_obj = $udlf->getCurrent();

						if ( $tmp_ud_obj->getUserValue1() == '' ) {
							$tmp_user_value1 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue1();
						} else {
							$tmp_user_value1 = $tmp_ud_obj->getUserValue1();
						}

						if ( $tmp_ud_obj->getUserValue2() == '' ) {
							$tmp_user_value2 = $tmp_ud_obj->getCompanyDeductionObject()->getUserValue2();
						} else {
							$tmp_user_value2 = $tmp_ud_obj->getUserValue2();
						}

						Debug::Text('TmpUserValue1: '. $tmp_user_value1, __FILE__, __LINE__, __METHOD__, 10);
						Debug::Text('TmpUserValue2: '. $tmp_user_value2, __FILE__, __LINE__, __METHOD__, 10);

						$pd_obj->setFederalFilingStatus( $tmp_user_value1 );
						$pd_obj->setFederalAllowance( $tmp_user_value2 );

						unset($tmp_ud_obj, $tmp_user_value1, $tmp_user_value1);
					}
					unset($udlf);

					$pd_obj->setStateFilingStatus( $user_value1 );
					$pd_obj->setStateAllowance( $user_value2 );

					$pd_obj->setUserValue1( $user_value1 );
					$pd_obj->setUserValue2( $user_value2 );
					$pd_obj->setUserValue3( $user_value3 );

					$retval = $pd_obj->getStatePayPeriodDeductions();
				}

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
			case 300: //District Income Tax
				if ( $this->getUserValue1() == '' ) {
					$user_value1 = $cd_obj->getUserValue1();
				} else {
					$user_value1 = $this->getUserValue1();
				}

				if ( $this->getUserValue2() == '' ) {
					$user_value2 = $cd_obj->getUserValue2();
				} else {
					$user_value2 = $this->getUserValue2();
				}

				if ( $this->getUserValue3() == '' ) {
					$user_value3 = $cd_obj->getUserValue3();
				} else {
					$user_value3 = $this->getUserValue3();
				}

				Debug::Text('UserValue1: '. $user_value1, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('UserValue2: '. $user_value2, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('UserValue3: '. $user_value3, __FILE__, __LINE__, __METHOD__, 10);

				$amount = $cd_obj->getCalculationPayStubAmount( $pay_stub_obj );

				Debug::Text('Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('Annual Pay Periods: '. $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10);
				Debug::Text('District: '. $this->getCompanyDeductionObject()->getDistrict(), __FILE__, __LINE__, __METHOD__, 10);

				$pd_obj = new PayrollDeduction( $this->getCompanyDeductionObject()->getCountry(), $this->getCompanyDeductionObject()->getProvince(), $this->getCompanyDeductionObject()->getDistrict() );
				$pd_obj->setCompany( $this->getUserObject()->getCompany() );
				$pd_obj->setUser( $this->getUser() );
				$pd_obj->setDate( $pay_period_obj->getTransactionDate() );
				$pd_obj->setAnnualPayPeriods( $annual_pay_periods );

				$pd_obj->setDistrictFilingStatus( $user_value1 );
				$pd_obj->setDistrictAllowance( $user_value2 );

				$pd_obj->setUserValue1( $user_value1 );
				$pd_obj->setUserValue2( $user_value2 );
				$pd_obj->setUserValue3( $user_value3 );

				$pd_obj->setGrossPayPeriodIncome( $amount );

				$retval = $pd_obj->getDistrictPayPeriodDeductions();

				if ( $retval < 0 ) {
					$retval = 0;
				}

				break;
		}

		Debug::Text('Deduction Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		//Allow negative values, as some advanced tax bracket setups require this.
		if ( $retval < 0 ) {
			//Debug::Text('Deduction was negative, setting to 0...', __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text('Deduction was negative...', __FILE__, __LINE__, __METHOD__, 10);
			//$retval = 0;
		}

		return $retval;
	}

	//Returns the maximum taxable wages for any given calculation formula.
	//Returns FALSE for no maximum.
	//Primary used in TaxSummary (Generic) report.
	function getMaximumPayStubEntryAccountAmount() {
		$retval = FALSE;

		$cd_obj = $this->getCompanyDeductionObject();
		if ( is_object( $cd_obj ) ) {
			switch ( $cd_obj->getCalculation() ) {
				case 15: //Advanced Percent
					if ( $this->getUserValue2() == '' ) {
						$wage_base = $cd_obj->getUserValue2();
					} else {
						$wage_base = $this->getUserValue2();
					}
					$retval = $this->Validator->stripNonFloat( $wage_base );
					break;
				case 17: //Advanced Percent (Range Bracket)
					if ( $this->getUserValue3() == '' ) {
						$max_wage = $cd_obj->getUserValue3();
					} else {
						$max_wage = $this->getUserValue3();
					}
					$retval = $this->Validator->stripNonFloat( $max_wage );
					break;
				case 18: //Advanced Percent (Tax Bracket)
					if ( $this->getUserValue2() == '' ) {
						$wage_base = $cd_obj->getUserValue2();
					} else {
						$wage_base = $this->getUserValue2();
					}
					$retval = $this->Validator->stripNonFloat( $wage_base );
					break;
			}
		}

		return $retval;
	}

	//Returns the percent rate when specified.
	function getRate() {
		$retval = FALSE;

		$cd_obj = $this->getCompanyDeductionObject();
		if ( is_object( $cd_obj ) ) {
			switch ( $cd_obj->getCalculation() ) {
				case 15: //Advanced Percent
				case 17: //Advanced Percent (Range Bracket)
				case 18: //Advanced Percent (Tax Bracket)
					if ( $this->getUserValue1() == '' ) {
						$percent = $cd_obj->getUserValue1();
					} else {
						$percent = $this->getUserValue1();
					}
					$retval = $this->Validator->stripNonFloat( $percent );
					break;
			}
		}

		return $retval;
	}

	function Validate() {
		if ( $this->getUser() == FALSE ) {
			$this->Validator->isTrue(		'user',
											FALSE,
											TTi18n::gettext('Employee not specified'));
		}

		if ( $this->getDeleted() == FALSE AND $this->getCompanyDeduction() > 0 AND is_object( $this->getCompanyDeductionObject() ) ) {
			$this->Validator->isTrue(				'company_deduction',
													$this->isUniqueCompanyDeduction( $this->getCompanyDeduction() ),
													TTi18n::gettext('Tax/Deduction is already assigned to employee').': '. $this->getCompanyDeductionObject()->getName()
													);
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			$cdf = new CompanyDeductionFactory();

			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						//CompanyDeduction columns.
						case 'name':
						case 'status_id':
						case 'type_id':
						case 'calculation_id':
						//User columns.
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						//CompanyDeduction columns.
						case 'type':
						case 'status':
						case 'calculation':
							$data[$variable] = Option::getByKey( $this->getColumn( $variable.'_id' ), $cdf->getOptions( $variable ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		$obj = $this->getUserObject();
		if ( is_object($obj) ) {
			return TTLog::addEntry( $this->getCompanyDeduction(), $log_action, TTi18n::getText('Employee Deduction') .': '. $obj->getFullName(), NULL, $this->getTable(), $this );
		}
	}
}
?>
