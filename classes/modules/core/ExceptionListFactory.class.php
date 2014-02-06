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
 * $Id: ExceptionListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Core
 */
class ExceptionListFactory extends ExceptionFactory implements IteratorAggregate {

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

	function getByUserDateId($user_date_id, $where = NULL, $order = NULL) {
		if ( $user_date_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_date_id' => $user_date_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_date_id = ?
						AND deleted = 0';
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

		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc', 'trigger_time' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$epf = new ExceptionPolicyFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $epf->getTable() .' as epf ON a.exception_policy_id = epf.id
					LEFT JOIN '. $epcf->getTable() .' as epcf ON epf.exception_policy_control_id = epcf.id
					where
						epcf.company_id = ?
						a.id in ('. $this->getListSQL($id, $ph) .')
						AND ( a.deleted = 0 AND epf.deleted = 0 AND epcf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$epf = new ExceptionPolicyFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $epf->getTable() .' as epf ON a.exception_policy_id = epf.id
					LEFT JOIN '. $epcf->getTable() .' as epcf ON epf.exception_policy_control_id = epcf.id
					where
						epcf.company_id = ?
						AND ( a.deleted = 0 AND epf.deleted = 0 AND epcf.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndUserIdAndStartDateAndEndDate($company_id, $user_id, $start_date, $end_date) {
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

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$epf = new ExceptionPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date )
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp,
							d.severity_id as severity_id,
							d.type_id as exception_policy_type_id
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					where
						c.company_id = ?
						AND	b.user_id = ?
						AND b.date_stamp >= ?
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					ORDER BY b.date_stamp asc, d.type_id
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getFlaggedExceptionsByUserIdAndPayPeriodStatus($user_id, $pay_period_status) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_status == '' ) {
			return FALSE;
		}

		$udf = new UserDateFactory();
		$epf = new ExceptionPolicyFactory();
		$ppf = new PayPeriodFactory();
		$rf = new RequestFactory();

		$ph = array(
					'user_id' => $user_id,
					'date_stamp1' => $this->db->BindDate( TTDate::getBeginDayEpoch( TTDate::getTime()-(32*86400) ) ), //Narrow down the date range as a performance optimizaiton.
					'date_stamp2' => $this->db->BindDate( TTDate::getBeginDayEpoch( TTDate::getTime() ) ), //Exclude any exceptions after today, but we must include todays exceptions because missing IN punches could exist.
					'status_id' => $pay_period_status,
					);

		$query = '
					select 	d.severity_id as severity_id,
							count(*) as total
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					LEFT JOIN '. $ppf->getTable() .' as e ON b.pay_period_id = e.id
					where
						b.user_id = ?
						AND a.type_id = 50
						AND ( b.date_stamp >= ? AND b.date_stamp <= ? )
						AND e.status_id = ?
						AND NOT EXISTS ( select z.id from '. $rf->getTable() .' as z where z.user_date_id = a.user_date_id AND z.status_id = 30 )
						AND ( a.deleted = 0 AND b.deleted = 0 AND e.deleted=0)
					GROUP BY d.severity_id
					ORDER BY d.severity_id desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSumExceptionsByPayPeriodIdAndBeforeDate($pay_period_id, $before_epoch) {
		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		if ( $before_epoch == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$epf = new ExceptionPolicyFactory();
		$ppf = new PayPeriodFactory();
		$rf = new RequestFactory();

		$ph = array(
					'pay_period_id' => $pay_period_id,
					'date_stamp' => $this->db->BindDate( TTDate::getBeginDayEpoch( $before_epoch  ) ),
					);

		//Ignore pre-mature exceptions when counting exceptions.
		$query = '
					select 	d.severity_id as severity_id,
							count(*) as count
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					LEFT JOIN '. $ppf->getTable() .' as e ON b.pay_period_id = e.id
					where
						e.id = ?
						AND a.type_id = 50
						AND b.date_stamp <= ?
						AND ( a.deleted = 0 AND b.deleted = 0 AND e.deleted=0)
					GROUP BY d.severity_id
					ORDER BY d.severity_id desc
					';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndPayPeriodStatus($company_id, $status, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.severity_id' => 'desc', 'b.user_id' => 'asc', 'b.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			//$strict = TRUE;
			$strict = FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$ppf = new PayPeriodFactory();
		$epf = new ExceptionPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp,
							d.severity_id as severity_id,
							d.type_id as exception_policy_type_id,
							b.user_id as user_id
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					LEFT JOIN '. $ppf->getTable() .' as e ON b.pay_period_id = e.id
					where
						c.company_id = ?
						AND e.status_id in ('. $this->getListSQL( $status, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND e.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIDAndTypeAndPayPeriodStatus($company_id, $type, $status, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $type == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.severity_id' => 'desc', 'b.user_id' => 'asc', 'b.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			//$strict = TRUE;
			$strict = FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$ppf = new PayPeriodFactory();
		$epf = new ExceptionPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp,
							d.severity_id as severity_id,
							d.type_id as exception_policy_type_id,
							b.user_id as user_id
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					LEFT JOIN '. $ppf->getTable() .' as e ON b.pay_period_id = e.id
					where
						c.company_id = ?
						AND a.type_id in ('. $this->getListSQL( $type, $ph ) .')
						AND e.status_id in ('. $this->getListSQL( $status, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND d.deleted = 0 AND e.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIDAndUserIDAndPayPeriodStatus($company_id, $user_id, $status, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $status == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'd.severity_id' => 'desc', 'b.user_id' => 'asc', 'b.date_stamp' => 'asc', 'd.type_id' => 'asc' );
			$strict = FALSE;
		} else {
			//$strict = TRUE;
			$strict = FALSE;
		}

		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$ppf = new PayPeriodFactory();
		$epf = new ExceptionPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp,
							d.severity_id as severity_id,
							d.type_id as exception_policy_type_id,
							b.user_id as user_id
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
					LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
					LEFT JOIN '. $epf->getTable() .' as d ON a.exception_policy_id = d.id
					LEFT JOIN '. $ppf->getTable() .' as e ON b.pay_period_id = e.id
					where
						c.company_id = ?
						AND b.user_id = ?
						AND e.status_id in ('. $this->getListSQL( $status, $ph ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND e.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate($time_period, $user_ids, $company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
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
		$udf = new UserDateFactory();

		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$epcf = new ExceptionPolicyControlFactory();

		//Get total date units
		switch (strtolower($time_period)) {
			case 'week':
				$total_date_units = round( TTDate::getWeekDifference($start_date, $end_date) );
				Debug::Text('Total Weeks: '. $total_date_units, __FILE__, __LINE__, __METHOD__,10);
				break;
			case 'month':
				$total_date_units = ceil( TTDate::getMonthDifference($start_date, $end_date) );
				Debug::Text('Total Months: '. $total_date_units, __FILE__, __LINE__, __METHOD__,10);
				break;
		}
		//Avoid division by 0 error.
		if ( $total_date_units == 0 ) {
			$total_date_units = 1;
		}

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select 	user_id,
							exception_policy_id,
							(sum(total) / '. $total_date_units .') as avg,
							min(total) as min,
							max(total) as max,
							sum(total) as total
					from (

						select 	b.user_id,
								(EXTRACT('.$time_period.' FROM b.date_stamp) ) as date,
								a.exception_policy_id,
								CASE WHEN count(*) > 0 THEN count(*) ELSE 0 END as total
						from 	'. $udf->getTable() .' as b
						LEFT JOIN '. $this->getTable() .' as a ON a.user_date_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
						where
							c.company_id = ?
							AND b.date_stamp >= ?
							AND b.date_stamp <= ?
							AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND a.exception_policy_id is not NULL
							AND ( ( a.deleted = 0 OR a.deleted is NULL ) AND b.deleted=0 AND c.deleted=0)
							GROUP BY user_id,(EXTRACT('.$time_period.' FROM b.date_stamp) ),a.exception_policy_id
						) tmp
					GROUP BY user_id,exception_policy_id
					ORDER BY user_id,sum(total) desc
					';
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getDOWReportByUserIdAndCompanyIdAndStartDateAndEndDate($user_ids, $company_id, $start_date, $end_date, $where = NULL, $order = NULL) {
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
		$udf = new UserDateFactory();

		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$epcf = new ExceptionPolicyControlFactory();

		$ph = array(
					'company_id' => $company_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
			$dow_sql = '(dayofweek( b.date_stamp)-1)';
		} else {
			$dow_sql = '(date_part(\'DOW\', b.date_stamp))';
		}

		$query = '
						select 	b.user_id,
								'. $dow_sql .' as dow,
								a.exception_policy_id,
								CASE WHEN count(*) > 0 THEN count(*) ELSE 0 END as total
						from 	'. $udf->getTable() .' as b
						LEFT JOIN '. $this->getTable() .' as a ON a.user_date_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
						where
							c.company_id = ?
							AND b.date_stamp >= ?
							AND b.date_stamp <= ?
							AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND a.exception_policy_id is not NULL
							AND ( ( a.deleted = 0 OR a.deleted is NULL ) AND b.deleted=0 AND c.deleted=0)
							GROUP BY b.user_id,'. $dow_sql .',a.exception_policy_id
						ORDER BY b.user_id,'. $dow_sql .'
					';

/*
		$query = '
						select 	b.user_id,
								(date_part(\'DOW\', b.date_stamp)) as dow,
								a.exception_policy_id,
								CASE WHEN count(*) > 0 THEN count(*) ELSE 0 END as total
						from 	'. $udf->getTable() .' as b
						LEFT JOIN '. $this->getTable() .' as a ON a.user_date_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
						where
							c.company_id = ?
							AND b.date_stamp >= ?
							AND b.date_stamp <= ?
							AND b.user_id in ('. $this->getListSQL($user_ids, $ph) .')
							AND a.exception_policy_id is not NULL
							AND ( ( a.deleted = 0 OR a.deleted is NULL ) AND b.deleted=0 AND c.deleted=0)
							GROUP BY b.user_id,(date_part(\'DOW\', b.date_stamp)),a.exception_policy_id
						ORDER BY b.user_id,(date_part(\'DOW\', b.date_stamp))
					';
*/
		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );

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

		$additional_order_fields = array('d.name', 'e.name', 'f.name', 'g.name', 'h.status_id','i.severity_id','i.type_id','c.first_name','c.last_name', 'b.date_stamp');
		if ( $order == NULL ) {
			//$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$order = array( 'i.severity_id' => 'desc', 'c.last_name' => 'asc', 'b.date_stamp' => 'asc', 'i.type_id' => 'asc' );

			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			if ( isset($order['default_branch']) ) {
				$order['d.name'] = $order['default_branch'];
				unset($order['default_branch']);
			}
			if ( isset($order['default_department']) ) {
				$order['e.name'] = $order['default_department'];
				unset($order['default_department']);
			}
			if ( isset($order['user_group']) ) {
				$order['f.name'] = $order['user_group'];
				unset($order['user_group']);
			}
			if ( isset($order['title']) ) {
				$order['g.name'] = $order['title'];
				unset($order['title']);
			}
			if ( isset($order['exception_policy_type_id']) ) {
				$order['i.type_id'] = $order['exception_policy_type_id'];
				unset($order['exception_policy_type_id']);
			}
			if ( isset($order['severity_id']) ) {
				$order['i.severity_id'] = $order['severity_id'];
				unset($order['severity_id']);
			}
			if ( isset($order['severity']) ) {
				$order['i.severity_id'] = $order['severity'];
				unset($order['severity']);
			}
			if ( isset($order['exception_policy_type']) ) {
				$order['i.type_id'] = $order['exception_policy_type'];
				unset($order['exception_policy_type']);
			}
			if ( isset($order['exception_policy_type_id']) ) {
				$order['i.type_id'] = $order['exception_policy_type_id'];
				unset($order['exception_policy_type_id']);
			}

			if ( isset($order['first_name']) ) {
				$order['c.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['last_name']) ) {
				$order['c.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
			if ( isset($order['date_stamp']) ) {
				$order['b.date_stamp'] = $order['date_stamp'];
				unset($order['date_stamp']);
			}

			//Always sort by last name,first name after other columns
			if ( !isset($order['c.last_name']) ) {
				$order['c.last_name'] = 'asc';
			}
			if ( !isset($order['c.first_name']) ) {
				$order['c.first_name'] = 'asc';
			}
			if ( !isset($order['b.date_stamp']) ) {
				$order['b.date_stamp'] = 'asc';
			}
			if ( !isset($order['i.severity_id']) ) {
				$order['i.severity_id'] = 'desc';
			}

			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$udf = new UserDateFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$ppf = new PayPeriodFactory();
		$epf = new ExceptionPolicyFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as user_date_stamp,
							i.severity_id as severity_id,
							i.type_id as exception_policy_type_id,
							b.user_id as user_id
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
						LEFT JOIN '. $bf->getTable() .' as d ON c.default_branch_id = d.id
						LEFT JOIN '. $df->getTable() .' as e ON c.default_department_id = e.id
						LEFT JOIN '. $ugf->getTable() .' as f ON c.group_id = f.id
						LEFT JOIN '. $utf->getTable() .' as g ON c.title_id = g.id
						LEFT JOIN '. $ppf->getTable() .' as h ON b.pay_period_id = h.id
						LEFT JOIN '. $epf->getTable() .' as i ON a.exception_policy_id = i.id
					where	c.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND c.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND c.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query  .=	' AND c.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['severity_id']) AND isset($filter_data['severity_id'][0]) AND !in_array(-1, (array)$filter_data['severity_id']) ) {
			$query  .=	' AND i.severity_id in ('. $this->getListSQL($filter_data['severity_id'], $ph) .') ';
		}
		if ( isset($filter_data['exception_policy_type_id']) AND isset($filter_data['exception_policy_type_id'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_type_id']) ) {
			$query  .=	' AND i.type_id in ('. $this->getListSQL($filter_data['exception_policy_type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query  .=	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_status_id']) AND isset($filter_data['pay_period_status_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_status_id']) ) {
			$query  .=	' AND h.status_id in ('. $this->getListSQL($filter_data['pay_period_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND c.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND c.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND c.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND c.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		/*
		if ( isset($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
			$ph[] = trim($filter_data['sin']);
			$query  .=	' AND a.sin LIKE ?';
		}
		*/

		$query .= 	'
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND h.deleted = 0 )
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

        if ( isset( $filter_data['user_group_id'] ) ) {
            $filter_data['group_id'] = $filter_data['user_group_id'];
        }
        if ( isset( $filter_data['user_title_id'] ) ) {
            $filter_data['title_id'] = $filter_data['user_title_id'];
        }
        if ( isset( $filter_data['include_user_id'] ) ) {
            $filter_data['user_id'] = $filter_data['include_user_id'];
        }
        if ( isset( $filter_data['exception_policy_severity_id'] ) ) {
            $filter_data['severity_id'] = $filter_data['exception_policy_severity_id'];
        }

		$additional_order_fields = array('d.name', 'e.name', 'f.name', 'g.name', 'h.status_id','i.severity_id','i.type_id','c.first_name','c.last_name', 'c.country', 'c.province', 'b.date_stamp', 'pgf.name', 'pscf.name', 'ppsf.name');
		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'type' => 'type_id',
									 );
		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			//$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$order = array( 'i.severity_id' => 'desc', 'c.last_name' => 'asc', 'b.date_stamp' => 'asc', 'i.type_id' => 'asc' );

			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			if ( isset($order['default_branch']) ) {
				$order['d.name'] = $order['default_branch'];
				unset($order['default_branch']);
			}
			if ( isset($order['default_department']) ) {
				$order['e.name'] = $order['default_department'];
				unset($order['default_department']);
			}
			if ( isset($order['user_group']) ) {
				$order['f.name'] = $order['user_group'];
				unset($order['user_group']);
			}
			if ( isset($order['title']) ) {
				$order['g.name'] = $order['title'];
				unset($order['title']);
			}
			if ( isset($order['exception_policy_type_id']) ) {
				$order['i.type_id'] = $order['exception_policy_type_id'];
				unset($order['exception_policy_type_id']);
			}
			if ( isset($order['severity_id']) ) {
				$order['i.severity_id'] = $order['severity_id'];
				unset($order['severity_id']);
			}
			if ( isset($order['severity']) ) {
				$order['i.severity_id'] = $order['severity'];
				unset($order['severity']);
			}
			if ( isset($order['exception_policy_type']) ) {
				$order['i.type_id'] = $order['exception_policy_type'];
				unset($order['exception_policy_type']);
			}
			if ( isset($order['exception_policy_type_id']) ) {
				$order['i.type_id'] = $order['exception_policy_type_id'];
				unset($order['exception_policy_type_id']);
			}

			if ( isset($order['first_name']) ) {
				$order['c.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['last_name']) ) {
				$order['c.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
            if ( isset($order['country']) ) {
				$order['c.country'] = $order['country'];
				unset($order['country']);
			}
			if ( isset($order['province']) ) {
				$order['c.province'] = $order['province'];
				unset($order['province']);
			}
			if ( isset($order['date_stamp']) ) {
				$order['b.date_stamp'] = $order['date_stamp'];
				unset($order['date_stamp']);
			}
            if ( isset($order['policy_group']) ) {
                $order['pgf.name'] = $order['policy_group'];
                unset($order['policy_group']);
            }
            if ( isset($order['permission_group']) ) {
                $order['pscf.name'] = $order['permission_group'];
                unset($order['permission_group']);
            }
            if ( isset($order['pay_period_schedule']) ) {
                $order['ppsf.name'] = $order['pay_period_schedule'];
                unset($order['pay_period_schedule']);
            }

			//Always sort by last name,first name after other columns
			if ( !isset($order['c.last_name']) ) {
				$order['c.last_name'] = 'asc';
			}
			if ( !isset($order['c.first_name']) ) {
				$order['c.first_name'] = 'asc';
			}
			if ( !isset($order['b.date_stamp']) ) {
				$order['b.date_stamp'] = 'asc';
			}
			if ( !isset($order['i.severity_id']) ) {
				$order['i.severity_id'] = 'desc';
			}

			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$udf = new UserDateFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$ppf = new PayPeriodFactory();
        $ppsf = new PayPeriodScheduleFactory();
		$epf = new ExceptionPolicyFactory();
        $epcf = new ExceptionPolicyControlFactory();
		$pguf = new PolicyGroupUserFactory();
        $pgf = new PolicyGroupFactory();
		$pf = new PunchFactory();
		$pcf = new PunchControlFactory();
        $pscf = new PermissionControlFactory();
        $puf = new PermissionUserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.date_stamp as date_stamp,
							b.pay_period_id as pay_period_id,
							h.pay_period_schedule_id as pay_period_schedule_id,
							i.severity_id as severity_id,
							i.type_id as exception_policy_type_id,
							b.user_id as user_id,
                            h.start_date as pay_period_start_date,
							h.end_date as pay_period_end_date,
							h.transaction_date as pay_period_transaction_date,
							c.first_name as first_name,
							c.last_name as last_name,
                            c.country as country,
                            c.province as province,
							c.status_id as user_status_id,
							c.group_id as group_id,
							f.name as "group",
							c.title_id as title_id,
							g.name as title,
							c.default_branch_id as default_branch_id,
							d.name as default_branch,
							c.default_department_id as default_department_id,
							e.name as default_department,

							pcf.branch_id as branch_id,
							bf.name as branch,
							pcf.department_id as department_id,
							df.name as department,
                            pgf.name as policy_group,
                            pscf.name as permission_group,
                            ppsf.name as pay_period_schedule,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $pf->getTable() .' as pf ON ( a.punch_id IS NOT NULL AND a.punch_id = pf.id AND pf.deleted = 0)
						LEFT JOIN '. $pcf->getTable() .' as pcf ON ( ( ( pf.id IS NOT NULL AND pf.punch_control_id = pcf.id ) OR ( a.punch_control_id is NOT NULL AND a.punch_control_id = pcf.id ) ) AND pcf.deleted = 0)
						LEFT JOIN '. $bf->getTable() .' as bf ON pcf.branch_id = bf.id
						LEFT JOIN '. $df->getTable() .' as df ON pcf.department_id = df.id

						LEFT JOIN '. $udf->getTable() .' as b ON a.user_date_id = b.id
						LEFT JOIN '. $uf->getTable() .' as c ON b.user_id = c.id
						LEFT JOIN '. $bf->getTable() .' as d ON c.default_branch_id = d.id
						LEFT JOIN '. $df->getTable() .' as e ON c.default_department_id = e.id
						LEFT JOIN '. $ugf->getTable() .' as f ON c.group_id = f.id
						LEFT JOIN '. $utf->getTable() .' as g ON c.title_id = g.id
						LEFT JOIN '. $ppf->getTable() .' as h ON b.pay_period_id = h.id
                        LEFT JOIN '. $ppsf->getTable() .' as ppsf ON ppsf.id = h.pay_period_schedule_id
						LEFT JOIN '. $epf->getTable() .' as i ON a.exception_policy_id = i.id
                        LEFT JOIN '. $epcf->getTable() .' as epcf ON epcf.id = i.exception_policy_control_id
						LEFT JOIN '. $pguf->getTable() .' as pguf ON b.user_id = pguf.user_id
                        LEFT JOIN '. $pgf->getTable() .' as pgf ON pguf.policy_group_id = pgf.id
                        LEFT JOIN '. $puf->getTable() .' as puf ON c.id = puf.user_id
                        LEFT JOIN '. $pscf->getTable() .' as pscf ON pscf.id = puf.permission_control_id
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	c.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND c.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND c.id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
        $query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'c.id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['user_status_id']) AND isset($filter_data['user_status_id'][0]) AND !in_array(-1, (array)$filter_data['user_status_id']) ) {
			$query  .=	' AND c.status_id in ('. $this->getListSQL($filter_data['user_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
		}
		if ( isset($filter_data['severity_id']) AND isset($filter_data['severity_id'][0]) AND !in_array(-1, (array)$filter_data['severity_id']) ) {
			$query  .=	' AND i.severity_id in ('. $this->getListSQL($filter_data['severity_id'], $ph) .') ';
		}
		if ( isset($filter_data['exception_policy_type_id']) AND isset($filter_data['exception_policy_type_id'][0]) AND !in_array(-1, (array)$filter_data['exception_policy_type_id']) ) {
			$query  .=	' AND i.type_id in ('. $this->getListSQL($filter_data['exception_policy_type_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_id']) AND isset($filter_data['pay_period_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_id']) ) {
			$query  .=	' AND b.pay_period_id in ('. $this->getListSQL($filter_data['pay_period_id'], $ph) .') ';
		}
		if ( isset($filter_data['pay_period_status_id']) AND isset($filter_data['pay_period_status_id'][0]) AND !in_array(-1, (array)$filter_data['pay_period_status_id']) ) {
			$query  .=	' AND h.status_id in ('. $this->getListSQL($filter_data['pay_period_status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query  .=	' AND c.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query  .=	' AND c.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query  .=	' AND c.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query  .=	' AND c.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}

		if ( isset($filter_data['branch_id']) AND isset($filter_data['branch_id'][0]) AND !in_array(-1, (array)$filter_data['branch_id']) ) {
			$query  .=	' AND pcf.branch_id in ('. $this->getListSQL($filter_data['branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['department_id']) AND isset($filter_data['department_id'][0]) AND !in_array(-1, (array)$filter_data['department_id']) ) {
			$query  .=	' AND pcf.department_id in ('. $this->getListSQL($filter_data['department_id'], $ph) .') ';
		}

		if ( isset($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['start_date']);
			$query  .=	' AND b.date_stamp >= ?';
		}
		if ( isset($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $this->db->BindDate($filter_data['end_date']);
			$query  .=	' AND b.date_stamp <= ?';
		}
        
        $query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        

		//Make sure we accept exception rows assign to pay_period_id = 0 (no pay period), as this can happen when punches exist in the future.
		$query .= 	'
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 AND pgf.deleted = 0 AND ( h.deleted = 0 OR h.deleted is NULL ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
