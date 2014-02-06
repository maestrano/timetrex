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
 * $Revision: 5335 $
 * $Id: KPIListFactory.class.php 5335 2011-10-18 00:32:47Z ipso $
 * $Date: 2011-10-18 08:32:47 +0800 (Tue, 18 Oct 2011) $
 */

/**
 * @package Module_KPI
 */
class KPIListFactory extends KPIFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query,NULL,$limit,$page);

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

		$this->ExecuteSQL($query,$ph);

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
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = ?
						AND company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query,$ph);

		return $this;
	}


	function getByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query,$ph);
	}

    function getByCompanyIDAndGroupID($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

        $cgmf = new CompanyGenericMapFactory();

		$ph = array(
					'company_id' => $company_id,
					'map_id' => $id,
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
                    LEFT JOIN '.$cgmf->getTable() .' as b ON ( a.id = b.object_id AND b.company_id = a.company_id AND b.object_type_id = 2020 )
					where 	a.company_id = ?
						AND b.map_id = ?
						AND deleted = 0';
        $query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );
        $this->ExecuteSQL($query,$ph);

        return $this;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

        if ( isset($filter_data['kpi_id']) ) {
            $filter_data['id'] = $filter_data['kpi_id'];
        }

        if ( isset($filter_data['kpi_status_id']) ) {
			$filter_data['status_id'] = $filter_data['kpi_status_id'];
		}
        if ( isset($filter_data['kpi_type_id']) ) {
            $filter_data['type_id'] = $filter_data['kpi_type_id'];
        }
        if ( isset($filter_data['kpi_group_id']) ) {
            $filter_data['group_id'] = $filter_data['kpi_group_id'];
        }

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('type_id','status_id');

        $sort_column_aliases = array(
                                        'type' => 'type_id',
                                        'status' => 'status_id',
                                    );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
		    $order = array( 'name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);
        $uf = new UserFactory();
		$cgmf = new CompanyGenericMapFactory();
        //$kgf = new KPIGroupFactory();
		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select DISTINCT a.*,
                    y.first_name as created_by_first_name,
					y.middle_name as created_by_middle_name,
					y.last_name as created_by_last_name,
					z.first_name as updated_by_first_name,
					z.middle_name as updated_by_middle_name,
					z.last_name as updated_by_last_name
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $cgmf->getTable() .' as b ON ( a.id = b.object_id AND b.company_id = a.company_id AND b.object_type_id = 2020 )
                        LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where a.company_id = ?
					';
        $query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
        $query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text', $ph ) : NULL;
        $query .= ( isset($filter_data['description']) ) ? $this->getWhereClauseSQL( 'a.description', $filter_data['description'], 'text', $ph ) : NULL;
        $query .= ( isset($filter_data['minimum_rate']) ) ? $this->getWhereClauseSQL( 'a.minimum_rate', $filter_data['minimum_rate'], 'numeric', $ph ) : NULL;
        $query .= ( isset($filter_data['maximum_rate']) ) ? $this->getWhereClauseSQL( 'a.maximum_rate', $filter_data['maximum_rate'], 'numeric', $ph ) : NULL;

        if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
        if ( isset($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['type_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions('type') );
		}
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

        if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) ) {
			$query  .=	' AND b.map_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
        //$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'b.map_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;

        $query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

        $query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => $company_id, 'object_type_id' => 310, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;


        //$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['created_by'], 'numeric_list', $ph ) : NULL;

        //$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( 'a.updated_by', $filter_data['updated_by'], 'numeric_list', $ph ) : NULL;

        if ( isset($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query  .=	' AND '. $date_filter;
			}
			unset($date_filter);
		}
		if ( isset($filter_data['updated_date']) AND trim($filter_data['updated_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['updated_date'], 'a.updated_date' );
			if ( $date_filter != FALSE ) {
				$query  .=	' AND '. $date_filter;
			}
			unset($date_filter);
		}        
        
        $query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by','y.first_name','y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
        
        $query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by','z.first_name','z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;
        
        
		$query .= 	'
						AND a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );
        
        Debug::Arr( $ph, 'Query: '.$query, __FILE__, __LINE__, __METHOD__, 10 );
        
		$this->ExecuteSQL($query,$ph,$limit,$page);
		return $this;
	}


	function getByCompanyIdArray($company_id, $include_blank = TRUE) {

		$klf = new KPIListFactory();
		$klf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}

		foreach ($klf as $kpi_obj) {
			$list[$kpi_obj->getID()] = $kpi_obj->getName();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE) {
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

}
?>
