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
 * $Id: EditUserDefault.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','edit') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'New Hire Defaults')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'user_data',
												'data_saved'
												) ) );

if ( isset($user_data) ) {
		if ( isset($user_data['hire_date']) AND $user_data['hire_date'] != '') {
			$user_data['hire_date'] = TTDate::parseDateTime($user_data['hire_date']);
		}
}

$uf = TTnew( 'UserFactory' );
$upf = TTnew( 'UserPreferenceFactory' );
$udlf = TTnew( 'UserDefaultListFactory' );
$udf = TTnew( 'UserDefaultFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
        //Debug::setVerbosity(11);

		if ( isset($user_data['id']) AND $user_data['id'] != '' ) {
			$udf->setId( $user_data['id'] );
		}
		$udf->setCompany( $current_company->getId() );
		$udf->setTitle($user_data['title_id']);
		$udf->setCity($user_data['city']);
		$udf->setCountry($user_data['country']);
        $udf->setProvince($user_data['province']);
		$udf->setWorkPhone($user_data['work_phone']);
		$udf->setWorkPhoneExt($user_data['work_phone_ext']);
		$udf->setWorkEmail($user_data['work_email']);
		$udf->setPayPeriodSchedule( $user_data['pay_period_schedule_id'] );
		$udf->setPolicyGroup( $user_data['policy_group_id'] );
        $udf->setCurrency( $user_data['currency_id'] );

        if ( $permission->Check('permission','edit') AND isset($user_data['permission_control_id']) ) {
            $udf->setPermissionControl( $user_data['permission_control_id'] );
        }

		$udf->setHireDate( $user_data['hire_date'] );
		$udf->setEmployeeNumber( $user_data['employee_number'] );
		$udf->setDefaultBranch( $user_data['default_branch_id'] );
		$udf->setDefaultDepartment( $user_data['default_department_id'] );
		$udf->setLanguage( $user_data['language'] );
		if ($user_data['language']=== 'en'){
		$udf->setDateFormat( $user_data['date_format'] );
		}else{
			$udf->setDateFormat( $user_data['other_date_format'] );
		}
		$udf->setTimeFormat( $user_data['time_format']);
		$udf->setTimeUnitFormat( $user_data['time_unit_format'] );
		$udf->setTimeZone( $user_data['time_zone'] );
		$udf->setItemsPerPage( $user_data['items_per_page'] );
        $udf->setStartWeekDay( $user_data['start_week_day'] );

		if ( isset($user_data['enable_email_notification_exception']) ) {
			$udf->setEnableEmailNotificationException( TRUE );
		} else {
			$udf->setEnableEmailNotificationException( FALSE );
		}

		if ( isset($user_data['enable_email_notification_message']) ) {
			$udf->setEnableEmailNotificationMessage( TRUE );
		} else {
			$udf->setEnableEmailNotificationMessage( FALSE );
		}

		if ( isset($user_data['enable_email_notification_home']) ) {
			$udf->setEnableEmailNotificationHome( TRUE );
		} else {
			$udf->setEnableEmailNotificationHome( FALSE );
		}

		if ( $udf->isValid() ) {
			$udf->Save(FALSE);

			if ( isset($user_data['company_deduction_ids'] ) ) {
					$udf->setCompanyDeduction( $user_data['company_deduction_ids'] );
			} else {
					$udf->setCompanyDeduction( array() );
			}

			if ( $udf->isValid() ) {
				$udf->Save(FALSE);

				Redirect::Page( URLBuilder::getURL( array('id' => $user_data['id'], 'data_saved' => TRUE), 'EditUserDefault.php') );
				break;
			}
		}
	default:
		if ( $action !== 'submit' ) {
			Debug::Text('ID IS set', __FILE__, __LINE__, __METHOD__,10);

			BreadCrumb::setCrumb($title);

			$udlf->getByCompanyId($current_company->getId() );

			foreach ($udlf as $user) {
				//Debug::Arr($user,'User', __FILE__, __LINE__, __METHOD__,10);
                $user_title = NULL;
				if ( is_object( $user->getTitleObject() )  ) {
					$user_title = $user->getTitleObject()->getName();
				}
				Debug::Text('Title: '. $user_title , __FILE__, __LINE__, __METHOD__,10);

				$user_data = array(
                                        'id' => $user->getId(),
                                        'company' => $user->getCompany(),
                                        'title_id' => $user->getTitle(),
                                        'title' => $user_title,
                                        'employee_number' => $user->getEmployeeNumber(),
                                        'city' => $user->getCity(),
                                        'province' => $user->getProvince(),
                                        'country' => $user->getCountry(),
                                        'work_phone' => $user->getWorkPhone(),
                                        'work_phone_ext' => $user->getWorkPhoneExt(),
                                        'work_email' => $user->getWorkEmail(),
                                        'hire_date' => $user->getHireDate(),
                                        'default_branch_id' => $user->getDefaultBranch(),
                                        'default_department_id' => $user->getDefaultDepartment(),
                                        'currency_id' => $user->getCurrency(),
                                        'permission_control_id' => $user->getPermissionControl(),
                                        'pay_period_schedule_id' => $user->getPayPeriodSchedule(),
                                        'policy_group_id' => $user->getPolicyGroup(),

                                        'company_deduction_ids' => $user->getCompanyDeduction(),

                                        'language' => $user->getLanguage(),
                                        'date_format' => $user->getDateFormat(),
                                        'other_date_format' => $user->getDateFormat(),
                                        'time_format' => $user->getTimeFormat(),
                                        'time_zone' => $user->getTimeZone(),
                                        'time_unit_format' => $user->getTimeUnitFormat(),
                                        'items_per_page' => $user->getItemsPerPage(),
                                        'start_week_day' => $user->getStartWeekDay(),
                                        'enable_email_notification_exception' => $user->getEnableEmailNotificationException(),
                                        'enable_email_notification_message' => $user->getEnableEmailNotificationMessage(),
                                        'enable_email_notification_home' => $user->getEnableEmailNotificationHome(),


                                        'created_date' => $user->getCreatedDate(),
                                        'created_by' => $user->getCreatedBy(),
                                        'updated_date' => $user->getUpdatedDate(),
                                        'updated_by' => $user->getUpdatedBy(),
                                        'deleted_date' => $user->getDeletedDate(),
                                        'deleted_by' => $user->getDeletedBy()
								);
			}

			if ( !isset($user_data) ) {
				$user_data = array(
							'items_per_page' => 10,
							'time_zone' => 'GMT',
                            'country' => 'CA',
							'language' => 'en',
								);
			}
		}
		//Select box options;
		$blf = TTnew( 'BranchListFactory' );
		$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

		$dlf = TTnew( 'DepartmentListFactory' );
		$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

		$culf = TTnew( 'CurrencyListFactory' );
        $culf->getByCompanyId( $current_company->getId() );
		$currency_options = $culf->getArrayByListFactory( $culf, FALSE, TRUE );

		//Select box options;
		$user_data['branch_options'] = $branch_options;
		$user_data['department_options'] = $department_options;
      	$user_data['currency_options'] = $currency_options;


		$cf = TTnew( 'CompanyFactory' );
		$user_data['country_options'] = $cf->getOptions('country');
		$user_data['province_options'] = $cf->getOptions('province', $user_data['country'] );

		$utlf = TTnew( 'UserTitleListFactory' );
		$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );
		$user_data['title_options'] = $user_titles;

		//Get Permission Groups
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getByCompanyId( $current_company->getId() );
		$user_data['permission_control_options'] = $pclf->getArrayByListFactory( $pclf, FALSE );

		//Get pay period schedules
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$pay_period_schedules = $ppslf->getByCompanyIDArray( $current_company->getId() );
		$user_data['pay_period_schedule_options'] = $pay_period_schedules;

		$pglf = TTnew( 'PolicyGroupListFactory' );
		$policy_groups = $pglf->getByCompanyIDArray( $current_company->getId() );
		$user_data['policy_group_options'] = $policy_groups;

		$user_data['company'] = $current_company->getName();

		$user_data['language_options'] = TTi18n::getLanguageArray();
		$user_data['date_format_options'] = $upf->getOptions('date_format');
		$user_data['other_date_format_options'] = $upf->getOptions('other_date_format');
		$user_data['time_format_options'] = $upf->getOptions('time_format');
		$user_data['time_unit_format_options'] = $upf->getOptions('time_unit_format');
		$user_data['timesheet_view_options'] = $upf->getOptions('timesheet_view');
		$user_data['start_week_day_options'] = $upf->getOptions('start_week_day');

		$timezone_options = Misc::prependArray( array(-1 => '---'), $upf->getOptions('time_zone') );
		$user_data['time_zone_options'] = $timezone_options;

        //Get all Company Deductions for drop down box.
        $cdlf = TTnew( 'CompanyDeductionListFactory' );
        $user_data['company_deduction_options'] = $cdlf->getByCompanyIdAndStatusIdArray( $current_company->getId(), 10, FALSE);

		$smarty->assign_by_ref('user_data', $user_data);
		$smarty->assign_by_ref('data_saved', $data_saved);

		break;
}

$smarty->assign_by_ref('udf', $udf);

$smarty->display('users/EditUserDefault.tpl');
?>