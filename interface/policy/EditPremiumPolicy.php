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
 * $Revision: 9993 $
 * $Id: EditPremiumPolicy.php 9993 2013-05-24 20:16:41Z ipso $
 * $Date: 2013-05-24 13:16:41 -0700 (Fri, 24 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('premium_policy','enabled')
		OR !( $permission->Check('premium_policy','edit') OR $permission->Check('premium_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Premium Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data)) {
	if ( $data['start_date'] != '' ) {
		$data['start_date'] = TTDate::parseDateTime( $data['start_date'] );
	}
	if ( $data['end_date'] != '' ) {
		$data['end_date'] = TTDate::parseDateTime( $data['end_date'] );
	}
	if ( $data['start_time'] != '' ) {
		$data['start_time'] = TTDate::parseDateTime( $data['start_time'] );
	}
	if ( $data['end_time'] != '' ) {
		$data['end_time'] = TTDate::parseDateTime( $data['end_time'] );
	}

	if ( isset($data['maximum_no_break_time'] ) ) {
		$data['maximum_no_break_time'] = TTDate::parseTimeUnit($data['maximum_no_break_time']);
	}
	if ( isset($data['minimum_break_time'] ) ) {
		$data['minimum_break_time'] = TTDate::parseTimeUnit($data['minimum_break_time']);
	}

	if ( isset($data['minimum_time_between_shift'] ) ) {
		$data['minimum_time_between_shift'] = TTDate::parseTimeUnit($data['minimum_time_between_shift']);
	}
	if ( isset($data['minimum_first_shift_time'] ) ) {
		$data['minimum_first_shift_time'] = TTDate::parseTimeUnit($data['minimum_first_shift_time']);
	}

	if ( isset($data['minimum_shift_time'] ) ) {
		$data['minimum_shift_time'] = TTDate::parseTimeUnit($data['minimum_shift_time']);
	}

	if ( isset($data['minimum_time'] ) ) {
		$data['minimum_time'] = TTDate::parseTimeUnit($data['minimum_time']);
	}
	if ( isset($data['maximum_time'] ) ) {
		$data['maximum_time'] = TTDate::parseTimeUnit($data['maximum_time']);
	}

	if ( $data['type_id'] == 30 ) {
		if ( isset($data['daily_trigger_time2'] ) ) {
			$data['daily_trigger_time'] = TTDate::parseTimeUnit($data['daily_trigger_time2']);
		}
	} else {
		if ( isset($data['daily_trigger_time'] ) ) {
			$data['daily_trigger_time'] = TTDate::parseTimeUnit($data['daily_trigger_time']);
		}
		if ( isset($data['maximum_daily_trigger_time'] ) ) {
			$data['maximum_daily_trigger_time'] = TTDate::parseTimeUnit($data['maximum_daily_trigger_time']);
		}

	}

	if ( isset($data['weekly_trigger_time'] ) ) {
		$data['weekly_trigger_time'] = TTDate::parseTimeUnit($data['weekly_trigger_time']);
	}
	if ( isset($data['maximum_weekly_trigger_time'] ) ) {
		$data['maximum_weekly_trigger_time'] = TTDate::parseTimeUnit($data['maximum_weekly_trigger_time']);
	}

}

$ppf = TTnew( 'PremiumPolicyFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$ppf->StartTransaction();

		$ppf->setId( $data['id'] );
		$ppf->setCompany( $current_company->getId() );
		$ppf->setName( $data['name'] );
		$ppf->setType( $data['type_id'] );
		$ppf->setPayType( $data['pay_type_id'] );

		if ( $data['type_id'] == 10 OR $data['type_id'] == 100 ) {
			$ppf->setStartDate( $data['start_date'] );
			$ppf->setEndDate( $data['end_date'] );

			$ppf->setStartTime( $data['start_time'] );
			$ppf->setEndTime( $data['end_time'] );

			$ppf->setDailyTriggerTime( $data['daily_trigger_time'] );
			$ppf->setMaximumDailyTriggerTime( $data['maximum_daily_trigger_time'] );
			$ppf->setWeeklyTriggerTime( $data['weekly_trigger_time'] );
			$ppf->setMaximumWeeklyTriggerTime( $data['maximum_weekly_trigger_time'] );

			if ( isset($data['mon']) ) {
				$ppf->setMon( TRUE );
			} else {
				$ppf->setMon( FALSE );
			}

			if ( isset($data['tue']) ) {
				$ppf->setTue( TRUE );
			} else {
				$ppf->setTue( FALSE );
			}

			if ( isset($data['wed']) ) {
				$ppf->setWed( TRUE );
			} else {
				$ppf->setWed( FALSE );
			}

			if ( isset($data['thu']) ) {
				$ppf->setThu( TRUE );
			} else {
				$ppf->setThu( FALSE );
			}

			if ( isset($data['fri']) ) {
				$ppf->setFri( TRUE );
			} else {
				$ppf->setFri( FALSE );
			}

			if ( isset($data['sat']) ) {
				$ppf->setSat( TRUE );
			} else {
				$ppf->setSat( FALSE );
			}

			if ( isset($data['sun']) ) {
				$ppf->setSun( TRUE );
			} else {
				$ppf->setSun( FALSE );
			}

			$ppf->setIncludeHolidayType( $data['include_holiday_type_id'] );

			if ( isset($data['include_partial_punch']) ) {
				$ppf->setIncludePartialPunch( TRUE );
			} else {
				$ppf->setIncludePartialPunch( FALSE );
			}
		} elseif ( $data['type_id'] == 30 ) {
			$ppf->setDailyTriggerTime( $data['daily_trigger_time'] );
		}

		if ( $data['type_id'] == 90 ) {
			if ( isset($data['holiday_include_partial_punch']) ) {
				$ppf->setIncludePartialPunch( TRUE );
			} else {
				$ppf->setIncludePartialPunch( FALSE );
			}
		}

		if ( isset($data['maximum_no_break_time']) ) {
			$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
		}
		if ( isset($data['minimum_break_time']) ) {
			$ppf->setMinimumBreakTime( $data['minimum_break_time'] );
		}

		if ( isset($data['minimum_time_between_shift']) ) {
			$ppf->setMinimumTimeBetweenShift( $data['minimum_time_between_shift'] );
		}
		if ( isset($data['minimum_first_shift_time']) ) {
			$ppf->setMinimumFirstShiftTime( $data['minimum_first_shift_time'] );
		}

		if ( isset($data['minimum_shift_time']) ) {
			$ppf->setMinimumShiftTime( $data['minimum_shift_time'] );
		}

		$ppf->setMinimumTime( $data['minimum_time'] );
		$ppf->setMaximumTime( $data['maximum_time'] );
		if ( isset($data['include_meal_policy']) ) {
			$ppf->setIncludeMealPolicy( TRUE );
		} else {
			$ppf->setIncludeMealPolicy( FALSE );
		}
		if ( isset($data['include_break_policy']) ) {
			$ppf->setIncludeBreakPolicy( TRUE );
		} else {
			$ppf->setIncludeBreakPolicy( FALSE );
		}

		$ppf->setWageGroup( $data['wage_group_id'] );
		$ppf->setRate( $data['rate'] );
		$ppf->setPayStubEntryAccountId( $data['pay_stub_entry_account_id'] );
		$ppf->setAccrualPolicyId( $data['accrual_policy_id'] );
		$ppf->setAccrualRate( $data['accrual_rate'] );

		$ppf->setBranchSelectionType( $data['branch_selection_type_id'] );
		if ( isset($data['exclude_default_branch']) ) {
			$ppf->setExcludeDefaultBranch( TRUE );
		} else {
			$ppf->setExcludeDefaultBranch( FALSE );
		}

		$ppf->setDepartmentSelectionType( $data['department_selection_type_id'] );
		if ( isset($data['exclude_default_department']) ) {
			$ppf->setExcludeDefaultDepartment( TRUE );
		} else {
			$ppf->setExcludeDefaultDepartment( FALSE );
		}

		if ( $current_company->getProductEdition() >= 20 ) {
			$ppf->setJobGroupSelectionType( $data['job_group_selection_type_id'] );
			$ppf->setJobSelectionType( $data['job_selection_type_id'] );

			$ppf->setJobItemGroupSelectionType( $data['job_item_group_selection_type_id'] );
			$ppf->setJobItemSelectionType( $data['job_item_selection_type_id'] );
		} else {
			//Set selection types to "All" so speed up checks in calcPremiumPolicy
			$ppf->setJobGroupSelectionType( 10 );
			$ppf->setJobSelectionType( 10 );

			$ppf->setJobItemGroupSelectionType( 10 );
			$ppf->setJobItemSelectionType( 10 );
		}

		if ( $ppf->isValid() ) {
			$ppf->Save(FALSE);

			if ( isset($data['branch_ids']) ){
				$ppf->setBranch( $data['branch_ids'] );
			} else {
				$ppf->setBranch( array() );
			}

			if ( isset($data['department_ids']) ){
				$ppf->setDepartment( $data['department_ids'] );
			} else {
				$ppf->setDepartment( array() );
			}

			if ( $current_company->getProductEdition() >= 20 ) {
				if ( isset($data['job_group_ids']) ){
					$ppf->setJobGroup( $data['job_group_ids'] );
				} else {
					$ppf->setJobGroup( array() );
				}
				if ( isset($data['job_ids']) ){
					$ppf->setJob( $data['job_ids'] );
				} else {
					$ppf->setJob( array() );
				}

				if ( isset($data['job_item_group_ids']) ){
					$ppf->setJobItemGroup( $data['job_item_group_ids'] );
				} else {
					$ppf->setJobItemGroup( array() );
				}
				if ( isset($data['job_item_ids']) ){
					$ppf->setJobItem( $data['job_item_ids'] );
				} else {
					$ppf->setJobItem( array() );
				}
			}

			if ( $ppf->isValid() ) {
				$ppf->Save(TRUE);

				//$ppf->FailTransaction();
				$ppf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'PremiumPolicyList.php') );

				break;
			}
		}

		$ppf->FailTransaction();
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$pplf = TTnew( 'PremiumPolicyListFactory' );
			$pplf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($pplf as $pp_obj) {
				$data = array(
									'id' => $pp_obj->getId(),
									'name' => $pp_obj->getName(),
									'type_id' => $pp_obj->getType(),
									'pay_type_id' => $pp_obj->getPayType(),
									//'level' => $pp_obj->getLevel(),

									'start_date' => $pp_obj->getStartDate(),
									'end_date' => $pp_obj->getEndDate(),

									'start_time' => $pp_obj->getStartTime(),
									'end_time' => $pp_obj->getEndTime(),

									'daily_trigger_time' => $pp_obj->getDailyTriggerTime(),
									'maximum_daily_trigger_time' => $pp_obj->getMaximumDailyTriggerTime(),
									'weekly_trigger_time' => $pp_obj->getWeeklyTriggerTime(),
									'maximum_weekly_trigger_time' => $pp_obj->getMaximumWeeklyTriggerTime(),

									'sun' => $pp_obj->getSun(),
									'mon' => $pp_obj->getMon(),
									'tue' => $pp_obj->getTue(),
									'wed' => $pp_obj->getWed(),
									'thu' => $pp_obj->getThu(),
									'fri' => $pp_obj->getFri(),
									'sat' => $pp_obj->getSat(),

									'include_holiday_type_id' => $pp_obj->getIncludeHolidayType(),

									'include_partial_punch' => $pp_obj->getIncludePartialPunch(),

									'maximum_no_break_time' => $pp_obj->getMaximumNoBreakTime(),
									'minimum_break_time' => $pp_obj->getMinimumBreakTime(),

									'minimum_time_between_shift' => $pp_obj->getMinimumTimeBetweenShift(),
									'minimum_first_shift_time' => $pp_obj->getMinimumFirstShiftTime(),

									'minimum_shift_time' => $pp_obj->getMinimumShiftTime(),

									'minimum_time' => $pp_obj->getMinimumTime(),
									'maximum_time' => $pp_obj->getMaximumTime(),

									'include_meal_policy' => $pp_obj->getIncludeMealPolicy(),
									'include_break_policy' => $pp_obj->getIncludeBreakPolicy(),

									'wage_group_id' => $pp_obj->getWageGroup(),
									'rate' => Misc::removeTrailingZeros( $pp_obj->getRate() ),

									'accrual_rate' => Misc::removeTrailingZeros( $pp_obj->getAccrualRate() ),
									'accrual_policy_id' => $pp_obj->getAccrualPolicyID(),
									'pay_stub_entry_account_id' => $pp_obj->getPayStubEntryAccountId(),

									'branch_selection_type_id' => $pp_obj->getBranchSelectionType(),
									'exclude_default_branch' => $pp_obj->getExcludeDefaultBranch(),
									'branch_ids' => $pp_obj->getBranch(),

									'department_selection_type_id' => $pp_obj->getDepartmentSelectionType(),
									'exclude_default_department' => $pp_obj->getExcludeDefaultDepartment(),
									'department_ids' => $pp_obj->getDepartment(),

									'job_group_selection_type_id' => $pp_obj->getJobGroupSelectionType(),
									'job_group_ids' => $pp_obj->getJobGroup(),
									'job_selection_type_id' => $pp_obj->getJobSelectionType(),
									'job_ids' => $pp_obj->getJob(),

									'job_item_group_selection_type_id' => $pp_obj->getJobItemGroupSelectionType(),
									'job_item_group_ids' => $pp_obj->getJobItemGroup(),
									'job_item_selection_type_id' => $pp_obj->getJobItemSelectionType(),
									'job_item_ids' => $pp_obj->getJobItem(),

									'created_date' => $pp_obj->getCreatedDate(),
									'created_by' => $pp_obj->getCreatedBy(),
									'updated_date' => $pp_obj->getUpdatedDate(),
									'updated_by' => $pp_obj->getUpdatedBy(),
									'deleted_date' => $pp_obj->getDeletedDate(),
									'deleted_by' => $pp_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit') {
			$data = array(
								'start_time' => NULL,
								'end_time' => NULL,
								'sun' => TRUE,
								'mon' => TRUE,
								'tue' => TRUE,
								'wed' => TRUE,
								'thu' => TRUE,
								'fri' => TRUE,
								'sat' => TRUE,
								'wage_group_id' => 0,
								'rate' => '1.00',
								'accrual_rate' => '1.00',
								'daily_trigger_time' => 0,
								'maximum_daily_trigger_time' => 0,
								'weekly_trigger_time' => 0,
								'maximum_weekly_trigger_time' => 0,
								'maximum_no_break_time' => 0,
								'minimum_break_time' => 0,
								'minimum_time_between_shift' => 0,
								'minimum_first_shift_time' => 0,
								'minimum_shift_time' => 0,
								'minimum_time' => 0,
								'maximum_time' => 0,
								'include_meal_policy' => TRUE,
								'include_break_policy' => TRUE,
								'include_holiday_type_id' => 10,
								);
		}

		$data = Misc::preSetArrayValues( $data, array('branch_ids', 'department_ids', 'job_group_ids', 'job_ids', 'job_item_group_ids', 'job_item_ids'), NULL);

		$aplf = TTnew( 'AccrualPolicyListFactory' );
		$accrual_options = $aplf->getByCompanyIDArray( $current_company->getId(), TRUE );

		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$pay_stub_entry_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,20,30,50) );

		//Get branches
		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = $blf->getArrayByListFactory( $blf, FALSE, TRUE );
		$data['src_branch_options'] = Misc::arrayDiffByKey( (array)$data['branch_ids'], $branch_options );
		$data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$data['branch_ids'], $branch_options );

		//Get departments
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = $dlf->getArrayByListFactory( $dlf, FALSE, TRUE );
		$data['src_department_options'] = Misc::arrayDiffByKey( (array)$data['department_ids'], $department_options );
		$data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$data['department_ids'], $department_options );

		if ( $current_company->getProductEdition() >= 20 ) {
			//Get Job Groups
			$jglf = TTnew( 'JobGroupListFactory' );
			$nodes = FastTree::FormatArray( $jglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_group_options = $jglf->getArrayByNodes( $nodes, FALSE, FALSE );
			$data['src_job_group_options'] = Misc::arrayDiffByKey( (array)$data['job_group_ids'], $job_group_options );
			$data['selected_job_group_options'] = Misc::arrayIntersectByKey( (array)$data['job_group_ids'], $job_group_options );

			//Get Jobs
			$jlf = TTnew( 'JobListFactory' );
			$jlf->getByCompanyId( $current_company->getId() );
			$job_options = $jlf->getArrayByListFactory( $jlf, FALSE, TRUE );
			$data['src_job_options'] = Misc::arrayDiffByKey( (array)$data['job_ids'], $job_options );
			$data['selected_job_options'] = Misc::arrayIntersectByKey( (array)$data['job_ids'], $job_options );

			//Get Job Item Groups
			$jiglf = TTnew( 'JobItemGroupListFactory' );
			$nodes = FastTree::FormatArray( $jiglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE);
			$job_item_group_options = $jiglf->getArrayByNodes( $nodes, FALSE, FALSE );
			$data['src_job_item_group_options'] = Misc::arrayDiffByKey( (array)$data['job_item_group_ids'], $job_item_group_options );
			$data['selected_job_item_group_options'] = Misc::arrayIntersectByKey( (array)$data['job_item_group_ids'], $job_item_group_options );

			//Get Job Items
			$jilf = TTnew( 'JobItemListFactory' );
			$jilf->getByCompanyId( $current_company->getId() );
			$job_item_options = $jilf->getArrayByListFactory( $jilf, FALSE, TRUE );
			$data['src_job_item_options'] = Misc::arrayDiffByKey( (array)$data['job_item_ids'], $job_item_options );
			$data['selected_job_item_options'] = Misc::arrayIntersectByKey( (array)$data['job_item_ids'], $job_item_options );
		}

		//Select box options;
		$wglf = TTnew( 'WageGroupListFactory' );
		$data['wage_group_options'] = $wglf->getArrayByListFactory( $wglf->getByCompanyId( $current_company->getId() ), TRUE );

		$data['type_options'] = $ppf->getOptions('type');
		$data['pay_type_options'] = $ppf->getOptions('pay_type');

		$data['include_holiday_type_options'] = $ppf->getOptions('include_holiday_type');

		$data['branch_selection_type_options'] = $ppf->getOptions('branch_selection_type');
		$data['department_selection_type_options'] = $ppf->getOptions('department_selection_type');
		$data['job_group_selection_type_options'] = $ppf->getOptions('job_group_selection_type');
		$data['job_selection_type_options'] = $ppf->getOptions('job_selection_type');
		$data['job_item_group_selection_type_options'] = $ppf->getOptions('job_item_group_selection_type');
		$data['job_item_selection_type_options'] = $ppf->getOptions('job_item_selection_type');

		$data['pay_stub_entry_options'] = $pay_stub_entry_options;
		$data['accrual_options'] = $accrual_options;

		//print_r($data);
		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('ppf', $ppf);

$smarty->display('policy/EditPremiumPolicy.tpl');
?>