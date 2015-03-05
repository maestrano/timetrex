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
 * @package Modules\PayStubAmendment
 */
class PayStubAmendmentListFactory extends PayStubAmendmentFactory implements IteratorAggregate {

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

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			//$order = array( 'a.effective_date' => 'desc', 'a.user_id' => 'asc' );
			$order = array( 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByPayStubEntryNameID($psen_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $psen_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$strict_order = FALSE;
		}

		$ph = array(
					'psen_id' => $psen_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where
						a.pay_stub_entry_name_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

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

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'user_id' => $user_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND user_id = ?
						AND deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByRecurringPayStubAmendmentId($recurring_ps_amendment_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		if ( $recurring_ps_amendment_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'effective_date' => 'desc', 'user_id' => 'asc' );
			$strict_order = FALSE;
		}

		$ph = array(
					'recurring_ps_amendment_id' => $recurring_ps_amendment_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	recurring_ps_amendment_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	function getByUserIdAndRecurringPayStubAmendmentIdAndStartDateAndEndDate($user_id, $recurring_ps_amendment_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $recurring_ps_amendment_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'recurring_ps_amendment_id' => $recurring_ps_amendment_id,
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND recurring_ps_amendment_id = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndCompanyId($user_id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND a.user_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndUserIdAndStartDateAndEndDate($company_id, $user_id, $start_date = NULL, $end_date = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		/*
		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}
		*/

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array( 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' );
			$strict_order = FALSE;
		}

		$ulf = new UserListFactory();

		$ph = array(
					'company_id' => $company_id,
					//'user_id' => $user_id,
					//'start_date' => $start_date,
					//'end_date' => $end_date,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $ulf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
					';

		if ( $user_id != '' AND isset($user_id[0]) AND !in_array(-1, (array)$user_id) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($user_id, $ph) .') ';
		}
		if ( $start_date != ''	) {
			$ph[] = $start_date;
			$ph[] = $end_date;
			$query	.=	' AND a.effective_date >= ? AND a.effective_date <= ? ';
		}

		$query .= '	AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getIsModifiedByUserIdAndStartDateAndEndDateAndDate($user_id, $start_date, $end_date, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'created_date' => $date,
					'updated_date' => $date
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	'. $this->getTable() .'
					where
							user_id = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND
							( created_date >= ? OR updated_date >= ? )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('PS Amendment rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}
		Debug::text('PS Amendment rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getByCompanyIdAndAuthorizedAndStartDateAndEndDate($company_id, $authorized, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $authorized == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'authorized' => $this->toBool($authorized),
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		//CalculatePayStub uses this to find PS amendments.
		//Because of percent amounts, make sure we order by effective date FIRST,
		//Then FIXED amounts, then percents.


		//Pay period end dates never equal the start start date, so >= and <= are proper.

		//06-Oct-06: Start including YTD_adjustment entries for the new pay stub calculation system.
		//						AND ytd_adjustment = 0
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as uf
					where
						a.user_id = uf.id
						AND uf.company_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.deleted = 0
					ORDER BY a.effective_date asc, a.type_id asc
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndAuthorizedAndStartDateAndEndDate($user_id, $authorized, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $authorized == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = array(
					'authorized' => $this->toBool($authorized),
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		//CalculatePayStub uses this to find PS amendments.
		//Because of percent amounts, make sure we order by effective date FIRST,
		//Then FIXED amounts, then percents.

		//Pay period end dates never equal the start start date, so >= and <= are proper.

		//06-Oct-06: Start including YTD_adjustment entries for the new pay stub calculation system.
		//						AND ytd_adjustment = 0

		//Make sure we ignore any pay stub amendments that happen to belong to deleted pay stub accounts.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $psealf->getTable() .' as psea
					where
						a.pay_stub_entry_name_id = psea.id
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.user_id in ('.$this->getListSQL($user_id, $ph) .')
						AND ( a.deleted = 0 AND psea.deleted = 0 )
					ORDER BY a.effective_date asc, a.type_id asc
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndAuthorizedAndYTDAdjustmentAndStartDateAndEndDate($user_id, $authorized, $ytd_adjustment, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $authorized == '') {
			return FALSE;
		}

		if ( $ytd_adjustment == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$ph = array(
					'authorized' => $this->toBool($authorized),
					'start_date' => $start_date,
					'end_date' => $end_date,
					'ytd_adjustment' => $this->toBool($ytd_adjustment),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						authorized = ?
						AND effective_date >= ?
						AND effective_date <= ?
						AND ytd_adjustment = ?
						AND user_id in ('.$this->getListSQL($user_id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;

	}

	function getAmountSumByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate($user_id, $type_id, $authorized, $start_date, $end_date, $where = NULL, $order = NULL) {
		$psalf = new PayStubAmendmentListFactory();
		$psalf->getByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate($user_id, $type_id, $authorized, $start_date, $end_date, $where, $order );
		if ( $psalf->getRecordCount() > 0 ) {
			$sum = 0;
			Debug::text('Record Count: '. $psalf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			foreach($psalf as $psa_obj) {
				$amount = $psa_obj->getCalculatedAmount();
				Debug::text('PS Amendment Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
				$sum += $amount;
			}

			return $sum;
		}

		Debug::text('No PS Amendments found...', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function getByUserIdAndTypeIdAndAuthorizedAndStartDateAndEndDate($user_id, $type_id, $authorized, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = array(
					'user_id' => $user_id,
					'type_id' => $type_id,
					'authorized' => $this->toBool($authorized),
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		//select	sum(amount)
		//						AND a.tax_exempt = \''. $this->toBool($tax_exempt) .'\'
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $psealf->getTable() .' as b
					where	a.pay_stub_entry_name_id = b.id
						AND	a.user_id = ?
						AND b.type_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND a.ytd_adjustment = 0
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAmountSumByUserIdAndNameIdAndAuthorizedAndStartDateAndEndDate($user_id, $name_id, $authorized, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $name_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();

		$ph = array(
					'user_id' => $user_id,
					'authorized' => $this->toBool($authorized),
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		$query = '
					select	sum(amount)
					from	'. $this->getTable() .' as a,
							'. $psealf->getTable() .' as b
					where	a.pay_stub_entry_name_id = b.id
						AND	a.user_id = ?
						AND a.authorized = ?
						AND a.effective_date >= ?
						AND a.effective_date <= ?
						AND b.id in ('.$this->getListSQL($name_id, $ph) .')
						AND a.ytd_adjustment = 0
						AND a.deleted = 0
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$sum = $this->db->GetOne($query, $ph);

		if ( $sum !== FALSE OR $sum !== NULL) {
			Debug::text('Amount Sum: '. $sum, __FILE__, __LINE__, __METHOD__, 10);
			return $sum;
		}

		Debug::text('Amount Sum is NULL', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
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

		$additional_order_fields = array('b.last_name', 'b.first_name');
		if ( $order == NULL ) {
			$order = array( 'a.effective_date' => 'desc', 'a.status_id' => 'asc', 'b.last_name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always try to order by status first so UNPAID employees go to the bottom.
			if ( isset($order['last_name']) ) {
				$order['b.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
			if ( isset($order['first_name']) ) {
				$order['b.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['type']) ) {
				$order['type_id'] = $order['type'];
				unset($order['type']);
			}
			if ( isset($order['status']) ) {
				$order['status_id'] = $order['status'];
				unset($order['status']);
			}

			if ( isset($order['effective_date']) ) {
				$order['b.last_name'] = 'asc';
			} else {
				$order['a.effective_date'] = 'desc';
			}

			$strict = TRUE;
		}
		//Debug::Arr($order, 'bOrder Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$psealf = new PayStubEntryAccountListFactory();


		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
						LEFT JOIN '. $psealf->getTable() .' as c ON a.pay_stub_entry_name_id  = c.id
					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND b.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND b.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query	.=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query	.=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query	.=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query	.=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query	.=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['recurring_ps_amendment_id']) AND isset($filter_data['recurring_ps_amendment_id'][0]) AND !in_array(-1, (array)$filter_data['recurring_ps_amendment_id']) ) {
			$query	.=	' AND a.recurring_ps_amendment_id in ('. $this->getListSQL($filter_data['recurring_ps_amendment_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['start_date']));
			$query	.=	' AND a.effective_date >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['end_date']));
			$query	.=	' AND a.effective_date <= ?';
		}
		if ( isset($filter_data['effective_date']) AND !is_array($filter_data['effective_date']) AND trim($filter_data['effective_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['effective_date']));
			$query	.=	' AND a.effective_date = ?';
		}

		$query .=	'
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

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

		$additional_order_fields = array('pay_stub_entry_name', 'user_status_id', 'last_name', 'first_name', 'default_branch', 'default_department', 'user_group', 'title');

		$sort_column_aliases = array(
									'user_status' => 'user_status_id',
									'status' => 'status_id',
									'type' => 'type_id',
									);
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'effective_date' => 'desc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always sort by effective_date, last name after other columns
			if ( !isset($order['effective_date']) ) {
				$order['effective_date'] = 'desc';
			}

			if ( !isset($order['last_name']) ) {
				$order['last_name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
			$to_timestamp_sql = 'from_unixtime(a.effective_date)';
		} else {
			$to_timestamp_sql = 'to_timestamp(a.effective_date)';
		}

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$pseaf = new PayStubEntryAccountFactory();
		$ppf = new PayPeriodFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							b.first_name as first_name,
							b.last_name as last_name,
							b.status_id as user_status_id,

							b.default_branch_id as default_branch_id,
							bf.name as default_branch,
							b.default_department_id as default_department_id,
							df.name as default_department,
							b.group_id as group_id,
							ugf.name as user_group,
							b.title_id as title_id,
							utf.name as title,

							pseaf.name as pay_stub_entry_name,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $bf->getTable() .' as bf ON ( b.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as df ON ( b.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as ugf ON ( b.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as utf ON ( b.title_id = utf.id AND utf.deleted = 0 )

						LEFT JOIN '. $pseaf->getTable() .' as pseaf ON ( a.pay_stub_entry_name_id = pseaf.id AND pseaf.deleted = 0 )
						LEFT JOIN '. $ppsuf->getTable() .' as ppsuf ON ( a.user_id = ppsuf.user_id )
						LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )
						LEFT JOIN '. $ppf->getTable() .' as ppf ON ( ppsuf.pay_period_schedule_id = ppf.pay_period_schedule_id AND '. $to_timestamp_sql .' >= ppf.start_date AND '. $to_timestamp_sql .' <= ppf.end_date AND ppf.deleted = 0 )

						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'b.id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'ppf.id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['recurring_ps_amendment_id']) ) ? $this->getWhereClauseSQL( 'a.recurring_ps_amendment_id', $filter_data['recurring_ps_amendment_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['start_date'], 'start_date', $ph ) : NULL;
		$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['end_date'], 'end_date', $ph ) : NULL;
		$query .= ( isset($filter_data['effective_date']) ) ? $this->getWhereClauseSQL( 'a.effective_date', $filter_data['effective_date'], 'end_date', $ph ) : NULL;
/*
		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = (int)$filter_data['start_date'];
			$query .= ' AND a.effective_date >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = (int)$filter_data['end_date'];
			$query .= ' AND a.effective_date <= ?';
		}

		if ( isset($filter_data['effective_date']) AND !is_array($filter_data['effective_date']) AND trim($filter_data['effective_date']) != '' ) {
			$ph[] = (int)$filter_data['effective_date'];
			$query .= ' AND a.effective_date <= ?';
		}
*/
/*
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND b.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND b.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query	.=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query	.=	' AND ppf.id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}

		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query	.=	' AND b.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query	.=	' AND b.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query	.=	' AND b.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query	.=	' AND b.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}

		if ( isset($filter_data['recurring_ps_amendment_id']) AND isset($filter_data['recurring_ps_amendment_id'][0]) AND !in_array(-1, (array)$filter_data['recurring_ps_amendment_id']) ) {
			$query	.=	' AND a.recurring_ps_amendment_id in ('. $this->getListSQL($filter_data['recurring_ps_amendment_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['start_date']));
			$query	.=	' AND a.effective_date >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['end_date']));
			$query	.=	' AND a.effective_date <= ?';
		}
		if ( isset($filter_data['effective_date']) AND !is_array($filter_data['effective_date']) AND trim($filter_data['effective_date']) != '' ) {
			$ph[] = strtolower(trim($filter_data['effective_date']));
			$query	.=	' AND a.effective_date = ?';
		}
*/

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		//Need to account for employees being assigned to deleted pay period schedules.
		$query .=	' AND ( ppsuf.id IS NULL OR ppsf.id IS NOT NULL ) AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($query, 'Query: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
