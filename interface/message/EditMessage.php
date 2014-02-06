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
 * $Revision: 4206 $
 * $Id: EditMessage.php 4206 2011-02-02 00:53:35Z ipso $
 * $Date: 2011-02-01 16:53:35 -0800 (Tue, 01 Feb 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('message','enabled')
		OR !( $permission->Check('message','edit') OR $permission->Check('message','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'New Message')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'filter_user_id',
												'data',
												) ) );

$mcf = TTnew( 'MessageControlFactory' );
$mrf = TTnew( 'MessageRecipientFactory' );
$msf = TTnew( 'MessageSenderFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit_message':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$redirect = TRUE;
		//Make sure the only array entry isn't 0 => 0;
		if ( is_array($filter_user_id) AND count($filter_user_id) > 0 AND ( isset($filter_user_id[0]) AND $filter_user_id[0] != 0 ) ) {
			$mcf->StartTransaction();

			$mcf = TTnew( 'MessageControlFactory' );
			$mcf->setFromUserId( $current_user->getId() );
			$mcf->setToUserId( $filter_user_id );
			$mcf->setObjectType( 5 );
			$mcf->setObject( $current_user->getId() );
			$mcf->setParent( 0 );
			$mcf->setSubject( $data['subject'] );
			$mcf->setBody( $data['body'] );

			if ( $mcf->isValid() ) {
				$mcf->Save();

				$mcf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'UserMessageList.php') );
				break;
			}
			$mcf->FailTransaction();
		} else {
			$mcf->Validator->isTrue(	'to',
									FALSE,
									TTi18n::gettext('Please select at least one recipient') );
		}
	default:
		if ( $permission->Check('message','send_to_any') ) {
			$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);
			$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		} else {
			//Only allow sending to supervisors OR children.
			$hlf = TTnew( 'HierarchyListFactory' );

			//FIXME: For supervisors, we may need to include supervisors at the same level
			// Also how to handle cases where there are no To: recipients to select from.

			//Get Parents
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100), FALSE, FALSE );
			//Debug::Arr( $request_parent_level_user_ids, 'Request Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

			//Get Children, in case the current user is a superior.
			$request_child_level_user_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100) );
			//Debug::Arr( $request_child_level_user_ids, 'Request Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

			$request_user_ids = array_merge( (array)$request_parent_level_user_ids, (array)$request_child_level_user_ids );
			//Debug::Arr( $request_user_ids, 'User Ids', __FILE__, __LINE__, __METHOD__,10);

			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByIdAndCompanyId( $request_user_ids, $current_user->getCompany() );
			$user_options = UserListFactory::getArrayByListFactory( $ulf, TRUE, FALSE);

			//$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$data['user_options'] = $user_options;
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		}


		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);
		$smarty->assign_by_ref('filter_user_id', $filter_user_id);

		break;
}

$smarty->assign_by_ref('mcf', $mcf);

$smarty->display('message/EditMessage.tpl');
?>