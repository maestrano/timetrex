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
 * @package Modules\Policy
 */
class PolicyGroupListFactory extends PolicyGroupFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
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

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIds($ids, $where = NULL, $order = NULL) {
		if ( $ids == '') {
			return FALSE;
		}
/*
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
*/
		$pguf = new PolicyGroupUserFactory();

		$ph = array();

		$query = '
					select	a.*,
							b.user_id as user_id
					from	'. $this->getTable() .' as a,
							'. $pguf->getTable() .' as b
					where	a.id = b.policy_group_id
						AND b.user_id in  ('. $this->getListSQL($ids, $ph) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndUserId($company_id, $user_ids, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_ids == '') {
			return FALSE;
		}
/*
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
*/
		$pguf = new PolicyGroupUserFactory();

		$ph = array( 'company_id' => $company_id );

		$query = '
					select	a.*,
							b.user_id as user_id
					from	'. $this->getTable() .' as a,
							'. $pguf->getTable() .' as b
					where	a.id = b.policy_group_id
						AND a.company_id = ? ';

		if ( $user_ids AND is_array($user_ids) AND isset($user_ids[0]) ) {
			$query	.=	' AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .') ';
		}

		$query .= '	AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array();
		if ( $order == NULL ) {
			//$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	distinct a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $pguf->getTable() .' as b ON a.id = b.policy_group_id
						LEFT JOIN '. $cgmf->getTable() .' as c ON ( a.id = c.object_id AND c.company_id = a.company_id AND c.object_type_id = 130)
						LEFT JOIN '. $cgmf->getTable() .' as d ON ( a.id = d.object_id AND d.company_id = a.company_id AND d.object_type_id = 110)
						LEFT JOIN '. $cgmf->getTable() .' as e ON ( a.id = e.object_id AND e.company_id = a.company_id AND e.object_type_id = 120)
						LEFT JOIN '. $cgmf->getTable() .' as f ON ( a.id = f.object_id AND f.company_id = a.company_id AND f.object_type_id = 140)
						LEFT JOIN '. $cgmf->getTable() .' as g ON ( a.id = g.object_id AND g.company_id = a.company_id AND g.object_type_id = 180)
					where	a.company_id = ?
					';

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exception_policy_control_id']) AND isset($filter_data['exception_policy_control_id'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_control_id']) ) {
			$query	.=	' AND a.exception_policy_control_id in ('. $this->getListSQL($filter_data['exception_policy_control_id'], $ph) .') ';
		}
		if ( isset($filter_data['holiday_policy']) AND isset($filter_data['holiday_policy'][0]) AND !in_array(-1, (array)$filter_data['holiday_policy']) ) {
			$query	.=	' AND g.map_id in ('. $this->getListSQL($filter_data['holiday_policy'], $ph) .') ';
		}
		if ( isset($filter_data['user_policy_id']) AND isset($filter_data['user_policy_id'][0]) AND !in_array(-1, (array)$filter_data['user_policy_id']) ) {
			$query	.=	' AND b.user_policy_id in ('. $this->getListSQL($filter_data['user_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['round_interval_policy_id']) AND isset($filter_data['round_interval_policy_id'][0]) AND !in_array(-1, (array)$filter_data['round_interval_policy_id']) ) {
			$query	.=	' AND c.map_id in ('. $this->getListSQL($filter_data['round_interval_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['over_time_policy_id']) AND isset($filter_data['over_time_policy_id'][0]) AND !in_array(-1, (array)$filter_data['over_time_policy_id']) ) {
			$query	.=	' AND d.map_id in ('. $this->getListSQL($filter_data['over_time_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['premium_policy_id']) AND isset($filter_data['premium_policy_id'][0]) AND !in_array(-1, (array)$filter_data['premium_policy_id']) ) {
			$query	.=	' AND e.map_id in ('. $this->getListSQL($filter_data['premium_policy_id'], $ph) .') ';
		}
		if ( isset($filter_data['accrual_policy_id']) AND isset($filter_data['accrual_policy_id'][0]) AND !in_array(-1, (array)$filter_data['accrual_policy_id']) ) {
			$query	.=	' AND f.map_id in ('. $this->getListSQL($filter_data['accrual_policy_id'], $ph) .') ';
		}

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	function getByCompanyIdArray($company_id, $include_blank = TRUE) {

		$pglf = new PolicyGroupListFactory();
		$pglf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($pglf as $pg_obj) {
			$list[$pg_obj->getID()] = $pg_obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getUserToPolicyGroupMapArrayByListFactory( $lf ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$retarr[$obj->getColumn('user_id')] = $obj->getId();
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		if ( isset($filter_data['user']) ) {
			$filter_data['user_id'] = $filter_data['user'];
		}

		$additional_order_fields = array();

		$sort_column_aliases = array(
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$pguf = new PolicyGroupUserFactory();
		$cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Count total users in PolicyGroup factory, so we can disable it when needed. That way it doesn't slow down Policy Group dropdown boxes.
		//(select count(*) from '. $pguf->getTable() .' as pguf_tmp where pguf_tmp.policy_group_id = a.id ) as total_users,
		$query = '
					select	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['exception_policy_control']) ) ? $this->getWhereClauseSQL( 'a.exception_policy_control_id', $filter_data['exception_policy_control'], 'numeric_list', $ph ) : NULL;

		//Optmize query using subselects rather than LEFT JOIN as that results in hundreds of thousands of records being returned and having to use DISTINCT.
		$query .= ( isset($filter_data['user_id']) ) ? ' AND ( a.id in ( SELECT policy_group_id FROM '. $pguf->getTable() .' as b WHERE a.id = b.policy_group_id '. $this->getWhereClauseSQL( 'b.user_id', $filter_data['user_id'], 'numeric_list', $ph ) .' ) ) ' : NULL;

		$query .= ( isset($filter_data['over_time_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as d WHERE a.id = d.object_id AND d.company_id = a.company_id AND d.object_type_id = 110 '. $this->getWhereClauseSQL( 'd.map_id', $filter_data['over_time_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;
		$query .= ( isset($filter_data['holiday_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as g WHERE a.id = g.object_id AND g.company_id = a.company_id AND g.object_type_id = 180 '. $this->getWhereClauseSQL( 'g.map_id', $filter_data['holiday_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;
		$query .= ( isset($filter_data['round_interval_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as c WHERE a.id = c.object_id AND c.company_id = a.company_id AND c.object_type_id = 130 '. $this->getWhereClauseSQL( 'c.map_id', $filter_data['round_interval_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;
		$query .= ( isset($filter_data['premium_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as e WHERE a.id = e.object_id AND e.company_id = a.company_id AND e.object_type_id = 120 '. $this->getWhereClauseSQL( 'e.map_id', $filter_data['premium_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;
		$query .= ( isset($filter_data['accrual_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as f WHERE a.id = f.object_id AND f.company_id = a.company_id AND f.object_type_id = 140 '. $this->getWhereClauseSQL( 'f.map_id', $filter_data['accrual_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;
		$query .= ( isset($filter_data['absence_policy']) ) ? ' AND ( a.id in ( SELECT object_id FROM '. $cgmf->getTable() .' as h WHERE a.id = h.object_id AND h.company_id = a.company_id AND h.object_type_id = 170 '. $this->getWhereClauseSQL( 'h.map_id', $filter_data['absence_policy'], 'numeric_list', $ph ) .' ) ) ' : NULL;

		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	'
						 AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );
		
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

}
?>
