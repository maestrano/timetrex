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
 * $Revision: 10749 $
 * $Id: UserDateTotalListFactory.class.php 10749 2013-08-26 22:00:42Z ipso $
 * $Date: 2013-08-26 15:00:42 -0700 (Mon, 26 Aug 2013) $
 */

/**
 * @package Core
 */
class UserDateTotalListFactory extends UserDateTotalFactory implements IteratorAggregate {

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

	function getByIDAndCompanyId($id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
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

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND a.id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserDateIdAndStatusAndOverride($user_date_id, $status, $override = FALSE) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		/*
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}
		*/

		$ph = array(
					'user_date_id' => $user_date_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND override = ?
						AND status_id in ('. $this->getListSQL($status, $ph) .')
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndOverrideAndMisMatchPunchControlUserDateId($user_date_id, $status, $override = FALSE) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$pcf = new PunchControlFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'override' => $this->toBool( $override ),
					);

		//Don't check for JUST b.deleted = 0 because of the LEFT JOIN, it might be NULL too.
		//There is a bug where sometimes a user_date_total row is orphaned with no punch_control rows that aren't deleted
		//So make sure this query includes those orphaned rows so they can be deleted.
		//( a.user_date_id != b.user_date_id OR b.deleted = 1 )
		//Ensures that all worked time entries that are map to a punch_control row that is marked as deleted
		//will also be returned so they can be deleted.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					where	a.user_date_id = ?
						AND
							(
								( a.override = ? AND a.status_id in ('. $this->getListSQL($status, $ph) .') )
								OR
								( b.id IS NOT NULL AND ( a.user_date_id != b.user_date_id OR b.deleted = 1 ) )
							)
						AND ( a.deleted = 0 )
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateId($user_date_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserDateIdAndType($user_date_id, $type) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND type_id in ('. $this->getListSQL($type, $ph) .')
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatus($user_date_id, $status, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$additional_order_fields = array('time_stamp');

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'desc', 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Want to be able to see overridden or, just time added on its own?
		//LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND c.status_id = 10
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND ( c.status_id = 10 OR c.status_id IS NULL )
					where	a.user_date_id = ?
						AND a.status_id in ('. $this->getListSQL($status, $ph) .')
						AND ( a.deleted = 0
								AND ( b.deleted=0 OR b.deleted IS NULL )
								AND ( c.deleted=0 OR c.deleted IS NULL ) )
					';
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserDateIdAndStatusAndType($user_date_id, $status, $type, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'desc', 'a.type_id' => 'asc', 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Want to be able to see overridden or, just time added on its own?
		//LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND c.status_id = 10
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND ( c.status_id = 10 OR c.status_id IS NULL )
					where	a.user_date_id = ?
						AND a.status_id in ('. $this->getListSQL($status, $ph) .')
						AND a.type_id in ('. $this->getListSQL($type, $ph) .')
						AND ( a.deleted = 0
								AND ( b.deleted=0 OR b.deleted IS NULL )
								AND ( c.deleted=0 OR c.deleted IS NULL ) )
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndPunchControlIdAndOverride($user_date_id, $status, $type, $punch_control_id, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $punch_control_id == FALSE ) {
			$punch_control_id = NULL;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'punch_control_id' => (int)$punch_control_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.punch_control_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndOverride($user_date_id, $status, $type, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndOverTimePolicyIdAndOverride($user_date_id, $status, $type, $over_time_policy_id, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'over_time_policy_id' => $over_time_policy_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.over_time_policy_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndPremiumPolicyIdAndOverride($user_date_id, $status, $type, $premium_policy_id, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'premium_policy_id' => $premium_policy_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.premium_policy_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndMealPolicyIdAndOverride($user_date_id, $status, $type, $meal_policy_id, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'meal_policy_id' => $meal_policy_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.meal_policy_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndStatusAndTypeAndAbsencePolicyIdAndOverride($user_date_id, $status, $type, $absence_policy_id, $override = FALSE, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $absence_policy_id == FALSE ) {
			$absence_policy_id = NULL;
		}

		if ( $order == NULL ) {
			//$order = array( 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					'status' => $status,
					'type' => $type,
					'absence_policy_id' => $absence_policy_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.user_date_id = ?
						AND a.status_id = ?
						AND a.type_id = ?
						AND a.absence_policy_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPunchControlId($punch_control_id) {
		if ( $punch_control_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'punch_control_id' => $punch_control_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where punch_control_id = ?
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateIdAndPunchControlId($user_date_id, $punch_control_id) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $punch_control_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					'punch_control_id' => $punch_control_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND punch_control_id = ?
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getTotalSumByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$apf = new AbsencePolicyFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Don't include total time row
		//Include paid absences
		$query = '
					select 	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					LEFT JOIN '. $apf->getTable() .' as c ON a.absence_policy_id = c.id
					where 	a.user_date_id = ?
						AND ( a.status_id in (20,30) OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
						AND ( c.type_id IS NULL OR c.type_id in ( 10, 12 ) )
						AND ( a.deleted = 0 AND (b.deleted=0 OR b.deleted is NULL) )
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getWorkedTimeSumByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Don't include total time row, OR paid absences
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND status_id = 20
						AND deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getOverTimeSumByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Don't include total time row
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND type_id = 30
						AND deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getPaidAbsenceSumByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $apf->getTable() .' as b
					where 	a.absence_policy_id = b.id
						AND b.type_id in ( 10, 12 )
						AND a.user_date_id = ?
						AND a.status_id = 30
						AND a.deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getMealPolicySumByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a
					where
						a.user_date_id = ?
						AND a.status_id = 40
						AND a.deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getPremiumPolicySumByUserDateIDAndPremiumPolicyID( $user_date_id, $premium_policy_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		if ( $premium_policy_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					'premium_policy_id' => $premium_policy_id,
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a
					where
						a.user_date_id = ?
						AND a.premium_policy_id = ?
						AND a.status_id = 10
						AND a.type_id = 40
						AND a.deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getWeekRegularTimeSumByUserIDAndEpochAndStartWeekEpoch( $user_id, $epoch, $week_start_epoch ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( $week_start_epoch == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'week_start_epoch' => $this->db->BindDate( $week_start_epoch ),
					'epoch' =>  $this->db->BindDate( $epoch ),
					);

		//DO NOT Include paid absences. Only count regular time towards weekly overtime.
		//And other weekly/bi-weekly overtime polices!
		$query = '
					select 	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $otpf->getTable() .' as c ON a.over_time_policy_id = c.id
					where
						b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp < ?
						AND a.status_id = 10
						AND (
							a.type_id = 20
							OR ( a.type_id = 30 AND c.type_id in ( 20, 30, 210 ) )
							)
						AND a.absence_policy_id = 0
						AND a.deleted = 0
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		//Debug::text('Total: '. $total .' Week Start: '. TTDate::getDate('DATE+TIME', $week_start_epoch ) .' End: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	//Make sure we take into account auto-deduct/add meal/break policies.
	function getWeekWorkedTimeSumByUserIDAndEpochAndStartWeekEpoch( $user_id, $epoch, $week_start_epoch ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( $week_start_epoch == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'week_start_epoch' => $this->db->BindDate( $week_start_epoch ),
					'epoch' =>  $this->db->BindDate( $epoch ),
					);

		$query = '
					select 	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					where
						b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp < ?
						AND ( a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
						AND a.absence_policy_id = 0
						AND a.deleted = 0
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		//Debug::text('Total: '. $total .' Week Start: '. TTDate::getDate('DATE+TIME', $week_start_epoch ) .' End: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getPaidAbsenceByUserDateID( $user_date_id ) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		//Include only paid absences.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $apf->getTable() .' as b
					where 	a.absence_policy_id = b.id
						AND b.type_id in ( 10, 12 )
						AND a.user_date_id = ?
						AND a.status_id = 30
						AND a.deleted = 0
				';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($company_id, $user_id, $status, $start_date, $end_date) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'status_id' => $status,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Order by a.over_time_policy last so we never leave the ordering up to the database. This can cause
		//the unit tests to fail between databases.
		//AND a.type_id != 40
		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $otpf->getTable() .' as d ON a.over_time_policy_id = d.id
					where
						c.company_id = ?
						AND	b.user_id = ?
						AND a.status_id = ?
						AND a.type_id not in (40,100,110)
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					ORDER BY b.date_stamp asc, a.status_id asc, a.type_id asc, d.type_id desc, a.over_time_policy_id desc, a.premium_policy_id, a.total_time, a.id
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndUserIdAndStatusAndTypeAndStartDateAndEndDate($company_id, $user_id, $status, $type, $start_date, $end_date) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					//'status_id' => $status,
					//'type' => $type,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $otpf->getTable() .' as d ON a.over_time_policy_id = d.id
					where
						c.company_id = ?
						AND	b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id in ('. $this->getListSQL($status, $ph) .')
						AND a.type_id in ('. $this->getListSQL($type, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					ORDER BY b.date_stamp asc, a.status_id asc, a.type_id asc, d.type_id desc, a.total_time asc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getPaidTimeByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate($company_id, $user_id, $status, $start_date, $end_date) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//						AND a.type_id != 40
		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $otpf->getTable() .' as d ON a.over_time_policy_id = d.id
					LEFT JOIN '. $apf->getTable() .' as e ON a.absence_policy_id = e.id
					LEFT JOIN '. $pcf->getTable() .' as f ON a.punch_control_id = f.id
					where
						c.company_id = ?
						AND	b.user_id = ?
						AND a.type_id not in (10,40,100,110)
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id in ('. $this->getListSQL($status, $ph) .')
						AND ( e.type_id is NULL OR e.type_id in ( 10, 12 ) )
						AND ( a.deleted = 0 AND b.deleted = 0 AND (f.deleted=0 OR f.deleted is NULL) )
					ORDER BY b.date_stamp asc, a.status_id asc, a.type_id asc, d.type_id desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//This isn't JUST worked time, it also includes paid lunch/break time. Specifically in auto-deduct cases
	//where they work 8.5hrs and only get paid for 8hrs due to auto-deduct lunch or breaks.
	function getWorkedTimeSumByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND ( a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getRegularTimeSumByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 10
						AND a.type_id = 20
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getAbsenceTimeSumByUserIDAndAbsenceIDAndStartDateAndEndDate( $user_id, $absence_policy_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $absence_policy_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		//Include only paid absences.
		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 30
						AND a.absence_policy_id in ('. $this->getListSQL($absence_policy_id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getPaidAbsenceTimeSumByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		//Include only paid absences.
		$udf = new UserDateFactory();
		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND a.absence_policy_id = c.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 30
						AND c.type_id in ( 10, 12 )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getDaysWorkedByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//This includes days where they only got overtime.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select 	distinct(b.date_stamp)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 AND b.deleted=0 )
				';
				
		return $this->db->getCol( $query, $ph );
	}

	function getDaysWorkedRegularTimeByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//This includes days where they only got overtime.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select 	distinct(b.date_stamp)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 10
						AND a.type_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 AND b.deleted=0 )
				';
		/*
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
		*/
		return $this->db->getCol( $query, $ph );
	}

	//Finds number of days the employee received paid absence time.
	function getDaysPaidAbsenceByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select 	distinct(b.date_stamp)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND a.absence_policy_id = c.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.status_id = 30
						AND a.total_time > 0
						AND c.type_id in ( 10, 12 )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		/*
		$total = $this->db->GetOne($query, $ph);
		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);
		return $total;		
		*/
		
		return $this->db->getCol( $query, $ph );
	}

	function getDaysWorkedByUserIDAndStartDateAndEndDateAndDayOfWeek( $user_id, $start_date, $end_date, $day_of_week ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $day_of_week == '' ) {
			return FALSE;
		}

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
			$day_of_week_clause = ' (dayofweek(b.date_stamp)-1) '; //Sunday=1 with MySQL, so we need to minus one so it matches PHP Sunday=0
		} else {
			$day_of_week_clause = ' extract(dow from b.date_stamp) ';
		}

		Debug::text('Day Of Week: '. $day_of_week, __FILE__, __LINE__, __METHOD__, 10);

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					'day_of_week' => $day_of_week,
					);

		//Include only paid absences.
		$query = '
					select 	count(distinct(a.user_date_id))
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND '. $day_of_week_clause .' = ?
						AND a.status_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getDaysWorkedByUserIDAndUserDateIDs( $user_id, $user_date_ids ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $user_date_ids == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					);

		//Include only paid absences.
		$query = '
					select 	count(distinct(a.user_date_id))
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND a.user_date_id in ('. $this->getListSQL($user_date_ids, $ph) .')
						AND a.status_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	/*

			Pay period sums

	*/
	function getWorkedUsersByPayPeriodId( $pay_period_id ) {
		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		$query = '
					select 	count(distinct(b.user_id))
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.pay_period_id = ?
						AND a.status_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND ( a.status_id in (20,30) OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total .' Pay Period: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getWorkedTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND ( a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total .' Pay Period: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getRegularAndOverTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Ignore the type_id = 10 //Total Time
		$query = '
					select 	a.type_id as type_id,
							a.over_time_policy_id as over_time_policy_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND a.status_id = 10
						AND a.type_id not in ( 10, 40, 100, 110 )
						AND ( a.deleted = 0 AND b.deleted=0 )
					group by a.type_id, a.over_time_policy_id
					order by a.type_id, a.over_time_policy_id
				';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getPaidAbsenceTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND a.absence_policy_id = c.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND a.status_id = 30
						AND c.type_id in ( 10, 12 )
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getPremiumTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $ppf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND a.premium_policy_id = c.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND a.status_id = 10
						AND a.type_id = 40
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getDockAbsenceTimeSumByUserIDAndPayPeriodId( $user_id, $pay_period_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$apf = new AbsencePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	sum(total_time)
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $apf->getTable() .' as c
					where 	a.user_date_id = b.id
						AND a.absence_policy_id = c.id
						AND b.user_id = ?
						AND b.pay_period_id = ?
						AND a.status_id = 30
						AND c.type_id = 30
						AND ( a.deleted = 0 AND b.deleted=0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByOverTimePolicyId($over_time_policy_id, $where = NULL, $order = NULL) {
		if ( $over_time_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'over_time_policy_id' => $over_time_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	over_time_policy_id = ?
						AND deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getPreviousDayByUserIdAndStartDateAndEndDateAndOverTimePolicyId($user_id, $start_date, $end_date, $over_time_policy_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $over_time_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					'over_time_policy_id' => $over_time_policy_id,
					);

		$udf = new UserDateFactory();

		$query = '
					select 	b.date_stamp
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					where
						b.user_id = ?
						AND b.date_stamp >= ? AND b.date_stamp < ?
						AND a.over_time_policy_id = ?
						AND a.deleted = 0
					ORDER BY b.date_stamp desc
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$date = $this->db->GetOne($query, $ph);

		return $date;
	}

	function getByMealPolicyId($meal_policy_id, $where = NULL, $order = NULL) {
		if ( $meal_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'meal_policy_id' => $meal_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	meal_policy_id = ?
						AND deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByBreakPolicyId($break_policy_id, $where = NULL, $order = NULL) {
		if ( $break_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'break_policy_id' => $break_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	break_policy_id = ?
						AND deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPremiumTimePolicyId($premium_policy_id, $where = NULL, $order = NULL) {
		if ( $premium_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'premium_policy_id' => $premium_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	premium_policy_id = ?
						AND deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByAbsencePolicyId($absence_policy_id, $where = NULL, $order = NULL) {
		if ( $absence_policy_id == '') {
			return FALSE;
		}

		$ph = array(
					'absence_policy_id' => $absence_policy_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	absence_policy_id = ?
						AND deleted = 0
					LIMIT 1
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByJobId($job_id, $where = NULL, $order = NULL) {
		if ( $job_id == '') {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();

		$ph = array(
					'job_id' => $job_id,
					);

		$query = '
					select 	a.*,
							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = 0
																			and z.deleted = 0
																			order by z.effective_date desc LIMIT 1)

					where	a.job_id = ?
						AND a.status_id = 10
						AND ( a.deleted = 0 AND b.deleted = 0)
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByJobId($job_ids) {
		if ( $job_ids == '') {
			return FALSE;
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array();

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		//Used to use this group by line for some reason, changed it to match that of getReportByStartDateAndEndDateAndJobList
		//--group by b.user_id, user_wage_id, user_wage_effective_date, a.job_id, a.job_item_id, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
		$query = '
					select
							b.user_id as user_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time,
							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where
						a.job_id in ('. $this->getListSQL($job_ids, $ph) .')
						AND a.status_id in (10)
						AND ( a.deleted = 0 AND b.deleted = 0 AND uf.deleted = 0 )
					group by b.user_id,user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id order by a.job_id asc
				';
		//$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndPayPeriodIdAndEndDate($user_id, $pay_period_id, $end_date = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pay_period_id == '') {
			return FALSE;
		}

		if ( $end_date == NULL ) {
			//Get pay period end date.
			$pplf = new PayPeriodListFactory();
			$pplf->getById( $pay_period_id );
			if ( $pplf->getRecordCount() > 0 ) {
				$pp_obj = $pplf->getCurrent();
				$end_date = $pp_obj->getEndDate();
			}
		}

		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					'end_date' =>  $this->db->BindDate( $end_date ),
					);

/*
					select 	a.status_id as status_id,
							a.type_id as type_id,
							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,
							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_Time) as total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where
						b.user_id = ?
						AND b.pay_period_id = ?
						AND b.date_stamp <= ?
						AND a.status_id in (10,30)
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by user_wage_id, user_wage_effective_date, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					order by a.status_id desc, a.type_id asc, user_wage_effective_date desc
*/
		//Order dock hours first, so it can be deducted from regular time.
		//Order newest wage changes first too. This is VERY important for calculating pro-rate amounts.
		$query = '
					select 	a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_Time) as total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.wage_group_id = 0
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where
						b.user_id = ?
						AND b.pay_period_id = ?
						AND b.date_stamp <= ?
						AND a.status_id in (10,30)
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					order by a.status_id desc, a.type_id asc, user_wage_effective_date desc, over_time_policy_wage_effective_date desc, absence_policy_wage_effective_date desc, premium_policy_wage_effective_date desc
				';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByPayPeriodIDListAndUserIdList($pay_period_ids, $user_ids, $order = NULL) {
		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $pay_period_ids == '') {
			return FALSE;
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();

		$ph = array();

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
				select z.id, tmp.*
				from '. $ulf->getTable() .' as z
				LEFT JOIN

					( select  b.user_id,
							a.status_id as status_id,
							a.type_id as type_id,
							b.pay_period_id as pay_period_id,
							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,
							sum(total_Time) as total_time,
							sum(actual_total_Time) as actual_total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					where 	a.user_date_id = b.id
						AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
					';

		if ( $pay_period_ids != '' AND isset($pay_period_ids[0]) AND !in_array(-1, (array)$pay_period_ids) ) {
			$query .= ' AND b.pay_period_id in ('. $this->getListSQL($pay_period_ids, $ph) .') ';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id,b.pay_period_id,a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					) as tmp ON z.id = tmp.user_id
				WHERE z.id in ('. $this->getListSQL($user_ids, $ph) .')
					AND z.deleted = 0
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['user_ids']) ) {
			$filter_data['user_id'] = $filter_data['user_ids'];
		}
		if ( isset($filter_data['job_ids']) ) {
			$filter_data['job_id'] = $filter_data['job_ids'];
		}

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$ppf = new PayPeriodFactory();

		$ph = array();

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
				select z.id, tmp.*
				from '. $ulf->getTable() .' as z
				LEFT JOIN
					( select  b.user_id,
							a.status_id as status_id,
							a.type_id as type_id,
							b.pay_period_id as pay_period_id,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,
							sum(a.total_time) as total_time,
							sum(a.actual_total_Time) as actual_total_time,
							count( distinct(CASE WHEN a.status_id = 20 AND a.type_id = 10 AND a.total_time > 0 THEN a.user_date_id ELSE NULL END) ) as worked_days
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $ppf->getTable() .' as c ON b.pay_period_id = c.id
					where 1=1
					';

					if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
						$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
					}

					if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
						$query .= 	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
					}

					if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
						$query .= 	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
					}
					if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
						$query .= 	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
					}

					if ( isset($filter_data['transaction_start_date']) AND trim($filter_data['transaction_start_date']) != '' ) {
						$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_start_date'])) );
						$query  .=	' AND c.transaction_date >= ?';
					}
					if ( isset($filter_data['transaction_end_date']) AND trim($filter_data['transaction_end_date']) != '' ) {
						$ph[] = $this->db->BindTimeStamp( strtolower(trim($filter_data['transaction_end_date'])) );
						$query  .=	' AND c.transaction_date <= ?';
					}

					if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
						$ph[] = $this->db->BindDate($filter_data['start_date']);
						$query  .=	' AND b.date_stamp >= ?';
					}
					if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
						$ph[] = $this->db->BindDate($filter_data['end_date']);
						$query  .=	' AND b.date_stamp <= ?';
					}

					if ( isset($filter_data['job_id']) AND isset($filter_data['job_id'][0]) AND !in_array(-1, (array)$filter_data['job_id']) ) {
						$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['job_id'], $ph) .') ';
					}
					if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
						$query  .=	' AND a.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
					}

		$ph[] = $company_id;

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id, b.pay_period_id, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					) as tmp ON z.id = tmp.user_id
				WHERE z.company_id = ? ';

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND z.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		$query .= ' AND z.deleted = 0 ';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;

	}

	function getDayReportByPayPeriodIDListAndUserIdList($pay_period_ids, $user_ids, $order = NULL) {
		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $pay_period_ids == '') {
			return FALSE;
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		$order = array( 'tmp.pay_period_id' => 'asc','z.last_name' => 'asc', 'tmp.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array();

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
				select z.id, tmp.*
				from '. $ulf->getTable() .' as z
				LEFT JOIN

					( select
							b.user_id,
							b.pay_period_id as pay_period_id,
							b.date_stamp as date_stamp,
							a.status_id as status_id,
							a.type_id as type_id,
							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,
							tmp2.min_punch_time_stamp as min_punch_time_stamp,
							tmp2.max_punch_time_stamp as max_punch_time_stamp,
							sum(total_Time) as total_time,
							sum(actual_total_Time) as actual_total_time
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b
					LEFT JOIN (
						select tmp2_a.id, min(tmp2_c.time_stamp) as min_punch_time_stamp, max(tmp2_c.time_stamp) as max_punch_time_stamp
							from '. $udf->getTable() .' as tmp2_a
							LEFT JOIN '. $pcf->getTable() .' as tmp2_b ON tmp2_a.id = tmp2_b.user_date_id
							LEFT JOIN '. $pf->getTable() .' as tmp2_c ON tmp2_b.id = tmp2_c.punch_control_id
							WHERE tmp2_a.user_id in ('. $this->getListSQL($user_ids, $ph) .') ';

							if ( $pay_period_ids != '' AND isset($pay_period_ids[0]) AND !in_array(-1, (array)$pay_period_ids) ) {
								$query .= ' AND tmp2_a.pay_period_id in ('. $this->getListSQL($pay_period_ids, $ph) .') ';
							}

							$query .= '
								AND tmp2_c.time_stamp is not null
								AND ( tmp2_a.deleted = 0 AND tmp2_b.deleted = 0 AND tmp2_c.deleted = 0 )
							group by tmp2_a.id

					) as tmp2 ON b.id = tmp2.id

					where 	a.user_date_id = b.id
						AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
					';

		if ( $pay_period_ids != '' AND isset($pay_period_ids[0]) AND !in_array(-1, (array)$pay_period_ids) ) {
			$query .= ' AND b.pay_period_id in ('. $this->getListSQL($pay_period_ids, $ph) .') ';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id, b.pay_period_id, b.date_stamp, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id, tmp2.min_punch_time_stamp, tmp2.max_punch_time_stamp
					) as tmp ON z.id = tmp.user_id
				WHERE z.id in ('. $this->getListSQL($user_ids, $ph) .')
					AND z.deleted = 0
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getDayReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		$order = array( 'tmp.pay_period_id' => 'asc','z.last_name' => 'asc', 'tmp.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array();

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
				select z.id, tmp.*
				from '. $ulf->getTable() .' as z
				LEFT JOIN

					( select
							b.user_id,
							b.pay_period_id as pay_period_id,
							b.date_stamp as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							tmp2.min_punch_time_stamp as min_punch_time_stamp,
							tmp2.max_punch_time_stamp as max_punch_time_stamp,
							sum(total_Time) as total_time,
							sum(actual_total_Time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = 0
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					LEFT JOIN (
						select tmp3.id, min(tmp3.min_punch_time_stamp) as min_punch_time_stamp, max(tmp3.max_punch_time_stamp) as max_punch_time_stamp from (
							select tmp2_a.id,
								CASE WHEN tmp2_c.status_id = 10 THEN min(tmp2_c.time_stamp) ELSE NULL END as min_punch_time_stamp,
								CASE WHEN tmp2_c.status_id = 20 THEN max(tmp2_c.time_stamp) ELSE NULL END as max_punch_time_stamp
								from '. $udf->getTable() .' as tmp2_a
								LEFT JOIN '. $pcf->getTable() .' as tmp2_b ON tmp2_a.id = tmp2_b.user_date_id
								LEFT JOIN '. $pf->getTable() .' as tmp2_c ON tmp2_b.id = tmp2_c.punch_control_id
								WHERE 1=1 ';
								if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
									$query  .=	' AND tmp2_a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
								}

								if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
									$query .= 	' AND tmp2_a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
								}

								if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
									$ph[] = $this->db->BindDate($filter_data['start_date']);
									$query  .=	' AND tmp2_a.date_stamp >= ?';
								}
								if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
									$ph[] = $this->db->BindDate($filter_data['end_date']);
									$query  .=	' AND tmp2_a.date_stamp <= ?';
								}

								$query .= '
									AND tmp2_c.time_stamp is not null
									AND ( tmp2_a.deleted = 0 AND tmp2_b.deleted = 0 AND tmp2_c.deleted = 0 )
								group by tmp2_a.id, tmp2_c.status_id
							) as tmp3 group by tmp3.id
					) as tmp2 ON b.id = tmp2.id

					where 	1=1 ';

					if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
						$query  .=	' AND b.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
					}

		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}

 		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query .= 	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
 		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query .= 	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		$ph[] = $company_id;

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, b.pay_period_id, a.branch_id, a.department_id, b.date_stamp, user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id, tmp2.min_punch_time_stamp, tmp2.max_punch_time_stamp
					) as tmp ON z.id = tmp.user_id
				WHERE z.company_id = ? ';

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND z.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		$query .= ' AND z.deleted = 0 ';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByStartDateAndEndDateAndUserIdList($start_date, $end_date, $user_ids, $order = NULL) {
		if ( $user_ids == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
				select z.id, tmp.*
				from '. $ulf->getTable() .' as z
				LEFT JOIN
					( select  b.user_id,
							b.date_stamp as date_stamp,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_Time) as total_time,
							sum(actual_total_Time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where
						b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND a.status_id in (10,30)
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id, b.date_stamp, user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					) as tmp ON z.id = tmp.user_id
				WHERE z.id in ('. $this->getListSQL($user_ids, $ph) .')
					AND z.deleted = 0
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByStartDateAndEndDateAndJobList($start_date, $end_date, $job_ids, $order = NULL) {
		if ( $job_ids == '') {
			Debug::Text('No Job Ids: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $start_date == '') {
			//Debug::Text('No Start Date: ', __FILE__, __LINE__, __METHOD__,10);
			$start_date = 0;
		}

		if ( $end_date == '') {
			//Debug::Text('No End Date: ', __FILE__, __LINE__, __METHOD__,10);
			$end_date = time();
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '

					select  b.user_id as user_id,
							a.status_id as status_id,
							a.type_id as type_id,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time,
							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where 	a.user_date_id = b.id
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND a.job_id in ('. $this->getListSQL($job_ids, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id,user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					order by a.job_id asc
				';
		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)

		$query .= $this->getSortSQL( $order, FALSE );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByStartDateAndEndDateAndUserIdListAndJobListAndJobItemList($start_date, $end_date, $user_ids, $job_ids, $job_item_ids, $order = NULL) {
		if ( $user_ids == '') {
			Debug::Text('No User Ids: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $job_ids == '') {
			Debug::Text('No Job Ids: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $job_item_ids == '') {
			Debug::Text('No Job Item Ids: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		if ( $start_date == '') {
			Debug::Text('No Start Date: ', __FILE__, __LINE__, __METHOD__,10);
			$start_date = 0;
		}

		if ( $end_date == '') {
			Debug::Text('No End Date: ', __FILE__, __LINE__, __METHOD__,10);
			$end_date = time();
		}

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'z.last_name' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$ulf = new UserListFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '

					select  b.user_id as user_id,
							a.status_id as status_id,
							a.type_id as type_id,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,

							a.over_time_policy_id as over_time_policy_id,
							n.id as over_time_policy_wage_id,
							n.effective_date as over_time_policy_wage_effective_date,

							a.absence_policy_id as absence_policy_id,
							p.id as absence_policy_wage_id,
							p.effective_date as absence_policy_wage_effective_date,

							a.premium_policy_id as premium_policy_id,
							r.id as premium_policy_wage_id,
							r.effective_date as premium_policy_wage_effective_date,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date,
							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time,
							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id

					LEFT JOIN '. $otpf->getTable() .' as m ON a.over_time_policy_id = m.id
					LEFT JOIN '. $uwf->getTable() .' as n ON n.id = (select n.id
																		from '. $uwf->getTable() .' as n
																		where n.user_id = b.user_id
																			and n.wage_group_id = m.wage_group_id
																			and n.effective_date <= b.date_stamp
																			and n.deleted = 0
																			order by n.effective_date desc limit 1)

					LEFT JOIN '. $apf->getTable() .' as o ON a.absence_policy_id = o.id
					LEFT JOIN '. $uwf->getTable() .' as p ON p.id = (select p.id
																		from '. $uwf->getTable() .' as p
																		where p.user_id = b.user_id
																			and p.wage_group_id = o.wage_group_id
																			and p.effective_date <= b.date_stamp
																			and p.deleted = 0
																			order by p.effective_date desc limit 1)

					LEFT JOIN '. $ppf->getTable() .' as q ON a.premium_policy_id = q.id
					LEFT JOIN '. $uwf->getTable() .' as r ON r.id = (select r.id
																		from '. $uwf->getTable() .' as r
																		where r.user_id = b.user_id
																			and r.wage_group_id = q.wage_group_id
																			and r.effective_date <= b.date_stamp
																			and r.deleted = 0
																			order by r.effective_date desc limit 1)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					where 	a.user_date_id = b.id
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND a.job_id in ('. $this->getListSQL($job_ids, $ph) .')
					';
		//AND a.job_item_id in ('. $this->getListSQL($job_item_ids, $ph) .')

		$filter_query = NULL;
		if ( $job_item_ids != '' AND isset($job_item_ids[0]) AND !in_array(-1, $job_item_ids) ) {
			$query  .=	' AND a.job_item_id in ('. $this->getListSQL($job_item_ids, $ph) .') ';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0)
					group by b.user_id,user_wage_id, user_wage_effective_date, over_time_policy_wage_id, over_time_policy_wage_effective_date, absence_policy_wage_id, absence_policy_wage_effective_date, premium_policy_wage_id, premium_policy_wage_effective_date, a.status_id, a.type_id, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
				';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate($time_period, $user_ids, $company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $time_period == '' ) {
			return FALSE;
		}

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		/*
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		//When grouping by time period week, we can't include the month, otherwise weeks that span the month will be counted twice.
		switch ( strtolower($time_period) ) {
			case 'day':
			case 'month':
				$time_period_sql = '(EXTRACT('.$time_period.' FROM b.date_stamp) || \'-\' || EXTRACT(month FROM b.date_stamp) || \'-\' || EXTRACT(year FROM b.date_stamp) )';
				break;
			case 'week':
				$time_period_sql = '(EXTRACT('.$time_period.' FROM b.date_stamp) || \'-\' || EXTRACT(year FROM b.date_stamp) )';
				break;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	user_id,
							status_id,
							type_id,
							over_time_policy_id,
							absence_policy_id,
							premium_policy_id,
							avg(total_time) as avg,
							min(total_time) as min,
							max(total_time) as max,
							count(*) as date_units
					from (
							select 	b.user_id,
									'. $time_period_sql .' as date,
									a.type_id,
									a.status_id,
									over_time_policy_id,
									absence_policy_id,
									premium_policy_id,
									sum(total_time) as total_time
							from	'. $this->getTable() .' as a,
									'. $udf->getTable() .' as b,
									'. $uf->getTable() .' as c
							where 	a.user_date_id = b.id
								AND b.user_id = c.id
								AND c.company_id = ?
								AND b.date_stamp >= ?
								AND b.date_stamp <= ?
								AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
								AND a.total_time > 0
								AND ( a.deleted = 0 AND b.deleted=0 AND c.deleted=0)
							GROUP BY user_id,'. $time_period_sql .',a.status_id,a.type_id,over_time_policy_id,absence_policy_id,premium_policy_id
						) tmp
					GROUP BY user_id,status_id,type_id,over_time_policy_id,absence_policy_id,premium_policy_id
					';

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAffordableCareReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc','uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();
		$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							b.user_id as user_id,
							date_trunc(\'month\',	b.date_stamp ) as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,

							z.hourly_rate as hourly_rate,
							z.labor_burden_percent as labor_burden_percent,

							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id

					LEFT JOIN '. $otpf->getTable() .' as m ON (a.over_time_policy_id = m.id AND m.deleted = 0)
					LEFT JOIN '. $apf->getTable() .' as o ON (a.absence_policy_id = o.id AND o.deleted = 0)
					LEFT JOIN '. $ppf->getTable() .' as q ON (a.premium_policy_id = q.id AND q.deleted = 0)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = (CASE WHEN a.over_time_policy_id > 0 THEN m.wage_group_id
																									ELSE
																										CASE WHEN a.absence_policy_id > 0 THEN o.wage_group_id ELSE
																											CASE WHEN a.premium_policy_id > 0 THEN q.wage_group_id ELSE 0 END
																										END
																									END)
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)

					where 	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['punch_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['punch_department_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_time_sheet_verify_status_id']) ) ? $this->getWhereClauseSQL( 'pptsvlf.status_id', $filter_data['pay_period_time_sheet_verify_status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, a.branch_id, a.department_id, b.date_stamp, z.hourly_rate, z.labor_burden_percent, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph ,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		return $this;
	}

	function getTimesheetSummaryReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc','uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();
		$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							b.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							b.date_stamp as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,

							z.hourly_rate as hourly_rate,
							z.labor_burden_percent as labor_burden_percent,

							min_punch.time_stamp as min_punch_time_stamp,
							max_punch.time_stamp as max_punch_time_stamp,
							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id

					LEFT JOIN '. $otpf->getTable() .' as m ON (a.over_time_policy_id = m.id AND m.deleted = 0)
					LEFT JOIN '. $apf->getTable() .' as o ON (a.absence_policy_id = o.id AND o.deleted = 0)
					LEFT JOIN '. $ppf->getTable() .' as q ON (a.premium_policy_id = q.id AND q.deleted = 0)

					LEFT JOIN '. $pptsvlf->getTable() .' as pptsvlf ON ( ppf.id = pptsvlf.pay_period_id AND b.user_id = pptsvlf.user_id AND pptsvlf.deleted = 0 )

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = (CASE WHEN a.over_time_policy_id > 0 THEN m.wage_group_id
																									ELSE
																										CASE WHEN a.absence_policy_id > 0 THEN o.wage_group_id ELSE
																											CASE WHEN a.premium_policy_id > 0 THEN q.wage_group_id ELSE 0 END
																										END
																									END)
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)

					LEFT JOIN '. $pf->getTable() .' as min_punch ON min_punch.id = (
																		select pf_a.id
																		from '. $pf->getTable() .' as pf_a
																		LEFT JOIN '. $pcf->getTable() .' as pcf_a ON pf_a.punch_control_id = pcf_a.id
																		WHERE pcf_a.user_date_id = a.user_date_id
																			AND pcf_a.branch_id = a.branch_id
																			AND pcf_a.department_id = a.department_id
																			AND pf_a.status_id = 10
																		ORDER BY pf_a.time_stamp ASC
																		LIMIT 1
																		)

					LEFT JOIN '. $pf->getTable() .' as max_punch ON max_punch.id = (
																		select pf_a.id
																		from '. $pf->getTable() .' as pf_a
																		LEFT JOIN '. $pcf->getTable() .' as pcf_a ON pf_a.punch_control_id = pcf_a.id
																		WHERE pcf_a.user_date_id = a.user_date_id
																			AND pcf_a.branch_id = a.branch_id
																			AND pcf_a.department_id = a.department_id
																			AND pf_a.status_id = 20
																		ORDER BY pf_a.time_stamp DESC
																		LIMIT 1
																		)

					where 	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['punch_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['punch_department_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_time_sheet_verify_status_id']) ) ? $this->getWhereClauseSQL( 'pptsvlf.status_id', $filter_data['pay_period_time_sheet_verify_status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.branch_id, a.department_id, b.date_stamp, z.hourly_rate, z.labor_burden_percent, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id, min_punch.time_stamp, max_punch.time_stamp
					';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph ,'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		return $this;
	}

	function getTimesheetDetailReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc','uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		//Show Min/Max punches based on day/branch/department, so we can split reports out day/branch/department and still show
		//  when the employee punched in/out for each.
		$query = '
					select
							b.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							b.date_stamp as date_stamp,
							bf.name as branch,
							df.name as department,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,

							z.hourly_rate as hourly_rate,
							z.labor_burden_percent as labor_burden_percent,

							min_punch.time_stamp as min_punch_time_stamp,
							max_punch.time_stamp as max_punch_time_stamp,
							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id

					LEFT JOIN '. $otpf->getTable() .' as m ON (a.over_time_policy_id = m.id AND m.deleted = 0)
					LEFT JOIN '. $apf->getTable() .' as o ON (a.absence_policy_id = o.id AND o.deleted = 0)
					LEFT JOIN '. $ppf->getTable() .' as q ON (a.premium_policy_id = q.id AND q.deleted = 0)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = (CASE WHEN a.over_time_policy_id > 0 THEN m.wage_group_id
																									ELSE
																										CASE WHEN a.absence_policy_id > 0 THEN o.wage_group_id ELSE
																											CASE WHEN a.premium_policy_id > 0 THEN q.wage_group_id ELSE 0 END
																										END
																									END)
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)

					LEFT JOIN '. $pf->getTable() .' as min_punch ON min_punch.id = (
																		select pf_a.id
																		from '. $pf->getTable() .' as pf_a
																		LEFT JOIN '. $pcf->getTable() .' as pcf_a ON pf_a.punch_control_id = pcf_a.id
																		WHERE pcf_a.user_date_id = a.user_date_id
																			AND pcf_a.branch_id = a.branch_id
																			AND pcf_a.department_id = a.department_id
																			AND pf_a.status_id = 10
																		ORDER BY pf_a.time_stamp ASC
																		LIMIT 1
																		)

					LEFT JOIN '. $pf->getTable() .' as max_punch ON max_punch.id = (
																		select pf_a.id
																		from '. $pf->getTable() .' as pf_a
																		LEFT JOIN '. $pcf->getTable() .' as pcf_a ON pf_a.punch_control_id = pcf_a.id
																		WHERE pcf_a.user_date_id = a.user_date_id
																			AND pcf_a.branch_id = a.branch_id
																			AND pcf_a.department_id = a.department_id
																			AND pf_a.status_id = 20
																		ORDER BY pf_a.time_stamp DESC
																		LIMIT 1
																		)

					where 	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['punch_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['punch_department_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, bf.name, df.name, b.date_stamp, z.hourly_rate, z.labor_burden_percent, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id, min_punch.time_stamp, max_punch.time_stamp
					';

		$query .= $this->getSortSQL( $order, FALSE );
		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		return $this;
	}


	function getJobDetailReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc','uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
		/*
		if ( $order == NULL ) {
			$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		$jf = new JobFactory();
		$jif = new JobItemFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							b.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							b.date_stamp as date_stamp,
							bf.name as branch,
							df.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.premium_policy_id as premium_policy_id,

							z.hourly_rate as hourly_rate,
							z.labor_burden_percent as labor_burden_percent,

							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time,
							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id

					LEFT JOIN '. $jf->getTable() .' as jf ON a.job_id = jf.id
					LEFT JOIN '. $jif->getTable() .' as jif ON a.job_item_id = jif.id

					LEFT JOIN '. $otpf->getTable() .' as m ON (a.over_time_policy_id = m.id AND m.deleted = 0)
					LEFT JOIN '. $apf->getTable() .' as o ON (a.absence_policy_id = o.id AND o.deleted = 0)
					LEFT JOIN '. $ppf->getTable() .' as q ON (a.premium_policy_id = q.id AND q.deleted = 0)

					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = b.user_id
																			and z.effective_date <= b.date_stamp
																			and z.wage_group_id = (CASE WHEN a.over_time_policy_id > 0 THEN m.wage_group_id
																									ELSE
																										CASE WHEN a.absence_policy_id > 0 THEN o.wage_group_id ELSE
																											CASE WHEN a.premium_policy_id > 0 THEN q.wage_group_id ELSE 0 END
																										END
																									END)
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)

					where 	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['user_status']) AND trim($filter_data['user_status']) != '' AND !isset($filter_data['user_status_id']) ) {
			$filter_data['user_status_id'] = Option::getByFuzzyValue( $filter_data['user_status'], $uf->getOptions('status') );
		}
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['punch_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['punch_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['job_status']) AND trim($filter_data['job_status']) != '' AND !isset($filter_data['job_status_id']) ) {
			$filter_data['job_status_id'] = Option::getByFuzzyValue( $filter_data['job_status'], $jf->getOptions('status') );
		}
		$query .= ( isset($filter_data['job_status_id']) ) ? $this->getWhereClauseSQL( 'jf.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'jf.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['job_item_group_id']) ) ? $this->getWhereClauseSQL( 'jif.group_id', $filter_data['job_item_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, bf.name, df.name, a.job_id, a.job_item_id, b.date_stamp, z.hourly_rate, z.labor_burden_percent, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id, a.premium_policy_id
					';

		$query .= $this->getSortSQL( $order, FALSE );
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getGeneralLedgerReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$uwf = new UserWageFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							b.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							b.date_stamp as date_stamp,
							a.branch_id,
							a.department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.status_id as status_id,
							a.type_id as type_id,

							a.over_time_policy_id as over_time_policy_id,
							a.absence_policy_id as absence_policy_id,

							sum(total_time) as total_time,
							sum(actual_total_time) as actual_total_time,
							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as uf ON b.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON b.pay_period_id = ppf.id ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '
					LEFT JOIN '. $jf->getTable() .' as jf ON a.job_id = jf.id
					LEFT JOIN '. $jif->getTable() .' as jif ON a.job_item_id = jif.id ';
		}

		$query .= '	LEFT JOIN '. $otpf->getTable() .' as m ON (a.over_time_policy_id = m.id AND m.deleted = 0)
					LEFT JOIN '. $apf->getTable() .' as o ON (a.absence_policy_id = o.id AND o.deleted = 0)
					LEFT JOIN '. $ppf->getTable() .' as q ON (a.premium_policy_id = q.id AND q.deleted = 0)

					where 	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['user_status']) AND trim($filter_data['user_status']) != '' AND !isset($filter_data['user_status_id']) ) {
			$filter_data['user_status_id'] = Option::getByFuzzyValue( $filter_data['user_status'], $uf->getOptions('status') );
		}
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['user_title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['punch_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['punch_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['punch_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'b.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['job_status']) AND trim($filter_data['job_status']) != '' AND !isset($filter_data['job_status_id']) ) {
			$filter_data['job_status_id'] = Option::getByFuzzyValue( $filter_data['job_status'], $jf->getOptions('status') );
		}
		$query .= ( isset($filter_data['job_status_id']) ) ? $this->getWhereClauseSQL( 'jf.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'jf.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['job_item_group_id']) ) ? $this->getWhereClauseSQL( 'jif.group_id', $filter_data['job_item_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10,20,30)
		$query .= '
						AND ( a.deleted = 0 AND b.deleted = 0 )
					group by b.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.branch_id, a.department_id, a.job_id, a.job_item_id, b.date_stamp, a.status_id, a.type_id, a.over_time_policy_id, a.absence_policy_id
					';

		$query .= $this->getSortSQL( $order, FALSE );
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}
        if ( isset($filter_data['user_date_total_type_id']) ) {
            $filter_data['type_id'] = $filter_data['user_date_total_type_id'];
        }
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp','time_stamp','type_id','status_id','branch','department','default_branch','default_department','group','title');
		if ( $order == NULL ) {
			$order = array( 'c.date_stamp' => 'asc','a.status_id' => 'asc', 'a.type_id' => 'asc', 'a.total_time' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = FALSE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['user_id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset($filter_data['include_job_ids']) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset($filter_data['job_group_ids']) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset($filter_data['job_item_ids']) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}


		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$uwf = new UserWageFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		$otpf = new OverTimePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$ppf = new PremiumPolicyFactory();
		$mpf = new MealPolicyFactory();
		$bpf = new BreakPolicyFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select
							a.id as id,
							a.user_date_id as user_date_id,
							a.type_id as type_id,
							a.status_id as status_id,
							a.punch_control_id as punch_control_id,

							a.over_time_policy_id as over_time_policy_id,
							otpf.name as over_time_policy,
							a.absence_policy_id as absence_policy_id,
							apf.name as absence_policy,
							apf.type_id as absence_policy_type_id,
							a.premium_policy_id as premium_policy_id,
							ppf.name as premium_policy,
							a.meal_policy_id as meal_policy_id,
							mpf.name as meal_policy,
							a.break_policy_id as break_policy_id,
							bpf.name as break_policy,

							a.start_time_stamp as start_time_stamp,
							a.end_time_stamp as end_time_stamp,

							a.override as override,

							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.quantity as quantity,
							a.bad_quantity as bad_quantity,
							a.total_time as total_time,
							a.actual_total_time as actual_total_time,

							c.user_id as user_id,
							c.date_stamp as date_stamp,
							c.pay_period_id as pay_period_id,

							d.first_name as first_name,
							d.last_name as last_name,
							d.status_id as user_status_id,
							d.group_id as group_id,
							g.name as "group",
							d.title_id as title_id,
							h.name as title,
							d.default_branch_id as default_branch_id,
							e.name as default_branch,
							d.default_department_id as default_department_id,
							f.name as default_department,
							d.created_by as user_created_by,

							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						x.name as job,
						x.name as job_name,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id,
						y.name as job_item';
		}

		$query .= '
					from 	'. $this->getTable() .' as a
							LEFT JOIN '. $udf->getTable() .' as c ON a.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $otpf->getTable() .' as otpf ON ( a.over_time_policy_id > 0 AND a.over_time_policy_id = otpf.id AND otpf.deleted = 0 )
							LEFT JOIN '. $apf->getTable() .' as apf ON ( a.absence_policy_id > 0 AND a.absence_policy_id = apf.id AND apf.deleted = 0 )
							LEFT JOIN '. $ppf->getTable() .' as ppf ON ( a.premium_policy_id > 0 AND a.premium_policy_id = ppf.id AND ppf.deleted = 0 )
							LEFT JOIN '. $mpf->getTable() .' as mpf ON ( a.meal_policy_id > 0 AND a.meal_policy_id = mpf.id AND mpf.deleted = 0 )
							LEFT JOIN '. $bpf->getTable() .' as bpf ON ( a.break_policy_id > 0 AND a.break_policy_id = bpf.id AND bpf.deleted = 0 )

							LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = c.user_id
																			and z.effective_date <= c.date_stamp
																			and z.wage_group_id = 0
																			and z.deleted = 0
																			order by z.effective_date desc LiMiT 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON a.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON a.job_item_id = y.id';
		}

		$query .= '	WHERE d.company_id = ?';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'c.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_group']) ) ? $this->getWhereClauseSQL( 'g.name', $filter_data['user_group'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'c.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'x.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;

/*
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND d.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND d.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND d.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['branch_id']) AND isset($filter_data['branch_id'][0]) AND !in_array(-1, (array)$filter_data['branch_id']) ) {
			$query  .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND a.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}


		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND a.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND x.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND a.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
		}
*/
		if ( isset($filter_data['date']) AND trim($filter_data['date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['date']);
			$query  .=	' AND c.date_stamp = ?';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND c.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND c.date_stamp <= ?';
		}

		$query .= 	'
						AND (a.deleted = 0 AND c.deleted = 0 AND d.deleted = 0)
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );
        
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		return $this;
	}
}
?>
