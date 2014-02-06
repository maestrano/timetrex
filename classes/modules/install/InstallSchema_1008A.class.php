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
 * $Revision: 8371 $
 * $Id: InstallSchema_1008A.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1008A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}


	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		$clf = TTnew( 'CompanyListFactory' );
		$clf->StartTransaction();
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				if ( $c_obj->getStatus() == 10 ) {
					$ulf = TTnew( 'UserListFactory' );

					$ulf->getHighestEmployeeNumberByCompanyId( $c_obj->getId() );
					if ( $ulf->getRecordCount() > 0 ) {
						$next_available_employee_number = $ulf->getCurrent()->getEmployeeNumber()+1;
					} else {
						$next_available_employee_number = 1;
					}

					$ulf->getByCompanyId( $c_obj->getId(), NULL, NULL, NULL, array('hire_date' => 'asc') );
					if ( $ulf->getRecordCount() > 0 ) {
						foreach( $ulf as $u_obj ) {
							if ( $u_obj->getEmployeeNumber() == '' ) {
								Debug::text('Setting Employee Number to: '. $next_available_employee_number .' for '. $u_obj->getUserName(), __FILE__, __LINE__, __METHOD__,9);

								$u_obj->setEmployeeNumber( $next_available_employee_number );
								if ( $u_obj->isValid() ) {
									$u_obj->Save();
									$next_available_employee_number++;
								}
							} else {
								Debug::text('NOT Setting Employee Number for '. $u_obj->getUserName() .' already set to: '. $u_obj->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__,9);
							}
						}
					}
				}
			}
		}
		//$clf->FailTransaction();
		$clf->CommitTransaction();

		return TRUE;

	}
}
?>
