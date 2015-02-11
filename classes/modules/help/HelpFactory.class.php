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
 * @package Modules\Help
 */
class HelpFactory extends Factory {
	protected $table = 'help';
	protected $pk_sequence_name = 'help_id_seq'; //PK Sequence name


	function _getFactoryOptions( $name ) {
	
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Form'),
										20 => TTi18n::gettext('Page')
									);
				break;
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('NEW'),
										15 => TTi18n::gettext('Pending Approval'),
										20 => TTi18n::gettext('ACTIVE')
									);
				break;

		}

		return $retval;
	}


	function getType() {
		return (int)$this->data['type_id'];
	}
	function setType($type) {
		$type = trim($type);
		
		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;	
		}
		
		Debug::Text('bType: '. $type, __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {
			
			$this->data['type_id'] = $type;
			
			return FALSE;
		}
		
		return FALSE;
	}

	function getStatus() {
		return (int)$this->data['status_id'];
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

	function getHeading() {
		return $this->data['heading'];
	}
	function setHeading($value) {
		$value = trim($value);

		if (	$value == NULL
				OR
				$this->Validator->isLength(	'heading',
											$value,
											TTi18n::gettext('Incorrect Heading length'),
											2, 255) ) {

			$this->data['heading'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getBody() {
		return $this->data['body'];
	}
	function setBody($value) {
		$value = trim($value);

		if (	$value == NULL
				OR
				$this->Validator->isLength(	'body',
											$value,
											TTi18n::gettext('Incorrect Body length'),
											2, 2048) ) {

			$this->data['body'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getKeywords() {
		return $this->data['keywords'];
	}
	function setKeywords($value) {
		$value = trim($value);

		if (	$value == NULL
				OR
				$this->Validator->isLength(	'keywords',
											$value,
											TTi18n::gettext('Incorrect Keywords length'),
											2, 1024) ) {

			$this->data['keywords'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getPrivate() {
		return $this->fromBool( $this->data['private'] );
	}
	function setPrivate($bool) {
		$this->data['private'] = $this->toBool($bool);

		return TRUE;
	}

}
?>
