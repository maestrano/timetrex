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
 * @package Modules\Other
 */
class ROEListFactory extends ROEFactory implements IteratorAggregate {

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

		$ph = array();

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id in ('. $this->getListSQL($id, $ph) .')
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

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND a.deleted = 0 AND b.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND b.company_id = ?
						AND a.id in ('. $this->getListSQL($id, $ph) .')
						AND a.deleted = 0 AND b.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getLastROEByUserId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'last_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		if ( $limit == NULL ) {
			$limit = 1;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByUserIdAndStartDateAndEndDate($id, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}


		$ph = array(
					'id' => $id,
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND last_date >= ?
						AND last_date <= ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getIsModifiedByUserIdAndStartDateAndEndDateAndDate($user_id, $start_date, $end_date, $date, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	'. $this->getTable() .'
					where
							user_id = ?
						AND last_date >= ?
						AND last_date <= ?
						AND
							( created_date >= ? OR updated_date >= ? )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('ROE rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}
		Debug::text('ROE rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}
		if ( isset($filter_data['roe_id']) ) {
			$filter_data['id'] = $filter_data['roe_id'];
			unset( $filter_data['roe_id'] );
		}
		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('uf.first_name', 'uf.last_name', 'code_id', 'pay_period_type_id');

		$sort_column_aliases = array(
									'code' => 'code_id',
									'pay_period_type' => 'pay_period_type_id',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'last_date' => 'desc'); //Order ROEs by last date for which paid.
			$strict = FALSE;
		} else {
			if ( isset($order['first_name']) ) {
				$order['uf.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['last_name']) ) {
				$order['uf.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		//$ppsf = new PayPeriodScheduleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							uf.first_name as first_name,
							uf.last_name as last_name,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '.$uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	uf.company_id = ?';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['code']) AND !is_array($filter_data['code']) AND trim($filter_data['code']) != '' AND !isset($filter_data['code_id']) ) {
			$filter_data['code_id'] = Option::getByFuzzyValue( $filter_data['code'], $this->getOptions('code') );
		}
		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'uf.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'uf.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : NULL;

		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['code_id']) ) ? $this->getWhereClauseSQL( 'a.code_id', $filter_data['code_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_type_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_type_id', $filter_data['pay_period_type_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['first_date']) ) ? $this->getWhereClauseSQL( 'a.first_date', $filter_data['first_date'], 'date_range', $ph ) : NULL;

		$query .= ( isset($filter_data['last_date']) ) ? $this->getWhereClauseSQL( 'a.last_date', $filter_data['last_date'], 'date_range', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_end_date']) ) ? $this->getWhereClauseSQL( 'a.pay_period_end_date', $filter_data['pay_period_end_date'], 'date_range', $ph ) : NULL;
		$query .= ( isset($filter_data['recall_date']) ) ? $this->getWhereClauseSQL( 'a.recall_date', $filter_data['recall_date'], 'date_range', $ph ) : NULL;

		$query .= ( isset($filter_data['serial']) ) ? $this->getWhereClauseSQL( 'a.serial', $filter_data['serial'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['comments']) ) ? $this->getWhereClauseSQL( 'a.comments', $filter_data['comments'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
