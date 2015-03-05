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
 * @package Modules\Message
 */
class MessageListFactory extends MessageFactory implements IteratorAggregate {

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

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
					AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}
		
		$uf = new UserFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.created_by = b.id
					WHERE
							b.company_id = ? AND a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndUserIdAndId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'user_id' => $user_id,
					'company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.created_by = b.id
					WHERE
							a.object_type_id in (5, 50)
							AND a.id = ?
							AND a.created_by = ?
							AND b.company_id = ?
							AND a.deleted = 0
					';
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getMessagesInThreadById( $id, $where = NULL, $order = NULL ) {

		if ( $id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					'id2' => $id,
					'id3' => $id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
					WHERE
							a.object_type_id in (5, 50)
							AND ( a.id = ?
									OR a.parent_id = ( select z.parent_id from '. $this->getTable() .' as z where z.id = ? AND z.parent_id != 0 )
									OR a.id = ( select z.parent_id from '. $this->getTable() .' as z where z.id = ? )
								)
							AND a.deleted = 0
					';
		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getNewMessagesByUserId( $user_id ) {
		if ( $user_id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$uf = new UserFactory();
		$pptsvf = new PayPeriodTimeSheetVerifyFactory();

		//Need to include all threads that user has posted to.
		$this->setCacheLifeTime( 600 );
		$unread_messages = $this->getCache($user_id);
		if ( $unread_messages === FALSE ) {
			$ph = array(
						'user_id' => $user_id,
						'id' => $user_id,
						'created_by1' => $user_id,
						'created_by2' => $user_id,
						'created_by3' => $user_id,
						'created_by4' => $user_id,
						);

			$query = '
						SELECT count(*)
						FROM '. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as d ON a.object_type_id = 5 AND a.object_id = d.id
							LEFT JOIN '. $uf->getTable() .' as f ON a.created_by = f.id
							LEFT JOIN '. $rf->getTable() .' as b ON a.object_type_id = 50 AND a.object_id = b.id
							LEFT JOIN '. $pptsvf->getTable() .' as e ON a.object_type_id = 90 AND a.object_id = e.id
						WHERE
								a.object_type_id in (5, 50, 90)
								AND a.status_id = 10
								AND
								(
									(
										b.user_id = ?
										OR d.id = ?
										OR e.user_id = ?
										OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id != 0 )
										OR a.parent_id in ( select id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id = 0 )
									)
									AND a.created_by != ?
								)

							AND ( a.deleted = 0 AND f.deleted = 0
								AND ( b.id IS NULL OR ( b.id IS NOT NULL AND b.deleted = 0 ) )
								AND ( d.id IS NULL OR ( d.id IS NOT NULL AND d.deleted = 0 ) )
								AND ( e.id IS NULL OR ( e.id IS NOT NULL AND e.deleted = 0 ) )
								AND NOT ( b.id IS NULL AND d.id IS NULL AND e.id IS NULL )
							)

						';
			$unread_messages = (int)$this->db->GetOne($query, $ph);

			$this->saveCache($unread_messages, $user_id);
		}
		return $unread_messages;
	}

	function getByUserIdAndFolder($user_id, $folder, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $user_id == '') {
			return FALSE;
		}

		$strict = TRUE;
		if ( $order == NULL ) {
			$strict = FALSE;
			$order = array( 'a.status_id' => '= 10 desc', 'a.created_date' => 'desc' );
		}

		//Folder is: INBOX, SENT
		$key = Option::getByValue($folder, $this->getOptions('folder') );
		if ($key !== FALSE) {
			$folder = $key;
		}

		$rf = new RequestFactory();
		$uf = new UserFactory();
		$pptsvf = new PayPeriodTimeSheetVerifyFactory();

		$ph = array(
					'user_id' => $user_id,
					//'id' => $user_id,
					);

		$folder_sent_query = NULL;
		$folder_inbox_query = NULL;
		$folder_inbox_query_a = NULL;
		$folder_inbox_query_ab = NULL;
		$folder_inbox_query_b = NULL;
		$folder_inbox_query_c = NULL;

		if ( $folder == 10 ) {
			$ph['id'] = $user_id;
			$ph['created_by1'] = $user_id;
			$ph['created_by2'] = $user_id;
			$ph['created_by3'] = $user_id;
			$ph['created_by4'] = $user_id;

			$folder_inbox_query = ' AND a.created_by != ?';
			$folder_inbox_query_a = ' OR d.id = ?';
			$folder_inbox_query_ab = ' OR e.user_id = ?';
			//$folder_inbox_query_b = ' OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = '. $user_id .' ) ';
			$folder_inbox_query_b = ' OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id != 0 ) ';
			$folder_inbox_query_c = ' OR a.parent_id in ( select id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id = 0 ) ';
		} elseif ( $folder == 20 ) {
			$ph['created_by4'] = $user_id;

			$folder_sent_query = ' OR a.created_by = ?';
		}

		//Need to include all threads that user has posted to.
		$query = '
					SELECT a.*,
							CASE WHEN a.object_type_id = 5 THEN d.id WHEN a.object_type_id = 50 THEN b.user_id WHEN a.object_type_id = 90 THEN e.user_id END as sent_to_user_id
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as d ON a.object_type_id = 5 AND a.object_id = d.id
						LEFT JOIN '. $uf->getTable() .' as f ON a.created_by = f.id
						LEFT JOIN '. $rf->getTable() .' as b ON a.object_type_id = 50 AND a.object_id = b.id
						LEFT JOIN '. $pptsvf->getTable() .' as e ON a.object_type_id = 90 AND a.object_id = e.id
					WHERE
							a.object_type_id in (5, 50, 90)
							AND
							(

								(
									(
										b.user_id = ?
										'. $folder_sent_query .'
										'. $folder_inbox_query_a .'
										'. $folder_inbox_query_ab .'
										'. $folder_inbox_query_b .'
										'. $folder_inbox_query_c .'
									)
									'. $folder_inbox_query .'
								)
							)

						AND ( a.deleted = 0 AND f.deleted = 0
								AND ( b.id IS NULL OR ( b.id IS NOT NULL AND b.deleted = 0 ) )
								AND ( d.id IS NULL OR ( d.id IS NOT NULL AND d.deleted = 0 ) )
								AND ( e.id IS NULL OR ( e.id IS NOT NULL AND e.deleted = 0 ) )
								AND NOT ( b.id IS NULL AND d.id IS NULL AND e.id IS NULL )
							)
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, array('sent_to_user_id') );

		//Debug::text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 9);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	function getByObjectTypeAndObjectAndId($object_type, $object_id, $id, $where = NULL, $order = NULL) {
		if ( $object_type == '' OR $object_id == '' OR $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'object_type' => $object_type,
					'object_id' => $object_id,
					'id' => $id,
					'parent_id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	object_type_id = ?
						AND object_id = ?
						AND ( id = ? OR parent_id = ? )
						AND deleted = 0
					ORDER BY id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByObjectTypeAndObject($object_type, $object_id, $where = NULL, $order = NULL) {
		if ( !isset($object_type) OR !isset($object_id) ) {
			return FALSE;
		}

		$ph = array(
					'object_type' => $object_type,
					'object_id' => $object_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	object_type_id = ?
						AND object_id = ?
						AND deleted = 0
					ORDER BY id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

}
?>
