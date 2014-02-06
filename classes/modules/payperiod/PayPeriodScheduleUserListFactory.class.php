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
 * $Id: PayPeriodScheduleUserListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\PayPeriod
 */
class PayPeriodScheduleUserListFactory extends PayPeriodScheduleUserFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable();
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
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $ppsf->getTable() .' as b ON a.pay_period_schedule_id = b.id
					WHERE
							b.company_id = ?
							AND ( b.deleted = 0 )
					';
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}


	function getByCompanyIDAndPayPeriodScheduleId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND b.company_id = ?
						AND a.pay_period_schedule_id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndUserID($company_id, $id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND b.company_id = ?
						AND a.pay_period_schedule_id = ?
						AND a.user_id in ( '. $this->getListSQL($user_id, $ph) .' )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodScheduleId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND pay_period_schedule_id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodScheduleIdAndUserID($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'id' => $id,
					'user_id' => $user_id
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	b.id = a.pay_period_schedule_id
						AND pay_period_schedule_id = ?
						AND user_id = ?
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodScheduleIdArray($id) {
		$ppsulf = new PayPeriodScheduleUserListFactory();

		$ppsulf->getByPayPeriodScheduleId($id);

		foreach ($ppsulf as $user) {
			$user_list[$user->getUser()] = NULL;
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return array();
	}
}
?>
