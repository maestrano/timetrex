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
 * $Revision: 5453 $
 * $Id: EditUser.php 5453 2011-11-03 20:30:28Z ipso $
 * $Date: 2011-11-03 13:30:28 -0700 (Thu, 03 Nov 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') OR $permission->Check('user','edit_own') OR $permission->Check('user','edit_child') OR $permission->Check('user','add')) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Employee')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_data',
												'saved_search_id',
												'company_id',
												'incomplete',
												'data_saved'
												) ) );

/*
Can't switch to free form dates for selecting Birth dates because they come before 1970!! :(
Strtotime sucks too much.
*/

if ( isset($user_data) ) {
    if ( isset($user_data['hire_date']) AND $user_data['hire_date'] != '') {
        $user_data['hire_date'] = TTDate::parseDateTime($user_data['hire_date']);
    }
    if ( isset($user_data['termination_date']) AND $user_data['termination_date'] != '') {
        Debug::Text('Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
        $user_data['termination_date'] = TTDate::parseDateTime($user_data['termination_date']);
    } else {
        Debug::Text('NOT Running strtotime on Termination date', __FILE__, __LINE__, __METHOD__,10);
    }

	$user_data['birth_date'] = TTDate::getTimeStampFromSmarty('birth_', $user_data);
}

$ulf = TTnew( 'UserListFactory' );
$uf = TTnew( 'UserFactory' );

$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId( $current_company->getId(), $current_user->getId() );
//Include current user in list.
if ( $permission->Check('user','edit_own') ) {
	$permission_children_ids[] = $current_user->getId();
}
//Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

//Debug::Text('aCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);

$action = Misc::findSubmitButton();
switch ($action) {
	case 'login':
		if ( $permission->Check('company','view') AND $permission->Check('company','login_other_user') ) {

			Debug::Text('Login as different user: '. $id, __FILE__, __LINE__, __METHOD__,10);
			//Get record for other user so we can check to make sure its not a primary company.
			$ulf = TTNew('UserListFactory');
			$ulf->getById( $id );
			if ( $ulf->getRecordCount() > 0 ) {
				if ( isset($config_vars['other']['primary_company_id']) AND $config_vars['other']['primary_company_id'] != $ulf->getCurrent()->getCompany() ) {
					$authentication->changeObject( $id );

					TTLog::addEntry( $current_user->getID(), 'Login',  TTi18n::getText('Switch User').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('UserID').': '. $id, $current_user->getId(), 'authentication');

					Redirect::Page( URLBuilder::getURL( NULL, '../index.php') );
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}
			}
		} else {
			$permission->Redirect( FALSE ); //Redirect
		}
		break;
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		unset($id); //Do this so it doesn't reload the data from the DB.

		//Additional permission checks.
		if ( $permission->Check('company','view') ) {
			$ulf->getById( $user_data['id'] );
		} else {
			$ulf->getByIdAndCompanyId( $user_data['id'], $current_company->getId() );
		}

		if ( $ulf->getRecordCount() > 0 ) {
			$user = $ulf->getCurrent();

			$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getID() );
			$is_child = $permission->isChild( $user->getId(), $permission_children_ids );
			if ( $permission->Check('user','edit')
					OR ( $permission->Check('user','edit_child') AND $is_child === TRUE )
					OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE ) ) {
					// Security measure.
					if ( !empty($user_data['id']) ) {
						if ( $permission->Check('company','view') ) {
                            $uf = $ulf->getById( $user_data['id'] )->getCurrent();
						} else {
                            $uf = $ulf->getByIdAndCompanyId($user_data['id'], $current_company->getId() )->getCurrent();
						}
					}
			} else {
				$permission->Redirect( FALSE ); //Redirect
				exit;
			}
			unset($user);
		}

		if ( isset( $user_data['company_id'] ) ) {
			if ( $permission->Check('company','view') ) {
				$uf->setCompany( $user_data['company_id'] );
			} else {
				$uf->setCompany( $current_company->getId() );
			}
		} else {
			$uf->setCompany( $current_company->getId() );
		}

        //Get New Hire Defaults.
        $udlf = TTnew( 'UserDefaultListFactory' );
        $udlf->getByCompanyId( $uf->getCompany() );
        if ( $udlf->getRecordCount() > 0 ) {
            Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
            $udf_obj = $udlf->getCurrent();
        }

		if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			if ( isset( $user_data['status'] ) ) {
				$uf->setStatus( $user_data['status'] );
			}

			if ( isset( $user_data['user_name'] ) ) {
				$uf->setUserName( $user_data['user_name'] );
			}

			//Phone ID is optional now.
			if ( isset( $user_data['phone_id'] ) ) {
				$uf->setPhoneId( $user_data['phone_id'] );
			}
		}

		if ( DEMO_MODE == FALSE OR $uf->isNew() == TRUE ) {
			if ( !empty($user_data['password']) OR !empty($user_data['password2']) ) {
				if ( $user_data['password'] == $user_data['password2'] ) {
					$uf->setPassword($user_data['password']);
				} else {
					$uf->Validator->isTrue(	'password',
											FALSE,
											TTi18n::gettext('Passwords don\'t match') );
				}
			}

			if ( isset( $user_data['phone_password'] ) ) {
				$uf->setPhonePassword($user_data['phone_password']);
			}
		}

		if ( $user_data['id'] != $current_user->getID()
				AND $permission->Check('user','edit_advanced') ) {
			//Don't force them to update all fields.
			//Unless they are editing their OWN user.
			$uf->setFirstName($user_data['first_name']);

			if ( isset($user_data['middle_name']) ) {
				$uf->setMiddleName($user_data['middle_name']);
			}

			$uf->setLastName($user_data['last_name']);

			if ( isset($user_data['second_last_name']) ){
				$uf->setSecondLastName($user_data['second_last_name']);
			}

			if ( !empty($user_data['sex']) ) {
				$uf->setSex($user_data['sex']);
			}

			if ( isset($user_data['address1']) ) {
				$uf->setAddress1($user_data['address1']);
			}

			if ( isset($user_data['address2']) ) {
				$uf->setAddress2($user_data['address2']);
			}

			if ( isset($user_data['city']) ) {
				$uf->setCity($user_data['city']);
			}

			if ( isset($user_data['country']) ) {
				$uf->setCountry($user_data['country']);
			}

			if ( isset($user_data['province']) ) {
				$uf->setProvince($user_data['province']);
			}

			if ( isset($user_data['postal_code']) ) {
				$uf->setPostalCode($user_data['postal_code']);
			}

			if ( isset($user_data['work_phone']) ) {
				$uf->setWorkPhone($user_data['work_phone']);
			}

			if ( isset($user_data['work_phone_ext']) ) {
				$uf->setWorkPhoneExt($user_data['work_phone_ext']);
			}

			if ( isset($user_data['home_phone']) ) {
				$uf->setHomePhone($user_data['home_phone']);
			}

			if ( isset($user_data['mobile_phone']) ) {
				$uf->setMobilePhone($user_data['mobile_phone']);
			}

			if ( isset($user_data['fax_phone']) ) {
				$uf->setFaxPhone($user_data['fax_phone']);
			}

			if ( isset($user_data['home_email']) ) {
				$uf->setHomeEmail($user_data['home_email']);
			}

			if ( isset($user_data['work_email']) ) {
				$uf->setWorkEmail($user_data['work_email']);
			}

			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}

			$uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
		} else {
			//Force them to update all fields.

			$uf->setFirstName($user_data['first_name']);
			$uf->setMiddleName($user_data['middle_name']);
			$uf->setLastName($user_data['last_name']);
			if ( isset($user_data['second_last_name']) ) {
				$uf->setSecondLastName($user_data['second_last_name']);
			}
			$uf->setSex($user_data['sex']);
			$uf->setAddress1($user_data['address1']);
			$uf->setAddress2($user_data['address2']);
			$uf->setCity($user_data['city']);

			if ( isset($user_data['country']) ) {
				$uf->setCountry($user_data['country']);
			}

			if ( isset($user_data['province']) ) {
				$uf->setProvince($user_data['province']);
			}

			$uf->setPostalCode($user_data['postal_code']);
			$uf->setWorkPhone($user_data['work_phone']);
			$uf->setWorkPhoneExt($user_data['work_phone_ext']);
			$uf->setHomePhone($user_data['home_phone']);
			$uf->setMobilePhone($user_data['mobile_phone']);
			$uf->setFaxPhone($user_data['fax_phone']);
			$uf->setHomeEmail($user_data['home_email']);
			$uf->setWorkEmail($user_data['work_email']);
			if ( isset($user_data['sin']) ) {
				$uf->setSIN($user_data['sin']);
			}

			$uf->setBirthDate( TTDate::getTimeStampFromSmarty('birth_', $user_data) );
		}

		if ( DEMO_MODE == FALSE
			AND isset($user_data['permission_control_id'])
			AND $uf->getPermissionLevel() <= $permission->getLevel()
			AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') ) ) {
			$uf->setPermissionControl( $user_data['permission_control_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
			$uf->setPermissionControl( $udf_obj->getPermissionControl() );
		}

		if ( isset($user_data['pay_period_schedule_id']) AND ( $permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule') ) ) {
			$uf->setPayPeriodSchedule( $user_data['pay_period_schedule_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
            $uf->setPayPeriodSchedule( $udf_obj->getPayPeriodSchedule() );
        }

		if ( isset($user_data['policy_group_id']) AND ( $permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group') ) ) {
			$uf->setPolicyGroup( $user_data['policy_group_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE) {
            $uf->setPolicyGroup( $udf_obj->getPolicyGroup() );
        }

		if ( isset($user_data['hierarchy_control']) AND ( $permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy') ) ) {
			$uf->setHierarchyControl( $user_data['hierarchy_control'] );
		}

		if ( isset($user_data['currency_id']) ) {
			$uf->setCurrency( $user_data['currency_id'] );
		} elseif ( isset($udf_obj) AND is_object($udf_obj) AND $uf->isNew() == TRUE ) {
            $uf->setCurrency( $udf_obj->getCurrency() );
        }

		if ( isset($user_data['hire_date']) ) {
			$uf->setHireDate( $user_data['hire_date'] );
		}
		if ( isset($user_data['termination_date']) ) {
			$uf->setTerminationDate( $user_data['termination_date'] );
		}
		if ( isset($user_data['employee_number']) ) {
			$uf->setEmployeeNumber( $user_data['employee_number'] );
		}
		if ( isset($user_data['default_branch_id']) ) {
			$uf->setDefaultBranch( $user_data['default_branch_id'] );
		}
		if ( isset($user_data['default_department_id']) ) {
			$uf->setDefaultDepartment( $user_data['default_department_id'] );
		}
		if ( isset($user_data['group_id']) ) {
			$uf->setGroup( $user_data['group_id'] );
		}
		if ( isset($user_data['title_id']) ) {
			$uf->setTitle($user_data['title_id']);
		}
		if ( isset($user_data['ibutton_id']) ) {
			$uf->setIButtonId($user_data['ibutton_id']);
		}
		if ( isset($user_data['other_id1']) ) {
			$uf->setOtherID1( $user_data['other_id1'] );
		}
		if ( isset($user_data['other_id2']) ) {
			$uf->setOtherID2( $user_data['other_id2'] );
		}
		if ( isset($user_data['other_id3']) ) {
			$uf->setOtherID3( $user_data['other_id3'] );
		}
		if ( isset($user_data['other_id4']) ) {
			$uf->setOtherID4( $user_data['other_id4'] );
		}
		if ( isset($user_data['other_id5']) ) {
			$uf->setOtherID5( $user_data['other_id5'] );
		}

		if ( isset($user_data['note']) ) {
			$uf->setNote( $user_data['note'] );
		}

		if ( $uf->isValid() ) {
			$uf->Save(FALSE);
			$user_data['id'] = $uf->getId();
			Debug::Text('Inserted ID: '. $user_data['id'], __FILE__, __LINE__, __METHOD__,10);

			Redirect::Page( URLBuilder::getURL( array('id' => $user_data['id'], 'saved_search_id' => $saved_search_id, 'company_id' => $company_id, 'data_saved' => TRUE), 'EditUser.php') );

			break;
		}
	default:
		//Debug::Text('bCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);
		if ( $permission->Check('company','view') == FALSE OR $company_id == '' OR $company_id == '-1' ) {
			$company_id = $current_company->getId();
		}
		//Debug::Text('cCompany ID: '. $company_id, __FILE__, __LINE__, __METHOD__,10);

		if ( isset($id) AND $action !== 'submit' ) {
			//Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			BreadCrumb::setCrumb($title);

			if ( $permission->Check('company','view') ) {
				$ulf->getById( $id )->getCurrent();
			} else {
				//$ulf->GetByIdAndCompanyId( $id, $company_id )->getCurrent();
				$ulf->getByIdAndCompanyId($id, $company_id );
			}

			foreach ($ulf as $user) {
				//Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);
				$is_owner = $permission->isOwner( $user->getCreatedBy(), $user->getId() );
				$is_child = $permission->isChild( $user->getId(), $permission_children_ids );
				if ( $permission->Check('user','edit')
						OR ( $permission->Check('user','edit_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user','edit_child') AND $is_child === TRUE ) ) {

                    $user_title = NULL;
					if ( $user->getTitle() != 0 AND is_object( $user->getTitleObject() ) ) {
						$user_title = $user->getTitleObject()->getName();
					}
					Debug::Text('Title: '. $user_title , __FILE__, __LINE__, __METHOD__,10);

					if ( $permission->Check('user','view_sin') == TRUE ) {
						$sin_number = $user->getSIN();
					} else {
						$sin_number = $user->getSecureSIN();
					}

					$user_data = array(
										'id' => $user->getId(),
										'company_id' => $user->getCompany(),
										'status' => $user->getStatus(),
										'user_name' => $user->getUserName(),
										'title_id' => $user->getTitle(),
										'title' => $user_title,
	//									'password' => $user->getPassword(),
										'phone_id' => $user->getPhoneId(),
										'phone_password' => $user->getPhonePassword(),
										'ibutton_id' => $user->getIbuttonId(),
										'employee_number' => $user->getEmployeeNumber(),
										'first_name' => $user->getFirstName(),
										'middle_name' => $user->getMiddleName(),
										'last_name' => $user->getLastName(),
										'second_last_name' => $user->getSecondLastName(),
										'sex' => $user->getSex(),
										'address1' => $user->getAddress1(),
										'address2' => $user->getAddress2(),
										'city' => $user->getCity(),
										'province' => $user->getProvince(),
										'country' => $user->getCountry(),
										'postal_code' => $user->getPostalCode(),
										'work_phone' => $user->getWorkPhone(),
										'work_phone_ext' => $user->getWorkPhoneExt(),
										'home_phone' => $user->getHomePhone(),
										'mobile_phone' => $user->getMobilePhone(),
										'fax_phone' => $user->getFaxPhone(),
										'home_email' => $user->getHomeEmail(),
										'work_email' => $user->getWorkEmail(),
										'birth_date' => $user->getBirthDate(),
										'hire_date' => $user->getHireDate(),
										'termination_date' => $user->getTerminationDate(),
										'sin' => $sin_number,

										'other_id1' => $user->getOtherID1(),
										'other_id2' => $user->getOtherID2(),
										'other_id3' => $user->getOtherID3(),
										'other_id4' => $user->getOtherID4(),
										'other_id5' => $user->getOtherID5(),

										'note' => $user->getNote(),

										'default_branch_id' => $user->getDefaultBranch(),
										'default_department_id' => $user->getDefaultDepartment(),
										'group_id' => $user->getGroup(),
                                        'currency_id' => $user->getCurrency(),
										'permission_level' => $user->getPermissionLevel(),
										'is_owner' => $is_owner,
										'is_child' => $is_child,
										'created_date' => $user->getCreatedDate(),
										'created_by' => $user->getCreatedBy(),
										'updated_date' => $user->getUpdatedDate(),
										'updated_by' => $user->getUpdatedBy(),
										'deleted_date' => $user->getDeletedDate(),
										'deleted_by' => $user->getDeletedBy()
									);

					$pclfb = TTnew( 'PermissionControlListFactory' );
					$pclfb->getByCompanyIdAndUserId( $user->getCompany(), $id );
					if ( $pclfb->getRecordCount() > 0 ) {
						$user_data['permission_control_id'] = $pclfb->getCurrent()->getId();
					}

					$ppslfb = TTnew( 'PayPeriodScheduleListFactory' );
					$ppslfb->getByUserId( $id );
					if ( $ppslfb->getRecordCount() > 0 ) {
						$user_data['pay_period_schedule_id'] = $ppslfb->getCurrent()->getId();
					}

					$pglf = TTnew( 'PolicyGroupListFactory' );
					$pglf->getByUserIds( $id );
					if ( $pglf->getRecordCount() > 0 ) {
						$user_data['policy_group_id'] = $pglf->getCurrent()->getId();
					}

					$hclf = TTnew( 'HierarchyControlListFactory' );
					$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $user->getCompany(), $user->getID() );
					$user_data['hierarchy_control'] = $hclf->getArrayByListFactory( $hclf, FALSE, TRUE, FALSE );
					unset($hclf);
				} else {
					$permission->Redirect( FALSE ); //Redirect
				}

			}
		} elseif ( $action == 'submit') {
			Debug::Text('ID Not set', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($user_obj ) ) {
				Debug::Text('User Object set', __FILE__, __LINE__, __METHOD__,10);

				$user_data['is_owner'] = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() );
				$user_data['is_owner'] = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				//If user doesn't have permissions to edit these values, we have to pull them
				//out of the DB and update the array.
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $user_obj->getCompany();
				}

				if ( !isset( $user_data['status'] ) ) {
					$user_data['status'] = $user_obj->getStatus();
				}

				if ( !isset( $user_data['user_name'] ) ) {
					$user_data['user_name'] = $user_obj->getUserName();
				}

				if ( !isset( $user_data['phone_id'] ) ) {
					$user_data['phone_id'] = $user_obj->getPhoneId();
				}

				if ( !isset( $user_data['hire_date'] ) ) {
					$user_data['hire_date'] = $user_obj->getHireDate();
				}

				if ( !isset( $user_data['birth_date'] ) ) {
					$user_data['birth_date'] = $user_obj->getBirthDate();
				}

				if ( !isset( $user_data['province'] ) ) {
					$user_data['province'] = $user_obj->getProvince();
				}

				if ( !isset( $user_data['country'] ) ) {
					$user_data['country'] = $user_obj->getCountry();
				}

			} else {
				Debug::Text('User Object NOT set', __FILE__, __LINE__, __METHOD__,10);
				if ( !isset( $user_data['company_id'] ) ) {
					$user_data['company_id'] = $company_id;
				}

			}


		} else {
			Debug::Text('Adding new User.', __FILE__, __LINE__, __METHOD__,10);

			//Get New Hire Defaults.
			$udlf = TTnew( 'UserDefaultListFactory' );
			$udlf->getByCompanyId( $company_id );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
				$udf_obj = $udlf->getCurrent();

				$user_data = array(
								'company_id' => $company_id,
								'title_id' => $udf_obj->getTitle(),
								'employee_number' => $udf_obj->getEmployeeNumber(),
								'city' => $udf_obj->getCity(),
								'province' => $udf_obj->getProvince(),
								'country' => $udf_obj->getCountry(),
								'work_phone' => $udf_obj->getWorkPhone(),
								'work_phone_ext' => $udf_obj->getWorkPhoneExt(),
								'work_email' => $udf_obj->getWorkEmail(),
								'hire_date' => $udf_obj->getHireDate(),
								'default_branch_id' => $udf_obj->getDefaultBranch(),
								'default_department_id' => $udf_obj->getDefaultDepartment(),
								'permission_control_id' => $udf_obj->getPermissionControl(),
								'pay_period_schedule_id' => $udf_obj->getPayPeriodSchedule(),
								'policy_group_id' => $udf_obj->getPolicyGroup(),
                                'currency_id' => $udf_obj->getCurrency(),
							);
			}

			if ( !isset($user_obj ) ) {
				$user_obj = $ulf->getByIdAndCompanyId($current_user->getId(), $company_id )->getCurrent();
			}

			if ( !isset( $user_data['company_id'] ) ) {
				$user_data['company_id'] = $company_id;
			}

			if ( !isset( $user_data['country'] ) ) {
				$user_data['country'] = 'CA';
			}

			$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
			if ( $ulf->getRecordCount() > 0 ) {
				Debug::Text('Highest Employee Number: '. $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,10);
				if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == TRUE ) {
					$user_data['next_available_employee_number'] = $ulf->getCurrent()->getEmployeeNumber()+1;
				} else {
					Debug::Text('Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__,10);
					$user_data['next_available_employee_number'] = NULL;
				}
			} else {
				$user_data['next_available_employee_number'] = 1;
			}

			if ( !isset($user_data['hire_date']) OR $user_data['hire_date'] == '' ) {
				$user_data['hire_date'] = time();
			}
		}
		//var_dump($user_data);

		//Select box options;
		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $company_id );

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $company_id );

		$culf = TTnew( 'CurrencyListFactory' );
        $culf->getByCompanyId( $company_id );
		$currency_options = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		$hotf = TTnew( 'HierarchyObjectTypeFactory' );
		$hierarchy_object_type_options = $hotf->getOptions('object_type');

		$hclf = TTnew( 'HierarchyControlListFactory' );
		$hclf->getObjectTypeAppendedListByCompanyID( $company_id );
		$hierarchy_control_options = $hclf->getArrayByListFactory( $hclf, TRUE, TRUE );

		//Select box options;
		$user_data['branch_options'] = $branch_options;
		$user_data['department_options'] = $department_options;
        $user_data['currency_options'] = $currency_options;

		$user_data['sex_options'] = $uf->getOptions('sex');
		$user_data['status_options'] = $uf->getOptions('status');

		$clf = TTnew( 'CompanyListFactory' );
		$user_data['country_options'] = $clf->getOptions('country');
		$user_data['province_options'] = $clf->getOptions('province', $user_data['country'] );

		$utlf = TTnew( 'UserTitleListFactory' );
		$user_titles = $utlf->getByCompanyIdArray( $company_id );
		$user_data['title_options'] = $user_titles;

		//Get Permission Groups
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getByCompanyIdAndLevel( $company_id, $permission->getLevel() );
		$user_data['permission_control_options'] = $pclf->getArrayByListFactory( $pclf, FALSE );

		//Get pay period schedules
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$pay_period_schedules = $ppslf->getByCompanyIDArray( $company_id );
		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

		$pglf = TTnew( 'PolicyGroupListFactory' );
		$policy_groups = $pglf->getByCompanyIDArray( $company_id );
		$user_data['policy_group_options'] = $policy_groups;

		$uglf = TTnew( 'UserGroupListFactory' );
		$user_data['group_options'] = $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $company_id ), 'TEXT', TRUE) );

		//Get other field names
		$oflf = TTnew( 'OtherFieldListFactory' );
		$user_data['other_field_names'] = $oflf->getByCompanyIdAndTypeIdArray( $company_id, 10 );

		$user_data['hierarchy_object_type_options'] = $hierarchy_object_type_options;
		$user_data['hierarchy_control_options'] = $hierarchy_control_options;

		//Company list.
		if ( $permission->Check('company','view') ) {
			$user_data['company_options'] = CompanyListFactory::getAllArray();
		} else {
			$user_data['company_options'] = array( $company_id => $current_company->getName() );
		}

		$filter_data = NULL;
		extract( UserGenericDataFactory::getSearchFormData( $saved_search_id, NULL ) );
		if ( $permission->Check('user','edit') == FALSE ) {
			$filter_data['permission_children_ids'] = $permission_children_ids;
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data );
		$user_data['user_options'] = UserListFactory::getArrayByListFactory( $ulf, FALSE, TRUE );

		$smarty->assign_by_ref('user_data', $user_data);

		$smarty->assign_by_ref('saved_search_id', $saved_search_id);
		$smarty->assign_by_ref('incomplete', $incomplete);
		$smarty->assign_by_ref('data_saved', $data_saved);

		Debug::Text('Current User Permission Level: '. $permission->getLevel() .' Level for user we are currently editing: '. $permission->getLevel( $uf->getID(), $uf->getCompany() ) .' User ID: '. $uf->getID(), __FILE__, __LINE__, __METHOD__,10);

		break;
}

$smarty->assign_by_ref('uf', $uf);

$smarty->display('users/EditUser.tpl');
?>