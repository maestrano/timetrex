#!/usr/bin/php
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
 * File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
 * http://osc.co.cr
 */

// creates the locale directories for use with gettext 
// and also initializes each with a messages.po file.
// Must be run from the i18n tools directory
//

$depth = '../..';

$locales = array(

//	'af_ZA',
//	'am_ET',
//	'as_IN',
//	'az_AZ',
//	'be_BY',
//	'bg_BG',
//	'bn_IN',
//	'bo_CN',
//	'br_FR',
//	'bs_BA',
//	'ca_ES',
//	'ce_RU',
//	'co_FR',
//	'cs_CZ',
//	'cy_GB',
	'da_DK',
	'de_DE',
//	'dz_BT',
//	'el_GR',
	'en_US',
	'es_ES',
//	'et_EE',
//	'fa_IR',
//	'fi_FI',
//	'fj_FJ',
//	'fo_FO',
	'fr_FR',
	'fr_CA',
//	'ga_IE',
//	'gd_GB',
//	'gu_IN',
//	'he_IL',
//	'hi_IN',
//	'hr_HR',
//	'hu_HU',
//	'hy_AM',
	'id_ID',
// 'is_IS',
	'it_IT',
//	'ja_JP',
//	'jv_ID',
//	'ka_GE',
//	'kk_KZ',
//	'kl_GL',
//	'km_KH',
//	'kn_IN',
//	'ko_KR',
//	'kok_IN',
//	'lo_LA',
//	'lt_LT',
//	'lv_LV',
//	'mg_MG',
//	'mk_MK',
//	'ml_IN',
//	'mn_MN',
//	'mr_IN',
//	'ms_MY',
//	'mt_MT',
//	'my_MM',
//	'mni_IN',
//	'na_NR',
//	'nb_NO',
//	'ne_NP',
//	'nl_NL',
//	'nn_NO',
//	'no_NO',
//	'oc_FR',
//	'or_IN',
//	'pa_IN',
//	'pl_PL',
//	'ps_AF',
	'pt_PT',
	'pt_BR',
//	'rm_CH',
//	'rn_BI',
//	'ro_RO',
//	'ru_RU',
//	'sa_IN',
//	'sc_IT',
//	'sg_CF',
//	'si_LK',
//	'sk_SK',
//	'sl_SI',
//	'so_SO',
//	'sq_AL',
//	'sr_YU',
//	'sv_SE',
//	'te_IN',
//	'tg_TJ',
//	'th_TH',
//	'tk_TM',
//	'tl_PH',
//	'to_TO',
//	'tr_TR',
//	'uk_UA',
//	'ur_PK',
//	'uz_UZ',
//	'vi_VN',
//	'wa_BE',
//	'wen_DE',
//	'lp_SG',
	'zh_ZH',
	'yi_US',
);

$dir = $depth . '/interface/locale';
chdir( $dir );

foreach( $locales as $locale ) {
	if ( !is_dir( './' . $locale ) ) {
		$cmd = "mkdir $locale && mkdir $locale/LC_MESSAGES && msginit --no-translator -l $locale -o $locale/LC_MESSAGES/messages.po -i messages.pot";
		shell_exec( $cmd );
	}
}
?>
