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
 * @package Modules\Help
 */
class HelpGroupFactory extends Factory {
	protected $table = 'help_group';
	protected $pk_sequence_name = 'help_group_id_seq'; //PK Sequence name
	function getHelpGroupControl() {
		return (int)$this->data['help_group_control_id'];
	}
	function setHelpGroupControl($id) {
		$id = trim($id);
		
		$hgclf = TTnew( 'HelpGroupControlListFactory' );
		
		if ( $this->Validator->isResultSetWithRows(	'help_group_control',
													$hgclf->getByID($id),
													TTi18n::gettext('Help Group Control is invalid')
													) ) {
			$this->data['help_group_control_id'] = $id;
		
			return TRUE;
		}

		return FALSE;
	}

	function getHelp() {
		return (int)$this->data['help_id'];
	}
	function setHelp($id) {
		$id = trim($id);
		
		$hlf = TTnew( 'HelpListFactory' );
		
		if ( $this->Validator->isResultSetWithRows(	'help',
													$hlf->getByID($id),
													TTi18n::gettext('Help Entry is invalid')
															) ) {
			$this->data['help_id'] = $id;
		
			return TRUE;
		}

		return FALSE;
	}
	
	function getOrder() {
		return $this->data['order_value'];
	}
	function setOrder($value) {
		$value = trim($value);
				
		if ( $this->Validator->isNumeric(	'order',
											$value,
											TTi18n::gettext('Order is invalid')
													) ) {
			$this->data['order_value'] = $value;
		
			return TRUE;
		}

		return FALSE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {		
		return FALSE;
	}
	
	function getCreatedDate() {
		return FALSE;
	}
	function setCreatedDate($epoch = NULL) {
		return FALSE;		
	}
	function getCreatedBy() {
		return FALSE;
	}
	function setCreatedBy($id = NULL) {
		return FALSE;		
	}

	function getUpdatedDate() {
		return FALSE;
	}
	function setUpdatedDate($epoch = NULL) {
		return FALSE;		
	}
	function getUpdatedBy() {
		return FALSE;
	}
	function setUpdatedBy($id = NULL) {
		return FALSE;	
	}


	function getDeletedDate() {
		return FALSE;
	}
	function setDeletedDate($epoch = NULL) {		
		return FALSE;
	}
	function getDeletedBy() {
		return FALSE;
	}
	function setDeletedBy($id = NULL) {		
		return FALSE;
	}
}
?>
