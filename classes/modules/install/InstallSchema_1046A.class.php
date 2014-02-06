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
 * $Revision: 1246 $
 * $Id: InstallSchema_1001B.class.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1046A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}

	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		//Allow edit password/phone password permissions for all permission groups.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj ) {
				Debug::text('Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__,9);
				if ( $c_obj->getStatus() != 30 ) {
					$pclf = TTnew( 'PermissionControlListFactory' );
					$pclf->getByCompanyId( $c_obj->getId(), NULL, NULL, NULL, array( 'name' => 'asc' ) ); //Force order to prevent references to columns that haven't been created yet.
					if ( $pclf->getRecordCount() > 0 ) {
						foreach( $pclf as $pc_obj ) {
							Debug::text('Permission Group: '. $pc_obj->getName(), __FILE__, __LINE__, __METHOD__,9);
							$plf = TTnew( 'PermissionListFactory' );
							$plf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $c_obj->getId(), $pc_obj->getId(), 'user', 'edit_own');
							if ( $plf->getRecordCount() > 0 ) {
								Debug::text('Found permission group with user edit own enabled: '. $plf->getCurrent()->getValue(), __FILE__, __LINE__, __METHOD__,9);
								$pc_obj->setPermission( array('user' => array('edit_own_password' => TRUE, 'edit_own_phone_password' => TRUE) ) );
							} else {
								Debug::text('Permission group does NOT have user edit own report enabled...', __FILE__, __LINE__, __METHOD__,9);
							}
						}
					}

				}
			}
		}

		//Metaphoneize data
		$ulf = TTnew('UserListFactory');
		$ulf->getAll();
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {

				$ph = array(
							'first_name_metaphone' => $u_obj->getFirstNameMetaphone( $u_obj->setFirstNameMetaphone( $u_obj->getFirstName() ) ),
							'last_name_metaphone' => $u_obj->getLastNameMetaphone( $u_obj->setLastNameMetaphone( $u_obj->getLastName() ) ),
							'id' => (int)$u_obj->getId(),
						   );
				$query = 'update '. $ulf->getTable() .' set first_name_metaphone = ?, last_name_metaphone = ? where id = ?';
				$this->db->Execute( $query, $ph );
			}
		}

		$clf = TTnew('CompanyListFactory');
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj ) {

				$ph = array(
							'name_metaphone' => $c_obj->getNameMetaphone( $c_obj->setNameMetaphone( $c_obj->getName() ) ),
							'id' => (int)$c_obj->getId(),
						   );
				$query = 'update '. $clf->getTable() .' set name_metaphone = ? where id = ?';
				$this->db->Execute( $query, $ph );
			}
		}

		$blf = TTnew('BranchListFactory');
		$blf->getAll();
		if ( $blf->getRecordCount() > 0 ) {
			foreach( $blf as $b_obj ) {

				$ph = array(
							'name_metaphone' => $b_obj->getNameMetaphone( $b_obj->setNameMetaphone( $b_obj->getName() ) ),
							'id' => (int)$b_obj->getId(),
						   );
				$query = 'update '. $blf->getTable() .' set name_metaphone = ? where id = ?';
				$this->db->Execute( $query, $ph );
			}
		}

		$dlf = TTnew('DepartmentListFactory');
		$dlf->getAll();
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {

				$ph = array(
							'name_metaphone' => $d_obj->getNameMetaphone( $d_obj->setNameMetaphone( $d_obj->getName() ) ),
							'id' => (int)$d_obj->getId(),
						   );
				$query = 'update '. $dlf->getTable() .' set name_metaphone = ? where id = ?';
				$this->db->Execute( $query, $ph );
			}
		}


		//Add GeoCode cronjob to database to run every morning.
		$cjf = TTnew( 'CronJobFactory' );
		$cjf->setName('GeoCode');
		$cjf->setMinute('15');
		$cjf->setHour('2');
		$cjf->setDayOfMonth('*');
		$cjf->setMonth('*');
		$cjf->setDayOfWeek('*');
		$cjf->setCommand('GeoCode.php');
		$cjf->Save();

		return TRUE;
	}
}
?>
