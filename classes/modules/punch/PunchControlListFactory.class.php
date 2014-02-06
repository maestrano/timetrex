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
 * $Id: PunchControlListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Punch
 */
class PunchControlListFactory extends PunchControlFactory implements IteratorAggregate {

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
					'id' => (int)$id,
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

	function getByCompanyId($company_id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $udf->getTable() .' as b,
							'. $uf->getTable() .' as c
					where	a.user_date_id = b.id
						AND b.user_id = c.id
						AND c.company_id = ?
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPunchId($punch_id, $order = NULL) {
		if ( $punch_id == '' ) {
			return FALSE;
		}

		$pf = new PunchFactory();

		$ph = array(
					'punch_id' => (int)$punch_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a,
							'. $pf->getTable() .' as b
					where 	a.id = b.punch_control_id
						AND b.id = ?
						AND ( a.deleted = 0 AND b.deleted=0 )
					';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserDateId($user_date_id, $order = NULL) {
		if ( $user_date_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					where
						a.user_date_id = ?
						AND ( a.deleted = 0 )
					';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	//This function grabs all the punches on the given day
	//and determines where the epoch will fit in.
	function getInCompletePunchControlIdByUserIdAndEpoch( $user_id, $epoch, $status_id ) {
		Debug::text(' Epoch: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__,10);
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $epoch == '' ) {
			return FALSE;
		}

		$plf = new PunchListFactory();
		$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch );
		if ( $plf->getRecordCount() > 0 ) {
			//Check for gaps.
			$prev_time_stamp = 0;
			foreach( $plf as $p_obj) {
				if ( $p_obj->getStatus() == 10 ) {
					$punch_arr[$p_obj->getPunchControlId()]['in'] = $p_obj->getTimeStamp();
				} else {
					$punch_arr[$p_obj->getPunchControlId()]['out'] = $p_obj->getTimeStamp();
				}

				if ( $prev_time_stamp != 0 ) {
					$prev_punch_arr[$p_obj->getTimeStamp()] = $prev_time_stamp;
				}

				$prev_time_stamp = $p_obj->getTimeStamp();
			}
			unset($prev_time_stamp);

			if ( isset($prev_punch_arr) ) {
				$next_punch_arr = array_flip($prev_punch_arr);
			}

			//Debug::Arr( $punch_arr, ' Punch Array: ', __FILE__, __LINE__, __METHOD__,10);
			//Debug::Arr( $next_punch_arr, ' Next Punch Array: ', __FILE__, __LINE__, __METHOD__,10);

			if ( isset($punch_arr) ) {
				$i=0;
				foreach($punch_arr as $punch_control_id => $data ) {
					$found_gap = FALSE;
					Debug::text(' Iteration: '. $i, __FILE__, __LINE__, __METHOD__,10);

					//Skip complete punch control rows.
					if ( isset($data['in']) AND isset($data['out']) ) {
						Debug::text(' Punch Control ID is Complete: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);
					} else {
						//Make sure we don't assign a In punch that comes AFTER an Out punch to the same pair.
						//As well the opposite, an Out punch that comes BEFORE an In punch to the same pair.
						if ( $status_id == 10 AND !isset($data['in']) AND ( isset($data['out']) AND $epoch <= $data['out'] ) ) {
							Debug::text(' aFound Valid Gap...', __FILE__, __LINE__, __METHOD__,10);
							$found_gap = TRUE;
						} elseif ( $status_id == 20 AND !isset($data['out']) AND ( isset($data['in']) AND $epoch >= $data['in'] ) ) {
							Debug::text(' bFound Valid Gap...', __FILE__, __LINE__, __METHOD__,10);
							$found_gap = TRUE;
						} else {
							Debug::text(' No Valid Gap Found...', __FILE__, __LINE__, __METHOD__,10);
						}
					}

					if ( $found_gap == TRUE ) {
						if ( $status_id == 10 ) { //In Gap
							Debug::text(' In Gap...', __FILE__, __LINE__, __METHOD__,10);
							if ( isset($prev_punch_arr[$data['out']]) ) {
								Debug::text(' Punch Before In Gap... Range Start: '. TTDate::getDate('DATE+TIME', $prev_punch_arr[$data['out']]) .' End: '. TTDate::getDate('DATE+TIME', $data['out']), __FILE__, __LINE__, __METHOD__,10);
								if ( $prev_punch_arr[$data['out']] == $data['out'] OR TTDate::isTimeOverLap($epoch, $epoch, $prev_punch_arr[$data['out']], $data['out'] ) ) {
									Debug::text(' Epoch OverLaps, THIS IS GOOD!', __FILE__, __LINE__, __METHOD__,10);
									Debug::text(' aReturning Punch Control ID: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);
									$retval = $punch_control_id;
									break; //Without this adding mass punches fails in some basic circumstances because it loops and attaches to a later punch control
								} else {
									Debug::text(' Epoch does not OverLaps, Cant attached to this punch_control!', __FILE__, __LINE__, __METHOD__,10);
								}

							} else {
								//No Punch After
								Debug::text(' NO Punch Before In Gap...', __FILE__, __LINE__, __METHOD__,10);
								$retval = $punch_control_id;
								break;
							}
						} else { //Out Gap
							Debug::text(' Out Gap...', __FILE__, __LINE__, __METHOD__,10);
							//Start: $data['in']
							//End: $data['in']
							if ( isset($next_punch_arr[$data['in']]) ) {
								Debug::text(' Punch After Out Gap... Range Start: '. TTDate::getDate('DATE+TIME', $data['in']) .' End: '. TTDate::getDate('DATE+TIME', $next_punch_arr[$data['in']]), __FILE__, __LINE__, __METHOD__,10);
								if ( $data['in'] == $next_punch_arr[$data['in']] OR TTDate::isTimeOverLap($epoch, $epoch, $data['in'], $next_punch_arr[$data['in']] ) ) {
									Debug::text(' Epoch OverLaps, THIS IS GOOD!', __FILE__, __LINE__, __METHOD__,10);
									Debug::text(' bReturning Punch Control ID: '. $punch_control_id, __FILE__, __LINE__, __METHOD__,10);
									$retval = $punch_control_id;
									break; //Without this adding mass punches fails in some basic circumstances because it loops and attaches to a later punch control
								} else {
									Debug::text(' Epoch does not OverLaps, Cant attached to this punch_control!', __FILE__, __LINE__, __METHOD__,10);
								}

							} else {
								//No Punch After
								Debug::text(' NO Punch After Out Gap...', __FILE__, __LINE__, __METHOD__,10);
								$retval = $punch_control_id;
								break;
							}
						}
					}
					$i++;
				}
			}
		}

		if ( isset($retval) ) {
			Debug::text(' Returning Punch Control ID: '. $retval, __FILE__, __LINE__, __METHOD__,10);
			return $retval;
		}

		Debug::text(' Returning FALSE No Valid Gaps Found...', __FILE__, __LINE__, __METHOD__,10);
		//FALSE means no gaps in punch control rows found.
		return FALSE;
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

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array('first_name', 'last_name', 'date_stamp','time_stamp','type_id','status_id','branch','department','default_branch','default_department','group','title');
		if ( $order == NULL ) {
			$order = array( 'c.pay_period_id' => 'asc','c.user_id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = FALSE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['user_id']) ) {
			$filter_data['id'] = $filter_data['user_id'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
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
		if ( isset($filter_data['punch_branch_ids']) ) {
			$filter_data['punch_branch_id'] = $filter_data['punch_branch_ids'];
		}
		if ( isset($filter_data['punch_department_ids']) ) {
			$filter_data['punch_department_id'] = $filter_data['punch_department_ids'];
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
		$pcf = new PunchControlFactory();
		$uwf = new UserWageFactory();
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
					);

		$query = '
					select
							b.id as id,
							b.user_date_id as user_date_id,
							b.branch_id as branch_id,
							j.name as branch,
							b.department_id as department_id,
							k.name as department,
							b.job_id as job_id,
							x.name as job,
							b.job_item_id as job_item_id,
							y.name as job_item,
							b.quantity as quantity,
							b.bad_quantity as bad_quantity,
							b.total_time as total_time,
							b.actual_total_time as actual_total_time,
							b.meal_policy_id as meal_policy_id,
							b.other_id1 as other_id1,
							b.other_id2 as other_id2,
							b.other_id3 as other_id3,
							b.other_id4 as other_id4,
							b.other_id5 as other_id5,
							b.note as note,

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
						x.name as job_name,
						x.status_id as job_status_id,
						x.manual_id as job_manual_id,
						x.branch_id as job_branch_id,
						x.department_id as job_department_id,
						x.group_id as job_group_id';
		}

		$query .= '
					from 	'. $this->getTable() .' as b
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $uf->getTable() .' as d ON c.user_id = d.id

							LEFT JOIN '. $bf->getTable() .' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN '. $ugf->getTable() .' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN '. $utf->getTable() .' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN '. $bf->getTable() .' as j ON ( b.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN '. $df->getTable() .' as k ON ( b.department_id = k.id AND k.deleted = 0)

							LEFT JOIN '. $uwf->getTable() .' as z ON z.id = (select z.id
																		from '. $uwf->getTable() .' as z
																		where z.user_id = c.user_id
																			and z.effective_date <= c.date_stamp
																			and z.deleted = 0
																			order by z.effective_date desc LiMiT 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as x ON b.job_id = x.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as y ON b.job_item_id = y.id';
		}

		$query .= '	WHERE d.company_id = ?';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND d.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND b.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND d.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}

		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND d.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
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
		if ( isset($filter_data['punch_branch_id']) AND isset($filter_data['punch_branch_id'][0]) AND !in_array(-1, (array)$filter_data['punch_branch_id']) ) {
			$query  .=	' AND b.branch_id in ('. $this->getListSQL($filter_data['punch_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['punch_department_id']) AND isset($filter_data['punch_department_id'][0]) AND !in_array(-1, (array)$filter_data['punch_department_id']) ) {
			$query  .=	' AND b.department_id in ('. $this->getListSQL($filter_data['punch_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_ids']) AND isset($filter_data['pay_period_ids'][0]) AND !in_array(-1, (array)$filter_data['pay_period_ids']) ) {
			$query .= 	' AND c.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_ids'], $ph) .') ';
		}


		//Use the job_id in the punch_control table so we can filter by '0' or No Job
		if ( isset($filter_data['include_job_id']) AND isset($filter_data['include_job_id'][0]) AND !in_array(-1, (array)$filter_data['include_job_id']) ) {
			$query  .=	' AND b.job_id in ('. $this->getListSQL($filter_data['include_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_job_id']) AND isset($filter_data['exclude_job_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_job_id']) ) {
			$query  .=	' AND b.job_id not in ('. $this->getListSQL($filter_data['exclude_job_id'], $ph) .') ';
		}
		if ( isset($filter_data['job_group_id']) AND isset($filter_data['job_group_id'][0]) AND !in_array(-1, (array)$filter_data['job_group_id']) ) {
			if ( isset($filter_data['include_job_subgroups']) AND (bool)$filter_data['include_job_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['job_group_id'] = $uglf->getByCompanyIdAndGroupIdAndjob_subgroupsArray( $company_id, $filter_data['job_group_id'], TRUE);
			}
			$query  .=	' AND x.group_id in ('. $this->getListSQL($filter_data['job_group_id'], $ph) .') ';
		}

		if ( isset($filter_data['job_item_id']) AND isset($filter_data['job_item_id'][0]) AND !in_array(-1, (array)$filter_data['job_item_id']) ) {
			$query  .=	' AND b.job_item_id in ('. $this->getListSQL($filter_data['job_item_id'], $ph) .') ';
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
						AND ( b.deleted = 0 AND c.deleted = 0 AND d.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}
?>