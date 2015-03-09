<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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
 * Checks for any version updates...
 *
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$ttsc = new TimeTrexSoapClient();
if ( $ttsc->isUpdateNotifyEnabled() == TRUE ) {
	sleep( rand(0, 60) ); //Further randomize when calls are made.
	$clf = new CompanyListFactory();
	$clf->getAll();
	if ( $clf->getRecordCount() > 0 ) {
		$i = 0;
		foreach ( $clf as $c_obj ) {
			if ( $ttsc->getLocalRegistrationKey() == FALSE
					OR $ttsc->getLocalRegistrationKey() == '' ) {
				$ttsc->saveRegistrationKey();
			}

			//We must ensure that the data is up to date
			//Otherwise version check will fail.
			$ttsc->sendCompanyData( $c_obj->getId() );
			$ttsc->sendCompanyUserLocationData( $c_obj->getId() );
			$ttsc->sendCompanyUserCountData( $c_obj->getId() );
			$ttsc->sendCompanyVersionData( $c_obj->getId() );

			//Check for new license once it starts expiring.
			//Help -> About, checking for new versions also gets the updated license file.
			if ( $i == 0 AND getTTProductEdition() > TT_PRODUCT_COMMUNITY ) {
				if ( !isset($system_settings['license']) ) {
					$system_settings['license'] = NULL;
				}

				$license = new TTLicense();
				$license->checkLicenseFile( $system_settings['license'] );
			}

			//Only need to call this on the last company
			if ( $i == ( $clf->getRecordCount() - 1 ) ) {
				$latest_version = $ttsc->isLatestVersion( $c_obj->getId() );
				$latest_tax_engine_version = $ttsc->isLatestTaxEngineVersion( $c_obj->getId() );
				$latest_tax_data_version = $ttsc->isLatestTaxDataVersion( $c_obj->getId() );

				$sslf = new SystemSettingListFactory();
				$sslf->getByName('new_version');
				if ( $sslf->getRecordCount() == 1 ) {
					$obj = $sslf->getCurrent();
				} else {
					$obj = new SystemSettingListFactory();
				}
				$obj->setName( 'new_version' );

				if( $latest_version == FALSE
						OR $latest_tax_engine_version == FALSE
						OR $latest_tax_data_version == FALSE ) {
					$obj->setValue( 1 );
				} else {
					$obj->setValue( 0 );
				}

				if ( $obj->isValid() ) {
					$obj->Save();
				}
			}

			$i++;
		}
	}
} else {
	Debug::Text('Auto Update Notifications are disabled!', __FILE__, __LINE__, __METHOD__, 10);
}
Debug::writeToLog();
Debug::Display();
?>