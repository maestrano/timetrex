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
 * $Revision: 9743 $
 * $Id: Upload.php 9743 2013-05-02 21:22:23Z ipso $
 * $Date: 2013-05-02 14:22:23 -0700 (Thu, 02 May 2013) $
 */
require_once('../../includes/global.inc.php');

//Debug::setVerbosity(11);
$skip_message_check = TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/upload/fileupload.class.php');
/*
if ( !$permission->Check('user','enabled')
		OR !( $permission->Check('user','view') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}
*/

$smarty->assign('title', TTi18n::gettext($title = 'File Upload')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'object_type',
												'object_id',
												'data',
												'userfile'
												) ) );

$ulf = TTnew( 'UserListFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'upload':
		Debug::Text('Upload... Object Type: '. $object_type, __FILE__, __LINE__, __METHOD__,10);

		$upload = new fileupload();

		$object_type = strtolower($object_type);
		switch ($object_type) {
			case 'invoice_config':
				$upload->set_max_filesize(1000000); //1mb or less
				$upload->set_acceptable_types( array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png') ); // comma separated string, or array
				//$upload->set_max_image_size(600, 600);
				$upload->set_overwrite_mode(1);

				$icf = TTnew( 'InvoiceConfigFactory' );
				$icf->cleanStoragePath( $current_company->getId() );

				$dir = $icf->getStoragePath( $current_company->getId() );
				break;
			case 'company_logo':
				$upload->set_max_filesize(1000000); //1mb or less
				$upload->set_acceptable_types( array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png') ); // comma separated string, or array
				//$upload->set_max_image_size(600, 600);
				$upload->set_overwrite_mode(1);

				$cf = TTnew( 'CompanyFactory' );
				$cf->cleanStoragePath( $current_company->getId() );

				$dir = $cf->getStoragePath( $current_company->getId() );
				break;
			case 'license':
				$upload->set_max_filesize(20000); //1mb or less
				$upload->set_acceptable_types( array('text/plain','plain/text','application/octet-stream') ); // comma separated string, or array
				$upload->set_overwrite_mode(1);

				$dir = Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'license'. DIRECTORY_SEPARATOR . $current_company->getId();
				break;
		}

		Debug::Text('bUpload... Object Type: '. $object_type, __FILE__, __LINE__, __METHOD__,10);
		if ( isset($dir) ) {
			@mkdir($dir, 0700, TRUE);

			$upload_result = $upload->upload("userfile", $dir);
			//var_dump($upload ); //file data
			if ($upload_result) {
				$success = $upload_result .' '. TTi18n::gettext('Successfully Uploaded');
			} else {
				$error = $upload->get_error();
			}
		}
		Debug::Text('cUpload... Object Type: '. $object_type, __FILE__, __LINE__, __METHOD__,10);

		switch ($object_type) {
			case 'invoice_config':
				Debug::Text('Post Upload Operation...', __FILE__, __LINE__, __METHOD__,10);
				if ( isset($success) AND $success != '' ) {
					Debug::Text('Rename', __FILE__, __LINE__, __METHOD__,10);
					//Submit filename to db.
					//Rename file to just "logo" so its always consistent.

					$file_data_arr = $upload->get_file();
					rename( $dir.'/'.$upload_result, $dir.'/logo'. $file_data_arr['extension'] );

					//$post_js = 'window.opener.document.getElementById(\'logo\').src = \''. Environment::getBaseURL().'/send_file.php?object_type=invoice_config&rand='.time().'\'; window.opener.showLogo();';
					$post_js = 'window.opener.setLogo()';
				}
				break;
			case 'company_logo':
				Debug::Text('Post Upload Operation...', __FILE__, __LINE__, __METHOD__,10);
				if ( isset($success) AND $success != '' ) {
					Debug::Text('Rename', __FILE__, __LINE__, __METHOD__,10);
					//Submit filename to db.
					//Rename file to just "logo" so its always consistent.

					//Don't resize image, because we use so many different sizes (paystub,logo,etc...) that we can't pick a good size, so just leave as original.
/*
					//Resize image if its too large
					require_once 'Image/Transform.php';
					$image_transform =& Image_Transform::factory('');

					$image_transform->load($dir.'/'.$upload_result);
					$image_transform->fit(170,40);
					$image_transform->save($dir.'/'.$upload_result);
*/
					$file_data_arr = $upload->get_file();
					rename( $dir.'/'.$upload_result, $dir.'/logo'. $file_data_arr['extension'] );

					//$post_js = 'window.opener.document.getElementById(\'logo\').src = \''. Environment::getBaseURL().'/send_file.php?object_type=invoice_config&rand='.time().'\'; window.opener.showLogo();';
					$post_js = 'window.opener.setLogo()';
				}
				break;
			case 'license':
				Debug::Text('Post Upload Operation...', __FILE__, __LINE__, __METHOD__,10);
				if ( isset($success) AND $success != '' ) {
					Debug::Text('Rename', __FILE__, __LINE__, __METHOD__,10);

					$file_data_arr = $upload->get_file();
					$license_data = trim( file_get_contents( $dir.'/'.$upload_result ) );

					$license = new TTLicense();
					$retval = $license->getLicenseFile( TRUE, $license_data ); //Download updated license file if one exists.
					if ( $retval === FALSE ) {
						$error = TTi18n::gettext('Invalid License File');
					} else {
						$post_js = 'window.opener.location.reload();';
					}
				}

				break;
		}

		$smarty->assign_by_ref('error', $error);
		$smarty->assign_by_ref('success', $success);
		$smarty->assign_by_ref('post_js', $post_js);

	default:
		$smarty->assign_by_ref('data', $data);

		$smarty->assign_by_ref('object_type', $object_type);
		$smarty->assign_by_ref('object_id', $object_id);

		break;
}

$smarty->display('upload/Upload.tpl');
?>