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
class LogListFactory extends LogFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
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

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
					';
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

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND ( b.deleted = 0 )';
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
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $uf->getTable() .' as b on a.user_id = b.id
					where	b.company_id = ?
						AND ( b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getLastEntryByUserIdAndActionAndTable($user_id, $action, $table_name) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $action == '') {
			return FALSE;
		}

		if ( $table_name == '') {
			return FALSE;
		}

		$action_key = Option::getByValue($action, $this->getOptions('action') );
		if ($action_key !== FALSE) {
			$action = $action_key;
		}

		$ph = array(
					'user_id' => $user_id,
					'table_name' => $table_name,
					'action_id' => $action,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND table_name = ?
						AND action_id = ?
					ORDER BY date desc
					LIMIT 1
					';
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

		$additional_order_fields = array('b.last_name');
		if ( $order == NULL ) {
			$order = array( 'date' => 'desc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);


		if ( isset($filter_data['user_ids']) ) {
			$filter_data['user_id'] = $filter_data['user_ids'];
		}
		if ( isset($filter_data['log_action_ids']) ) {
			$filter_data['log_action_id'] = $filter_data['log_action_ids'];
		}
		if ( isset($filter_data['log_table_name_ids']) ) {
			$filter_data['log_table_name_id'] = $filter_data['log_table_name_ids'];
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
					b.first_name as first_name,
					b.middle_name as middle_name,
					b.last_name as last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where	b.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query	.=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_user_id']) AND isset($filter_data['exclude_user_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_user_id']) ) {
			$query	.=	' AND a.user_id not in ('. $this->getListSQL($filter_data['exclude_user_id'], $ph) .') ';
		}		
		if ( isset($filter_data['log_action_id']) AND isset($filter_data['log_action_id'][0]) AND !in_array(-1, (array)$filter_data['log_action_id']) ) {
			$query	.=	' AND a.action_id in ('. $this->getListSQL($filter_data['log_action_id'], $ph) .') ';
		}
		if ( isset($filter_data['log_table_name_id']) AND isset($filter_data['log_table_name_id'][0]) AND !in_array(-1, (array)$filter_data['log_table_name_id']) ) {
			$query	.=	' AND a.table_name in ('. $this->getListSQL($filter_data['log_table_name_id'], $ph) .') ';
		}
		if ( isset($filter_data['start_date']) AND !is_array($filter_data['start_date']) AND trim($filter_data['start_date']) != '' ) {
			$ph[] = $filter_data['start_date'];
			$query	.=	' AND a.date >= ?';
		}
		if ( isset($filter_data['end_date']) AND !is_array($filter_data['end_date']) AND trim($filter_data['end_date']) != '' ) {
			$ph[] = $filter_data['end_date'];
			$query	.=	' AND a.date <= ?';
		}

		$query .= ' AND ( b.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByPhonePunchDataByCompanyIdAndStartDateAndEndDate($company_id, $start_date, $end_date ) { //$where = NULL, $order = NULL
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '') {
			return FALSE;
		}

		if ( $end_date == '') {
			return FALSE;
		}

		$ph = array(
					//'company_id' => $company_id,
					'start_date' => $start_date,
					'end_date' => $end_date,
					);

		$query = 'select	m.*,
							CASE WHEN m.calls > m.minutes THEN m.calls ELSE m.minutes END as billable_units
							from (
								select	company_id,
										product,
										sum(seconds)/60 as minutes,
										count(*) as calls,
										count(distinct(user_id)) as unique_users
								from
										(	select	company_id,
													user_id,
													CASE WHEN seconds < 60 THEN 60 ELSE seconds END as seconds,
													product from
													(	select	a.id,
																b.company_id,
																a.user_id,
																a.description,
																array_to_string( regexp_matches(a.description, \'([0-9]{1,3})s$\',\'i\'),\'\')::int as seconds,
																CASE WHEN a.description ILIKE \'%Destination: unknown%\' THEN \'local\' ELSE \'tollfree\' END as product
														from system_log as a
															LEFT JOIN users as b ON a.user_id = b.id
														where a.table_name = \'punch\'
															AND ( a.description ILIKE \'Telephone Punch End%\' )
															AND (a.date >= ? AND a.date < ? ) ';

															if ( $company_id != '' AND ( isset($company_id[0]) AND !in_array(-1, (array)$company_id) ) ) {
																$query	.=	' AND company_id in ('. $this->getListSQL($company_id, $ph) .') ';
															}

		$query .= '									) as tmp
										) as tmp2
								group by company_id, product ) as m
							LEFT JOIN company as n ON m.company_id = n.id
							order by product, name;
					';

		//$query .= $this->getWhereSQL( $where );
		//$query .= $this->getSortSQL( $order );
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

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

		$additional_order_fields = array('action_id', 'object_id', 'last_name', 'first_name');

		$sort_column_aliases = array(
									'action' => 'action_id',
									'object' => 'table_name',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'date' => 'desc', 'table_name' => 'asc', 'object_id' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['date']) ) {
				$order['date'] = 'desc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							uf.first_name as first_name,
							uf.middle_name as middle_name,
							uf.last_name as last_name

					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					where	uf.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['action']) AND !is_array($filter_data['action']) AND trim($filter_data['action']) != '' AND !isset($filter_data['action_id']) ) {
			$filter_data['action_id'] = Option::getByFuzzyValue( $filter_data['action'], $this->getOptions('action') );
		}
		$query .= ( isset($filter_data['action_id']) ) ? $this->getWhereClauseSQL( 'a.action_id', $filter_data['action_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['object_id']) ) ? $this->getWhereClauseSQL( 'a.object_id', $filter_data['object_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['table_name']) ) ? $this->getWhereClauseSQL( 'a.table_name', $filter_data['table_name'], 'text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['date']) ) ? $this->getWhereClauseSQL( 'a.date', $filter_data['date'], 'date_range', $ph ) : NULL;

		if ( isset($filter_data['first_name']) AND !is_array($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
			$ph[] = $this->handleSQLSyntax(strtolower(trim($filter_data['first_name'])));
			$query	.=	' AND (lower(uf.first_name) LIKE ? ) ';
		}
		if ( isset($filter_data['last_name']) AND !is_array($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
			$ph[] = $this->handleSQLSyntax(strtolower(trim($filter_data['last_name'])));
			$query	.=	' AND (lower(uf.last_name) LIKE ? ) ';
		}

		//Need to support table_name -> object_id pairs for including log entires from different tables/objects.
		if ( isset( $filter_data['table_name_object_id'] ) AND is_array($filter_data['table_name_object_id']) AND count($filter_data['table_name_object_id']) > 0 ) {
			foreach( $filter_data['table_name_object_id'] as $table_name => $object_id ) {
				$ph[] = strtolower(trim($table_name));
				$sub_query[] =	'(a.table_name = ? AND a.object_id in ('. $this->getListSQL($object_id, $ph, 'int') .') )';
			}

			if ( isset($sub_query) ) {
				$query .= ' AND ( '. implode(' OR ', $sub_query ) .' ) ';
			}
			unset($table_name, $object_id, $sub_query);
		}

		$query .= ( isset($filter_data['description']) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : NULL;

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
