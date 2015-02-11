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
class SetupPresets extends Factory {

	public $data = NULL;

	protected $company_obj = NULL;
	protected $user_obj = NULL;

	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getCompany() {
		return (int)$this->data['company_id'];
	}
	function setCompany( $id ) {
		$this->data['company_id'] = $id;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser( $id ) {
		$this->data['user_id'] = $id;
	}


	function createPayStubAccount( $data ) {
		if ( is_array($data) ) {

			$pseaf = TTnew( 'PayStubEntryAccountFactory' );
			$pseaf->setObjectFromArray( $data );
			if ( $pseaf->isValid() ) {
				return $pseaf->Save();
			}
		}

		return FALSE;
	}

	//Need to be able to add just global accounts, or country specific accounts, or just province specific accounts,
	//So this function should be called with once with no arguments, once for the country, and once for each province.
	//ie: PayStubAccounts(), PayStubAccounts( 'ca' ), PayStubAccounts( 'ca', 'bc' )
	function PayStubAccounts( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		//See if accounts are already linked
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $this->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$psealf = $pseallf->getCurrent();
		} else {
			$psealf = TTnew( 'PayStubEntryAccountLinkFactory' );
			$psealf->setCompany( $this->getCompany() );
		}

		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				case 'ca':
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'CA - Federal Income Tax',
														'ps_order' => 200,
													)
												);
					/* //Don't separate this into its own pay stub account, as for US at least we can't rejoin it when multiple states are involved.
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'CA - Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$cpp_employee_psea_id = $this->createPayStubAccount( //Need to update PayStubAccountLink for: $psealf->setEmployeeCPP( $psea_id );
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'CPP',
														'ps_order' => 203,
													)
												);
					if ( $cpp_employee_psea_id > 0 ) {
						$psealf->setEmployeeCPP( $cpp_employee_psea_id );
					}
					$ei_employee_psea_id = $this->createPayStubAccount( //Need to update PayStubAccountLink for:$psealf->setEmployeeEI( $psea_id );
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'EI',
														'ps_order' => 204,
													)
												);
					if ( $ei_employee_psea_id > 0 ) {
						$psealf->setEmployeeEI( $ei_employee_psea_id );
					}

					//Employer Contributions
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'CPP - Employer',
														'ps_order' => 303,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'EI - Employer',
														'ps_order' => 304,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'Workers Compensation - Employer',
														'ps_order' => 305,
													)
												);

					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 10,
														'name' => 'Vacation - No Accrual',
														'ps_order' => 180,
													)
												);
					$vacation_accrual_psea_id = $this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 50,
														'name' => 'Vacation Accrual',
														'ps_order' => 400,
													)
												);
					if ( $vacation_accrual_psea_id > 0 ) {
						$this->createPayStubAccount(
														array(
															'company_id' => $this->getCompany(),
															'status_id' => 10,
															'type_id' => 10,
															'name' => 'Vacation - Accrual Release',
															'ps_order' => 181,
															'accrual_pay_stub_entry_account_id' => $vacation_accrual_psea_id,
														)
													);
					}

					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'RRSP',
														'ps_order' => 206,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'RRSP - Employer',
														'ps_order' => 306,
													)
												);
					break;
				case 'us':
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'US - Federal Income Tax',
														'ps_order' => 200,
													)
												);
					/*
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'US - Federal Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'Social Security (FICA)',
														'ps_order' => 202,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'Social Security (FICA)',
														'ps_order' => 302,
													)
												);

					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'US - Federal Unemployment Insurance',
														'ps_order' => 303,
													)
												);

					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => 'Medicare',
														'ps_order' => 203,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'Medicare',
														'ps_order' => 303,
													)
												);

					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => '401(k)',
														'ps_order' => 230,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => '401(k)',
														'ps_order' => 330,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => 'Workers Compensation - Employer',
														'ps_order' => 305,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 10,
														'name' => 'Vacation',
														'ps_order' => 181,
													)
												);
					break;
				default:
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($country) .' - Federal Income Tax',
														'ps_order' => 200,
													)
												);
					/*
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($country) .' - Addl. Income Tax',
														'ps_order' => 201,
													)
												);
					*/
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 10,
														'name' => 'Vacation',
														'ps_order' => 181,
													)
												);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' AND $province != '' ) {
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => strtoupper($province) .' - Provincial Income Tax',
												'ps_order' => 202,
											)
										);
		}

		//United States
		if ( $country == 'us' AND $province != '' ) {
			if ( in_array( $province, array('al', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'dc', 'ga', 'hi', 'id', 'il',
											'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi', 'mn', 'ms', 'mo',
											'mt', 'ne', 'nj', 'nm', 'ny', 'nc', 'nd', 'oh', 'ok', 'or', 'pa', 'ri',
											'sc', 'ut', 'vt', 'va', 'wi', 'wv') ) ) {
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => strtoupper($province) .' - State Income Tax',
													'ps_order' => 204,
												)
											);
				/*
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => strtoupper($province) .' - State Addl. Income Tax',
													'ps_order' => 205,
												)
											);
				*/
			}

			//District/Local, income tax.
			if ( in_array( $province, array('al', 'ar', 'co', 'dc', 'de',
											'ia', 'in', 'ky', 'md', 'mi',
											'mo', 'ny', 'oh', 'or', 'pa') ) ) {
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => strtoupper($province) .' - District Income Tax',
													'ps_order' => 206,
												)
											);
			}

			//State Unemployement Insurace, deducted from employee
			if ( in_array( $province, array('ak', 'nj', 'pa') ) ) {
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => strtoupper($province) .' - Unemployment Insurance',
													'ps_order' => 207,
												)
											);
			}
			//State Unemployement Insurance, deducted from employer
			if ( in_array( $province, array('ak', 'al', 'ar', 'az', 'ca', 'co', 'ct', 'dc', 'de', 'fl', 'ga', 'hi',
											'ia', 'id', 'il', 'in', 'ks', 'ky', 'la', 'ma', 'md', 'me', 'mi', 'mn',
											'mo', 'ms', 'mt', 'nc', 'nd', 'ne', 'nh', 'nj', 'nm', 'nv', 'ny', 'oh',
											'ok', 'or', 'pa', 'sc', 'sd', 'tn', 'tx', 'ut', 'va', 'vt', 'wa', 'wi',
											'wv', 'wy') ) ) {
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 30,
													'name' => strtoupper($province) .' - Unemployment Insurance',
													'ps_order' => 306,
												)
											);
			}

			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			switch ($province) {
				//US
				case 'al': //alabama
					//Unemployment Insurance - Employee
					//Employment Security Asmt
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employment Security Assessment',
														'ps_order' => 310,
													)
												);
					break;
				case 'ak': //alaska
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					break;
				case 'az': //arizona
					//Unemployment Insurance - Employee
					//Surcharge
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Job Training Surcharge',
														'ps_order' => 310,
													)
												);
					break;
				case 'ar': //arkansas
					//Unemployment Insurance - Employee
					break;
				case 'ca': //california
					//Unemployment Insurance - Employee
					//Disability Insurance
					//Employee Training Tax
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance',
														'ps_order' => 210,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employee Training Tax',
														'ps_order' => 310,
													)
												);
					break;
				case 'co': //colorado
					//Unemployment Insurance - Employee
					break;
				case 'ct': //connecticut
					//Unemployment Insurance - Employee
					break;
				case 'de': //delaware
					//Unemployment Insurance - Employee
					break;
				case 'dc': //d.c.
					//Unemployment Insurance - Employee
					//Administrative Assessment
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Administrative Assessment',
														'ps_order' => 310,
													)
												);
					break;
				case 'fl': //florida
					//Unemployment Insurance - Employee
					break;
				case 'ga': //georgia
					//Unemployment Insurance - Employee
					//Administrative Assessment
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Administrative Assessment',
														'ps_order' => 310,
													)
												);
					break;
				case 'hi': //hawaii
					//Unemployment Insurance - Employee
					//E&T Assessment
					//Health Insurance
					//Disability Insurance
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - E&T Assessment',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Health Insurance',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance',
														'ps_order' => 210,
													)
												);
					break;
				case 'id': //idaho
					//Unemployment Insurance - Employee
					//Administrative Reserve
					//Workforce Development
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Administrative Reserve',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Workforce Development',
														'ps_order' => 310,
													)
												);
					break;
				case 'il': //illinois
					//Unemployment Insurance - Employee
					break;
				case 'in': //indiana
					//Unemployment Insurance - Employee
					//County Tax
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - County Income Tax',
														'ps_order' => 210,
													)
												);
					break;
				case 'ia': //iowa
					//Unemployment Insurance - Employee
					//Reserve Fund
					//Surcharge
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Reserve Fund',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Surcharge',
														'ps_order' => 311,
													)
												);
					break;
				case 'ks': //kansas
					//Unemployment Insurance - Employee
					break;
				case 'ky': //kentucky
					//Unemployment Insurance - Employee
					break;
				case 'la': //louisiana
					//Unemployment Insurance - Employee
					break;
				case 'me': //maine
					//Unemployment Insurance - Employee
					//Competitive Skills
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Competitive Skills',
														'ps_order' => 310,
													)
												);
					break;
				case 'md': //maryland
					//Unemployment Insurance - Employee
					break;
				case 'ma': //massachusetts
					//Unemployment Insurance - Employee
					//Health Insurance
					//Workforce Training Fund
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Health Insurance',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Workforce Training Fund',
														'ps_order' => 311,
													)
												);
					break;
				case 'mi': //michigan
					//Unemployment Insurance - Employee
					break;
				case 'mn': //minnesota
					//Unemployment Insurance - Employee
					//Workforce Enhancement Fee
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Workforce Enhancement Fee',
														'ps_order' => 310,
													)
												);
					break;
				case 'ms': //mississippi
					//Unemployment Insurance - Employee
					//Training Contribution
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Training Contribution',
														'ps_order' => 310,
													)
												);
					break;
				case 'mo': //missouri
					//Unemployment Insurance - Employee
					break;
				case 'mt': //montana
					//Unemployment Insurance - Employee
					//Administrative Fund
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Administrative Fund',
														'ps_order' => 310,
													)
												);
					break;
				case 'ne': //nebraska
					//Unemployment Insurance - Employee
					//SUIT
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - SUIT',
														'ps_order' => 310,
													)
												);
					break;
				case 'nv': //nevada
					//Unemployment Insurance - Employee
					//Career Enhancement
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Career Enhancement',
														'ps_order' => 310,
													)
												);
					break;
				case 'nh': //new hampshire
					//Unemployment Insurance - Employee
					//Administrative Contribution
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Administrative Contribution',
														'ps_order' => 310,
													)
												);
					break;
				case 'nm': //new mexico
					//Unemployment Insurance - Employee
					//State Trust Fund
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - State Trust Fund',
														'ps_order' => 310,
													)
												);
					break;
				case 'nj': //new jersey
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					//Disability Insurance - Employee
					//Disability Insurance - Employer
					//Workforce Development - Employee
					//Workforce Development - Employer
					//Healthcare Subsidy - Employee
					//Healthcare Subsidy - Employer
					//Family Leave Insurace
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance',
														'ps_order' => 210,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Disability Insurance',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Workforce Development',
														'ps_order' => 211,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Workforce Development',
														'ps_order' => 311,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Healthcare Subsidy',
														'ps_order' => 212,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Healthcare Subsidy',
														'ps_order' => 312,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Family Leave Insurance',
														'ps_order' => 213,
													)
												);
					break;
				case 'ny': //new york
					//Unemployment Insurance - Employee
					//Reemployment Service Fund
					//Disability Insurance - Employee
					//Disability Insurance - Male
					//Disability Insurance - Female
					//Metropolitan Commuter Tax
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Reemployment Service Fund',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance',
														'ps_order' => 210,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance - Male',
														'ps_order' => 211,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Disability Insurance - Female',
														'ps_order' => 212,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Metropolitan Commuter Tax',
														'ps_order' => 213,
													)
												);
					break;
				case 'nc': //north carolina
					//Unemployment Insurance - Employee
					break;
				case 'nd': //north dakota
					//Unemployment Insurance - Employee
					break;
				case 'oh': //ohio
					//Unemployment Insurance - Employee
					break;
				case 'ok': //oklahoma
					//Unemployment Insurance - Employee
					break;
				case 'or': //oregon
					//Unemployment Insurance - Employee
					//Workers Benefit - Employee
					//Workers Benefit - Employer
					//Tri-Met Transit District
					//Lane Transit District
					//Special Payroll Tax offset
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Workers Benefit',
														'ps_order' => 210,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Workers Benefit',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Tri-Met Transit District',
														'ps_order' => 311,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Lane Transit District',
														'ps_order' => 312,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Special Payroll Tax Offset',
														'ps_order' => 313,
													)
												);

					break;
				case 'pa': //pennsylvania
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					break;
				case 'ri': //rhode island
					//Employment Security
					//Job Development Fund
					//Temporary Disability Insurance
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employment Security',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Job Development Fund',
														'ps_order' => 311,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Temporary Disability Ins.',
														'ps_order' => 212,
													)
												);
					break;
				case 'sc': //south carolina
					//Unemployment Insurance - Employee
					//Contingency Assessment
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Contingency Assessment',
														'ps_order' => 310,
													)
												);
					break;
				case 'sd': //south dakota
					//Unemployment Insurance - Employee
					//Investment Fee
					//UI Surcharge
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Investment Fee',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - UI Surcharge',
														'ps_order' => 310,
													)
												);
					break;
				case 'tn': //tennessee
					//Unemployment Insurance - Employee
					//Job Skills Fee
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Job Skills Fee',
														'ps_order' => 310,
													)
												);
					break;
				case 'tx': //texas
					//Unemployment Insurance - Employee
					//Employment & Training
					//UI Obligation Assessment
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employment & Training',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - UI Obligation Assessment',
														'ps_order' => 311,
													)
												);
					break;
				case 'ut': //utah
					//Unemployment Insurance - Employee
					break;
				case 'vt': //vermont
					//Unemployment Insurance - Employee
					break;
				case 'va': //virginia
					//Unemployment Insurance - Employee
					break;
				case 'wa': //washington
					//Unemployment Insurance - Employee
					//Industrial Insurance - Employee
					//Industrial Insurance - Employer
					//Employment Admin Fund
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - Industrial Insurance',
														'ps_order' => 210,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Industrial Insurance',
														'ps_order' => 310,
													)
												);
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employment Admin Fund',
														'ps_order' => 311,
													)
												);
					break;
				case 'wv': //west virginia
					//Unemployment Insurance - Employee
					break;
				case 'wi': //wisconsin
					//Unemployment Insurance - Employee
					break;
				case 'wy': //wyomin
					//Unemployment Insurance - Employee
					//Employment Support Fund
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 30,
														'name' => strtoupper($province) .' - Employment Support Fund',
														'ps_order' => 310,
													)
												);
					break;
			}
		}

		//Default accounts, only created if country and province are not defined.
		if ( $country == '' AND $province == '' AND $district == '' ) {
			$regular_time_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Regular Time',
												'ps_order' => 100,
											)
										);
			if ( $regular_time_psea_id > 0 ) {
				$psealf->setRegularTime( $regular_time_psea_id );
			}

			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Over Time 1',
												'ps_order' => 120,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Over Time 2',
												'ps_order' => 121,
											)
										);


			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Premium 1',
												'ps_order' => 130,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Premium 2',
												'ps_order' => 131,
											)
										);

			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Statutory Holiday',
												'ps_order' => 140,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Sick',
												'ps_order' => 142,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Bereavement',
												'ps_order' => 145,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Jury Duty',
												'ps_order' => 146,
											)
										);

			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Tips',
												'ps_order' => 150,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Commission',
												'ps_order' => 152,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Expense Reimbursement',
												'ps_order' => 154,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Bonus',
												'ps_order' => 156,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Severance',
												'ps_order' => 160,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 10,
												'name' => 'Advance',
												'ps_order' => 170,
											)
										);


			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Health Benefits Plan',
												'ps_order' => 250,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Dental Benefits Plan',
												'ps_order' => 255,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Life Insurance',
												'ps_order' => 256,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Long Term Disability',
												'ps_order' => 257,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Accidental Death & Dismemberment',
												'ps_order' => 258,
											)
										);

			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Advance Paid',
												'ps_order' => 280,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Union Dues',
												'ps_order' => 282,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 20,
												'name' => 'Garnishment',
												'ps_order' => 289,
											)
										);


			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 30,
												'name' => 'Health Benefits Plan',
												'ps_order' => 340,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 30,
												'name' => 'Dental Benefits Plan',
												'ps_order' => 341,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 30,
												'name' => 'Life Insurance',
												'ps_order' => 346,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 30,
												'name' => 'Long Term Disability',
												'ps_order' => 347,
											)
										);
			$this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 30,
												'name' => 'Accidental Death & Dismemberment',
												'ps_order' => 348,
											)
										);


			//Loan
			$loan_accrual_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 50,
												'name' => 'Loan Balance',
												'ps_order' => 497,
											)
										);
			if ( $loan_accrual_psea_id > 0 ) {
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 10,
													'name' => 'Loan',
													'ps_order' => 197,
													'accrual_pay_stub_entry_account_id' => $loan_accrual_psea_id,
												)
											);
				$this->createPayStubAccount(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10,
													'type_id' => 20,
													'name' => 'Loan Repayment',
													'ps_order' => 297,
													'accrual_pay_stub_entry_account_id' => $loan_accrual_psea_id,
												)
											);
			}

			//Totals
			$total_gross_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 40,
												'name' => 'Total Gross',
												'ps_order' => 199,
												'debit_account' => ( DEMO_MODE == TRUE ) ? '5400' : '',
												'credit_account' => '',
											)
										);
			if ( $total_gross_psea_id > 0 ) {
				$psealf->setTotalGross( $total_gross_psea_id );
			}

			$total_deductions_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 40,
												'name' => 'Total Deductions',
												'ps_order' => 298,
												'debit_account' => '',
												'credit_account' => ( DEMO_MODE == TRUE ) ? '2100' : '',												
											)
										);
			if ( $total_deductions_psea_id > 0 ) {
				$psealf->setTotalEmployeeDeduction( $total_deductions_psea_id );
			}


			$net_pay_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 40,
												'name' => 'Net Pay',
												'ps_order' => 299,
												'debit_account' => '',
												'credit_account' => ( DEMO_MODE == TRUE ) ? '1060' : '',																								
											)
										);
			if ( $net_pay_psea_id > 0 ) {
				$psealf->setTotalNetPay( $net_pay_psea_id );
			}


			$employer_deductions_psea_id = $this->createPayStubAccount(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10,
												'type_id' => 40,
												'name' => 'Employer Total Contributions',
												'ps_order' => 399,
												'debit_account' => ( DEMO_MODE == TRUE ) ? '5450' : '',
												'credit_account' => ( DEMO_MODE == TRUE ) ? '1060' : '',
											)
										);
			if ( $employer_deductions_psea_id > 0 ) {
				$psealf->setTotalEmployerDeduction( $employer_deductions_psea_id );
			}
		}

		if ( $psealf->isValid() == TRUE ) {
			Debug::text('Saving.... PSA Linking', __FILE__, __LINE__, __METHOD__, 10);
			$psealf->Save();
		} else {
			Debug::text('Saving.... PSA Linking FAILED!', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}


	function createCompanyDeduction( $data ) {
		if ( is_array($data) ) {

			$cdf = TTnew( 'CompanyDeductionFactory' );

			$data['id'] = $cdf->getNextInsertId();
			//Debug::Arr($data, 'zzzCompany Deduction Data: ', __FILE__, __LINE__, __METHOD__, 10);

			$cdf->setObjectFromArray( $data );
			if ( $cdf->isValid() ) {
				return $cdf->Save( TRUE, TRUE );
			}
		}

		return FALSE;
	}

	function getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $type_id, $name ) {
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$psealf->getByCompanyIdAndTypeAndFuzzyName( $this->getCompany(), $type_id, $name );
		if ( $psealf->getRecordCount() > 0 ) {
			return $psealf->getCurrent()->getId();
		}

		return FALSE;
	}

	function CompanyDeductions( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		//
		//Additional Information: http://www.payroll-taxes.com/state-tax.htm
		//
		//Get PayStub Link accounts
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
		$pseallf->getByCompanyId( $this->getCompany() );
		if	( $pseallf->getRecordCount() > 0 ) {
			$psea_obj = $pseallf->getCurrent();
		} else {
			Debug::text('Company ID: '. $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');
		$pd_obj = new PayrollDeduction( $country, $province );
		$pd_obj->setDate( time() );

		$cdf = TTnew( 'CompanyDeductionFactory' );
		$cdf->StartTransaction();

		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				case 'ca':
					$pd_obj = new PayrollDeduction( $country, 'BC' ); //Pick default province for now.
					$pd_obj->setDate( time() );

					//Federal Income Tax
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'CA - Federal Income Tax',
														'calculation_id' => 100,
														'calculation_order' => 100,
														'country' => strtoupper($country),
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
														'user_value1' => $pd_obj->getBasicFederalClaimCodeAmount(),
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'RRSP' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Union Dues' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'CA - Addl. Income Tax',
														'calculation_id' => 20,
														'calculation_order' => 105,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CA - Federal Income Tax' ),
														'user_value1' => 0,
													)
												);

					//CPP
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'CPP - Employee',
														'calculation_id' => 90, //CPP Formula
														'calculation_order' => 80,
														'minimum_user_age' => 18,
														'maximum_user_age' => 70,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ),
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'CPP - Employer',
														'calculation_id' => 10,
														'calculation_order' => 85,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'CPP - Employer' ),
														'include_pay_stub_entry_account' => array( $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'CPP' ), ),
														'user_value1' => 100,
													)
												);

					//EI
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'EI - Employee',
														'calculation_id' => 91, //EI Formula
														'calculation_order' => 90,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ),
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'EI - Employer',
														'calculation_id' => 10,
														'calculation_order' => 95,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'EI - Employer' ),
														'include_pay_stub_entry_account' => array( $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'EI' ), ),
														'user_value1' => 140,
													)
												);

					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Workers Compensation - Employer',
														'calculation_id' => 15,
														'calculation_order' => 96,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Workers Compensation - Employer' ),
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								),
														'user_value1' => 0.00,
														'user_value2' => 0, //Annual Wage Base
														'user_value3' => 0,
													)
												);

					break;
				case 'us':
					//Federal Income Tax
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'US - Federal Income Tax',
														'calculation_id' => 100,
														'calculation_order' => 100,
														'country' => strtoupper($country),
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
														'user_value1' => 0, //Allowances
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'US - Addl. Income Tax',
														'calculation_id' => 20,
														'calculation_order' => 105,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'US - Federal Income Tax' ),
														'user_value1' => 0,
													)
												);

					//Federal Unemployment Insurance.
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'US - Federal Unemployment Insurance',
														'calculation_id' => 15,
														'calculation_order' => 80,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'US - Federal Unemployment Insurance' ),
														'user_value1' => $pd_obj->getFederalUIMinimumRate(),
														'user_value2' => $pd_obj->getFederalUIMaximumEarnings(),
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Social Security
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Social Security - Employee',
														'calculation_id' => 84,
														'calculation_order' => 80,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Social Security (FICA)' ),
														//'user_value1' => $pd_obj->getSocialSecurityRate(), //2013
														//'user_value2' => $pd_obj->getSocialSecurityMaximumEarnings(),
														//'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Social Security - Employer',
														'calculation_id' => 85,
														'calculation_order' => 81,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Social Security (FICA)' ),
														//'user_value1' => $pd_obj->getSocialSecurityRate(),
														//'user_value2' => $pd_obj->getSocialSecurityMaximumEarnings(),
														//'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Medicare
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Medicare - Employee',
														'calculation_id' => 82,
														'calculation_order' => 90,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Medicare' ),
														//'user_value1' => $pd_obj->getMedicareRate(),
														'user_value1' => 10, //Single
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Medicare - Employer',
														'calculation_id' => 83,
														'calculation_order' => 91,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Medicare' ),
														//'user_value1' => $pd_obj->getMedicareRate(),
														//'user_value1' => 10, //Single
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => 'Workers Compensation - Employer',
														'calculation_id' => 15,
														'calculation_order' => 96,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, 'Workers Compensation - Employer' ),
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								),
														'user_value1' => 0.00,
														'user_value2' => 0, //Annual Wage Base
														'user_value3' => 0,
													)
												);
					break;
			}

			unset($pd_obj);
		}

		//Canada
		if ( $country == 'ca' AND $province != '' ) {
			$vacation_data = array(
									'primary_percent' => 0,
									'secondary_percent' => 0,
									'secondary_length_of_service' => 0
									);

			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			switch ($province) {
				//CA
				case 'bc':
				case 'ab':
				case 'mb':
				case 'qc':
				case 'nu':
				case 'nt':
					$vacation_data = array(
											'primary_percent' => 4,
											'secondary_percent' => 6,
											'secondary_length_of_service' => 6, //After 5th year
											);
					break;
				case 'nb':
				case 'ns':
				case 'pe':
					$vacation_data = array(
											'primary_percent' => 4,
											'secondary_percent' => 6,
											'secondary_length_of_service' => 9, //After 8th year
											);
					break;
				case 'on':
				case 'yt':
					$vacation_data = array(
											'primary_percent' => 4,
											'secondary_percent' => 0,
											'secondary_length_of_service' => 0,
											);
					break;
				case 'sk':
					$vacation_data = array(
											'primary_percent' => 4,
											'secondary_percent' => 8,
											'secondary_length_of_service' => 11, //After 10th year
											);
					break;
				case 'nl':
					$vacation_data = array(
											'primary_percent' => 4,
											'secondary_percent' => 6,
											'secondary_length_of_service' => 16, //After 15th year
											);
					break;
			}

			if ( !in_array( $province, array('on', 'yt') ) ) {
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 20, //Deduction
													'name' => strtoupper($province) .' - Vacation Accrual - 0-'. ($vacation_data['secondary_length_of_service'] - 1) .' Years',
													'calculation_id' => 10,
													'calculation_order' => 50,
													'minimum_length_of_service_unit_id' => 40, //Years
													'minimum_length_of_service' => 0,
													'maximum_length_of_service_unit_id' => 40, //Years
													'maximum_length_of_service' => ($vacation_data['secondary_length_of_service'] - 0.001),
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Vacation Accrual' ),
													'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
													'exclude_pay_stub_entry_account' => array(
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
																							),
													'user_value1' => $vacation_data['primary_percent'],
												)
											);
			}
			$this->createCompanyDeduction(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10, //Enabled
												'type_id' => 20, //Deduction
												'name' => strtoupper($province) .' - Vacation Accrual - '.($vacation_data['secondary_length_of_service'] - 0).'+ Years',
												'calculation_id' => 10,
												'calculation_order' => 51,
												'minimum_length_of_service_unit_id' => 40, //Years
												'minimum_length_of_service' => $vacation_data['secondary_length_of_service'],
												'maximum_length_of_service_unit_id' => 40, //Years
												'maximum_length_of_service' => 0,
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Vacation Accrual' ),
												'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
												'exclude_pay_stub_entry_account' => array(
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
																						),
												'user_value1' => $vacation_data['secondary_percent'],
											)
										);
			if ( !in_array( $province, array('on', 'yt') ) ) {
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 20, //Deduction
													'name' => strtoupper($province) .' - Vacation No Accrual - 0-'. ($vacation_data['secondary_length_of_service'] - 1) .' Years',
													'calculation_id' => 10,
													'calculation_order' => 50,
													'minimum_length_of_service_unit_id' => 40, //Years
													'minimum_length_of_service' => 0,
													'maximum_length_of_service_unit_id' => 40, //Years
													'maximum_length_of_service' => ($vacation_data['secondary_length_of_service'] - 0.001),
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
													'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
													'exclude_pay_stub_entry_account' => array(
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
																							),
													'user_value1' => $vacation_data['primary_percent'],
												)
											);
			}
			$this->createCompanyDeduction(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10, //Enabled
												'type_id' => 20, //Deduction
												'name' => strtoupper($province) .' - Vacation No Accrual - '.($vacation_data['secondary_length_of_service'] - 0).'+ Years',
												'calculation_id' => 10,
												'calculation_order' => 51,
												'minimum_length_of_service_unit_id' => 40, //Years
												'minimum_length_of_service' => $vacation_data['secondary_length_of_service'],
												'maximum_length_of_service_unit_id' => 40, //Years
												'maximum_length_of_service' => 0,
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
												'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
												'exclude_pay_stub_entry_account' => array(
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - Accrual Release' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation - No Accrual' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Severance' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Tips' ),
																						),
												'user_value1' => $vacation_data['secondary_percent'],
											)
										);

			$this->createCompanyDeduction(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10, //Enabled
												'type_id' => 10, //Tax
												'name' => strtoupper($province) .' - Provincial Income Tax',
												'calculation_id' => 200,
												'calculation_order' => 101,
												'country' => strtoupper($country),
												'province' => strtoupper($province),
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province) .' - Provincial Income Tax' ),
												'user_value1' => $pd_obj->getBasicProvinceClaimCodeAmount(),
												'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
												'exclude_pay_stub_entry_account' => array(
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'RRSP' ),
																							$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Union Dues' ),
																						),
											)
										);
		}

		if ( $country == 'us' AND $province != '' ) {
			if ( in_array( $province, array('al', 'az', 'ar', 'ca', 'co', 'ct', 'de', 'dc', 'ga', 'hi', 'id', 'il',
											'in', 'ia', 'ks', 'ky', 'la', 'me', 'md', 'ma', 'mi', 'mn', 'ms', 'mo',
											'mt', 'ne', 'nj', 'nm', 'ny', 'nc', 'nd', 'oh', 'ok', 'or', 'pa', 'ri',
											'sc', 'ut', 'vt', 'va', 'wi', 'wv') ) ) {
				//State Income Tax
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 10, //Tax
													'name' => strtoupper($province) .' - State Income Tax',
													'calculation_id' => 200,
													'calculation_order' => 200,
													'country' => strtoupper($country),
													'province' => strtoupper($province),
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - State Income Tax' ),
													'user_value1' => 10, //Single
													'user_value2' => 0, //0 Allowances
													'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
													'exclude_pay_stub_entry_account' => array(
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																							),
												)
											);
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 10, //Tax
													'name' => strtoupper($province) .' - State Addl. Income Tax',
													'calculation_id' => 20,
													'calculation_order' => 205,
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province) .' - State Income Tax' ),
													'user_value1' => 0,
												)
											);
			}

			//Default to unemployment rates to 0.
			$company_state_unemployment_rate = 0;
			$company_state_unemployment_wage_base = 0;
			$state_unemployment_rate = 0;
			$state_unemployment_wage_base = 0;

			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			switch ($province) {
				//US
				case 'al': //alabama
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 8000;

					//Employment Security Asmt
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employment Security Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employment Security Assessment' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ak': //alaska
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					$company_state_unemployment_wage_base =	$state_unemployment_wage_base = 37400;
					break;
				case 'az': //arizona
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 7000;

					//Surcharge
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Job Training Surcharge',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Job Training Surcharge' ),
														'user_value1' => 0.10, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ar': //arkansas
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 12000;

					break;
				case 'ca': //california
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 7000;

					//Disability Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance' ),
														'user_value1' => 0.9, //Percent
														'user_value2' => 104378, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Employee Training Tax
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employee Training Tax',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employee Training Tax' ),
														'user_value1' => 0.10, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'co': //colorado
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 11700;

					break;
				case 'ct': //connecticut
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 15000;
					break;
				case 'de': //delaware
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 18500;
					break;
				case 'dc': //d.c.
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9000;

					//Administrative Assessment
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Administrative Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Administrative Assessment' ),
														'user_value1' => 0.20, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'fl': //florida
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 8000;

					break;
				case 'ga': //georgia
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9500;

					//Administrative Assessment
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Administrative Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Administrative Assessment' ),
														'user_value1' => 0.08, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'hi': //hawaii
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 40400;

					//E&T Assessment
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - E&T Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - E&T Assessment' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Health Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Health Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Health Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Disability Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 48882.60, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					break;
				case 'id': //idaho
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 35200;

					//Administrative Reserve
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Administrative Reserve',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Administrative Reserve' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Workforce Development
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workforce Development',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Workforce Development' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'il': //illinois
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 12960;
					break;
				case 'in': //indiana
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9500;

					//County Tax
					/*
					$this->createPayStubAccount(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10,
														'type_id' => 20,
														'name' => strtoupper($province) .' - County Income Tax',
														'ps_order' => 210,
													)
												);
					*/
					break;
				case 'ia': //iowa
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 26800;

					//Reserve Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Reserve Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Reserve Fund' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Surcharge
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Surcharge',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Surcharge' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ks': //kansas
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 8000;
					break;
				case 'ky': //kentucky
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9600;
					break;
				case 'la': //louisiana
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 7700;
					break;
				case 'me': //maine
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 12000;

					//Competitive Skills
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Competitive Skills',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Competitive Skills' ),
														'user_value1' => 0.06, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'md': //maryland
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 8500;
					break;
				case 'ma': //massachusetts
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 14000;

					//Health Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Health Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Health Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Workforce Training Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workforce Training Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Workforce Training Fund' ),
														'user_value1' => 0.06, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'mi': //michigan
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9500;
					break;
				case 'mn': //minnesota
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 29000;

					//Workforce Enhancement Fee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workforce Enhancement Fee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Workforce Enhancement Fee' ),
														'user_value1' => 0.10, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ms': //mississippi
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 14000;

					//Training Contribution
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Training Contribution',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Training Contribution' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'mo': //missouri
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 13000;
					break;
				case 'mt': //montana
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 29000;

					//Administrative Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Administrative Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Administrative Fund' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ne': //nebraska
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9000;

					//SUIT
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - SUIT',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - SUIT' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'nv': //nevada
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 27400;

					//Career Enhancement
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Career Enhancement',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Career Enhancement' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'nh': //new hampshire
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 14000;

					//Administrative Contribution
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Administrative Contribution',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Administrative Contribution' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'nj': //new jersey
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					$state_unemployment_wage_base = 31500;

					//Disability Insurance - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance - Employee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance' ),
														'user_value1' => 0.38, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Disability Insurance - Employer
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance - Employer',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Disability Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Workforce Development - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workforce Development - Employee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Workforce Development' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Workforce Development - Employer
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workforce Development - Employer',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Workforce Development' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Healthcare Subsidy - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Healthcare Subsidy - Employee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Healthcare Subsidy' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Healthcare Subsidy - Employer
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Healthcare Subsidy - Employer',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Healthcare Subsidy' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Family Leave Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Family Leave Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Family Leave Insurance' ),
														'user_value1' => 0.08, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					break;
				case 'nm': //new mexico
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 23400;

					//State Trust Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - State Trust Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - State Trust Fund' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ny': //new york
					//Unemployment Insurance - Employee
					$company_state_unemployment_wage_base = $state_unemployment_wage_base = 10300;

					//Reemployment Service Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Reemployment Service Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Reemployment Service Fund' ),
														'user_value1' => 0.075, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Disability Insurance - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance' ),
														'user_value1' => 0.50, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Disability Insurance - Male
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance - Male',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance - Male' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 6000, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Disability Insurance - Female
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Disability Insurance - Female',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Disability Insurance - Female' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 6000, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Metropolitan Commuter Tax
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Metropolitan Commuter Tax',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Metropolitan Commuter Tax' ),
														'user_value1' => 0.34, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'nc': //north carolina
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 21400;
					break;
				case 'nd': //north dakota
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 33600;
					break;
				case 'oh': //ohio
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9000;
					break;
				case 'ok': //oklahoma
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 18700;
					break;
				case 'or': //oregon
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 35000;

					//Workers Benefit - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workers Benefit - Employee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Workers Benefit' ),
														'user_value1' => 0.016, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Workers Benefit - Employer
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Workers Benefit - Employer',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Workers Benefit' ),
														'user_value1' => 0.017, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Tri-Met Transit District
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Tri-Met Transit District',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Tri-Met Transit District' ),
														'user_value1' => 0.7237, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Lane Transit District
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Lane Transit District',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Lane Transit District' ),
														'user_value1' => 0.70, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Special Payroll Tax offset
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Special Payroll Tax Offset',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Special Payroll Tax Offset' ),
														'user_value1' => 0.09, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'pa': //pennsylvania
					//Unemployment Insurance - Employee
					//Unemployment Insurance - Employer
					$state_unemployment_wage_base = 0;
					$company_state_unemployment_wage_base = 8750;
					break;
				case 'ri': //rhode island
					//Employment Security
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employment Security',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employment Security' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 20600, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Job Development Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Job Development Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Job Development Fund' ),
														'user_value1' => 0.51, //Percent
														'user_value2' => 20600, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Temporary Disability Insurance
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Temporary Disability Insurance',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Temporary Disability Ins.' ),
														'user_value1' => 1.20, //Percent
														'user_value2' => 62700, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'sc': //south carolina
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 12000;

					//Contingency Assessment
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Contingency Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Contingency Assessment' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'sd': //south dakota
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 14000;

					//Investment Fee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Investment Fee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Investment Fee' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//UI Surcharge
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - UI Surcharge',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - UI Surcharge' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'tn': //tennessee
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9000;

					//Job Skills Fee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Job Skills Fee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Job Skills Fee' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'tx': //texas
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 9000;

					//Employment & Training
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employment & Training',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employment & Training' ),
														'user_value1' => 0.10, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//UI Obligation Assessment
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - UI Obligation Assessment',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - UI Obligation Assessment' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'ut': //utah
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 30800;
					break;
				case 'vt': //vermont
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 16000;
					break;
				case 'va': //virginia
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 8000;
					break;
				case 'wa': //washington
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 41300;

					//Industrial Insurance - Employee
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Industrial Insurance - Employee',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Industrial Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Industrial Insurance - Employer
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Industrial Insurance - Employer',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Industrial Insurance' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => 0, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);

					//Employment Admin Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employment Admin Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employment Admin Fund' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
				case 'wv': //west virginia
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 12000;
					break;
				case 'wi': //wisconsin
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 14000;
					break;
				case 'wy': //wyoming
					//Unemployment Insurance - Employee
					$state_unemployment_wage_base = 24500;

					//Employment Support Fund
					$this->createCompanyDeduction(
													array(
														'company_id' => $this->getCompany(),
														'status_id' => 10, //Enabled
														'type_id' => 10, //Tax
														'name' => strtoupper($province) .' - Employment Support Fund',
														'calculation_id' => 15,
														'calculation_order' => 186,
														'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Employment Support Fund' ),
														'user_value1' => 0.00, //Percent
														'user_value2' => $state_unemployment_wage_base, //WageBase
														'user_value3' => 0,
														'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
														'exclude_pay_stub_entry_account' => array(
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																									$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																								),
													)
												);
					break;
			}

			//Unemployment insurance must go below the above state settings so it has the proper rate/wage_base for
			//State Unemployement Insurace, deducted from employer
			if ( in_array( $province, array('ak', 'al', 'ar', 'az', 'ca', 'co', 'ct', 'dc', 'de', 'fl', 'ga', 'hi',
											'ia', 'id', 'il', 'in', 'ks', 'ky', 'la', 'ma', 'md', 'me', 'mi', 'mn',
											'mo', 'ms', 'mt', 'nc', 'nd', 'ne', 'nh', 'nj', 'nm', 'nv', 'ny', 'oh',
											'ok', 'or', 'pa', 'sc', 'sd', 'tn', 'tx', 'ut', 'va', 'vt', 'wa', 'wi',
											'wv', 'wy') ) ) {
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 10, //Tax
													'name' => strtoupper($province) .' - Unemployment Insurance - Employer',
													'calculation_id' => 15,
													'calculation_order' => 185,
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 30, strtoupper($province).' - Unemployment Insurance' ),
													'user_value1' => $state_unemployment_rate, //Percent
													'user_value2' => $state_unemployment_wage_base, //WageBase
													'user_value3' => 0,
													'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
													'exclude_pay_stub_entry_account' => array(
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																							),
												)
											);
			}
			//State Unemployement Insurace, deducted from employee
			if ( in_array( $province, array('ak', 'nj', 'pa') ) ) {
				$this->createCompanyDeduction(
												array(
													'company_id' => $this->getCompany(),
													'status_id' => 10, //Enabled
													'type_id' => 10, //Tax
													'name' => strtoupper($province) .' - Unemployment Insurance - Employee',
													'calculation_id' => 15,
													'calculation_order' => 186,
													'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, strtoupper($province).' - Unemployment Insurance' ),
													'user_value1' => $company_state_unemployment_rate, //Percent
													'user_value2' => $company_state_unemployment_wage_base, //WageBase
													'user_value3' => 0,
													'include_pay_stub_entry_account' => array( $psea_obj->getTotalGross() ),
													'exclude_pay_stub_entry_account' => array(
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Loan' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Expense Reimbursement' ),
																								$this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, '401(k)' ),
																							),
												)
											);
			}
		}

		//Default accounts, only created if country and province are not defined.
		if ( $country == '' AND $province == '' AND $district == '' ) {
			$this->createCompanyDeduction(
											array(
												'company_id' => $this->getCompany(),
												'status_id' => 10, //Enabled
												'type_id' => 20, //Deduction
												'name' => 'Loan Repayment',
												'calculation_id' => 52,
												'calculation_order' => 200, //Fixed Amount w/Target
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 20, 'Loan Repayment' ),
												'user_value1' => 25, //Fixed amount to repay each pay period.
												'user_value2' => 0,
												'include_account_amount_type_id' => 30, //YTD Amount
												'include_pay_stub_entry_account' => array( $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 50, 'Loan Balance' ) ),
											)
										);
		}

		$cdf->CommitTransaction();
		return TRUE;
	}


	function getRecurringHolidayByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$rhlf = TTnew( 'RecurringHolidayListFactory' );
		$rhlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $rhlf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $rhlf as $rh_obj ) {
				$retarr[] = $rh_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}
	function createRecurringHoliday( $data ) {
		if ( is_array($data) ) {

			$rhf = TTnew( 'RecurringHolidayFactory' );
			$rhf->setObjectFromArray( $data );
			if ( $rhf->isValid() ) {
				return $rhf->Save();
			}
		}

		return FALSE;
	}
	function RecurringHolidays( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {

		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			//
			//http://www.statutoryholidays.com/
			//
			switch ($country) {
				case 'ca':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - New Years Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Good Friday',
														'type_id' => 20,
														'special_day' => 1, //Easter
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														//'day_of_month' => 1,
														//'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Canada Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 7,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Labour Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 9,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Christmas Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 25,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);

					//Optional holidays or ones observed by many provinces.
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Christmas Eve',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 24,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Boxing Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 26,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Thanksgiving Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														'week_interval' => 2,
														'day_of_week' => 1,
														//'day_of_month' => 24,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Victoria Day',
														'type_id' => 30,
														'special_day' => 0,
														'pivot_day_direction_id' => 30,
														//'week_interval' => 0,
														'day_of_week' => 1,
														'day_of_month' => 24,
														'month_int' => 5,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Remembrance Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 11,
														'month_int' => 11,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'us':
					//Offical federal holidays
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - New Years Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Memorial Day',
														'type_id' => 30,
														'special_day' => 0,
														'pivot_day_direction_id' => 20,
														//'week_interval' => 3,
														'day_of_week' => 1,
														'day_of_month' => 24,
														'month_int' => 5,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Independence Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 4,
														'month_int' => 7,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Labour Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 9,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Veterans Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 11,
														'month_int' => 11,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Thanksgiving Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														'week_interval' => 4,
														'day_of_week' => 4,
														//'day_of_month' => 24,
														'month_int' => 11,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Christmas Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 25,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);

					//Rhode Island doesn't observe this, but all other states do.
					//Optional days
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Martin Luther King Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 11,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Presidents Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 11,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Christmas Eve',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 24,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Columbus Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 20,
														'week_interval' => 2,
														'day_of_week' => 1,
														//'day_of_month' => 24,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'cr':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('New Years Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Good Friday'),
														'type_id' => 20,
														'special_day' => 1, //Easter
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														//'day_of_month' => 1,
														//'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Christmas Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 25,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Juan Santamaria Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 11,
														'month_int' => 4,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Labour Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 1,
														'month_int' => 5,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Anexion de Guanacaste Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 25,
														'month_int' => 7,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Virgen de los Angeles Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 2,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Mothers Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 15,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Independence Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 15,
														'month_int' => 9,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Culture Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 12,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'gt':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('New Years Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Good Friday'),
														'type_id' => 20,
														'special_day' => 1, //Easter
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														//'day_of_month' => 1,
														//'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Labour Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 1,
														'month_int' => 5,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Army Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 30,
														'month_int' => 6,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Virgin Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 15,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Independence Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 15,
														'month_int' => 9,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('1944 Revolution Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 20,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('All Saint Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 1,
														'month_int' => 11,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Christmas Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 25,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Christmas Eve'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 24,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'hn':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('New Years Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 1,
														'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Good Friday'),
														'type_id' => 20,
														'special_day' => 1, //Easter
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														//'day_of_month' => 1,
														//'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Labour Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 1,
														'month_int' => 5,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Independence Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 15,
														'month_int' => 9,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Christmas Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 25,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Morazan Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 3,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Culture Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 12,
														'month_int' => 10,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - '. TTi18n::gettext('Armed Forces Day'),
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 30,
														//'week_interval' => 4,
														//'day_of_week' => 4,
														'day_of_month' => 21,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			switch ($province) {
				case 'bc':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - British Columbia Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Family Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 2,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);

					break;
				case 'ab':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Family Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);

					break;
				case 'mb':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Louis Riel Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'qc':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Easter Monday',
														'type_id' => 20,
														'special_day' => 6, //Easter Monday
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														//'day_of_month' => 1,
														//'month_int' => 1,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - St. Jean Baptiste Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 24,
														'month_int' => 6,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'nu':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Civic Holiday',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'nt':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Civic Holiday',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'nb':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - New Brunswick Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'ns':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Heritage Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'pe':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Islander Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Civic Holiday',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);

					break;
				case 'on':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Family Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);

					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Civic Holiday',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);

					break;
				case 'yt':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Discovery Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
				case 'sk':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Family Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 3,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Saskatchewan Day',
														'type_id' => 20,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														'week_interval' => 1,
														'day_of_week' => 1,
														//'day_of_month' => 1,
														'month_int' => 8,
														'always_week_day_id' => 3, //Closest
													)
												);

					break;
				case 'nl':
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - St. Patrick\'s Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 17,
														'month_int' => 3,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - St. George\'s Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 23,
														'month_int' => 4,
														'always_week_day_id' => 3, //Closest
													)
												);
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Discovery Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 24,
														'month_int' => 6,
														'always_week_day_id' => 3, //Closest
													)
												);
					break;
			}
		}

		//US
		if ( $country == 'us' AND $province != '' ) {
			if ( in_array( $province, array('ct', 'de', 'fl', 'hi', 'in', 'ky', 'la', 'nj', 'nc', 'nd', 'tn', 'tx') ) ) {
				$this->createRecurringHoliday(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Good Friday',
													'type_id' => 20,
													'special_day' => 1, //Easter
													//'pivot_day_direction_id' => 0,
													//'week_interval' => 0,
													//'day_of_week' => 0,
													//'day_of_month' => 1,
													//'month_int' => 1,
													'always_week_day_id' => 3, //Closest
												)
											);
			}
			if ( in_array( $province, array('de', 'fl', 'ga', 'in', 'ia', 'ky', 'me', 'md', 'mi', 'mn', 'ne', 'nv', 'nm', 'ok', 'pa', 'tx', 'wa', 'wv') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Day After Thanksgiving Day',
														'type_id' => 30,
														'special_day' => 0,
														'pivot_day_direction_id' => 40,
														//'week_interval' => 4,
														'day_of_week' => 5,
														'day_of_month' => 23,
														'month_int' => 11,
														'always_week_day_id' => 3, //Closest
													)
												);
			}
			if ( in_array( $province, array('al', 'nc', 'tx') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Day After Christmas',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 26,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
			}

			if ( in_array( $province, array('al', 'ky', 'mi', 'wv', 'wi') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - New Years Eve',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 31,
														'month_int' => 12,
														'always_week_day_id' => 3, //Closest
													)
												);
			}

			if ( in_array( $province, array('ct', 'il', 'ia', 'mo', 'mt', 'nj', 'ny', '', '', '', '', '', '', '', '', '', '') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Lincolns Birthday',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 12,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
			}
			if ( in_array( $province, array('ca', 'oh') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Rosa Parks Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 4,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
			}
			if ( in_array( $province, array('fl', 'wi') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Susan B. Anthony Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 15,
														'month_int' => 2,
														'always_week_day_id' => 3, //Closest
													)
												);
			}
			if ( in_array( $province, array('ca') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - César Chávez Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 31,
														'month_int' => 3,
														'always_week_day_id' => 3, //Closest
													)
												);
			}

			if ( in_array( $province, array('dc') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Emancipation Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 16,
														'month_int' => 4,
														'always_week_day_id' => 3, //Closest
													)
												);
			}

			if ( in_array( $province, array('me', 'ma') ) ) {
					$this->createRecurringHoliday(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($province) .' - Patriots Day',
														'type_id' => 10,
														'special_day' => 0,
														//'pivot_day_direction_id' => 0,
														//'week_interval' => 0,
														//'day_of_week' => 0,
														'day_of_month' => 16,
														'month_int' => 4,
														'always_week_day_id' => 3, //Closest
													)
												);
			}
		}
		return TRUE;
	}

	function getRegularTimePolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$rtplf = TTnew( 'RegularTimePolicyListFactory' );
		$rtplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $rtplf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $rtplf as $rtp_obj ) {
				$retarr[] = $rtp_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}
	function createRegularTimePolicy( $data ) {
		if ( is_array($data) ) {
			$rtpf = TTnew( 'RegularTimePolicyFactory' );
			$rtpf->setObjectFromArray( $data );
			if ( $rtpf->isValid() ) {
				return $rtpf->Save();
			}
		}

		return FALSE;
	}
	function RegularTimePolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			$this->createRegularTimePolicy(
											array(
												'company_id' => $this->getCompany(),
												'name' => 'Regular Time',
												'calculation_order' => 9999,
												'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time' ),
												'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Regular Time' ) ),
											)
										);
		}

		return TRUE;
	}

	function getOverTimePolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$otplf = TTnew( 'OverTimePolicyListFactory' );
		$otplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $otplf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $otplf as $otp_obj ) {
				$retarr[] = $otp_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}
	function createOverTimePolicy( $data ) {
		if ( is_array($data) ) {

			$otpf = TTnew( 'OverTimePolicyFactory' );
			$otpf->setObjectFromArray( $data );
			if ( $otpf->isValid() ) {
				return $otpf->Save();
			}
		}

		return FALSE;
	}
	function OverTimePolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				case 'ca':
				case 'us':
					//Need to prefix the country/province on the overtime policies so they get a break-down of BC OT vs AB OT for example.
					//This also makes it easier to create policy groups.
					$this->createOverTimePolicy(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Holiday',
														'type_id' => 180, //Holiday
														'trigger_time' => 0, //0hrs
														'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
														'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
													)
												);
					break;
				default:
					//Default policies for other countries.
					$this->createOverTimePolicy(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Daily >8hrs',
														'type_id' => 10, //Daily
														'trigger_time' => (8 * 3600), //8hrs
														'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
														'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
													)
												);
					$this->createOverTimePolicy(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Weekly >40hrs',
														'type_id' => 20, //Weekly
														'trigger_time' => ( 40 * 3600 ), //40hrs
														'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
														'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
													)
												);
					$this->createOverTimePolicy(
													array(
														'company_id' => $this->getCompany(),
														'name' => strtoupper($country) .' - Holiday',
														'type_id' => 180, //Holiday
														'trigger_time' => 0, //0hrs
														'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
														'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
													)
												);
					break;
			}
		}

		//Canada
		if ( $country == 'ca' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			if ( in_array( $province, array('bc', 'ab', 'sk', 'mb', 'yt', 'nt', 'nu') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Daily >8hrs',
													'type_id' => 10, //Daily
													'trigger_time' => ( 8 * 3600 ), //8hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}
			if ( in_array( $province, array('bc') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Daily >12hrs',
													'type_id' => 10, //Daily
													'trigger_time' => ( 12 * 3600 ), //8hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
												)
											);
			}

			if ( in_array( $province, array('bc', 'sk', 'mb', 'qc', 'nl', 'yt', 'nt', 'nu') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >40hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 40 * 3600 ), //40hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}
			if ( in_array( $province, array('ab', 'on', 'nb') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >44hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 44 * 3600 ), //44hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}
			if ( in_array( $province, array('ns') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >48hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 48 * 3600 ), //48hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}
		}

		//US
		if ( $country == 'us' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			if ( in_array( $province, array('ca', 'ak', 'nv' ) ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Daily >8hrs',
													'type_id' => 10, //Daily
													'trigger_time' => ( 8 * 3600 ), //8hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}

			if ( in_array( $province, array('ca', 'co' ) ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Daily >12hrs',
													'type_id' => 10, //Daily
													'trigger_time' => ( 12 * 3600 ), //12hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
												)
											);
			}

			if ( in_array( $province, array('ca', 'ky' ) ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - 7th Consecutive Day',
													'type_id' => 155, //Daily
													'trigger_time' => ( 0 * 3600 ), //0hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}

			if ( in_array( $province, array('ca') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - 7th Consecutive Day >8hrs',
													'type_id' => 155, //Daily
													'trigger_time' => ( 8 * 3600 ), //0hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ) ),
												)
											);
			}


			if ( in_array( $province, array('mn') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >48hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 48 * 3600 ), //40hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			} elseif ( in_array( $province, array('ks') ) ) {
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >46hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 46 * 3600 ), //40hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			} else {
				//Always have a weekly OT policy for reference at least, many companies probably implement it on their own anyways?
				$this->createOverTimePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => strtoupper($province) .' - Weekly >40hrs',
													'type_id' => 20, //Weekly
													'trigger_time' => ( 40 * 3600 ), //40hrs
													'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ) ),
												)
											);
			}
		}

		return TRUE;
	}

	function getExceptionPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$eplf = TTnew( 'ExceptionPolicyControlListFactory' );
		$eplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $eplf->getRecordCount() > 0 ) {
			return $eplf->getCurrent()->getID();
		}

		return FALSE;
	}
	function createExceptionPolicy( $data ) {
		if ( is_array($data) ) {
			$epcf = TTnew( 'ExceptionPolicyControlFactory' );
			$epcf->setObjectFromArray( $data );
			if ( $epcf->isValid() ) {
				$control_id = $epcf->Save();
				if ( $control_id > 0 ) {
					$epf = TTnew( 'ExceptionPolicyFactory' );

					$data = $epf->getExceptionTypeDefaultValues( NULL, $this->getCompanyObject()->getProductEdition() );
					if ( is_array($data) ) {
						foreach($data as $exception_policy_type_id => $exception_policy_data ) {
							$exception_policy_data['exception_policy_control_id'] = $control_id;
							unset($exception_policy_data['id']);

							$epf->setObjectFromArray( $exception_policy_data );
							if ( $epf->isValid() ) {
								$epf->Save();
							}
						}

					}
				}

				return TRUE;
			}
		}

		return FALSE;
	}
	function ExceptionPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				default:
					//Default policies for other countries.
					$this->createExceptionPolicy(
													array(
														'company_id' => $this->getCompany(),
														'name' => 'Default',
													)
												);
					break;
			}
		}

		return TRUE;
	}

	function getMealPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$mplf = TTnew( 'MealPolicyListFactory' );
		$mplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $mplf->getRecordCount() > 0 ) {
			return $mplf->getCurrent()->getId();
		}

		return FALSE;
	}
	function createMealPolicy( $data ) {
		if ( is_array($data) ) {
			$mpf = TTnew( 'MealPolicyFactory' );
			Debug::Arr($data, 'zzzCountry: ', __FILE__, __LINE__, __METHOD__, 10);
			$mpf->setObjectFromArray( $data );
			if ( $mpf->isValid() ) {
				return $mpf->Save();
			}
		}

		return FALSE;
	}
	function MealPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				default:
					//Default policies for other countries.
					$this->createMealPolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => '30min Lunch',
													'type_id' => 20, //Normal
													'trigger_time' => ( 5 * 3600 ), //5hrs
													'amount' => 1800, //30min
													'auto_detect_type_id' => 20, //Punch Type
													'minimum_punch_time' => ( 20 * 60 ), //20min
													'maximum_punch_time' => ( 40 * 60 ), //40min
													'include_lunch_punch_time' => FALSE,
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Lunch Time' ) ),
												)
											);
					$this->createMealPolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => '60min Lunch',
													'type_id' => 20, //Normal
													'trigger_time' => ( 7 * 3600 ), //7hrs
													'amount' => 3600, //60min
													'auto_detect_type_id' => 20, //Punch Type
													'minimum_punch_time' => ( 45 * 60 ), //20min
													'maximum_punch_time' => ( 75 * 60 ), //40min
													'include_lunch_punch_time' => FALSE,
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Lunch Time' ) ),
												)
											);

					break;
			}
		}

		return TRUE;
	}

	function getBreakPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$bplf = TTnew( 'BreakPolicyListFactory' );
		$bplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $bplf->getRecordCount() > 0 ) {
			return $bplf->getCurrent()->getId();
		}

		return FALSE;
	}
	function createBreakPolicy( $data ) {
		if ( is_array($data) ) {
			$bpf = TTnew( 'BreakPolicyFactory' );
			$bpf->setObjectFromArray( $data );
			if ( $bpf->isValid() ) {
				return $bpf->Save();
			}
		}

		return FALSE;
	}
	function BreakPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				default:
					//Default policies for other countries.
					$this->createBreakPolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'Break1',
													'type_id' => 20, //Normal
													'trigger_time' => ( 2 * 3600 ), //2hrs
													'amount' => ( 15 * 60 ), //15min
													'auto_detect_type_id' => 20, //Punch Type
													'minimum_punch_time' => ( 5 * 60 ), //5min
													'maximum_punch_time' => ( 19 * 60 ), //19min
													'include_break_punch_time' => FALSE,
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Break Time' ) ),
												)
											);
					$this->createBreakPolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'Break2',
													'type_id' => 20, //Normal
													'trigger_time' => ( 5 * 3600 ), //5hrs
													'amount' => ( 15 * 60 ), //15min
													'auto_detect_type_id' => 20, //Punch Type
													'minimum_punch_time' => ( 5 * 60 ), //5min
													'maximum_punch_time' => ( 19 * 60 ), //19min
													'include_break_punch_time' => FALSE,
													'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Break Time' ) ),
												)
											);

					break;
			}
		}

		return TRUE;
	}

	function createSchedulePolicy( $data ) {
		if ( is_array($data) ) {
			$spf = TTnew( 'SchedulePolicyFactory' );
			$spf->setObjectFromArray( $data );
			if ( $spf->isValid() ) {
				return $spf->Save();
			}
		}

		return FALSE;
	}
	function SchedulePolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				default:
					//Default policies for other countries.
					$this->createSchedulePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'No Lunch',
													'meal_policy_id' => FALSE,
													'start_stop_window' => (3600 * 2), //1 hr
												)
											);
					$this->createSchedulePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => '30min Lunch',
													'meal_policy_id' => $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ),
													//'break_policy_id' => FALSE
													'start_stop_window' => (3600 * 2), //1 hr
												)
											);
					$this->createSchedulePolicy(
												array(
													'company_id' => $this->getCompany(),
													'name' => '60min Lunch',
													'meal_policy_id' => $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ),
													//'break_policy_id' => FALSE
													'start_stop_window' => (3600 * 2), //1 hr
												)
											);
					break;
			}
		}

		return TRUE;
	}


	function getAccrualPolicyAccountByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$apalf = TTnew( 'AccrualPolicyAccountListFactory' );
		$apalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $apalf->getRecordCount() > 0 ) {
			return $apalf->getCurrent()->getId();
		}

		return FALSE;
	}
	function createAccrualPolicyAccount( $data ) {
		if ( is_array($data) ) {
			$apaf = TTnew( 'AccrualPolicyAccountFactory' );
			$apaf->setObjectFromArray( $data );
			if ( $apaf->isValid() ) {
				return $apaf->Save();
			}
		}

		return FALSE;
	}

	function getAccrualPolicyByCompanyIDAndTypeAndName( $type_id, $name ) {
		$filter_data = array(
								'type_id' => array($type_id),
								'name' => $name
							);
		$acplf = TTnew( 'AccrualPolicyListFactory' );
		$acplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $acplf->getRecordCount() > 0 ) {
			return $acplf->getCurrent()->getId();
		}

		return FALSE;
	}
	function createAccrualPolicy( $data ) {
		if ( is_array($data) ) {
			$apf = TTnew( 'AccrualPolicyFactory' );
			$apf->setObjectFromArray( $data );
			if ( $apf->isValid() ) {
				return $apf->Save();
			}
		}

		return FALSE;
	}
	function createAccrualPolicyMilestone( $data ) {
		if ( is_array($data) ) {
			$apmf = TTnew( 'AccrualPolicyMilestoneFactory' );
			$apmf->setObjectFromArray( $data );
			if ( $apmf->isValid() ) {
				return $apmf->Save();
			}
		}

		return FALSE;
	}
	function AccrualPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			//Time Bank
			$this->createAccrualPolicyAccount(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Time Bank',
											'enable_pay_stub_balance_display' => TRUE,
										)
									);

			switch ($country) {
				case 'ca':
					break;
				case 'us':
					//Vacation
					$accrual_policy_account_id = $this->createAccrualPolicyAccount(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'Paid Time Off (PTO)',
													'enable_pay_stub_balance_display' => TRUE,
												)
											);
					if ( $accrual_policy_account_id == FALSE ) {
						$accrual_policy_account_id = $this->getAccrualPolicyAccountByCompanyIDAndName( 'Paid Time Off (PTO)' );
					}

					$accrual_policy_id = $this->createAccrualPolicy(
												array(
													'company_id' => $this->getCompany(),
													'accrual_policy_account_id' => $accrual_policy_account_id,
													'name' => 'Paid Time Off (PTO)',
													'type_id' => 20, //Calendar
													'apply_frequency_id' => 10, //Each pay period.
													'milestone_rollover_hire_date' => TRUE,
													'minimum_employed_days' => 0,
												)
											);

					if ( $accrual_policy_id > 0 ) {
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 0,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 5,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
					}
					unset($accrual_policy_account_id, $accrual_policy_id);
					
					break;
				default:
					//Vacation
					$accrual_policy_account_id = $this->createAccrualPolicyAccount(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'Vacation',
													'enable_pay_stub_balance_display' => TRUE,
												)
											);
					if ( $accrual_policy_account_id == FALSE ) {
						$accrual_policy_account_id = $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' );
					}

					$accrual_policy_id = $this->createAccrualPolicy(
												array(
													'company_id' => $this->getCompany(),
													'accrual_policy_account_id' => $accrual_policy_account_id,
													'name' => 'Vacation',
													'type_id' => 20, //Calendar
													'apply_frequency_id' => 10, //Each pay period.
													'milestone_rollover_hire_date' => TRUE,
													'minimum_employed_days' => 0,
												)
											);

					if ( $accrual_policy_id > 0 ) {
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 0,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 5,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
					}
					unset($accrual_policy_account_id, $accrual_policy_id);

					//Sick
					$accrual_policy_account_id = $this->createAccrualPolicyAccount(
												array(
													'company_id' => $this->getCompany(),
													'name' => 'Sick',
													'enable_pay_stub_balance_display' => TRUE,
												)
											);
					if ( $accrual_policy_account_id == FALSE ) {
						$accrual_policy_account_id = $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' );
					}

					$accrual_policy_id = $this->createAccrualPolicy(
												array(
													'company_id' => $this->getCompany(),
													'accrual_policy_account_id' => $accrual_policy_account_id,
													'name' => 'Sick',
													'type_id' => 20, //Calendar
													'apply_frequency_id' => 10, //Each pay period.
													'milestone_rollover_hire_date' => TRUE,
													'minimum_employed_days' => 0,
												)
											);
					if ( $accrual_policy_id > 0 ) {
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 0,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
						$this->createAccrualPolicyMilestone(
													array(
														'accrual_policy_id' => $accrual_policy_id,
														'length_of_service' => 5,
														'length_of_service_unit_id' => 40, //Years
														'accrual_rate' => ( 0 * 3600 ),
														'maximum_time' => ( 0 * 3600 ),
														'rollover_time' => ( 9999 * 3600 ),
													)
												);
					}
					unset($accrual_policy_account_id, $accrual_policy_id);

					break;
			}
		}

		//Canada
		if ( $country == 'ca' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);

			$accrual_policy_account_id = $this->createAccrualPolicyAccount(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Vacation',
											'enable_pay_stub_balance_display' => TRUE,
										)
									);
			if ( $accrual_policy_account_id == FALSE ) {
				$accrual_policy_account_id = $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' );
			}

			$accrual_policy_id = $this->createAccrualPolicy(
										array(
											'company_id' => $this->getCompany(),
											'accrual_policy_account_id' => $accrual_policy_account_id,
											'name' => strtoupper($province) .' - Vacation',
											'type_id' => 20, //Calendar
											'apply_frequency_id' => 10, //Each pay period.
											'milestone_rollover_hire_date' => TRUE,
											'minimum_employed_days' => 0,
										)
									);

			if ( in_array( $province, array('bc', 'ab', 'mb', 'qc', 'nt', 'nu') ) ) {
				if ( $accrual_policy_id > 0 ) {
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 0,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 80 * 3600 ),
													'maximum_time' => ( 80 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 5,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 120 * 3600 ),
													'maximum_time' => ( 120 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
				}
				unset($accrual_policy_id);
			}

			if ( in_array( $province, array('sk') ) ) {
				if ( $accrual_policy_id > 0 ) {
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 0,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 120 * 3600 ),
													'maximum_time' => ( 120 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 10,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 160 * 3600 ),
													'maximum_time' => ( 160 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
				}
				unset($accrual_policy_id);
			}

			if ( in_array( $province, array('on', 'yt') ) ) {
				if ( $accrual_policy_id > 0 ) {
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 0,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 80 * 3600 ),
													'maximum_time' => ( 80 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
				}
				unset($accrual_policy_id);
			}

			if ( in_array( $province, array('nb', 'ns', 'pe') ) ) {
				if ( $accrual_policy_id > 0 ) {
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 0,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 80 * 3600 ),
													'maximum_time' => ( 80 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 8,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 120 * 3600 ),
													'maximum_time' => ( 120 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);

				}
				unset($accrual_policy_id);
			}

			if ( in_array( $province, array('nl') ) ) {
				if ( $accrual_policy_id > 0 ) {
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 0,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 80 * 3600 ),
													'maximum_time' => ( 80 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);
					$this->createAccrualPolicyMilestone(
												array(
													'accrual_policy_id' => $accrual_policy_id,
													'length_of_service' => 15,
													'length_of_service_unit_id' => 40, //Years
													'accrual_rate' => ( 120 * 3600 ),
													'maximum_time' => ( 120 * 3600 ),
													'rollover_time' => ( 9999 * 3600 ),
												)
											);

				}
				unset($accrual_policy_account_id, $accrual_policy_id);
			}
			unset($accrual_policy_account_id);


			//Sick
			$accrual_policy_account_id = $this->createAccrualPolicyAccount(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick',
											'enable_pay_stub_balance_display' => TRUE,
										)
									);
			if ( $accrual_policy_account_id == FALSE ) {
				$accrual_policy_account_id = $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' );
			}

			$accrual_policy_id = $this->createAccrualPolicy(
										array(
											'company_id' => $this->getCompany(),
											'accrual_policy_account_id' => $accrual_policy_account_id,
											'name' => 'Sick',
											'type_id' => 20, //Calendar
											'apply_frequency_id' => 10, //Each pay period.
											'milestone_rollover_hire_date' => TRUE,
											'minimum_employed_days' => 0,
										)
									);
			if ( $accrual_policy_id > 0 ) {
				$this->createAccrualPolicyMilestone(
											array(
												'accrual_policy_id' => $accrual_policy_id,
												'length_of_service' => 0,
												'length_of_service_unit_id' => 40, //Years
												'accrual_rate' => ( 0 * 3600 ),
												'maximum_time' => ( 0 * 3600 ),
												'rollover_time' => ( 9999 * 3600 ),
											)
										);
				$this->createAccrualPolicyMilestone(
											array(
												'accrual_policy_id' => $accrual_policy_id,
												'length_of_service' => 5,
												'length_of_service_unit_id' => 40, //Years
												'accrual_rate' => ( 0 * 3600 ),
												'maximum_time' => ( 0 * 3600 ),
												'rollover_time' => ( 9999 * 3600 ),
											)
										);
			}
			unset($accrual_policy_account_id, $accrual_policy_id);
		}
		
		return TRUE;
	}


	function getAbsencePolicyByCompanyIDAndTypeAndName( $type_id, $name ) {
		$filter_data = array(
								'type_id' => $type_id,
								'name' => $name
							);
		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$aplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $aplf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $aplf as $ap_obj ) {
				$retarr[] = $ap_obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}

	function createAbsencePolicy( $data ) {
		if ( is_array($data) ) {
			$apf = TTnew( 'AbsencePolicyFactory' );
			$apf->setObjectFromArray( $data );
			if ( $apf->isValid() ) {
				return $apf->Save();
			}
		}

		return FALSE;
	}
	function AbsencePolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Vacation (PAID)',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Vacation' ) )
										)
									);

			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Vacation (UNPAID)',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Vacation (UNPAID)' ) ),
										)
									);

			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick (PAID)',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Sick' ) ),
										)
									);
			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick (UNPAID)',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Sick (UNPAID)' ) ),
										)
									);

			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Jury Duty',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Jury Duty' ) ),
										)
									);
			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Bereavement',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Bereavement' ) ),
										)
									);

			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Statutory Holiday',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Statutory Holiday' ) )
										)
									);
			$this->createAbsencePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Time Bank (Withdrawal)',
											'pay_code_id' => current( $this->getPayCodeByCompanyIDAndName( 'Time Bank' ) )
										)
									);

		}
		
		return TRUE;
	}




	function getPayFormulaPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$lf = TTnew( 'PayFormulaPolicyListFactory' );
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			foreach( $lf as $obj ) {
				return $obj->getCurrent()->getId();
			}
		}

		return FALSE;
	}

	function createPayFormulaPolicy( $data ) {
		if ( is_array($data) ) {
			$f = TTnew( 'PayFormulaPolicyFactory' );
			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save();
			}
		}

		return FALSE;
	}
	function PayFormulaPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		if ( $country != '' AND $province == '' ) {
			//Common for all countries.
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'None ($0)',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 0.00,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 1.00,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'OverTime (1.5x)',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 1.50,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'OverTime (2.0x)',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 2.00,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Premium 1',
											'pay_type_id' => 20, //Premium Only
											'rate' => 0.50,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Premium 2',
											'pay_type_id' => 20, //Premium Only
											'rate' => 0.75,
											'wage_group_id' => 0,
											'accrual_rate' => 0.00,
											'accrual_policy_id' => 0,
										)
									);

			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 1.00,
											'wage_group_id' => 0,
											'accrual_rate' => 1.00,
											'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Sick' ),
										)
									);
			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Time Bank',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 0.00,
											'wage_group_id' => 0,
											'accrual_rate' => 1.00,
											'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Time Bank' ),
										)
									);

			$this->createPayFormulaPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Vacation',
											'pay_type_id' => 10, //Pay Multiplied By Factor
											'rate' => 1.00,
											'wage_group_id' => 0,
											'accrual_rate' => 1.00,
											'accrual_policy_account_id' => $this->getAccrualPolicyAccountByCompanyIDAndName( 'Vacation' ),
										)
									);

		}
		
		return TRUE;
	}


	function getPayCodeByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$lf = TTnew( 'PayCodeListFactory' );
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $lf as $obj ) {
				$retarr[] = $obj->getCurrent()->getId();
				//return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}

	function createPayCode( $data ) {
		if ( is_array($data) ) {
			$f = TTnew( 'PayCodeFactory' );
			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save();
			}
		}

		return FALSE;
	}
	function PayCode( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		if ( $country != '' AND $province == '' ) {
			//Common for all countries.
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'UnPaid',
											'code' => 'UNPAID',
											'type_id' => 20, //UNPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time',
											'code' => 'REG',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Lunch Time',
											'code' => 'LNH',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Break Time',
											'code' => 'BRK',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Regular Time' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'OverTime (1.5x)',
											'code' => 'OT1',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 1' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'OverTime (1.5x)' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'OverTime (2.0x)',
											'code' => 'OT1',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Over Time 2' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'OverTime (2.0x)' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Premium 1',
											'code' => 'PRE1',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 1' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Premium 1' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Premium 2',
											'code' => 'PRE2',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Premium 2' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Premium 2' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick',
											'code' => 'SICK',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Sick' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Sick' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Sick (UNPAID)',
											'code' => 'USICK',
											'type_id' => 20, //UnPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Time Bank',
											'code' => 'BANK',
											'type_id' => 20, //UNPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Time Bank' ),
										)
									);
			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Statutory Holiday',
											'code' => 'STAT',
											'type_id' => 10, //PAID
											'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Statutory Holiday' ),
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Regular' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Vacation (UNPAID)',
											'code' => 'UVAC',
											'type_id' => 20, //UnPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Jury Duty',
											'code' => 'JURY',
											'type_id' => 20, //UnPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
										)
									);

			$this->createPayCode(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Bereavement',
											'code' => 'BEREAV',
											'type_id' => 20, //UnPAID
											'pay_stub_entry_account_id' => 0,
											'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'None ($0)' ),
										)
									);

			if ( $country == 'ca' ) {
				$this->createPayCode(
											array(
												'company_id' => $this->getCompany(),
												'name' => 'Vacation',
												'code' => 'VAC',
												'type_id' => 10, //PAID
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation Accrual Release' ),
												'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Vacation' ),
											)
										);
			} else {
				$this->createPayCode(
											array(
												'company_id' => $this->getCompany(),
												'name' => 'Vacation',
												'code' => 'VAC',
												'type_id' => 10, //PAID
												'pay_stub_entry_account_id' => $this->getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( 10, 'Vacation' ),
												'pay_formula_policy_id' => $this->getPayFormulaPolicyByCompanyIDAndName( 'Vacation' ),
											)
										);
			}
		}
		
		return TRUE;
	}

	function getContributingPayCodePolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$lf = TTnew( 'ContributingPayCodePolicyListFactory' );
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $lf as $obj ) {
				//$retarr[] = $obj->getCurrent()->getId();
				return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}

	function createContributingPayCodePolicy( $data ) {
		if ( is_array($data) ) {
			$f = TTnew( 'ContributingPayCodePolicyFactory' );
			$data['id'] = $f->getNextInsertId();

			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save( TRUE, TRUE );
			}
		}

		return FALSE;
	}
	function ContributingPayCodePolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		if ( $country != '' AND $province == '' ) {
			//Common for all countries.
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Meal',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Meal + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' )
																	),
										)
									);

			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Paid Absence',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
																	$this->getPayCodeByCompanyIDAndName( '%Vacation' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Paid Absence + Meal + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' ),
																	$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
																	$this->getPayCodeByCompanyIDAndName( '%Vacation' )
																	),
										)
									);



			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Meal',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Meal + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Paid Absence',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
																	$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
																	$this->getPayCodeByCompanyIDAndName( '%Vacation' )
																	),
										)
									);
			$this->createContributingPayCodePolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Paid Absence + Meal + Break',
											'pay_code' => array_merge(
																	$this->getPayCodeByCompanyIDAndName( 'Regular Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Lunch Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'Break Time' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (1.5x)' ),
																	$this->getPayCodeByCompanyIDAndName( 'OverTime (2.0x)' ),
																	$this->getPayCodeByCompanyIDAndName( '"Sick"' ),
																	$this->getPayCodeByCompanyIDAndName( '%Vacation' )
																	),
										)
									);
		}
		
		return TRUE;
	}


	function getContributingShiftPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$lf = TTnew( 'ContributingShiftPolicyListFactory' );
		$lf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $lf->getRecordCount() > 0 ) {
			$retarr = array();
			foreach( $lf as $obj ) {
				//$retarr[] = $obj->getCurrent()->getId();
				return $obj->getCurrent()->getId();
			}

			return $retarr;
		}

		return FALSE;
	}

	function createContributingShiftPolicy( $data ) {
		if ( is_array($data) ) {
			$f = TTnew( 'ContributingShiftPolicyFactory' );
			$data['id'] = $f->getNextInsertId();

			$f->setObjectFromArray( $data );
			if ( $f->isValid() ) {
				return $f->Save( TRUE, TRUE );
			}
		}

		return FALSE;
	}
	function ContributingShiftPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		if ( $country != '' AND $province == '' ) {
			//Common for all countries.
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Break',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Break' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Meal',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Meal' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Meal + Break',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Meal + Break' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Paid Absence',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Paid Absence' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + Paid Absence + Meal + Break',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + Paid Absence + Meal + Break' ),
										)
									);

			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Meal',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Meal' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Meal + Break',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Meal + Break' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Paid Absence',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence' ),
										)
									);
			$this->createContributingShiftPolicy(
										array(
											'company_id' => $this->getCompany(),
											'name' => 'Regular Time + OT + Paid Absence + Meal + Break',
											'contributing_pay_code_policy_id' => $this->getContributingPayCodePolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
										)
									);
		}

		return TRUE;
	}


	function getHolidayPolicyByCompanyIDAndName( $name ) {
		$filter_data = array(
								'name' => $name
							);
		$hplf = TTnew( 'HolidayPolicyListFactory' );
		$hplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), $filter_data );
		if ( $hplf->getRecordCount() > 0 ) {
			return $hplf->getCurrent()->getId();
		}

		return FALSE;
	}
	function createHolidayPolicy( $data ) {
		if ( is_array($data) ) {
			$hpf = TTnew( 'HolidayPolicyFactory' );
			$data['id'] = $hpf->getNextInsertId();

			if ( isset($data['absence_policy_id']) AND is_array($data['absence_policy_id']) ) {
				$data['absence_policy_id'] = $data['absence_policy_id'][0];
			}

			$hpf->setObjectFromArray( $data );
			if ( $hpf->isValid() ) {
				return $hpf->Save( TRUE, TRUE );
			}
		}

		return FALSE;
	}
	function HolidayPolicy( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				case 'ca':
					break;
				case 'us':
				default:
					//Default policies for other countries.
					$this->createHolidayPolicy(
									array(
										'company_id' => $this->getCompany(),
										'name' => strtoupper($country) .' - Statutory Holiday',
										'type_id' => 10, //Standard
										'default_schedule_status_id' => 20, //Absent
										'minimum_employed_days' => 30,

										'minimum_time' => (8 * 3600), //8hrs

										//'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
										'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, 'Statutory Holiday' ),
										'recurring_holiday_id' => (array)$this->getRecurringHolidayByCompanyIDAndName( $country.'%' ),
										'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Meal + Break' ),
										'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
									)
								);

					break;
			}
		}

		if ( $country == 'ca' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			if ( in_array( $province, array('bc') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 30,

									//Prior to the holiday
									'minimum_worked_days' => 15, //Employee must work at least
									'minimum_worked_period_days' => 30, //Of the last X days
									'worked_scheduled_days' => 0, //Calendar days

									//After the holiday
									'minimum_worked_after_period_days' => 0,
									'minimum_worked_after_days' => 0,
									'worked_after_scheduled_days' => 0,

									//Averaging
									'average_time_days' => 30, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}
			if ( in_array( $province, array('ab') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 5, //Employee must work at least
									'minimum_worked_period_days' => 9, //Of the last X days
									'worked_scheduled_days' => 2, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 63, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => FALSE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}
			if ( in_array( $province, array('sk') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 0, //Employee must work at least
									'minimum_worked_period_days' => 0, //Of the last X days
									'worked_scheduled_days' => 0, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 0,
									'minimum_worked_after_days' => 0,
									'worked_after_scheduled_days' => 0,

									//Averaging
									'average_time_days' => 28, //Days to average time over
									'average_time_worked_days' => FALSE, //Only days worked
									'average_days' => 20, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('mb') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 28, //Days to average time over
									'average_time_worked_days' => FALSE, //Only days worked
									'average_days' => 20, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('on') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 28, //Days to average time over
									'average_time_worked_days' => FALSE, //Only days worked
									'average_days' => 20, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Boxing Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('qc') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 28, //Days to average time over
									'average_time_worked_days' => FALSE, //Only days worked
									'average_days' => 20, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('nb') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 90,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 30, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}


			if ( in_array( $province, array('ns') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 0,

									//Prior to the holiday
									'minimum_worked_days' => 15, //Employee must work at least
									'minimum_worked_period_days' => 30, //Of the last X days
									'worked_scheduled_days' => 0, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 30, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('pe') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 30,

									//Prior to the holiday
									'minimum_worked_days' => 15, //Employee must work at least
									'minimum_worked_period_days' => 30, //Of the last X days
									'worked_scheduled_days' => 0, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 30, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('nl') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 30,

									//Prior to the holiday
									'minimum_worked_days' => 15, //Employee must work at least
									'minimum_worked_period_days' => 30, //Of the last X days
									'worked_scheduled_days' => 0, //Holiday week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 21, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => TRUE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' )
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Paid Absence + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('yt') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 30,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Scheduled week days

									//After the holiday
									'minimum_worked_after_period_days' => 1,
									'minimum_worked_after_days' => 1,
									'worked_after_scheduled_days' => 1,

									//Averaging
									'average_time_days' => 14, //Days to average time over
									'average_time_worked_days' => FALSE, //Only days worked
									'average_days' => 10, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => FALSE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}

			if ( in_array( $province, array('nt', 'nu') ) ) {
				$this->createHolidayPolicy(
								array(
									'company_id' => $this->getCompany(),
									'name' => strtoupper($province) .' - Statutory Holiday',
									'type_id' => 30, //Advanced: Average
									'default_schedule_status_id' => 20, //Absent
									'minimum_employed_days' => 30,

									//Prior to the holiday
									'minimum_worked_days' => 1, //Employee must work at least
									'minimum_worked_period_days' => 1, //Of the last X days
									'worked_scheduled_days' => 1, //Scheduled week days

									//After the holiday
									'minimum_worked_after_period_days' => 0,
									'minimum_worked_after_days' => 0,
									'worked_after_scheduled_days' => 0,

									//Averaging
									'average_time_days' => 28, //Days to average time over
									'average_time_worked_days' => TRUE, //Only days worked
									'average_days' => 0, //Divsor for average formula.

									'minimum_time' => 0,
									'maximum_time' => 0,

									'include_paid_absence_time' => FALSE,
									'absence_policy_id' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( 10, strtoupper($province) .' - Statutory Holiday' ),
									'recurring_holiday_id' => array_merge(
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - New Year%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Good Friday%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Victoria%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Canada%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Labour%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Thanksgiving%' ),
																		  //(array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Remembrance%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($country).' - Christmas Day%' ),
																		  (array)$this->getRecurringHolidayByCompanyIDAndName( strtoupper($province).'%' )
																		),

									'contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT + Meal + Break' ),
									'eligible_contributing_shift_policy_id' => $this->getContributingShiftPolicyByCompanyIDAndName( 'Regular Time + OT' ),
								)
							);
			}
		}

		return TRUE;
	}

	function createPolicyGroup( $data ) {
		if ( is_array($data) ) {
			$pgf = TTnew( 'PolicyGroupFactory' );
			$data['id'] = $pgf->getNextInsertId();
			$pgf->setObjectFromArray( $data );
			if ( $pgf->isValid() ) {
				return $pgf->Save( TRUE, TRUE );
			}
		}

		return FALSE;
	}

	function PolicyGroup( $country = NULL, $province = NULL, $district = NULL, $industry = NULL ) {
		Debug::text('Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		if ( $country != '' AND $province == '' ) {
			switch ($country) {
				case 'ca':
					break;
				case 'us':
					break;
				default:
					//Default policies for other countries.
					$this->createPolicyGroup(
									array(
										'company_id' => $this->getCompany(),
										'name' => strtoupper($province) .' - Hourly Employees',
										'over_time_policy' => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($province).'%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ) ),
										'meal_policy' => array( $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ),
										'accrual_policy' => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper($province).'%' ),
										'holiday_policy' => $this->getHolidayPolicyByCompanyIDAndName( strtoupper($province).'%' ),
										'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
										'absence_policy' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( array(10, 20), '%' ),
									)
								);
					break;
			}
		}

		if ( $country == 'us' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			$this->createPolicyGroup(
							array(
								'company_id' => $this->getCompany(),
								'name' => strtoupper($province) .' - Hourly (OT Non-Exempt)',
								'over_time_policy' => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($province).'%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ) ),
								'meal_policy' => array( $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ),
								'accrual_policy' => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper($province).'%' ),
								'holiday_policy' => $this->getHolidayPolicyByCompanyIDAndName( strtoupper($province).'%' ),
								'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
								'absence_policy' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( array(10, 20), '%' ),
							)
						);

			$this->createPolicyGroup(
							array(
								'company_id' => $this->getCompany(),
								'name' => strtoupper($province) .' - Salary (OT Exempt)',
								//'over_time_policy' => (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ),
								'meal_policy' => array( $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ),
								'accrual_policy' => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper($province).'%' ),
								'holiday_policy' => $this->getHolidayPolicyByCompanyIDAndName( strtoupper($province).'%' ),
								'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
								'absence_policy' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( array(10, 20), '%' ),
							)
						);
		}
		if ( $country == 'ca' AND $province != '' ) {
			Debug::text('Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
			$this->createPolicyGroup(
							array(
								'company_id' => $this->getCompany(),
								'name' => strtoupper($province) .' - Hourly Employees',
								'over_time_policy' => array_merge( (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($province).'%' ), (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ) ),
								'meal_policy' => array( $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ),
								'accrual_policy' => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper($province).'%' ),
								'holiday_policy' => $this->getHolidayPolicyByCompanyIDAndName( strtoupper($province).'%' ),
								'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
								'absence_policy' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( array(10, 20), '%' )
							)
						);

			$this->createPolicyGroup(
							array(
								'company_id' => $this->getCompany(),
								'name' => strtoupper($province) .' - Salary Employees',
								//'over_time_policy' => (array)$this->getOverTimePolicyByCompanyIDAndName( strtoupper($country).' - Holiday' ),
								'meal_policy' => array( $this->getMealPolicyByCompanyIDAndName( '30min Lunch' ), $this->getMealPolicyByCompanyIDAndName( '60min Lunch' ) ),
								'accrual_policy' => $this->getAccrualPolicyByCompanyIDAndTypeAndName( 20, strtoupper($province).'%' ),
								'holiday_policy' => $this->getHolidayPolicyByCompanyIDAndName( strtoupper($province).'%' ),
								'exception_policy_control_id' => $this->getExceptionPolicyByCompanyIDAndName( 'Default' ),
								'absence_policy' => $this->getAbsencePolicyByCompanyIDAndTypeAndName( array(10, 20), '%' )
							)
						);
		}

		return TRUE;
	}

	function Permissions() {
		//Always assume that Administrator permission group already exists.
		//This must be called before UserDefaults.
		Debug::text('Adding Preset Permission Groups', __FILE__, __LINE__, __METHOD__, 9);

		$pf = TTnew( 'PermissionFactory' );
		$pf->StartTransaction();

		$preset_flags = array_keys( $pf->getOptions('preset_flags') );
		$preset_options = $pf->getOptions('preset');
		unset($preset_options[40]); //Remove Administration presets, as they should already exist.
		$preset_level_options = $pf->getOptions('preset_level');
		foreach( $preset_options as $preset_id => $preset_name ) {
			$pcf = TTnew( 'PermissionControlFactory' );
			$pcf->setCompany( $this->getCompanyObject()->getID() );
			$pcf->setName( $preset_name );
			$pcf->setDescription( '' );
			$pcf->setLevel( $preset_level_options[$preset_id] );
			if ( $pcf->isValid() ) {
				$pcf_id = $pcf->Save(FALSE);
				$pf->applyPreset($pcf_id, $preset_id, $preset_flags );
			}
		}
		$pf->CommitTransaction();

		return TRUE;
	}

	function UserDefaults() {
		if ( is_object( $this->getCompanyObject() ) ) {
			//User Default settings, always do this last.
			if ( is_object( $this->getCompanyObject()->getUserDefaultObject() ) ) {
				$udf = $this->getCompanyObject()->getUserDefaultObject();
			} else {
				$udf = TTnew( 'UserDefaultFactory' );
			}
			$udf->setCompany( $this->getCompanyObject()->getID() );
			$udf->setCity( $this->getCompanyObject()->getCity() );
			$udf->setCountry( $this->getCompanyObject()->getCountry() );
			$udf->setProvince( $this->getCompanyObject()->getProvince() );
			$udf->setWorkPhone( $this->getCompanyObject()->getWorkPhone() );

			$udf->setLanguage( 'en' );
			$udf->setItemsPerPage( 50 );

			//Get currently logged in user preferences and create defaults from those.
			if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getUserPreferenceObject() ) ) {
				$udf->setDateFormat( $this->getUserObject()->getUserPreferenceObject()->getDateFormat() );
				$udf->setTimeFormat( $this->getUserObject()->getUserPreferenceObject()->getTimeFormat() );
				$udf->setTimeUnitFormat( $this->getUserObject()->getUserPreferenceObject()->getTimeUnitFormat() );
				$udf->setStartWeekDay( $this->getUserObject()->getUserPreferenceObject()->getStartWeekDay() );
			} else {
				$udf->setDateFormat( 'd-M-y' );
				$udf->setTimeFormat( 'g:i A' );
				$udf->setTimeUnitFormat( 10 );
				$udf->setStartWeekDay( 0 );
			}

			//Get Pay Period Schedule
			$ppslf = TTNew('PayPeriodScheduleListFactory');
			$ppslf->getByCompanyId( $this->getCompanyObject()->getID() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$udf->setPayPeriodSchedule( $ppslf->getCurrent()->getID() );
			}

			//Get Policy Group
			$pglf = TTNew('PolicyGroupListFactory');
			$pglf->getByCompanyId( $this->getCompanyObject()->getID() );
			if ( $pglf->getRecordCount() > 0 ) {
				$udf->setPolicyGroup( $pglf->getCurrent()->getID() );
			}

			//Permissions
			$pclf = TTnew('PermissionControlListFactory');
			$pclf->getByCompanyIdAndLevel( $this->getCompanyObject()->getID(), 1 );
			if ( $pclf->getRecordCount() > 0 ) {
				$udf->setPermissionControl( $pclf->getCurrent()->getID() );
			}

			//Currency
			$clf = TTNew('CurrencyListFactory');
			$clf->getByCompanyIdAndDefault( $this->getCompany(), TRUE);
			if ( $clf->getRecordCount() > 0 ) {
				$udf->setCurrency( $clf->getCurrent()->getID() );
			}

			$upf = TTnew( 'UserPreferenceFactory' );
			$udf->setTimeZone( $upf->getLocationTimeZone( $this->getCompanyObject()->getCountry(), $this->getCompanyObject()->getProvince(), $this->getCompanyObject()->getWorkPhone() ) );
			Debug::text('Time Zone: '. $udf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9);

			$udf->setEnableEmailNotificationException( TRUE );
			$udf->setEnableEmailNotificationMessage( TRUE );
			$udf->setEnableEmailNotificationPayStub( TRUE );
			$udf->setEnableEmailNotificationHome( TRUE );

			if ( $udf->isValid() ) {
				Debug::text('Adding User Default settings...', __FILE__, __LINE__, __METHOD__, 9);

				return $udf->Save();
			}
		}

		return FALSE;
	}


	function createPresets( $country = NULL, $province = NULL, $district = NULL, $industry = NULL, $flags = NULL ) {
		$country = strtolower($country);
		$province = strtolower($province);

		//Policies: ( In Order )
		// Accrual
		// PayFormula
		// PayCode
		// Absence
		// Overtime
		// Meal
		// Break
		// Holiday
		// Schedule
		// Premium
		// Exception
		// Policy Groups
		// UserDefaults (this should be called manually after everything is done, outside of this function)
		if ( $country == '' ) {
			$this->PayStubAccounts();
			$this->CompanyDeductions();
			$this->RecurringHolidays();

			$this->AccrualPolicy();
			$this->PayFormulaPolicy();
			$this->PayCode();
			$this->ContributingPayCodePolicy();
			$this->ContributingShiftPolicy();
			
			$this->AbsencePolicy();
			$this->HolidayPolicy();
			$this->RegularTimePolicy();
			$this->OverTimePolicy();
			$this->MealPolicy();
			$this->BreakPolicy();
			$this->SchedulePolicy();
			$this->ExceptionPolicy();

			$this->PolicyGroup();
		} elseif ( $country != '' AND $province == '' ) {
			$this->PayStubAccounts( $country );
			$this->CompanyDeductions( $country );
			$this->RecurringHolidays( $country );

			$this->AccrualPolicy( $country );
			$this->PayFormulaPolicy( $country );
			$this->PayCode( $country );
			$this->ContributingPayCodePolicy( $country );
			$this->ContributingShiftPolicy( $country );

			$this->AbsencePolicy( $country );
			$this->HolidayPolicy( $country );
			$this->RegularTimePolicy( $country );
			$this->OverTimePolicy( $country );
			$this->MealPolicy( $country );
			$this->BreakPolicy( $country );
			$this->SchedulePolicy( $country );
			$this->ExceptionPolicy( $country );

			$this->PolicyGroup( $country );
		} elseif ( $country != '' AND $province != '' ) {
			$this->PayStubAccounts( $country, $province );
			$this->CompanyDeductions( $country, $province );
			$this->RecurringHolidays( $country, $province );

			$this->AccrualPolicy( $country, $province );
			$this->PayFormulaPolicy( $country, $province );
			$this->PayCode( $country, $province );
			$this->ContributingPayCodePolicy( $country, $province );
			$this->ContributingShiftPolicy(  $country, $province );

			$this->AbsencePolicy( $country, $province );
			$this->HolidayPolicy( $country, $province );
			$this->RegularTimePolicy( $country, $province );
			$this->OverTimePolicy( $country, $province);
			$this->MealPolicy( $country, $province );
			$this->BreakPolicy( $country, $province );
			$this->SchedulePolicy( $country, $province);
			$this->ExceptionPolicy( $country, $province );

			$this->PolicyGroup( $country, $province );
		}

		return TRUE;
	}
}
?>
