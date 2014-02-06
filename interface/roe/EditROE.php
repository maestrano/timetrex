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
 * $Revision: 6822 $
 * $Id: EditROE.php 6822 2012-05-23 15:46:28Z ipso $
 * $Date: 2012-05-23 08:46:28 -0700 (Wed, 23 May 2012) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('roe','enabled')
		OR !( $permission->Check('roe','edit') OR $permission->Check('roe','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Record Of Employment')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'roe_data',
												'setup_data',
												'user_id'
												) ) );

$roef = TTnew( 'ROEFactory' );

if ( isset($roe_data) ) {
	if ( $roe_data['first_date'] != '' ) {
		$roe_data['first_date'] = TTDate::parseDateTime($roe_data['first_date']);
	}
	if ( $roe_data['last_date'] != '' ) {
		$roe_data['last_date'] = TTDate::parseDateTime($roe_data['last_date']);
	}
	if ( $roe_data['pay_period_end_date'] != '' ) {
		$roe_data['pay_period_end_date'] = TTDate::parseDateTime($roe_data['pay_period_end_date']);
	}
	if ( $roe_data['recall_date'] != '' ) {
		$roe_data['recall_date'] = TTDate::parseDateTime($roe_data['recall_date']);
	}
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Save report setup data
		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $roef->getTable() );
		if ( $ugdlf->getRecordCount() > 0 ) {
			$ugdf->setID( $ugdlf->getCurrent()->getID() );
		}
		$ugdf->setCompany( $current_company->getId() );
		$ugdf->setScript( $roef->getTable() );
		$ugdf->setName( $title );
		$ugdf->setData( $setup_data );
		$ugdf->setDefault( TRUE );
		if ( $ugdf->isValid() ) {
			$ugdf->Save();
		}

		if ( !empty($roe_data['id']) ) {
			$roef->setId( $roe_data['id'] );
		}

		$roef->setUser( $roe_data['user_id'] );
		$roef->setPayPeriodType( $roe_data['pay_period_type_id'] );
		$roef->setCode( $roe_data['code_id'] );

		if ( $roe_data['first_date'] != '' ) {
			$roef->setFirstDate( $roe_data['first_date'] );
		}
		if ( $roe_data['last_date'] != '' ) {
			$roef->setLastDate( $roe_data['last_date']);
		}
		if ( $roe_data['pay_period_end_date'] != '' ) {
			$roef->setPayPeriodEndDate( $roe_data['pay_period_end_date'] );
		}
		if ( $roe_data['recall_date'] != '' ) {
			$roef->setRecallDate( $roe_data['recall_date'] );
		}

		$roef->setSerial( $roe_data['serial'] );
		$roef->setComments( $roe_data['comments'] );

		if ( $roef->isValid() ) {
			$roef->setEnableReCalculate( TRUE );
			if ( isset($roe_data['generate_pay_stub']) AND $roe_data['generate_pay_stub'] == 1 ) {
				$roef->setEnableGeneratePayStub(TRUE );
			} else {
				$roef->setEnableGeneratePayStub( FALSE );
			}
			if ( isset($roe_data['release_accruals']) AND $roe_data['release_accruals'] == 1 ) {
				$roef->setEnableReleaseAccruals(TRUE );
			} else {
				$roef->setEnableReleaseAccruals( FALSE );
			}

			$roef->Save();

			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $current_user->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();

			$next_page = URLBuilder::getURL( array('user_id' => $roe_data['user_id'] ), '../roe/ROEList.php');

			Redirect::Page( URLBuilder::getURL( array('batch_id' => $ugsf->getBatchID(), 'batch_title' => 'Record of Employement', 'batch_next_page' => $next_page), '../users/UserGenericStatusList.php') );

			unset($ugsf);

			//Redirect::Page( URLBuilder::getURL( array('user_id' => $roe_data['user_id'] ), 'ROEList.php') );

			break;
		}

	default:
		$ugdlf->getByCompanyIdAndScriptAndDefault( $current_company->getId(), $roef->getTable() );
		if ( $ugdlf->getRecordCount() > 0 ) {
			Debug::Text('Found Company Report Setup!', __FILE__, __LINE__, __METHOD__,10);
			$ugd_obj = $ugdlf->getCurrent();
			$setup_data = $ugd_obj->getData();
		}
		unset($ugd_obj);

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$roelf = TTnew( 'ROEListFactory' );

			$roelf->getById( $id );

			foreach ($roelf as $roe) {
				//Debug::Arr($department,'Department', __FILE__, __LINE__, __METHOD__,10);

				$roe_data = array(
									'id' => $roe->getId(),
									'user_id' => $roe->getUser(),
									'pay_period_type_id' => $roe->getPayPeriodType(),
									'code_id' => $roe->getCode(),
									'first_date' => $roe->getFirstDate(),
									'last_date' => $roe->getLastDate(),
									'pay_period_end_date' => $roe->getPayPeriodEndDate(),
									'recall_date' => $roe->getRecallDate(),
									'insurable_hours' => $roe->getInsurableHours(),
									'insurable_earnings' => $roe->getInsurableEarnings(),
									'vacation_pay' => $roe->getVacationPay(),
									'serial' => $roe->getSerial(),
									'comments' => $roe->getComments(),
									'created_date' => $roe->getCreatedDate(),
									'created_by' => $roe->getCreatedBy(),
									'updated_date' => $roe->getUpdatedDate(),
									'updated_by' => $roe->getUpdatedBy(),
									'deleted_date' => $roe->getDeletedDate(),
									'deleted_by' => $roe->getDeletedBy()
								);
			}
		} elseif ( !isset($action))  {
			//Get all the data we should need for this ROE in regards to pay period and such
			//Guess for end dates...

			//get User data for hire date
			$ulf = TTnew( 'UserListFactory' );
			$user_obj = $ulf->getById($user_id)->getCurrent();

			$plf = TTnew( 'PunchListFactory' );

			//Is there a previous ROE? If so, find first shift back since ROE was issued.
			$rlf = TTnew( 'ROEListFactory' );
			$rlf->getLastROEByUserId( $user_id );
			if ( $rlf->getRecordCount() > 0 ) {
				$roe_obj = $rlf->getCurrent();

				Debug::Text('Previous ROE Last Date: '. TTDate::getDate('DATE+TIME', $roe_obj->getLastDate() ) , __FILE__, __LINE__, __METHOD__,10);
				//$plf->getFirstPunchByUserIDAndEpoch( $user_id, $roe_obj->getLastDate() );
				$plf->getNextPunchByUserIdAndEpoch( $user_id, $roe_obj->getLastDate() );
				if ( $plf->getRecordCount() > 0 ) {
					$first_date = $plf->getCurrent()->getTimeStamp();
				}
			}

			if ( !isset($first_date) OR $first_date == '' ) {
				$first_date = $user_obj->getHireDate();
			}
			Debug::Text('First Date: '. TTDate::getDate('DATE+TIME', $first_date) , __FILE__, __LINE__, __METHOD__,10);

			//Get last shift worked (not scheduled)
			$plf->getLastPunchByUserId( $user_id );
			if ( $plf->getRecordCount() > 0 ) {
				$punch_obj = $plf->getCurrent();
				$last_date = $punch_obj->getPunchControlObject()->getUserDateObject()->getDateStamp();
			} else {
				$last_date = TTDate::getTime();
			}

			Debug::Text('Last Punch Date: '. TTDate::getDate('DATE+TIME', $last_date) , __FILE__, __LINE__, __METHOD__,10);

			//Get pay period of last shift workd
			$plf = TTnew( 'PayPeriodListFactory' );
			$pay_period_obj = $plf->getByUserIdAndEndDate( $user_id, $last_date )->getCurrent();

			$pay_period_type_id = FALSE;
			if ( is_object( $pay_period_obj->getPayPeriodScheduleObject() ) ) {
				$pay_period_type_id = $pay_period_obj->getPayPeriodScheduleObject()->getType();
			}
			$roe_data = array(
								'user_id' => $user_id,
								'pay_period_type_id' => $pay_period_type_id,
								'first_date' => $first_date,
								'last_date' => $last_date,
								'pay_period_end_date' => $pay_period_obj->getEndDate()
								);
		}

		//Select box options;
		$roe_data['code_options'] = $roef->getOptions('code');

		$ppsf = TTnew( 'PayPeriodScheduleFactory' );
		$roe_data['pay_period_type_options'] = $ppsf->getOptions('type');
		unset($roe_data['pay_period_type_options'][5]);

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$smarty->assign_by_ref('user_options', $user_options);

		$aplf = TTnew( 'AbsencePolicyListFactory' );
		$absence_policy_options = Misc::prependArray( array( 0 => TTi18n::gettext('--') ), $aplf->getByCompanyIdArray( $current_company->getId() ) );
		$smarty->assign_by_ref('absence_policy_options', $absence_policy_options);

		//PSEA accounts
		$psealf = TTnew( 'PayStubEntryAccountListFactory' );
		$earning_pay_stub_entry_account_options = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $current_company->getId(), 10, array(10,30,40), TRUE );
		$smarty->assign_by_ref('earning_pay_stub_entry_account_options', $earning_pay_stub_entry_account_options);

		$smarty->assign_by_ref('roe_data', $roe_data);
		$smarty->assign_by_ref('setup_data', $setup_data);

		break;
}

$smarty->assign_by_ref('roef', $roef);

$smarty->display('roe/EditROE.tpl');
?>