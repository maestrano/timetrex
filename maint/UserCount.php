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
 * Counts the total active/inactive/deleted users for each company once a day.
 *
 */
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

$cuclf = new CompanyUserCountListFactory();
$cuclf->getActiveUsers();
if ( $cuclf->getRecordCount() > 0 ) {
	foreach( $cuclf as $cuc_obj ) {
		$user_counts[$cuc_obj->getColumn('company_id')]['active'] = $cuc_obj->getColumn('total');
	}
}

$cuclf->getInActiveUsers();
if ( $cuclf->getRecordCount() > 0 ) {
	foreach( $cuclf as $cuc_obj ) {
		$user_counts[$cuc_obj->getColumn('company_id')]['inactive'] = $cuc_obj->getColumn('total');
	}
}

$cuclf->getDeletedUsers();
if ( $cuclf->getRecordCount() > 0 ) {
	foreach( $cuclf as $cuc_obj ) {
		$user_counts[$cuc_obj->getColumn('company_id')]['deleted'] = $cuc_obj->getColumn('total');
	}
}

$cuclf->StartTransaction();
if ( isset($user_counts) AND count($user_counts) > 0 ) {
	foreach( $user_counts as $company_id => $user_count_arr) {

		$cucf = new CompanyUserCountFactory();
		$cucf->setCompany( $company_id );
		$cucf->setDateStamp( time() );
		if ( !isset($user_count_arr['active']) ) {
			$user_count_arr['active'] = 0;
		}
		$cucf->setActiveUsers( $user_count_arr['active'] );

		if ( !isset($user_count_arr['inactive']) ) {
			$user_count_arr['inactive'] = 0;
		}
		$cucf->setInActiveUsers( $user_count_arr['inactive'] );

		if ( !isset($user_count_arr['deleted']) ) {
			$user_count_arr['deleted'] = 0;
		}
		$cucf->setDeletedUsers( $user_count_arr['deleted']);

		Debug::text('Company ID: '. $company_id .' Active: '. $user_count_arr['active'] .' InActive: '. $user_count_arr['inactive'] .' Deleted: '. $user_count_arr['deleted'], __FILE__, __LINE__, __METHOD__, 10);

		if ( $cucf->isValid() ) {
			$cucf->Save();
		}
	}
}
$cuclf->CommitTransaction();

Debug::Display();
?>