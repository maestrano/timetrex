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
 * $Id: CompanyListFactory.class.php 11018 2013-09-24 23:39:40Z ipso $
 * $Date: 2013-09-24 16:39:40 -0700 (Tue, 24 Sep 2013) $
 */

/**
 * @package Modules\Company
 */
class CompanyListFactory extends CompanyFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$additional_order_fields = array('last_login_date');

		$uf = new UserFactory();

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a
					WHERE a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields  );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getAllAndLastLoginDate($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$additional_order_fields = array('last_login_date');

		$uf = new UserFactory();

		$query = '
					select 	a.*,
							(select max(last_login_date) from '. $uf->getTable() .' as uf where uf.company_id = a.id ) as last_login_date
					from	'. $this->getTable() .' as a
					WHERE a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields  );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
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
	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		return self::getById( $company_id, $where, $order);
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
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByShortName($short_name, $where = NULL, $order = NULL) {
		if ( $short_name == '' ) {
			return FALSE;
		}

		$ph = array(
					'short_name' => strtolower($short_name),
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	lower(short_name) = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserName($user_name, $where = NULL, $order = NULL) {
		if ( $user_name == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'user_name' => strtolower( $user_name ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a, '. $uf->getTable() .' as b
					where	a.id = b.company_id
						AND b.status_id = 10
						AND b.user_name = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPhoneID($phone_id, $where = NULL, $order = NULL) {
		if ( $phone_id == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'phone_id' => strtolower( $phone_id ),
					);

		$query = '
					select 	a.*
					from	'. $this->getTable() .' as a, '. $uf->getTable() .' as b
					where	a.id = b.company_id
						AND b.status_id = 10
						AND b.phone_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
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
			if ( $obj->getStatus() != 10 ) {
				$status = '('.Option::getByKey($obj->getStatus(), $obj->getOptions('status') ).') ';
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


	static function getAllArray() {
		$clf = new CompanyListFactory();
		$clf->getAll();

		$company_list[0] = '--';

		foreach ($clf as $company) {
			$company_list[$company->getID()] = $company->getName();
		}

		return $company_list;
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

		$additional_order_fields = array('status_id','last_login_date', 'total_active_days', 'last_login_days', 'this_month_max_active_users', 'this_month_avg_active_users', 'this_month_min_active_users', 'last_month_max_active_users', 'last_month_avg_active_users', 'last_month_min_active_users' );

		$sort_column_aliases = array(
									 'status' => 'status_id',
									 'product_edition' => 'product_edition_id',
									 );

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'name' => 'asc');
			$strict = FALSE;
		} else {
			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name,first name after other columns
			if ( !isset($order['name']) ) {
				$order['name'] = 'asc';
			}
			$strict = TRUE;
		}
		//Debug::Arr($order,'Order Data:', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($filter_data,'Filter Data:', __FILE__, __LINE__, __METHOD__,10);

		$uf = new UserFactory();
		$cuff = new CompanyUserCountFactory();

		$ph = array(
					//'company_id' => $company_id,
					);

		$query = '
					select 	a.*,
							_ADODB_COUNT
							(select max(last_login_date) from '. $uf->getTable() .' as uf where uf.company_id = a.id ) as last_login_date,
							((select max(last_login_date) from '. $uf->getTable() .' as uf where uf.company_id = a.id )-a.created_date) as total_active_days,
							('. time() .'-(select max(last_login_date) from '. $uf->getTable() .' as uf where uf.company_id = a.id )) as last_login_days,
							(select min(active_users) as min_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch() ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( time() ) ) .' ) as this_month_min_active_users,
							(select avg(active_users) as avg_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch() ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( time() ) ) .' ) as this_month_avg_active_users,
							(select max(active_users) as max_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch() ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( time() ) ) .' ) as this_month_max_active_users,
							(select min(active_users) as min_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( TTDate::getEndMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' ) as last_month_min_active_users,
							(select avg(active_users) as avg_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( TTDate::getEndMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' ) as last_month_avg_active_users,
							(select max(active_users) as max_active_users from '. $cuff->getTable() .' as cuff where cuff.company_id = a.id AND date_stamp >= '. $this->db->qstr( $this->db->BindDate( TTDate::getBeginMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' AND date_stamp <= '. $this->db->qstr( $this->db->BindDate( TTDate::getEndMonthEpoch( TTDate::getBeginMonthEpoch()-86400 ) ) ) .' ) as last_month_max_active_users,
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
							_ADODB_COUNT
					from 	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	1=1
					';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['name']) ) ? $this->getWhereClauseSQL( 'a.name', $filter_data['name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['short_name']) ) ? $this->getWhereClauseSQL( 'a.short_name', $filter_data['short_name'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['product_edition_id']) ) ? $this->getWhereClauseSQL( 'a.product_edition_id', $filter_data['product_edition_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['fax_phone']) ) ? $this->getWhereClauseSQL( 'a.fax_phone', $filter_data['fax_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['business_number']) ) ? $this->getWhereClauseSQL( 'a.business_number', $filter_data['businessnumber'], 'text', $ph ) : NULL;

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
