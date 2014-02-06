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
 * $Revision: 10749 $
 * $Id: CurrencyListFactory.class.php 10749 2013-08-26 22:00:42Z ipso $
 * $Date: 2013-08-26 15:00:42 -0700 (Mon, 26 Aug 2013) $
 */

/**
 * @package Core
 */
class CurrencyListFactory extends CurrencyFactory implements IteratorAggregate {

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

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
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

			$this->saveCache($this->rs,$id);
		}

		return $this;
	}

	function getUniqueISOCodeByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	distinct iso_code
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		return $this->db->GetCol( $query, $ph );
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'is_base' => 'desc', 'is_default' => 'desc', 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

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
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndISOCode($company_id, $iso_code, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $iso_code == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'iso_code' => trim($iso_code),
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	iso_code = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndBase($company_id, $is_base, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $is_base == '' ) {
			return FALSE;
		}

		$this->rs = $this->getCache( $company_id.$is_base );
		if ( $this->rs === FALSE ) {
			$ph = array(
						'company_id' => $company_id,
						'is_base' => $is_base,
						);

			$query = '
						select 	*
						from 	'. $this->getTable() .'
						where	company_id = ?
							AND	is_base = ?
							AND deleted = 0';
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs,$company_id.$is_base);
		}

		return $this;
	}

	function getByCompanyIdAndDefault($company_id, $is_default, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $is_default == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'is_default' => $is_default,
					);

		$query = '
					select 	*
					from 	'. $this->getTable() .'
					where	company_id = ?
						AND	is_default = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			if ( $obj->getStatus() == 20 ) {
				$status = '(DISABLED) ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $obj->getStatus() == 10 ) ) {
				$list[$obj->getID()] = $status.$obj->getName();
			}
		}

		if ( isset($list) ) {
			return $list;
		}

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

		$additional_order_fields = array();
		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

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
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
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
		if ( isset($filter_data['iso_code']) AND isset($filter_data['iso_code'][0]) AND !in_array(-1, (array)$filter_data['iso_code']) ) {			
            $query  .=	' AND a.iso_code in ('. $this->getListSQL($filter_data['iso_code'], $ph) .') ';
		}
        if ( isset($filter_data['iso_code']) AND !is_array( $filter_data['iso_code'] ) AND trim($filter_data['iso_code']) != '' AND !is_array($filter_data['iso_code']) ) {
			$ph[] = strtolower(trim($filter_data['iso_code']));
			$query  .=	' AND lower(a.iso_code) LIKE ?';
		}

		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE ?';
		}
        
        if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		//Returns the default currency of a specific user.
		if ( isset($filter_data['user_id']) AND trim($filter_data['user_id']) != '' ) {
			$ph[] = (int)$filter_data['user_id'];
			$query  .=	' AND a.id = (select currency_id from '. $uf->getTable() .' as uf_tmp where uf_tmp.id = ? )';
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
