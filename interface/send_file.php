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
 * $Revision: 9851 $
 * $Id: send_file.php 9851 2013-05-10 20:43:50Z ipso $
 * $Date: 2013-05-10 13:43:50 -0700 (Fri, 10 May 2013) $
 */
require_once('../includes/global.inc.php');

require_once('PEAR.php');
require_once('HTTP/Download.php');

extract	(FormVariables::GetVariables(
										array	(
												'action',
												'api', //Called from Flex
												'object_type',
												'object_id',
												'parent_id',
												) ) );

if ( isset($api) AND $api == TRUE ) {
	require_once('../includes/API.inc.php');
}

$object_type = strtolower($object_type);

if ( $object_type != 'primary_company_logo' AND $object_type != 'copyright' ) {
	$skip_message_check = TRUE;
	require_once(Environment::getBasePath() .'includes/Interface.inc.php');
}

switch ($object_type) {
	case 'document':
		Debug::Text('Document...', __FILE__, __LINE__, __METHOD__,10);

		$drlf = TTnew( 'DocumentRevisionListFactory' );
		$drlf->getByIdAndDocumentId( $object_id, $parent_id );
		Debug::Text('Record Count: '. $drlf->getRecordCount(), __FILE__, __LINE__, __METHOD__,10);
		if ( $drlf->getRecordCount() == 1 ) {
			//echo "File Name: $file_name<br>\n";
			$dr_obj = $drlf->getCurrent();

			$file_name = $dr_obj->getStoragePath().$dr_obj->getLocalFileName();
			Debug::Text('File Name: '. $file_name .' Mime: '. $dr_obj->getMimeType(), __FILE__, __LINE__, __METHOD__,10);
			if ( file_exists($file_name) ) {
				$params['file'] = $file_name;
				$params['ContentType'] = $dr_obj->getMimeType();
				$params['ContentDisposition'] = array( HTTP_DOWNLOAD_ATTACHMENT, $dr_obj->getRemoteFileName() );
				$params['cache'] = FALSE;
			} else {
				Debug::Text('File does not exist... File Name: '. $file_name .' Mime: '. $dr_obj->getMimeType(), __FILE__, __LINE__, __METHOD__,10);
			}
		}
		Debug::writeToLog(); //Write to log when downloading documents.
		break;
	case 'client_payment_signature':
		Debug::Text('Client Payment Signature...', __FILE__, __LINE__, __METHOD__,10);

		$cplf = TTnew( 'ClientPaymentListFactory' );
		$cplf->getByIdAndClientId($object_id, $parent_id);
		if ( $cplf->getRecordCount() == 1 ) {
			//echo "File Name: $file_name<br>\n";
			$cp_obj = $cplf->getCurrent();

			$file_name = $cp_obj->getSignatureFileName();
			Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
			if ( file_exists($file_name) ) {
				$params['file'] = $file_name;
				$params['ContentType'] = 'image/png';
				$params['ContentDisposition'] = array( HTTP_DOWNLOAD_ATTACHMENT, 'signature.png' );
				$params['cache'] = FALSE;
			}
		}
		break;
	case 'invoice_config':
		Debug::Text('Invoice Config...', __FILE__, __LINE__, __METHOD__,10);

		$icf = TTNew('InvoiceConfigFactory');
		$file_name = $icf->getLogoFileName( $current_company->getId() );
		Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
		if ( file_exists($file_name) ) {
			$params['file'] = $file_name;
			$params['cache'] = TRUE;
		}
		break;
	case 'company_logo':
		Debug::Text('Company Logo...', __FILE__, __LINE__, __METHOD__,10);

		$cf = TTnew( 'CompanyFactory' );
		$file_name = $cf->getLogoFileName( $current_company->getId() );
		Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
		if ( $file_name != '' AND file_exists($file_name) ) {
			$params['file'] = $file_name;
			$params['ContentType'] = 'image/'. strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
			$params['ContentDisposition'] = array( HTTP_DOWNLOAD_INLINE, $file_name );
			$params['cache'] = TRUE;
		}
		break;
	case 'primary_company_logo':
		Debug::Text('Primary Company Logo...', __FILE__, __LINE__, __METHOD__,10);

		$cf = TTnew( 'CompanyFactory' );
		$file_name = $cf->getLogoFileName( PRIMARY_COMPANY_ID, TRUE, TRUE );
		Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
		if ( $file_name != '' AND file_exists($file_name) ) {
			$params['file'] = $file_name;
			$params['ContentType'] = 'image/'. strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
			$params['ContentDisposition'] = array( HTTP_DOWNLOAD_INLINE, $file_name );
			$params['cache'] = TRUE;
		}
		break;
	case 'user_photo':
		Debug::Text('User Photo...', __FILE__, __LINE__, __METHOD__,10);

		$uf = TTnew( 'UserFactory' );
		$file_name = $uf->getPhotoFileName( $current_company->getId(), $object_id );
		Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
		if ( $file_name != '' AND file_exists($file_name) ) {
			$params['file'] = $file_name;
			$params['ContentType'] = 'image/'. strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
			$params['ContentDisposition'] = array( HTTP_DOWNLOAD_INLINE, $file_name );
			$params['cache'] = TRUE;
		}
		break;
	case 'copyright':
		Debug::Text('Copyright Logo...', __FILE__, __LINE__, __METHOD__,10);
		//
		//REMOVING OR CHANGING THIS LOGO IS IN STRICT VIOLATION OF THE LICENSE AGREEMENT AND COPYRIGHT LAWS.
		//
		if ( getTTProductEdition() > 10 ) { $file_name = Environment::getImagesPath().'/powered_by.jpg';Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);if ( $file_name != '' AND file_exists($file_name) ) { $params['file'] = $file_name;$params['contentdisposition'] = 'attachment; filename=pro_copyright.jpg';$params['data'] = file_get_contents($file_name);$params['cache'] = TRUE;}} else {$params['contentdisposition'] = 'attachment; filename=copyright.jpg';$params['data'] = base64_decode('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');$params['cache'] = TRUE;}
		break;
	case 'copyright_wide':
	case 'smcopyright':
		Debug::Text('Copyright Logo...', __FILE__, __LINE__, __METHOD__,10);
		//
		//REMOVING OR CHANGING THIS LOGO IS IN STRICT VIOLATION OF THE LICENSE AGREEMENT AND COPYRIGHT LAWS.
		//
		if ( getTTProductEdition() > 10 ) { $file_name = Environment::getImagesPath().'/powered_by_wide.png';Debug::Text('File Name: '. $file_name, __FILE__, __LINE__, __METHOD__,10);if ( $file_name != '' AND file_exists($file_name) ) {$params['file'] = $file_name;$params['contentdisposition'] = 'attachment; filename=pro_copyright.jpg';$params['data'] = file_get_contents($file_name);$params['cache'] = TRUE;}} else {$params['contentdisposition'] = 'attachment; filename=copyright.jpg';$params['data'] = base64_decode('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');$params['cache'] = TRUE;}
		break;

	default:
		break;
}

//Debug::Arr($params, 'Download Params:', __FILE__, __LINE__, __METHOD__,10);
if ( isset($params) ) {
	HTTP_Download::staticSend($params);
} else {
	echo "File does not exist, unable to download!<br>\n";
	Debug::writeToLog();
}
?>