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
 * $Revision: 9521 $
 * $Id: HierarchyObjectTypeFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Hierarchy
 */
class HierarchyObjectTypeFactory extends Factory {
	protected $table = 'hierarchy_object_type';
	protected $pk_sequence_name = 'hierarchy_object_type_id_seq'; //PK Sequence name

	var $hierarchy_control_obj = NULL;

	function _getFactoryOptions( $name ) {
		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
										//10 => TTi18n::gettext('Recurring Schedule'),
										//20 => TTi18n::gettext('Schedule Amendment'),
										//30 => TTi18n::gettext('Shift Amendment'),
										//40 => TTi18n::gettext('Pay Stub Amendment')
										//50 => TTi18n::gettext('Request'),

										//Add 1000 to request type_id's. Make sure no other objects pass 1000.
										1010 => TTi18n::gettext('Request: Missed Punch'),
										1020 => TTi18n::gettext('Request: Time Adjustment'),
										1030 => TTi18n::gettext('Request: Absence (incl. Vacation)'),
										1040 => TTi18n::gettext('Request: Schedule Adjustment'),
										1100 => TTi18n::gettext('Request: Other'),

										80 => TTi18n::gettext('Exception'),
										90 => TTi18n::gettext('TimeSheet'),
										100 => TTi18n::gettext('Permission'),
                                        200 => TTi18n::gettext('Expense'),
									);
				break;
			case 'short_object_type': //Defines a short form of the names.
				$retval = array(
										//10 => TTi18n::gettext('Recurring Schedule'),
										//20 => TTi18n::gettext('Schedule Amendment'),
										//30 => TTi18n::gettext('Shift Amendment'),
										//40 => TTi18n::gettext('Pay Stub Amendment')
										//50 => TTi18n::gettext('Request'),

										//Add 1000 to request type_id's. Make sure no other objects pass 1000.
										1010 => TTi18n::gettext('R:Missed Punch'),
										1020 => TTi18n::gettext('R:Adjustment'),
										1030 => TTi18n::gettext('R:Absence'),
										1040 => TTi18n::gettext('R:Schedule'),
										1100 => TTi18n::gettext('R:Other'),

										80 => TTi18n::gettext('Exception'),
										90 => TTi18n::gettext('TimeSheet'),
										100 => TTi18n::gettext('Permission'),
                                        200 => TTi18n::gettext('Expense'),
									);
				break;

		}

		return $retval;
	}

	function getHierarchyControlObject() {
		if ( is_object($this->hierarchy_control_obj) ) {
			return $this->hierarchy_control_obj;
		} else {
			$hclf = TTnew( 'HierarchyControlListFactory' );
			$this->hierarchy_control_obj = $hclf->getById( $this->getHierarchyControl() )->getCurrent();

			return $this->hierarchy_control_obj;
		}
	}

	function getHierarchyControl() {
		if ( isset($this->data['hierarchy_control_id']) ) {
			return $this->data['hierarchy_control_id'];
		}

		return FALSE;
	}
	function setHierarchyControl($id) {
		$id = trim($id);

		$hclf = TTnew( 'HierarchyControlListFactory' );
		Debug::Text('Hierarchy Control ID: '. $id, __FILE__, __LINE__, __METHOD__,10);

		if ( $id != 0
				OR $this->Validator->isResultSetWithRows(	'hierarchy_control_id',
															$hclf->getByID($id),
															TTi18n::gettext('Invalid Hierarchy Control')
															) ) {
			$this->data['hierarchy_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueObjectType($object_type) {
/*
		$company_id = $this->getHierarchyControlObject()->getCompany();

		$hotlf = TTnew( 'HierarchyObjectTypeListFactory' );
		$hotlf->getByCompanyId( $company_id );
		foreach ( $hotlf as $object_type_obj) {
			if ( $object_type_obj->getId() !== $this->getId() ) {
				$assigned_object_types[] = $object_type_obj->getObjectType();
			}
		}

		if ( isset($assigned_object_types) AND is_array($assigned_object_types) AND in_array( $object_type, $assigned_object_types) ){
			return FALSE;
		}
*/
		return TRUE;
	}

	function getObjectType() {
		return $this->data['object_type_id'];
	}
	function setObjectType($id) {
		$id = trim($id);

		$key = Option::getByValue($id, $this->getOptions('object_type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'object_type',
											$id,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('object_type'))
				AND
						$this->Validator->isTrue(		'object_type',
														$this->isUniqueObjectType($id),
														TTi18n::gettext('Object Type is already assigned to another hierarchy'))

			) {

			$this->data['object_type_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function postSave() {
		$cache_id = $this->getHierarchyControlObject()->getCompany().$this->getObjectType();
		$this->removeCache( $cache_id );

		return TRUE;
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

	function addLog( $log_action ) {
		$object_type = Option::getByKey($this->getObjectType(), Misc::TrimSortPrefix( $this->getOptions('object_type') ) );
		return TTLog::addEntry( $this->getHierarchyControl(), $log_action, TTi18n::getText('Object').': '. $object_type, NULL, $this->getTable() );
	}
}
?>
