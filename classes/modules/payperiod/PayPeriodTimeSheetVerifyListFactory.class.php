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
 * $Revision: 11018 $
 * $Id: PayPeriodTimeSheetVerifyListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\PayPeriod
 */
class PayPeriodTimeSheetVerifyListFactory extends PayPeriodTimeSheetVerifyFactory implements IteratorAggregate {

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

		return $this;
	}

	function getByPayPeriodIdAndUserId($pay_period_id, $user_id, $where = NULL, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'user_id' => $user_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where
						a.pay_period_id = ?
						AND a.user_id = ?
						AND ( a.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodIdAndUserIdAndCompanyId($pay_period_id, $user_id, $company_id, $where = NULL, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'user_id' => $user_id,
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where 	a.user_id = b.id
						AND a.pay_period_id = ?
						AND a.user_id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodIdAndCompanyId($pay_period_id, $company_id, $where = NULL, $order = NULL) {
		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					//'pay_period_id' => $pay_period_id,
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where 	a.user_id = b.id
						AND b.company_id = ?
						AND a.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph).')
						AND ( a.deleted = 0 AND b.deleted = 0 )
						';
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

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where 	a.user_id = b.id
						AND a.id = ?
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ppf = new PayPeriodFactory();

		$ph = array(
					'company_id' => $id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppf->getTable() .' as b ON a.pay_period_id = b.id
					where	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdListAndStatusAndLevelAndMaxLevelAndNotAuthorized($ids, $status, $level, $max_level, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $ids == '') {
			return FALSE;
		}

		if ( $status == '') {
			return FALSE;
		}


		if ( $level == '') {
			return FALSE;
		}

		if ( $max_level == '') {
			return FALSE;
		}

		$additional_sort_fields = array( 'start_date', 'user_id' );

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('a.user_id' => 'asc', 'b.start_date' => 'asc');
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$ppf = new PayPeriodFactory();
		//$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'status' => $status,
					'level' => $level,
					'max_level' => $max_level,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppf->getTable() .' as b

					where	a.pay_period_id = b.id
						AND	a.status_id = ?
						AND a.authorized = 0
						AND ( a.authorization_level = ? OR a.authorization_level > ? )
						AND a.user_id in ('. $this->getListSQL($ids, $ph).')
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByHierarchyLevelMapAndStatusAndNotAuthorized($hierarchy_level_map, $status,  $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $hierarchy_level_map == '') {
			return FALSE;
		}

		if ( $status == '') {
			return FALSE;
		}

		$additional_sort_fields = array( 'start_date', 'user_id' );

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('a.user_id' => 'asc', 'b.start_date' => 'asc');
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$ppf = new PayPeriodFactory();
		//$udf = new UserDateFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'status' => $status,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppf->getTable() .' as b,
							'. $huf->getTable() .' as z
					where	a.pay_period_id = b.id
						AND a.user_id = z.user_id
						AND	a.status_id = ?
						AND a.authorized = 0
						AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $hierarchy_level_map ) .' )
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
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

		$additional_order_fields = array('start_date','end_date','transaction_date', 'user_status_id','last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title' );

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 );
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'start_date' => 'desc', );
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			/*
			if ( !isset($order['effective_date']) ) {
				$order['effective_date'] = 'desc';
			}
			*/
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$huf = new HierarchyUserFactory();
		$ppf = new PayPeriodFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Need to make this return DISTINCT records only, because if the same child is assigned to multiple hierarchies,
		//the join to table HUF will force it to return one row for each hierarchy they are a child of. This prevents that.
		$query = '
					select	DISTINCT
							a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							ppf.start_date as start_date,
							ppf.end_date as end_date,
							ppf.transaction_date as transaction_date,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as user_group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $ppf->getTable() .' as ppf ON ( a.pay_period_id = ppf.id AND ppf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $huf->getTable() .' as huf ON ( a.user_id = huf.user_id )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )
                        LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )

					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.user_id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query  .=	' AND a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}

		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
			$query  .=	' AND b.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
		}
		if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
			$query  .=	' AND b.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
		}

		//Handle authorize list criteria here.
		if ( isset($filter_data['authorized']) AND isset($filter_data['authorized'][0]) AND !in_array(-1, (array)$filter_data['authorized']) ) {
			$query  .=	' AND a.authorized in ('. $this->getListSQL($filter_data['authorized'], $ph) .') ';
		}
		if ( isset($filter_data['hierarchy_level_map']) AND is_array($filter_data['hierarchy_level_map']) ) {
			$query  .= ' AND  huf.id IS NOT NULL '; //Make sure the user maps to a hierarchy.
			$query  .= ' AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $filter_data['hierarchy_level_map'], 'a.', 'huf.' ) .' )';
		} elseif ( isset($filter_data['hierarchy_level_map']) AND $filter_data['hierarchy_level_map'] == FALSE ) {
			//If hierarchy_level_map is not an array, don't return any requests.
			$query  .= ' AND  huf.id = -1 '; //Make sure the user maps to a hierarchy.
		}

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        

		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		Debug::Arr($ph,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

/*
	function getByUserIdListAndStatusAndNotAuthorized($id, $status, $parent_level_user_ids, $current_level_user_ids, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('a.user_id' => 'asc', 'b.start_date' => 'asc');
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$ppf = new PayPeriodFactory();
		$uf = new UserFactory();

		$ph = array(
					'status' => $status,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppf->getTable() .' as b
					where	a.pay_period_id = b.id
						AND	a.status_id = ?
						AND ( a.user_id in ('. $this->getListSQL($id, $ph).')
								OR a.id in ( select object_id from '. $af->getTable() .' as x
												WHERE x.object_type_id = 90
													AND x.created_by in ('. $this->getListSQL($id, $ph).') ) )
						AND	( select count(*) from '. $af->getTable() .' as z
								where z.object_type_id = 90
									AND z.object_id = a.id
									AND (  ( created_by in ('. $this->getListSQL($parent_level_user_ids, $ph) .')
												OR created_by in ('. $this->getListSQL($current_level_user_ids, $ph) .')
											)
											OR
											(
											created_by in ('. $this->getListSQL($id, $ph) .')
												AND z.authorized = 0
											)
										 )
									AND z.created_date >= a.updated_date
									) = 0
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
*/
}
?>
