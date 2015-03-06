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
 * @package Modules\Users
 */
class UserWageListFactory extends UserWageFactory implements IteratorAggregate {

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

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
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

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	function getByCompanyId($company_id, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndUserId($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id.$user_id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						'user_id' => $user_id,
						);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND user_id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id.$user_id);
		}

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
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndGroupIDAndBeforeDate($user_id, $wage_group_id, $epoch, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $wage_group_id == '') {
			$wage_group_id = 0;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'wage_group_id' => $wage_group_id,
					'date' => $this->db->BindTimeStamp( $epoch ),

					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND wage_group_id = ?
						AND effective_date <= ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		//Debug::text(' Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

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
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND
							( created_date >= ? OR updated_date >= ? )
						';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User Tax rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}
		Debug::text('User Tax rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}


	//Grabs JUST the latest wage entry.
	function getLastWageByUserId($user_id) {
		if ( $user_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select	b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND	b.user_id = ?
						AND b.wage_group_id = 0
						AND a.deleted = 0
						AND b.deleted = 0
					ORDER BY b.effective_date desc
					LIMIT 1';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//Grabs JUST the latest wage entry.
	function getLastWageByUserIdAndDate($user_id, $epoch) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $epoch == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'epoch' => $this->db->BindTimeStamp( $epoch ),
					);

		$query = '
					select a.*
					from '. $this->getTable() .' as a,
						(
						select	z.user_id, max(effective_date) as effective_date
						from	'. $this->getTable() .' as z
						where
							z.effective_date <= ?
							AND z.wage_group_id = 0
							AND z.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
							AND ( z.deleted = 0 )
						GROUP BY z.user_id
						) as b,
						'. $uf->getTable() .' as c
					WHERE a.user_id = b.user_id
						AND a.effective_date = b.effective_date
						AND a.user_id = c.id
						AND ( c.deleted = 0	AND a.deleted = 0)
				';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getWageByUserIdAndPayPeriodEndDate($user_id, $pay_period_end_date) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pay_period_end_date == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'epoch' => $this->db->BindTimeStamp( $pay_period_end_date ),
					);

		$query = '
					select	b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND	b.user_id = ?
						AND b.effective_date <= ?
						AND b.wage_group_id = 0
						AND (a.deleted = 0 AND b.deleted=0)
					ORDER BY b.effective_date desc
					LIMIT 1';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDate($user_id, $date) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'date' => $this->db->BindTimeStamp(	 $date ),
					);

		$query = '
					select	b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND	b.user_id = ?
						AND b.effective_date <= ?
						AND b.wage_group_id = 0
						AND (a.deleted = 0 AND b.deleted=0)
					ORDER BY b.effective_date desc
					LIMIT 1';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getDefaultWageGroupByUserIdAndStartDateAndEndDate($user_id, $start_date = FALSE, $end_date = FALSE) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			$start_date = 0;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime();
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id1' => $user_id,
					'start_date1' => $this->db->BindTimeStamp( $start_date ),
					'end_date1' => $this->db->BindTimeStamp( $end_date ),
					'user_id2' => $user_id,
					'start_date2' => $this->db->BindTimeStamp( $start_date ),
					);

		$query = '
					(
					select b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND	b.user_id = ?
						AND b.effective_date >= ?
						AND b.effective_date <= ?
						AND b.wage_group_id = 0
						AND (a.deleted = 0 AND b.deleted=0)
					)
					UNION
					(
						select	d.*
						from	'. $uf->getTable() .' as c,
								'. $this->getTable() .' as d
						where	c.id = d.user_id
							AND	d.user_id = ?
							AND d.effective_date <= ?
							AND d.wage_group_id = 0
							AND (c.deleted = 0 AND d.deleted=0)
						ORDER BY d.effective_date desc
						LIMIT 1
					)
					ORDER BY effective_date desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date = FALSE, $end_date = FALSE) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			$start_date = 0;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime();
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id1' => $user_id,
					'start_date1' => $this->db->BindTimeStamp( $start_date ),
					'end_date1' => $this->db->BindTimeStamp( $end_date ),
					'user_id2' => $user_id,
					'start_date2' => $this->db->BindTimeStamp( $start_date ),
					);

		$query = '
					(
					select b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND	b.user_id = ?
						AND b.effective_date >= ?
						AND b.effective_date <= ?
						AND (a.deleted = 0 AND b.deleted=0)
					)
					UNION
					(
						select	d.*
						from	'. $uf->getTable() .' as c,
								'. $this->getTable() .' as d
						where	c.id = d.user_id
							AND	d.user_id = ?
							AND d.effective_date <= ?
							AND (c.deleted = 0 AND d.deleted=0)
						ORDER BY d.effective_date desc
					)
					ORDER BY wage_group_id, effective_date desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}
	function getByUserIdAndCompanyIdAndStartDateAndEndDate($user_id, $company_id, $start_date = FALSE, $end_date = FALSE) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			$start_date = 0;
		}

		if ( $end_date == '' ) {
			$end_date = TTDate::getTime();
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		$b_user_id_sql = $this->getListSQL($user_id, $ph);

		$ph['company_id2'] = $company_id;
		$ph['start_date2'] = $this->db->BindTimeStamp( $start_date );

		$query = '
					(
					select b.*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND a.company_id = ?
						AND b.effective_date >= ?
						AND b.effective_date <= ?
						AND	b.user_id in ('. $b_user_id_sql .')
						AND (a.deleted = 0 AND b.deleted=0)

					)
					UNION
					(
						select	m.*
						from	'. $this->getTable() .' as m
						where
							m.id in (
									select max(d.id) as id
									from	'. $uf->getTable() .' as c,
											'. $this->getTable() .' as d
									where c.id = d.user_id
										AND c.company_id = ?
										AND d.effective_date <= ?
										AND	d.user_id in ('. $this->getListSQL($user_id, $ph) .')
										AND (c.deleted = 0 AND d.deleted=0)
									group by d.user_id
									)
					)
					ORDER BY effective_date desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getArrayByUserIdAndStartDateAndEndDate($user_id, $start_date = FALSE, $end_date = FALSE) {
		$uwlf = new UserWageListFactory();
		$uwlf->getDefaultWageGroupByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date);

		foreach ($uwlf as $uw_obj) {
			$list[$uw_obj->getEffectiveDate()] = array(
														'wage' => $uw_obj->getWage(),
														'type_id' => $uw_obj->getType(),
														'hourly_rate' => $uw_obj->getHourlyRate(),
														'effective_date' => $uw_obj->getEffectiveDate()
														);
		}

		if ( isset($list) ) {
			return $list;
		}

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
			$order = array( 'b.effective_date' => 'desc');
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
					select	*
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

	function getByWageGroupIDAndCompanyId($wage_group_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( empty($wage_group_id) ) {
			return FALSE;
		}

		if ( empty($company_id) ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'b.effective_date' => 'desc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'wage_group_id' => $wage_group_id,
					);

		$query = '
					select	*
					from	'. $uf->getTable() .' as a,
							'. $this->getTable() .' as b
					where	a.id = b.user_id
						AND a.company_id = ?
						AND	b.wage_group_id = ?
						AND b.deleted = 0';
		$query .= $this->getSortSQL( $order, $strict );

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

		$additional_order_fields = array('user_status_id', 'last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title', 'wage_group');

		$sort_column_aliases = array(
									'user_status' => 'user_status_id',
									'type' => 'type_id',
									);
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'effective_date' => 'desc', 'wage_group_id' => 'asc', 'type_id' => 'asc', );
			$strict = FALSE;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset($order['effective_date']) ) {
				$order['effective_date'] = 'desc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();
		$wgf = new WageGroupFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							CASE WHEN a.wage_group_id = 0 THEN \''. TTi18n::getText('-Default-') .'\' ELSE ab.name END as wage_group,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title,
							g.id as currency_id,
							g.iso_code as iso_code,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $wgf->getTable() .' as ab ON ( a.wage_group_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as g ON ( b.currency_id = g.id AND g.deleted = 0 )


						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['wage_group_id']) ) ? $this->getWhereClauseSQL( 'a.wage_group_id', $filter_data['wage_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getAPILastWageSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		if ( !isset($filter_data['effective_date']) ) {
			$filter_data['effective_date'] = TTDate::getTime();
		}

		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['user_id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['exclude_user_id']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		$additional_order_fields = array( 'wage_group' );
		if ( $order == NULL ) {
			$order = array( 'effective_date' => 'desc', 'wage_group_id' => 'asc', 'type_id' => 'asc', );
			$strict = FALSE;
		} else {
			//Always sort by last name, first name after other columns
			if ( !isset($order['effective_date']) ) {
				$order['effective_date'] = 'desc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();
		$wgf = new WageGroupFactory();

		$ph = array(
					'effective_date' => $this->db->BindTimeStamp($filter_data['effective_date']),
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							CASE WHEN a.wage_group_id = 0 THEN \''. TTi18n::getText('-Default-') .'\' ELSE ab.name END as wage_group,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title,
							g.id as currency_id,
							g.iso_code as iso_code,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	(
								select	uwf.user_id as user_id, uwf.wage_group_id as wage_group_id, max(effective_date) as effective_date
								from	'. $this->getTable() .' as uwf
								where uwf.effective_date <= ? AND uwf.deleted = 0
								GROUP BY uwf.wage_group_id, uwf.user_id
							) as uwf_b

						LEFT JOIN '. $this->getTable() .' as a ON ( a.user_id = uwf_b.user_id AND a.wage_group_id = uwf_b.wage_group_id AND a.effective_date = uwf_b.effective_date )

						LEFT JOIN '. $wgf->getTable() .' as ab ON ( a.wage_group_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )

						LEFT JOIN '. $bf->getTable() .' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as f ON ( b.title_id = f.id AND f.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as g ON ( b.currency_id = g.id AND g.deleted = 0 )


						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['wage_group_id']) ) ? $this->getWhereClauseSQL( 'a.wage_group_id', $filter_data['wage_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>