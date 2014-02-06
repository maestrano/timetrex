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
 * $Revision: 8371 $
 * $Id: HelpGroupControlFactory.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Help
 */
class HelpGroupControlFactory extends Factory {
	protected $table = 'help_group_control';
	protected $pk_sequence_name = 'help_group_control_id_seq'; //PK Sequence name
	function getScriptName() {
		return $this->data['script_name'];
	}
	function setScriptName($value) {
		$value = trim($value);

		if (	$this->Validator->isLength(	'script_name',
											$value,
											TTi18n::gettext('Incorrect Script Name'),
											2,255) ) {

			$this->data['script_name'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getName() {
		return $this->data['name'];
	}
	function setName($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'name',
											$value,
											TTi18n::gettext('Incorrect Name'),
											2,255) ) {

			$this->data['name'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getHelp() {
		$hglf = TTnew( 'HelpGroupListFactory' );
		$hglf->getByHelpGroupControlId( $this->getId() );
		foreach ($hglf as $help_group_obj) {
			$help_list[] = $help_group_obj->getHelp();
		}

		if ( isset($help_list) ) {
			return $help_list;
		}

		return FALSE;
	}
	function setHelp($ids) {
		//If needed, delete mappings first.
		$hglf = TTnew( 'HelpGroupListFactory' );
		$hglf->getByHelpGroupControlId( $this->getId() );

		$help_ids = array();
		foreach ($hglf as $help_group_entry) {
			$help_id = $help_group_entry->getHelp();
			Debug::text('Help ID: '. $help_group_entry->getHelp(), __FILE__, __LINE__, __METHOD__, 10);

			//Delete all items first.				
			$help_group_entry->Delete();
		}
		
		if (is_array($ids) and count($ids) > 0) {

			//Insert new mappings.
			$hgf = TTnew( 'HelpGroupFactory' );
			$i=0;
			foreach ($ids as $id) {
				//if ( !in_array($id, $help_ids) ) {
					$hgf->setHelpGroupControl( $this->getId() );
					$hgf->setOrder( $i );
					$hgf->setHelp( $id );
					

					if ($this->Validator->isTrue(		'help',
														$hgf->Validator->isValid(),
														TTi18n::gettext('Incorrect Help Entry'))) {
						$hgf->save();
					}
				//}
				$i++;
			}

			//return TRUE;
		}

		return TRUE;
	}

}
?>
