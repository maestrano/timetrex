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
 * $Id: EditUserPreference.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('user_preference','enabled')
		OR !( $permission->Check('user_preference','edit') OR $permission->Check('user_preference','edit_child') OR $permission->Check('user_preference','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Preferences')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'user_id',
												'incomplete',
												'pref_data',
												'data_saved',
												) ) );

$hlf = TTnew( 'HierarchyListFactory' );
$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeId( $current_company->getId(), $current_user->getId() );
//Include current user in list.
if ( $permission->Check('user_preference','edit_own') ) {
	$permission_children_ids[] = $current_user->getId();
}

$upf = TTnew( 'UserPreferenceFactory' );
$ulf = TTnew( 'UserListFactory' );
$action = Misc::findSubmitButton('action');
switch ($action) {
	case 'submit':
		//Debug::setVerbosity( 11 );
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		if ( $pref_data['id'] != '' ) {
			$upf->setId( $pref_data['id'] );
		}

		if ( isset($pref_data['user_id']) AND $pref_data['user_id'] != '' ) {
			$upf->setUser( $pref_data['user_id'] );
		} else {
			$upf->setUser( $current_user->getId() );
		}

		$upf->setLanguage( $pref_data['language'] );
		if ( $pref_data['language'] == 'en' ) {
			$upf->setDateFormat( $pref_data['date_format'] );
		} else {
			$upf->setDateFormat( $pref_data['other_date_format'] );
		}

		$upf->setTimeFormat( $pref_data['time_format']);
		$upf->setTimeUnitFormat( $pref_data['time_unit_format'] );
		$upf->setTimeZone( $pref_data['time_zone'] );
		//$upf->setTimeSheetView( $pref_data['timesheet_view'] );
		$upf->setStartWeekDay( $pref_data['start_week_day'] );
		$upf->setItemsPerPage( $pref_data['items_per_page'] );

		if ( isset($pref_data['enable_email_notification_exception']) ) {
			$upf->setEnableEmailNotificationException( TRUE );
		} else {
			$upf->setEnableEmailNotificationException( FALSE );
		}

		if ( isset($pref_data['enable_email_notification_message']) ) {
			$upf->setEnableEmailNotificationMessage( TRUE );
		} else {
			$upf->setEnableEmailNotificationMessage( FALSE );
		}

		if ( isset($pref_data['enable_email_notification_home']) ) {
			$upf->setEnableEmailNotificationHome( TRUE );
		} else {
			$upf->setEnableEmailNotificationHome( FALSE );
		}

		if ( $upf->isValid() ) {
			$upf->Save( FALSE );

			if ( $current_user->getId() == $upf->getUser() ) {
				TTi18n::setLocaleCookie( $pref_data['language'].'_'.$current_user->getCountry() );
			}

			Redirect::Page( URLBuilder::getURL( array('user_id' => $pref_data['user_id'], 'data_saved' => 1), Environment::getBaseURL().'/users/EditUserPreference.php') );
			unset($upf);
			break;
		}
	default:
		if ( !isset($user_id) OR (isset($user_id) AND $user_id == '' ) ) {
			$user_id = $current_user->getId();
		}

		$ulf->getByIdAndCompanyId( $user_id, $current_company->getId() );
		if ( $ulf->getRecordCount() > 0 ) {
			$user_obj = $ulf->getCurrent();
		}

		if ( !isset($action) ) {
			BreadCrumb::setCrumb($title);

			$uplf = TTnew( 'UserPreferenceListFactory' );
			$uplf->getByUserIDAndCompanyID( $user_id, $current_company->getId() );

			if ( isset($user_obj) AND is_object( $user_obj) ) {
				$is_owner = $permission->isOwner( $user_obj->getCreatedBy(), $user_obj->getId() );
				$is_child = $permission->isChild( $user_obj->getId(), $permission_children_ids );

				if ( $permission->Check('user_preference','edit')
						OR ( $permission->Check('user_preference','edit_own') AND $is_owner === TRUE )
						OR ( $permission->Check('user_preference','edit_child') AND $is_child === TRUE ) ) {

					foreach ($uplf as $user_preference) {
							$pref_data = array(
												'id' => $user_preference->getId(),
												'user_id' => $user_preference->getUser(),
												'user_full_name' => $user_obj->getFullName(),
												'language' =>  $user_preference->getLanguage(),
												'date_format' => $user_preference->getDateFormat(),
												'other_date_format'=> $user_preference->getDateFormat(),
												'time_format' => $user_preference->getTimeFormat(),
												'time_zone' => $user_preference->getTimeZone(),
												'time_unit_format' => $user_preference->getTimeUnitFormat(),
												'timesheet_view' => $user_preference->getTimeSheetView(),
												'start_week_day' => $user_preference->getStartWeekDay(),
												'items_per_page' => $user_preference->getItemsPerPage(),
												'enable_email_notification_exception' => $user_preference->getEnableEmailNotificationException(),
												'enable_email_notification_message' => $user_preference->getEnableEmailNotificationMessage(),
												'enable_email_notification_home' => $user_preference->getEnableEmailNotificationHome(),
												'created_date' => $user_preference->getCreatedDate(),
												'created_by' => $user_preference->getCreatedBy(),
												'updated_date' => $user_preference->getUpdatedDate(),
												'updated_by' => $user_preference->getUpdatedBy(),
												'deleted_date' => $user_preference->getDeletedDate(),
												'deleted_by' => $user_preference->getDeletedBy()
											);
					}
				}
			}
		}

		if ( !isset($pref_data) AND isset($user_obj) ) {
			$udlf = TTnew( 'UserDefaultListFactory' );
			$udlf->getByCompanyId( $current_company->getId() );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text('Using User Defaults', __FILE__, __LINE__, __METHOD__,10);
				$udf_obj = $udlf->getCurrent();

				$pref_data = array(
								'user_id' => $user_obj->getId(),
								'user_full_name' => $user_obj->getFullName(),
								'language' =>  $udf_obj->getLanguage(),
								'date_format' => $udf_obj->getDateFormat(),
								'other_date_format' => $udf_obj->getDateFormat(),
								'time_format' => $udf_obj->getTimeFormat(),
								'time_zone' => $udf_obj->getTimeZone(),
								'time_unit_format' => $udf_obj->getTimeUnitFormat(),
								'start_week_day' => $udf_obj->getStartWeekDay(),
								'items_per_page' => $udf_obj->getItemsPerPage(),
								'enable_email_notification_exception' => $udf_obj->getEnableEmailNotificationException(),
								'enable_email_notification_message' => $udf_obj->getEnableEmailNotificationMessage(),
								'enable_email_notification_home' => $udf_obj->getEnableEmailNotificationHome(),
							);
			} else {
				$pref_data = array(
								'user_id' => $user_obj->getId(),
								'user_full_name' => $user_obj->getFullName(),
								'language' =>  'en',
								'time_unit_format' => 20, //Hours
								'items_per_page' => 25,
								'enable_email_notification_exception' => TRUE,
								'enable_email_notification_message' => TRUE,
								'enable_email_notification_home' => FALSE,
							);
			}
		}

		//Select box options;
		$pref_data['language_options'] = TTi18n::getLanguageArray();
		$pref_data['date_format_options'] = $upf->getOptions('date_format');
		$pref_data['other_date_format_options'] = $upf->getOptions('other_date_format');

		$pref_data['time_format_options'] = $upf->getOptions('time_format');
		$pref_data['time_unit_format_options'] = $upf->getOptions('time_unit_format');
		$pref_data['timesheet_view_options'] = $upf->getOptions('timesheet_view');
		$pref_data['start_week_day_options'] = $upf->getOptions('start_week_day');

		$timezone_options = Misc::prependArray( array(-1 => '---'), $upf->getOptions('time_zone') );
		$pref_data['time_zone_options'] = $timezone_options;

		$smarty->assign_by_ref('pref_data', $pref_data);
		$smarty->assign_by_ref('incomplete', $incomplete);
		$smarty->assign_by_ref('data_saved', $data_saved);
		break;
}
$smarty->assign_by_ref('upf', $upf);

$smarty->display('users/EditUserPreference.tpl');
?>