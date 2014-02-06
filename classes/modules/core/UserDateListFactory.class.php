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
 * $Id: UserDateListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Core
 */
class UserDateListFactory extends UserDateFactory implements IteratorAggregate {

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

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {

			$query = '
						select 	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByIds($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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
					select 	a.*
					from 	'. $this->getTable() .' as a,
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
					select 	a.*
					from 	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndStartDateAndEndDateAndPayPeriodStatus($company_id, $start_date, $end_date, $status, $where = NULL, $order = NULL) {
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
			$order = array( 'a.user_id' => 'asc', 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$ppf = new PayPeriodFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
						LEFT JOIN '. $ppf->getTable() .' as c ON a.pay_period_id = c.id
					where	b.company_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND c.status_id in ('. $this->getListSQL($status, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPayPeriodId($pay_period_id, $order = NULL) {
		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByDate($date) {
		if ( $date == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where
						a.date_stamp = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDate($user_id, $date) {
		if ( $user_id === '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = 0
					ORDER BY id ASC
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDateAndEmptyPayPeriod($user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL($user_ids, $ph) .')
						AND ( pay_period_id = 0 OR pay_period_id IS NULL )
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndPayPeriodID($user_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						user_id in ('. $this->getListSQL($user_id, $ph) .')
						AND pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .')
						AND deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndPayPeriodID($company_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					//'pay_period_id' => $pay_period_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.pay_period_id in ('. $this->getListSQL($pay_period_id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//Used by calcQuickExceptions maintenance job to speed up finding days that need to have exceptions calculated throughout the day.
	function getMidDayExceptionsByStartDateAndEndDateAndPayPeriodStatus( $start_date, $end_date, $pay_period_status_id ) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $pay_period_status_id == '' ) {
			return FALSE;
		}

		$epf = new ExceptionPolicyFactory();
		$ef = new ExceptionFactory();
		$epcf = new ExceptionPolicyControlFactory();
		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$uf = new UserFactory();
		$cf = new CompanyFactory();
		$udf = new UserDateFactory();
		$sf = new ScheduleFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$ppf = new PayPeriodFactory();

		$current_epoch = time();

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
				$to_timestamp_sql = 'FROM_UNIXTIME';
		} else {
				$to_timestamp_sql = 'to_timestamp';
		}

		$ph = array(
					'current_time1' => $this->db->BindTimeStamp( $current_epoch ),
					'current_time2' => $this->db->BindTimeStamp( $current_epoch ),
					'current_epoch1' => $current_epoch,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Exceptions that need to be calculated in the middle of the day:
		//Definitely: In Late, Out Late, Missed CheckIn
		//Possible: Over Daily Scheduled Time, Over Weekly Scheduled Time, Over Daily Time, Over Weekly Time, Long Lunch (can't run this fast enough), Long Break (can't run this fast enough),
		//Optimize calcQuickExceptions:
		// Loop through exception policies where In Late/Out Late/Missed CheckIn are enabled.
		// Loop through ACTIVE users assigned to these exceptions policies.
		// Only find days that are scheduled AND ( NO punch after schedule start time OR NO punch after schedule end time )
		//      For Missed CheckIn they do not need to be scheduled.
		// Exclude days that already have the exceptions triggered on them (?) (What about split shifts?)
		//	- Just exclude exceptions not assigned to punch/punch_control_id, if there is more than one in the day I don't think it helps much anyways.
		//
		//Currently Over Weekly/Daily time exceptions are only triggered on a Out punch.
		$query = '	select distinct udf.*
					FROM '. $epf->getTable() .' as epf
					LEFT JOIN '. $epcf->getTable() .' as epcf ON ( epf.exception_policy_control_id = epcf.id )
					LEFT JOIN '. $pgf->getTable() .' as pgf ON ( epcf.id = pgf.exception_policy_control_id )
					LEFT JOIN '. $pguf->getTable() .' as pguf ON ( pgf.id = pguf.policy_group_id )
					LEFT JOIN '. $uf->getTable() .' as uf ON ( pguf.user_id = uf.id )
					LEFT JOIN '. $cf->getTable() .' as cf ON ( uf.company_id = cf.id )
					LEFT JOIN '. $udf->getTable() .' as udf ON ( uf.id = udf.user_id )
					LEFT JOIN '. $ppf->getTable() .' as ppf ON ( ppf.id = udf.pay_period_id )
					LEFT JOIN '. $ef->getTable() .' as ef ON ( udf.id = ef.user_date_id AND ef.exception_policy_id = epf.id AND ef.type_id != 5 )
					LEFT JOIN '. $sf->getTable() .' as sf ON ( udf.id = sf.user_date_id AND ( sf.start_time <= ? OR sf.end_time <= ? ) )
					LEFT JOIN '. $pcf->getTable() .' as pcf ON ( udf.id = pcf.user_date_id AND pcf.deleted = 0 )
					LEFT JOIN '. $pf->getTable() .' as pf ON 	(
																pcf.id = pf.punch_control_id AND pf.deleted = 0
																AND (
																		( epf.type_id = \'S4\' AND ( pf.time_stamp >= sf.start_time OR pf.time_stamp <= sf.end_time ) )
																		OR
																		( epf.type_id = \'S6\' AND ( pf.time_stamp >= sf.end_time ) )
																		OR
																		( epf.type_id = \'C1\' AND ( pf.status_id = 10 AND pf.time_stamp <= '. $to_timestamp_sql .'(?-epf.grace) ) )
																	)
																)
					WHERE ( epf.type_id in (\'S4\',\'S6\', \'C1\') AND epf.active = 1 )
						AND ( uf.status_id = 10 AND cf.status_id != 30 )
						AND ( udf.date_stamp >= ? AND udf.date_stamp <= ? )
						AND ppf.status_id in ('. $this->getListSQL($pay_period_status_id, $ph) .')
						AND ( ( ( epf.type_id in (\'S4\',\'S6\') AND ( sf.id IS NOT NULL AND sf.deleted = 0 ) AND pf.id IS NULL ) OR epf.type_id = \'C1\' ) AND ef.id IS NULL  )
						AND ( epf.deleted = 0 AND epcf.deleted = 0 AND pgf.deleted = 0 AND uf.deleted = 0 AND cf.deleted = 0 AND udf.deleted = 0 )
				';
				//Don't check deleted = 0 on PCF/PF tables, as we need to check IS NULL on them instead.

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/*

		Report functions

	*/

	function getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate($time_period, $user_ids, $company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
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

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select 	a.user_id,
								(EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) ) as date,
								count(*) as total
						from	'. $this->getTable() .' as a,
								'. $uf->getTable() .' as b
						where 	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND exists(
										select id
										from '. $pcf->getTable() .' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id,(EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) )
						) tmp
					GROUP BY user_id
					';

/*
		$query = '
					select 	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select 	a.user_id,
								(date_part(\''.$time_period.'\', a.date_stamp) || \'-\' || date_part(\'year\', a.date_stamp) ) as date,
								count(*) as total
						from	'. $this->getTable() .' as a,
								'. $uf->getTable() .' as b
						where 	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND exists(
										select id
										from '. $pcf->getTable() .' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id,(date_part(\''. $time_period.'\', a.date_stamp) || \'-\' ||  date_part(\'year\', a.date_stamp) )
						) tmp
					GROUP BY user_id
					';
*/
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function deleteByUserIdAndDateAndDeleted( $user_id, $date, $deleted ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}

		if ( $deleted == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_id' => $user_id,
					'date' => $this->db->BindDate( $date ),
					'deleted' => (int)$deleted
					);

		$query = '
					delete
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = ?
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getArrayByListFactory($lf) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		foreach ($lf as $obj) {
			$list[] = $obj->getID();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

}
?>
