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
 * @package Core
 */
class UserDateTotalListFactory extends UserDateTotalFactory implements IteratorAggregate {

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

		$uf = new UserFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as c
					where	a.user_id = c.id
						AND a.id = ?
						AND c.company_id = ?
						AND ( a.deleted = 0 AND c.deleted = 0 )
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

		$uf = new UserFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as c
					where	a.user_id = c.id
						AND c.company_id = ?
						AND ( a.deleted = 0 AND c.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
	
	function getByUserIdAndDateStampAndObjectTypeAndOverrideAndMisMatchPunchControlDateStamp($user_id, $date_stamp, $object_type_id, $override = FALSE) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$pcf = new PunchControlFactory();

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					'override' => $this->toBool( $override ),
					);

		//Don't check for JUST b.deleted = 0 because of the LEFT JOIN, it might be NULL too.
		//There is a bug where sometimes a user_date_total row is orphaned with no punch_control rows that aren't deleted
		//So make sure this query includes those orphaned rows so they can be deleted.
		//( a.user_date_id != b.user_date_id OR b.deleted = 1 )
		//Ensures that all worked time entries that are map to a punch_control row that is marked as deleted
		//will also be returned so they can be deleted.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					where	a.user_id = ?
						AND a.date_stamp = ?
						AND
							(
								( a.override = ? AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .') )
								OR
								( b.id IS NOT NULL AND ( a.user_id = b.user_id AND ( a.date_stamp != b.date_stamp OR b.deleted = 1 ) ) )
							)
						AND ( a.deleted = 0 )
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDateStampAndObjectType($user_id, $date_stamp, $object_type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.object_type_id' => 'asc', 'c.time_stamp' => 'asc', 'a.start_time_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		//Want to be able to see overridden or, just time added on its own?
		//LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND c.status_id = 10
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					LEFT JOIN '. $pf->getTable() .' as c ON a.punch_control_id = c.punch_control_id AND ( c.status_id = 10 OR c.status_id IS NULL )
					where a.user_id = ?
						AND a.date_stamp = ?
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND ( a.deleted = 0
								AND ( b.deleted=0 OR b.deleted IS NULL )
								AND ( c.deleted=0 OR c.deleted IS NULL ) )
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndDateStampAndObjectTypeAndPunchControlIdAndOverride($user_id, $date_stamp, $object_type, $punch_control_id, $override = FALSE, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		if ( $object_type == '' ) {
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
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					'object_type' => $object_type,
					'punch_control_id' => (int)$punch_control_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp = ?
						AND a.object_type_id = ?
						AND a.punch_control_id = ?
						AND a.override = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDateStampAndObjectTypeAndPayCodeIdAndOverride($user_id, $date_stamp, $object_type, $pay_code_id, $override = FALSE, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		if ( $object_type == '' ) {
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
					'user_id' => (int)$user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					'object_type' => (int)$object_type,
					'pay_code_id' => (int)$pay_code_id,
					'override' => $this->toBool( $override ),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp = ?
						AND a.object_type_id = ?
						AND a.pay_code_id = ?
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
					select	*
					from	'. $this->getTable() .'
					where punch_control_id = ?
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDateStampAndOldDateStampAndPunchControlId($user_id, $date_stamp, $old_date_stamp, $punch_control_id) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		if ( $punch_control_id == '' ) {
			return FALSE;
		}

		if ( empty( $old_date_stamp ) ) {
			$old_date_stamp = $date_stamp;
		}

		$ph = array(
					'user_id' => (int)$user_id,
					'date_stamp' => $this->db->BindDate( (int)$date_stamp ),
					'old_date_stamp' => $this->db->BindDate( (int)$old_date_stamp ),
					'punch_control_id' => (int)$punch_control_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where user_id = ?
						AND ( date_stamp = ? OR date_stamp = ? )
						AND punch_control_id = ?
						AND deleted = 0
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//function getTotalSumByUserDateID( $user_date_id ) {
	function getTotalSumByUserIdAndDateStamp( $user_id, $date_stamp ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		$apf = new AbsencePolicyFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		//Don't include total time row
		//Include paid absences
		//AND ( a.status_id in (20, 30) OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
		$query = '
					select	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as b ON a.punch_control_id = b.id
					LEFT JOIN '. $apf->getTable() .' as c ON ( a.object_type_id = 50 AND a.pay_code_id = c.id )
					where	a.user_id = ?
						AND a.date_stamp = ?
						AND a.object_type_id in (10, 50, 100, 110)
						AND ( c.type_id IS NULL OR c.type_id in ( 10, 12 ) )
						AND ( a.deleted = 0 AND (b.deleted=0 OR b.deleted is NULL) )
				';
		$total = $this->db->GetOne($query, $ph);
		//Debug::Arr( $ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		if ($total == '' ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getTotalSumByUserIdAndDateStampAndObjectType( $user_id, $date_stamp, $object_type_id ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamp == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		//Don't include total time row, OR paid absences
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .'
					where	user_id = ?
						AND date_stamp = ?
						AND object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND deleted = 0
				';

		$total = $this->db->GetOne($query, $ph);

		if ( $total === FALSE ) {
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

		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'week_start_epoch' => $this->db->BindDate( $week_start_epoch ),
					'epoch' => $this->db->BindDate( $epoch ),
					);

		//DO NOT Include paid absences. Only count regular time towards weekly overtime.
		//And other weekly/bi-weekly overtime polices!
		//AND a.status_id = 10
		//AND (
		//	a.type_id = 20
		//	OR ( a.type_id = 30 AND c.type_id in ( 20, 30, 210 ) )
		//	)
		//AND a.absence_policy_id = 0
		$query = '
					select	sum(a.total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $otpf->getTable() .' as c ON ( a.object_type_id = 30 AND a.pay_code_id = c.id )
					where
						a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp < ?
						AND (
							a.object_type_id = 20
							OR ( a.object_type_id = 30 AND c.type_id in ( 20, 30, 210 ) )
							)
						AND a.deleted = 0
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total .' Week Start: '. TTDate::getDate('DATE+TIME', $week_start_epoch ) .' End: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

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

		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'user_id' => $user_id,
					'week_start_epoch' => $this->db->BindDate( $week_start_epoch ),
					'epoch' => $this->db->BindDate( $epoch ),
					);

		//a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) )
		//AND a.absence_policy_id = 0
		$query = '
					select	sum(a.total_time)
					from	'. $this->getTable() .' as a
					where
						a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp < ?
						AND a.object_type_id in (10,100,110)
						AND a.deleted = 0
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		//Debug::text('Total: '. $total .' Week Start: '. TTDate::getDate('DATE+TIME', $week_start_epoch ) .' End: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate($company_id, $user_id, $object_type_id, $start_date, $end_date) {
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

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$otpf = new OverTimePolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Order by a.over_time_policy last so we never leave the ordering up to the database. This can cause
		//the unit tests to fail between databases.
		//AND a.type_id != 40
		$query = '	select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as c ON a.user_id = c.id
					LEFT JOIN '. $otpf->getTable() .' as d ON ( a.object_type_id = 30 AND a.src_object_id = d.id )
					where
						c.company_id = ?
						AND	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
						AND ( a.deleted = 0 )
					ORDER BY a.date_stamp asc, a.object_type_id asc, d.type_id desc, a.src_object_id desc, a.total_time, a.id
					';

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
		$udtf = new UserDateTotalFactory();
		$sf = new ScheduleFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$ppf = new PayPeriodFactory();

		$current_epoch = time();

		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
				$to_timestamp_sql = 'FROM_UNIXTIME';
		} else {
				$to_timestamp_sql = 'to_timestamp';
		}

		$ph = array(
					'start_date1' => $this->db->BindDate( $start_date ),
					'end_date1' => $this->db->BindDate( $end_date ),
					'start_date2' => $this->db->BindDate( $start_date ),
					'end_date2' => $this->db->BindDate( $end_date ),
					'current_time1' => $this->db->BindTimeStamp( $current_epoch ),
					'current_time2' => $this->db->BindTimeStamp( $current_epoch ),
					'start_date3' => $this->db->BindDate( $start_date ),
					'end_date3' => $this->db->BindDate( $end_date ),
					'current_epoch1' => $current_epoch,
					);

		//Exceptions that need to be calculated in the middle of the day:
		//Definitely: In Late, Out Late, Missed CheckIn
		//Possible: Over Daily Scheduled Time, Over Weekly Scheduled Time, Over Daily Time, Over Weekly Time, Long Lunch (can't run this fast enough), Long Break (can't run this fast enough),
		//Optimize calcQuickExceptions:
		// Loop through exception policies where In Late/Out Late/Missed CheckIn are enabled.
		// Loop through ACTIVE users assigned to these exceptions policies.
		// Only find days that are scheduled AND ( NO punch after schedule start time OR NO punch after schedule end time )
		//		For Missed CheckIn they do not need to be scheduled.
		// Exclude days that already have the exceptions triggered on them (?) (What about split shifts?)
		//	- Just exclude exceptions not assigned to punch/punch_control_id, if there is more than one in the day I don't think it helps much anyways.
		//
		//Currently Over Weekly/Daily time exceptions are only triggered on a Out punch.
		//Check for udtf.object_type_id=10 (system time) as we used to use 5 (Worked Time) but if an employee punched In for their shift and not Out, it wouldn't ever trigger a Out Late exception.
		//
		//NOTE: Make sure we take into account that UDT rows may not exist for a day at all if the employee hasn't punched in/out, so we can't join other tables on udtf.date_stamp
		$query = '
					SELECT
						tmp.user_id as user_id,
						min(tmp.date_stamp) as start_date,
						max(tmp.date_stamp) as end_date
					FROM (
						SELECT
							CASE WHEN sf.user_id IS NOT NULL THEN sf.user_id ELSE udtf.user_id END as user_id,
							CASE WHEN sf.date_stamp IS NOT NULL THEN sf.date_stamp ELSE udtf.date_stamp END as date_stamp
						FROM '. $epf->getTable() .' as epf
						LEFT JOIN '. $epcf->getTable() .' as epcf ON ( epf.exception_policy_control_id = epcf.id )
						LEFT JOIN '. $pgf->getTable() .' as pgf ON ( epcf.id = pgf.exception_policy_control_id )
						LEFT JOIN '. $pguf->getTable() .' as pguf ON ( pgf.id = pguf.policy_group_id )
						LEFT JOIN '. $uf->getTable() .' as uf ON ( pguf.user_id = uf.id )
						LEFT JOIN '. $cf->getTable() .' as cf ON ( uf.company_id = cf.id )
						LEFT JOIN '. $ef->getTable() .' as ef ON ( uf.id = ef.user_id AND ef.date_stamp >= ? AND ef.date_stamp <= ? AND ef.exception_policy_id = epf.id AND ef.type_id != 5 AND ef.deleted = 0 )
						LEFT JOIN '. $sf->getTable() .' as sf ON ( uf.id = sf.user_id AND sf.date_stamp >= ? AND sf.date_stamp <= ? AND ( sf.start_time <= ? OR sf.end_time <= ? ) AND sf.deleted = 0 )
						LEFT JOIN '. $udtf->getTable() .' as udtf ON ( uf.id = udtf.user_id AND udtf.object_type_id = 10 AND udtf.date_stamp >= ? AND udtf.date_stamp <= ? AND udtf.deleted = 0 )
						LEFT JOIN '. $ppf->getTable() .' as ppf ON ( ppf.id = ef.pay_period_id OR ppf.id = sf.pay_period_id OR ppf.id = udtf.pay_period_id )
						LEFT JOIN '. $pcf->getTable() .' as pcf ON ( uf.id = pcf.user_id AND udtf.date_stamp = pcf.date_stamp AND pcf.deleted = 0 )
						LEFT JOIN '. $pf->getTable() .' as pf ON	(
																	pcf.id = pf.punch_control_id AND pf.deleted = 0
																	AND (
																			( epf.type_id = \'S4\' AND ( pf.time_stamp >= sf.start_time OR pf.time_stamp <= sf.end_time ) )
																			OR
																			( epf.type_id = \'S6\' AND ( pf.time_stamp >= sf.end_time ) )
																			OR
																			( epf.type_id = \'C1\' AND ( pf.status_id = 10 AND pf.time_stamp <= '. $to_timestamp_sql .'(?-epf.grace) ) )
																		)
																	)
						WHERE ( epf.type_id in (\'S4\', \'S6\', \'C1\') AND epf.active = 1 )
							AND ( uf.status_id = 10 AND cf.status_id != 30 )
							AND ppf.status_id in ('. $this->getListSQL($pay_period_status_id, $ph) .')
							AND ( ( ( epf.type_id in (\'S4\', \'S6\') AND ( sf.id IS NOT NULL AND sf.deleted = 0 ) AND pf.id IS NULL ) OR epf.type_id = \'C1\' ) AND ef.id IS NULL	)
							AND ( epf.deleted = 0 AND epcf.deleted = 0 AND pgf.deleted = 0 AND uf.deleted = 0 AND cf.deleted = 0 )
					) as tmp
					GROUP BY tmp.user_id
				';
				//Don't check deleted = 0 on PCF/PF tables, as we need to check IS NULL on them instead.

/*
 		//This query doesn't take into the fact that UDT rows may not exist for a day if the user hasn't punched in/out.
		$query = '	select udtf.user_id, min(udtf.date_stamp) as start_date, max(udtf.date_stamp) as end_date
					FROM '. $epf->getTable() .' as epf
					LEFT JOIN '. $epcf->getTable() .' as epcf ON ( epf.exception_policy_control_id = epcf.id )
					LEFT JOIN '. $pgf->getTable() .' as pgf ON ( epcf.id = pgf.exception_policy_control_id )
					LEFT JOIN '. $pguf->getTable() .' as pguf ON ( pgf.id = pguf.policy_group_id )
					LEFT JOIN '. $uf->getTable() .' as uf ON ( pguf.user_id = uf.id )
					LEFT JOIN '. $cf->getTable() .' as cf ON ( uf.company_id = cf.id )
					LEFT JOIN '. $udtf->getTable() .' as udtf ON ( uf.id = udtf.user_id )
					LEFT JOIN '. $ppf->getTable() .' as ppf ON ( ppf.id = udtf.pay_period_id )
					LEFT JOIN '. $ef->getTable() .' as ef ON ( uf.id = ef.user_id AND udtf.date_stamp = ef.date_stamp AND ef.exception_policy_id = epf.id AND ef.type_id != 5 )
					LEFT JOIN '. $sf->getTable() .' as sf ON ( uf.id = sf.user_id AND udtf.date_stamp = sf.date_stamp AND ( sf.start_time <= ? OR sf.end_time <= ? ) )
					LEFT JOIN '. $pcf->getTable() .' as pcf ON ( uf.id = pcf.user_id AND udtf.date_stamp = pcf.date_stamp AND pcf.deleted = 0 )
					LEFT JOIN '. $pf->getTable() .' as pf ON	(
																pcf.id = pf.punch_control_id AND pf.deleted = 0
																AND (
																		( epf.type_id = \'S4\' AND ( pf.time_stamp >= sf.start_time OR pf.time_stamp <= sf.end_time ) )
																		OR
																		( epf.type_id = \'S6\' AND ( pf.time_stamp >= sf.end_time ) )
																		OR
																		( epf.type_id = \'C1\' AND ( pf.status_id = 10 AND pf.time_stamp <= '. $to_timestamp_sql .'(?-epf.grace) ) )
																	)
																)
					WHERE ( epf.type_id in (\'S4\', \'S6\', \'C1\') AND epf.active = 1 )
						AND ( uf.status_id = 10 AND cf.status_id != 30 )
						AND udtf.object_type_id = 10
						AND ( udtf.date_stamp >= ? AND udtf.date_stamp <= ? )
						AND ppf.status_id in ('. $this->getListSQL($pay_period_status_id, $ph) .')
						AND ( ( ( epf.type_id in (\'S4\', \'S6\') AND ( sf.id IS NOT NULL AND sf.deleted = 0 ) AND pf.id IS NULL ) OR epf.type_id = \'C1\' ) AND ef.id IS NULL	)
						AND ( epf.deleted = 0 AND epcf.deleted = 0 AND pgf.deleted = 0 AND uf.deleted = 0 AND cf.deleted = 0 AND udtf.deleted = 0 )
					GROUP BY udtf.user_id
				';
				//Don't check deleted = 0 on PCF/PF tables, as we need to check IS NULL on them instead.
*/
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

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

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//AND ( a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND ( a.object_type_id in ( 10, 100, 110 ) )
						AND ( a.deleted = 0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getTotalTimeSumByUserIDAndPayCodeIDAndStartDateAndEndDate( $user_id, $pay_code_id, $start_date, $end_date ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.pay_code_id in ('. $this->getListSQL($pay_code_id, $ph) .')
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 20
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 25
						AND a.src_object_id in ('. $this->getListSQL($absence_policy_id, $ph) .')
						AND ( a.deleted = 0 )
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

		$pcf = new PayCodeFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					where pcf.type_id in (10,12)
						AND a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 25
						AND a.total_time > 0
						AND a.total_time_amount > 0
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//This includes days where they only got overtime.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select	distinct(a.date_stamp)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 10
						AND a.total_time > 0
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//This includes days where they only got overtime.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select	distinct(a.date_stamp)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 20
						AND a.total_time > 0
						AND ( a.deleted = 0 )
				';
				
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

		$pcf = new PayCodeFactory();

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Include only paid absences.
		//Return a list of dates, so they can be compared/added/subtracted with other lists
		$query = '
					select	distinct(a.date_stamp)
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id

					where	pcf.type_id in (10,12)
						AND a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.object_type_id = 50
						AND a.total_time > 0
						AND a.total_time_amount > 0
						AND ( a.deleted = 0 )
				';
		
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

		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
			$day_of_week_clause = ' (dayofweek(a.date_stamp)-1) '; //Sunday=1 with MySQL, so we need to minus one so it matches PHP Sunday=0
		} else {
			$day_of_week_clause = ' extract(dow from a.date_stamp) ';
		}

		Debug::text('Day Of Week: '. $day_of_week, __FILE__, __LINE__, __METHOD__, 10);
		$ph = array(
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					'day_of_week' => $day_of_week,
					);

		//Include only paid absences.
		$query = '
					select	count(distinct(a.date_stamp))
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND '. $day_of_week_clause .' = ?
						AND a.object_type_id = 10
						AND a.total_time > 0
						AND ( a.deleted = 0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	//function getDaysWorkedByUserIDAndUserDateIDs( $user_id, $user_date_ids ) {
	function getDaysWorkedByUserIDAndDateStamps( $user_id, $date_stamps ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date_stamps == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		//Include only paid absences.
		//AND a.status_id = 20
		$query = '
					select	count(distinct(a.user_date_id))
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.date_stamp in ('. $this->getListSQL($date_stamps, $ph) .')
						AND a.object_type_id = 10
						AND a.total_time > 0
						AND ( a.deleted = 0 )
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

		$ph = array(
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		//AND a.status_id = 20
		$query = '
					select	count(distinct(a.user_id))
					from	'. $this->getTable() .' as a
					where	a.pay_period_id = ?
						AND a.object_type_id = 10
						AND a.total_time > 0
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		//AND ( a.status_id in (20, 30) OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.pay_period_id = ?
						AND a.object_type_id in ( 10, 50, 100, 110 )
						AND ( a.deleted = 0 )
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

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					);

		//Include only paid absences.
		//AND ( a.status_id = 20 OR ( a.status_id = 10 AND a.type_id in ( 100, 110 ) ) )
		$query = '
					select	sum(total_time)
					from	'. $this->getTable() .' as a
					where	a.user_id = ?
						AND a.pay_period_id = ?
						AND a.object_type_id in ( 10, 50, 100, 110 )
						AND ( a.deleted = 0 )
				';

		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total .' Pay Period: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByPayCodeId( $pay_code_id, $limit = NULL, $where = NULL, $order = NULL) {
		if ( $pay_code_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'pay_code_id' => $pay_code_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where  a.pay_code_id = ?
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit );

		return $this;
	}

	function getByJobId($job_id, $where = NULL, $order = NULL) {
		if ( $job_id == '') {
			return FALSE;
		}

		$uwf = new UserWageFactory();

		$ph = array(
					'job_id' => $job_id,
					);

		//AND a.status_id = 10
		$query = '
					select	a.*,
							z.id as user_wage_id,
							z.effective_date as user_wage_effective_date
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = a.user_id
																			and z.effective_date <= a.date_stamp
																			and z.wage_group_id = 0
																			and z.deleted = 0
																			order by z.effective_date desc LIMIT 1)

					where	a.job_id = ?
						AND a.object_type_id in ( 5, 20, 30, 40, 100, 110 )
						AND ( a.deleted = 0 )
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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

		$pcf = new PayCodeFactory();
		$uwf = new UserWageFactory();

		$ph = array(
					'user_id' => $user_id,
					'pay_period_id' => $pay_period_id,
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Order dock hours first, so it can be deducted from regular time.
		//Order newest wage changes first too. This is VERY important for calculating pro-rate amounts.

		//We only need to map to the default wage group so we can handle salaried employees and pro-rating. All dollars amounts are obtained from total_time_amount.
		$query = '
					select
							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							uwf.id as user_wage_id,
							uwf.type_id as user_wage_type_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,

							sum(a.total_time) as total_time,
							sum(a.total_time_amount) as total_time_amount
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					LEFT JOIN '. $uwf->getTable() .' as uwf ON uwf.id = (select uwf.id
													from '. $uwf->getTable() .' as uwf
													where uwf.user_id = a.user_id
														and uwf.wage_group_id = 0
														and uwf.effective_date <= a.date_stamp
														and uwf.deleted = 0
														order by uwf.effective_date desc limit 1)

					where
						a.user_id = ?
						AND a.pay_period_id = ?
						AND a.date_stamp <= ?
						AND a.object_type_id in (20, 25, 30, 40, 100, 110 )
						AND ( a.deleted = 0 )
					group by a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, uwf.id, uwf.type_id, a.currency_id, a.currency_rate, a.hourly_rate
					order by a.object_type_id = 25, a.object_type_id desc, a.pay_code_id asc
				';

		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	function getAccrualOrphansByUserIdAndPayCodeIdAndAccrualPolicyAccountIdAndStartDateAndEndDate( $user_id, $pay_code_id, $accrual_policy_account_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $pay_code_id == '') {
			return FALSE;
		}

		if ( $accrual_policy_account_id == '') {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$af = new AccrualFactory();
		$udtf = new UserDateTotalFactory();

		//
		// getOrphansByUserIdAndDate() AND getOrphansByUserId() are similar, may need to modify both!
		// Also check UserDateTotalListFactory->getAccrualOrphansByPayCodeIdAndStartDateAndEndDate()
		//

		$ph = array(
					'accrual_policy_account_id' => $accrual_policy_account_id,
					'user_id' => $user_id,
					'pay_code_id' => $pay_code_id,
					'start_date' => $this->db->BindDate( TTDate::getBeginDayEpoch( (TTDate::getMiddleDayEpoch( $start_date ) ) ) ),
					'end_date' => $this->db->BindDate( TTDate::getBeginDayEpoch( (TTDate::getMiddleDayEpoch( $end_date ) ) ) ),
					);

		//If we include object_type_id=25 here, it will include cases where Hour Based accrual policies accrue time every day.
		//I think we just want to focus on absence (taken) records instead, object_type_id=50
		//
		//The 2nd UNION query is for cases where the absence (taken) record exists, but no object_type_id=25 record corresponds with it.
		$query = '
					SELECT * FROM (
						SELECT	a.*, b.id as joined_id
						FROM	'. $this->getTable() .' as a
						LEFT JOIN '. $af->getTable() .' as b ON (
																	a.user_id = b.user_id
																	AND a.date_stamp = b.time_stamp::date
																	AND b.accrual_policy_account_id = ?
																	AND b.type_id = 20
																	AND abs(a.total_time) = abs(b.amount)
																	AND b.deleted = 0
																)
						UNION ALL

						SELECT	a.*, c.id as joined_id
						FROM	'. $this->getTable() .' as a
						LEFT JOIN '. $this->getTable() .' as c ON (
																	a.user_id = c.user_id
																	AND a.date_stamp = c.date_stamp
																	AND a.src_object_id = c.src_object_id
																	AND a.pay_code_id = c.pay_code_id
																	AND a.object_type_id = 50
																	AND c.object_type_id = 25
																	AND abs(a.total_time) = abs(c.total_time)
																	AND c.deleted = 0
																	)
						WHERE a.object_type_id = 50 AND a.total_time != 0
					) as a					
					WHERE
						a.user_id = ?
						AND a.pay_code_id = ?
						AND a.object_type_id IN ( 25, 50 )
						AND ( a.date_stamp >= ? AND a.date_stamp <= ? )
						AND ( a.joined_id is NULL )
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByStartDateAndEndDateAndJobList($start_date, $end_date, $job_ids, $order = NULL) {
		if ( $job_ids == '') {
			Debug::Text('No Job Ids: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( $start_date == '') {
			//Debug::Text('No Start Date: ', __FILE__, __LINE__, __METHOD__, 10);
			$start_date = 0;
		}

		if ( $end_date == '') {
			//Debug::Text('No End Date: ', __FILE__, __LINE__, __METHOD__, 10);
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
		$pcf = new PayCodeFactory();

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = 'select	a.user_id as user_id,
							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							a.branch_id as branch_id,
							a.department_id as department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,
							a.hourly_rate_with_burden as hourly_rate_with_burden,

							sum(quantity) as quantity,
							sum(bad_quantity) as bad_quantity,
							min(a.start_time_stamp) as start_time_stamp,
							max(a.end_time_stamp) as end_time_stamp,
							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time,
							sum(a.total_time_amount) as total_time_amount,
							sum(a.total_time_amount_with_burden) as total_time_amount_with_burden
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id

					where	a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.job_id in ('. $this->getListSQL($job_ids, $ph) .')
						AND ( a.deleted = 0 )
					group by a.user_id, a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.currency_id, a.currency_rate, a.hourly_rate, a.hourly_rate_with_burden
					order by a.job_id asc
				';
		//This isn't needed as it lists every status:	AND a.status_id in (10, 20, 30)

		$query .= $this->getSortSQL( $order, FALSE );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAffordableCareReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc', 'uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
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
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$pcf = new PayCodeFactory();

		$ph = array( 'company_id' => $company_id );

		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
			$date_sql = 'month(	a.date_stamp )';
		} else {
			$date_sql = 'date_trunc(\'month\',	a.date_stamp )';
		}

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							a.user_id as user_id,
							'. $date_sql .' as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.object_type_id as object_type_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id

					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id

					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON a.pay_period_id = ppf.id

					where	uf.company_id = ? ';

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

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_time_sheet_verify_status_id']) ) ? $this->getWhereClauseSQL( 'pptsvlf.status_id', $filter_data['pay_period_time_sheet_verify_status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10, 20, 30)
		$query .= '
						AND ( a.deleted = 0 )
					group by a.user_id, a.branch_id, a.department_id, a.date_stamp, a.object_type_id, a.pay_code_id, pcf.type_id
					';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	function getTimesheetSummaryReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc', 'uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
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
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$pcf = new PayCodeFactory();
		$ppf_b = new PayPeriodFactory();
		$pptsvlf = new PayPeriodTimeSheetVerifyListFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							a.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							a.date_stamp as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,

							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,
							a.hourly_rate_with_burden as hourly_rate_with_burden,

							min(a.start_time_stamp) as start_time_stamp,
							max(a.end_time_stamp) as end_time_stamp,
							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time,
							sum(a.total_time_amount) as total_time_amount,
							sum(a.total_time_amount_with_burden) as total_time_amount_with_burden
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id
					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON a.pay_period_id = ppf.id
					LEFT JOIN '. $pptsvlf->getTable() .' as pptsvlf ON ( ppf.id = pptsvlf.pay_period_id AND a.user_id = pptsvlf.user_id AND pptsvlf.deleted = 0 )
					where	uf.company_id = ? ';

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

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_time_sheet_verify_status_id']) ) ? $this->getWhereClauseSQL( 'pptsvlf.status_id', $filter_data['pay_period_time_sheet_verify_status_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		//This isn't needed as it lists every status:	AND a.status_id in (10, 20, 30)
		$query .= '
						AND ( a.deleted = 0 AND uf.deleted = 0 )
						GROUP BY a.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.date_stamp, a.branch_id, a.department_id, a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, a.currency_id, a.currency_rate, a.hourly_rate, a.hourly_rate_with_burden
					';

		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	function getTimesheetDetailReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc', 'uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
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
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$pcf = new PayCodeFactory();

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		//Show Min/Max punches based on day/branch/department, so we can split reports out day/branch/department and still show
		//	when the employee punched in/out for each.
		$query = '
					select
							a.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							a.date_stamp as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,

							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,
							a.hourly_rate_with_burden as hourly_rate_with_burden,

							min(a.start_time_stamp) as start_time_stamp,
							max(a.end_time_stamp) as end_time_stamp,
							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time,
							sum(a.total_time_amount) as total_time_amount,
							sum(a.total_time_amount_with_burden) as total_time_amount_with_burden
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id
					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON a.pay_period_id = ppf.id
					where	uf.company_id = ? ';

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

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'uf.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10, 20, 30)
		$query .= '
						AND ( a.deleted = 0 )
					GROUP BY a.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.date_stamp, a.branch_id, a.department_id, a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, a.currency_id, a.currency_rate, a.hourly_rate, a.hourly_rate_with_burden
					';

		$query .= $this->getSortSQL( $order, FALSE );
		$this->ExecuteSQL( $query, $ph );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}


	function getJobDetailReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		//$order = array( 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc' );
		//$order = array( 'b.pay_period_id' => 'asc', 'uf.last_name' => 'asc', 'b.date_stamp' => 'asc' );
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
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$pcf = new PayCodeFactory();
		
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							a.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							a.date_stamp as date_stamp,
							a.branch_id as branch_id,
							a.department_id as department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,

							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,
							a.hourly_rate_with_burden as hourly_rate_with_burden,

							min(a.start_time_stamp) as start_time_stamp,
							max(a.end_time_stamp) as end_time_stamp,
							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time,
							sum(a.total_time_amount) as total_time_amount,
							sum(a.total_time_amount_with_burden) as total_time_amount_with_burden
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id
					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON a.pay_period_id = ppf.id ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '
					LEFT JOIN '. $jf->getTable() .' as jf ON a.job_id = jf.id
					LEFT JOIN '. $jif->getTable() .' as jif ON a.job_item_id = jif.id ';
		}

		$query .= ' where	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['user_status']) AND !is_array($filter_data['user_status']) AND trim($filter_data['user_status']) != '' AND !isset($filter_data['user_status_id']) ) {
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
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['job_status']) AND !is_array($filter_data['job_status']) AND trim($filter_data['job_status']) != '' AND !isset($filter_data['job_status_id']) ) {
			$filter_data['job_status_id'] = Option::getByFuzzyValue( $filter_data['job_status'], $jf->getOptions('status') );
		}
		$query .= ( isset($filter_data['job_status_id']) ) ? $this->getWhereClauseSQL( 'jf.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'jf.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_tag']) ) ? $this->getWhereClauseSQL( 'jf.id', array( 'company_id' => $company_id, 'object_type_id' => 600, 'tag' => $filter_data['job_tag'] ), 'tag', $ph ) : NULL;

		$query .= ( isset($filter_data['job_item_group_id']) ) ? $this->getWhereClauseSQL( 'jif.group_id', $filter_data['job_item_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_item_tag']) ) ? $this->getWhereClauseSQL( 'jif.id', array( 'company_id' => $company_id, 'object_type_id' => 610, 'tag' => $filter_data['job_item_tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10, 20, 30)
		$query .= '
						AND ( a.deleted = 0 )
					GROUP BY a.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.date_stamp, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, a.currency_id, a.currency_rate, a.hourly_rate, a.hourly_rate_with_burden
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
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ppf_b = new PayPeriodFactory();
		$pcf = new PayCodeFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array( 'company_id' => $company_id );

		//Make it so employees with 0 hours still show up!! Very important!
		//Order dock hours first, so it can be deducted from regular time.
		$query = '
					select
							a.user_id as user_id,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,
							a.date_stamp as date_stamp,
							a.branch_id,
							a.department_id,
							a.job_id as job_id,
							a.job_item_id as job_item_id,

							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id,
							pcf.type_id as pay_code_type_id,

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,

							a.hourly_rate as hourly_rate,
							a.hourly_rate_with_burden as hourly_rate_with_burden,

							min(a.start_time_stamp) as start_time_stamp,
							max(a.end_time_stamp) as end_time_stamp,
							sum(a.total_time) as total_time,
							sum(a.actual_total_time) as actual_total_time,
							sum(a.total_time_amount) as total_time_amount,
							sum(a.total_time_amount_with_burden) as total_time_amount_with_burden
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id
					LEFT JOIN '. $bf->getTable() .' as bf ON a.branch_id = bf.id
					LEFT JOIN '. $df->getTable() .' as df ON a.department_id = df.id
					LEFT JOIN '. $pcf->getTable() .' as pcf ON a.pay_code_id = pcf.id
					LEFT JOIN '. $ppf_b->getTable() .' as ppf ON a.pay_period_id = ppf.id ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '
					LEFT JOIN '. $jf->getTable() .' as jf ON a.job_id = jf.id
					LEFT JOIN '. $jif->getTable() .' as jif ON a.job_item_id = jif.id ';
		}

		$query .= '	where	uf.company_id = ? ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'uf.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['user_status']) AND !is_array($filter_data['user_status']) AND trim($filter_data['user_status']) != '' AND !isset($filter_data['user_status_id']) ) {
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
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['job_status']) AND !is_array($filter_data['job_status']) AND trim($filter_data['job_status']) != '' AND !isset($filter_data['job_status_id']) ) {
			$filter_data['job_status_id'] = Option::getByFuzzyValue( $filter_data['job_status'], $jf->getOptions('status') );
		}
		$query .= ( isset($filter_data['job_status_id']) ) ? $this->getWhereClauseSQL( 'jf.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'jf.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['job_item_group_id']) ) ? $this->getWhereClauseSQL( 'jif.group_id', $filter_data['job_item_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		//This isn't needed as it lists every status: AND a.status_id in (10, 20, 30)
		$query .= '
						AND ( a.deleted = 0 )
					GROUP BY a.user_id, ppf.id, ppf.start_date, ppf.end_date, ppf.transaction_date, a.date_stamp, a.branch_id, a.department_id, a.job_id, a.job_item_id, a.object_type_id, a.src_object_id, a.pay_code_id, pcf.type_id, a.currency_id, a.currency_rate, a.hourly_rate, a.hourly_rate_with_burden
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
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp', 'time_stamp', 'object_type_id', 'branch', 'department', 'default_branch', 'default_department', 'group', 'title');
		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc', 'a.object_type_id' => 'asc', 'a.total_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($filter_data['date_stamp']) ) {
			$filter_data['date'] = $filter_data['date_stamp'];
		}

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

		//If the user filters timesheet data based on branch/department/job/task, that will exclude object_id=5 (system total time) records
		//As those are never assigned to any branch/department/job/task and their timesheet will always look funny ( Total Time = 0, even if Regular Time is 8hrs ),
		//So always include *_id=0.
		if ( isset($filter_data['branch_id']) AND is_array($filter_data['branch_id']) ) {
			$filter_data['branch_id'][] = 0;
		}
		if ( isset($filter_data['department_id']) AND is_array($filter_data['department_id']) ) {
			$filter_data['department_id'][] = 0;
		}
		if ( isset($filter_data['job_id']) AND is_array($filter_data['job_id']) ) {
			$filter_data['job_id'][] = 0;
		}
		if ( isset($filter_data['job_item_id']) AND is_array($filter_data['job_item_id']) ) {
			$filter_data['job_item_id'][] = 0;
		}

		$uf = new UserFactory();
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
							a.punch_control_id as punch_control_id,

							a.object_type_id as object_type_id,
							a.src_object_id as src_object_id,
							a.pay_code_id as pay_code_id,

							CASE
							WHEN a.pay_code_id > 0 AND a.object_type_id = 30 THEN otpf.name
							WHEN a.pay_code_id > 0 AND a.object_type_id = 40 THEN ppf.name
							WHEN a.pay_code_id > 0 AND a.object_type_id = 50 THEN apf.name
							WHEN a.pay_code_id > 0 AND a.object_type_id = 100 THEN mpf.name
							WHEN a.pay_code_id > 0 AND a.object_type_id = 110 THEN bpf.name
							END as policy_name,

							a.start_type_id as start_type_id,
							a.start_time_stamp as start_time_stamp,
							a.end_type_id as end_type_id,
							a.end_time_stamp as end_time_stamp,

							a.override as override,
							a.note as note,

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

							a.currency_id as currency_id,
							a.currency_rate as currency_rate,
							a.base_hourly_rate as base_hourly_rate,
							a.hourly_rate as hourly_rate,
							a.total_time_amount as total_time_amount,
							a.hourly_rate_with_burden as hourly_rate_with_burden,
							a.total_time_amount_with_burden as total_time_amount_with_burden,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							a.pay_period_id as pay_period_id,

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
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as d ON a.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $otpf->getTable() .' as otpf ON ( a.pay_code_id > 0 AND a.object_type_id = 30 AND a.pay_code_id = otpf.id AND otpf.deleted = 0 )
							LEFT JOIN '. $ppf->getTable() .' as ppf ON   ( a.pay_code_id > 0 AND a.object_type_id = 40 AND a.pay_code_id = ppf.id AND ppf.deleted = 0 )
							LEFT JOIN '. $apf->getTable() .' as apf ON   ( a.pay_code_id > 0 AND a.object_type_id = 50 AND a.pay_code_id = apf.id AND apf.deleted = 0 )
							LEFT JOIN '. $mpf->getTable() .' as mpf ON   ( a.pay_code_id > 0 AND a.object_type_id = 100 AND a.pay_code_id = mpf.id AND mpf.deleted = 0 )
							LEFT JOIN '. $bpf->getTable() .' as bpf ON   ( a.pay_code_id > 0 AND a.object_type_id = 110 AND a.pay_code_id = bpf.id AND bpf.deleted = 0 )

							LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = a.user_id
																			and z.effective_date <= a.date_stamp
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
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		//$query .= ( isset($filter_data['user_date_id']) ) ? $this->getWhereClauseSQL( 'a.user_date_id', $filter_data['user_date_id'], 'numeric_list', $ph ) : NULL;
		//if ( isset($filter_data['date']) AND !is_array($filter_data['date']) AND trim($filter_data['date']) != '' ) {
		//	$ph[] = $this->db->BindDate( (int)$filter_data['date'] );
		//	$query	.=	' AND a.date_stamp = ?';
		//}
		$query .= ( isset($filter_data['date']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date'], 'date_stamp', $ph ) : NULL;

		$query .= ( isset($filter_data['object_type_id']) ) ? $this->getWhereClauseSQL( 'a.object_type_id', $filter_data['object_type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['src_object_id']) ) ? $this->getWhereClauseSQL( 'a.src_object_id', $filter_data['src_object_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_code_id']) ) ? $this->getWhereClauseSQL( 'a.pay_code_id', $filter_data['pay_code_id'], 'numeric_list', $ph ) : NULL;

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

		//$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		//$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'numeric_list', $ph ) : NULL;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset($filter_data['job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'x.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;

			$query .= ( isset($filter_data['job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['include_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['include_job_item_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['exclude_job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['exclude_job_item_id'], 'not_numeric_list', $ph ) : NULL;
		}

		$query .= ( isset($filter_data['date_stamp']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date_stamp'], 'date_range_datestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['start_date'], 'start_datestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['end_date'], 'end_datestamp', $ph ) : NULL;
		
		$query .= ' AND (a.deleted = 0 AND d.deleted = 0) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}
}
?>
