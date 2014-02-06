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
 * $Revision: 11053 $
 * $Id: RecurringScheduleTemplateListFactory.class.php 11053 2013-09-27 23:08:52Z ipso $
 * $Date: 2013-09-27 16:08:52 -0700 (Fri, 27 Sep 2013) $
 */

/**
 * @package Modules\Schedule
 */
class RecurringScheduleTemplateListFactory extends RecurringScheduleTemplateFactory implements IteratorAggregate {

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

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $rstcf->getTable() .' as b ON a.recurring_schedule_template_control_id = b.id
					where	b.company_id = ?
						AND a.deleted = 0';
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

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $rstcf->getTable() .' as b ON a.recurring_schedule_template_control_id = b.id
					where	b.company_id = ?
						AND a.id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByRecurringScheduleTemplateControlId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	recurring_schedule_template_control_id = ?
						AND deleted = 0
					ORDER BY week asc';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
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
		//Debug::Arr($order,'aOrder Data:', __FILE__, __LINE__, __METHOD__,10);

		$additional_order_fields = array('name', 'description', 'last_name', 'start_date', 'user_id');
		if ( $order == NULL ) {
			$order = array( 'c.start_date' => 'asc', 'cb.user_id' => 'desc', 'a.week' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}

		//This is used by Flex Schedule Summary report.
		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		/*
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		*/
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

		//Debug::Arr($order,'bOrder Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$uwf = new UserWageFactory();
		$rscf = new RecurringScheduleControlFactory();
		$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$apf = new AbsencePolicyFactory();

		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'filter_end_date' => $this->db->BindDate( $filter_data['end_date'] ),
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							apf.type_id as absence_policy_type_id,
							apf.name as absence_policy,
							cb.user_id as user_id,

							CASE WHEN a.branch_id = -1 THEN d.default_branch_id ELSE a.branch_id END as schedule_branch_id,
							CASE WHEN a.branch_id = -1 THEN bf.name ELSE bfb.name END as schedule_branch,
							CASE WHEN a.department_id = -1 THEN d.default_department_id ELSE a.department_id END as schedule_department_id,
							CASE WHEN a.department_id = -1 THEN df.name ELSE dfb.name END as schedule_department,

							c.start_date as recurring_schedule_control_start_date,
							c.end_date as recurring_schedule_control_end_date,
							c.start_week as recurring_schedule_control_start_week,
							zz.max_week as max_week,
							( (((a.week-1)+zz.max_week-(c.start_week-1))%zz.max_week) + 1) as remapped_week,

							d.first_name as first_name,
							d.last_name as last_name,
							d.default_branch_id as default_branch_id,
							bf.name as default_branch,
							d.default_department_id as default_department_id,
							df.name as default_department,
							d.title_id as title_id,
							utf.name as title,
							d.group_id as group_id,
							ugf.name as "group",
							d.created_by as user_created_by,
							d.hire_date as hire_date,
							d.termination_date as termination_date,

							uw.id as user_wage_id,
							uw.hourly_rate as user_wage_hourly_rate,
							uw.effective_date as user_wage_effective_date,

							ppsf.shift_assigned_day_id as shift_assigned_day_id,

							c.created_by as recurring_schedule_control_created_by
							';
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

		//Since when dealing with recurring schedules, we don't have a row for each specific date, so when determining wages
		//we can only use the last wage entered that is earlier than the filter end date.
		//Since in theory committed schedules will occur before todays date anyways, the accuracy won't be off too much unless
		//the end date they specify is really far in the future, and post dated wage entry is also made.
		$query .= '
					from 	'. $this->getTable() .' as a
						LEFT JOIN ( select z.recurring_schedule_template_control_id, max(z.week) as max_week from recurring_schedule_template as z where deleted = 0 group by z.recurring_schedule_template_control_id ) as zz ON a.recurring_schedule_template_control_id = zz.recurring_schedule_template_control_id
						LEFT JOIN '. $rstcf->getTable() .' as b ON a.recurring_schedule_template_control_id = b.id
						LEFT JOIN '. $rscf->getTable() .' as c ON a.recurring_schedule_template_control_id = c.recurring_schedule_template_control_id
						LEFT JOIN '. $rsuf->getTable() .' as cb ON c.id = cb.recurring_schedule_control_id
						LEFT JOIN '. $uf->getTable() .' as d ON cb.user_id = d.id

						LEFT JOIN '. $ppsuf->getTable() .' as ppsuf ON d.id = ppsuf.user_id
						LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )

						LEFT JOIN '. $bf->getTable() .' as bf ON ( d.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN '. $bf->getTable() .' as bfb ON ( a.branch_id = bfb.id AND bfb.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as df ON ( d.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as dfb ON ( a.department_id = dfb.id AND dfb.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as ugf ON ( d.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as utf ON ( d.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN '. $apf->getTable() .' as apf ON ( a.absence_policy_id = apf.id AND apf.deleted = 0 )

						LEFT JOIN '. $uwf->getTable() .' as uw ON uw.id = (select uwb.id
																	from '. $uwf->getTable() .' as uwb
																	where uwb.user_id = cb.user_id
																		and uwb.effective_date <= ?
																		and uwb.deleted = 0
																		order by uwb.effective_date desc limit 1)

						';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON a.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON a.job_item_id = y.id';
		}

		$query .=' where 	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}

		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
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

		if ( isset($filter_data['schedule_branch_id']) AND isset($filter_data['schedule_branch_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_branch_id']) ) {
			$query  .=	' AND ( a.branch_id in ('. $this->getListSQL($filter_data['schedule_branch_id'], $ph) .') OR ( a.branch_id = -1 AND d.default_branch_id in ('. $this->getListSQL($filter_data['schedule_branch_id'], $ph) .') ) )';
		}
		if ( isset($filter_data['schedule_department_id']) AND isset($filter_data['schedule_department_id'][0]) AND !in_array(-1, (array)$filter_data['schedule_department_id']) ) {
			$query  .=	' AND ( a.department_id in ('. $this->getListSQL($filter_data['schedule_department_id'], $ph) .') OR ( a.department_id = -1 AND d.default_department_id in ('. $this->getListSQL($filter_data['schedule_department_id'], $ph) .') ) )';
		}

		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND d.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}

		//Use the job_id in the schedule table so we can filter by '0' or No Job
		if ( isset($filter_data['job_id']) AND isset($filter_data['job_id'][0]) AND !in_array(-1, (array)$filter_data['job_id']) ) {
			$query  .=	' AND a.job_id in ('. $this->getListSQL($filter_data['job_id'], $ph) .') ';
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

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != ''
				AND isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '') {
			$start_date_stamp = $this->db->BindDate( $filter_data['start_date'] );
			$end_date_stamp = $this->db->BindDate( $filter_data['end_date'] );

			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;
			$ph[] = $start_date_stamp;
			$ph[] = $end_date_stamp;

			$ph[] = $filter_data['end_date'] ;
			$ph[] = $filter_data['start_date'];

			$query  .=	' AND (
								(c.start_date >= ? AND c.start_date <= ? AND c.end_date IS NULL )
								OR
								(c.start_date <= ? AND c.end_date IS NULL )
								OR
								(c.start_date <= ? AND c.end_date >= ? )
								OR
								(c.start_date >= ? AND c.end_date <= ? )
								OR
								(c.start_date >= ? AND c.start_date <= ? )
								OR
								(c.end_date >= ? AND c.end_date <= ? )
								OR
								(c.start_date <= ? AND c.end_date >= ? )
							)
							AND
							(
								( d.hire_date is NULL OR d.hire_date <= ? )
								AND
								( d.termination_date is NULL OR d.termination_date >= ? )
							)
						';
		}

		$query .= 	'
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND (d.deleted is NULL OR d.deleted = 0 ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph,' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

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


		$additional_order_fields = array();
		$sort_column_aliases = array(

									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'week' => 'asc', 'sun' => 'asc', 'mon' => 'asc', 'tue' => 'asc', 'wed' => 'asc', 'thu' => 'asc', 'fri' => 'asc', 'sat' => 'asc',  'start_time' => 'asc', 'end_time' => 'asc');
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['week']) ) {
				$order['week'] = 'asc';
			}
			if ( !isset($order['start_time']) ) {
				$order['start_time'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$rstcf = new RecurringScheduleTemplateControlFactory();
		$uf = new UserFactory();


		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $rstcf->getTable() .' as b ON a.recurring_schedule_template_control_id = b.id
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['recurring_schedule_template_control_id']) AND isset($filter_data['recurring_schedule_template_control_id'][0]) AND !in_array(-1, (array)$filter_data['recurring_schedule_template_control_id']) ) {
			$query  .=	' AND a.recurring_schedule_template_control_id in ('. $this->getListSQL($filter_data['recurring_schedule_template_control_id'], $ph) .') ';
		}

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
