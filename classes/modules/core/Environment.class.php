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
 * @package Core
 */
class Environment {

	static protected $template_dir = 'templates';
	static protected $template_compile_dir = 'templates_c';

	static function getBasePath() {
		//return dirname( __FILE__ ) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR;
		return str_replace('classes'. DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR .'core', '', dirname( __FILE__ ) );
	}

	static function getHostName() {
		return Misc::getHostName( TRUE );
	}

	static function getBaseURL() {
		global $config_vars;

		if ( isset($config_vars['path']['base_url']) ) {
			if ( substr( $config_vars['path']['base_url'], -1) != '/' ) {
				return $config_vars['path']['base_url']. '/'; //Don't use directory separator here
			}
			return $config_vars['path']['base_url'];
		}

		return '/';
	}

	//Due to how the legacy interface is handled, we need to use the this function to determine the URL to redirect too,
	//as the base_url needs to be /interface most of the time, for images and such to load properly.
	static function getDefaultInterfaceBaseURL() {
		return self::getBaseURL();
	}

	//Returns the BASE_URL for the API functions.
	static function getAPIBaseURL( $api = NULL ) {
		global $config_vars;

		//If "interface" appears in the base URL, replace it with API directory
		$base_url = str_replace( array('/interface', '/api'), '', $config_vars['path']['base_url']);

		if ( $api == '' ) {
			if ( defined('TIMETREX_AMF_API') AND TIMETREX_AMF_API == TRUE ) {
				$api = 'amf';
			} elseif ( defined('TIMETREX_SOAP_API') AND TIMETREX_SOAP_API == TRUE )	 {
				$api = 'soap';
			} elseif ( defined('TIMETREX_JSON_API') AND TIMETREX_JSON_API == TRUE )	 {
				$api = 'json';
			}
		}

		$base_url = $base_url.'/api/'.$api.'/';

		return $base_url;
	}

	static function getAPIURL( $api ) {
		return self::getAPIBaseURL( $api ).'api.php';
	}

	static function getTemplateDir() {
		return self::getBasePath() . self::$template_dir . DIRECTORY_SEPARATOR;
	}

	static function getTemplateCompileDir() {
		global $config_vars;

		if ( isset($config_vars['path']['templates']) ) {
			return $config_vars['path']['templates'] . DIRECTORY_SEPARATOR;
		} else {
			return self::getBasePath() . self::$template_compile_dir . DIRECTORY_SEPARATOR;
		}
	}

	static function getImagesPath() {
		return self::getBasePath() . DIRECTORY_SEPARATOR .'interface'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR;
	}

	static function getImagesURL() {
		return self::getBaseURL() .'images/';
	}

	static function getStorageBasePath() {
		global $config_vars;

		return $config_vars['path']['storage'] . DIRECTORY_SEPARATOR;
	}

	static function getLogBasePath() {
		global $config_vars;

		return $config_vars['path']['log'] . DIRECTORY_SEPARATOR;
	}

}
?>
