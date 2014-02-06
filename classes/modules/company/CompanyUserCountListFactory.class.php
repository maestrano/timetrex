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
 * $Revision: 8371 $
 * $Id: CompanyUserCountListFactory.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Company
 */
class CompanyUserCountListFactory extends CompanyUserCountFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
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
						';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getActiveUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						status_id = 10
						AND deleted = 0
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getInActiveUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						status_id != 10
						AND deleted = 0
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getDeletedUsers($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$uf = new UserFactory();

		$query = '
					select 	company_id,
							count(*) as total
					from	'. $uf->getTable() .'
					where
						deleted = 1
					GROUP BY company_id
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getMinAvgMaxByCompanyIdAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where	company_id = ?
						AND date_stamp >= ?
						AND date_stamp <= ?
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	//This function returns data for multiple companies, used by the API.
	function getMinAvgMaxByCompanyIDsAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					//'company_id' => $id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select
							company_id,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ? ';

		if ( $id != '' AND ( isset($id[0]) AND !in_array(-1, (array)$id) ) ) {
			$query  .=	' AND company_id in ('. $this->getListSQL($id, $ph) .') ';
		}

		$query .= ' group by company_id';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getMonthlyMinAvgMaxByCompanyIdAndStartDateAndEndDate($id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
			//$month_sql = '(month( date_stamp ))';
			$month_sql = '( date_format( date_stamp, \'%Y-%m-01\') )';
		} else {
			//$month_sql = '( date_part(\'month\', date_stamp) )';
			$month_sql = '( to_char(date_stamp, \'YYYY-MM\') || \'-01\' )'; //Concat -01 to end due to EnterpriseDB issue with to_char
		}

		$query = '
					select
							'. $month_sql .' as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where	company_id = ?
						AND date_stamp >= ?
						AND date_stamp <= ?
					GROUP BY '. $month_sql .'
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getMonthlyMinAvgMaxByStartDateAndEndDate($start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
			//$month_sql = '(month( date_stamp ))';
			$month_sql = '( date_format( date_stamp, \'%Y-%m-01\') )';
		} else {
			//$month_sql = '( date_part(\'month\', date_stamp) )';
			$month_sql = '( to_char(date_stamp, \'YYYY-MM-01\') )';
		}

		$query = '
					select
							company_id,
							'. $month_sql .' as date_stamp,
							min(active_users) as min_active_users,
							ceil(avg(active_users)) as avg_active_users,
							max(active_users) as max_active_users,

							min(inactive_users) as min_inactive_users,
							ceil(avg(inactive_users)) as avg_inactive_users,
							max(inactive_users) as max_inactive_users,

							min(deleted_users) as min_deleted_users,
							ceil(avg(deleted_users)) as avg_deleted_users,
							max(deleted_users) as max_deleted_users

					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
					GROUP BY company_id,'. $month_sql .'
					ORDER BY company_id,'. $month_sql .'
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	//This gets the totals across all companies.
	function getTotalMonthlyMinAvgMaxByCompanyStatusAndStartDateAndEndDate($status_id, $start_date, $end_date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		$cf = TTNew('CompanyFactory');

		$ph = array(
					'status_id' => $status_id,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		if ( strncmp($this->db->databaseType,'mysql',5) == 0 ) {
			//$month_sql = '(month( date_stamp ))';
			$month_sql = '( date_format( a.date_stamp, \'%Y-%m-01\') )';
		} else {
			//$month_sql = '( date_part(\'month\', date_stamp) )';
			$month_sql = '( to_char(a.date_stamp, \'YYYY-MM-01\') )';
		}

		$query = '
					select
							date_stamp,
							sum(min_active_users) as min_active_users,
							sum(avg_active_users) as avg_active_users,
							sum(max_active_users) as max_active_users,

							sum(min_inactive_users) as min_inactive_users,
							sum(avg_inactive_users) as avg_inactive_users,
							sum(max_inactive_users) as max_inactive_users,

							sum(min_deleted_users) as min_deleted_users,
							sum(avg_deleted_users) as avg_deleted_users,
							sum(max_deleted_users) as max_deleted_users
					FROM (
							select
									company_id,
									'. $month_sql .' as date_stamp,
									min(a.active_users) as min_active_users,
									ceil(avg(a.active_users)) as avg_active_users,
									max(a.active_users) as max_active_users,

									min(a.inactive_users) as min_inactive_users,
									ceil(avg(a.inactive_users)) as avg_inactive_users,
									max(a.inactive_users) as max_inactive_users,

									min(a.deleted_users) as min_deleted_users,
									ceil(avg(a.deleted_users)) as avg_deleted_users,
									max(a.deleted_users) as max_deleted_users

							from	'. $this->getTable() .' as a
								LEFT JOIN '. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
							where
								cf.status_id = ?
								AND a.date_stamp >= ?
								AND a.date_stamp <= ?
								AND ( cf.deleted = 0 )
							GROUP BY company_id,'. $month_sql .'
						) as tmp
					GROUP BY date_stamp
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getLastDateByCompanyId($company_id, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
					ORDER BY date_stamp desc
					LIMIT 1
						';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $order = NULL) {
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
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	id = ?
						';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}


}
?>
