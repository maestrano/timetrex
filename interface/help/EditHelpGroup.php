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
 * $Revision: 4104 $
 * $Id: EditHelpGroup.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('help','enabled')
		OR !( $permission->Check('help','edit') OR $permission->Check('help','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Help Group Entries')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'help_data',
												'script',
												'name'
												) ) );

$hgcf = TTnew( 'HelpGroupControlFactory' );

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$hgcf->setId($help_data['id']);
		$hgcf->setHelp($help_data['selected_help_ids']);

		if ( $hgcf->isValid() ) {
			//$hgcf->Save();

			Redirect::Page( URLBuilder::getURL(NULL, 'HelpGroupControlList.php') );

			break;
		}

	default:
		$hgclf = TTnew( 'HelpGroupControlListFactory' );

		if ( isset($script) AND !isset($id) ) {
			Debug::Text('Script and Name were passed, attempt lookup!', __FILE__, __LINE__, __METHOD__,10);

			$hgclf->getByScriptAndName( $script, $name );
			if ( $hgclf->getRecordCount() > 0 ) {
				$id = $hgclf->getCurrent()->getID();
				Debug::Text('Found already existing ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
			} else {
				//$help_data = array( 'script_name' => $script, 'name' => $name);
			}
		}

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$hgclf->getById($id);

			foreach ($hgclf as $help_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$help_data = array(
								'id' => $help_obj->getId(),
								'script_name' => $help_obj->getScriptName(),
								'name' => $help_obj->getName(),
								'help_ids' => $help_obj->getHelp(),
								'created_date' => $help_obj->getCreatedDate(),
								'created_by' => $help_obj->getCreatedBy(),
								'updated_date' => $help_obj->getUpdatedDate(),
								'updated_by' => $help_obj->getUpdatedBy(),
								'deleted_date' => $help_obj->getDeletedDate(),
								'deleted_by' => $help_obj->getDeletedBy(),
								'deleted' => $help_obj->getDeleted()
								);
			}
		}

		//Get all help items
		$hlf = TTnew( 'HelpListFactory' );
		$help_options = $hlf->getAllArray();
		//Select box options;
		$help_data['help_options'] = $help_options;

		if ( isset($help_data['help_ids']) AND is_array($help_data['help_ids']) ) {
			foreach( $help_data['help_ids'] as $selected_help_id ) {
				$help_data['selected_help_options'][$selected_help_id] = $help_options[$selected_help_id];
			}
		}
		$smarty->assign_by_ref('help_data', $help_data);

		break;
}

$smarty->assign_by_ref('hgcf', $hgcf);

$smarty->display('help/EditHelpGroup.tpl');
?>