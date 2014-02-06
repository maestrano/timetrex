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
 * $Revision: 9521 $
 * $Id: UserGenericDataListFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Users
 */
class UserGenericDataListFactory extends UserGenericDataFactory implements IteratorAggregate {

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

	function getByUserId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndId($user_id, $id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndUserIdAndId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND id = ? ';

		//Allow getting company wide data if user_id == ''
		if ( $user_id != '' ) {
			$ph[] = $user_id;
			$query .= '		AND user_id = ?';
		} else {
			$query .= '		AND ( user_id = 0 OR user_id IS NULL )';
		}

		$query .= ' AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndScript($user_id, $script, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'is_default' => 'desc', 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'script' => $script,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND script = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndScriptAndDefault($user_id, $script, $default = TRUE, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'user_id' => $user_id,
					'script' => $script,
					'default' => $this->toBool($default),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND script = ?
						AND is_default = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserIdAndScriptArray($user_id, $script, $include_blank = TRUE) {

		$ugdlf = new UserGenericDataListFactory();
		$ugdlf->getByUserIdAndScript($user_id, $script);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($ugdlf as $ugd_obj) {
			if ( $ugd_obj->getDefault() == TRUE ) {
				$default = ' (Default)';
			} else {
				$default = NULL;
			}
			$list[$ugd_obj->getID()] = $ugd_obj->getName().$default;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	/*

		Company List Functions

	*/
	function getByCompanyId($id, $where = NULL, $order = NULL) {
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
						AND ( user_id = 0 OR user_id is NULL )
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndId($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND ( user_id = 0 OR user_id is NULL )
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndScript($company_id, $script, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					'script' => $script
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND ( user_id = 0 OR user_id is NULL )
						AND script = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndScriptAndDefault($company_id, $script, $default = TRUE, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $script == '') {
			return FALSE;
		} else {
			$script = self::handleScriptName( $script );
		}

		if ( $order == NULL ) {
			$order = array( 'updated_date' => 'desc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					'script' => $script,
					'default' =>  $this->toBool($default)
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND ( user_id = 0 OR user_id is NULL )
						AND script = ?
						AND is_default = ?
						AND deleted = 0';
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

		$additional_order_fields = array();
		if ( $order == NULL ) {
			$order = array( 'a.name' => 'asc' ); //Default to sort by name for saved reports and such.
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);
        $uf = new UserFactory();
		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*
					from 	'. $this->getTable() .' as a
                        LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND a.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		} else {
			$query  .=	' AND ( a.user_id = 0 OR a.user_id is NULL ) ';
		}
		if ( isset($filter_data['script']) AND isset($filter_data['script'][0]) AND !in_array(-1, (array)$filter_data['script']) ) {
			$query  .=	' AND a.script in ('. $this->getListSQL($filter_data['script'], $ph) .') ';
		}
		if ( isset($filter_data['name']) AND isset($filter_data['name'][0]) AND !in_array(-1, (array)$filter_data['name']) ) {
			$query  .=	' AND lower( a.name ) in ('. $this->getListSQL( array_map('strtolower', (array)$filter_data['name']), $ph) .') ';
		}

		if ( isset($filter_data['is_default']) ) {
			$ph[] = $this->toBool($filter_data['is_default']);
			$query  .=	' AND a.is_default = ? ';
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

	function getArrayByListFactory($lf, $include_blank = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getByCompanyIdAndScriptArray($company_id, $script, $include_blank = TRUE) {

		$ugdlf = new UserGenericDataListFactory();
		$ugdlf->getByUserIdAndScript($company_id, $script);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($ugdlf as $ugd_obj) {
			if ( $ugd_obj->getDefault() == TRUE ) {
				$default = ' (Default)';
			} else {
				$default = NULL;
			}
			$list[$ugd_obj->getID()] = $ugd_obj->getName().$default;
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
}
?>
