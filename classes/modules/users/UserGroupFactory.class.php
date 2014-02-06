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
 * $Id: UserGroupFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Users
 */
class UserGroupFactory extends Factory {
	protected $table = 'user_group';
	protected $pk_sequence_name = 'user_group_id_seq'; //PK Sequence name

	protected $fasttree_obj = NULL;

	protected $tmp_data = array();

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1000-name' => TTi18n::gettext('Name'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'created_by',
								'created_date',
								'updated_by',
								'updated_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'parent_id' => 'Parent',
										'name' => 'Name',
										'deleted' => 'Deleted',
 										);
		return $variable_function_map;
	}

	function getFastTreeObject() {

		if ( is_object($this->fasttree_obj) ) {
			return $this->fasttree_obj;
		} else {
			global $fast_tree_user_group_options;
			$this->fasttree_obj = new FastTree($fast_tree_user_group_options);

			return $this->fasttree_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Use this for completly editing a row in the tree
	//Basically "old_id".
	function getPreviousParent() {
		if ( isset($this->tmp_data['previous_parent_id']) ) {
			return $this->tmp_data['previous_parent_id'];
		}

		return FALSE;
	}
	function setPreviousParent($id) {

		$this->tmp_data['previous_parent_id'] = $id;

		return TRUE;
	}

	function getParent() {
		if ( isset($this->tmp_data['parent_id']) ) {
			return $this->tmp_data['parent_id'];
		}

		return FALSE;
	}
	function setParent($id) {

		$this->tmp_data['parent_id'] = $id;

		return TRUE;
	}

	function getName() {
		return $this->data['name'];
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is invalid'),
											2,50)
						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {

		if ( $this->isNew() == FALSE
				AND $this->getId() == $this->getParent() ) {
				$this->Validator->isTrue(	'parent',
											FALSE,
											TTi18n::gettext('Cannot re-parent group to itself')
											);
		} else {
			if ( $this->isNew() == FALSE ) {
				$this->getFastTreeObject()->setTree( $this->getCompany() );
				//$children_ids = array_keys( $this->getFastTreeObject()->getAllChildren( $this->getID(), 'RECURSE' ) );

				$children_ids = $this->getFastTreeObject()->getAllChildren( $this->getID(), 'RECURSE' );
				if ( is_array($children_ids) ) {
					$children_ids = array_keys( $children_ids );
				}

				if ( is_array($children_ids) AND in_array( $this->getParent(), $children_ids) == TRUE ) {
					Debug::Text(' Objects cant be re-parented to their own children...' , __FILE__, __LINE__, __METHOD__,10);
					$this->Validator->isTrue(	'parent',
												FALSE,
												TTi18n::gettext('Unable to change parent to a child of itself')
												);
				}
			}
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->isNew() ) {
			Debug::Text(' Setting Insert Tree TRUE ', __FILE__, __LINE__, __METHOD__,10);
			$this->insert_tree = TRUE;
		} else {
			Debug::Text(' Setting Insert Tree FALSE ', __FILE__, __LINE__, __METHOD__,10);
			$this->insert_tree = FALSE;
		}

		return TRUE;
	}


	//Must be postSave because we need the ID of the object.
	function postSave() {

		$this->StartTransaction();

		$this->getFastTreeObject()->setTree( $this->getCompany() );

		if ( $this->getDeleted() == TRUE ) {
			//FIXME: Get parent of this object, and re-parent all groups to it.
			$parent_id = $this->getFastTreeObject()->getParentId( $this->getId() );

			//Get items by group id.
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByCompanyIdAndGroupId( $this->getCompany(), $this->getId() );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach( $ulf as $obj ) {
					Debug::Text(' Re-Grouping Item: '. $obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					$obj->setGroup($parent_id);
					$obj->Save();
				}
			}

			$this->getFastTreeObject()->delete( $this->getId() );

			//Delete this group from station/job criteria
			$sugf = TTnew( 'StationUserGroupFactory' );

			$query = 'delete from '. $sugf->getTable() .' where group_id = '. (int)$this->getId();
			$this->db->Execute($query);

			//Job employee criteria
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1030, $this->getID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach( $cgmlf as $cgm_obj ) {
					Debug::text('Deleteing from Company Generic Map: '. $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
					$cgm_obj->Delete();
				}
			}

			$this->CommitTransaction();

			return TRUE;
		} else {

			$retval = TRUE;
			//if ( $this->getId() === FALSE ) {
			if ( $this->insert_tree === TRUE ) {
				Debug::Text(' Adding Node ', __FILE__, __LINE__, __METHOD__,10);

				//echo "Current ID: ".  $this->getID() ."<br>\n";
				//echo "Parent ID: ".  $this->getParent() ."<br>\n";

				//Add node to tree
				if ( $this->getFastTreeObject()->add( $this->getID(), $this->getParent() ) === FALSE ) {
					Debug::Text(' Failed adding Node ', __FILE__, __LINE__, __METHOD__,10);

					$this->Validator->isTrue(	'name',
												FALSE,
												TTi18n::gettext('Name is already in use')
												);
					$retval = FALSE;
				}
			} else {
				Debug::Text(' Editing Node ', __FILE__, __LINE__, __METHOD__,10);

				//Edit node.
				$retval = $this->getFastTreeObject()->move( $this->getID() , $this->getParent() );
			}

			if ( $retval === TRUE ) {
				$this->CommitTransaction();
			} else {
				$this->FailTransaction();
			}

			return $retval;
		}
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}


	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Group'), NULL, $this->getTable(), $this );
	}
}
?>
