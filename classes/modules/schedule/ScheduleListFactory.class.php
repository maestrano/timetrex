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
 * @package Modules\Schedule
 */
class ScheduleListFactory extends ScheduleFactory implements IteratorAggregate {

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

	function getByCompanyID($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.start_time' => 'asc', 'a.status_id' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	 a.company_id = ?
						AND  a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );
	
		return $this;
	}

	function getByIdAndCompanyId( $id, $company_id ) {
		return $this->getByCompanyIDAndId($company_id, $id);
	}
	function getByCompanyIDAndId($company_id, $id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					'company_id2' => $company_id,
					);

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		//Always include the user_id, this is required for mass edit to function correctly and not assign schedules to OPEN employee all the time.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	( c.company_id = ? OR a.company_id = ? )
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 )
					ORDER BY a.start_time asc, a.status_id desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDateStamp($user_id, $date_stamp, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date_stamp == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND date_stamp = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndDateStampAndStatus($user_id, $date_stamp, $status_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $date_stamp == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					'status_id' => $status_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND date_stamp = ?
						AND status_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$additional_order_fields = array('a.date_stamp');

		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where	a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch( $user_id, $epoch, $week_start_epoch ) {
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
					'epoch' =>	$this->db->BindDate( $epoch ),
					);

		//DO NOT Include paid absences. Only count regular time towards weekly overtime.
		//And other weekly overtime polices!
		$query = '
					select	sum(a.total_time)
					from	'. $this->getTable() .' as a
					where
						a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp < ?
						AND a.status_id = 10
						AND a.deleted = 0
				';
		$total = $this->db->GetOne($query, $ph);

		if ($total === FALSE ) {
			$total = 0;
		}
		Debug::text('Total: '. $total, __FILE__, __LINE__, __METHOD__, 10);

		return $total;
	}

	function getByUserIdAndTypeAndDirectionFromDate($user_id, $type_id, $direction, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $direction == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$strict = FALSE;

			$order = array( 'a.date_stamp' => 'asc' );	//When direction is after, we need to get the days in the proper order (ASC)
			if ( strtolower($direction) == 'before' ) {
				$order = array( 'a.date_stamp' => 'desc' ); //When direction is before, we need to get the days in the proper order (DESC)
				$direction = '<';
			} elseif ( strtolower($direction) == 'after' ) {
				$direction = '>';
			} else {
				return FALSE;
			}
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'date' => $this->db->BindDate( $date ),
					'type_id' => $type_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where	a.date_stamp '. $direction .' ?
						AND a.status_id = ?
						AND a.user_id in ('. $this->getListSQL( $user_id, $ph ) .')
						AND ( a.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query .' Limit: '. $limit, __FILE__, __LINE__, __METHOD__, 10);
		return $this;
	}

	function getConflictingByUserIdAndStartDateAndEndDate($user_id, $start_date, $end_date, $id = NULL, $where = NULL, $order = NULL) {
		Debug::Text('User ID: '. $user_id .' Start Date: '. $start_date .' End Date: '. $end_date, __FILE__, __LINE__, __METHOD__, 10);

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		//MySQL is picky when it comes to timestamp filters on datestamp columns.
		$start_datestamp = $this->db->BindDate( $start_date );
		$end_datestamp = $this->db->BindDate( $end_date );

		$start_timestamp = $this->db->BindTimeStamp( $start_date );
		$end_timestamp = $this->db->BindTimeStamp( $end_date );

/*
	This doesn't allow matching start/end times. SO it doesn't work with
	scheduling job transfers.
						(
							(start_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time >= '. $start_date .' AND start_time <= '. $end_date .')
							OR
							(end_time >= '. $start_date .' AND end_time <= '. $end_date .')
							OR
							(start_time <= '. $start_date .' AND end_time >= '. $end_date .')
						)


*/

		$ph = array(
					'user_id' => $user_id,
					'start_date_a' => $start_datestamp,
					'end_date_b' => $end_datestamp,
					'id' => (int)$id,
					'start_date1' => $start_timestamp,
					'end_date1' => $end_timestamp,
					'start_date2' => $start_timestamp,
					'end_date2' => $end_timestamp,
					'start_date3' => $start_timestamp,
					'end_date3' => $end_timestamp,
					'start_date4' => $start_timestamp,
					'end_date4' => $end_timestamp,
					'start_date5' => $start_timestamp,
					'end_date5' => $end_timestamp,
					);

		//Add filter on date_stamp for optimization
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.id != ?
						AND
						(
							( a.start_time >= ? AND a.end_time <= ? )
							OR
							( a.start_time >= ? AND a.start_time < ? )
							OR
							( a.end_time > ? AND a.end_time <= ? )
							OR
							( a.start_time <= ? AND a.end_time >= ? )
							OR
							( a.start_time = ? AND a.end_time = ? )
						)
						AND ( a.deleted = 0 )
					ORDER BY start_time';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getScheduleObjectByUserIdAndEpoch( $user_id, $epoch ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		//Need to handle schedules on next/previous dates from when the punch is.
		//ie: if the schedule started on 11:30PM on Jul 5th and the punch is 01:00AM on Jul 6th.
		$slf = new ScheduleListFactory();
		$slf->getByUserIdAndStartDateAndEndDate($user_id, (TTDate::getMiddleDayEpoch($epoch) - 86400), (TTDate::getMiddleDayEpoch($epoch) + 86400) );
		if ( $slf->getRecordCount() > 0 ) {
			Debug::Text(' Found User Date ID! User: '. $user_id .' Epoch: '. TTDate::getDATE('DATE+TIME', $epoch ) .'('.$epoch.')', __FILE__, __LINE__, __METHOD__, 10);		
			$retval = FALSE;
			$best_diff = FALSE;
			//Check for schedule policy
			foreach( $slf as $s_obj ) {
				Debug::Text(' Found Schedule!: ID: '. $s_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				//If the Start/Stop window is large (ie: 6-8hrs) we need to find the closest schedule.
				$schedule_diff = $s_obj->inScheduleDifference( $epoch );
				if ( $schedule_diff === 0 ) {
					Debug::text(' Within schedule times. ', __FILE__, __LINE__, __METHOD__, 10);
					return $s_obj;
				} else {
					if ( $schedule_diff > 0 AND ( $best_diff === FALSE OR $schedule_diff < $best_diff ) ) {
						Debug::text(' Within schedule start/stop time by: '. $schedule_diff .' Prev Best Diff: '. $best_diff, __FILE__, __LINE__, __METHOD__, 10);
						$best_diff = $schedule_diff;
						$retval = $s_obj;
					}
				}
			}

			if ( isset($retval) AND is_object($retval) ) {
				return $retval;
			}
		}
		
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

		$additional_order_fields = array('pay_period_id', 'user_id', 'last_name');

		$sort_column_aliases = array(
									'pay_period' => 'udf.pay_period',
									'user_id' => 'udf.user_id',
									'status_id' => 'a.status_id',
									'last_name' => 'uf.last_name',
									'first_name' => 'uf.first_name',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			//$order = array( 'udf.pay_period_id' => 'asc', 'udf.user_id' => 'asc', 'a.start_time' => 'asc' );
			$order = array( 'uf.last_name' => 'asc', 'a.start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}

		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['status_ids']) ) {
			$filter_data['status_id'] = $filter_data['status_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['schedule_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['schedule_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['schedule_branch_ids']) ) {
			$filter_data['schedule_branch_id'] = $filter_data['schedule_branch_ids'];
		}
		if ( isset($filter_data['schedule_department_ids']) ) {
			$filter_data['schedule_department_id'] = $filter_data['schedule_department_ids'];
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
		$uwf = new UserWageFactory();
		$apf = new AbsencePolicyFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'company_id' => $company_id,
					'company_id2' => $company_id,
					);

		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							bfb.name as branch,
							a.department_id as department_id,
							dfb.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							apf.name as absence_policy,
							apf.type_id as absence_policy_type_id,

							a.note as note,

							a.created_date as created_date,
							a.updated_date as updated_date,

							bf.name as default_branch,
							df.name as default_department,
							ugf.name as "group",
							utf.name as title,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							a.pay_period_id as pay_period_id,

							uf.first_name as first_name,
							uf.last_name as last_name,
							uf.default_branch_id as default_branch_id,
							uf.default_department_id as default_department_id,
							uf.title_id as title_id,
							uf.group_id as group_id,
							uf.created_by as user_created_by,

							uwf.id as user_wage_id,
							uwf.hourly_rate as user_wage_hourly_rate,
							uwf.effective_date as user_wage_effective_date ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						x.name as job,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id,

						y.name as job_item,
						y.manual_id as job_item_manual_id,
						y.group_id as job_item_group_id';
		}

		$query .= '
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as uf ON a.user_id = uf.id
							LEFT JOIN '. $bf->getTable() .' as bf ON ( uf.default_branch_id = bf.id AND bf.deleted = 0)
							LEFT JOIN '. $bf->getTable() .' as bfb ON ( a.branch_id = bfb.id AND bfb.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as df ON ( uf.default_department_id = df.id AND df.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as dfb ON ( a.department_id = dfb.id AND dfb.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as ugf ON ( uf.group_id = ugf.id AND ugf.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as utf ON ( uf.title_id = utf.id AND utf.deleted = 0 )
							LEFT JOIN '. $apf->getTable() .' as apf ON a.absence_policy_id = apf.id
							LEFT JOIN '. $uwf->getTable() .' as uwf ON uwf.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = a.user_id
																			and z.effective_date <= a.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON a.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON a.job_item_id = y.id';
		}

		$query .= '	WHERE ( uf.company_id = ? OR a.company_id = ? )';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		//Make sure we filter on user_date.user_id column, to handle OPEN shifts.
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['schedule_branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['schedule_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['schedule_department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['schedule_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['schedule_policy_id']) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['absence_policy_id']) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset($filter_data['job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'x.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'numeric_list', $ph ) : NULL;
		}

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['start_date'] );
			$query	.=	' AND a.start_time >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp( (int)$filter_data['end_date'] );
			$query	.=	' AND a.start_time <= ?';
		}

		$query .=	'
						AND ( a.deleted = 0 AND ( uf.deleted IS NULL OR uf.deleted = 0 ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		$uf = new UserFactory();

		$ph = array( 'company_id' => $company_id );

		$query = '
					select	a.user_id as user_id,
							a.pay_period_id as pay_period_id,
							a.status_id as status_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as c
					where	a.user_id = c.id
						AND c.company_id = ? ';
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .=	' AND a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}

		//Schedule/Punch branches/departments
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query .=	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		$query .= '		AND ( a.deleted = 0 AND c.deleted = 0)
					GROUP BY a.user_id, a.pay_period_id, a.status_id
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getDayReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
		}

		$uf = new UserFactory();

		$ph = array( 'company_id' => $company_id );

		$query = '
					select	a.user_id as user_id,
							a.pay_period_id as pay_period_id,
							a.date_stamp as date_stamp,
							a.status_id as status_id,
							sum(total_time) as total_time
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as c
					where	a.user_id = c.id
						AND c.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}

		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query	.=	' AND c.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query	.=	' AND c.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query	.=	' AND c.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query	.=	' AND c.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .=	' AND a.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query	.=	' AND c.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}

		//Schedule/Punch branches/departments
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query .=	' AND a.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query .=	' AND a.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}

		if ( isset($filter_data['schedule_policy_id']) AND isset($filter_data['schedule_policy_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_policy_id']) ) {
			$query	.=	' AND a.schedule_policy_id in ('. $this->getListSQL($filter_data['schedule_policy_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query	.=	' AND a.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query	.=	' AND a.date_stamp <= ?';
		}

		$query .= '
						AND ( a.deleted = 0 AND c.deleted = 0)
					GROUP BY a.user_id, a.pay_period_id, a.date_stamp, a.status_id
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

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('schedule_policy_id', 'schedule_policy', 'absence_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', );

		$sort_column_aliases = array(
									'first_name' => 'd.first_name',
									'last_name' => 'd.last_name',
									'updated_date' => 'a.updated_date',
									'created_date' => 'a.created_date',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'a.pay_period_id' => 'asc', 'a.user_id' => 'asc', 'a.start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		//if ( isset($filter_data['exclude_user_ids']) ) {
		//	$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		//}
		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_user_id'] = $filter_data['exclude_user_ids'];
		}

		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['user_id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
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
		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['start_time']) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset($filter_data['end_time']) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'company_id' => $company_id,
					'company_id2' => $company_id,
					);

		//"group" is a reserved word in MySQL.
		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.note as note,

							i.name as schedule_policy,
							apf.name as absence_policy,

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

							m.id as user_wage_id,
							m.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						w.name as job,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id,

						x.name as job_item,
						x.manual_id as job_item_manual_id,
						x.group_id as job_item_group_id
						';
		}

		$query .= '
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $spf->getTable() .' as i ON a.schedule_policy_id = i.id
							LEFT JOIN '. $uf->getTable() .' as d ON ( a.user_id = d.id AND d.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $apf->getTable() .' as apf ON a.absence_policy_id = apf.id

							LEFT JOIN '. $uwf->getTable() .' as m ON m.id = (select m.id
																		from '. $uwf->getTable() .' as m
																		where m.user_id = a.user_id
																			and m.effective_date <= a.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE ( d.company_id = ? OR a.company_id = ? ) ';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['schedule_policy_id']) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['absence_policy_id']) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'w.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
			$query .= ( isset($filter_data['job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'numeric_list', $ph ) : NULL;
		}

		$query .= ( isset($filter_data['date_stamp']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date_stamp'], 'date_range_datestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['start_date'], 'start_datestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['end_date'], 'end_datestamp', $ph ) : NULL;

		$query .= ( isset($filter_data['start_time']) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['start_time'], 'start_timestamp', $ph ) : NULL;
		$query .= ( isset($filter_data['end_time']) ) ? $this->getWhereClauseSQL( 'a.end_time', $filter_data['end_time'], 'end_timestamp', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND ( a.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getScheduleSummaryReportByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('schedule_policy_id', 'schedule_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', );

		$sort_column_aliases = array(
									'updated_date' => 'a.updated_date',
									'created_date' => 'a.created_date',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'a.pay_period_id' => 'asc', 'a.user_id' => 'asc', 'a.start_time' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_user_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['include_user_id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset($filter_data['schedule_branch_id']) ) {
			$filter_data['branch_id'] = $filter_data['schedule_branch_id'];
		}
		if ( isset($filter_data['schedule_department_id']) ) {
			$filter_data['department_id'] = $filter_data['schedule_department_id'];
		}

		if ( isset($filter_data['exclude_job_ids']) ) {
			$filter_data['exclude_job_id'] = $filter_data['exclude_job_ids'];
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
		if ( isset($filter_data['pay_period_ids']) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset($filter_data['start_time']) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset($filter_data['end_time']) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$ppf = new PayPeriodFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'company_id' => $company_id,
					'company_id2' => $company_id,
					);

		//"group" is a reserved word in MySQL.
		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							i.name as schedule_policy,

							a.note as note,

							a.absence_policy_id as absence_policy_id,
							apf.name as absence_policy,
							apf.type_id as absence_policy_type_id,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							ppf.id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

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

							m.id as user_wage_id,
							m.hourly_rate as user_wage_hourly_rate,
							m.labor_burden_percent as user_labor_burden_percent,
							m.effective_date as user_wage_effective_date,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						w.name as job_name,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id';
		}

		$query .= '
					from	'. $this->getTable() .' as a
							LEFT JOIN '. $spf->getTable() .' as i ON ( a.schedule_policy_id = i.id AND i.deleted = 0)
							LEFT JOIN '. $apf->getTable() .' as apf ON ( a.absence_policy_id = apf.id AND apf.deleted = 0)
							LEFT JOIN '. $ppf->getTable() .' as ppf ON a.pay_period_id = ppf.id
							LEFT JOIN '. $uf->getTable() .' as d ON ( a.user_id = d.id AND d.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $uwf->getTable() .' as m ON m.id = (select m.id
																		from '. $uwf->getTable() .' as m
																		where m.user_id = a.user_id
																			and m.effective_date <= a.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE ( d.company_id = ? OR a.company_id = ? )';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'd.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;

		//Need to include/exclude users based on a.user_id, as we need to support OPEN shifts and user_id=0 which can only happen in user_date table.
		$query .= ( isset($filter_data['include_user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['include_user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_status_id']) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_user_subgroups']) AND (bool)$filter_data['include_user_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
		}
		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['branch_id']) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['department_id']) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['schedule_policy_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['schedule_policy_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'd.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		//Use the job_id in the schedule table so we can filter by '0' or No Job
		$query .= ( isset($filter_data['include_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_job_id']) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_numeric_list', $ph ) : NULL;
		if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
			$jglf = new JobGroupListFactory();
			$filter_data['job_group_id'] = $jglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['job_group_id']) ) ? $this->getWhereClauseSQL( 'w.group_id', $filter_data['job_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['job_item_id']) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp($filter_data['start_date']);
			$query	.=	' AND a.start_time >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindTimeStamp($filter_data['end_date']);
			$query	.=	' AND a.start_time <= ?';
		}

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;

		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	'
						AND (a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}
?>
