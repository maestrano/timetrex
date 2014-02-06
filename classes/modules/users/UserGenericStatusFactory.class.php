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
 * $Id: UserGenericStatusFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Users
 */
class UserGenericStatusFactory extends Factory {
	protected $table = 'user_generic_status';
	protected $pk_sequence_name = 'user_generic_status_id_seq'; //PK Sequence name
	protected $batch_sequence_name = 'user_generic_status_batch_id_seq'; //PK Sequence name

	protected $batch_id = NULL;
	protected $queue = NULL;
	static protected $static_queue = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('Failed'),
										20 => TTi18n::gettext('Warning'),
										//25 => TTi18n::gettext('Notice'), //Friendly than a warning.
										30 => TTi18n::gettext('Success'),
									);
				break;
            case 'columns':
				$retval = array(
										'-1010-label' => TTi18n::gettext('Label'),
										'-1020-status' => TTi18n::gettext('Status'),
                                        '-1030-description' => TTi18n::gettext('Description'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
            case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
            case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'label',
								'status',
                                'description',
								);
				break;

		}

		return $retval;
	}


	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return $this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getNextBatchID() {
		$this->batch_id = $this->db->GenID( $this->batch_sequence_name );

		return $this->batch_id;
	}
	function getBatchID() {
		if ( isset($this->data['batch_id']) ) {
			return $this->data['batch_id'];
		}

		return FALSE;
	}
	function setBatchID($val) {
		$val = trim($val);
		if (	$this->Validator->isNumeric(	'batch_id',
												$val,
												TTi18n::gettext('Invalid Batch ID') )
						) {

			$this->data['batch_id'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return FALSE;
		}

		return FALSE;
	}

	function getLabel() {
		if ( isset($this->data['label']) ) {
			return $this->data['label'];
		}

		return FALSE;
	}
	function setLabel($val) {
		$val = trim($val);
		if (	$this->Validator->isLength(	'label',
											$val,
											TTi18n::gettext('Invalid label'),
											1,1024)
						) {

			$this->data['label'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($val) {
		$val = trim($val);
		if (	$val == ''
				OR
				$this->Validator->isLength(	'description',
											$val,
											TTi18n::gettext('Invalid description'),
											1,1024)
						) {

			$this->data['description'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getLink() {
		if ( isset($this->data['link']) ) {
			return $this->data['link'];
		}

		return FALSE;
	}
	function setLink($val) {
		$val = trim($val);
		if (	$val == ''
				OR
				$this->Validator->isLength(	'link',
											$val,
											TTi18n::gettext('Invalid link'),
											1,1024)
						) {

			$this->data['link'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	//Static Queue functions
	static function isStaticQueue() {
		if ( is_array( self::$static_queue ) AND count(self::$static_queue) > 0 ) {
			return TRUE;
		}

		return FALSE;
	}
	static function getStaticQueue() {
		return self::$static_queue;
	}
	static function clearStaticQueue() {
		self::$static_queue = NULL;

		return TRUE;
	}
	static function queueGenericStatus($label, $status, $description = NULL, $link = NULL ) {
		Debug::Text('Add Generic Status row to queue... Label: '. $label .' Status: '. $status, __FILE__, __LINE__, __METHOD__,10);
		$arr = array(
					'label' => $label,
					'status' => $status,
					'description' => $description,
					'link' => $link
					);

		self::$static_queue[] = $arr;

		return TRUE;
	}


	//Non-Static Queue functions
	function setQueue( $queue ) {
		$this->queue = $queue;

		UserGenericStatusFactory::clearStaticQueue();

		return TRUE;
	}

	function saveQueue() {
		if ( is_array($this->queue) ) {
			Debug::Arr($this->queue, 'Generic Status Queue', __FILE__, __LINE__, __METHOD__,10);
			foreach( $this->queue as $key => $queue_data ) {

				$ugsf = TTnew( 'UserGenericStatusFactory' );
				$ugsf->setUser( $this->getUser() );
				if ( $this->getBatchId() !== FALSE ) {
					$ugsf->setBatchID( $this->getBatchID() );
				} else {
					$this->setBatchId( $this->getNextBatchId() );
				}

				$ugsf->setLabel( $queue_data['label'] );
				$ugsf->setStatus( $queue_data['status'] );
				$ugsf->setDescription( $queue_data['description'] );
				$ugsf->setLink( $queue_data['link'] );

				if ( $ugsf->isValid() ) {
					$ugsf->Save();

					unset($this->queue[$key]);
				}
			}

			return TRUE;
		}

		Debug::Text('Generic Status Queue Empty', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	/*
	function addGenericStatus($label, $status, $description = NULL, $link = NULL ) {
		$this->setLabel( $label );
		$this->setStatus( $status );
		$this->setDescription( $description );
		$this->setLink( $link );

		$batch_id = $this->getBatchId();
		$user_id = $this->getUser();

		if ( $this->isValid() ) {
			$this->Save();

			$this->setBatchId( $batch_id );
			$this->setUser( $user_id );

			return TRUE;
		}

		return FALSE;
	}
	*/

	function preSave() {
		return TRUE;
	}
}
?>
