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
 * $Revision: 6743 $
 * $Id: EditAccrualPolicy.php 6743 2012-05-08 16:33:08Z ipso $
 * $Date: 2012-05-08 09:33:08 -0700 (Tue, 08 May 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('accrual_policy','enabled')
		OR !( $permission->Check('accrual_policy','edit') OR $permission->Check('accrual_policy','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Accrual Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'ids',
												'data',
												'type_id',
												) ) );

if ( isset($data['recalculate_start_date']) ) {
	$data['recalculate_start_date'] = TTDate::parseDateTime( $data['recalculate_start_date'] );
}
if ( isset($data['recalculate_end_date']) ) {
	$data['recalculate_end_date'] = TTDate::parseDateTime( $data['recalculate_end_date'] );
}

if ( isset($data['milestone_rows']) ) {
	foreach( $data['milestone_rows'] as $milestone_row_id => $milestone_row ) {

		if ( $data['type_id'] == 20 AND isset($milestone_row['accrual_rate']) AND $milestone_row['accrual_rate'] != '' ) {
			$data['milestone_rows'][$milestone_row_id]['accrual_rate'] = TTDate::parseTimeUnit($milestone_row['accrual_rate'] );
		}
		if ( isset($milestone_row['maximum_time']) AND $milestone_row['maximum_time'] != '' ) {
			$data['milestone_rows'][$milestone_row_id]['maximum_time'] = TTDate::parseTimeUnit($milestone_row['maximum_time'] );
		}
		/*
		if ( isset($milestone_row['minimum_time']) AND $milestone_row['minimum_time'] != '' ) {
			$data['milestone_rows'][$milestone_row_id]['minimum_time'] = TTDate::parseTimeUnit($milestone_row['minimum_time'] );
		}
		*/
		if ( isset($milestone_row['rollover_time']) AND $milestone_row['rollover_time'] != '' ) {
			$data['milestone_rows'][$milestone_row_id]['rollover_time'] = TTDate::parseTimeUnit($milestone_row['rollover_time'] );
		}

	}
}

$apf = TTnew( 'AccrualPolicyFactory' );
$apmf = TTnew( 'AccrualPolicyMilestoneFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'delete':
		//Debug::setVerbosity(11);
		if ( count($ids) > 0) {
			foreach ($ids as $apm_id) {
				if ($apm_id > 0) {
					Debug::Text('cDeleting Milestone Row ID: '. $apm_id, __FILE__, __LINE__, __METHOD__,10);

					$apmlf = TTnew( 'AccrualPolicyMilestoneListFactory' );
					$apmlf->getById( $apm_id );
					if ( $apmlf->getRecordCount() == 1 ) {
						foreach($apmlf as $apm_obj ) {
							$apm_obj->setDeleted( TRUE );
							if ( $apm_obj->isValid() ) {
								$apm_obj->Save();
							}
						}
					}
				}
				unset($data['milestone_rows'][$apm_id]);

			}
			unset($apm_id);
		}

		Redirect::Page( URLBuilder::getURL( array('id' => $data['id']), 'EditAccrualPolicy.php') );

		break;
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
		$redirect=0;

		$apf->StartTransaction();

		$apf->setId( $data['id'] );
		$apf->setCompany( $current_company->getId() );
		$apf->setName( $data['name'] );
		$apf->setType( $data['type_id'] );

		if ( isset($data['enable_pay_stub_balance_display']) ) {
			$apf->setEnablePayStubBalanceDisplay( TRUE );
		} else {
			$apf->setEnablePayStubBalanceDisplay( FALSE );
		}

		$apf->setApplyFrequency( $data['apply_frequency_id'] );
		$apf->setApplyFrequencyMonth( $data['apply_frequency_month'] );
		$apf->setApplyFrequencyDayOfMonth( $data['apply_frequency_day_of_month'] );
		$apf->setApplyFrequencyDayOfWeek( $data['apply_frequency_day_of_week'] );
		if ( isset($data['apply_frequency_hire_date']) ) {
			$apf->setApplyFrequencyHireDate( TRUE );
		} else {
			$apf->setApplyFrequencyHireDate( FALSE );
		}

		if ( isset($data['milestone_rollover_hire_date']) ) {
			$apf->setMilestoneRolloverHireDate( TRUE );
		} else {
			$apf->setMilestoneRolloverHireDate( FALSE );
			$apf->setMilestoneRolloverMonth( $data['milestone_rollover_month'] );
			$apf->setMilestoneRolloverDayOfMonth( $data['milestone_rollover_day_of_month'] );
		}

		$apf->setMinimumEmployedDays( $data['minimum_employed_days'] );

		if ( $apf->isValid() ) {
			$ap_id = $apf->Save();

			if ( $ap_id === TRUE ) {
				$ap_id = $data['id'];
			}

			if ( ( $data['type_id'] == 20 OR $data['type_id'] == 30 ) AND isset($data['milestone_rows']) AND count($data['milestone_rows']) > 0 ) {
				foreach( $data['milestone_rows'] as $milestone_row_id => $milestone_row ) {
					Debug::Text('Row ID: '. $milestone_row_id, __FILE__, __LINE__, __METHOD__,10);
					if ( $milestone_row['accrual_rate'] > 0 ) {
						if ( $milestone_row_id > 0 ) {
							$apmf->setId( $milestone_row_id);
						}

						$apmf->setAccrualPolicy( $ap_id );
						$apmf->setLengthOfService( $milestone_row['length_of_service'] );
						$apmf->setLengthOfServiceUnit( $milestone_row['length_of_service_unit_id'] );
						$apmf->setAccrualRate( $milestone_row['accrual_rate'] );
						$apmf->setMaximumTime( $milestone_row['maximum_time'] );
						//$apmf->setMinimumTime( $milestone_row['minimum_time'] );
						$apmf->setRolloverTime( $milestone_row['rollover_time'] );

						if ( $apmf->isValid() ) {
							Debug::Text('Saving Milestone Row ID: '. $milestone_row_id, __FILE__, __LINE__, __METHOD__,10);
							$apmf->Save();
						} else {
							$redirect++;
						}
					}
				}
			}

			if ( $redirect == 0 ) {
				$apf->CommitTransaction();
				//$apf->FailTransaction();

				if ( isset($ap_id) AND isset($data['recalculate']) AND $data['recalculate'] == 1 ) {
					Debug::Text('Recalculating Accruals...', __FILE__, __LINE__, __METHOD__,10);

					if ( isset($data['recalculate_start_date']) AND isset($data['recalculate_end_date'])
							AND $data['recalculate_start_date'] < $data['recalculate_end_date']) {
						Redirect::Page( URLBuilder::getURL( array('action' => 'recalculate_accrual_policy', 'data' => array('accrual_policy_id' => $ap_id, 'start_date' => $data['recalculate_start_date'], 'end_date' => $data['recalculate_end_date']), 'next_page' => urlencode( URLBuilder::getURL( NULL, '../policy/AccrualPolicyList.php') ) ), '../progress_bar/ProgressBarControl.php'), FALSE );
					}
				}

				Redirect::Page( URLBuilder::getURL( NULL, 'AccrualPolicyList.php') );

				break;
			}

		}
		$apf->FailTransaction();
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$aplf = TTnew( 'AccrualPolicyListFactory' );
			$apmlf = TTnew( 'AccrualPolicyMilestoneListFactory' );

			$aplf->getByIdAndCompanyID( $id, $current_company->getID() );
			if ( $aplf->getRecordCount() > 0 ) {
				$apmlf->getByAccrualPolicyId( $id );
				if ( $apmlf->getRecordCount() > 0 ) {
					foreach( $apmlf as $apm_obj ) {
						$milestone_rows[$apm_obj->getId()] = array(
																'id' => $apm_obj->getId(),
																'length_of_service' => $apm_obj->getLengthOfService(),
																'length_of_service_unit_id' => $apm_obj->getLengthOfServiceUnit(),
																'accrual_rate' => $apm_obj->getAccrualRate(),
																'maximum_time' => $apm_obj->getMaximumTime(),
																'rollover_time' => $apm_obj->getRolloverTime(),
																//'minimum_time' => $apm_obj->getMinimumTime(),
																);
					}
				} else {
					$milestone_rows[-1] = array(
						'id' => -1,
						'length_of_service' => 0,
						'accrual_rate' => 0,
						'minimum_time' => 0,
						'maximum_time' => 0,
						'rollover_time' => '', //NULL is not used.
						);

				}

				foreach ($aplf as $ap_obj) {
					//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);
					$data = array(
										'id' => $ap_obj->getId(),
										'name' => $ap_obj->getName(),
										'type_id' => $ap_obj->getType(),
										'enable_pay_stub_balance_display' => $ap_obj->getEnablePayStubBalanceDisplay(),
										'apply_frequency_id' => $ap_obj->getApplyFrequency(),
										'apply_frequency_month' => $ap_obj->getApplyFrequencyMonth(),
										'apply_frequency_day_of_month' => $ap_obj->getApplyFrequencyDayOfMonth(),
										'apply_frequency_day_of_week' => $ap_obj->getApplyFrequencyDayOfWeek(),
										'apply_frequency_hire_date' => $ap_obj->getApplyFrequencyHireDate(),
										'milestone_rollover_hire_date' => $ap_obj->getMilestoneRolloverHireDate(),
										'milestone_rollover_month' => $ap_obj->getMilestoneRolloverMonth(),
										'milestone_rollover_day_of_month' => $ap_obj->getMilestoneRolloverDayOfMonth(),
										'minimum_employed_days' => $ap_obj->getMinimumEmployedDays(),

										'recalculate_start_date' => TTDate::getBeginMonthEpoch( time() ),
										'recalculate_end_date' => TTDate::getEndMonthEpoch( time() ),

										'milestone_rows' => $milestone_rows,

										'created_date' => $ap_obj->getCreatedDate(),
										'created_by' => $ap_obj->getCreatedBy(),
										'updated_date' => $ap_obj->getUpdatedDate(),
										'updated_by' => $ap_obj->getUpdatedBy(),
										'deleted_date' => $ap_obj->getDeletedDate(),
										'deleted_by' => $ap_obj->getDeletedBy()
									);
				}
			}
		} elseif ( $action == 'add_milestone' ) {
			Debug::Text('Adding Blank Week', __FILE__, __LINE__, __METHOD__,10);
			if ( !isset($data['milestone_rows']) ) {
				$data['milestone_rows'] = array();
			}

			$row_keys = array_keys($data['milestone_rows']);
			sort($row_keys);

			Debug::Text('Lowest ID: '. $row_keys[0], __FILE__, __LINE__, __METHOD__,10);
			$lowest_id = $row_keys[0];
			if ( $lowest_id < 0 ) {
				$next_blank_id = $lowest_id-1;
			} else {
				$next_blank_id = -1;
			}

			Debug::Text('Next Blank ID: '. $next_blank_id, __FILE__, __LINE__, __METHOD__,10);

			$data['milestone_rows'][$next_blank_id] = array(
							'id' => $next_blank_id,
							'length_of_service' => 0,
							'accrual_rate' => 0,
							'minimum_time' => 0,
							'maximum_time' => 0,
							'rollover_time' => '',
							);
		} elseif ( $action != 'submit' AND $action != 'change_type' ) {
			$data = array(
						'type_id' => 10,
						'minimum_employed_days' => 0,
						'recalculate_start_date' => TTDate::getBeginMonthEpoch( time() ),
						'recalculate_end_date' => TTDate::getEndMonthEpoch( time() ),
						'apply_frequency_hire_date' => TRUE,
						'milestone_rows' => array( -1 => array(
													'id' => -1,
													'length_of_service' => 0,
													'accrual_rate' => '0.0000',
													'minimum_time' => 0,
													'maximum_time' => 0,
													'rollover_time' => '',
												) )
						);
		} else {
			if ( $data['type_id'] == 20 ) {
				$data['recalculate_start_date'] = TTDate::getBeginMonthEpoch( time() );
				$data['recalculate_end_date'] = TTDate::getEndMonthEpoch( time() );
			}
		}
		//print_r($data);

		//Select box options;
		$data['type_options'] = $apf->getOptions('type');
		$data['apply_frequency_options'] = $apf->getOptions('apply_frequency');
		$data['month_options'] = TTDate::getMonthOfYearArray();
		$data['day_of_month_options'] = TTDate::getDayOfMonthArray();
		$data['day_of_week_options'] = TTDate::getDayOfWeekArray();
		$data['length_of_service_unit_options'] = $apmf->getOptions('length_of_service_unit');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('apf', $apf);
$smarty->assign_by_ref('apmf', $apmf);

$smarty->display('policy/EditAccrualPolicy.tpl');
?>