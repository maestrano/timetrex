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


/**
 * @package Modules\Install
 */
class InstallSchema_1059A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//For some reason some MySQL installs have duplicate indexes, so detect them here and try to delete them.
		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
			$message_recipient_indexes = array_keys( $this->db->MetaIndexes('message_recipient') );
			if ( is_array($message_recipient_indexes) ) {
				if ( array_search( 'message_recipient_id', $message_recipient_indexes ) !== FALSE ) {
					Debug::text('Dropping already existing index: message_recipient_id', __FILE__, __LINE__, __METHOD__, 9);
					$this->db->Execute('DROP INDEX message_recipient_id ON message_recipient');
				} else {
					Debug::text('NOT Dropping already existing index: message_recipient_id', __FILE__, __LINE__, __METHOD__, 9);
				}
			}
			unset($message_recipient_indexes);

			$message_sender_indexes = array_keys( $this->db->MetaIndexes('message_sender') );
			if ( is_array($message_sender_indexes) ) {
				if ( array_search( 'message_sender_id', $message_sender_indexes ) !== FALSE ) {
					Debug::text('Dropping already existing index: message_sender_id', __FILE__, __LINE__, __METHOD__, 9);
					$this->db->Execute('DROP INDEX message_sender_id ON message_sender');
				} else {
					Debug::text('NOT Dropping already existing index: message_sender_id', __FILE__, __LINE__, __METHOD__, 9);
				}
			}
			unset($message_sender_indexes);

			$message_control_indexes = array_keys( $this->db->MetaIndexes('message_control') );
			if ( is_array($message_control_indexes) ) {
				if ( array_search( 'message_control_id', $message_control_indexes ) !== FALSE ) {
					Debug::text('Dropping already existing index: message_control_id', __FILE__, __LINE__, __METHOD__, 9);
					$this->db->Execute('DROP INDEX message_control_id ON message_control');
				} else {
					Debug::text('NOT Dropping already existing index: message_control_id', __FILE__, __LINE__, __METHOD__, 9);
				}
			}
			unset($message_control_indexes);

			$system_log_detail_indexes = array_keys( $this->db->MetaIndexes('system_log_detail') );
			if ( is_array($system_log_detail_indexes) ) {
				if ( array_search( 'system_log_detail_id', $system_log_detail_indexes ) !== FALSE ) {
					Debug::text('Dropping already existing index: system_log_detail_id', __FILE__, __LINE__, __METHOD__, 9);
					$this->db->Execute('DROP INDEX system_log_detail_id ON system_log_detail');
				} else {
					Debug::text('NOT Dropping already existing index: system_log_detail_id', __FILE__, __LINE__, __METHOD__, 9);
				}
			}
			unset($system_log_detail_indexes);
		}

		return TRUE;
	}

	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}
}
?>
