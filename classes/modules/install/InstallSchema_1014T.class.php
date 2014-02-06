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
 * $Revision: 2095 $
 * $Id: InstallSchema_1007A.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1014T extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}


	function postInstall() {
		global $config_vars;

		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		$sslf = TTnew( 'SystemSettingListFactory' );

		//
		// Tax Data Version
		//
		$sslf->getByName( 'tax_data_version' );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = TTnew( 'SystemSettingListFactory' );
		}

		$tax_data_version = '20100101';
		$obj->setName( 'tax_data_version' );
		$obj->setValue( $tax_data_version );
		if ( $obj->isValid() ) {
			Debug::text('Setting Tax Data Version to: '. $tax_data_version, __FILE__, __LINE__, __METHOD__,9);
			$obj->Save();
		}

		//
		// Tax Engine Version
		//
		$sslf->getByName( 'tax_engine_version' );
		if ( $sslf->getRecordCount() == 1 ) {
			$obj = $sslf->getCurrent();
		} else {
			$obj = TTnew( 'SystemSettingListFactory' );
		}

		$tax_engine_version = '1.0.14';
		$obj->setName( 'tax_engine_version' );
		$obj->setValue( $tax_engine_version );
		if ( $obj->isValid() ) {
			Debug::text('Setting Tax Engine Version to: '. $tax_engine_version, __FILE__, __LINE__, __METHOD__,9);
			$obj->Save();
		}

		return TRUE;

	}
}
?>
