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
 * $Revision: 4252 $
 * $Id: TTLog.class.php 4252 2011-02-16 01:19:08Z ipso $
 * $Date: 2011-02-15 17:19:08 -0800 (Tue, 15 Feb 2011) $
 */

/**
 * @package Core
 */
class TTLog {
	static function addEntry( $object_id, $action_id, $description, $user_id, $table, $object = NULL ) {
		global $config_vars;

		if ( isset($config_vars['other']['disable_audit_log']) AND $config_vars['other']['disable_audit_log'] == TRUE ) {
			return TRUE;
		}

		if ( !is_numeric($object_id) ) {
			return FALSE;
		}

		if ( $action_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			global $current_user;
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				$user_id = 0;
			}
		}

		if ( $table == '' ) {
			return FALSE;
		}

		$lf = TTnew( 'LogFactory' );

		$lf->setObject( $object_id );
		$lf->setAction( $action_id );
		$lf->setTableName( $table );
		$lf->setUser( (int)$user_id );
		$lf->setDescription( $description );

		//Debug::text('Object ID: '. $object_id .' Action ID: '. $action_id .' Table: '. $table .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
		if ( $lf->isValid() === TRUE ) {
			$insert_id = $lf->Save();

			if ( 	(
					!isset($config_vars['other']['disable_audit_log_detail'])
						OR ( isset($config_vars['other']['disable_audit_log_detail']) AND $config_vars['other']['disable_audit_log_detail'] != TRUE )
					)
					AND is_object($object) AND $object->getEnableSystemLogDetail() == TRUE ) {

				$ldf = TTnew( 'LogDetailFactory' );
				$ldf->addLogDetail( $action_id, $insert_id, $object );
			} else {
				Debug::text('LogDetail Disabled... Object ID: '. $object_id .' Action ID: '. $action_id .' Table: '. $table .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('LogDetail Disabled... Config: '. (int)$config_vars['other']['disable_audit_log_detail'] .' Function: '. (int)$object->getEnableSystemLogDetail(), __FILE__, __LINE__, __METHOD__, 10);
			}

			return TRUE;
		}

		return FALSE;
	}
}
?>
