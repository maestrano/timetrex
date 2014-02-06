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
 * $Revision: 2196 $
 * $Id: APIAbout.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Core
 */
class APIAbout extends APIFactory {
	protected $main_class = FALSE;

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}
    /**
	 * Get about data .
     *
	 */
    function getAboutData( $ytd = 0, $all_companies = FALSE ) {
        global $config_vars;

        $clf = new CompanyListFactory();
        $sslf = new SystemSettingListFactory();
		$system_settings = $sslf->getAllArray();
        $clf->getByID( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() == 1 ) {
			$primary_company = $clf->getCurrent();
		}
        $current_user = $this->getCurrentUserObject();
        if ( isset($primary_company) AND PRIMARY_COMPANY_ID == $current_user->getCompany() ) {
			$current_company = $primary_company;
		} else {
			$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();
		}

        //$current_user_prefs = $current_user->getUserPreferenceObject();
        $data = $system_settings;

        if ( isset($data['new_version']) AND $data['new_version'] == TRUE  ) {
            $data['new_version'] = TRUE;
        } else {
            $data['new_version'] = FALSE;
        }

        $data['product_edition'] = Option::getByKey( ( DEPLOYMENT_ON_DEMAND == TRUE ) ? $current_company->getProductEdition() : getTTProductEdition(), $current_company->getOptions('product_edition') );

        $data['application_name'] = APPLICATION_NAME;

        $data['organization_url'] = ORGANIZATION_URL;
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

        if ( isset($data['user_counts']) == FALSE ) {
            $data['user_counts'] = array();
        }

		$cjlf = TTnew( 'CronJobListFactory' );
		$cjlf->getMostRecentlyRun();
		if ( $cjlf->getRecordCount() > 0 ) {
			$cj_obj = $cjlf->getCurrent();
			$data['cron'] = array(
								'last_run_date' => ( $cj_obj->getLastRunDate() == FALSE ) ? TTi18n::getText('Never') : TTDate::getDate('DATE+TIME', $cj_obj->getLastRunDate() ),
								);
		}
        $data['show_license_data'] = FALSE;
		if ( ( ( DEPLOYMENT_ON_DEMAND == FALSE AND $current_company->getId() == 1 ) OR ( isset($config_vars['other']['primary_company_id']) AND $current_company->getId() == $config_vars['other']['primary_company_id'] ) ) AND getTTProductEdition() > 10 ) {

            if ( !isset($system_settings['license']) ) {
				$system_settings['license'] = NULL;
			}
            $data['show_license_data'] = TRUE;
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

		//Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__,10);
        return $this->returnHandler( $data );
    }

    function isNewVersionAvailable( $ytd = 0, $all_companies = FALSE ) {
        Debug::Text('Check For Update!', __FILE__, __LINE__, __METHOD__,10);

        $current_company = $this->getCurrentCompanyObject();

        $data = $this->getAboutData( $ytd, $all_companies );			

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
			$data['new_version'] = TRUE;
		} else {
			$obj->setValue( 0 );
			$data['new_version'] = FALSE;
		}

		if ( $obj->isValid() ) {
			$obj->Save();
		}

        return $this->returnHandler( $data );

    }

}
?>
