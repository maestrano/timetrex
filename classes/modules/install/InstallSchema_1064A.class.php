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
 * @package Module_Install
 */
class InstallSchema_1064A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	function addPayCodeIdToContributingPayCodePolicy( $pay_code_id, $contributing_pay_code_policy_id ) {
		$cpcplf = TTnew( 'ContributingPayCodePolicyListFactory' );
		$cpcplf->getById( $contributing_pay_code_policy_id );
		if ( $cpcplf->getRecordCount() == 1 ) {
			foreach( $cpcplf as $cpcp_obj ) {
				$pay_code_ids = $cpcp_obj->getPayCode();
				if ( !is_array( $pay_code_ids) ) {
					Debug::Arr($pay_code_ids, 'No Pay Codes assigned yet, starting from scratch...', __FILE__, __LINE__, __METHOD__, 9);
					$pay_code_ids = array();
				}
				$pay_code_ids[] = $pay_code_id;

				$cpcp_obj->setPayCode( $pay_code_ids );
				if ( $cpcp_obj->isValid() ) {
					$cpcp_obj->Save();
					Debug::Text('Assigning Pay Code: '. $pay_code_id .' To Contributing Pay Code Policy: '. $contributing_pay_code_policy_id, __FILE__, __LINE__, __METHOD__, 9);
					return TRUE;
				}
			}
		}

		Debug::text('ERROR: Unable to assign Pay Code to Contributing Pay Code... Pay Code: '. $pay_code_id .' Contributing Pay Code Policy: '. $contributing_pay_code_policy_id .' Record Count: '. $cpcplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
		return FALSE;
	}
	
	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		$clf = TTNew('CompanyListFactory');
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$x = 0;
			foreach( $clf as $company_obj ) {
				//Go through each permission group, and enable schedule, view_open for for anyone who has schedule, view
				Debug::text('Company: '. $company_obj->getName() .' X: '. $x .' of :'. $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
				$pclf = TTnew( 'PermissionControlListFactory' );
				$pclf->getByCompanyId( $company_obj->getId(), NULL, NULL, NULL, array( 'name' => 'asc' ) ); //Force order to prevent references to columns that haven't been created yet.
				if ( $pclf->getRecordCount() > 0 ) {
					foreach( $pclf as $pc_obj ) {
						Debug::text('Permission Group: '. $pc_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);
						$plf = TTnew( 'PermissionListFactory' );
						$plf->getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue( $company_obj->getId(), $pc_obj->getId(), 'over_time_policy', 'add', 1 ); //Only return records where permission is ALLOWED.
						if ( $plf->getRecordCount() > 0 ) {
							Debug::text('Found permission group with over_time_policy, add enabled: '. $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__, 9);
							$pc_obj->setPermission(
													array(
															'pay_code' => array(
																								'enabled' => TRUE,
																								'view' => TRUE,
																								'add' => TRUE,
																								'edit' => TRUE,
																								'delete' => TRUE
																							),
															'pay_formula_policy' => array(
																								'enabled' => TRUE,
																								'view' => TRUE,
																								'add' => TRUE,
																								'edit' => TRUE,
																								'delete' => TRUE
																							),
															'contributing_pay_code_policy' => array(
																								'enabled' => TRUE,
																								'view' => TRUE,
																								'add' => TRUE,
																								'edit' => TRUE,
																								'delete' => TRUE
																							),
															'contributing_shift_policy' => array(
																								'enabled' => TRUE,
																								'view' => TRUE,
																								'add' => TRUE,
																								'edit' => TRUE,
																								'delete' => TRUE
																							),
															'regular_time_policy' => array(
																								'enabled' => TRUE,
																								'view' => TRUE,
																								'add' => TRUE,
																								'edit' => TRUE,
																								'delete' => TRUE
																							),
														)
													);
						} else {
							Debug::text('Permission group does NOT have over_time_policy, add enabled...', __FILE__, __LINE__, __METHOD__, 9);
						}
					}
				}
				unset($pclf, $plf, $pc_obj);

				//Get list of valid pay stub accounts for this company, so we can make sure we don't get a validation error when creating pay codes.
				$pay_stub_accounts = array();
				$psealf = TTNew('PayStubEntryAccountListFactory');
				$psealf->getByCompanyId( $company_obj->getId() );
				if ( $psealf->getRecordCount() > 0 ) {
					foreach( $psealf as $psea_obj ) {
						$pay_stub_accounts[$psea_obj->getId()] = TRUE;
					}
				}
				unset($psealf);


				//Dummy pay code.
				$pcf = TTNew('PayCodeFactory');
				$pcf->setCompany( $company_obj->getId() );
				$pcf->setName( 'DUMMY (DELETE ME)' ); //Can't change name, as we need to delete this in 1065A schema upgrade.
				$pcf->setCode( 'DUMMY' ); //Can't change code, as we need to delete this in 1065A schema upgrade.
				$pcf->setType( 10 ); //Paid
				$pcf->setPayFormulaPolicy( 0 );
				$pcf->setPayStubEntryAccountId( (int)CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_obj->getId(), 10, TTi18n::gettext('Regular Time')) );
				if ( $pcf->isValid() ) {
					$dummy_pay_code_id = $pcf->Save();
					Debug::text(' Created Dummy Pay Code ID: '. $dummy_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);
				}

				//Create dummy pay code/contributing shift policies, then assign actual pay codes to them later.

				//Contributing Pay Code Policy for just Regular Time
				//Required to calculate Overtime Policies
				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time'], __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::Arr($cpcpf->Validator->getErrors(), 'ERROR: Invalid ContributingPayCodePolicy, unable to continue with upgrade...', __FILE__, __LINE__, __METHOD__, 10);
					return FALSE;
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Meal + Break' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				//Contributing Pay Code Policy for just Regular Time & Overtime
				//Required to calculate Premium Policies
				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime + Meal' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime + Break' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time_and_break_policy'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime + Meal + Break' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				//Contributing Pay Code Policy for just Regular Time & OverTime & Paid Absences
				//Required to calculate Holiday Policies
				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Paid Absence' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_paid_absence'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_paid_absence'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_paid_absence'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Meal + Break + Paid Absence' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime + Paid Absence' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time_and_paid_absence'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

				$cpcpf = TTnew( 'ContributingPayCodePolicyFactory' );
				$cpcpf->setId( $cpcpf->getNextInsertId() );
				$cpcpf->setCompany( $company_obj->getId() );
				$cpcpf->setName( 'Regular Time + Overtime + Meal + Break + Paid Absence' );
				$cpcpf->setPayCode( $dummy_pay_code_id );
				if ( $cpcpf->isValid() ) {
					$contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] = $cpcpf->Save( FALSE, TRUE );
					Debug::Text('Contributing Pay Code Policy ID: '. $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);

					$cspf = TTnew( 'ContributingShiftPolicyFactory' );
					$cspf->setCompany( $company_obj->getId() );
					$cspf->setName( $cpcpf->getName() );
					$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
					$cspf->setMon( TRUE );
					$cspf->setTue( TRUE );
					$cspf->setWed( TRUE );
					$cspf->setThu( TRUE );
					$cspf->setFri( TRUE );
					$cspf->setSat( TRUE );
					$cspf->setSun( TRUE );
					if ( $cspf->isValid() ) {
						$contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] = $cspf->Save();
						Debug::Text('Contributing Shift Policy ID: '. $contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'], __FILE__, __LINE__, __METHOD__, 10);
					}
				}
				unset($cpcpf,$cspf);

			
				//This must go before PayFormulaPolicies are created.
				$aplf = TTNew('AccrualPolicyListFactory');
				$aplf->getByCompanyId( $company_obj->getId() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						//Create Accrual Policy Account with matching ID so we don't have to migrate Accrual/Pay Formula Policy rows.
						$apa_obj = TTNew('AccrualPolicyAccountFactory');
						$apa_obj->setId( $ap_obj->getID() );
						$apa_obj->setCompany( $ap_obj->getCompany() );
						$apa_obj->setName( $ap_obj->getName() );
						$apa_obj->setEnablePayStubBalanceDisplay( $ap_obj->getEnablePayStubBalanceDisplay() );
						if ( $apa_obj->isValid() ) {
							$insert_accrual_policy_account_id = $apa_obj->Save(TRUE, TRUE); //Force lookup

							$ap_obj->setContributingShiftPolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time'] );
							$ap_obj->setLengthOfServiceContributingPayCodePolicy( $contributing_pay_code_policy_insert_id['regular_time_and_over_time'] );

							$ap_obj->setAccrualPolicyAccount( $insert_accrual_policy_account_id );
							if ( $ap_obj->isValid() ) {
								$ap_obj->Save();
							}
						}
					}
				}

				//Create pay formulas.
				$pfpf = TTnew( 'PayFormulaPolicyFactory' );
				$pfpf->setCompany( $company_obj->getId() );
				$pfpf->setName( 'UnPaid (0.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 0.0 );
				$pfpf->setWageGroup( 0 );
				$pfpf->setAccrualRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( 0 );
				if ( $pfpf->isValid() ) {
					$unpaid_pay_formula_policy_id = $pfpf->Save();
				}
				unset($pfpf);


				$pfpf = TTnew( 'PayFormulaPolicyFactory' );
				$pfpf->setCompany( $company_obj->getId() );
				$pfpf->setName( 'Regular (1.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setWageGroup( 0 );
				$pfpf->setAccrualRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( 0 );
				if ( $pfpf->isValid() ) {
					$regular_pay_formula_policy_id = $pfpf->Save();
				}
				unset($pfpf);

				$pay_code_map = array();

				//Regular time pay code.
				$pcf = TTNew('PayCodeFactory');
				$pcf->setCompany( $company_obj->getId() );
				$pcf->setName( 'Regular Time' );
				$pcf->setCode( 'REG' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayFormulaPolicy( $regular_pay_formula_policy_id );
				$pcf->setPayStubEntryAccountId( (int)CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_obj->getId(), 10, TTi18n::gettext('Regular Time')) );
				if ( $pcf->isValid() ) {
					$insert_pay_code_id = $pcf->Save();
					Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

					//Need to convert all wage/wage_with_burden/hourly_rate/hourly_rate_with_burden columns.
					$pay_code_map['/"regular_time"/'] = '"pay_code-'.$insert_pay_code_id.'_time"';
					$pay_code_map['/"regular_time_wage"/'] = '"pay_code-'.$insert_pay_code_id.'_wage"';
					$pay_code_map['/"regular_time_wage_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_wage_with_burden"';
					$pay_code_map['/"regular_time_hourly_rate"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate"';
					$pay_code_map['/"regular_time_hourly_rate_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate_with_burden"';

					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_paid_absence'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
					$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );

					$rtpf = TTnew( 'RegularTimePolicyFactory' );
					$rtpf->setCompany( $company_obj->getId() );
					$rtpf->setName( 'Regular Time' );
					$rtpf->setCalculationOrder( 9999 );
					//Meal/Break time is included in Regular Time in v7.3 and lower, so we need to do that here too.
					//However this makes its so it has to always be included in premium time as well.
					$rtpf->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );
					$rtpf->setPayCode( $insert_pay_code_id );
					if ( $rtpf->isValid() ) {
						$regular_time_policy_id = $rtpf->Save();

						//Add regular time policy to Policy Groups.
						$pglf = TTNew('PolicyGroupListFactory');
						$pglf->getByCompanyId( $company_obj->getId() );
						if ( $pglf->getRecordCount() > 0 ) {
							foreach( $pglf as $pg_obj ) {
								Debug::text(' Adding Regular Time Policy ID: '. $regular_time_policy_id .' to Policy Group: '. $pg_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);
								$pg_obj->setRegularTimePolicy( $regular_time_policy_id );
								if ( $pg_obj->isValid() ) {
									$pg_obj->Save();
								}
							}
						}
						unset($pglf, $pg_obj);
					}
				}
				unset($pcf, $rtpf, $insert_pay_code_id, $regular_time_policy_id);

				//Loop over all overtime/premium/absence/meal/break policies and create pay codes based on them.
				//Update pay_code_id on each policy as we go, so we can use that as the mapping later when user_date_total as modified.
				$otplf = TTNew('OverTimePolicyListFactory');
				$otplf->getByCompanyId( $company_obj->getId() );
				if ( $otplf->getRecordCount() > 0 ) {
					foreach( $otplf as $otp_obj ) {
						Debug::text(' Converting OverTime Policy ID: '. $otp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						$pfpf = TTnew( 'PayFormulaPolicyFactory' );
						$pfpf->setCompany( $company_obj->getId() );
						$pfpf->setName( 'OverTime - '. $otp_obj->getName() .' ('. (float)$otp_obj->getColumn('rate') .'x) ['. $otp_obj->getID() .']' );
						$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
						$pfpf->setRate( (float)$otp_obj->getColumn('rate') );
						$pfpf->setWageGroup( (int)$otp_obj->getColumn('wage_group_id') );
						$pfpf->setAccrualRate( (float)$otp_obj->getColumn('accrual_rate') );
						$pfpf->setAccrualPolicyAccount( (int)$otp_obj->getColumn('accrual_policy_id') );
						if ( $pfpf->isValid() ) {
							$pay_formula_policy_id = $pfpf->Save();
						}

						$pcf = TTNew('PayCodeFactory');
						$pcf->setCompany( $otp_obj->getCompany() );
						$pcf->setName( 'OverTime - '. $otp_obj->getName().' ['. $otp_obj->getID().']' );
						$pcf->setCode( 'OT'.$otp_obj->getID() );
						$pcf->setType( 12 ); //Paid Above Salary
						$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
						if ( isset($pay_stub_accounts[(int)$otp_obj->getColumn('pay_stub_entry_account_id')]) ) {
							$pcf->setPayStubEntryAccountId( (int)$otp_obj->getColumn('pay_stub_entry_account_id') );
						} else {
							Debug::text(' Invalid PayStub Account ID: '. (int)$otp_obj->getColumn('pay_stub_entry_account_id'), __FILE__, __LINE__, __METHOD__, 9);
							$pcf->setPayStubEntryAccountId( 0 );
						}
						if ( $pcf->isValid() ) {
							$insert_pay_code_id = $pcf->Save();
							Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

							$pay_code_map['/"over_time_policy-'.$otp_obj->getID().'"/'] = '"pay_code-'.$insert_pay_code_id.'_time"';
							$pay_code_map['/"over_time_policy-'.$otp_obj->getID().'_wage"/'] = '"pay_code-'.$insert_pay_code_id.'_wage"';
							$pay_code_map['/"over_time_policy-'.$otp_obj->getID().'_wage_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_wage_with_burden"';
							$pay_code_map['/"over_time_policy-'.$otp_obj->getID().'_hourly_rate"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate"';
							$pay_code_map['/"over_time_policy-'.$otp_obj->getID().'_hourly_rate_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate_with_burden"';


							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );

							$otp_obj->setPayCode( $insert_pay_code_id );

							//Regular time already includes Meal/Break policy time as defined above, so don't include it twice by using Reg+Meal+Break here.
							$otp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time'] );
							//$otp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );

							if ( $otp_obj->isValid() ) {
								$otp_obj->Save();
							}
						}
					}
				}
				unset($otplf, $otp_obj, $pcf, $insert_pay_code_id );


				$pplf = TTNew('PremiumPolicyListFactory');
				$pplf->getByCompanyId( $company_obj->getId() );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach( $pplf as $pp_obj ) {
						Debug::text(' Converting Premium Policy ID: '. $pp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						$tmp_rate = $pp_obj->getColumn('rate');
						if ( $pp_obj->getColumn('pay_type_id') == 10 ) {
							//Reduce rate by -1.0 as the pay codes handle the rates now and it doesn't make sense to automatically account for this in premium policies anymore.
							//$tmp_rate = ( $pp_obj->getColumn('rate') >= 1 ) ? ( (float)$pp_obj->getColumn('rate') - 1.0 ) : (float)$pp_obj->getColumn('rate');
							$tmp_rate = ( (float)$pp_obj->getColumn('rate') - 1.0 );
						}

						$pfpf = TTnew( 'PayFormulaPolicyFactory' );
						$pfpf->setCompany( $company_obj->getId() );
						$pfpf->setName( 'Premium - '. $pp_obj->getName() .' ('. $tmp_rate .'x) ['. $pp_obj->getID() .']' );

						if ( $pp_obj->getColumn('pay_type_id') == 20 ) {
							//Convert Pay + Premium (#20) to Flat Hourly Rate (#32), as thats really what it was when considering premium policies.
							$pfpf->setPayType( 32 ); //Flat Hourly Rate
							$pfpf->setRate( $tmp_rate );
						} elseif ( $pp_obj->getColumn('pay_type_id') == 32 ) {
							//In v7 Pay Type #32 (Flat Hourly Rate) wasn't actually using the flat hourly rate
							//specified in the premium policy, but the rate specified by the wage group wage instead.
							//Since we need to keep the historical data the same, keep the policies calculating the same values too.
							//Therefore convert it to Pay Multiplied By Factor of 1.0 using the same wage group.
							$pfpf->setPayType( 10 );
							$pfpf->setRate( 1.0 );
						} else {
							$pfpf->setPayType( $pp_obj->getColumn('pay_type_id') );
							$pfpf->setRate( $tmp_rate );
						}
						$pfpf->setWageGroup( (int)$pp_obj->getColumn('wage_group_id') );
						$pfpf->setAccrualRate( (float)$pp_obj->getColumn('accrual_rate') );
						$pfpf->setAccrualPolicyAccount( (int)$pp_obj->getColumn('accrual_policy_id') );
						if ( $pfpf->isValid() ) {
							$pay_formula_policy_id = $pfpf->Save();
						}

						$pcf = TTNew('PayCodeFactory');
						$pcf->setCompany( $pp_obj->getCompany() );
						$pcf->setName('Premium - '.  $pp_obj->getName() .' ['. $pp_obj->getID().']' );
						$pcf->setCode( 'PRE'.$pp_obj->getID() );
						$pcf->setType( 12 ); //Paid Above Salary
						$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
						if ( isset($pay_stub_accounts[(int)$pp_obj->getColumn('pay_stub_entry_account_id')]) ) {
							$pcf->setPayStubEntryAccountId( (int)$pp_obj->getColumn('pay_stub_entry_account_id') );
						} else {
							Debug::text(' Invalid PayStub Account ID: '. (int)$pp_obj->getColumn('pay_stub_entry_account_id'), __FILE__, __LINE__, __METHOD__, 9);
							$pcf->setPayStubEntryAccountId( 0 );
						}

						if ( $pcf->isValid() ) {
							$insert_pay_code_id = $pcf->Save();
							Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

							$pay_code_map['/"premium_policy-'.$pp_obj->getID().'"/'] = '"pay_code-'.$insert_pay_code_id.'_time"';
							$pay_code_map['/"premium_policy-'.$pp_obj->getID().'_wage"/'] = '"pay_code-'.$insert_pay_code_id.'_wage"';
							$pay_code_map['/"premium_policy-'.$pp_obj->getID().'_wage_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_wage_with_burden"';
							$pay_code_map['/"premium_policy-'.$pp_obj->getID().'_hourly_rate"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate"';
							$pay_code_map['/"premium_policy-'.$pp_obj->getID().'_hourly_rate_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate_with_burden"';

							$pp_obj->setPayCode( $insert_pay_code_id );

							//Regular time already includes Meal/Break policy time as defined above.
							//So we can't exclude Meal/Break from premium policies, we have to just use Regular time by itself always.
							$pp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time'] );
							/*
							if ( $pp_obj->getIncludeMealPolicy() == TRUE AND $pp_obj->getIncludeBreakPolicy() == TRUE ) {
								$pp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
							} elseif( $pp_obj->getIncludeMealPolicy() == TRUE ) {
								$pp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
							} elseif( $pp_obj->getIncludeBreakPolicy() == TRUE ) {
								$pp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
							} else {
								$pp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time'] );
							}
							*/

							if ( $pp_obj->isValid() ) {
								$pp_obj->Save();
							}
						}
					}
				}
				unset($pplf, $pp_obj, $pcf, $insert_pay_code_id );


				$aplf = TTNew('AbsencePolicyListFactory');
				$aplf->getByCompanyId( $company_obj->getId() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						Debug::text(' Converting Absence Policy ID: '. $ap_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						$pfpf = TTnew( 'PayFormulaPolicyFactory' );
						$pfpf->setCompany( $company_obj->getId() );
						$pfpf->setName( 'Absence - '. $ap_obj->getName() .' ('. (float)$ap_obj->getColumn('rate') .'x) ['. $ap_obj->getID() .']' );
						$pfpf->setPayType( 10 ); //Multiplied by factor
						$pfpf->setRate( (float)$ap_obj->getColumn('rate') );
						$pfpf->setWageGroup( (int)$ap_obj->getColumn('wage_group_id') );
						$pfpf->setAccrualRate( ( (float)$ap_obj->getColumn('accrual_rate') * -1 ) ); //Absence time should withdrawl from accrual.
						$pfpf->setAccrualPolicyAccount( (int)$ap_obj->getColumn('accrual_policy_id') );
						if ( $pfpf->isValid() ) {
							$pay_formula_policy_id = $pfpf->Save();
						}

						$pcf = TTNew('PayCodeFactory');
						$pcf->setCompany( $ap_obj->getCompany() );
						$pcf->setName( 'Absence - '. $ap_obj->getName().' ['. $ap_obj->getID().']' );
						$pcf->setCode( 'ABS'.$ap_obj->getID() );
						$pcf->setType( $ap_obj->getColumn('type_id') );
						$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
						if ( isset($pay_stub_accounts[(int)$ap_obj->getColumn('pay_stub_entry_account_id')]) ) {
							$pcf->setPayStubEntryAccountId( (int)$ap_obj->getColumn('pay_stub_entry_account_id') );
						} else {
							Debug::text(' Invalid PayStub Account ID: '. (int)$ap_obj->getColumn('pay_stub_entry_account_id'), __FILE__, __LINE__, __METHOD__, 9);
							$pcf->setPayStubEntryAccountId( 0 );
						}

						if ( $pcf->isValid() ) {
							$insert_pay_code_id = $pcf->Save();
							Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

							$pay_code_map['/"absence_policy-'.$ap_obj->getID().'"/'] = '"pay_code-'.$insert_pay_code_id.'_time"';
							$pay_code_map['/"absence_policy-'.$ap_obj->getID().'_wage"/'] = '"pay_code-'.$insert_pay_code_id.'_wage"';
							$pay_code_map['/"absence_policy-'.$ap_obj->getID().'_wage_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_wage_with_burden"';
							$pay_code_map['/"absence_policy-'.$ap_obj->getID().'_hourly_rate"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate"';
							$pay_code_map['/"absence_policy-'.$ap_obj->getID().'_hourly_rate_with_burden"/'] = '"pay_code-'.$insert_pay_code_id.'_hourly_rate_with_burden"';

							if ( $ap_obj->getColumn('type_id') == 10 OR $ap_obj->getColumn('type_id') == 12 ) { //Paid absences only.
								$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
								$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_paid_absence'] );
								$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
								$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );
							}

							$ap_obj->setPayCode( $insert_pay_code_id );
							if ( $ap_obj->isValid() ) {
								$ap_obj->Save();
							}
						}
					}
				}
				unset($pplf, $pp_obj, $pcf, $insert_pay_code_id );


				$mplf = TTNew('MealPolicyListFactory');
				$mplf->getByCompanyId( $company_obj->getId() );
				if ( $mplf->getRecordCount() > 0 ) {
					foreach( $mplf as $mp_obj ) {
						Debug::text(' Converting Meal Policy ID: '. $mp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						$pcf = TTNew('PayCodeFactory');
						$pcf->setCompany( $mp_obj->getCompany() );
						$pcf->setName( 'Meal - '. $mp_obj->getName().' ['. $mp_obj->getID() .']' );
						$pcf->setCode( 'MEAL'.$mp_obj->getID() );
						$pcf->setType( 10 ); //Paid
						$pcf->setPayStubEntryAccountId( (int)CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($mp_obj->getCompany(), 10, TTi18n::gettext('Regular Time')) );

						//Since Auto-Add lunch time is included in Regular Time prior to TT v8, the pay formula should always be at a 0 rate since its already paid.
						$pcf->setPayFormulaPolicy( $unpaid_pay_formula_policy_id );
						//if ( $mp_obj->getType() == 15 ) { //Auto-Add
						//	$pcf->setPayFormulaPolicy( $regular_pay_formula_policy_id );
						//} else {
						//	$pcf->setPayFormulaPolicy( 0 );
						//}
						if ( $pcf->isValid() ) {
							$insert_pay_code_id = $pcf->Save();
							Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );

							$mp_obj->setPayCode( $insert_pay_code_id );
							if ( $mp_obj->isValid() ) {
								$mp_obj->Save();
							}
						}
					}
				}
				unset($mplf, $mp_obj, $pcf, $insert_pay_code_id );


				$bplf = TTNew('BreakPolicyListFactory');
				$bplf->getByCompanyId( $company_obj->getId() );
				if ( $bplf->getRecordCount() > 0 ) {
					foreach( $bplf as $bp_obj ) {
						Debug::text(' Converting Break Policy ID: '. $bp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						$pcf = TTNew('PayCodeFactory');
						$pcf->setCompany( $bp_obj->getCompany() );
						$pcf->setName( 'Break - '. $bp_obj->getName().' ['. $bp_obj->getID() .']' );
						$pcf->setCode( 'BRK'.$bp_obj->getID() );
						$pcf->setType( 10 ); //Paid
						$pcf->setPayStubEntryAccountId( (int)CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($bp_obj->getCompany(), 10, TTi18n::gettext('Regular Time')) );

						//Since Auto-Add break time is included in Regular Time prior to TT v8, the pay formula should always be at a 0 rate since its already paid.
						$pcf->setPayFormulaPolicy( $unpaid_pay_formula_policy_id );
						//if ( $bp_obj->getType() == 15 ) { //Auto-Add
						//	$pcf->setPayFormulaPolicy( $regular_pay_formula_policy_id );
						//} else {
						//	$pcf->setPayFormulaPolicy( 0 );
						//}
						if ( $pcf->isValid() ) {
							$insert_pay_code_id = $pcf->Save();
							Debug::text(' Created Pay Code ID: '. $insert_pay_code_id, __FILE__, __LINE__, __METHOD__, 9);

							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_over_time_and_meal_policy_and_break_policy_and_paid_absence'] );
							$this->addPayCodeIdToContributingPayCodePolicy( $insert_pay_code_id, $contributing_pay_code_policy_insert_id['regular_time_and_meal_policy_and_break_policy_and_paid_absence'] );

							$bp_obj->setPayCode( $insert_pay_code_id );
							if ( $bp_obj->isValid() ) {
								$bp_obj->Save();
							}
						}
					}
				}
				unset($bplf, $bp_obj, $pcf, $insert_pay_code_id );

				$hplf = TTNew('HolidayPolicyListFactory');
				$hplf->getByCompanyId( $company_obj->getId() );
				if ( $hplf->getRecordCount() > 0 ) {
					foreach( $hplf as $hp_obj ) {
						if ( $hp_obj->getIncludeOverTime() == TRUE AND $hp_obj->getIncludePaidAbsenceTime() == TRUE ) {
							//Meal/Break time is already included in Regular Time by default.
							$hp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
							$hp_obj->setEligibleContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time_and_paid_absence'] );
						} elseif( $hp_obj->getIncludeOverTime() == TRUE ) {
							$hp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time'] );
							$hp_obj->setEligibleContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_over_time'] );
						} elseif( $hp_obj->getIncludePaidAbsenceTime() == TRUE ) {
							$hp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_paid_absence'] );
							$hp_obj->setEligibleContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time_and_paid_absence'] );
						} else {
							$hp_obj->setContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time'] );
							$hp_obj->setEligibleContributingShiftPolicy( $contributing_shift_policy_insert_id['regular_time'] );
						}

						if ( $hp_obj->isValid() ) {
							Debug::text(' Assigned Contributing Shift Policy to Holiday Policy: '. $hp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);
							$hp_obj->Save();
						}
					}
				}
				

				//Remove addUserDate() from cron
				//However calcExceptions calculates dates retroactively using UserDate table, so they will always have 0 hour totals.
				//Can we change this to calculate dates that don't technically exist yet? We just need to loop through the specific dates and recalculate the days.
				//  - Also check to see if the pay period is closed and skip those dates to save processing?
				$cjlf = TTnew('CronJobListFactory');
				$cjlf->getByName('AddUserDate');
				if ( $cjlf->getRecordCount() > 0 ) {
					foreach( $cjlf as $cj_obj ) {
						$cj_obj->setDeleted(TRUE);
						if ( $cj_obj->isValid() ) {
							$cj_obj->Save();
						}
					}
					unset($cjlf, $cj_obj);
				}


				Debug::Arr($pay_code_map, '  Pay Code Map: ', __FILE__, __LINE__, __METHOD__, 9);
				//Migrate saved reports to the new pay codes
				//This handles PayrollExport settings too as those are saved as UserReportData as well.
				if ( is_array($pay_code_map) AND count($pay_code_map) > 0 ) {
					$urdlf = TTnew('UserReportDataListFactory');
					$urdlf->getAPISearchByCompanyIdAndArrayCriteria( $company_obj->getID(), array() );
					Debug::text('  Found Saved Reports: '. $urdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
					if ( $urdlf->getRecordCount() > 0 ) {
						foreach($urdlf as $urd_obj ) {
							Debug::text('    Migrating Saved Report: '. $urd_obj->getName() .' ('. $urd_obj->getID() .') Script: '. $urd_obj->getScript(), __FILE__, __LINE__, __METHOD__, 9);

							$report_data = $urd_obj->getData();
							if ( is_array($report_data) ) {
								//Debug::Arr( $report_data, '  Report Data: ', __FILE__, __LINE__, __METHOD__, 9);

								//Recursively search & replace all keys/values in an array, json encode it to text, search&replace, then decode.
								if ( $urd_obj->getUser() == '' AND isset($report_data['export_columns']) ) {
									Debug::text('  Found Export Setup data, converting separately...', __FILE__, __LINE__, __METHOD__, 9);

									//This is a company wide saved report, like Payroll Export setup data. Need to handle it slightly differently.
									$report_data['export_columns'] = json_decode( preg_replace( array_keys($pay_code_map), str_replace( '_time', '', array_values( $pay_code_map ) ), json_encode( $report_data['export_columns'] ) ), TRUE );
								}

								$tmp_report_data = json_decode( preg_replace( array_keys($pay_code_map), array_values( $pay_code_map ), json_encode( $report_data ) ), TRUE );
								Debug::Arr( $tmp_report_data, '  Converted Report: ', __FILE__, __LINE__, __METHOD__, 9);

								$urd_obj->setData( $tmp_report_data );
								if ( $urd_obj->isValid() ) {
									$urd_obj->Save();
								}
								unset($tmp_report_data, $report_data);
							}
						}
					}
					unset($urdlf, $urd_obj);

					if ( getTTProductEdition() > TT_PRODUCT_COMMUNITY ) {
						//Custom Columns selections and formulas need to be converted as well.
						$rcclf = TTnew('ReportCustomColumnListFactory');
						$rcclf->getByCompanyId( $company_obj->getID() );
						Debug::text('  Found Custom Columns: '. $rcclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
						if ( $rcclf->getRecordCount() > 0 ) {
							foreach($rcclf as $rcc_obj ) {
								Debug::text('    Migrating Custom Column: '. $rcc_obj->getName() .' ('. $rcc_obj->getID() .') Script: '. $rcc_obj->getScript() .' Type: '. $rcc_obj->getType(), __FILE__, __LINE__, __METHOD__, 9);

								$include_columns = $rcc_obj->getIncludeColumns();
								if ( is_array( $include_columns ) AND count($include_columns) > 0 ) {
									$tmp_include_columns = json_decode( preg_replace( array_keys($pay_code_map), array_values( $pay_code_map ), json_encode( $include_columns ) ), TRUE );
									Debug::Arr( $tmp_include_columns, '  Converted Include Columns: ', __FILE__, __LINE__, __METHOD__, 9);

									$rcc_obj->setIncludeColumns( $tmp_include_columns );
								}
								unset($include_columns, $tmp_include_columns);

								$exclude_columns = $rcc_obj->getExcludeColumns();
								if ( is_array( $exclude_columns ) AND count($exclude_columns) > 0 ) {
									$tmp_exclude_columns = json_decode( preg_replace( array_keys($pay_code_map), array_values( $pay_code_map ), json_encode( $exclude_columns ) ), TRUE );
									Debug::Arr( $tmp_exclude_columns, '  Converted Exclude Columns: ', __FILE__, __LINE__, __METHOD__, 9);
									$rcc_obj->setExcludeColumns( $tmp_exclude_columns );
								}
								unset($exclude_columns, $tmp_exclude_columns);

								$formula = $rcc_obj->getFormula();
								if ( $formula != '' ) {
									//Make sure to remove double quotes from pay_code_map as they aren't used in the formula.
									$tmp_formula = json_decode( preg_replace( str_replace('"', '', array_keys($pay_code_map) ), str_replace('"', '', array_values( $pay_code_map ) ), json_encode( $formula ) ), TRUE );
									Debug::Arr( $tmp_formula, '  Converted Formula: ', __FILE__, __LINE__, __METHOD__, 9);
									$rcc_obj->setFormula( $tmp_formula );
								}
								unset($formula, $tmp_formula);

								if ( $rcc_obj->isValid() ) {
									$rcc_obj->Save();
								}
							}
						}
						unset($rcclf, $rcc_obj);
					}
				}

				
				//Convert schedule policies to Include/Exclude format.
				$splf = TTNew('SchedulePolicyListFactory');
				$splf->getByCompanyId( $company_obj->getId() );
				if ( $splf->getRecordCount() > 0 ) {
					foreach( $splf as $sp_obj ) {
						Debug::text(' Converting Schedule Policy ID: '. $sp_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);
						$sp_obj->getIncludeOverTimePolicy( (int)$sp_obj->getColumn('over_time_policy_id') );
						$sp_obj->setMealPolicy( (int)$sp_obj->getColumn('meal_policy_id') );
						if ( $sp_obj->isValid() ) {
							$sp_obj->Save();
						}
					}
				}

				//
				//Delete dummy pay code in 1065A postInstall, as the DB still needs to be fully updated.
				//

				unset($pay_stub_accounts, $pay_code_map, $contributing_pay_code_policy_insert_id, $contributing_shift_policy_insert_id, $company_obj );
				$x++;
			}

		}

		return TRUE;
	}
}
?>
