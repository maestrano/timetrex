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
 * $Revision: 3351 $
 * $Id: MessageFactory.class.php 3351 2010-02-18 17:22:09Z ipso $
 * $Date: 2010-02-18 09:22:09 -0800 (Thu, 18 Feb 2010) $
 */

/**
 * @package Modules\Message
 */
class MessageSenderFactory extends Factory {
	protected $table = 'message_sender';
	protected $pk_sequence_name = 'message_sender_id_seq'; //PK Sequence name
	protected $obj_handler = NULL;

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid Employee')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getParent() {
		if ( isset($this->data['parent_id']) ) {
			return $this->data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {
		$id = trim($id);

		if ( empty($id) ) {
			$id = 0;
		}

		$mslf = TTnew( 'MessageSenderListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'parent',
															$mslf->getByID($id),
															TTi18n::gettext('Parent is invalid')
															) ) {
			$this->data['parent_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getMessageControl() {
		if ( isset($this->data['message_control_id']) ) {
			return $this->data['message_control_id'];
		}

		return FALSE;
	}
	function setMessageControl($id) {
		$id = trim($id);

		$mclf = TTnew( 'MessageControlListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'message_control_id',
													$mclf->getByID($id),
													TTi18n::gettext('Message Control is invalid')
													) ) {
			$this->data['message_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		return TRUE;
	}
}
?>
