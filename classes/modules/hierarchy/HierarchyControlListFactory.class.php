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
 * $Id: HierarchyControlListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Hierarchy
 */
class HierarchyControlListFactory extends HierarchyControlFactory implements IteratorAggregate {

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
		if ( $id == '' ) {
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

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'company_id' => $company_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('name' => 'asc');
			$strict_order = FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $object_sorted_array = FALSE, $include_name = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $object_sorted_array == FALSE AND $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($lf as $obj) {
			if ( $object_sorted_array == TRUE ) {
				if ( $include_blank == TRUE AND !isset($list[$obj->getColumn('object_type_id')][0]) ) {
					$list[$obj->getColumn('object_type_id')][0] = '--';
				}

				if ( $include_name == TRUE ) {
					$list[$obj->getColumn('object_type_id')][$obj->getID()] = $obj->getName();
				} else {
					$list[$obj->getColumn('object_type_id')] = $obj->getID();
				}
			} else {
				$list[$obj->getID()] = $obj->getName();
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getObjectTypeAppendedListByCompanyID($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc', 'description' => 'asc');
			$strict = FALSE;
		} else {
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}

		$hotf = new HierarchyObjectTypeFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							b.object_type_id
					from '. $this->getTable() .' as a
					LEFT JOIN '. $hotf->getTable() .' as b ON a.id = b.hierarchy_control_id
					where 	a.company_id = ?
							AND a.deleted = 0
				';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getObjectTypeAppendedListByCompanyIDAndUserID($company_id, $user_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$hotf = new HierarchyObjectTypeFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$query = '
					select 	a.*,
							b.object_type_id
					from '. $this->getTable() .' as a
					LEFT JOIN '. $hotf->getTable() .' as b ON a.id = b.hierarchy_control_id
					LEFT JOIN '. $huf->getTable() .' as c ON a.id = c.hierarchy_control_id
					where 	a.company_id = ?
							AND c.user_id = ?
							AND a.deleted = 0
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

		$additional_order_fields = array( 'superiors', 'subordinates');

		$sort_column_aliases = array();

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc', 'description' => 'asc');
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
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		//Count total users in HierarchyControlFactory factory, so we can disable it when needed. That way it doesn't slow down Hierarchy dropdown boxes.
		//(select count(*) from '. $hlf->getTable().' as hlf WHERE a.id = hlf.hierarchy_control_id AND hlf.deleted = 0 AND a.deleted = 0) as superiors,
		//(select count(*) from '. $huf->getTable().' as hulf WHERE a.id = hulf.hierarchy_control_id AND a.deleted = 0 ) as subordinates,
		$query = '
					select 	distinct a.*,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $hlf->getTable() .' as hlf ON ( a.id = hlf.hierarchy_control_id AND hlf.deleted = 0 )
						LEFT JOIN '. $huf->getTable() .' as huf ON ( a.id = huf.hierarchy_control_id )
						LEFT JOIN '. $hotf->getTable() .' as hotf ON ( a.id = hotf.hierarchy_control_id )
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
		if ( isset($filter_data['name']) AND trim($filter_data['name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['name']));
			$query  .=	' AND lower(a.name) LIKE ?';
		}
		if ( isset($filter_data['description']) AND trim($filter_data['description']) != '' ) {
			$ph[] = strtolower(trim($filter_data['description']));
			$query  .=	' AND lower(a.description) LIKE ?';
		}

		if ( isset($filter_data['object_type']) AND isset($filter_data['object_type'][0]) AND !in_array(-1, (array)$filter_data['object_type']) ) {
			$query  .=	' AND hotf.object_type_id in ('. $this->getListSQL($filter_data['object_type'], $ph) .') ';
		}
		if ( isset($filter_data['superior_user_id']) AND isset($filter_data['superior_user_id'][0]) AND !in_array(-1, (array)$filter_data['superior_user_id']) ) {
			$query  .=	' AND hlf.user_id in ('. $this->getListSQL($filter_data['superior_user_id'], $ph) .') ';
		}
		if ( isset($filter_data['user_id']) AND isset($filter_data['user_id'][0]) AND !in_array(-1, (array)$filter_data['user_id']) ) {
			$query  .=	' AND huf.user_id in ('. $this->getListSQL($filter_data['user_id'], $ph) .') ';
		}


		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        

		//Don't filter hlf.deleted=0 here as that will not shown hierarchies without any superiors assigned to them. Do the filter on the JOIN instead.
		$query .= 	'
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}
}
?>
