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
 * $Id: RequestListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Request
 */
class RequestListFactory extends RequestFactory implements IteratorAggregate {

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

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND a.id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as udf ON a.user_date_id = udf.id
					LEFT JOIN '. $uf->getTable() .' as uf ON udf.user_id = uf.id
					where	uf.company_id = ?
						AND ( a.deleted = 0 AND udf.deleted = 0 AND uf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'b.date_stamp' => 'desc', 'a.type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND b.user_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndCompanyIdAndStartDateAndEndDate($user_id, $company_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'b.date_stamp' => 'desc', 'a.type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndUserIdAndStatusAndStartDateAndEndDate($company_id, $user_id, $status_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'status_id' => $status_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND b.user_id = ?
						AND a.status_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

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

		$additional_sort_fields = array( 'date_stamp', 'user_id' );

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('b.user_id' => 'asc', 'b.date_stamp' => 'asc');
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'status' => $status,
					'level' => $level,
					'max_level' => $max_level,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b

					where	a.user_date_id = b.id
						AND	a.status_id = ?
						AND a.authorized = 0
						AND ( a.authorization_level = ? OR a.authorization_level > ? )
						AND b.user_id in ('. $this->getListSQL($ids, $ph).')
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

		$additional_sort_fields = array( 'date_stamp', 'user_id' );

		$sort_column_aliases = array(
									 'date_stamp' => 'date_stamp',
									 'user_id' => 'c.last_name',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('a.type_id' => 'asc', 'b.date_stamp' => 'desc', 'c.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'status' => $status,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c,
							'. $huf->getTable() .' as z
					where	a.user_date_id = b.id
						AND b.user_id = z.user_id
						AND b.user_id = c.id
						AND	a.status_id = ?
						AND a.authorized = 0
						AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $hierarchy_level_map ) .' )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized($hierarchy_level_map, $type_id, $status,  $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $hierarchy_level_map == '') {
			return FALSE;
		}

		if ( $status == '') {
			return FALSE;
		}

		$additional_sort_fields = array( 'date_stamp', 'user_id' );

		$sort_column_aliases = array(
									 'date_stamp' => 'date_stamp',
									 'user_id' => 'c.last_name',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('b.date_stamp' => 'desc', 'c.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'status' => $status,
					'type_id' => $type_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c,
							'. $huf->getTable() .' as z
					where	a.user_date_id = b.id
						AND b.user_id = z.user_id
						AND b.user_id = c.id
						AND	a.status_id = ?
						AND	a.type_id = ?
						AND a.authorized = 0
						AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $hierarchy_level_map ) .' )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order, $additional_sort_fields );

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
			$order = array('b.user_id' => 'asc', 'b.date_stamp' => 'asc');
			$strict_order = FALSE;
		}

		$af = new AuthorizationFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'status' => $status,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b

					where	a.user_date_id = b.id
						AND	a.status_id = ?
						AND ( b.user_id in ('. $this->getListSQL($id, $ph).')
								OR a.id in ( select object_id from '. $af->getTable() .' as x
												WHERE x.object_type_id = 50
													AND x.created_by in ('. $this->getListSQL($id, $ph).') ) )
						AND	( select count(*) from '. $af->getTable() .' as z
								where z.object_type_id = 50
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
	function getSumByPayPeriodIdAndStatus($pay_period_id, $status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$af = new AuthorizationFactory();

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'status_id' => $status,
					);

		$query = '
					select 	b.pay_period_id as pay_period_id, count(*) as total
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where	a.user_date_id = b.id
						AND	a.status_id = ?
						AND b.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph).')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					GROUP By b.pay_period_id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSumByCompanyIDAndPayPeriodIdAndStatus($company_id, $pay_period_id, $status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$af = new AuthorizationFactory();

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'status_id' => $status,
					);

		$query = '
					select 	b.pay_period_id as pay_period_id, count(*) as total
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND	a.status_id = ?
						AND b.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph).')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					GROUP By b.pay_period_id
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSumByPayPeriodIdAndStatusAndBeforeDate($pay_period_id, $status, $before_date, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$af = new AuthorizationFactory();

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'status_id' => $status,
					'before_date' => $this->db->BindDate( $before_date ),
					);

		$query = '
					select 	count(*)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where	a.user_date_id = b.id
						AND b.pay_period_id = ?
						AND	a.status_id = ?
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//$this->rs = $this->db->PageExecute($query, $limit, $page);

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'b.date_stamp' => 'desc', 'a.type_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ? ';
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}
		$query .= '		AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 ) ';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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

		$additional_order_fields = array('date_stamp', 'user_status_id','last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title' );

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 );
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'type_id' => 'asc', 'date_stamp' => 'desc', );
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
		$udf = new UserDateFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Need to make this return DISTINCT records only, because if the same child is assigned to multiple hierarchies,
		//the join to table HUF will force it to return one row for each hierarchy they are a child of. This prevents that.
		$query = '
					select 	DISTINCT
							a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							udf.date_stamp as date_stamp,
							udf.user_id as user_id,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as user_group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $udf->getTable() .' as udf ON ( a.user_date_id = udf.id AND udf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( udf.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $huf->getTable() .' as huf ON ( udf.user_id = huf.user_id )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )

					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND udf.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND udf.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND udf.user_id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
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
			$query  .= ' AND ( '. HierarchyLevelFactory::convertHierarchyLevelMapToSQL( $filter_data['hierarchy_level_map'], 'a.', 'huf.', 'a.type_id' ) .' )';
		} elseif ( isset($filter_data['hierarchy_level_map']) AND $filter_data['hierarchy_level_map'] == FALSE ) {
			//If hierarchy_level_map is not an array, don't return any requests.
			$query  .= ' AND  huf.id = -1 '; //Make sure the user maps to a hierarchy.
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate( TTDate::parseDateTime( $filter_data['start_date'] ) );
			$query  .=	' AND udf.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate( TTDate::parseDateTime( $filter_data['end_date'] ) );
			$query  .=	' AND udf.date_stamp <= ?';
		}

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        
		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
