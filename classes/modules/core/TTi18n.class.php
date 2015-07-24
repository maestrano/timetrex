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


/*
 * --> NOTE TO Ubuntu/Debian users! <--
 *
 * In some cases you may have to generate the locale that you wish to use with the following command:
 * sudo locale-gen <locale name>
 *
 * ie:
 *
 * sudo locale-gen es_ES
 *
 */

/**
 * @package Core
 */
class TTi18n {
	static private $locale_handler = NULL;
	static private $translation_handler = NULL;

	static private $language = 'en';
	static private $country = 'US';

	static private $master_locale = NULL;
	static private $locale = NULL;
	static private $normalized_locale = NULL;
	static private $is_default_locale = TRUE;

	static public function getLocaleHandler() {
		if ( self::$locale_handler === NULL ) {
			require_once('I18Nv2.php');

			// If first param is not NULL, (eg 'en_US') then I18n throws php notices on debian/ubuntu.
			//Set second param (paranoid) to TRUE so it doesn't break FPDF/TCPDF
			self::$locale_handler = &I18Nv2::createLocale( NULL, TRUE );
		}

		return self::$locale_handler;
	}

	/**
	 * Specifies if the native PHP gettext extension is
	 * available and safe to use.
	 *
	 * @return bool
	 */
	static private function useGetTextExtension() {

		//Force the use of GetText extension for now, as Translation2 is WAY to slow right now.
		//HHVM currently doesn't support getText extension though, so at least allow it to be used.
		//return TRUE;

		// Use a static for speed.
		static $use_gettext = NULL;

		if ( $use_gettext === NULL ) {
			// Here we check that:
			//	1) gettext extension is loaded
			//	2) we are running under apache
			//	3) the apache prefork module is loaded.	 so we are not multi-threaded.
			if ( extension_loaded( 'gettext' ) ) {
				$use_gettext = TRUE;
				
				//Translation2 is too slow on Windows, force getText for now.
				//$sapi_name = php_sapi_name();
				//if ( $sapi_name == 'cli' ) {
				//	$use_gettext = TRUE;
				//} elseif ( in_array( php_sapi_name(), array( 'apache', 'apache2handler' ) ) AND in_array( 'prefork', apache_get_modules() ) ) {
					//Only check apache_modules if we are using Apache.
				//	$use_gettext = TRUE;
				//} else {
				//	$use_gettext = FALSE;
				//}
			}
			// Note: We could also check for a config file setting that would override this.

			// uncomment for debugging.
			//$use_gettext = false;
		}

		return $use_gettext;
	}


	static public function getTranslationHandler() {
		if ( self::$translation_handler === NULL ) {
			if ( self::useGetTextExtension() == TRUE ) {
				//Debug::Text('Using getText()...', __FILE__, __LINE__, __METHOD__, 10);
				return new NativeGettextTranslationHandler();
			} else {
				//Debug::Text('Using Translation2...', __FILE__, __LINE__, __METHOD__, 10);
				require_once( 'Translation2.php' );

				$locale_dir = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'locale' .DIRECTORY_SEPARATOR;

				// The Translation2 Gettext example has this comment:
				//
				//	  "Better set prefetch to FALSE for the gettext container, so we don't need
				//	   to read in the whole MO file with File_Gettext on every request."
				//
				// I haven't investigated this fully yet.  So for now I am doing as they advise.
				// Probably worth checking out for performance purposes. Also, it is unclear
				// how this affects PO mode, as opposed to MO.
				//
				// NOTE: getText extension caches data within Apache itself, you must restart Apache to clear the cache.
				$params = array(
					'prefetch'			=> FALSE,
					'langs_avail_file'	=> $locale_dir . 'langs.ini',
					'domains_path_file' => $locale_dir . 'domains.ini',
					'default_domain'	=> 'messages',
					'file_type'			=> 'mo',
					'cacheDir'			=> '/tmp/timetrex/',
					'lifeTime'			=> ( 86400 * 7 ),
				);
				self::$translation_handler = Translation2::factory('gettext', $params);
				//self::$translation_handler->getDecorator('CacheLiteFunction');

				// Okay, this is super-gross, as we are modifying private data of the
				// Translation2::storage object.  Why do we do it?	Because the
				// brain-dead Translation2 api for gettext only allows specifying
				// fixed paths via domains.ini file.  Our path depends on our environment
				// and we need to tell it that.	 So, it appears we either do this nasty
				// hack, or we have to start modifying the Translation2 code directly.
				self::$translation_handler->storage->_domains = array( 'messages' => $locale_dir );
			}
		}

		return self::$translation_handler;
	}


	/*

		Locale setting functions

	*/
	static public function getLanguage() {
		return self::$language;
	}
	static public function setLanguage( $language ) {
		if ( $language == '' OR strlen( $language ) > 7 ) {
			$language = 'en';
		}

		self::$language = $language;

		return TRUE;
	}

	static public function getCountry() {
		return self::$country;
	}
	static public function setCountry( $country ) {
		if ( $country == '' OR strlen( $country ) > 7 ) {
			$country = 'US';
		}

		self::$country = $country;

		return TRUE;
	}

	static public function getLocaleArrayAsString( $locale_arr ) {
		if ( !is_array($locale_arr) ) {
			$locale_arr = (array)$locale_arr;
		}


		return implode(',', $locale_arr);
	}

	static public function tryLocale( $locale ) {
		if ( !is_array($locale) ) {
			$locale = (array)$locale;
		}

		//Try each Locale in array to see which one works.
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			//setLocale supports passing it an array, so thats easy.
			$valid_locale = setlocale( LC_ALL, $locale );
		} else {
			//Add caching here if we find this loop is too slow.
			self::getLocaleHandler(); //Include I18Nv2 files.
			$windows_locales = I18Nv2::getStaticProperty('windows');

			$valid_locale = 'en_US';
			foreach( $locale as $tmp_locale) {
				//This is setting the locale

				//Match the windows locales directly, because if we use self::getLocaleHandler()->setLocale( $tmp_locale, 'LC_ALL'); instead
				//it will actually change the locale out from under us causing strange problems, and also circumventing the locale cache
				//in setLocale() (that prevents changing to the same locale we are already in)
				//
				//On windows sometimes its using 127 digits after the decimal because after setLocale() is called, localeconv() returns
				//an array that is basically empty or uses 127 for everything.
				//This is fixed in Pear::I18Nv2->setLocale(), so it defaults to english values if it fails.
				if ( isset( $windows_locales[$tmp_locale] ) ) {
					Debug::Text('Found valid windows locale: '. $windows_locales[$tmp_locale] .' Linux locale: '. $tmp_locale, __FILE__, __LINE__, __METHOD__, 10);
					$valid_locale = $tmp_locale;
					break;
				}

				/*
				$windows_locale = self::getLocaleHandler()->setLocale( $tmp_locale, 'LC_ALL');
				if ( $windows_locale !== FALSE ) {
					Debug::Text('Found valid windows locale: '. $windows_locale .' Linux locale: '. $tmp_locale, __FILE__, __LINE__, __METHOD__, 10);
					//Returning $windows_locale appears to fix issues with some systems, but then it causes problems with language selection since its a long locale name that doens't match pt_PT for example.
					$valid_locale = $tmp_locale;
					break;
				}
				*/
			}
		}

		if ( $valid_locale != '' ) {
			//Check if the locale is the default locale, so we can more quickly determine if translation is needed or not.
			global $config_vars;
			if ( ( isset($config_vars['other']['enable_default_language_translation']) AND $config_vars['other']['enable_default_language_translation'] == TRUE )
					OR strpos( $valid_locale, 'en_US' ) === FALSE ) {
				self::$is_default_locale = FALSE;
			}
			Debug::Text('Found valid locale: '. $valid_locale .' Default: '. (int)self::$is_default_locale, __FILE__, __LINE__, __METHOD__, 11);

			return $valid_locale;
		}

		Debug::Text('FAILED TRYING LOCALE: '. self::getLocaleArrayAsString( $locale ), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	static public function generateLocale( $locale_arg = NULL ) {
		//Generate an array of possible locales to try in order.
		//1. <language>_<country>
		//2. Normalized locale.
		//3. Just Language
		//4a. If Linux then try with ".UTF8" appended to all of the above.
		//4b. If windows, let i18Nv2 normalize to windows locale names.
		if ( $locale_arg != '' AND strlen( $locale_arg ) <= 7 ) {
			$locale_arr[] = $locale_arg;
			$locale_arr[] = self::_normalizeLocale( $locale_arg );
		}
		$locale_arr[] = $locale = self::getLanguage().'_'.self::getCountry();
		$locale_arr[] = self::_normalizeLocale( $locale );
		$locale_arr[] = self::getLanguage();

		//Finally add a fallback locale that should always work, in theory.
		$locale_arr[] = 'en_US';

		$locale_arr = array_unique($locale_arr);

		if ( OPERATING_SYSTEM == 'LINUX' ) {
			//Duplicate each locale with .UTF8 appended to it to try, as some distro's like Ubuntu require this.
			$retarr = array();
			foreach( $locale_arr as $tmp_locale ) {
				$retarr[] = $tmp_locale;
				$retarr[] = $tmp_locale.'.UTF-8';
				$retarr[] = $tmp_locale.'.utf8';
			}
		} else {
			//Normalize each locale to Windows.
			$retarr = $locale_arr;
		}

		Debug::Text('Array of Locales to try in order for "'.$locale_arg.'": '. self::getLocaleArrayAsString( $retarr ), __FILE__, __LINE__, __METHOD__, 11);

		return $retarr;
	}

	static public function setMasterLocale() {
		if ( self::$master_locale != '' ) {
			return self::setLocale( self::$master_locale );
		}

		return FALSE;
	}

	static public function setLocaleCookie( $locale = NULL ) {
		if ( $locale == '' ) {
			$locale = self::getLocale();
		}

		if ( self::getLocaleCookie() != $locale ) {
			Debug::Text('Setting Locale cookie: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
			setcookie( 'language', $locale, ( time() + 9999999 ), Environment::getBaseURL() );
		}

		return TRUE;
	}
	static public function getLocaleCookie() {
		if ( isset($_COOKIE['language']) AND strlen( $_COOKIE['language'] ) <= 7 ) { //Prevent user supplied locale from attempting XSS/SQL injection.
			return $_COOKIE['language'];
		}

		return FALSE;
	}

	static public function getLanguageFromLocale( $locale = NULL ) {
		if ( $locale == '' ) {
			$locale = self::getLocale();
		}

		Debug::Text('Locale: '. $locale, __FILE__, __LINE__, __METHOD__, 11);

		$language = substr( $locale, 0, 2);
		$language_arr = self::getLanguageArray();

		if ( isset( $language_arr[$language] ) ) {
			return $language;
		}

		return FALSE;
	}

	static function getCountryFromLocale( $locale = NULL ) {
		if ( $locale == '' ) {
			$locale = self::getLocale();
		}

		$split_locale = explode('_', $locale);
		if ( isset($split_locale[2]) ) {
			return $split_locale[2];
		}

		return FALSE;
	}

	static public function getNormalizedLocale() {
		return self::$normalized_locale;
	}

	static public function getLocale() {
		return self::$locale;
	}
	static public function setLocale( $locale_arg = NULL, $category = LC_ALL ) {
		Debug::Text('Generated/Passed In Locale: '. $locale_arg, __FILE__, __LINE__, __METHOD__, 11);
		$locale = self::tryLocale( self::generateLocale( $locale_arg ) );

		Debug::Text('Attempting to set Locale(s) to: '. $locale .' Category: '. $category .' Current Locale: '. self::getLocale(), __FILE__, __LINE__, __METHOD__, 11);

		//In order to validate Windows locales with tryLocale() we have to always force the locale to be set, otherwise
		//if tryLocale() doesn't get it right on the first try, the locale is reverted to something that may not work.
		if ( $locale != self::getLocale() ) {
			if ( in_array( $category, array( LC_ALL, LC_MONETARY, LC_NUMERIC ) ) ) {
				Debug::Text('Setting currency/numeric Locale to: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
				//if ( self::getLocaleHandler()->setLocale( $locale, $category ) != $locale ) {
				//Setting the locale in Windows can cause the locale names to not match at all, so check for FALSE
				if ( self::getLocaleHandler()->setLocale( $locale, $category ) == FALSE ) {
					Debug::Text('Failed setting currency/numeric locale: '. $locale, __FILE__, __LINE__, __METHOD__, 11);
				}
			}

			if ( in_array( $category, array( LC_ALL, LC_MESSAGES ) ) ) {
				// We normalize locales to a single "standard" locale for each lang
				// to avoid having to maintain lots of mostly duplicate translation files
				// for each lang/country combination.
				$normal_locale = self::_normalizeLocale( $locale );
				Debug::Text('Setting translator to normalized locale: '. $normal_locale, __FILE__, __LINE__, __METHOD__, 11);
				if ( self::getTranslationHandler()->setLang( $normal_locale ) === FALSE ) {
					//Fall back on non-normalized locale
					Debug::Text('Failed setting translator normalized locale: '. $normal_locale .' Falling back to: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
					if ( self::getTranslationHandler()->setLang( $locale ) === FALSE ) {
						Debug::Text('Failed setting translator locale: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
						return FALSE;
					} else {
						self::$normalized_locale = $locale;
					}
				} else {
					self::$normalized_locale = $normal_locale;
				}
			}

			self::$locale = $locale;
			self::$language = substr($locale, 0, 2 );  //save language here to avoid becoming out of sync.

			if ( $category == LC_ALL ) {
				if ( self::$master_locale == NULL ) {
					self::$master_locale = $locale;
				}
			}

			Debug::Text('Set Master Locale To: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/**
	 *
	 * @param string|array $user_locale_pref
	 * @return string|boolean
	 * @author Dan Libby <dan@osc.co.cr>
	 */
	static public function getBrowserLanguage() {
		$list = array();

		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			Debug::text('HTTP_ACCEPT_LANGUAGE: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'], __FILE__, __LINE__, __METHOD__, 10);

			$accept = str_replace( ',', ';', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			$accept = str_replace( '-', '_', $accept );
			$locales = explode( ';', $accept );

			foreach( $locales as $l ) {
				if ( substr( $l, 0, 2 ) != "q=" ) {
					$list[] = $l;
				}
			}
		}

		return $list;
	}

	/**
	 * Determines the most appropriate locale, based on user metadata including
	 * the user's saved locale preference (if any), the user's browser lang pref,
	 * and the application's default locale. It also allows an override via
	 * setting URL param 'ttlang' to a valid locale.
	 *
	 * Returns the best locale, or false if unable to find and set a locale.
	 *
	 * @param string|array $user_locale_pref
	 * @return string|boolean
	 * @author Dan Libby <dan@osc.co.cr>
	 */
	static public function chooseBestLocale( $user_locale_pref = NULL) {
		Debug::text('Choosing Best Locale...', __FILE__, __LINE__, __METHOD__, 11);

		$success = FALSE;
		$category = LC_ALL; //LC_MESSAGES isn't defined on Windows.

		// First, we'll check if 'ttlang' url param (override) is specified.
		//Check cookie first, as we want GET/POST to override the cookie, incase of form errors on Login page etc...
		if ( TTi18n::getLocaleCookie() != FALSE ) {
			Debug::text('Using Language from cookie: ' . TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 11);
			$success = TTi18n::setLocale( TTi18n::getLocaleCookie(), $category );
		}

		if ( isset( $_GET['language'] ) AND $_GET['language'] != '' ) {
			Debug::text('Using Language from _GET: ' . $_GET['language'], __FILE__, __LINE__, __METHOD__, 11);
			$success = self::setLocale( $_GET['language'] );
		}

		if ( isset( $_POST['language'] ) AND $_POST['language'] != '' ) {
			Debug::text('Using Language from _POST: ' . $_POST['language'], __FILE__, __LINE__, __METHOD__, 11);
			$success = self::setLocale( $_POST['language'] );
		}

		if ( $success == FALSE ) {
			// Check for a user pref first.
			if ( $user_locale_pref != '' ) {
				// Could be an array of preferred locales.
				if ( is_array( $user_locale_pref ) ) {
					foreach( $user_locale_pref as $locale ) {
						Debug::text('aSetting Locale: ' . $user_locale_pref, __FILE__, __LINE__, __METHOD__, 11);
						if ( $success == self::setLocale( $locale, $category ) ) {
							break;
						}
					}
				} else {
					Debug::text('bSetting Locale: ' . $user_locale_pref, __FILE__, __LINE__, __METHOD__, 11);
					// or a single locale
					$success = self::setLocale( $user_locale_pref, $category );
				}
			}
		}

		// Otherwise, check for lang prefs from the browser
		if ( $success == FALSE ) {
			// browser can specify more than one, so we get an array.
			$browser_lang_prefs = self::getBrowserLanguage();
			foreach( $browser_lang_prefs as $locale ) {
				//The country code needs to be upper case for locales to work correctly.
				if ( strpos($locale, '_') !== FALSE ) {
					$split_locale = explode('_', $locale);
					if ( isset($split_locale[1]) ) {
						$locale = $split_locale[0].'_'.strtoupper($split_locale[1]);
					}
				}

				Debug::text('cSetting Locale: ' . $locale, __FILE__, __LINE__, __METHOD__, 11);
				if ( $success == self::setLocale( $locale, $category ) ) {
					break;
				}
			}
		}

		if ( $success == FALSE ) {
			global $config_vars;

			//Use system locale if its set from timetrex.ini.php
			if ( isset($config_vars['other']['system_locale']) AND $config_vars['other']['system_locale'] != '' ) {
				Debug::text('Using system locale from .ini: ' . $config_vars['other']['system_locale'], __FILE__, __LINE__, __METHOD__, 11);
				$success = self::setLocale( $config_vars['other']['system_locale'], $category );
			}
		}

		// If it worked, then we save this for future reference.
		if ( $success !== FALSE ) {
			Debug::text('Using Locale: ' . self::getLocale(), __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::text('Unable to find and set a locale.', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/*

		Language Functions

	*/
	static public function getLanguageArray() {
		require_once( 'I18Nv2/Language.php' );

		// We use self::getLanguage() because I18Nv2_Language() expects a 2 char lang code.
		$lang = new I18Nv2_Language( self::getLanguage(), 'UTF-8' );

		$retarr = $lang->getAllCodes();
		//asort($retarr);

		// Return supported languages only.
		$supported_langs = array( 'en', 'da', 'de', 'es', 'id', 'it', 'fr', 'pt', 'ar', 'zh');
		$beta_langs = array( 'da', 'de', 'es', 'id', 'it', 'fr', 'pt', 'ar', 'zh' );

		if ( PRODUCTION == FALSE ) {
			//YI is for testing only.
			$supported_langs[] = 'yi';
			$beta_langs[] = 'yi';
		}

		$retarr2 = array();
		foreach( $supported_langs as $language ) {
			if ( in_array( $language, $beta_langs ) ) {
				$retarr2[$language] = $retarr[$language] .' (UO)'; //UO = UnOfficial languages
			} else {
				$retarr2[$language] = $retarr[$language];
			}
		}

		return $retarr2;
	}

	static public function getTextStringArgs( $str, $args ) {
		if ( $args != '' ) {
			if ( !is_array($args) ) {
				$args = (array)$args;
			}

			$i = 1;
			foreach( $args as $arg ) {
				$tr['%'.$i] = $arg;

				$i++;
			}

			return strtr($str, $tr);
		}

		return $str;
	}

	static public function getText( $str, $args = FALSE ) {
		if ( $args != '' ) {
			$str = self::getTextStringArgs( $str, $args );
		}

		if ( self::$is_default_locale == TRUE ) { //Optimization: If default locale and config isn't set to enable default translation, just return the string immediately.
			return $str;
		}
		
		return self::getTranslationHandler()->get( $str );
	}

	static public function gt( $str, $args = FALSE ) {
		return self::getText( $str, $args );
	}

	/**
	* Returns a fully normalized locale string, or the original string
	* if no match was found.
	*
	* @param string $locale a locale string of the form 'es', or 'es_CR'. Both will be converted to 'es_ES'
	* @return string
	*/
	static protected function _normalizeLocale($locale) {

		static $language = array(
								'af' => 'af_ZA',	// Afrikaans	South Africa
								'am' => 'am_ET',	// Amharic	Ethiopia
								'ar' => 'ar_EG',	// Arabic Egypt
								'as' => 'as_IN',	// Assamese	India
								'az' => 'az_AZ',	// Azerbaijani	Azerbaijan
								'be' => 'be_BY',	// Belarusian	Belarus
								'bg' => 'bg_BG',	// Bulgarian	Bulgaria
								'bn' => 'bn_IN',	// Bengali	India
								'bo' => 'bo_CN',	// Tibetan	China
								'br' => 'br_FR',	// Breton	France
								'bs' => 'bs_BA',	// Bosnian	Bosnia
								'ca' => 'ca_ES',	// Catalan	Spain
								'ce' => 'ce_RU',	// Chechen	Russia
								'co' => 'co_FR',	// Corsican	France
								'cs' => 'cs_CZ',	// Czech	Czech Republic
								'cy' => 'cy_GB',	// Welsh	Britain
								'da' => 'da_DK',	// Danish	Denmark
								'de' => 'de_DE',	// German	Germany
								'dz' => 'dz_BT',	// Dzongkha	Bhutan
								'el' => 'el_GR',	// Greek	Greece
								'en' => 'en_US',	// English USA
								'es' => 'es_ES',	// Spanish	Spain
								'et' => 'et_EE',	// Estonian	Estonia
								'fa' => 'fa_IR',	// Persian	Iran
								'fi' => 'fi_FI',	// Finnish	Finland
								'fj' => 'fj_FJ',	// Fijian	Fiji
								'fo' => 'fo_FO',	// Faroese	Faeroe Islands
								'fr' => 'fr_FR',	// French	France
								'fr_CA' => 'fr_CA',		// French	Canada
								'ga' => 'ga_IE',	// Irish	Ireland
								'gd' => 'gd_GB',	// Scots	Britain
								'gu' => 'gu_IN',	// Gujarati	India
								'he' => 'he_IL',	// Hebrew	Israel
								'hi' => 'hi_IN',	// Hindi	India
								'hr' => 'hr_HR',	// Croatian	Croatia
								'hu' => 'hu_HU',	// Hungarian	Hungary
								'hy' => 'hy_AM',	// Armenian	Armenia
								'id' => 'id_ID',	// Indonesian	Indonesia
								'is' => 'is_IS',	// Icelandic	Iceland
								'it' => 'it_IT',	// Italian	Italy
								'ja' => 'ja_JP',	// Japanese	Japan
								'jv' => 'jv_ID',	// Javanese	Indonesia
								'ka' => 'ka_GE',	// Georgian	Georgia
								'kk' => 'kk_KZ',	// Kazakh	Kazakhstan
								'kl' => 'kl_GL',	// Kalaallisut	Greenland
								'km' => 'km_KH',	// Khmer	Cambodia
								'kn' => 'kn_IN',	// Kannada	India
								'ko' => 'ko_KR',	// Korean	Korea (South)
								'ko' => 'kok_IN',	// Konkani	India
								'lo' => 'lo_LA',	// Laotian	Laos
								'lt' => 'lt_LT',	// Lithuanian	Lithuania
								'lv' => 'lv_LV',	// Latvian	Latvia
								'mg' => 'mg_MG',	// Malagasy	Madagascar
								'mk' => 'mk_MK',	// Macedonian	Macedonia
								'ml' => 'ml_IN',	// Malayalam	India
								'mn' => 'mn_MN',	// Mongolian	Mongolia
								'mr' => 'mr_IN',	// Marathi	India
								'ms' => 'ms_MY',	// Malay	Malaysia
								'mt' => 'mt_MT',	// Maltese	Malta
								'my' => 'my_MM',	// Burmese	Myanmar
								'mn' => 'mni_IN',	// Manipuri	India
								'na' => 'na_NR',	// Nauru	Nauru
								'nb' => 'nb_NO',	// Norwegian Bokml	Norway
								'ne' => 'ne_NP',	// Nepali	Nepal
								'nl' => 'nl_NL',	// Dutch	Netherlands
								'nn' => 'nn_NO',	// Norwegian Nynorsk	Norway
								'no' => 'no_NO',	// Norwegian	Norway
								'oc' => 'oc_FR',	// Occitan	France
								'or' => 'or_IN',	// Oriya	India
								'pa' => 'pa_IN',	// Punjabi	India
								'pl' => 'pl_PL',	// Polish	Poland
								'ps' => 'ps_AF',	// Pashto	Afghanistan
								'pt' => 'pt_PT',	// Portuguese	Portugal
								'pt_BR' => 'pt_BR',		// Portuguese	Brazilian
								'rm' => 'rm_CH',	// Rhaeto-Roman	Switzerland
								'rn' => 'rn_BI',	// Kirundi	Burundi
								'ro' => 'ro_RO',	// Romanian	Romania
								'ru' => 'ru_RU',	// Russian	Russia
								'sa' => 'sa_IN',	// Sanskrit	India
								'sc' => 'sc_IT',	// Sardinian	Italy
								'sg' => 'sg_CF',	// Sango	Central African Rep.
								'si' => 'si_LK',	// Sinhalese	Sri Lanka
								'sk' => 'sk_SK',	// Slovak	Slovakia
								'sl' => 'sl_SI',	// Slovenian	Slovenia
								'so' => 'so_SO',	// Somali	Somalia
								'sq' => 'sq_AL',	// Albanian	Albania
								'sr' => 'sr_YU',	// Serbian	Yugoslavia
								'sv' => 'sv_SE',	// Swedish	Sweden
								'te' => 'te_IN',	// Telugu	India
								'tg' => 'tg_TJ',	// Tajik	Tajikistan
								'th' => 'th_TH',	// Thai		Thailand
								'tk' => 'tk_TM',	// Turkmen	Turkmenistan
								'tl' => 'tl_PH',	// Tagalog	Philippines
								'to' => 'to_TO',	// Tonga	Tonga
								'tr' => 'tr_TR',	// Turkish	Turkey
								'uk' => 'uk_UA',	// Ukrainian	Ukraine
								'ur' => 'ur_PK',	// Urdu		Pakistan
								'uz' => 'uz_UZ',	// Uzbek	Uzbekistan
								'vi' => 'vi_VN',	// Vietnamese	Vietnam
								'wa' => 'wa_BE',	// Walloon	Belgium
								'we' => 'wen_DE',	// Sorbian	Germany
								'zh' => 'zh_CN',	// Chinese Simplified

								//Test locale to make sure all strings are translated.
								'yi' => 'yi_US',
								);


		//Using .UTF-8 fails using Translation2 or Windows. Perhaps we attempt to add this later if it fails on the first try.
		if ( isset($language[$locale]) ) { //Check for full language first for cases where the same language has different translations for different countries (ie: France/Canada)
			return $language[$locale];
		} else {
			$lang = trim( substr( $locale, 0, 2 ) );
			if ( isset($language[$lang]) ) {
				return $language[$lang];// . '.UTF-8';	// setlocale fails on ubuntu if UTF-8 not specified
			}
		}

		return $locale;
	}

	//Returns PDF font appropriate for language.
	static function getPDFDefaultFont( $language = NULL, $encoding = FALSE ) {
		if ( $language == '' ) {
			$language = self::getLanguage();
		}

		//Helvetica is a PDF core font that should always work.
		//But does it not support many unicode characters?
		if ( $language == 'en' AND ( $encoding == '' OR $encoding == FALSE OR $encoding == 'ISO-8859-1' ) ) {
			return 'helvetica'; //Core PDF font, works with setFontSubsetting(TRUE) and is fast with small PDF sizes.
		}

		Debug::text('Using international font: freeserif', __FILE__, __LINE__, __METHOD__, 10);
		return 'freeserif'; //Slow with setFontSubsetting(TRUE), produces PDFs at least 1mb.
	}

	/*

		Number/Currency functions

	*/
	static public function getCurrencyArray() {
		require_once('I18Nv2/Currency.php');

		$c = new I18Nv2_Currency();

		$code_arr = $c->getAllCodes();

		foreach( $code_arr as $iso_code => $name ) {
			$retarr[$iso_code] = ''.$iso_code.' - '. $name;
		}
		
		//Add support for Bitcoin (XBT)
		$retarr['XBT'] = TTi18n::getText('XBT').' - '. TTi18n::getText('Bitcoin');

		return $retarr;
	}

	static public function formatNumber( $number, $auto_format_decimals = FALSE, $min_decimals = 2, $max_decimals = 4 ) {
		if ( $auto_format_decimals == TRUE ) {
			$number = Misc::removeTrailingZeros( $number, $min_decimals );
			$decimal_places = strlen( Misc::getAfterDecimal( $number, FALSE ) );
			if ( $decimal_places > $max_decimals ) {
				$decimal_places = $max_decimals;
			} elseif ( $decimal_places < $min_decimals ) {
				$decimal_places = $min_decimals;
			}

			if ( isset(self::getLocaleHandler()->numberFormats[I18Nv2_NUMBER_FLOAT]) ) {
				$custom_format = self::getLocaleHandler()->numberFormats[I18Nv2_NUMBER_FLOAT];
			} else {
				$custom_format = array(
									'float' => array(
														0 => 2,
														1 => '.',
														2 => ', ',
													)
									);
			}

			$custom_format[0] = $decimal_places;
			self::getLocaleHandler()->numberFormats['long_float'] = $custom_format;

			return self::getLocaleHandler()->formatNumber( $number, 'long_float' );
		} else {
			return self::getLocaleHandler()->formatNumber( $number );
		}
	}

	//
	// Show Code: 0 = No, 1 = Left, 2 = Right
	//
	static public function formatCurrency( $amount, $currency_code = NULL, $show_code = 0 ) {
		$currency_code_left_str = NULL;
		$currency_code_right_str = NULL;

		if ( $show_code != 0 ) {
			if ( is_object( $currency_code ) ) {
				//CurrencyFactory Object, grab ISO code for this.
				$currency_code = $currency_code->getISOCode();
			}
		}

		if ( !is_object($currency_code) AND $show_code != 0 AND $currency_code != '' ) {
			if ( $show_code == 1 ) {
				$currency_code_left_str = $currency_code .' ';
			} elseif ( $show_code == 2 ) {
				$currency_code_right_str = ' '. $currency_code;
			}
		}

		return $currency_code_left_str . self::getLocaleHandler()->formatCurrency( $amount, I18Nv2_CURRENCY_LOCAL ) . $currency_code_right_str;
		//return $currency_code . self::getLocaleHandler()->formatCurrency( $amount, I18Nv2_CURRENCY_INTERNATIONAL );
	}

	static function getCurrencySymbol( $iso_code ) {
		static $currency_symbols = array (
										'AED' => 'د.إ', //('United Arab Emirates')
										'AFA' => 'نی', //('Afghanistan')
										'ALL' => 'Lek', //('Albania')
										'AMD' => 'դր.', // ('Armenia')
										'ANG' => 'ƒ', //('Netherlands Antilles')
										'AON' => 'Kz', //('Angola')
										'ARA' => '$', //('Argentina'),
										'AUD' => '$', //('Australia')('Christmas Island')('Cocos (Keeling) Islands')('Heard Island and Mcdonald Islands')('Kiribati')('Nauru')('Tuvalu')
										'AWG' => 'ƒ', //('Aruba')
										'AZM' => 'm', //('Azerbaijan')
										'BAM' => 'KM', //('Bosnia and Herzegovina')
										'BBD' => '$', //('Barbados')
										'BDT' => 'Tk', //('Bangladesh')
										'BGL' => 'лв', //('Bulgaria')
										'BHD' => 'دج', //('Bahrain')
										'BIF' => '₣', //('Burundi')
										'BMD' => '$', //('Bermuda')
										'BND' => '$', //('Brunei Darussalam')
										'BOB' => '$b', //('Bolivia')
										'BRR' => 'R$', //('Brazil')
										'BSD' => '$', //('Bahamas')
										'BTN' => 'Nu', //('Bhutan')
										'BWP' => 'P', //('Botswana')
										'BYR' => 'p.', //('Belarus')
										'BZD' => 'BZ$', //('Belize')
										'CAD' => '$', //('Canada')
										'CDF' => 'F', //('Congo, Democratic Republic')
										'CDZ' => 'CDZ', //('Congo, the Democratic Republic of')
										'CHF' => '₣', //('Liechtenstein')('Switzerland')
										'CLF' => '$', //('Chile')
										'CNY' => '¥', //('China')
										'COP' => '$', //('Colombia')
										'CRC' => '₡', //Costa Rica
										'CSD' => 'دج', //('Serbia and Montenegro')
										'CUP' => '$', //('Cuba')
										'CVE' => '$', //('Cape Verde')
										'CYP' => '£', //('Cyprus')
										'CZK' => 'Kč', //('Czech Republic')
										'DJF' => '₣', //('Djibouti')
										'DKK' => 'kr', //('Denmark')('Faroe Islands')('Greenland')
										'DOP' => 'RD$', //('Dominican Republic')
										'DZD' => 'دج', //('Algeria')
										'EEK' => 'kr', //('Estonia')
										'EGP' => '£', //('Egypt')
										'ERN' => 'Nfk', //('Eritrea')
										'ETB' => 'Br', //('Ethiopia')
										'EUR' => '€', //('Germany')('Andorra')('Austria')('Belgium')('Finland')('France')('Greece')('French Guiana')('French Southern Territories')('Guadeloupe')('Holy See (Vatican City State)')('Ireland')('Italy')('Luxembourg')('Martinique')('Mayotte')('Monaco')('Netherlands')('Portugal')('Reunion')('San Marino')('Spain')('Saint Pierre and Miquelon')
										'FJD' => '$', //'Fiji')
										'FKP' => '£', //('Falkland Islands (Malvinas)')
										'GBP' => '£', //('United Kingdom')('British Indian Ocean Territory')('South Georgia, South Sandwich Islands')
										'GEL' => '$', //('Georgia')
										'GHC' => '₵', //('Ghana')
										'GIP' => '£', //('Gibraltar')
										'GMD' => 'D', //('Gambia')
										'GNS' => '$', //('Guinea')
										'GTQ' => 'Q', //('Guatemala')
										'GWP' => '$', //('Guinea-Bissau')
										'GYD' => '$', //('Guyana')
										'HKD' => 'HK$', //('Hong Kong')
										'HNL' => 'L', //('Honduras')
										'HRK' => 'kn', //('Croatia')
										'HTG' => 'G', //('Haiti')
										'HUF' => 'Ft', //('Hungary')
										'IDR' => 'Rp', //('Indonesia')
										'ILS' => '₪', //('Israel')
										'INR' => '₨', //('India')
										'IQD' => 'ع.د', //('Iraq')
										'IRR' => '﷼', //('Iran, Islamic Republic of')
										'ISK' => 'kr', //('Iceland'),
										'JMD' => 'J$', //('Jamaica')
										'JOD' => 'ع.د', //('Jordan')
										'JPY' => '¥', //('Japan')
										'KES' => 'KSh', //('Kenya')
										'KGS' => 'лв', //('Kyrgyzstan')
										'KHR' => '$', //('Cambodia')
										'KMF' => '₣', //('Comoros')
										'KPW' => '₩', //('Korea, Democratic People\'s Republic of')
										'KRW' => '₩', //('Korea, Republic of')
										'KWD' => 'د.ك', //('Kuwait')
										'KYD' => '$', //('Cayman Islands')
										'KZT' => 'лв', //('Kazakhstan')
										'LAK' => '₭', //('Lao People\'s Democratic Republic')
										'LBP' => '£', //('Lebanon')
										'LKR' => '₨', //('Sri Lanka')
										'LRD' => '$', //('Liberia')
										'LSL' => 'L', //('Lesotho')
										'LTL' => 'Lt', //('Lithuania')
										'LVL' => 'Ls', //('Latvia')
										'LYD' => 'ل.د', //('Libyan Arab Jamahiriya')
										'MAD' => 'د.م', //('Morocco')('Western Sahara')
										'MDL' => 'L', //('Moldova, Republic of')
										'MGF' => '₣', //('Madagascar')
										'MKD' => 'ден', //('Macedonia, Former Yugoslav Republic of')
										'MMK' => 'K', //('Myanmar')
										'MNT' => '₮', //('Mongolia')
										'MOP' => 'MOP$', //('Macao')
										'MRO' => 'UM', //('Mauritania')
										'MTL' => 'Lm', //('Malta')
										'MUR' => '₨', //('Mauritius')
										'MVR' => 'Rf', //('Maldives')
										'MWK' => 'MK', //('Malawi')
										'MXN' => '$', //('Mexico')
										'MYR' => 'RM', //('Malaysia')
										'MZM' => 'MTn', //('Mozambique')
										'NAD' => '$', //('Namibia')
										'NGN' => '₦', //('Nigeria')
										'NIC' => 'C$', //('Nicaragua')
										'NOK' => 'kr', //('Antarctica')('Bouvet Island')('Norway')('Svalbard and Jan Mayen')
										'NPR' => '₨', //('Nepal')
										'NZD' => '$', //('New Zealand')
										'OMR' => '﷼', //('Oman')
										'PAB' => '$', //('Panama')
										'PEI' => 'I/.', //('Peru')
										'PGK' => 'K', //('Papua New Guinea')
										'PHP' => 'Php', //('Philippines')
										'PKR' => '₨', //('Pakistan')
										'PLN' => 'zł', //('Poland')
										'PYG' => 'Gs', //('Paraguay')
										'QAR' => '﷼', //('Qatar')
										'ROL' => 'L', //('Romania')
										'RUB' => 'руб', //('Russian Federation')
										'RWF' => '₣', //('Rwanda')
										'SAR' => '﷼', //('Saudi Arabia')
										'SBD' => '$', //('Solomon Islands')
										'SCR' => '₨', //('Seychelles')
										'SDP' => '£Sd', //('Sudan')
										'SEK' => 'kr', //('Sweden')
										'SGD' => '$', //('Singapore')
										'SHP' => '£', //('Saint Helena')
										'SIT' => 'SIT', //('Slovenia')
										'SKK' => 'kr', //('Slovakia')
										'SLL' => 'Le', //('Sierra Leone')
										'SOS' => 'S', //('Somalia')
										'SRG' => 'ƒ', //('Suriname')
										'STD' => 'Db', //('Sao Tome and Principe')
										'SUR' => 'руб',
										'SVC' => '₡', //('El Salvador')
										'SYP' => '£', //('Syrian Arab Republic')
										'SZL' => 'L', //('Swaziland')
										'THB' => '฿', //('Thailand')
										'TMM' => 'm', //('Turkmenistan')
										'TND' => 'د.ت', //('Tunisia')
										'TOP' => 'T$', //('Tonga')
										'TPE' => '$',
										'TRL' => '₤', //('Turkey')
										'TTD' => '$', //('Trinidad and Tobago')
										'TWD' => '$', //('Taiwan, Province of China')
										'TZS' => 'x/y', //('Tanzania, United Republic of')
										'UAH' => '₴', //('Ukraine')
										'UGS' => 'USh', //('Uganda')
										'USD' => '$',	 //('United States')('American Samoa')('Ecuador')('Guam')('Marshall Islands')('Micronesia, Federated States of')('Northern Mariana Islands')('Palau')('Puerto Rico')('Turks and Caicos Islands')('United States Minor Outlying Islands')('Virgin Islands, British')('Virgin Islands, U.s.')
										'UYU' => '$U', //('Uruguay')
										'UZS' => 'лв', //('Uzbekistan')
										'VEB' => 'Bs', //('Venezuela')
										'VND' => '₫', //('Viet Nam')
										'VUV' => 'Vt', //('Vanuatu')
										'WST' => 'WS$', //('Samoa')
										'XAF' => '₣', //('Benin')
										'XCD' => '$', //('Anguilla')('Antigua and Barbuda')('Dominica')('Grenada')('Montserrat')('Saint Kitts and Nevis')('Saint Lucia')('Saint Vincent, Grenadines')
										'XOF' => '₣', //('Niger')('Senegal')
										'XPF' => '₣', //('Wallis and Futuna')('French Polynesia')('New Caledonia')
										'YER' => '﷼', //('Yemen')
										'ZAR' => 'R', //('South Africa')
										'ZMK' => 'ZK', //('Zambia')
										'ZMW' => 'ZK', //('Zambia') new as of August 2012
										'ZWD' => 'Z$', //('Zimbabwe')
								);

		if ( isset($currency_symbols[$iso_code]) ) {
			return($currency_symbols[$iso_code]);
		}

		return '$';
	}

	static function detectUTF8($string) {
			return preg_match('%(?:
			[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
			|\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
			|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
			|\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
			|\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
			|[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
			|\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
			)+%xs', $string);
	}

	/*

		Date Functions

	*/
}


/**
 * Generic interface for a translation handler.
 * This confirms to the interface for Translation2, and
 * let's us easily mix and match Translation2 with native
 * gettext.
 * @package Core
 */
interface TranslationHandler {
	// Set the locale to use for LC_MESSAGES translations
	// Returns TRUE | FALSE
	function setLang( $locale );

	// Returns translated string.
	function get( $str );
}


/**
 * @package Core
 */
class NativeGettextTranslationHandler implements TranslationHandler {

	function setLang( $locale ) {

		// Beware: this is changing the locale process-wide.
		// But *only* for LC_MESSAGES, not other LC_*.
		// This is not thread-safe.	 For threaded web servers,
		// the slower Translation2 classes should be used.

		//Setting the locale again here overrides what i18Nv2 just set
		//breaking Windows. However not setting it breaks some Linux distro's.
		//Because apparently LC_ALL doesn't matter on some Unix, it still doesn't set LC_MESSAGES.
		//So if we didn't explicity set LC_MESSAGES above, do it here.
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			$rc = setlocale( LC_MESSAGES, $locale.'.UTF-8' );

			/* This often reports failure even if it works.
			if ( $rc == 0 ) {
				Debug::text('setLocale failed!: '. (int)$rc .' Locale: '. $locale, __FILE__, __LINE__, __METHOD__, 10);
			}
			*/
		}

		// Normally, setting env var(s) would not be necessary, but I18Nv2
		// is explicitly setting LANG* env variables, which seem to be
		// overriding setlocale(). Setting the env var here, fixes it.
		// Yes, I know it seems backwards.	YMMV.
		@putEnv('LANGUAGE=' . $locale);

		$domain = 'messages';

		//This fixes the mysterious issue of the "sticky locale". Where PHP
		//wouldn't change locales half way through a script.
		textdomain( $domain );

		// Tell gettext where to find the locale translation files.
		bindtextdomain( $domain, Environment::getBasePath() . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'locale');

		// Tell gettext which codeset to use for output.
		bind_textdomain_codeset( $domain, 'UTF-8');

		return TRUE;
	}

	function get( $str ) {
		return gettext( $str );
	}
}

?>
