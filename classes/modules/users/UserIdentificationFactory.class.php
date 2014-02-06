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
 * $Revision: 1378 $
 * $Id: UserWageFactory.class.php 1378 2007-11-02 22:09:17Z ipso $
 * $Date: 2007-11-02 15:09:17 -0700 (Fri, 02 Nov 2007) $
 */


/**
 * @package Modules\Users
 */
class UserIdentificationFactory extends Factory {
	protected $table = 'user_identification';
	protected $pk_sequence_name = 'user_identification_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
											5 	=> TTi18n::gettext('Password History'), //Web interface password history
											10 	=> TTi18n::gettext('iButton'),
											20	=> TTi18n::gettext('USB Fingerprint'),
											//25	=> TTi18n::gettext('LibFingerPrint'),
											30	=> TTi18n::gettext('Barcode'), //For barcode readers and USB proximity card readers.
											35	=> TTi18n::gettext('QRcode'), //For cameras to read QR code badges.
											40	=> TTi18n::gettext('Proximity Card'), //Mainly for proximity cards on timeclocks.
											75	=> TTi18n::gettext('Facial Recognition'),
											100	=> TTi18n::gettext('TimeClock FingerPrint (v9)'), //TimeClocks v9 algo
											101	=> TTi18n::gettext('TimeClock FingerPrint (v10)'), //TimeClocks v10 algo
									);
				break;

		}

		return $retval;
	}

	function getUserObject() {
		if ( is_object($this->user_obj) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	function getUser() {
		return $this->data['user_id'];
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	/*
		For fingerprints,
			10 = Fingerprint 1	Pass 0.
			11 = Fingerprint 1	Pass 1.
			12 = Fingerprint 1	Pass 2.

			20 = Fingerprint 2	Pass 0.
			21 = Fingerprint 2	Pass 1.
			...
	*/
	function getNumber() {
		if ( isset($this->data['number']) ) {
			return $this->data['number'];
		}

		return FALSE;
	}
	function setNumber($value) {
		$value = trim($value);

		//Pull out only digits
		$value = $this->Validator->stripNonNumeric($value);

		if (	$this->Validator->isFloat(	'number',
											$value,
											TTi18n::gettext('Incorrect Number')) ) {

			$this->data['number'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueValue($user_id, $type_id, $value) {
		$ph = array(
					'user_id' => (int)$user_id,
					'type_id' => (int)$type_id,
					'value' => (string)$value,
					);

		$uf = TTnew( 'UserFactory' );

		$query = 'select a.id
					from '. $this->getTable() .' as a,
						'. $uf->getTable() .' as b
					where a.user_id = b.id
						AND b.company_id = ( select z.company_id from '. $uf->getTable() .' as z where z.id = ? and z.deleted = 0 )
						AND a.type_id = ?
						AND a.value = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$id = $this->db->GetOne($query, $ph);
		//Debug::Arr($id,'Unique Value: '. $value, __FILE__, __LINE__, __METHOD__,10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getValue() {
		if ( isset($this->data['value']) ) {
			return $this->data['value'];
		}

		return FALSE;
	}
	function setValue($value) {
		$value = trim($value);

		if (
				$this->Validator->isLength(			'value',
													$value,
													TTi18n::gettext('Value is too short or too long'),
													1,
													48000) //Need relatively large face images.
			) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getExtraValue() {
		if ( isset($this->data['extra_value']) ) {
			return $this->data['extra_value'];
		}

		return FALSE;
	}
	function setExtraValue($value) {
		$value = trim($value);

		if (
				$this->Validator->isLength(			'extra_value',
													$value,
													TTi18n::gettext('Extra Value is too long'),
													1,
													256000)
			) {

			$this->data['extra_value'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function Validate() {
		if ( $this->getValue() == FALSE ) {
				$this->Validator->isTRUE(			'value',
													FALSE,
													TTi18n::gettext('Value is not defined') );

		} else {
			$this->Validator->isTrue(		'value',
											$this->isUniqueValue( $this->getUser(), $this->getType(), $this->getValue() ),
											TTi18n::gettext('Value is already in use, please enter a different one'));
		}
		return TRUE;
	}

	function preSave() {
		if (  $this->getNumber() == '' ) {
			$this->setNumber( 0 );
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );

		return TRUE;
	}

	function addLog( $log_action ) {
		//Don't do detail logging for this, as it will store entire figerprints in the log table.
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Identification - Employee'). ': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Type') . ': '. Option::getByKey($this->getType(), $this->getOptions('type') ) , NULL, $this->getTable() );
	}
}
?>
