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
 * $Revision: 4104 $
 * $Id: EditRoundPolicy.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('round_policy','enabled')
		OR !( $permission->Check('round_policy','edit') OR $permission->Check('round_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Rounding Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'round_policy_data'
												) ) );
if ( isset($round_policy_data) ) {
	$round_policy_data['round_grace_start'] = TTDate::parseTimeUnit( $round_policy_data['round_grace_start'] );
	$round_policy_data['round_grace_lunch_start'] = TTDate::parseTimeUnit( $round_policy_data['round_grace_lunch_start'] );
	$round_policy_data['round_grace_lunch_end'] = TTDate::parseTimeUnit( $round_policy_data['round_grace_lunch_end'] );
	$round_policy_data['round_grace_end'] = TTDate::parseTimeUnit( $round_policy_data['round_grace_end'] );

	$round_policy_data['round_start'] = TTDate::parseTimeUnit( $round_policy_data['round_start'] );
	$round_policy_data['round_lunch_start'] = TTDate::parseTimeUnit( $round_policy_data['round_lunch_start'] );
	$round_policy_data['round_lunch_end'] = TTDate::parseTimeUnit( $round_policy_data['round_lunch_end'] );
	$round_policy_data['round_end'] = TTDate::parseTimeUnit( $round_policy_data['round_end'] );
}

$rpf = TTnew( 'RoundPolicyFactory' );

$action = strtolower($action);
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$rpf->setId($round_policy_data['id']);
		$rpf->setCompany($current_company->getId() );
		$rpf->setName($round_policy_data['name']);

		if ( $round_policy_data['description'] != '' ) {
			$rpf->setDescription($round_policy_data['description']);
		}

		if ( isset($round_policy_data['default']) ) {
			$rpf->setDefault(TRUE);
		} else {
			$rpf->setDefault(FALSE);
		}

		if ( isset($round_policy_data['strict_start']) ) {
			$rpf->setStrictStart(TRUE);
		} else {
			$rpf->setStrictStart(FALSE);
		}

		if ( isset($round_policy_data['strict_lunch_start']) ) {
			$rpf->setStrictLunchStart(TRUE);
		} else {
			$rpf->setStrictLunchStart(FALSE);
		}

		if ( isset($round_policy_data['strict_lunch_end']) ) {
			$rpf->setStrictLunchEnd(TRUE);
		} else {
			$rpf->setStrictLunchEnd(FALSE);
		}

		if ( isset($round_policy_data['strict_end']) ) {
			$rpf->setStrictEnd(TRUE);
		} else {
			$rpf->setStrictEnd(FALSE);
		}

		$rpf->setRoundGraceStart( $round_policy_data['round_grace_start'] );
		$rpf->setRoundGraceLunchStart( $round_policy_data['round_grace_lunch_start'] );
		$rpf->setRoundGraceLunchEnd( $round_policy_data['round_grace_lunch_end'] );
		$rpf->setRoundGraceEnd( $round_policy_data['round_grace_end'] );

		$rpf->setRoundStart( $round_policy_data['round_start'] );
		$rpf->setRoundLunchStart( $round_policy_data['round_lunch_start'] );
		$rpf->setRoundLunchEnd( $round_policy_data['round_lunch_end'] );
		$rpf->setRoundEnd( $round_policy_data['round_end'] );

		$rpf->setRoundTypeStart( $round_policy_data['round_type_start'] );
		$rpf->setRoundTypeLunchStart( $round_policy_data['round_type_lunch_start'] );
		$rpf->setRoundTypeLunchEnd( $round_policy_data['round_type_lunch_end'] );
		$rpf->setRoundTypeEnd( $round_policy_data['round_type_end'] );

		if ( isset($round_policy_data['round_lunch_total']) ) {
			$rpf->setRoundLunchTotal( TRUE );
		} else {
			$rpf->setRoundLunchTotal( FALSE );
		}

		if ( isset($round_policy_data['round_total']) ) {
			$rpf->setRoundTotal( TRUE );
		} else {
			$rpf->setRoundTotal( FALSE );
		}

		if ( isset($round_policy_data['enable_bank_time']) ) {
			$rpf->setEnableBankTime( TRUE );
		} else {
			$rpf->setEnableBankTime( FALSE );
		}

		if ( isset($round_policy_data['over_time_default']) ) {
			$rpf->setOverTimeDefault( $round_policy_data['over_time_default'] );
		}

		if ( isset($round_policy_data['under_time_default']) ) {
			$rpf->setUnderTimeDefault( $round_policy_data['under_time_default'] );
		}

		if ( $rpf->isValid() ) {
			$rpf->Save();

			Redirect::Page( URLBuilder::getURL(NULL, 'RoundPolicyList.php') );

			break;
		}
	default:
		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$rplf = TTnew( 'RoundPolicyListFactory' );

			$rplf->getByIdAndCompanyId($id, $current_company->getId() );

			foreach ($rplf as $round_policy_obj) {
				//Debug::Arr($branch,'branch', __FILE__, __LINE__, __METHOD__,10);

				$round_policy_data = array(
									'id' => $round_policy_obj->getId(),
									'name' => $round_policy_obj->getName(),
									'description' => $round_policy_obj->getDescription(),

									'default' => $round_policy_obj->getDefault(),

									'strict_start' => $round_policy_obj->getStrictStart(),
									'strict_lunch_start' => $round_policy_obj->getStrictLunchStart(),
									'strict_lunch_end' => $round_policy_obj->getStrictLunchEnd(),
									'strict_end' => $round_policy_obj->getStrictEnd(),

									'round_grace_start' => $round_policy_obj->getRoundGraceStart(),
									'round_grace_lunch_start' => $round_policy_obj->getRoundGraceLunchStart(),
									'round_grace_lunch_end' => $round_policy_obj->getRoundGraceLunchEnd(),
									'round_grace_end' => $round_policy_obj->getRoundGraceEnd(),

									'round_start' => $round_policy_obj->getRoundStart(),
									'round_lunch_start' => $round_policy_obj->getRoundLunchStart(),
									'round_lunch_end' => $round_policy_obj->getRoundLunchEnd(),
									'round_end' => $round_policy_obj->getRoundEnd(),

									'round_type_start' => $round_policy_obj->getRoundTypeStart(),
									'round_type_lunch_start' => $round_policy_obj->getRoundTypeLunchStart(),
									'round_type_lunch_end' => $round_policy_obj->getRoundTypeLunchEnd(),
									'round_type_end' => $round_policy_obj->getRoundTypeEnd(),

									'round_lunch_total' => $round_policy_obj->getRoundLunchTotal(),
									'round_total' => $round_policy_obj->getRoundTotal(),

									'enable_bank_time' => $round_policy_obj->getEnableBankTime(),
									'over_time_default' => $round_policy_obj->getOverTimeDefault(),
									'under_time_default' => $round_policy_obj->getUnderTimeDefault(),

									'created_date' => $round_policy_obj->getCreatedDate(),
									'created_by' => $round_policy_obj->getCreatedBy(),
									'updated_date' => $round_policy_obj->getUpdatedDate(),
									'updated_by' => $round_policy_obj->getUpdatedBy(),
									'deleted_date' => $round_policy_obj->getDeletedDate(),
									'deleted_by' => $round_policy_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			//Set defaults.
				$round_policy_data = array(

									'round_grace_start' => 0,
									'round_grace_lunch_start' => 0,
									'round_grace_lunch_end' => 0,
									'round_grace_end' => 0,

									'round_start' => 900,
									'round_lunch_start' => 60,
									'round_lunch_end' => 900,
									'round_end' => 900,

									'round_type_start' => 30,
									'round_type_lunch_start' => 10,
									'round_type_lunch_end' => 30,
									'round_type_end' => 10,

									'round_lunch_total' => TRUE,
									'round_total' => FALSE
								);

		}

		//Select box options;
		$round_type_options = $rpf->getOptions('round_type');
		$smarty->assign_by_ref('round_type_options', $round_type_options);

		$over_time_default_options = $rpf->getOptions('over_time_default');
		$smarty->assign_by_ref('over_time_default_options', $over_time_default_options );

		$under_time_default_options = $rpf->getOptions('under_time_default');
		$smarty->assign_by_ref('under_time_default_options', $under_time_default_options );

		$smarty->assign_by_ref('round_policy_data', $round_policy_data);

		break;
}

$smarty->assign_by_ref('rpf', $rpf);

$smarty->display('round_policy/EditRoundPolicy.tpl');
?>