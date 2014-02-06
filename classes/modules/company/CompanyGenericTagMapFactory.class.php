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
 * $Id: PolicyGroupAccrualPolicyFactory.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Modules\Policy
 */
class CompanyGenericTagMapFactory extends Factory {
	protected $table = 'company_generic_tag_map';
	protected $pk_sequence_name = 'company_generic_tag_map_id_seq'; //PK Sequence name

	protected $tag_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$cgtf = TTnew('CompanyGenericTagFactory');
				$retval = $cgtf->getOptions( $name );
				break;
		}

		return $retval;
	}

	function getTagObject() {
		if ( is_object($this->tag_obj) ) {
			return $this->tag_obj;
		} else {
			$cgtlf = TTnew( 'CompanyGenericTagListFactory' );
			$this->tag_obj = $cgtlf->getById( $this->getTagID() )->getCurrent();

			return $this->tag_obj;
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return $this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function getObjectID() {
		if ( isset($this->data['object_id']) ) {
			return $this->data['object_id'];
		}

		return FALSE;
	}
	function setObjectID($id) {
		$id = trim($id);

		$pglf = TTnew( 'PolicyGroupListFactory' );

		if ( $this->Validator->isNumeric(	'object_id',
										$id,
										TTi18n::gettext('Object ID is invalid')
										) ) {
			$this->data['object_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTagID() {
		if ( isset($this->data['tag_id']) ) {
			return $this->data['tag_id'];
		}

		return FALSE;
	}
	function setTagID($id) {
		$id = trim($id);

		if ( $this->Validator->isNumeric(	'tag_id',
										$id,
										TTi18n::gettext('Tag ID is invalid')
										) ) {
			$this->data['tag_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	static function setTags( $company_id, $object_type_id, $object_id, $tags ) {
		Debug::text('Setting Tags: Company: '. $company_id .' Object Type: '. $object_type_id .' Object: '. $object_type_id .' Tags: '. $tags, __FILE__, __LINE__, __METHOD__, 10);

		if ( $object_id > 0 ) {
			//Parse tags
			$parsed_tags = CompanyGenericTagFactory::parseTags( $tags );
			if ( is_array($parsed_tags) ) {

				$existing_tags = CompanyGenericTagFactory::getOrCreateTags( $company_id, $object_type_id, $parsed_tags );
				$existing_tag_ids = array_values( (array)$existing_tags );
				//Debug::Arr($existing_tags, 'Existing Tags: ', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($existing_tag_ids, 'Existing Tag IDs: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get list of mapped Tag IDs that need to be deleted.
				if ( isset($parsed_tags['delete']) ) {
					foreach( $parsed_tags['delete'] as $del_tag ) {
						$del_tag = strtolower($del_tag);
						if ( isset($existing_tags[$del_tag]) AND $existing_tags[$del_tag] > 0 ) {
							$del_tag_ids[] = $existing_tags[$del_tag];
						}
					}
				}

				//If needed, delete mappings first.
				$cgtmlf = TTnew( 'CompanyGenericTagMapListFactory' );
				$cgtmlf->getByCompanyIDAndObjectTypeAndObjectID( $company_id, $object_type_id, $object_id );

				$tmp_ids = array();
				foreach ( $cgtmlf as $obj ) {
					$id = $obj->getTagID();
					Debug::text('Object Type ID: '. $object_type_id .' Object ID: '. $obj->getObjectID() .' Tag ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					if ( isset($del_tag_ids) AND in_array($id, $del_tag_ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
				//Debug::Arr($tmp_ids, 'TMP Ids: ', __FILE__, __LINE__, __METHOD__, 10);

				//Add new tags.
				if ( isset($parsed_tags['add']) ) {
					foreach( $parsed_tags['add'] as $add_tag ) {
						$add_tag = strtolower($add_tag);
						if ( isset($existing_tags[$add_tag]) AND $existing_tags[$add_tag] > 0 AND !in_array($existing_tags[$add_tag], $tmp_ids) ) {
							$cgtmf = TTnew('CompanyGenericTagMapFactory');
							$cgtmf->setObjectType( $object_type_id );
							$cgtmf->setObjectID( $object_id );
							$cgtmf->setTagID( $existing_tags[strtolower($add_tag)] );
							if ( $cgtmf->isValid() ) {
								$cgtmf->Save();
							}
						}
					}
				}
			}
		} else {
			Debug::Text('Object ID not set, skipping tags!', __FILE__, __LINE__, __METHOD__, 10);
		}

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
		$retval = FALSE;
		if ( $this->getObjectType() > 0 ) {
			//Get Tag name.
			$description = TTi18n::getText('Tag');
			if ( is_object( $this->getTagObject() ) ) {
				$description .= ': '. $this->getTagObject()->getName();
			}

			switch( $this->getObjectType() ) {
/*
										100 => 'company',
										110 => 'branch',
										120 => 'department',
										130 => 'stations',
										140 => 'hierarchy',
										150 => 'request',
										160 => 'message',
										170 => 'policy_group',

										200 => 'users',
										210 => 'user_wage',
										220 => 'user_title',

										300 => 'pay_stub_amendment',

										400 => 'schedule',
										410 => 'recurring_schedule_template',

										500 => 'report',
										510 => 'report_schedule',

										600 => 'job',
										610 => 'job_item',

										700 => 'document',

										800 => 'client',
										810 => 'client_contact',
										820 => 'client_payment',

										900 => 'product',
										910 => 'invoice',

*/
				case 100:
					$lf = TTnew( 'CompanyListFactory' );
					$lf->getById( $this->getObjectId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description = ' - '.TTi18n::getText('Company').': '. $lf->getCurrent()->getName();
					}

					Debug::text('Action: '. $log_action .' TagID: '. $this->getTagID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'company' );
					break;
				case 200:
					$lf = TTnew( 'UserListFactory' );
					$lf->getById( $this->getObjectId() );
					if ( $lf->getRecordCount() > 0 ) {
						$description .= ' - '.TTi18n::getText('Employee').': '. $lf->getCurrent()->getFullName();
					}

					Debug::text('Action: '. $log_action .' TagID: '. $this->getTagID() .' ObjectID: '. $this->getObjectID() .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
					$retval = TTLog::addEntry( $this->getObjectId(), $log_action, $description, NULL, 'users' );
					break;
			}
		}

		return $retval;
	}

}
?>
