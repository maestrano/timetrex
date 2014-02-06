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
 * $Id: OtherFieldListFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Core
 */
class OtherFieldListFactory extends OtherFieldFactory implements IteratorAggregate {

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

	function getByCompanyId($id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndTypeID($id, $type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $type_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => (int)$id,
					//'type_id' => (int)$type_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND type_id in ('. $this->getListSQL($type_id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

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
					select 	a.*
					from 	'. $this->getTable() .' as a
					where
						a.company_id = ?
						AND	a.id = ?
						AND a.deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndTypeIDArray($id, $type_id, $key_prefix = NULL, $name_prefix = NULL ) {
		$oflf = new OtherFieldListFactory();
		$oflf->getByCompanyIdAndTypeID( $id, $type_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL );
		if ( $oflf->getRecordCount() > 0 ) {
			foreach($oflf as $obj) {
				if ( is_array($key_prefix) ) {
					if ( isset($key_prefix[$obj->getType()]) ) {
						$prefix = $key_prefix[$obj->getType()];
					} else {
						$prefix = NULL;
					}
				} else {
					$prefix = $key_prefix;
				}

				if ( is_array($name_prefix) ) {
					if ( isset($name_prefix[$obj->getType()]) ) {
						$prefix2 = $name_prefix[$obj->getType()];
					} else {
						$prefix2 = NULL;
					}
				} else {
					$prefix2 = $name_prefix;
				}

				if ( $obj->getOtherID1() != '' ) {
					$retarr[$prefix.'other_id1'] = $prefix2.$obj->getOtherID1();
				}
				if ( $obj->getOtherID2() != '' ) {
					$retarr[$prefix.'other_id2'] = $prefix2.$obj->getOtherID2();
				}
				if ( $obj->getOtherID3() != '' ) {
					$retarr[$prefix.'other_id3'] = $prefix2.$obj->getOtherID3();
				}
				if ( $obj->getOtherID4() != '' ) {
					$retarr[$prefix.'other_id4'] = $prefix2.$obj->getOtherID4();
				}
				if ( $obj->getOtherID5() != '' ) {
					$retarr[$prefix.'other_id5'] = $prefix2.$obj->getOtherID5();
				}
				if ( $obj->getOtherID6() != '' ) {
					$retarr[$prefix.'other_id6'] = $prefix2.$obj->getOtherID6();
				}
				if ( $obj->getOtherID7() != '' ) {
					$retarr[$prefix.'other_id7'] = $prefix2.$obj->getOtherID7();
				}
				if ( $obj->getOtherID8() != '' ) {
					$retarr[$prefix.'other_id8'] = $prefix2.$obj->getOtherID8();
				}
				if ( $obj->getOtherID9() != '' ) {
					$retarr[$prefix.'other_id9'] = $prefix2.$obj->getOtherID9();
				}
				if ( $obj->getOtherID10() != '' ) {
					$retarr[$prefix.'other_id10'] = $prefix2.$obj->getOtherID10();
				}
			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}

	function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select 	*
					from	'. $this->getTable() .'
					where
							company_id = ?
						AND
							( created_date >= ? OR updated_date >= ? )
					LIMIT 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('Rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);

			return TRUE;
		}
		Debug::text('Rows have NOT been modified', __FILE__, __LINE__, __METHOD__,10);
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

		$additional_order_fields = array('type_id');

		$sort_column_aliases = array(
									 'type' => 'type_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'type_id' => 'asc' );
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['type_id']) ) {
				$order = Misc::prependArray( array('type_id' => 'asc'), $order );
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
					where	a.company_id = ?';
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query  .=	' AND a.created_by in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query  .=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query  .=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['type_id']) AND isset($filter_data['type_id'][0]) AND !in_array(-1, (array)$filter_data['type_id']) ) {
			$query  .=	' AND a.type_id in ('. $this->getListSQL($filter_data['type_id'], $ph) .') ';
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
