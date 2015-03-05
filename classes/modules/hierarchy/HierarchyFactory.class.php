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
 * @package Modules\Hierarchy
 */
class HierarchyFactory extends Factory {

	protected $table = 'hierarchy'; //Used for caching purposes only.

	protected $fasttree_obj = NULL;
	//protected $tmp_data = array(); //Tmp data.
	function getFastTreeObject() {

		if ( is_object($this->fasttree_obj) ) {
			return $this->fasttree_obj;
		} else {
			global $fast_tree_options;
			$this->fasttree_obj = new FastTree($fast_tree_options);

			return $this->fasttree_obj;
		}
	}

	function getId() {
		if ( isset($this->data['id']) ) {
			return $this->data['id'];
		}

		return FALSE;
	}
	function setId($id) {

		$this->data['id'] = $id;

		return TRUE;
	}

	function getHierarchyControl() {
		if ( isset($this->data['hierarchy_control_id']) ) {
			return (int)$this->data['hierarchy_control_id'];
		}

		return FALSE;
	}
	function setHierarchyControl($id) {

		$this->data['hierarchy_control_id'] = $id;

		return TRUE;
	}

	//Use this for completly editing a row in the tree
	//Basically "old_id".
	function getPreviousUser() {
		if ( isset($this->data['previous_user_id']) ) {
			return (int)$this->data['previous_user_id'];
		}

		return FALSE;
	}
	function setPreviousUser($id) {

		$this->data['previous_user_id'] = $id;

		return TRUE;
	}

	function getParent() {
		if ( isset($this->data['parent_user_id']) ) {
			return (int)$this->data['parent_user_id'];
		}

		return FALSE;
	}
	function setParent($id) {

		$this->data['parent_user_id'] = $id;

		return TRUE;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {

		$this->data['user_id'] = $id;

		return TRUE;
	}

	function getShared() {
		if ( isset( $this->data['shared'] ) ) {
			return $this->fromBool( $this->data['shared'] );
		}

		return FALSE;
	}
	function setShared($bool) {
		$this->data['shared'] = $this->toBool($bool);

		return TRUE;
	}


	function Validate() {

		if ( $this->getUser() == $this->getParent() ) {
				$this->Validator->isTrue(	'parent',
											FALSE,
											TTi18n::gettext('User is the same as parent')
											);
		}

		//Make sure both user and parent belong to the same company
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $this->getUser() );
		$user = $ulf->getIterator()->current();
		unset($ulf);

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $this->getParent() );
		$parent = $ulf->getIterator()->current();
		unset($ulf);


		if ( $this->getUser() == 0 AND $this->getParent() == 0 ) {
			$parent_company_id = 0;
			$user_company_id = 0;
		} elseif ( $this->getUser() == 0 ) {
			$parent_company_id = $parent->getCompany();
			$user_company_id = $parent->getCompany();
		} elseif ( $this->getParent() == 0 ) {
			$parent_company_id = $user->getCompany();
			$user_company_id = $user->getCompany();
		} else {
			$parent_company_id = $parent->getCompany();
			$user_company_id = $user->getCompany();
		}

		if ( $user_company_id > 0 AND $parent_company_id > 0 ) {

			Debug::Text(' User Company: '. $user_company_id .' Parent Company: '. $parent_company_id, __FILE__, __LINE__, __METHOD__, 10);
			if ( $user_company_id != $parent_company_id ) {
					$this->Validator->isTrue(	'parent',
												FALSE,
												TTi18n::gettext('User or parent has incorrect company')
												);
			}

			$this->getFastTreeObject()->setTree( $this->getHierarchyControl() );
			$children_arr = $this->getFastTreeObject()->getAllChildren( $this->getUser(), 'RECURSE' );
			if ( is_array($children_arr) ) {
				$children_ids = array_keys( $children_arr );

				if ( isset($children_ids) AND is_array($children_ids) AND in_array( $this->getParent(), $children_ids) == TRUE ) {
					Debug::Text(' Objects cant be re-parented to their own children...', __FILE__, __LINE__, __METHOD__, 10);
					$this->Validator->isTrue(	'parent',
												FALSE,
												TTi18n::gettext('Unable to change parent to a child of itself')
												);
				}
			}

/*
			//Make sure we're not re-parenting to a child.
			$uhlf = TTnew( 'UserHierarchyListFactory' );
			$hierarchy = $uhlf->getByCompanyIdArray( $parent_company_id );

			Debug::Text(' User ID: '. $this->getUser() .' Parent ID: '. $this->getParent(), __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array( $hierarchy ) ) {
				if ( in_array( $this->getParent(), array_keys( $hierarchy[$this->getUser()] ) ) ) {
					Debug::Text(' Trying to re-parent to a child! ', __FILE__, __LINE__, __METHOD__, 10);

					$this->Validator->isTrue(	'parent',
												FALSE,
												TTi18n::gettext('Unable to change parent to a child of itself')
												);

				} else {
					Debug::Text(' NOT Trying to re-parent to a child! ', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::Text(' NOT Trying to re-parent to a child! 22', __FILE__, __LINE__, __METHOD__, 10);
			}
*/
		}

		return TRUE;
	}

	function Save() {
		$this->StartTransaction();

		$this->getFastTreeObject()->setTree( $this->getHierarchyControl() );

		$retval = TRUE;
		if ( $this->getId() === FALSE ) {
			Debug::Text(' Adding Node ', __FILE__, __LINE__, __METHOD__, 10);
			$log_action = 10;

			//Add node to tree
			if ( $this->getFastTreeObject()->add( $this->getUser(), $this->getParent() ) === FALSE ) {
				Debug::Text(' Failed adding Node ', __FILE__, __LINE__, __METHOD__, 10);

				$this->Validator->isTrue(	'user',
											FALSE,
											TTi18n::gettext('Employee is already assigned to this hierarchy')
											);
				$retval = FALSE;
			}
		} else {
			Debug::Text(' Editing Node ', __FILE__, __LINE__, __METHOD__, 10);
			$log_action = 20;

			//Edit node.
			if ( $this->getFastTreeObject()->edit( $this->getPreviousUser(), $this->getUser() ) === TRUE ) {
				$retval = $this->getFastTreeObject()->move( $this->getUser(), $this->getParent() );
			} else {
				Debug::Text(' Failed editing Node ', __FILE__, __LINE__, __METHOD__, 10);

				//$retval = FALSE;
				$retval = TRUE;
			}
		}

		/*
		if ( $retval === TRUE ) {
			Debug::Text(' Retval true, Setting Shared flag ', __FILE__, __LINE__, __METHOD__, 10);

			$hslf = TTnew( 'HierarchyShareListFactory' );
			$hslf->getByHierarchyControlIdAndUserId( $this->getHierarchyControl(), $this->getUser() );
			if ( $hslf->getRecordCount() > 0 ) {
				Debug::Text(' Deleting already set shared flag ', __FILE__, __LINE__, __METHOD__, 10);

				$shared_obj = $hslf->getCurrent();
				$shared_obj->Delete();
			}

			if ( $this->getShared() === TRUE ) {
				Debug::Text(' Setting Shared flag ', __FILE__, __LINE__, __METHOD__, 10);

				$hsf = TTnew( 'HierarchyShareFactory' );
				$hsf->setHierarchyControl( $this->getHierarchyControl() );
				$hsf->setUser( $this->getUser() );
				$hsf->Save();

			}
		} else {
			Debug::Text(' Retval NOT true, Setting Shared flag ', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		TTLog::addEntry( $this->getUser(), $log_action, TTi18n::getText('Hierarchy Tree - Control ID: ').$this->getHierarchyControl(), NULL, $this->getTable() );

		$this->CommitTransaction();
		//$this->FailTransaction();

		$cache_id = $this->getHierarchyControl().$this->getParent();
		$this->removeCache( $cache_id );

		return $retval;
	}

	function Delete() {
		if ( $this->getUser() !== FALSE ) {
			/*
			$this->StartTransaction();

			$this->getFastTreeObject()->setTree( $this->getHierarchyControl() );

			$this->getFastTreeObject()->delete( $this->getUser(), 'RECURSE');

			//FIXME: When deleting recursively, we don't clear out the hierarhcy share table for all the children.
			$hslf = TTnew( 'HierarchyShareListFactory' );
			Debug::Text(' Hierarchy Control ID: '. $this->getHierarchyControl(), __FILE__, __LINE__, __METHOD__, 10);
			$hslf->getByHierarchyControlIdAndUserId( $this->getHierarchyControl(), $this->getUser() );
			if ( $hslf->getRecordCount() > 0 ) {
				Debug::Text(' Deleting already set shared flag ', __FILE__, __LINE__, __METHOD__, 10);

				$shared_obj = $hslf->getCurrent();
				$shared_obj->Delete();
			} else {
				Debug::Text(' NOT Deleting already set shared flag ', __FILE__, __LINE__, __METHOD__, 10);
			}

			TTLog::addEntry( $this->getUser(), 30, TTi18n::getText('Hierarchy Tree - Control ID: ').$this->getHierarchyControl(), NULL, $this->getTable() );

			$this->CommitTransaction();
			*/

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
