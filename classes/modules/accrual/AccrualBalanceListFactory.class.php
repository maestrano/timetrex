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
 * $Revision: 9714 $
 * $Id: AccrualBalanceListFactory.class.php 9714 2013-04-27 17:50:36Z ipso $
 * $Date: 2013-04-27 10:50:36 -0700 (Sat, 27 Apr 2013) $
 */

/**
 * @package Modules\Accrual
 */
class AccrualBalanceListFactory extends AccrualBalanceFactory implements IteratorAggregate {

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

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
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
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
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

		$additional_order_fields = array('a.balance', 'c.name');
		if ( $order == NULL ) {
			$order = array( 'c.name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$apf = new AccrualPolicyFactory();
		$af = new AccrualFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_id = b.id
						AND a.accrual_policy_id = c.id
						AND b.company_id = ?
						AND a.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND EXISTS ( select 1 from '. $af->getTable() .' as af WHERE af.accrual_policy_id = a.accrual_policy_id AND a.user_id = af.user_id AND af.deleted = 0 )
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndCompanyIdAndEnablePayStubBalanceDisplay($user_id, $company_id, $enable_pay_stub_balance_display, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$additional_order_fields = array('a.balance', 'c.name');
		if ( $order == NULL ) {
			$order = array( 'c.name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$apf = new AccrualPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'enable_pay_stub_balance_display' => (int)$enable_pay_stub_balance_display,
					);

		$query = '
					select 	a.*,
							c.name as name
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_id = b.id
						AND a.accrual_policy_id = c.id
						AND b.company_id = ?
						AND c.enable_pay_stub_balance_display = ?
						AND a.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndAccrualPolicyId($user_id, $accrual_policy_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'accrual_policy_id' => $accrual_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND accrual_policy_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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

		$additional_order_fields = array( 'accrual_policy','accrual_policy_type_id','first_name','last_name','name','default_branch','default_department' );
		$sort_column_aliases = array(
									 'accrual_policy_type' => 'accrual_policy_type_id',
									 'group' => 'e.name',
									 );
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'last_name' => 'asc', 'accrual_policy_id' => 'asc', );
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
		$apf = new AccrualPolicyFactory();
		$af = new AccrualFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							ab.name as accrual_policy,
							ab.type_id as accrual_policy_type_id,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as "group",
							f.id as title_id,
							f.name as title
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $apf->getTable() .' as ab ON ( a.accrual_policy_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )
                        
					where	b.company_id = ?
						AND EXISTS ( select 1 from '. $af->getTable() .' as af WHERE af.accrual_policy_id = a.accrual_policy_id AND a.user_id = af.user_id AND af.deleted = 0 )
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['type_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions('type') );
		}
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'ab.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['accrual_policy_id']) ) ? $this->getWhereClauseSQL( 'a.accrual_policy_id', $filter_data['accrual_policy_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group']) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch']) ) ? $this->getWhereClauseSQL( 'c.name', $filter_data['default_branch'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department']) ) ? $this->getWhereClauseSQL( 'd.name', $filter_data['default_department'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'a.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title']) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['title'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'b.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'b.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : NULL;

		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;

		$query .= 	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>