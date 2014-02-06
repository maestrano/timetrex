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
 * $Id: WindowViewHelp.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('help','enabled')
		OR !( $permission->Check('help','view') OR $permission->Check('help','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

/*
$title = 'Edit Help';
$smarty->assign('title', $title);
*/

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'script',
												'group',
												'title'
												) ) );

$hf = TTnew( 'HelpFactory' );

switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);
	default:
		//Trim path off script name
		$split_script = explode('/', $script);
		$script = $split_script[count($split_script)-1];

		$hlf = TTnew( 'HelpListFactory' );

		//$hlf->getByScriptNameAndGroupName($script, NULL);
		$hlf->getByScriptNameAndStatus($script,'ACTIVE');
		//$hlf->getById($id);

		$i=0;
		foreach ($hlf as $help_obj) {
			//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

			if ( $i > 0 AND $prev_type != $help_obj->getType() ) {
				$type_change = TRUE;
			} else {
				$type_change = FALSE;
			}

			$help_entries[] = array(
							'id' => $help_obj->GetId(),
							'type_id' => $help_obj->getType(),
							'type' => Option::getByKey($help_obj->getType(), $help_obj->getOptions('type') ),
							'type_change' => $type_change,
							'status_id' => $help_obj->getStatus(),
							'status' => Option::getByKey($help_obj->getStatus(), $help_obj->getOptions('status') ),
							'heading' => $help_obj->getHeading(),
							'body' => $help_obj->getBody(),
							'keywords' => $help_obj->getKeywords(),
							'private' => $help_obj->getPrivate(),
							'created_date' => $help_obj->getCreatedDate(),
							'created_by' => $help_obj->getCreatedBy(),
							'updated_date' => $help_obj->getUpdatedDate(),
							'updated_by' => $help_obj->getUpdatedBy(),
							'deleted_date' => $help_obj->getDeletedDate(),
							'deleted_by' => $help_obj->getDeletedBy(),
							'deleted' => $help_obj->getDeleted()
							);

			$prev_type = $help_obj->getType();

			$i++;
		}

		$smarty->assign_by_ref('help_entries', $help_entries);
		$smarty->assign_by_ref('title', $title);

		$smarty->assign_by_ref('SCRIPT_BASE_NAME', $script);

		break;
}

$smarty->assign_by_ref('hf', $hf);

$smarty->display('help/WindowViewHelp.tpl');
?>