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
 * $Revision: 1246 $
 * $Id: UserWageListFactory.class.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */

/**
 * @package Modules\Users
 */
class UserIdentificationListFactory extends UserIdentificationFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						);

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByTypeIdAndValue($type_id, $value, $order = NULL) {
		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value == '') {
			return FALSE;
		}

		$ph = array(
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
					where	a.type_id = ?
						AND a.value = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND ( a.deleted = 0  AND b.deleted = 0) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeId($company_id, $type_id, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.type_id' => 'asc', 'a.number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND b.status_id = 10
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeIdAndDateAndValidUserIDs($company_id, $type_id, $date = NULL, $valid_user_ids = array(), $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.type_id' => 'asc', 'a.number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//If the user record is modified, we have to consider the identification record to be modified as well,
		//otherwise a terminated employee re-hired will not have their old prox/fingerprint records put back on the clock.
		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND b.status_id = 10
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
				';

		if ( ( isset($date) AND $date > 0) OR ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) ) {
			$query .= ' AND ( ';

			if ( isset($date) AND $date > 0 ) {
				//Append the same date twice for created and updated.
				$ph[] = (int)$date;
				$ph[] = (int)$date;
				$ph[] = (int)$date;
				$ph[] = (int)$date;
				$query  .=	' 	( ( a.created_date >= ? OR a.updated_date >= ? ) OR ( b.created_date >= ? OR b.updated_date >= ? ) ) ';
			}

			//Valid USER IDs is an "OR", so if any IDs are specified they should *always* be included ,regardless of the $date variable.
			if ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) {
				if ( isset($date) AND $date > 0 ) {
					$query .= ' OR ';
				}
				$query  .=	' a.user_id in ('. $this->getListSQL($valid_user_ids, $ph) .') ';
			}

			$query .= ' ) ';
		}

		$query .= ' AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeIdAndValue($company_id, $type_id, $value, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.type_id = ?
						AND a.value = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserId($user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndTypeId($user_id, $type_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'user_id' => 'asc', 'type_id' => 'asc', 'number' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndTypeIdAndNumber($user_id, $type_id, $number, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $number === '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'type_id' => $type_id,
					'number' => $number,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id = ?
						AND number = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndUserIdAndTypeIdAndNumber($company_id, $user_id, $type_id, $number, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $number === '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'type_id' => $type_id,
					'number' => $number,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND a.user_id = ?
						AND a.type_id = ?
						AND a.number = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndTypeIdAndValue($user_id, $type_id, $value, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $value === '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'type_id' => $type_id,
					'value' => $value,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND type_id = ?
						AND value = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getIsModifiedByUserIdAndDate($user_id, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND
							( created_date >= ? OR updated_date >= ? )
						';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User Identification rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}
		Debug::text('User Identification rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					'user_created_date' => $date,
					'user_updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		//If the user record is modified, we have to consider the identification record to be modified as well,
		//otherwise a terminated employee re-hired will not have their old prox/fingerprint records put back on the clock.
		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	b.company_id = ?
						AND
							( ( a.created_date >= ? OR a.updated_date >= ? ) OR ( b.created_date >= ? OR b.updated_date >= ? ) )
						';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User Identification rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
			return TRUE;
		}
		Debug::text('User Identification rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( empty($user_id) ) {
			return FALSE;
		}

		if ( empty($company_id) ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND a.company_id = ?
						AND	b.user_id = ?
						AND b.deleted = 0';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}
?>