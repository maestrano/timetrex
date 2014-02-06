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
 * $Revision: 9743 $
 * $Id: About.php 9743 2013-05-02 21:22:23Z ipso $
 * $Date: 2013-05-02 14:22:23 -0700 (Thu, 02 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity( 11 );

$smarty->assign('title', TTi18n::gettext($title = 'About')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'ytd',
												'all_companies'
												) ) );

$data = $system_settings;

$action = Misc::findSubmitButton();
switch ($action) {
	case 'university':
		//Debug::setVerbosity( 11 );
		Debug::Text('Redirect to Online University!', __FILE__, __LINE__, __METHOD__,10);

		//Generate encoded data as well.
		$encoded_data = NULL;
		if ( is_object( $current_company ) AND is_object( $current_user ) ) {
			//If for some reason the registration key isn't set, generate a random one now.
			if ( isset($system_settings) AND ( !isset($system_settings['registration_key']) OR $system_settings['registration_key'] == '') ) {
				$sslf->setName('registration_key');
				$sslf->setValue( md5( uniqid() ) );
				if ( $sslf->isValid() == TRUE ) {
					$sslf->Save();
				}
			}

			//Get permissions of current user so we can show them the proper courses.
			$pf = TTnew( 'PermissionFactory' );
			$permission_sections = $pf->getOptions('section');
			foreach( $permission_sections as $section => $name ) {
				$permission_arr[$section] = $permission->Check( $section, 'enabled');
			}
			$encoded_data = array( 'company_name' => $current_company->getName(), 'product_edition' => $current_company->getProductEdition(), 'version' => $system_settings['system_version'], 'registration_key' => $system_settings['registration_key'], 'first_name' => $current_user->getFirstName(), 'last_name' => $current_user->getLastName(), 'work_email' => $current_user->getWorkEmail(), 'permissions' => $permission_arr );

			if ( function_exists('gzdeflate') ) {
				$encoded_data = urlencode( base64_encode( gzdeflate( serialize( $encoded_data ) ) ) );
			} else {
				$encoded_data = urlencode( base64_encode( serialize( $encoded_data )  ) );
			}
			//Debug::Text(' Encoded Data ('.strlen($encoded_data).'): '. $encoded_data, __FILE__, __LINE__, __METHOD__,10);
		}
		Redirect::Page( URLBuilder::getURL( array('data' => $encoded_data ), 'https://www.timetrex.com/university.php') );
		exit;

		break;
	case 'check_for_updates':
		Debug::Text('Check For Update!', __FILE__, __LINE__, __METHOD__,10);

		$ttsc = new TimeTrexSoapClient();

		//We must ensure that the data is up to date
		//Otherwise version check will fail.
		$ttsc->sendCompanyData( $current_company->getId(), TRUE );
		$ttsc->sendCompanyUserLocationData( $current_company->getId() );
		$ttsc->sendCompanyUserCountData( $current_company->getId() );
		$ttsc->sendCompanyVersionData( $current_company->getId() );

		$license = new TTLicense();
        $license->getLicenseFile( FALSE ); //Download updated license file if one exists.

		$latest_version = $ttsc->isLatestVersion( $current_company->getId() );
		$latest_tax_engine_version = $ttsc->isLatestTaxEngineVersion( $current_company->getId() );
		$latest_tax_data_version = $ttsc->isLatestTaxDataVersion( $current_company->getId() );

		$sslf = TTnew( 'SystemSettingListFactory' );
		$sslf->getByName('new_version');
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = TTnew( 'SystemSettingListFactory' );
		}
		$obj->setName( 'new_version' );

		if( $latest_version == FALSE
				OR $latest_tax_engine_version == FALSE
				OR $latest_tax_data_version == FALSE ) {
			$obj->setValue( 1 );
			$data['new_version'] = 1;
		} else {
			$obj->setValue( 0 );
			$data['new_version'] = 0;
		}

		if ( $obj->isValid() ) {
			$obj->Save();
		}
	default:
		$data['product_edition'] = Option::getByKey( ( DEPLOYMENT_ON_DEMAND == TRUE ) ? $current_company->getProductEdition() : getTTProductEdition(), $current_company->getOptions('product_edition') );

		//Get Employee counts for this month, and last month
		$month_of_year_arr = TTDate::getMonthOfYearArray();

		//This month
		if ( isset($ytd) AND $ytd == 1 ) {
			$begin_month_epoch = strtotime( '-2 years' );
		} else {
			$begin_month_epoch = TTDate::getBeginMonthEpoch(TTDate::getBeginMonthEpoch(time())-86400);
		}

		$cuclf = TTnew( 'CompanyUserCountListFactory' );
		if ( isset($config_vars['other']['primary_company_id']) AND $current_company->getId() == $config_vars['other']['primary_company_id'] AND $all_companies == TRUE ) {
			$cuclf->getTotalMonthlyMinAvgMaxByCompanyStatusAndStartDateAndEndDate( 10, $begin_month_epoch, TTDate::getEndMonthEpoch( time() ), NULL, NULL, NULL, array('date_stamp' => 'desc') );
		} else {
			$cuclf->getMonthlyMinAvgMaxByCompanyIdAndStartDateAndEndDate( $current_company->getId(), $begin_month_epoch, TTDate::getEndMonthEpoch( time() ), NULL, NULL, NULL, array('date_stamp' => 'desc') );
		}
		Debug::Text('Company User Count Rows: '. $cuclf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach( $cuclf as $cuc_obj ) {
				$data['user_counts'][] = array(
																//'label' => $month_of_year_arr[TTDate::getMonth( $begin_month_epoch )] .' '. TTDate::getYear($begin_month_epoch),
																'label' => $month_of_year_arr[TTDate::getMonth( TTDate::strtotime( $cuc_obj->getColumn('date_stamp') ) )] .' '. TTDate::getYear( TTDate::strtotime( $cuc_obj->getColumn('date_stamp') ) ),
																'max_active_users' => $cuc_obj->getColumn('max_active_users'),
																'max_inactive_users' => $cuc_obj->getColumn('max_inactive_users'),
																'max_deleted_users' => $cuc_obj->getColumn('max_deleted_users'),
																);
			}
		}

		$cjlf = TTnew( 'CronJobListFactory' );
		$cjlf->getMostRecentlyRun();
		if ( $cjlf->getRecordCount() > 0 ) {
			$cj_obj = $cjlf->getCurrent();
			$data['cron'] = array(
								'last_run_date' => $cj_obj->getLastRunDate()
								);
		}

		if ( ( ( DEPLOYMENT_ON_DEMAND == FALSE AND $current_company->getId() == 1 ) OR ( isset($config_vars['other']['primary_company_id']) AND $current_company->getId() == $config_vars['other']['primary_company_id'] ) ) AND getTTProductEdition() > 10 ) {
			if ( !isset($system_settings['license']) ) {
				$system_settings['license'] = NULL;
			}

			//Set this so the license upload area at least shows up regardles of edition.
			$data['license_data'] = array();

			$license = new TTLicense();
			$retval = $license->validateLicense( $system_settings['license'] );
			if ( $retval == TRUE ) {
				$data['license_data'] = array(
										'organization_name' => $license->getOrganizationName(),
										'major_version' => $license->getMajorVersion(),
										'minor_version' => $license->getMinorVersion(),
										'product_name' => $license->getProductName(),
										'active_employee_licenses' => $license->getActiveEmployeeLicenses(),
										'issue_date' => TTDate::getDate('DATE', $license->getIssueDate() ),
										'expire_date' => $license->getExpireDate(),
										'expire_date_display' => TTDate::getDate('DATE', $license->getExpireDate() ),
										'registration_key' => $license->getRegistrationKey(),
										'message' => $license->getFullErrorMessage( $retval ),
										'retval' => $retval,
										);
			}
		}
}

//var_dump($data);
$smarty->assign_by_ref('data', $data);

$smarty->display('help/About.tpl');
?>