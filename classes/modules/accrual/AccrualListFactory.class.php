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
 * $Id: AccrualListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Accrual
 */
class AccrualListFactory extends AccrualFactory implements IteratorAggregate {

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

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id
					);

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.user_id = ?
						AND b.company_id = ?
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyID($company_id, $user_id, $accrual_policy_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id,
					'accrual_policy_id' => $accrual_policy_id,
					);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = ?
						AND b.company_id = ?
						AND a.accrual_policy_id = ?
						AND ( a.user_date_total_id IS NULL OR ( a.user_date_total_id IS NOT NULL AND c.deleted = 0 AND d.deleted = 0) )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyIDAndTimeStampAndAmount($company_id, $user_id, $accrual_policy_id, $time_stamp, $amount, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $time_stamp == '') {
			return FALSE;
		}

		if ( $amount == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id,
					'accrual_policy_id' => $accrual_policy_id,
					'time_stamp' => $this->db->BindTimeStamp( $time_stamp ),
					'amount' => $amount,
					);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = ?
						AND b.company_id = ?
						AND a.accrual_policy_id = ?
						AND a.time_stamp = ?
						AND a.amount = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndUserIdAndAccrualPolicyIDAndTypeIDAndTimeStamp($company_id, $user_id, $accrual_policy_id, $type_id, $time_stamp, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $time_stamp == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('d.date_stamp' => 'desc', 'a.time_stamp' => 'desc');
			$strict_order = FALSE;
		}

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id,
					'accrual_policy_id' => $accrual_policy_id,
					'type_id' => $type_id,
					'time_stamp' => $this->db->BindTimeStamp( $time_stamp ),
					);

		$query = '
					select 	a.*,
							d.date_stamp as date_stamp
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
							LEFT JOIN '. $udtf->getTable() .' as c ON a.user_date_total_id = c.id
							LEFT JOIN '. $udf->getTable() .' as d ON c.user_date_id = d.id
					where
						a.user_id = ?
						AND b.company_id = ?
						AND a.accrual_policy_id = ?
						AND a.type_id = ?
						AND a.time_stamp = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndAccrualPolicyIDAndUserDateTotalID($user_id, $accrual_policy_id, $user_date_total_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		if ( $user_date_total_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'accrual_policy_id' => $accrual_policy_id,
					'user_date_total_id' => $user_date_total_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND accrual_policy_id = ?
						AND user_date_total_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}


	function getByUserIdAndUserDateTotalID($user_id, $user_date_total_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $user_date_total_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'user_date_total_id' => $user_date_total_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND user_date_total_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getOrphansByUserId($user_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$apf = new AccrualPolicyFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					);

		//Make sure we check if user_date rows are deleted where user_date_total rows are not.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
					LEFT JOIN '. $apf->getTable() .' as d ON a.accrual_policy_id = d.id
					where	a.user_id = ?
						AND (
								( b.id is NULL OR b.deleted = 1 )
								OR
								( b.deleted = 0 AND ( c.id is NULL OR c.deleted = 1) )
							)
						AND ( a.type_id = 10 OR a.type_id = 20 OR ( a.type_id = 75 AND d.type_id = 30 ) )
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getOrphansByUserIdAndDate($user_id, $date_stamp, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		$apf = new AccrualPolicyFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					//Filter the accrual rows to one day before and one day after as an optimization.
					'date_stamp1' => $this->db->BindDate( TTDate::getBeginDayEpoch( TTDate::getMiddleDayEpoch( $date_stamp )-86400 ) ),
					'date_stamp2' => $this->db->BindDate( TTDate::getBeginDayEpoch( TTDate::getMiddleDayEpoch( $date_stamp )+86400 ) ),
					);

		//Make sure we check if user_date rows are deleted where user_date_total rows are not.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
					LEFT JOIN '. $apf->getTable() .' as d ON a.accrual_policy_id = d.id
					where	a.user_id = ?
						AND ( a.time_stamp >= ? AND a.time_stamp <= ? )
						AND (
								( b.id is NULL OR b.deleted = 1 )
								OR
								( b.deleted = 0 AND ( c.id is NULL OR c.deleted = 1) )
							)
						AND ( a.type_id = 10 OR a.type_id = 20 OR ( a.type_id = 75 AND d.type_id = 30 ) )
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSumByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$udtf = new UserDateTotalFactory();

		$ph = array(
					'user_id' => $user_id,
					'accrual_policy_id' => $accrual_policy_id,
					);

		//Make sure orphaned records that slip through are not counted in the balance.
		$query = '
					select 	sum(amount) as amount
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					where	a.user_id = ?
						AND a.accrual_policy_id = ?
						AND ( ( a.user_date_total_id is NOT NULL AND b.id is NOT NULL AND b.deleted = 0 )
								OR ( a.user_date_total_id IS NULL AND b.id is NULL ) )
						AND a.deleted = 0';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Balance: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getSumByUserIdAndAccrualPolicyIdAndAfterDate($user_id, $accrual_policy_id, $epoch ) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$udtf = new UserDateTotalFactory();

		$ph = array(
					'user_id' => $user_id,
					'accrual_policy_id' => $accrual_policy_id,
					'time_stamp' => $this->db->BindTimeStamp( $epoch ),
					);

		//Make sure orphaned records that slip through are not counted in the balance.
		$query = '
					select 	sum(amount) as amount
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udtf->getTable() .' as b ON a.user_date_total_id = b.id
					where	a.user_id = ?
						AND a.accrual_policy_id = ?
						AND ( ( a.user_date_total_id is NOT NULL AND b.id is NOT NULL AND b.deleted = 0 )
								OR ( a.user_date_total_id IS NULL AND b.id is NULL ) )
						AND a.time_stamp >= ?
						AND a.deleted = 0';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Balance After Date: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByAccrualPolicyId($accrual_policy_id, $where = NULL, $order = NULL) {
		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'accrual_policy_id' => $accrual_policy_id,
					);

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	a.accrual_policy_id = ?
						AND a.deleted = 0
						AND b.deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

        if ( isset($filter_data['user_status_id']) ) {
			$filter_data['status_id'] = $filter_data['user_status_id'];
		}
        if ( isset($filter_data['user_group_id']) ) {
            $filter_data['group_id'] = $filter_data['user_group_id'];
        }
        if ( isset($filter_data['user_title_id']) ) {
            $filter_data['title_id'] = $filter_data['user_title_id'];
        }
        if ( isset($filter_data['include_user_id']) ) {
			$filter_data['user_id'] = $filter_data['include_user_id'];
		}
        if ( isset($filter_data['exclude_user_id']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}
        if ( isset($filter_data['accrual_type_id']) ) {
            $filter_data['type_id'] = $filter_data['accrual_type_id'];
        }

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

        if ( isset( $filter_data['accrual_type_id'] ) ) {
            $filter_data['type_id'] = $filter_data['accrual_type_id'];
        }

		$additional_order_fields = array( 'accrual_policy','accrual_policy_type_id','date_stamp' );
		$sort_column_aliases = array(
									 'accrual_policy_type' => 'accrual_policy_type_id',
									 'type' => 'type_id',
									 );
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'accrual_policy_id' => 'asc', 'date_stamp' => 'desc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$udtf = new UserDateTotalFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$apf = new AccrualPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							ab.name as accrual_policy,
							ab.type_id as accrual_policy_type_id,
							CASE WHEN udf.date_stamp is NOT NULL THEN udf.date_stamp ELSE a.time_stamp END as date_stamp,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as "user_group",
							f.id as title_id,
							f.name as title
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $apf->getTable() .' as ab ON ( a.accrual_policy_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $udtf->getTable() .' as udtf ON ( a.user_date_total_id = udtf.id AND udtf.deleted = 0 )
						LEFT JOIN '. $udf->getTable() .' as udf ON ( udtf.user_date_id = udf.id AND udf.deleted = 0 )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )
                        LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )

					where	b.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['type_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions('type') );
		}
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}

        $query .= ( isset($filter_data['accrual_policy_type_id']) ) ? $this->getWhereClauseSQL( 'ab.type_id', $filter_data['accrual_policy_type_id'], 'numeric_list', $ph ) : NULL;
        $query .= ( isset($filter_data['accrual_policy_id']) ) ? $this->getWhereClauseSQL( 'a.accrual_policy_id', $filter_data['accrual_policy_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'udf.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND ( ( udf.date_stamp is NULL AND a.time_stamp >= ? ) OR ( udf.date_stamp is NOT NULL AND udf.date_stamp >= ? ) )';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND ( ( udf.date_stamp is NULL AND a.time_stamp <= ? ) OR ( udf.date_stamp is NOT NULL AND udf.date_stamp <= ? ) )';
		}


		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        

		//Make sure we exclude delete user_date_total records, so we match the accrual balances.
		$query .= 	'
						AND ( ( a.user_date_total_id is NOT NULL AND udtf.id is NOT NULL AND udtf.deleted = 0 ) OR ( a.user_date_total_id IS NULL AND udtf.id is NULL ) )
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph,'Filter Data:'. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
