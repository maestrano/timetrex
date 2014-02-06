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
 * $Id: CompanyUserCountFactory.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Company
 */
class CompanyUserCountFactory extends Factory {
	protected $table = 'company_user_count';
	protected $pk_sequence_name = 'company_user_count_id_seq'; //PK Sequence name
	function getCompany() {
		return $this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = trim($epoch);

		if 	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date'))
			) {

			if 	(	$epoch > 0 ) {
				$this->data['date_stamp'] = $epoch;

				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date'));
			}


		}

		return FALSE;
	}

	function getActiveUsers() {
		if ( isset($this->data['active_users']) ) {
			return $this->data['active_users'];
		}

		return FALSE;
	}
	function setActiveUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'active_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['active_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getInActiveUsers() {
		if ( isset($this->data['inactive_users']) ) {
			return $this->data['inactive_users'];
		}

		return FALSE;
	}
	function setInActiveUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'inactive_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['inactive_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDeletedUsers() {
		if ( isset($this->data['deleted_users']) ) {
			return $this->data['deleted_users'];
		}

		return FALSE;
	}
	function setDeletedUsers($value) {
		$value = (int)trim($value);

		if 	(	$this->Validator->isNumeric(	'deleted_users',
												$value,
												TTi18n::gettext('Incorrect value')) ) {

			$this->data['deleted_users'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		//$this->removeCache( $this->getId() );

		return TRUE;
	}

	//This table doesn't have any of these columns, so overload the functions.
	function getDeleted() {
		return FALSE;
	}
	function setDeleted($bool) {
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
