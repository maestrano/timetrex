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
 * $Revision: 8720 $
 * $Id: EditUserDeduction.php 8720 2012-12-29 01:06:58Z ipso $
 * $Date: 2012-12-28 17:06:58 -0800 (Fri, 28 Dec 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user_tax_deduction','enabled')
		OR !( $permission->Check('user_tax_deduction','edit') OR $permission->Check('user_tax_deduction','edit_own') OR $permission->Check('user_tax_deduction','add') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee Tax / Deduction')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'company_deduction_id',
												'user_id',
												'saved_search_id',
												'id',
												'data'
												) ) );

$udf = TTnew( 'UserDeductionFactory' );
$cdf = TTnew( 'CompanyDeductionFactory' );
$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		//Debug::setVerbosity(11);

		$udf->StartTransaction();
		if ( $company_deduction_id != '' ) {
			//Debug::setVerbosity(11);
			Debug::Text('Mass User Update', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr($data, 'All User Data', __FILE__, __LINE__, __METHOD__,10);

			$redirect = 0;

			if ( isset($data['users']) AND is_array($data['users']) AND count($data['users']) > 0 ) {
				foreach( $data['users'] as  $user_id => $user_data ) {
					Debug::Text('Editing Deductions for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
					//Debug::Arr($user_data, 'Specific User Data', __FILE__, __LINE__, __METHOD__,10);
					if ( isset($user_data['id']) AND $user_data['id'] > 0 ) {
						$udf->setId( $user_data['id'] );
					}
					$udf->setUser( $user_data['user_id'] );

					if ( isset($user_data['user_value1']) ) {
						$udf->setUserValue1( $user_data['user_value1'] );
					}
					if ( isset($user_data['user_value2']) ) {
						$udf->setUserValue2( $user_data['user_value2'] );
					}
					if ( isset($user_data['user_value3']) ) {
						$udf->setUserValue3( $user_data['user_value3'] );
					}
					if ( isset($user_data['user_value4']) ) {
						$udf->setUserValue4( $user_data['user_value4'] );
					}
					if ( isset($user_data['user_value5']) ) {
						$udf->setUserValue5( $user_data['user_value5'] );
					}
					if ( isset($user_data['user_value6']) ) {
						$udf->setUserValue6( $user_data['user_value6'] );
					}
					if ( isset($user_data['user_value7']) ) {
						$udf->setUserValue7( $user_data['user_value7'] );
					}
					if ( isset($user_data['user_value8']) ) {
						$udf->setUserValue8( $user_data['user_value8'] );
					}
					if ( isset($user_data['user_value9']) ) {
						$udf->setUserValue9( $user_data['user_value9'] );
					}
					if ( isset($user_data['user_value10']) ) {
						$udf->setUserValue10( $user_data['user_value10'] );
					}

					if ( $udf->isValid() ) {
						$udf->Save();
					} else {
						$redirect++;
					}
				}

				if ( $redirect == 0 ) {
					$udf->CommitTransaction();

					Redirect::Page( URLBuilder::getURL( NULL, '../company/CompanyDeductionList.php') );

					break;
				}
			}
		} else {
			if ( isset($data['add']) AND $data['add'] == 1 ) {
				Debug::Text('Adding Deductions', __FILE__, __LINE__, __METHOD__,10);
				if ( isset($data['deduction_ids']) AND count($data['deduction_ids']) > 0 ) {
					foreach( $data['deduction_ids'] as $deduction_id ) {
						$udf = TTnew( 'UserDeductionFactory' );
						$udf->setUser( $data['user_id'] );
						$udf->setCompanyDeduction( $deduction_id );
						if ( $udf->isValid() ) {
							$udf->Save();
						}
					}
				}

				$udf->CommitTransaction();

				Redirect::Page( URLBuilder::getURL( array('user_id' => $data['user_id'], 'saved_search_id' => $saved_search_id ), 'UserDeductionList.php') );
			} else {
				Debug::Text('Editing Deductions', __FILE__, __LINE__, __METHOD__,10);
				$udf->setId( $data['id'] );
				$udf->setUser( $data['user_id'] );

				if ( isset($data['user_value1']) ) {
					$udf->setUserValue1( $data['user_value1'] );
				}
				if ( isset($data['user_value2']) ) {
					$udf->setUserValue2( $data['user_value2'] );
				}
				if ( isset($data['user_value3']) ) {
					$udf->setUserValue3( $data['user_value3'] );
				}
				if ( isset($data['user_value4']) ) {
					$udf->setUserValue4( $data['user_value4'] );
				}
				if ( isset($data['user_value5']) ) {
					$udf->setUserValue5( $data['user_value5'] );
				}
				if ( isset($data['user_value6']) ) {
					$udf->setUserValue6( $data['user_value6'] );
				}
				if ( isset($data['user_value7']) ) {
					$udf->setUserValue7( $data['user_value7'] );
				}
				if ( isset($data['user_value8']) ) {
					$udf->setUserValue8( $data['user_value8'] );
				}
				if ( isset($data['user_value9']) ) {
					$udf->setUserValue9( $data['user_value9'] );
				}
				if ( isset($data['user_value10']) ) {
					$udf->setUserValue10( $data['user_value10'] );
				}

				if ( $udf->isValid() ) {
					$udf->Save();

					$udf->CommitTransaction();

					Redirect::Page( URLBuilder::getURL( array('user_id' => $data['user_id'], 'saved_search_id' => $saved_search_id ), 'UserDeductionList.php') );

					break;
				}
			}
		}
		$udf->FailTransaction();
	default:
		$cf = TTnew( 'CompanyFactory' );

		if ( isset($company_deduction_id) AND $company_deduction_id != '' ) {
			Debug::Text('Mass User Deduction Edit!', __FILE__, __LINE__, __METHOD__,10);

			//Get all employees assigned to this company deduction.
			$cdlf = TTnew( 'CompanyDeductionListFactory' );
			$cdlf->getByCompanyIdAndId( $current_company->getId(), $company_deduction_id );
			Debug::Text('Company Deduction Records: '. $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			if ( $cdlf->getRecordCount() > 0 ) {

				foreach( $cdlf as $cd_obj ) {
					$province_options = $cf->getOptions('province', $cd_obj->getCountry() );
					$tmp_district_options = $cf->getOptions('district', $cd_obj->getCountry() );
					$district_options = array();
					if ( isset($tmp_district_options[$cd_obj->getProvince()]) ) {
						$district_options = $tmp_district_options[$cd_obj->getProvince()];
					}
					unset($tmp_district_options);

					if ( !isset($data['users']) ) {
						$data['users'] = NULL;
					}

					$data = array(
									'id' => $cd_obj->getId(),
									'company_id' => $cd_obj->getCompany(),

									'status_id' => $cd_obj->getStatus(),
									'status' => Option::getByKey( $cd_obj->getStatus(), $cd_obj->getOptions('status') ),

									'type_id' => $cd_obj->getType(),
									'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),

									'name' => $cd_obj->getName(),

									'combined_calculation_id' => $cd_obj->getCombinedCalculationId(),
									'calculation_id' => $cd_obj->getCalculation(),
									'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),

									'country_id' => $cd_obj->getCountry(),
									'country' => Option::getByKey( $cd_obj->getCountry(), $cd_obj->getOptions('country') ),

									'province_id' => $cd_obj->getProvince(),
									'province' => Option::getByKey( $cd_obj->getProvince(), $province_options ),

									'district_id' => $cd_obj->getDistrict(),
									'district' => Option::getByKey( $cd_obj->getDistrict(), $district_options ),

									'company_value1' => $cd_obj->getCompanyValue1(),
									'company_value2' => $cd_obj->getCompanyValue2(),

									'default_user_value1' => $cd_obj->getUserValue1(),
									'default_user_value2' => $cd_obj->getUserValue2(),
									'default_user_value3' => $cd_obj->getUserValue3(),
									'default_user_value4' => $cd_obj->getUserValue4(),
									'default_user_value5' => $cd_obj->getUserValue5(),
									'default_user_value6' => $cd_obj->getUserValue6(),
									'default_user_value7' => $cd_obj->getUserValue7(),
									'default_user_value8' => $cd_obj->getUserValue8(),
									'default_user_value9' => $cd_obj->getUserValue9(),
									'default_user_value10' => $cd_obj->getUserValue10(),

									'users' => $data['users'],
								);

					if ($action != 'submit' ) {
						$user_ids = $cd_obj->getUser();

						Debug::Text('Assigned Users: '. count($user_ids), __FILE__, __LINE__, __METHOD__,10);
						if ( is_array($user_ids) AND count($user_ids) > 0 ) {
							//Get User deduction data for each user.
							$udlf = TTnew( 'UserDeductionListFactory' );
							$udlf->getByUserIdAndCompanyDeductionId( $user_ids, $cd_obj->getId() );
							if ( $udlf->getRecordCount() > 0 ) {
								//Get deduction data for each user.
								//When ever we add/subtract users to/from a company dedution, the user deduction rows are handled then.
								//So we don't need to worry about new users at all here.
								foreach( $udlf as $ud_obj ) {
									//Use Company Deduction values as default.
									if ( $ud_obj->getUserValue1() === FALSE ) {
										$user_value1 = $cd_obj->getUserValue1();
									} else {
										$user_value1 = $ud_obj->getUserValue1();
									}
									if ( $ud_obj->getUserValue2() === FALSE ) {
										$user_value2 = $cd_obj->getUserValue2();
									} else {
										$user_value2 = $ud_obj->getUserValue2();
									}
									if ( $ud_obj->getUserValue3() === FALSE ) {
										$user_value3 = $cd_obj->getUserValue3();
									} else {
										$user_value3 = $ud_obj->getUserValue3();
									}
									if ( $ud_obj->getUserValue4() === FALSE ) {
										$user_value4 = $cd_obj->getUserValue4();
									} else {
										$user_value4 = $ud_obj->getUserValue4();
									}
									if ( $ud_obj->getUserValue5() === FALSE ) {
										$user_value5 = $cd_obj->getUserValue5();
									} else {
										$user_value5 = $ud_obj->getUserValue5();
									}

									$data['users'][$ud_obj->getUser()] = array(
														'id' => $ud_obj->getId(),
														'user_id' => $ud_obj->getUser(),
														'user_full_name' => $ud_obj->getUserObject()->getFullName(TRUE),

														'user_value1' => $user_value1,
														'user_value2' => $user_value2,
														'user_value3' => $user_value3,
														'user_value4' => $user_value4,
														'user_value5' => $user_value5,
														'user_value6' => $ud_obj->getUserValue6(),
														'user_value7' => $ud_obj->getUserValue7(),
														'user_value8' => $ud_obj->getUserValue8(),
														'user_value9' => $ud_obj->getUserValue9(),
														'user_value10' => $ud_obj->getUserValue10(),
														);
								}
							}
						}
					}
				}
			}
			//print_r($data);
		} else {
			if ( isset($id) AND $action != 'submit'  ) {
				Debug::Text('ID Passed', __FILE__, __LINE__, __METHOD__,10);
				BreadCrumb::setCrumb($title);

				//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
				$hlf = TTnew( 'HierarchyListFactory' );
				$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );

				$udlf = TTnew( 'UserDeductionListFactory' );
				$udlf->getByCompanyIdAndId( $current_company->getID(), $id );

				foreach ($udlf as $ud_obj) {

					$user_obj = $ulf->getByIdAndCompanyId( $ud_obj->getUser(), $current_company->getId() )->getCurrent();
					if ( is_object($user_obj) ) {
						$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getID() );
						$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

						if ( $permission->Check('user_tax_deduction','edit')
								OR ( $permission->Check('user_tax_deduction','edit_own') AND $is_owner === TRUE )
								OR ( $permission->Check('user_tax_deduction','edit_child') AND $is_child === TRUE ) ) {

							//Get Company Deduction info
							$cd_obj = $ud_obj->getCompanyDeductionObject();

							$province_options = $cf->getOptions('province', $cd_obj->getCountry() );
							$tmp_district_options = $cf->getOptions('district', $cd_obj->getCountry() );
							$district_options = array();
							if ( isset($tmp_district_options[$cd_obj->getProvince()]) ) {
								$district_options = $tmp_district_options[$cd_obj->getProvince()];
							}
							unset($tmp_district_options);

							//Use Company Deduction values as default.
							if ( $ud_obj->getUserValue1() === FALSE ) {
								$user_value1 = $cd_obj->getUserValue1();
							} else {
								$user_value1 = $ud_obj->getUserValue1();
							}
							if ( $ud_obj->getUserValue2() === FALSE ) {
								$user_value2 = $cd_obj->getUserValue2();
							} else {
								$user_value2 = $ud_obj->getUserValue2();
							}
							if ( $ud_obj->getUserValue3() === FALSE ) {
								$user_value3 = $cd_obj->getUserValue3();
							} else {
								$user_value3 = $ud_obj->getUserValue3();
							}
							if ( $ud_obj->getUserValue4() === FALSE ) {
								$user_value4 = $cd_obj->getUserValue4();
							} else {
								$user_value4 = $ud_obj->getUserValue4();
							}
							if ( $ud_obj->getUserValue5() === FALSE ) {
								$user_value5 = $cd_obj->getUserValue5();
							} else {
								$user_value5 = $ud_obj->getUserValue5();
							}

							$data = array(
												'id' => $ud_obj->getId(),
												'user_id' => $ud_obj->getUser(),
												'company_id' => $cd_obj->getCompany(),

												'status_id' => $cd_obj->getStatus(),
												'status' => Option::getByKey( $cd_obj->getStatus(), $cd_obj->getOptions('status') ),

												'type_id' => $cd_obj->getType(),
												'type' => Option::getByKey( $cd_obj->getType(), $cd_obj->getOptions('type') ),

												'name' => $cd_obj->getName(),

												'combined_calculation_id' => $cd_obj->getCombinedCalculationId(),
												'calculation_id' => $cd_obj->getCalculation(),
												'calculation' => Option::getByKey( $cd_obj->getCalculation(), $cd_obj->getOptions('calculation') ),

												'country_id' => $cd_obj->getCountry(),
												'country' => Option::getByKey( $cd_obj->getCountry(), $cd_obj->getOptions('country') ),

												'province_id' => $cd_obj->getProvince(),
												'province' => Option::getByKey( $cd_obj->getProvince(), $province_options ),

												'district_id' => $cd_obj->getDistrict(),
												'district' => Option::getByKey( $cd_obj->getDistrict(), $district_options ),

												'company_value1' => $cd_obj->getCompanyValue1(),
												'company_value2' => $cd_obj->getCompanyValue2(),

												'user_value1' => $user_value1,
												'user_value2' => $user_value2,
												'user_value3' => $user_value3,
												'user_value4' => $user_value4,
												'user_value5' => $user_value5,
												'user_value6' => $ud_obj->getUserValue6(),
												'user_value7' => $ud_obj->getUserValue7(),
												'user_value8' => $ud_obj->getUserValue8(),
												'user_value9' => $ud_obj->getUserValue9(),
												'user_value10' => $ud_obj->getUserValue10(),

												'default_user_value1' => $cd_obj->getUserValue1(),
												'default_user_value2' => $cd_obj->getUserValue2(),
												'default_user_value3' => $cd_obj->getUserValue3(),
												'default_user_value4' => $cd_obj->getUserValue4(),
												'default_user_value5' => $cd_obj->getUserValue5(),
												'default_user_value6' => $cd_obj->getUserValue6(),
												'default_user_value7' => $cd_obj->getUserValue7(),
												'default_user_value8' => $cd_obj->getUserValue8(),
												'default_user_value9' => $cd_obj->getUserValue9(),
												'default_user_value10' => $cd_obj->getUserValue10(),

												'created_date' => $ud_obj->getCreatedDate(),
												'created_by' => $ud_obj->getCreatedBy(),
												'updated_date' => $ud_obj->getUpdatedDate(),
												'updated_by' => $ud_obj->getUpdatedBy(),
												'deleted_date' => $ud_obj->getDeletedDate(),
												'deleted_by' => $ud_obj->getDeletedBy()
								);
						} else {
							$permission->Redirect( FALSE ); //Redirect
							exit;
						}
					}
				}
			} else {
				Debug::Text('Adding... ', __FILE__, __LINE__, __METHOD__,10);
				//Adding User Deductions...
				$data['add'] = 1;
				$data['user_id'] = $user_id;

				//Get all Company Deductions for drop down box.
				$cdlf = TTnew( 'CompanyDeductionListFactory' );
				$data['deduction_options'] = $cdlf->getByCompanyIdAndStatusIdArray( $current_company->getId(), 10, FALSE);

				$udlf = TTnew( 'UserDeductionListFactory' );
				$udlf->getByCompanyIdAndUserId( $current_company->getId(), $user_id );
				if ($udlf->getRecordCount() > 0 ) {
					//Remove deductions from select box that are already assigned to user.
					$deduction_ids = array_keys($data['deduction_options']);
					foreach( $udlf as $ud_obj) {
						if ( in_array( $ud_obj->getCompanyDeduction(), $deduction_ids ) ) {
							unset($data['deduction_options'][$ud_obj->getCompanyDeduction()]);
						}
					}
				}
			}

			//Get user full name
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByIdAndCompanyId( $data['user_id'], $current_company->getId() );
			if ( $ulf->getRecordCount() > 0 ) {
				$data['user_full_name'] = $ulf->getCurrent()->getFullName();
			}
		}

		//Select box options;
		$data['us_medicare_filing_status_options'] = $cdf->getOptions('us_medicare_filing_status');
		$data['us_eic_filing_status_options'] = $cdf->getOptions('us_eic_filing_status');
		$data['federal_filing_status_options'] = $cdf->getOptions('federal_filing_status');
		$data['state_filing_status_options'] = $cdf->getOptions('state_filing_status');
		$data['state_ga_filing_status_options'] = $cdf->getOptions('state_ga_filing_status');
		$data['state_nj_filing_status_options'] = $cdf->getOptions('state_nj_filing_status');
		$data['state_nc_filing_status_options'] = $cdf->getOptions('state_nc_filing_status');
		$data['state_ma_filing_status_options'] = $cdf->getOptions('state_ma_filing_status');
		$data['state_al_filing_status_options'] = $cdf->getOptions('state_al_filing_status');
		$data['state_ct_filing_status_options'] = $cdf->getOptions('state_ct_filing_status');
		$data['state_wv_filing_status_options'] = $cdf->getOptions('state_wv_filing_status');
		$data['state_me_filing_status_options'] = $cdf->getOptions('state_me_filing_status');
		$data['state_de_filing_status_options'] = $cdf->getOptions('state_de_filing_status');
		$data['state_dc_filing_status_options'] = $cdf->getOptions('state_dc_filing_status');
		$data['state_la_filing_status_options'] = $cdf->getOptions('state_la_filing_status');

		$data['js_arrays'] = $cdf->getJavaScriptArrays();

		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('saved_search_id', $saved_search_id);

		break;
}

$smarty->assign_by_ref('udf', $udf);
$smarty->assign_by_ref('company_deduction_id', $company_deduction_id);

$smarty->display('users/EditUserDeduction.tpl');
?>
