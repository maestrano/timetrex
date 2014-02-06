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
 * $Revision: 9811 $
 * $Id: PayPeriodListFactory.class.php 9811 2013-05-08 20:12:17Z ipso $
 * $Date: 2013-05-08 13:12:17 -0700 (Wed, 08 May 2013) $
 */

/**
 * @package Modules\PayPeriod
 */
class PayPeriodListFactory extends PayPeriodFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
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
							AND deleted=0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByIdList($ids, $where = NULL, $order = NULL) {
		if ( $ids == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where	a.pay_period_schedule_id = b.id
						AND a.id in ( '. $this->getListSQL($ids, $ph) .' )
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdListArray($ids, $where = NULL, $order = NULL, $enable_names = TRUE ) {
		if ( $ids == '' ) {
			return FALSE;
		}

		$result = $this->getByIdList($ids, $where, $order);

		foreach($result as $pay_period) {
			$pay_period_schedule_id[$pay_period->getPayPeriodScheduleObject()->getId()] = $pay_period->getPayPeriodScheduleObject()->getName();
		}

		$use_names = FALSE;
		if ( $enable_names == TRUE AND isset($pay_period_schedule_id) AND count($pay_period_schedule_id) > 1 ) {
			$use_names = TRUE;
		}

		$pay_period_schedule_name = NULL;
		foreach($result as $pay_period) {
			//Debug::Text('Pay Period: '. $pay_period->getId() , __FILE__, __LINE__, __METHOD__,10);
			/*
			if ( $use_names == TRUE ) {
				$pay_period_schedule_name = '('.$pay_period->getPayPeriodScheduleObject()->getName().') ';
			}
			*/
			//$pay_period_list[$pay_period->getId()] = $pay_period_schedule_name . TTDate::getDate('DATE', $pay_period->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period->getEndDate() );
			$pay_period_list[$pay_period->getId()] = $pay_period->getName($use_names);
		}

		if ( isset($pay_period_list) ) {
			return $pay_period_list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $sort_prefix = FALSE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		Debug::Text('Total Rows: '. $lf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		$use_names = FALSE;

		//Get all pay period schedules, if more than one pay period schedule is in use, include PP schedule name.
		$pay_period_schedule_id = array();
		$i=0;
		foreach ($lf as $obj) {
			if ( !isset($pay_period_schedule_id[$obj->getPayPeriodSchedule()]) ) {
				$pay_period_schedule_id[$obj->getPayPeriodSchedule()] = TRUE;
				$i++;
			}

			if ( $i >= 2 ) {
				$use_names = TRUE;
				break;
			}
		}

		$prefix = NULL;
		$i=0;
		foreach ($lf as $obj) {

			if ( $sort_prefix == TRUE ) {
				$prefix = '-'.str_pad( $i, 4, 0, STR_PAD_LEFT).'-';
			}

			$list[$prefix.$obj->getID()] = $obj->getName( $use_names );

			$i++;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getByPayPeriodScheduleId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_date' => 'desc' );
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

					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.deleted=0 AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndStatus($company_id, $status_ids, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b

					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.status_id in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0 AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndStatusAndTransactionDate($company_id, $status_ids, $transaction_date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'transaction_date' => $this->db->BindTimeStamp( $transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b

					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.transaction_date <= ?
						AND a.status_id in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND ( a.deleted=0 AND b.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND id = ?
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndEndDate($company_id, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $end_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND start_date <= ?
						AND end_date > ?
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTransactionDate($company_id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.end_date <= ?
						AND a.transaction_date > ?
						AND a.deleted=0
						AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTransactionStartDateAndTransactionEndDate($company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.deleted=0 AND b.deleted=0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserId($user_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'id' => $user_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = ?
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		//No pay period
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = ?
						AND a.start_date >= ?
						AND a.end_date <= ?
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//Gets all pay periods that start or end between the two dates. Ideal for finding all pay periods that affect a given week.
	function getByUserIdAndOverlapStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindTimeStamp( $start_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					'start_date2' => $this->db->BindTimeStamp( $start_date ),
					'end_date2' => $this->db->BindTimeStamp( $end_date ),
					'start_date3' => $this->db->BindTimeStamp( $start_date ),
					'end_date3' => $this->db->BindTimeStamp( $end_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = ?
						AND
						(
							( a.start_date >= ? AND a.start_date <= ? )
							OR
							( a.end_date >= ? AND a.end_date <= ? )
							OR
							( a.start_date <= ? AND a.end_date >= ? )
						)
						AND ( a.deleted=0 AND b.deleted=0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndEndDate($user_id, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $end_date == '' OR $end_date <= 0 ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindTimeStamp( $end_date ),
					'end_date' => $this->db->BindTimeStamp( $end_date ),
					);

		//No pay period
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = ?
						AND a.start_date <= ?
						AND a.end_date >= ?
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndTransactionDate($user_id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindTimeStamp( $transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b,
							'. $ppsuf->getTable() .' as c

					where	a.pay_period_schedule_id = b.id
						AND a.pay_period_schedule_id = c.pay_period_schedule_id
						AND	c.user_id = ?
						AND a.start_date <= ?
						AND a.transaction_date > ?
						AND a.deleted=0
						AND b.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate($id, $start_transaction_date, $end_transaction_date, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_transaction_date == '' ) {
			return FALSE;
		}

		if ( $end_transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array(
					'id' => $id,
					'start_date' => $this->db->BindTimeStamp( $start_transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $end_transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a

					where 	a.pay_period_schedule_id = ?
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndStartTransactionDateAndEndTransactionDate($company_id, $id, $start_transaction_date, $end_transaction_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_transaction_date == '' ) {
			return FALSE;
		}

		if ( $end_transaction_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $start_transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $end_transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where 	ppsf.company_id = ?
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndStatusAndStartTransactionDateAndEndTransactionDate($company_id, $id, $status_id, $start_transaction_date, $end_transaction_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		if ( $status_id == '' ) {
			return FALSE;
		}

		if ( $start_transaction_date == '' ) {
			return FALSE;
		}

		if ( $end_transaction_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'status_id' => $status_id,
					'start_date' => $this->db->BindTimeStamp( $start_transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $end_transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where 	ppsf.company_id = ?
						AND a.status_id = ?
						AND a.transaction_date >= ?
						AND a.transaction_date <= ?
						AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIDAndPayPeriodScheduleIdAndAnyDate($company_id, $id, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $date ),
					'end_date' => $this->db->BindTimeStamp( $date ),
					'transaction_date' => $this->db->BindTimeStamp( $date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where 	ppsf.company_id = ?
						AND ( a.start_date >= ? OR a.end_date >= ? OR a.transaction_date >= ? )
						AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getThisPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate($company_id, $id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		//ID can be blank/NULL, which means we search all pay_period schedules.
		if ( $date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindTimeStamp( $date ),
					'end_date' => $this->db->BindTimeStamp( $date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( a.pay_period_schedule_id = ppsf.id )
					where ppsf.company_id = ?
						AND a.start_date <= ?
						AND a.end_date >= ? ';

		if ( isset($id[0]) AND !in_array(-1, (array)$id) ) {
			$query .= '
							AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' ) ';
		}

		$query .= '		AND ( a.deleted = 0 AND ppsf.deleted = 0)';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getLastPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate($company_id, $id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		//ID can be blank/NULL, which means we search all pay_period schedules.
		if ( $date == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					'end_date' => $this->db->BindTimeStamp( $date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
					( 	select
							b.pay_period_schedule_id,
							max(b.start_date) as start_date
						FROM '. $this->getTable() .' as b
						LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( b.pay_period_schedule_id = ppsf.id )
						where ppsf.company_id = ?
							AND b.end_date < ?
							AND ( b.deleted = 0 AND ppsf.deleted = 0 )
						GROUP BY b.pay_period_schedule_id
					) as pp2

					where a.pay_period_schedule_id = pp2.pay_period_schedule_id
						AND a.start_date = pp2.start_date ';

		if ( isset($id[0]) AND !in_array(-1, (array)$id) ) {
			$query .= '
							AND a.pay_period_schedule_id in ( '. $this->getListSQL($id, $ph) .' ) ';
		}

		$query .= '		AND ( a.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodScheduleIdAndTransactionDate($id, $transaction_date, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $transaction_date == '' ) {
			return FALSE;
		}

		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array(
					'id' => $id,
					'start_date' => $this->db->BindTimeStamp( $transaction_date ),
					'end_date' => $this->db->BindTimeStamp( $transaction_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a

					where 	a.pay_period_schedule_id = ?
						AND a.start_date <= ?
						AND a.transaction_date > ?
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Text('Query: '. $query , __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getPayPeriodEndDateByUserIdAndTransactionDate($user_id, $transaction_date = NULL ) {
		if ($transaction_date == '' ) {
			$transaction_date = TTDate::getTime();
		}

		$pay_period_obj = $this->getByUserIdAndTransactionDate( $user_id, $transaction_date )->getCurrent();

		if ( $pay_period_obj->getAdvanceTransactionDate() !== FALSE
				AND $pay_period_obj->getAdvanceTransactionDate() > TTDate::getTime() ) {
			$epoch = $pay_period_obj->getAdvanceEndDate();
		} else {
			$epoch = $pay_period_obj->getEndDate();
		}

		return $epoch;
	}

	function getPreviousPayPeriodById($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$pplf = new PayPeriodListFactory();
		$pay_period_obj = $pplf->getById($id)->getCurrent();
		$pay_period_schedule_id = $pay_period_obj->getPayPeriodSchedule();

		if ( $pay_period_schedule_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'pay_period_schedule_id' => $pay_period_schedule_id,
					'start_date' => $this->db->BindTimeStamp( $pay_period_obj->getStartDate() )
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = ?
						AND start_date < ?
						AND deleted=0
					ORDER BY id desc
					LIMIT 1';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByStatus($status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		$ph = array(
					'status_id' => $status,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'

					where	status_id = ?
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdListAndNotStatus($user_ids, $status_ids, $where = NULL, $order = NULL) {
		/*
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}
		*/

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where 	a.pay_period_schedule_id in
						( select distinct(x.pay_period_schedule_id)
							from
									'. $ppsuf->getTable() .' as x,
									'. $ppsf->getTable() .' as z
							where x.user_id in ( '. $this->getListSQL($user_ids, $ph) .' )
								AND z.deleted=0)
						AND a.status_id not in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdListAndNotStatusAndStartDateAndEndDate($user_ids, $status_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		/*
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}
		*/

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $status_ids == '' ) {
			return FALSE;
		}

		if ( (int)$start_date == 0 ) {
			return FALSE;
		}

		if ( (int)$end_date == 0 ) {
			$end_date = TTDate::getTime() + (86400 * 355); //Only check ahead one year of open pay periods.
		}

		$ppsf = new PayPeriodScheduleFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();

		$ph = array();

		$user_ids_sql = $this->getListSQL($user_ids, $ph);

		$ph['start_date'] = $this->db->BindTimeStamp( $start_date );
		$ph['end_date'] = $this->db->BindTimeStamp( $end_date );

		//Start Date arg should be greater then pay period END DATE.
		//So recurring PS amendments start_date can fall anywhere in the pay period and still get applied.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where 	a.pay_period_schedule_id in
						( select distinct(x.pay_period_schedule_id)
							from
									'. $ppsuf->getTable() .' as x,
									'. $ppsf->getTable() .' as z
							where x.user_id in ( '. $user_ids_sql .' )
								AND z.deleted=0)
						AND a.end_date >= ?
						AND a.start_date <= ?
						AND a.status_id not in ( '. $this->getListSQL($status_ids, $ph) .' )
						AND a.deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getFirstStartDateAndLastEndDateByPayPeriodScheduleId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array();
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = 'select 	min(start_date) as first_start_date,
							max(end_date) as last_end_date,
							count(*) as total
					from	'. $this->getTable() .'
					where	pay_period_schedule_id = ?
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$retarr = $this->db->GetRow($query, $ph);

		return $retarr;
	}

	function getYearsArrayByCompanyId($company_id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	distinct(extract(year from a.transaction_date))
					from	'. $this->getTable() .' as a,
							'. $ppsf->getTable() .' as b
					where 	a.pay_period_schedule_id = b.id
						AND a.company_id = ?
						AND a.deleted=0
						AND b.deleted=0
					ORDER by extract(year from a.transaction_date) desc
					';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		//$this->rs = $this->db->Execute($query);
		//return $this;

		$year_arr = $this->db->getCol($query, $ph);
		foreach($year_arr as $year) {
			$retarr[$year] = $year;
		}

		return $retarr;
	}

	function getPayPeriodsWithPayStubsByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.transaction_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$uf = new UserFactory();
		$psf = new PayStubFactory();

		//Make sure just one row per pay period is returned.

/*
		//This is way too slow on older versions of PGSQL.
		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = ?
						AND ( a.deleted = 0 )
						AND EXISTS ( select id from '. $psf->getTable() .' as b WHERE a.id = b.pay_period_id AND b.deleted = 0)';
*/
		$query = '	select 	distinct a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '.  $psf->getTable() .' as b on ( a.id = b.pay_period_id )
					where	a.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	//Get last 6mths worth of pay periods and prepare a JS array so they can be highlighted in the calendar.
	function getJSCalendarPayPeriodArray( $include_all_pay_period_schedules = FALSE ) {
		global $current_company, $current_user;

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_object($current_company) ) {
			return FALSE;
		}

		if ( !is_object($current_user) ) {
			return FALSE;
		}

		if ( $include_all_pay_period_schedules == TRUE ) {
			$cache_id = 'JSCalendarPayPeriodArray_'.$current_company->getId().'_0';
		} else {
			$cache_id = 'JSCalendarPayPeriodArray_'.$current_company->getId().'_'.$current_user->getId();
		}

		$retarr = $this->getCache($cache_id);
		if ( $retarr === FALSE ) {
			$pplf = new PayPeriodListFactory();
			if ( $include_all_pay_period_schedules == TRUE ) {
				$pplf->getByCompanyId( $current_company->getId(), 13);
			} else {
				$pplf->getByUserId( $current_user->getId(), 13);
			}

			$retarr = FALSE;
			if ( $pplf->getRecordCount() > 0 ) {
				foreach( $pplf as $pp_obj) {
					//$retarr['start_date'][] = TTDate::getDate('Ymd', $pp_obj->getStartDate() );
					$retarr['end_date'][] = TTDate::getDate('Ymd', $pp_obj->getEndDate() );
					$retarr['transaction_date'][] = TTDate::getDate('Ymd', $pp_obj->getTransactionDate() );
				}
			}

			$this->saveCache( $retarr, $cache_id);
		}

		return $retarr;
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

		$additional_order_fields = array('status_id','type_id','pay_period_schedule');

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'transaction_date' => 'desc', 'end_date' => 'desc', 'start_date' => 'desc', 'pay_period_schedule_id' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['transaction_date']) ) {
				$order['transaction_date'] = 'desc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$ppsf = new PayPeriodScheduleFactory();
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.name as pay_period_schedule,
							b.type_id as type_id,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $ppsf->getTable() .' as b ON ( a.pay_period_schedule_id = b.id AND b.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['type_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $ppsf->getOptions('type') );
		}
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'b.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_schedule_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_schedule_id', $filter_data['pay_period_schedule_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_schedule']) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['pay_period_schedule'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['name'], 'text', $ph ) : NULL;


/*
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_schedule_id']) AND isset($filter_data['pay_period_schedule_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_schedule_id']) ) {
			$query  .=	' AND a.pay_period_schedule_id in ('. $this->getListSQL($filter_data['pay_period_schedule_id'], $ph) .') ';
		}

		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND b.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(b.name) LIKE ?';
		}
*/

/*
		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp($filter_data['start_date']);
			$query  .=	' AND a.start_date >= ?';
		}

		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp($filter_data['end_date']);
			$query  .=	' AND a.start_date <= ?';
		}
*/
        $query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.start_date', $filter_data['start_date'], 'date_range_timestamp', $ph ) : NULL;
        $query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.end_date', $filter_data['end_date'], 'date_range_timestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['transaction_date']) ) ? $this->getWhereClauseSQL( 'a.transaction_date', $filter_data['transaction_date'], 'date_range_timestamp', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        

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
