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
 * @package Modules\PayStub
 */
class PayStubEntryAccountListFactory extends PayStubEntryAccountFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( is_array($id) ) {
			$this->rs = FALSE;
		} else {
			$this->rs = $this->getCache($id);
		}

		if ( $this->rs === FALSE ) {
			$ph = array();

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id in ('. $this->getListSQL($id, $ph) .')
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			if ( !is_array($id) ) {
				$this->saveCache($this->rs, $id);
			}
		}

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0
					ORDER BY ps_order ASC';
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

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND id = ?
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndAccrualId($company_id, $accrual_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $accrual_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'accrual_id' => $accrual_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND accrual_pay_stub_entry_account_id = ?
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndStatusId($company_id, $status_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND status_id in ('. $this->getListSQL($status_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeId($company_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeAndFuzzyName($company_id, $type_id, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'name' => $name,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND lower(name) LIKE lower(?)
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByTypeId($type_id, $where = NULL, $order = NULL) {
		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getHighestOrderByCompanyIdAndTypeId($company_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'company_id2' => $company_id,
					'type_id' => $type_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND id = (
								select id
									from '. $this->getTable() .'
									where company_id = ?
										AND type_id = ?
										AND deleted = 0
									ORDER BY ps_order DESC
									LIMIT 1
						)
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndStatusIdAndTypeId($company_id, $status_id, $type_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $type_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND status_id in ('. $this->getListSQL($status_id, $ph) .')
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0
					ORDER BY ps_order ASC';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function isInUseById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$pself = new PayStubEntryListFactory();
		$psalf = new PayStubAmendmentListFactory();

		$ph = array(
					'pay_stub_account_id' => $id,
					);

		$query = '
					select	a.id
					from	'. $pself->getTable() .' as a
					where	a.pay_stub_entry_name_id = ? AND a.deleted = 0
					UNION ALL
					select	a.id
					from	'. $psalf->getTable() .' as a
					where	a.pay_stub_entry_name_id = ? AND a.deleted = 0
					LIMIT 1';

		$id = $this->db->GetOne($query, $ph);

		if ( $id === FALSE ) {
			return FALSE;
		}

		return TRUE;
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

		$additional_order_fields = array(
										'type_id'
										);

		$sort_column_aliases = array(
									'type' => 'type_id',
									'status' => 'status_id',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'a.status_id' => 'asc', 'a.type_id' => 'asc', 'a.ps_order' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE records go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('a.status_id' => 'asc'), $order );
			}

			//Always sort by type, ps_order after other columns
			if ( !isset($order['type_id']) ) {
				$order['a.type_id'] = 'asc';
			}

			if ( !isset($order['ps_order']) ) {
				$order['ps_order'] = 'asc';
			}

			$strict = TRUE;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$pcf = new PayCodeFactory();
		$cdf = new CompanyDeductionFactory();
		$cdpseaf = new CompanyDeductionPayStubEntryAccountFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							_ADODB_COUNT
							(
								CASE WHEN a.type_id = 40 THEN 1
								ELSE
									CASE WHEN EXISTS
										( select 1 from '. $pcf->getTable() .' as x where x.pay_stub_entry_account_id = a.id and x.deleted = 0)
									THEN 1
									ELSE
										CASE WHEN EXISTS
											( select 1 from '. $cdf->getTable() .' as x where x.pay_stub_entry_account_id = a.id and x.deleted = 0)
										THEN 1
										ELSE
											CASE WHEN EXISTS
												( select 1 from '. $cdpseaf->getTable() .' as x where x.pay_stub_entry_account_id = a.id)
											THEN 1
											ELSE 0
											END
										END
									END
								END
							) as in_use,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							_ADODB_COUNT
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND !is_array($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['type']) AND !is_array($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['type_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions('type') );
		}

		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE, $abbreviate_type = TRUE, $include_type = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		$list = array();


		$type_options  = $this->getOptions('type');
		if ( $include_type != FALSE AND $abbreviate_type == TRUE ) {
			foreach( $type_options as $key => $val ) {
				$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction'), array('EE', 'ER', 'Ded'), $val);
			}
			unset($key, $val);
		}

		foreach ($lf as $obj) {
			if ( $include_type == FALSE ) {
				$list[$obj->getID()] = $obj->getName();
			} else {
				$list[$obj->getID()] = $type_options[$obj->getType()] .' - '. $obj->getName();
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getByIdArray($id, $include_blank = TRUE) {
		if ( $id == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getById($id);

		if ( $include_blank == TRUE ) {
			$entry_name_list[0] = '--';
		}

		$type_options  = $this->getOptions('type');

		foreach ($psealf as $entry_name) {
			$entry_name_list[$entry_name->getID()] = $type_options[$entry_name->getType()] .' - '. $entry_name->getName();
		}

		return $entry_name_list;
	}

	function getByCompanyIdAndStatusIdAndTypeIdArray($company_id, $status_id, $type_id, $include_blank = TRUE, $abbreviate_type = TRUE ) {
		if ( $type_id == '') {
			return FALSE;
		}

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyIdAndStatusIdAndTypeId( $company_id, $status_id, $type_id );
		//$psenlf->getByTypeId($type_id);

		$entry_name_list = array();

		if ( $include_blank == TRUE ) {
			$entry_name_list[0] = '--';
		}

		$type_options  = $this->getOptions('type');
		if ( $abbreviate_type == TRUE ) {
			foreach( $type_options as $key => $val ) {
				$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction'), array('EE', 'ER', 'Ded'), $val);
			}
			unset($key, $val);
		}

		foreach ($psealf as $entry_name) {
			$entry_name_list[$entry_name->getID()] = $type_options[$entry_name->getType()] .' - '. $entry_name->getName();
		}

		return $entry_name_list;
	}

	function getByTypeArrayByCompanyIdAndStatusId($company_id, $status_id) {

		$psealf = new PayStubEntryAccountListFactory();
		$psealf->getByCompanyIdAndStatusId( $company_id, $status_id);

		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $company_id );
		if ( $pseallf->getRecordCount() == 0 ) {
			return FALSE;
		}

		$psea_type_map = $pseallf->getCurrent()->getPayStubEntryAccountIDToTypeIDMap();

		if ( $psealf->getRecordCount() > 0 ) {
			foreach ($psealf as $psea_obj) {
				$entry_name_list[$psea_obj->getType()][] = $psea_obj->getId();
			}

			foreach( $entry_name_list[40] as $key => $entry_name_id ) {
				if ( isset($psea_type_map[$entry_name_id]) ) {
					$tmp_entry_name_list[$entry_name_id] = $entry_name_list[$psea_type_map[$entry_name_id]];
				}
			}

			return $tmp_entry_name_list;
		}

		return FALSE;
	}

}
?>
