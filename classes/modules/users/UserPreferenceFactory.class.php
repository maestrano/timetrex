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
 * @package Modules\Users
 */
class UserPreferenceFactory extends Factory {
	protected $table = 'user_preference';
	protected $pk_sequence_name = 'user_preference_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	function _getFactoryOptions( $name, $include_sort_prefix = FALSE ) {

		$retval = NULL;
		switch( $name ) {

			// I18n: No need to use gettext because these options only appear for english.
			case 'date_format':
				$retval = array(
											'd-M-y'		=> TTi18n::gettext('25-Feb-01 (dd-mmm-yy)'),
											'd-M-Y'		=> TTi18n::gettext('25-Feb-2001 (dd-mmm-yyyy)'),
											//PHP 5.1.2 fails to parse these with strtotime it looks like
											//'d/M/y'	=> '25/Feb/01 (dd/mmm/yy)',
											//'d/M/Y'	=> '25/Feb/2001 (dd/mmm/yyyy)',
											'dMY'		=> TTi18n::gettext('25Feb2001 (ddmmmyyyy)'),
											'd/m/Y'		=> '25/02/2001 (dd/mm/yyyy)',
											'd/m/y'		=> '25/02/01 (dd/mm/yy)',
											'd-m-y'		=> '25-02-01 (dd-mm-yy)',
											'd-m-Y'		=> '25-02-2001 (dd-mm-yyyy)',
											'm/d/y'		=> '02/25/01 (mm/dd/yy)',
											'm/d/Y'		=> '02/25/2001 (mm/dd/yyyy)',
											'm-d-y'		=> '02-25-01 (mm-dd-yy)',
											'm-d-Y'		=> '02-25-2001 (mm-dd-yyyy)',
											'Y-m-d'		=> '2001-02-25 (yyyy-mm-dd)',
											//'Ymd'			=> '20010225 (yyyymmdd)', //This can't be parsed properly due to all integer values, parseDateTime() thinks its an epoch.
											'M-d-y'		=> TTi18n::gettext('Feb-25-01 (mmm-dd-yy)'),
											'M-d-Y'		=> TTi18n::gettext('Feb-25-2001 (mmm-dd-yyyy)'),
											'l, F d Y'	=> TTi18n::gettext('Sunday, February 25 2001'),
											'D, F d Y'	=> TTi18n::gettext('Sun, February 25 2001'),
											'D, M d Y'	=> TTi18n::gettext('Sun, Feb 25 2001'),
											'D, d-M-Y'	=> TTi18n::gettext('Sun, 25-Feb-2001'),
											'D, dMY'	=> TTi18n::gettext('Sun, 25Feb2001')
									);

				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			// I18n: We use fewer calendar options for non-en langs, as otherwise strtotime chokes.
			case 'other_date_format':
				$retval = array(
											'd/m/Y'		=> '25/02/2001 (dd/mm/yyyy)',
											'd/m/y'		=> '25/02/01 (dd/mm/yy)',
											'd-m-y'		=> '25-02-01 (dd-mm-yy)',
											'd-m-Y'		=> '25-02-2001 (dd-mm-yyyy)',
											'm/d/y'		=> '02/25/01 (mm/dd/yy)',
											'm/d/Y'		=> '02/25/2001 (mm/dd/yyyy)',
											'm-d-y'		=> '02-25-01 (mm-dd-yy)',
											'm-d-Y'		=> '02-25-2001 (mm-dd-yyyy)',
											'Y-m-d'		=> '2001-02-25 (yyyy-mm-dd)',
									);

				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'moment_date_format':
				$retval = array(
											'D, F d Y'   => 'ddd, MMMM DD YYYY',
											'D, M d Y'   => 'ddd, MMM DD YYYY',
											'D, d-M-Y'   => 'ddd, DD-MMM-YYYY',
											'D, dMY'     => 'ddd, DDMMMYYYY',
											'M-d-Y'      => 'MMM-DD-YYYY',
											'M-d-y'      => 'MMM-DD-YY',
											'Y-m-d'      => 'YYYY-MM-DD',
											'd-M-Y'      => 'DD-MMM-YYYY',
											'd-M-y'      => 'DD-MMM-YY',
											'd-m-Y'      => 'DD-MM-YYYY',
											'd-m-y'      => 'DD-MM-YY',
											'd/m/Y'      => 'DD/MM/YYYY',
											'd/m/y'      => 'DD/MM/YY',
											'dMY'        => 'DDMMMYYYY',
											'l, F d Y'   => 'dddd, MMMM DD YYYY',
											'm-d-Y'      => 'MM-DD-YYYY',
											'm-d-y'      => 'MM-DD-YY',
											'm/d/Y'      => 'MM/DD/YYYY',
											'm/d/y'      => 'MM/DD/YY'
								);
				break;
			case 'time_format':
				$retval = array(
											//'g:i:s A'		=> TTi18n::gettext('8:09:11 PM'),
											'g:i A'		=> TTi18n::gettext('8:09 PM'),
											//'g:i:s a'		=> TTi18n::gettext('8:09:11 pm'),
											'g:i a'		=> TTi18n::gettext('8:09 pm'),
											//'G:i:s'	=> TTi18n::gettext('20:09:11'),
											'G:i'		=> TTi18n::gettext('20:09'),
											'g:i A T'	=> TTi18n::gettext('8:09 PM GMT'),
											'G:i T'		=> TTi18n::gettext('20:09 GMT'),
											//'Gis'			=> TTi18n::gettext('200959'), //This sorts to the top
									);
				break;
			case 'moment_time_format':
				$retval = array(

											'g:i A'		=> 'hh:mm A',
											'g:i a'		=> 'hh:mm a',
											'G:i'		  => 'HH:mm',
											'g:i A T'	  => 'hh:mm A Z',
											'G:i T'		=> 'HH:mm Z'
											);
				break;
			case 'date_time_format':
				//Merge Date and Time formats together.
				$date_formats = $this->getOptions('date_format');
				$time_formats = $this->getOptions('time_format');
				if ( is_array($date_formats) AND is_array($time_formats) ) {
					foreach( $date_formats as $date_format => $date_format_name ) {
						foreach( $time_formats as $time_format => $time_format_name ) {
							//Use "|" as a separate so we can later split them back into separate date/time formats.
							$retval[$date_format.'_'.$time_format] = trim(preg_replace('/\(.*\)/i', '', $date_format_name )).' '. $time_format_name;
						}
					}
				}
				break;
			case 'time_unit_format':
				$retval = array(
											10	=> TTi18n::gettext('hh:mm (2:15)'),
											12	=> TTi18n::gettext('hh:mm:ss (2:15:59)'),
											20	=> TTi18n::gettext('Hours (2.25)'),
											22	=> TTi18n::gettext('Hours (2.141)'),
											23	=> TTi18n::gettext('Hours (2.3587)'),
											30	=> TTi18n::gettext('Minutes (135)'),
											40  => TTi18n::gettext('Seconds (3600)'),
									);
				break;

			// I18n: These timezones probably should be translated, but doing so would add ~550
			//		 lines to the translator's workload for each lang.	And these are hard to translate.
			//		 Probably better to use an already translated timezone class, if one exists.
			//
			//Commented out timezones do not work in PostgreSQL 8.2, as they hardcode timezone data into versions.
			case 'time_zone':
				$retval = array(

											'Africa/Abidjan' => 'Africa/Abidjan',
											'Africa/Accra' => 'Africa/Accra',
											'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
											'Africa/Algiers' => 'Africa/Algiers',
											'Africa/Asmera' => 'Africa/Asmera',
											'Africa/Bamako' => 'Africa/Bamako',
											'Africa/Bangui' => 'Africa/Bangui',
											'Africa/Banjul' => 'Africa/Banjul',
											'Africa/Bissau' => 'Africa/Bissau',
											//'Africa/Blantyre' => 'Africa/Blantyre',
											'Africa/Brazzaville' => 'Africa/Brazzaville',
											//'Africa/Bujumbura' => 'Africa/Bujumbura',
											'Africa/Cairo' => 'Africa/Cairo',
											'Africa/Casablanca' => 'Africa/Casablanca',
											'Africa/Ceuta' => 'Africa/Ceuta',
											'Africa/Conakry' => 'Africa/Conakry',
											'Africa/Dakar' => 'Africa/Dakar',
											'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
											'Africa/Djibouti' => 'Africa/Djibouti',
											'Africa/Douala' => 'Africa/Douala',
											'Africa/El_Aaiun' => 'Africa/El_Aaiun',
											'Africa/Freetown' => 'Africa/Freetown',
											//'Africa/Gaborone' => 'Africa/Gaborone',
											//'Africa/Harare' => 'Africa/Harare',
											'Africa/Johannesburg' => 'Africa/Johannesburg',
											'Africa/Kampala' => 'Africa/Kampala',
											'Africa/Khartoum' => 'Africa/Khartoum',
											//'Africa/Kigali' => 'Africa/Kigali',
											'Africa/Kinshasa' => 'Africa/Kinshasa',
											'Africa/Lagos' => 'Africa/Lagos',
											'Africa/Libreville' => 'Africa/Libreville',
											'Africa/Lome' => 'Africa/Lome',
											'Africa/Luanda' => 'Africa/Luanda',
											//'Africa/Lubumbashi' => 'Africa/Lubumbashi',
											//'Africa/Lusaka' => 'Africa/Lusaka',
											'Africa/Malabo' => 'Africa/Malabo',
											//'Africa/Maputo' => 'Africa/Maputo',
											'Africa/Maseru' => 'Africa/Maseru',
											'Africa/Mbabane' => 'Africa/Mbabane',
											'Africa/Mogadishu' => 'Africa/Mogadishu',
											'Africa/Monrovia' => 'Africa/Monrovia',
											'Africa/Nairobi' => 'Africa/Nairobi',
											'Africa/Ndjamena' => 'Africa/Ndjamena',
											'Africa/Niamey' => 'Africa/Niamey',
											'Africa/Nouakchott' => 'Africa/Nouakchott',
											'Africa/Ouagadougou' => 'Africa/Ouagadougou',
											'Africa/Porto-Novo' => 'Africa/Porto-Novo',
											'Africa/Sao_Tome' => 'Africa/Sao_Tome',
											'Africa/Timbuktu' => 'Africa/Timbuktu',
											'Africa/Tripoli' => 'Africa/Tripoli',
											'Africa/Tunis' => 'Africa/Tunis',
											'Africa/Windhoek' => 'Africa/Windhoek',
											//'America/Adak' => 'America/Adak',
											'America/Anchorage' => 'America/Anchorage',
											'America/Anguilla' => 'America/Anguilla',
											'America/Antigua' => 'America/Antigua',
											'America/Araguaina' => 'America/Araguaina',
											'America/Aruba' => 'America/Aruba',
											'America/Asuncion' => 'America/Asuncion',
											//'America/Atka' => 'America/Atka',
											'America/Barbados' => 'America/Barbados',
											'America/Belem' => 'America/Belem',
											'America/Belize' => 'America/Belize',
											'America/Boa_Vista' => 'America/Boa_Vista',
											'America/Bogota' => 'America/Bogota',
											'America/Boise' => 'America/Boise',
											'America/Buenos_Aires' => 'America/Buenos_Aires',
											'America/Cambridge_Bay' => 'America/Cambridge_Bay',
											'America/Cancun' => 'America/Cancun',
											'America/Caracas' => 'America/Caracas',
											'America/Catamarca' => 'America/Catamarca',
											'America/Cayenne' => 'America/Cayenne',
											'America/Cayman' => 'America/Cayman',
											'America/Chicago' => 'America/Chicago',
											'America/Chihuahua' => 'America/Chihuahua',
											'America/Cordoba' => 'America/Cordoba',
											'America/Costa_Rica' => 'America/Costa_Rica',
											'America/Cuiaba' => 'America/Cuiaba',
											'America/Curacao' => 'America/Curacao',
											'America/Danmarkshavn' => 'America/Danmarkshavn',
											'America/Dawson' => 'America/Dawson',
											'America/Dawson_Creek' => 'America/Dawson_Creek',
											'America/Denver' => 'America/Denver',
											'America/Detroit' => 'America/Detroit',
											'America/Dominica' => 'America/Dominica',
											'America/Edmonton' => 'America/Edmonton',
											'America/Eirunepe' => 'America/Eirunepe',
											'America/El_Salvador' => 'America/El_Salvador',
											'America/Ensenada' => 'America/Ensenada',
											'America/Fort_Wayne' => 'America/Fort_Wayne',
											'America/Fortaleza' => 'America/Fortaleza',
											'America/Glace_Bay' => 'America/Glace_Bay',
											'America/Godthab' => 'America/Godthab',
											'America/Goose_Bay' => 'America/Goose_Bay',
											'America/Grand_Turk' => 'America/Grand_Turk',
											'America/Grenada' => 'America/Grenada',
											'America/Guadeloupe' => 'America/Guadeloupe',
											'America/Guatemala' => 'America/Guatemala',
											//'America/Guayaquil' => 'America/Guayaquil',
											'America/Guyana' => 'America/Guyana',
											'America/Halifax' => 'America/Halifax',
											'America/Havana' => 'America/Havana',
											'America/Hermosillo' => 'America/Hermosillo',
											'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
											'America/Indiana/Knox' => 'America/Indiana/Knox',
											'America/Indiana/Marengo' => 'America/Indiana/Marengo',
											'America/Indiana/Vevay' => 'America/Indiana/Vevay',
											'America/Indianapolis' => 'America/Indianapolis',
											'America/Inuvik' => 'America/Inuvik',
											'America/Iqaluit' => 'America/Iqaluit',
											'America/Jamaica' => 'America/Jamaica',
											'America/Jujuy' => 'America/Jujuy',
											'America/Juneau' => 'America/Juneau',
											'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
											'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
											'America/Knox_IN' => 'America/Knox_IN',
											'America/La_Paz' => 'America/La_Paz',
											'America/Lima' => 'America/Lima',
											'America/Los_Angeles' => 'America/Los_Angeles',
											'America/Louisville' => 'America/Louisville',
											'America/Maceio' => 'America/Maceio',
											'America/Managua' => 'America/Managua',
											'America/Manaus' => 'America/Manaus',
											'America/Martinique' => 'America/Martinique',
											'America/Mazatlan' => 'America/Mazatlan',
											'America/Mendoza' => 'America/Mendoza',
											'America/Menominee' => 'America/Menominee',
											'America/Merida' => 'America/Merida',
											'America/Mexico_City' => 'America/Mexico_City',
											'America/Miquelon' => 'America/Miquelon',
											'America/Monterrey' => 'America/Monterrey',
											'America/Montevideo' => 'America/Montevideo',
											'America/Montreal' => 'America/Montreal',
											'America/Montserrat' => 'America/Montserrat',
											'America/Nassau' => 'America/Nassau',
											'America/New_York' => 'America/New_York',
											'America/Nipigon' => 'America/Nipigon',
											'America/Nome' => 'America/Nome',
											'America/Noronha' => 'America/Noronha',
											'America/North_Dakota/Center' => 'America/North_Dakota/Center',
											'America/Panama' => 'America/Panama',
											'America/Pangnirtung' => 'America/Pangnirtung',
											//'America/Paramaribo' => 'America/Paramaribo',
											'America/Phoenix' => 'America/Phoenix',
											'America/Port-au-Prince' => 'America/Port-au-Prince',
											'America/Port_of_Spain' => 'America/Port_of_Spain',
											'America/Porto_Acre' => 'America/Porto_Acre',
											'America/Porto_Velho' => 'America/Porto_Velho',
											'America/Puerto_Rico' => 'America/Puerto_Rico',
											'America/Rainy_River' => 'America/Rainy_River',
											'America/Rankin_Inlet' => 'America/Rankin_Inlet',
											'America/Recife' => 'America/Recife',
											'America/Regina' => 'America/Regina',
											'America/Rio_Branco' => 'America/Rio_Branco',
											'America/Rosario' => 'America/Rosario',
											'America/Santiago' => 'America/Santiago',
											'America/Santo_Domingo' => 'America/Santo_Domingo',
											'America/Sao_Paulo' => 'America/Sao_Paulo',
											'America/Scoresbysund' => 'America/Scoresbysund',
											'America/Shiprock' => 'America/Shiprock',
											'America/St_Johns' => 'America/St_Johns',
											'America/St_Kitts' => 'America/St_Kitts',
											'America/St_Lucia' => 'America/St_Lucia',
											'America/St_Thomas' => 'America/St_Thomas',
											'America/St_Vincent' => 'America/St_Vincent',
											'America/Swift_Current' => 'America/Swift_Current',
											'America/Tegucigalpa' => 'America/Tegucigalpa',
											'America/Thule' => 'America/Thule',
											'America/Thunder_Bay' => 'America/Thunder_Bay',
											'America/Tijuana' => 'America/Tijuana',
											'America/Tortola' => 'America/Tortola',
											'America/Vancouver' => 'America/Vancouver',
											'America/Virgin' => 'America/Virgin',
											'America/Whitehorse' => 'America/Whitehorse',
											'America/Winnipeg' => 'America/Winnipeg',
											'America/Yakutat' => 'America/Yakutat',
											'America/Yellowknife' => 'America/Yellowknife',
											//'Antarctica/Casey' => 'Antarctica/Casey',
											'Antarctica/Davis' => 'Antarctica/Davis',
											'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
											'Antarctica/Mawson' => 'Antarctica/Mawson',
											'Antarctica/McMurdo' => 'Antarctica/McMurdo',
											'Antarctica/Palmer' => 'Antarctica/Palmer',
											'Antarctica/South_Pole' => 'Antarctica/South_Pole',
											//'Antarctica/Syowa' => 'Antarctica/Syowa',
											//'Antarctica/Vostok' => 'Antarctica/Vostok',
											'Arctic/Longyearbyen' => 'Arctic/Longyearbyen',
											'Asia/Aden' => 'Asia/Aden',
											'Asia/Almaty' => 'Asia/Almaty',
											'Asia/Amman' => 'Asia/Amman',
											'Asia/Anadyr' => 'Asia/Anadyr',
											//'Asia/Aqtau' => 'Asia/Aqtau',
											//'Asia/Aqtobe' => 'Asia/Aqtobe',
											'Asia/Ashgabat' => 'Asia/Ashgabat',
											'Asia/Ashkhabad' => 'Asia/Ashkhabad',
											'Asia/Baghdad' => 'Asia/Baghdad',
											'Asia/Bahrain' => 'Asia/Bahrain',
											'Asia/Baku' => 'Asia/Baku',
											'Asia/Bangkok' => 'Asia/Bangkok',
											'Asia/Beirut' => 'Asia/Beirut',
											'Asia/Bishkek' => 'Asia/Bishkek',
											'Asia/Brunei' => 'Asia/Brunei',
											'Asia/Calcutta' => 'Asia/Calcutta',
											//'Asia/Choibalsan' => 'Asia/Choibalsan',
											'Asia/Chongqing' => 'Asia/Chongqing',
											'Asia/Chungking' => 'Asia/Chungking',
											'Asia/Colombo' => 'Asia/Colombo',
											'Asia/Dacca' => 'Asia/Dacca',
											'Asia/Damascus' => 'Asia/Damascus',
											'Asia/Dhaka' => 'Asia/Dhaka',
											//'Asia/Dili' => 'Asia/Dili',
											//'Asia/Dubai' => 'Asia/Dubai',
											'Asia/Dushanbe' => 'Asia/Dushanbe',
											'Asia/Gaza' => 'Asia/Gaza',
											'Asia/Harbin' => 'Asia/Harbin',
											'Asia/Hong_Kong' => 'Asia/Hong_Kong',
											//'Asia/Hovd' => 'Asia/Hovd',
											'Asia/Irkutsk' => 'Asia/Irkutsk',
											'Asia/Istanbul' => 'Asia/Istanbul',
											//'Asia/Jakarta' => 'Asia/Jakarta',
											//'Asia/Jayapura' => 'Asia/Jayapura',
											//'Asia/Jerusalem' => 'Asia/Jerusalem', //Offset 10800
											'Asia/Kabul' => 'Asia/Kabul',
											'Asia/Kamchatka' => 'Asia/Kamchatka',
											'Asia/Karachi' => 'Asia/Karachi',
											'Asia/Kashgar' => 'Asia/Kashgar',
											'Asia/Katmandu' => 'Asia/Katmandu',
											'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
											'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
											'Asia/Kuching' => 'Asia/Kuching',
											'Asia/Kuwait' => 'Asia/Kuwait',
											'Asia/Macao' => 'Asia/Macao',
											'Asia/Magadan' => 'Asia/Magadan',
											'Asia/Manila' => 'Asia/Manila',
											//'Asia/Muscat' => 'Asia/Muscat',
											'Asia/Nicosia' => 'Asia/Nicosia',
											'Asia/Novosibirsk' => 'Asia/Novosibirsk',
											'Asia/Omsk' => 'Asia/Omsk',
											'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
											//'Asia/Pontianak' => 'Asia/Pontianak',
											'Asia/Pyongyang' => 'Asia/Pyongyang',
											'Asia/Qatar' => 'Asia/Qatar',
											'Asia/Rangoon' => 'Asia/Rangoon',
											'Asia/Riyadh' => 'Asia/Riyadh',
											//'Asia/Riyadh87' => 'Asia/Riyadh87',
											//'Asia/Riyadh88' => 'Asia/Riyadh88',
											//'Asia/Riyadh89' => 'Asia/Riyadh89',
											'Asia/Saigon' => 'Asia/Saigon',
											//'Asia/Sakhalin' => 'Asia/Sakhalin',
											'Asia/Samarkand' => 'Asia/Samarkand',
											'Asia/Seoul' => 'Asia/Seoul',
											'Asia/Shanghai' => 'Asia/Shanghai',
											//'Asia/Singapore' => 'Asia/Singapore',
											'Asia/Taipei' => 'Asia/Taipei',
											'Asia/Tashkent' => 'Asia/Tashkent',
											'Asia/Tbilisi' => 'Asia/Tbilisi',
											//'Asia/Tehran' => 'Asia/Tehran',
											//'Asia/Tel_Aviv' => 'Asia/Tel_Aviv',
											'Asia/Thimbu' => 'Asia/Thimbu',
											'Asia/Thimphu' => 'Asia/Thimphu',
											'Asia/Tokyo' => 'Asia/Tokyo',
											//'Asia/Ujung_Pandang' => 'Asia/Ujung_Pandang',
											'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
											'Asia/Ulan_Bator' => 'Asia/Ulan_Bator',
											'Asia/Urumqi' => 'Asia/Urumqi',
											'Asia/Vientiane' => 'Asia/Vientiane',
											'Asia/Vladivostok' => 'Asia/Vladivostok',
											'Asia/Yakutsk' => 'Asia/Yakutsk',
											'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
											'Asia/Yerevan' => 'Asia/Yerevan',
											'Atlantic/Azores' => 'Atlantic/Azores',
											'Atlantic/Bermuda' => 'Atlantic/Bermuda',
											//'Atlantic/Canary' => 'Atlantic/Canary',
											//'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
											//'Atlantic/Faeroe' => 'Atlantic/Faeroe',
											'Atlantic/Jan_Mayen' => 'Atlantic/Jan_Mayen',
											//'Atlantic/Madeira' => 'Atlantic/Madeira',
											'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
											//'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
											'Atlantic/St_Helena' => 'Atlantic/St_Helena',
											'Atlantic/Stanley' => 'Atlantic/Stanley',
											'Australia/ACT' => 'Australia/ACT',
											'Australia/Adelaide' => 'Australia/Adelaide',
											'Australia/Brisbane' => 'Australia/Brisbane',
											'Australia/Broken_Hill' => 'Australia/Broken_Hill',
											'Australia/Canberra' => 'Australia/Canberra',
											'Australia/Darwin' => 'Australia/Darwin',
											'Australia/Hobart' => 'Australia/Hobart',
											'Australia/LHI' => 'Australia/LHI',
											'Australia/Lindeman' => 'Australia/Lindeman',
											'Australia/Lord_Howe' => 'Australia/Lord_Howe',
											'Australia/Melbourne' => 'Australia/Melbourne',
											'Australia/NSW' => 'Australia/NSW',
											'Australia/North' => 'Australia/North',
											//'Australia/Perth' => 'Australia/Perth',
											'Australia/Queensland' => 'Australia/Queensland',
											'Australia/South' => 'Australia/South',
											'Australia/Sydney' => 'Australia/Sydney',
											'Australia/Tasmania' => 'Australia/Tasmania',
											'Australia/Victoria' => 'Australia/Victoria',
											//'Australia/West' => 'Australia/West',
											'Australia/Yancowinna' => 'Australia/Yancowinna',
											'Brazil/Acre' => 'Brazil/Acre',
											'Brazil/DeNoronha' => 'Brazil/DeNoronha',
											'Brazil/East' => 'Brazil/East',
											'Brazil/West' => 'Brazil/West',
											'Canada/Atlantic' => 'Canada/Atlantic',
											'Canada/Central' => 'Canada/Central',
											'Canada/East-Saskatchewan' => 'Canada/East-Saskatchewan',
											'Canada/Eastern' => 'Canada/Eastern',
											'Canada/Mountain' => 'Canada/Mountain',
											'Canada/Newfoundland' => 'Canada/Newfoundland',
											'Canada/Pacific' => 'Canada/Pacific',
											'Canada/Saskatchewan' => 'Canada/Saskatchewan',
											'Canada/Yukon' => 'Canada/Yukon',
											'Chile/Continental' => 'Chile/Continental',
											'Chile/EasterIsland' => 'Chile/EasterIsland',
											'Cuba' => 'Cuba',
											'Egypt' => 'Egypt',
											'Eire' => 'Eire',
											//'Etc/GMT0' => 'Etc/GMT0',
											//'Etc/Greenwich' => 'Etc/Greenwich',
											//'Etc/UCT' => 'Etc/UCT',
											//'Etc/UTC' => 'Etc/UTC',
											//'Etc/Universal' => 'Etc/Universal',
											//'Etc/Zulu' => 'Etc/Zulu',
											'Europe/Amsterdam' => 'Europe/Amsterdam',
											'Europe/Andorra' => 'Europe/Andorra',
											'Europe/Athens' => 'Europe/Athens',
											'Europe/Belfast' => 'Europe/Belfast',
											'Europe/Belgrade' => 'Europe/Belgrade',
											'Europe/Berlin' => 'Europe/Berlin',
											'Europe/Bratislava' => 'Europe/Bratislava',
											'Europe/Brussels' => 'Europe/Brussels',
											'Europe/Bucharest' => 'Europe/Bucharest',
											'Europe/Budapest' => 'Europe/Budapest',
											'Europe/Chisinau' => 'Europe/Chisinau',
											'Europe/Copenhagen' => 'Europe/Copenhagen',
											'Europe/Dublin' => 'Europe/Dublin',
											'Europe/Gibraltar' => 'Europe/Gibraltar',
											'Europe/Helsinki' => 'Europe/Helsinki',
											'Europe/Istanbul' => 'Europe/Istanbul',
											'Europe/Kaliningrad' => 'Europe/Kaliningrad',
											'Europe/Kiev' => 'Europe/Kiev',
											//'Europe/Lisbon' => 'Europe/Lisbon',
											'Europe/Ljubljana' => 'Europe/Ljubljana',
											'Europe/London' => 'Europe/London',
											'Europe/Luxembourg' => 'Europe/Luxembourg',
											'Europe/Madrid' => 'Europe/Madrid',
											'Europe/Malta' => 'Europe/Malta',
											'Europe/Minsk' => 'Europe/Minsk',
											'Europe/Monaco' => 'Europe/Monaco',
											'Europe/Moscow' => 'Europe/Moscow',
											'Europe/Nicosia' => 'Europe/Nicosia',
											'Europe/Oslo' => 'Europe/Oslo',
											'Europe/Paris' => 'Europe/Paris',
											'Europe/Prague' => 'Europe/Prague',
											'Europe/Riga' => 'Europe/Riga',
											'Europe/Rome' => 'Europe/Rome',
											//'Europe/Samara' => 'Europe/Samara',
											'Europe/San_Marino' => 'Europe/San_Marino',
											'Europe/Sarajevo' => 'Europe/Sarajevo',
											'Europe/Simferopol' => 'Europe/Simferopol',
											'Europe/Skopje' => 'Europe/Skopje',
											'Europe/Sofia' => 'Europe/Sofia',
											'Europe/Stockholm' => 'Europe/Stockholm',
											'Europe/Tallinn' => 'Europe/Tallinn',
											'Europe/Tirane' => 'Europe/Tirane',
											'Europe/Tiraspol' => 'Europe/Tiraspol',
											'Europe/Uzhgorod' => 'Europe/Uzhgorod',
											'Europe/Vaduz' => 'Europe/Vaduz',
											'Europe/Vatican' => 'Europe/Vatican',
											'Europe/Vienna' => 'Europe/Vienna',
											'Europe/Vilnius' => 'Europe/Vilnius',
											'Europe/Warsaw' => 'Europe/Warsaw',
											'Europe/Zagreb' => 'Europe/Zagreb',
											'Europe/Zaporozhye' => 'Europe/Zaporozhye',
											'Europe/Zurich' => 'Europe/Zurich',
											'GB' => 'GB',
											'GB-Eire' => 'GB-Eire',
											'Greenwich' => 'Greenwich',
											'Hongkong' => 'Hongkong',
											'Iceland' => 'Iceland',
											'-1000-Asia/Calcutta' => 'India', //GMT+5:30, same as Asia Calcutta
											'Indian/Antananarivo' => 'Indian/Antananarivo',
											'Indian/Chagos' => 'Indian/Chagos',
											'Indian/Christmas' => 'Indian/Christmas',
											'Indian/Cocos' => 'Indian/Cocos',
											'Indian/Comoro' => 'Indian/Comoro',
											'Indian/Kerguelen' => 'Indian/Kerguelen',
											'Indian/Mahe' => 'Indian/Mahe',
											'Indian/Maldives' => 'Indian/Maldives',
											'Indian/Mauritius' => 'Indian/Mauritius',
											'Indian/Mayotte' => 'Indian/Mayotte',
											'Indian/Reunion' => 'Indian/Reunion',
											//'Iran' => 'Iran',
											//'Israel' => 'Israel', //Fails in PostgreSQL 8.2
											'Jamaica' => 'Jamaica',
											'Japan' => 'Japan',
											'Kwajalein' => 'Kwajalein',
											'Libya' => 'Libya',
											'Mexico/BajaNorte' => 'Mexico/BajaNorte',
											'Mexico/BajaSur' => 'Mexico/BajaSur',
											'Mexico/General' => 'Mexico/General',
											//'Mideast/Riyadh87' => 'Mideast/Riyadh87',
											//'Mideast/Riyadh88' => 'Mideast/Riyadh88',
											//'Mideast/Riyadh89' => 'Mideast/Riyadh89',
											'NZ' => 'NZ',
											'NZ-CHAT' => 'NZ-CHAT',
											'Navajo' => 'Navajo',
											//'Pacific/Apia' => 'Pacific/Apia',
											'Pacific/Auckland' => 'Pacific/Auckland',
											'Pacific/Chatham' => 'Pacific/Chatham',
											'Pacific/Easter' => 'Pacific/Easter',
											'Pacific/Efate' => 'Pacific/Efate',
											'Pacific/Enderbury' => 'Pacific/Enderbury',
											'Pacific/Fakaofo' => 'Pacific/Fakaofo',
											'Pacific/Fiji' => 'Pacific/Fiji',
											'Pacific/Funafuti' => 'Pacific/Funafuti',
											'Pacific/Galapagos' => 'Pacific/Galapagos',
											'Pacific/Gambier' => 'Pacific/Gambier',
											//'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
											//'Pacific/Guam' => 'Pacific/Guam',
											'Pacific/Honolulu' => 'Pacific/Honolulu',
											'Pacific/Johnston' => 'Pacific/Johnston',
											'Pacific/Kiritimati' => 'Pacific/Kiritimati',
											'Pacific/Kosrae' => 'Pacific/Kosrae',
											'Pacific/Kwajalein' => 'Pacific/Kwajalein',
											'Pacific/Majuro' => 'Pacific/Majuro',
											'Pacific/Marquesas' => 'Pacific/Marquesas',
											//'Pacific/Midway' => 'Pacific/Midway',
											//'Pacific/Nauru' => 'Pacific/Nauru',
											'Pacific/Niue' => 'Pacific/Niue',
											'Pacific/Norfolk' => 'Pacific/Norfolk',
											//'Pacific/Noumea' => 'Pacific/Noumea',
											//'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
											'Pacific/Palau' => 'Pacific/Palau',
											'Pacific/Pitcairn' => 'Pacific/Pitcairn',
											'Pacific/Ponape' => 'Pacific/Ponape',
											'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
											'Pacific/Rarotonga' => 'Pacific/Rarotonga',
											//'Pacific/Saipan' => 'Pacific/Saipan',
											//'Pacific/Samoa' => 'Pacific/Samoa',
											'Pacific/Tahiti' => 'Pacific/Tahiti',
											'Pacific/Tarawa' => 'Pacific/Tarawa',
											'Pacific/Tongatapu' => 'Pacific/Tongatapu',
											'Pacific/Truk' => 'Pacific/Truk',
											'Pacific/Wake' => 'Pacific/Wake',
											'Pacific/Wallis' => 'Pacific/Wallis',
											'Pacific/Yap' => 'Pacific/Yap',
											'Poland' => 'Poland',
											//'Portugal' => 'Portugal',
											'ROK' => 'ROK',
											//'SST' => 'SST',
											//'Singapore' => 'Singapore',
											//'SystemV/AST4' => 'SystemV/AST4',
											//'SystemV/AST4ADT' => 'SystemV/AST4ADT',
											//'SystemV/CST6' => 'SystemV/CST6',
											//'SystemV/CST6CDT' => 'SystemV/CST6CDT',
											//'SystemV/EST5' => 'SystemV/EST5',
											//'SystemV/EST5EDT' => 'SystemV/EST5EDT',
											//'SystemV/HST10' => 'SystemV/HST10',
											//'SystemV/MST7' => 'SystemV/MST7',
											//'SystemV/MST7MDT' => 'SystemV/MST7MDT',
											//'SystemV/PST8' => 'SystemV/PST8',
											//'SystemV/PST8PDT' => 'SystemV/PST8PDT',
											//'SystemV/YST9' => 'SystemV/YST9',
											//'SystemV/YST9YDT' => 'SystemV/YST9YDT',
											'Turkey' => 'Turkey',
											'US/Alaska' => 'US/Alaska',
											//'US/Aleutian' => 'US/Aleutian',
											'US/Arizona' => 'US/Arizona',
											'US/Central' => 'US/Central',
											'US/East-Indiana' => 'US/East-Indiana',
											'US/Eastern' => 'US/Eastern',
											'US/Hawaii' => 'US/Hawaii',
											'US/Indiana-Starke' => 'US/Indiana-Starke',
											'US/Michigan' => 'US/Michigan',
											'US/Mountain' => 'US/Mountain',
											'US/Pacific' => 'US/Pacific',
											//'US/Pacific-New' => 'US/Pacific-New',
											//'US/Samoa' => 'US/Samoa',
											'Universal' => 'Universal',
											'W-SU' => 'W-SU',
											//'WET' => 'WET',
											'Zulu' => 'Zulu',

											'SystemV/AST4ADT' => 'AST4ADT', //This appears to only work on Linux.
											//'AST4ADT' => 'AST4ADT', //This doesn't appear to work on Linux.
											'CST6CDT' => 'CST6CDT',
											'EST5EDT' => 'EST5EDT',
											'MST7MDT' => 'MST7MDT',
											'PST8PDT' => 'PST8PDT',
											'SystemV/YST9YDT' => 'YST9YDT',
											//'YST9YDT' => 'YST9YDT', //This doesn't appear to work on Linux.

											//'ACT' => 'ACT',
											//'AET' => 'AET',
											//'AGT' => 'AGT',
											//'ART' => 'ART',
											//'AST' => 'AST',
											//'BDT' => 'BDT',
											//'BET' => 'BET',
											//'CAT' => 'CAT',
											'CET' => 'CET',
											//'CNT' => 'CNT',
											//'CST' => 'CST',
											//'CTT' => 'CTT',
											//'EAT' => 'EAT',
											//'ECT' => 'ECT',
											'EET' => 'EET',
											'EST' => 'EST',
											'GMT' => 'GMT',
											'HST' => 'HST',
											//'IET' => 'IET',
											//'IST' => 'IST', //10800 offset
											//'JST' => 'JST',
											'MET' => 'MET',
											//'MIT' => 'MIT',
											'MST' => 'MST',
											//'NET' => 'NET',
											//'NST' => 'NST',
											//'PLT' => 'PLT',
											//'PNT' => 'PNT',
											'PRC' => 'PRC',
											//'PRT' => 'PRT',
											//'PST' => 'PST', Not a valid timezone in PHP or PostgreSQL
											'UCT' => 'UCT',
											'UTC' => 'UTC',
											//'VST' => 'VST',

											//POSIX standard states to invert the signs, so do this here for our users.
											'Etc/GMT' => 'GMT',
											'Etc/GMT-0' => 'GMT+0',
											'Etc/GMT-1' => 'GMT+1',
											'Etc/GMT-2' => 'GMT+2',
											'Etc/GMT-3' => 'GMT+3',
											'Etc/GMT-4' => 'GMT+4',
											'Etc/GMT-5' => 'GMT+5',
											'Etc/GMT-6' => 'GMT+6',
											'Etc/GMT-7' => 'GMT+7',
											'Etc/GMT-8' => 'GMT+8',
											'Etc/GMT-9' => 'GMT+9',
											'Etc/GMT-10' => 'GMT+10',
											'Etc/GMT-11' => 'GMT+11',
											'Etc/GMT-12' => 'GMT+12',
											'Etc/GMT-13' => 'GMT+13',
											'Etc/GMT-14' => 'GMT+14',
											'Etc/GMT+0' => 'GMT-0',
											'Etc/GMT+1' => 'GMT-1',
											'Etc/GMT+2' => 'GMT-2',
											'Etc/GMT+3' => 'GMT-3',
											'Etc/GMT+4' => 'GMT-4',
											'Etc/GMT+5' => 'GMT-5',
											'Etc/GMT+6' => 'GMT-6',
											'Etc/GMT+7' => 'GMT-7',
											'Etc/GMT+8' => 'GMT-8',
											'Etc/GMT+9' => 'GMT-9',
											'Etc/GMT+10' => 'GMT-10',
											'Etc/GMT+11' => 'GMT-11',
											'Etc/GMT+12' => 'GMT-12',
									);

				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'location_timezone':
				//Country/Province to TimeZone map.
				$retval = array(
										'CA' => array(
														'AB' => 'MST7MDT',
														'BC' => 'PST8PDT',
														'SK' => 'Canada/Saskatchewan',
														'MB' => 'CST6CDT',
														'QC' => 'EST5EDT',
														'ON' => 'EST5EDT',
														'NL' => 'Canada/Newfoundland',
														'NB' => 'EST5EDT',
														'NS' => 'Canada/Atlantic',
														'PE' => 'Canada/Atlantic',
														'NT' => 'MST7MDT',
														'YT' => 'PST8PDT',
														'NU' => 'EST5EDT',
														),
										'US' => array(
														'AL' => 'CST6CDT',
														'AK' => 'US/Alaska',
														'AZ' => 'America/Phoenix',
														'AR' => 'CST6CDT',
														'CA' => 'PST8PDT',
														'CO' => 'MST7MDT',
														'CT' => 'EST5EDT',
														'DE' => 'EST5EDT',
														'DC' => 'EST5EDT',
														'FL' => 'EST5EDT',
														'GA' => 'EST5EDT',
														'HI' => 'HST',
														'ID' => 'MST7MDT',
														'IL' => 'CST6CDT',
														'IN' => 'EST5EDT',
														'IA' => 'CST6CDT',
														'KS' => 'CST6CDT',
														'KY' => 'EST5EDT',
														'LA' => 'CST6CDT',
														'ME' => 'EST5EDT',
														'MD' => 'EST5EDT',
														'MA' => 'EST5EDT',
														'MI' => 'EST5EDT',
														'MN' => 'CST6CDT',
														'MS' => 'CST6CDT',
														'MO' => 'CST6CDT',
														'MT' => 'MST7MDT',
														'NE' => 'CST6CDT',
														'NV' => 'PST8PDT',
														'NH' => 'EST5EDT',
														'NM' => 'MST7MDT',
														'NJ' => 'EST5EDT',
														'NY' => 'EST5EDT',
														'NC' => 'EST5EDT',
														'ND' => 'CST6CDT',
														'OH' => 'EST5EDT',
														'OK' => 'CST6CDT',
														'OR' => 'PST8PDT',
														'PA' => 'EST5EDT',
														'RI' => 'EST5EDT',
														'SC' => 'EST5EDT',
														'SD' => 'EST5EDT',
														'TN' => 'CST6CDT',
														'TX' => 'CST6CDT',
														'UT' => 'MST7MDT',
														'VT' => 'EST5EDT',
														'VA' => 'EST5EDT',
														'WA' => 'PST8PDT',
														'WV' => 'EST5EDT',
														'WI' => 'CST6CDT',
														'WY' => 'MST7MDT',
													),
										'MX' => 'CST6CDT',
								);
				break;
			case 'area_code_timezone':
				//Area code to Country/Province/TimeZone map.
				$retval = array(
									211	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Local community info / referral services
									242	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'EST5EDT'), //	 Bahamas
									246	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Barbados
									264	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Anguilla (split from 809)
									268	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Antigua and Barbuda (split from 809)
									284	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 British Virgin Islands (split from 809)
									311	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Reserved for special applications
									345	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'EST5EDT'), //	 Cayman Islands
									411	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Reserved for special applications
									441	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Bermuda (part of what used to be 809)
									456	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Inbound International
									473	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Grenada ("new" -- split from 809)
									500	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Personal Communication Service
									511	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Nationwide travel information
									555	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // Reserved for directory assistance applications
									600	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Canadian Services
									611	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Reserved for special applications
									649	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'EST5EDT'), //	 Turks & Caicos Islands
									664	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Montserrat (split from 809)
									684	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //1	 American Samoa
									700	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Interexchange Carrier Services
									710	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US Government
									711	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Telecommunications Relay Services
									758	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 St. Lucia (split from 809)
									767	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Dominica (split from 809)
									784	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 St. Vincent & Grenadines (split from 809)
									800	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL ), // US/Canada toll free (see 888, 877, 866, 855, 844, 833, 822)
									809	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Dominican Republic (see splits 264, 268, 284, 340, 441, 473, 664, 758, 767, 784, 868, 876; overlay 829)
									811	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Reserved for special applications
									822	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free (proposed, may not be in use yet)
									829	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Dominican Republic (perm 1/31/05; mand 8/1/05; overlaid on 809)
									833	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free (proposed, may not be in use yet)
									844	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free (proposed, may not be in use yet)
									855	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free (proposed, may not be in use yet)
									866	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free
									868	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 Trinidad and Tobago ("new" -- see 809)
									869	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'AST4ADT'), //	 St. Kitts & Nevis
									876	=> array('country' => 'US', 'province' => NULL, 'time_zone' => 'EST5EDT'), //	 Jamaica (split from 809)
									877	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free
									880	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Paid Toll-Free Service
									881	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Paid Toll-Free Service
									882	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Paid Toll-Free Service
									888	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US/Canada toll free
									898	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // VoIP service
									900	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), // US toll calls -- prices vary with the number called
									911	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Emergency
									976	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Unassigned
									999	=> array('country' => 'US', 'province' => NULL, 'time_zone' => NULL), //	 Often used by carriers to indicate that the area code information is unavailable for CNID, even though the rest of the number is present
									525 => array('country' => 'MX', 'province' => NULL, 'time_zone' => 'CST6CDT'), //-6	 Mexico: Mexico City area (country code + city code)
									403	=> array('country' => 'CA', 'province' => 'AB', 'time_zone' => 'MST7MDT'), //	 Canada: Southern Alberta (see 780, 867)
									780	=> array('country' => 'CA', 'province' => 'AB', 'time_zone' => 'MST7MDT'), //	 Canada: Northern Alberta, north of Lacombe (see 403)
									250	=> array('country' => 'CA', 'province' => 'BC', 'time_zone' => 'PST8PDT'), ///-7	 Canada: British Columbia (see 604)
									604	=> array('country' => 'CA', 'province' => 'BC', 'time_zone' => 'PST8PDT'), //	 Canada: British Columbia: Greater Vancouver (overlay 778, perm 11/3/01; see 250)
									778	=> array('country' => 'CA', 'province' => 'BC', 'time_zone' => 'PST8PDT'), //	 Canada: British Columbia: Greater Vancouver (overlaid on 604, per 11/3/01; see also 250)
									204	=> array('country' => 'CA', 'province' => 'MB', 'time_zone' => 'CST6CDT'), //	 Canada: Manitoba
									506	=> array('country' => 'CA', 'province' => 'NB', 'time_zone' => 'EST5EDT'), //	 Canada: New Brunswick
									226	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: SW Ontario: Windsor (overlaid on 519; eff 6/06)
									289	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: S Cent. Ontario: Greater Toronto Area -- Durham, Halton, Hamilton-Wentworth, Niagara, Peel, York, and southern Simcoe County (excluding Toronto -- overlaid on 905, eff 6/9/01)
									416	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: S Cent. Ontario: Toronto (see overlay 647, eff 3/5/01)
									519	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: SW Ontario: Windsor (see overlay 226)
									613	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: SE Ontario: Ottawa
									647	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: S Cent. Ontario: Toronto (overlaid on 416; eff 3/5/01)
									705	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: NE Ontario: Sault Ste. Marie/N Ontario: N Bay, Sudbury
									807	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), ///-6	 Canada: W Ontario: Thunder Bay region to Manitoba border
									905	=> array('country' => 'CA', 'province' => 'ON', 'time_zone' => 'EST5EDT'), //	 Canada: S Cent. Ontario: Greater Toronto Area -- Durham, Halton, Hamilton-Wentworth, Niagara, Peel, York, and southern Simcoe County (excluding Toronto -- see overlay 289 [eff 6/9/01], splits 416, 647)
									418	=> array('country' => 'CA', 'province' => 'QC', 'time_zone' => 'EST5EDT'), ///-4	 Canada: NE Quebec: Quebec
									438	=> array('country' => 'CA', 'province' => 'QC', 'time_zone' => 'EST5EDT'), //	 Canada: SW Quebec: Montreal city (overlaid on 514, [delayed until 6/06] eff 10/10/03, mand 2/7/04)
									450	=> array('country' => 'CA', 'province' => 'QC', 'time_zone' => 'EST5EDT'), ///-4	 Canada: Southeastern Quebec; suburbs outside metro Montreal
									514	=> array('country' => 'CA', 'province' => 'QC', 'time_zone' => 'EST5EDT'), //	 Canada: SW Quebec: Montreal city (see overlay 438, eff 10/10/03, mand 2/7/04)
									306	=> array('country' => 'CA', 'province' => 'SK', 'time_zone' => 'Canada/Saskatchewan'), ///-7*	 Canada: Saskatchewan
									819	=> array('country' => 'CA', 'province' => 'QC', 'time_zone' => 'EST5EDT'), //	 NW Quebec: Trois Rivieres, Sherbrooke, Outaouais (Gatineau, Hull), and the Laurentians (up to St Jovite / Tremblant) (see 867)
									867	=> array('country' => 'CA', 'province' => 'YT', 'time_zone' => 'EST5EDT'), ///-6/-7/-8	 Canada: Yukon, Northwest Territories, Nunavut (split from 403/819)
									709	=> array('country' => 'CA', 'province' => 'NL', 'time_zone' => 'Canada/Newfoundland'), ///-3.5	 Canada: Newfoundland and Labrador
									902	=> array('country' => 'CA', 'province' => 'NS', 'time_zone' => 'Canada/Atlantic'), //	 Canada: Nova Scotia, Prince Edward Island
									907	=> array('country' => 'US', 'province' => 'AK', 'time_zone' => 'US/Alaska'), //	 Alaska
									205	=> array('country' => 'US', 'province' => 'AL', 'time_zone' => 'CST6CDT'), //	 Central Alabama (including Birmingham; excludes the southeastern corner of Alabama and the deep south; see splits 256 and 334)
									251	=> array('country' => 'US', 'province' => 'AL', 'time_zone' => 'CST6CDT'), //	 S Alabama: Mobile and coastal areas, Jackson, Evergreen, Monroeville (split from 334, eff 6/18/01; see also 205, 256)
									256	=> array('country' => 'US', 'province' => 'AL', 'time_zone' => 'CST6CDT'), //	 E and N Alabama (Huntsville, Florence, Gadsden; split from 205; see also 334)
									334	=> array('country' => 'US', 'province' => 'AL', 'time_zone' => 'CST6CDT'), //	 S Alabama: Auburn/Opelika, Montgomery and coastal areas (part of what used to be 205; see also 256, split 251)
									479	=> array('country' => 'US', 'province' => 'AR', 'time_zone' => 'CST6CDT'), //	 NW Arkansas: Fort Smith, Fayetteville, Springdale, Bentonville (SPLIt from 501, perm 1/19/02, mand 7/20/02)
									501	=> array('country' => 'US', 'province' => 'AR', 'time_zone' => 'CST6CDT'), //	 Central Arkansas: Little Rock, Hot Springs, Conway (see split 479)
									870	=> array('country' => 'US', 'province' => 'AR', 'time_zone' => 'CST6CDT'), //	 Arkansas: areas outside of west/central AR: Jonesboro, etc
									480	=> array('country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix'), //*	 Arizona: East Phoenix (see 520; also Phoenix split 602, 623)
									520	=> array('country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix'), //*	 SE Arizona: Tucson area (split from 602; see split 928)
									602	=> array('country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix'), //*	 Arizona: Phoenix (see 520; also Phoenix split 480, 623)
									623	=> array('country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix'), //*	 Arizona: West Phoenix (see 520; also Phoenix split 480, 602)
									928	=> array('country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix'), //*	 Central and Northern Arizona: Prescott, Flagstaff, Yuma (split from 520)
									209	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Cent. California: Stockton (see split 559)
									213	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Los Angeles (see 310, 323, 626, 818)
									310	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Beverly Hills, West Hollywood, West Los Angeles (see split 562; overlay 424)
									323	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Los Angeles (outside downtown: Hollywood; split from 213)
									341	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 (overlay on 510; SUSPENDED)
									369	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Solano County (perm 12/2/00, mand 6/2/01)
									408	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Cent. Coastal California: San Jose (see overlay 669)
									415	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: San Francisco County and Marin County on the north side of the Golden Gate Bridge, extending north to Sonoma County (see 650)
									424	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Los Angeles (see split 562; overlaid on 310 mand 7/26/06)
									442	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Far north suburbs of San Diego (Oceanside, Escondido, SUSPENDED -- originally perm 10/21/00, mand 4/14/01)
									510	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: Oakland, East Bay (see 925)
									530	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 NE California: Eldorado County area, excluding Eldorado Hills itself: incl cities of Auburn, Chico, Redding, So. Lake Tahoe, Marysville, Nevada City/Grass Valley (split from 916)
									559	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Central California: Fresno (split from 209)
									562	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: Long Beach (split from 310 Los Angeles)
									619	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: San Diego (see split 760; overlay 858, 935)
									626	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 E S California: Pasadena (split from 818 Los Angeles)
									627	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 No longer in use [was Napa, Sonoma counties (perm 10/13/01, mand 4/13/02); now 707]
									628	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 (Region unknown; perm 10/21/00)
									650	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: Peninsula south of San Francisco -- San Mateo County, parts of Santa Clara County (split from 415)
									661	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: N Los Angeles, Mckittrick, Mojave, Newhall, Oildale, Palmdale, Taft, Tehachapi, Bakersfield, Earlimart, Lancaster (split from 805)
									669	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 Cent. Coastal California: San Jose (rejected was: overlaid on 408)
									707	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 NW California: Santa Rosa, Napa, Vallejo, American Canyon, Fairfield
									714	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 North and Central Orange County (see split 949)
									747	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Los Angeles, Agoura Hills, Calabasas, Hidden Hills, and Westlake Village (see 818; implementation suspended)
									760	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: San Diego North County to Sierra Nevada (split from 619)
									764	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 (overlay on 650; SUSPENDED)
									805	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S Cent. and Cent. Coastal California: Ventura County, Santa Barbara County: San Luis Obispo, Thousand Oaks, Carpinteria, Santa Barbara, Santa Maria, Lompoc, Santa Ynez Valley / Solvang (see 661 split)
									818	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: Los Angeles: San Fernando Valley (see 213, 310, 562, 626, 747)
									831	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: central coast area from Santa Cruz through Monterey County
									858	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: San Diego (see split 760; overlay 619, 935)
									909	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: Inland empire: San Bernardino (see split 951), Riverside
									916	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 NE California: Sacramento, Walnut Grove, Lincoln, Newcastle and El Dorado Hills (split to 530)
									925	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: Contra Costa area: Antioch, Concord, Pleasanton, Walnut Creek (split from 510)
									935	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 S California: San Diego (see split 760; overlay 858, 619; assigned but not in use)
									949	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: S Coastal Orange County (split from 714)
									951	=> array('country' => 'US', 'province' => 'CA', 'time_zone' => 'PST8PDT'), //	 California: W Riverside County (split from 909; eff 7/17/04)
									303	=> array('country' => 'US', 'province' => 'CO', 'time_zone' => 'MST7MDT'), //	 Central Colorado: Denver (see 970, also 720 overlay)
									719	=> array('country' => 'US', 'province' => 'CO', 'time_zone' => 'MST7MDT'), //	 SE Colorado: Pueblo, Colorado Springs
									720	=> array('country' => 'US', 'province' => 'CO', 'time_zone' => 'MST7MDT'), //	 Central Colorado: Denver (overlaid on 303)
									970	=> array('country' => 'US', 'province' => 'CO', 'time_zone' => 'MST7MDT'), //	 N and W Colorado (part of what used to be 303)
									203	=> array('country' => 'US', 'province' => 'CT', 'time_zone' => 'EST5EDT'), //	 Connecticut: Fairfield County and New Haven County; Bridgeport, New Haven (see 860)
									475	=> array('country' => 'US', 'province' => 'CT', 'time_zone' => 'EST5EDT'), //	 Connecticut: New Haven, Greenwich, southwestern (postponed; was perm 1/6/01; mand 3/1/01???)
									860	=> array('country' => 'US', 'province' => 'CT', 'time_zone' => 'EST5EDT'), //	 Connecticut: areas outside of Fairfield and New Haven Counties (split from 203, overlay 959)
									959	=> array('country' => 'US', 'province' => 'CT', 'time_zone' => 'EST5EDT'), //	 Connecticut: Hartford, New London (postponed; was overlaid on 860 perm 1/6/01; mand 3/1/01???)
									202	=> array('country' => 'US', 'province' => 'DC', 'time_zone' => 'EST5EDT'), //	 Washington, D.C.
									302	=> array('country' => 'US', 'province' => 'DE', 'time_zone' => 'EST5EDT'), //	 Delaware
									239	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida (Lee, Collier, and Monroe Counties, excl the Keys; see 305; eff 3/11/02; mand 3/11/03)
									305	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 SE Florida: Miami, the Keys (see 786, 954; 239)
									321	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Brevard County, Cape Canaveral area; Metro Orlando (split from 407)
									352	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Gainesville area, Ocala, Crystal River (split from 904)
									386	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 N central Florida: Lake City (split from 904, perm 2/15/01, mand 11/5/01)
									407	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Central Florida: Metro Orlando (see overlay 689, eff 7/02; split 321)
									561	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 S. Central Florida: Palm Beach County (West Palm Beach, Boca Raton, Vero Beach; see split 772, eff 2/11/02; mand 11/11/02)
									689	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Central Florida: Metro Orlando (see overlay 321; overlaid on 407, assigned but not in use)
									727	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida Tampa Metro: Saint Petersburg, Clearwater (Pinellas and parts of Pasco County; split from 813)
									754	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Broward County area, incl Ft. Lauderdale (overlaid on 954; perm 8/1/01, mand 9/1/01)
									772	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 S. Central Florida: St. Lucie, Martin, and Indian River counties (split from 561; eff 2/11/02; mand 11/11/02)
									786	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 SE Florida, Monroe County (Miami; overlaid on 305)
									813	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 SW Florida: Tampa Metro (splits 727 St. Petersburg, Clearwater, and 941 Sarasota)
									850	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'CST6CDT'), ///-5	 Florida panhandle, from east of Tallahassee to Pensacola (split from 904); western panhandle (Pensacola, Panama City) are UTC-6
									863	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Lakeland, Polk County (split from 941)
									904	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 N Florida: Jacksonville (see splits 352, 386, 850)
									927	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Cellular coverage in Orlando area
									941	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 SW Florida: Sarasota and Manatee counties (part of what used to be 813; see split 863)
									954	=> array('country' => 'US', 'province' => 'FL', 'time_zone' => 'EST5EDT'), //	 Florida: Broward County area, incl Ft. Lauderdale (part of what used to be 305, see overlay 754)
									229	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 SW Georgia: Albany (split from 912; see also 478; perm 8/1/00)
									404	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 N Georgia: Atlanta and suburbs (see overlay 678, split 770)
									470	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 Georgia: Greater Atlanta Metropolitan Area (overlaid on 404/770/678; mand 9/2/01)
									478	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 Central Georgia: Macon (split from 912; see also 229; perm 8/1/00; mand 8/1/01)
									678	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 N Georgia: metropolitan Atlanta (overlay; see 404, 770)
									706	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 N Georgia: Columbus, Augusta (see overlay 762)
									762	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 N Georgia: Columbus, Augusta (overlaid on 706)
									770	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 Georgia: Atlanta suburbs: outside of I-285 ring road (part of what used to be 404; see also overlay 678)
									912	=> array('country' => 'US', 'province' => 'GA', 'time_zone' => 'EST5EDT'), //	 SE Georgia: Savannah (see splits 229, 478)
									671	=> array('country' => 'US', 'province' => 'GU', 'time_zone' => 'US/Guam'), //0*	 Guam
									808	=> array('country' => 'US', 'province' => 'HI', 'time_zone' => 'US/Hawaii'), //0*	 Hawaii
									319	=> array('country' => 'US', 'province' => 'IA', 'time_zone' => 'CST6CDT'), //	 E Iowa: Cedar Rapids (see split 563)
									515	=> array('country' => 'US', 'province' => 'IA', 'time_zone' => 'CST6CDT'), //	 Cent. Iowa: Des Moines (see split 641)
									563	=> array('country' => 'US', 'province' => 'IA', 'time_zone' => 'CST6CDT'), //	 E Iowa: Davenport, Dubuque (split from 319, eff 3/25/01)
									641	=> array('country' => 'US', 'province' => 'IA', 'time_zone' => 'CST6CDT'), //	 Iowa: Mason City, Marshalltown, Creston, Ottumwa (split from 515; perm 7/9/00)
									712	=> array('country' => 'US', 'province' => 'IA', 'time_zone' => 'CST6CDT'), //	 W Iowa: Council Bluffs
									208	=> array('country' => 'US', 'province' => 'ID', 'time_zone' => 'MST7MDT'), ///-8	 Idaho
									217	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Cent. Illinois: Springfield
									224	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Northern NE Illinois: Evanston, Waukegan, Northbrook (overlay on 847, eff 1/5/02)
									309	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 W Cent. Illinois: Peoria
									312	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Illinois: Chicago (downtown only -- in the loop; see 773; overlay 872)
									331	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 W NE Illinois, western suburbs of Chicago (part of what used to be 708; overlaid on 630; eff 7/07)
									464	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Illinois: south suburbs of Chicago (see 630; overlaid on 708)
									618	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 S Illinois: Centralia
									630	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 W NE Illinois, western suburbs of Chicago (part of what used to be 708; overlay 331)
									708	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Illinois: southern and western suburbs of Chicago (see 630; overlay 464)
									773	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Illinois: city of Chicago, outside the loop (see 312; overlay 872)
									779	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 NW Illinois: Rockford, Kankakee (overlaid on 815; eff 8/19/06, mand 2/17/07)
									815	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 NW Illinois: Rockford, Kankakee (see overlay 779; eff 8/19/06, mand 2/17/07)
									847	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Northern NE Illinois: northwestern suburbs of chicago (Evanston, Waukegan, Northbrook; see overlay 224)
									872	=> array('country' => 'US', 'province' => 'IL', 'time_zone' => 'CST6CDT'), //	 Illinois: Chicago (downtown only -- in the loop; see 773; overlaid on 312 and 773)
									219	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'CST6CDT'), ///-5	 NW Indiana: Gary (see split 574, 260)
									260	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'EST5EDT'), //	 NE Indiana: Fort Wayne (see 219)
									317	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'EST5EDT'), //	 Cent. Indiana: Indianapolis (see 765)
									574	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'EST5EDT'), //	 N Indiana: Elkhart, South Bend (split from 219)
									765	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'EST5EDT'), //	 Indiana: outside Indianapolis (split from 317)
									812	=> array('country' => 'US', 'province' => 'IN', 'time_zone' => 'CST6CDT'), ///-5	 S Indiana: Evansville, Cincinnati outskirts in IN, Columbus, Bloomington (mostly GMT-5)
									316	=> array('country' => 'US', 'province' => 'KS', 'time_zone' => 'CST6CDT'), //	 S Kansas: Wichita (see split 620)
									620	=> array('country' => 'US', 'province' => 'KS', 'time_zone' => 'CST6CDT'), //	 S Kansas: Wichita (split from 316; perm 2/3/01)
									785	=> array('country' => 'US', 'province' => 'KS', 'time_zone' => 'CST6CDT'), //	 N & W Kansas: Topeka (split from 913)
									913	=> array('country' => 'US', 'province' => 'KS', 'time_zone' => 'CST6CDT'), //	 Kansas: Kansas City area (see 785)
									270	=> array('country' => 'US', 'province' => 'KY', 'time_zone' => 'CST6CDT'), //	 W Kentucky: Bowling Green, Paducah (split from 502)
									502	=> array('country' => 'US', 'province' => 'KY', 'time_zone' => 'EST5EDT'), //	 N Central Kentucky: Louisville (see 270)
									606	=> array('country' => 'US', 'province' => 'KY', 'time_zone' => 'EST5EDT'), ///-6	 E Kentucky: area east of Frankfort: Ashland (see 859)
									859	=> array('country' => 'US', 'province' => 'KY', 'time_zone' => 'EST5EDT'), //	 N and Central Kentucky: Lexington; suburban KY counties of Cincinnati OH metro area; Covington, Newport, Ft. Thomas, Ft. Wright, Florence (split from 606)
									225	=> array('country' => 'US', 'province' => 'LA', 'time_zone' => 'CST6CDT'), //	 Louisiana: Baton Rouge, New Roads, Donaldsonville, Albany, Gonzales, Greensburg, Plaquemine, Vacherie (split from 504)
									318	=> array('country' => 'US', 'province' => 'LA', 'time_zone' => 'CST6CDT'), //	 N Louisiana: Shreveport, Ruston, Monroe, Alexandria (see split 337)
									337	=> array('country' => 'US', 'province' => 'LA', 'time_zone' => 'CST6CDT'), //	 SW Louisiana: Lake Charles, Lafayette (see split 318)
									504	=> array('country' => 'US', 'province' => 'LA', 'time_zone' => 'CST6CDT'), //	 E Louisiana: New Orleans metro area (see splits 225, 985)
									985	=> array('country' => 'US', 'province' => 'LA', 'time_zone' => 'CST6CDT'), //	 E Louisiana: SE/N shore of Lake Pontchartrain: Hammond, Slidell, Covington, Amite, Kentwood, area SW of New Orleans, Houma, Thibodaux, Morgan City (split from 504; perm 2/12/01; mand 10/22/01)
									339	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: Boston suburbs, to the south and west (see splits 617, 508; overlaid on 781, eff 5/2/01)
									351	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: north of Boston to NH, 508, and 781 (overlaid on 978, eff 4/2/01)
									413	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 W Massachusetts: Springfield
									508	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Cent. Massachusetts: Framingham; Cape Cod (see split 978, overlay 774)
									617	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: greater Boston (see overlay 857)
									774	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Cent. Massachusetts: Framingham; Cape Cod (see split 978, overlaid on 508, eff 4/2/01)
									781	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: Boston surburbs, to the north and west (see splits 617, 508; overlay 339)
									857	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: greater Boston (overlaid on 617, eff 4/2/01)
									978	=> array('country' => 'US', 'province' => 'MA', 'time_zone' => 'EST5EDT'), //	 Massachusetts: north of Boston to NH (see split 978 -- this is the northern half of old 508; see overlay 351)
									240	=> array('country' => 'US', 'province' => 'MD', 'time_zone' => 'EST5EDT'), //	 W Maryland: Silver Spring, Frederick, Gaithersburg (overlay, see 301)
									301	=> array('country' => 'US', 'province' => 'MD', 'time_zone' => 'EST5EDT'), //	 W Maryland: Silver Spring, Frederick, Camp Springs, Prince George's County (see 240)
									410	=> array('country' => 'US', 'province' => 'MD', 'time_zone' => 'EST5EDT'), //	 E Maryland: Baltimore, Annapolis, Chesapeake Bay area, Ocean City (see 443)
									443	=> array('country' => 'US', 'province' => 'MD', 'time_zone' => 'EST5EDT'), //	 E Maryland: Baltimore, Annapolis, Chesapeake Bay area, Ocean City (overlaid on 410)
									207	=> array('country' => 'US', 'province' => 'ME', 'time_zone' => 'EST5EDT'), //	 Maine
									231	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 W Michigan: Northwestern portion of lower Peninsula; Traverse City, Muskegon, Cheboygan, Alanson
									248	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Michigan: Oakland County, Pontiac (split from 810; see overlay 947)
									269	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 SW Michigan: Kalamazoo, Saugatuck, Hastings, Battle Creek, Sturgis to Lake Michigan (split from 616)
									278	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Michigan (overlaid on 734, SUSPENDED)
									313	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Michigan: Detroit and suburbs (see 734, overlay 679)
									517	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Cent. Michigan: Lansing (see split 989)
									586	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Michigan: Macomb County (split from 810; perm 9/22/01, mand 3/23/02)
									616	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 W Michigan: Holland, Grand Haven, Greenville, Grand Rapids, Ionia (see split 269)
									679	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), ///-6	 Michigan: Dearborn area (overlaid on 313; assigned but not in use)
									734	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 SE Michigan: west and south of Detroit -- Ann Arbor, Monroe (split from 313)
									810	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 E Michigan: Flint, Pontiac (see 248; split 586)
									906	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'CST6CDT'), ///-5	 Upper Peninsula Michigan: Sault Ste. Marie, Escanaba, Marquette (UTC-6 towards the WI border)
									947	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), ///-6	 Michigan: Oakland County (overlays 248, perm 5/5/01)
									989	=> array('country' => 'US', 'province' => 'MI', 'time_zone' => 'EST5EDT'), //	 Upper central Michigan: Mt Pleasant, Saginaw (split from 517; perm 4/7/01)
									218	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 N Minnesota: Duluth
									320	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 Cent. Minnesota: Saint Cloud (rural Minn, excl St. Paul/Minneapolis)
									507	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 S Minnesota: Rochester, Mankato, Worthington
									612	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 Cent. Minnesota: Minneapolis (split from St. Paul, see 651; see splits 763, 952)
									651	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 Cent. Minnesota: St. Paul (split from Minneapolis, see 612)
									763	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 Minnesota: Minneapolis NW (split from 612; see also 952)
									952	=> array('country' => 'US', 'province' => 'MN', 'time_zone' => 'CST6CDT'), //	 Minnesota: Minneapolis SW, Bloomington (split from 612; see also 763)
									314	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 SE Missouri: St Louis city and parts of the metro area only (see 573, 636, overlay 557)
									417	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 SW Missouri: Springfield
									557	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 SE Missouri: St Louis metro area only (cancelled: overlaid on 314)
									573	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 SE Missouri: excluding St Louis metro area, includes Central/East Missouri, area between St. Louis and Kansas City
									636	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 Missouri: W St. Louis metro area of St. Louis county, St. Charles County, Jefferson County area south (between 314 and 573)
									660	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 N Missouri (split from 816)
									816	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 N Missouri: Kansas City (see split 660, overlay 975)
									975	=> array('country' => 'US', 'province' => 'MO', 'time_zone' => 'CST6CDT'), //	 N Missouri: Kansas City (overlaid on 816)
									670	=> array('country' => 'US', 'province' => 'MP', 'time_zone' => NULL), //0*	 Commonwealth of the Northern Mariana Islands (CNMI, US Commonwealth)
									228	=> array('country' => 'US', 'province' => 'MS', 'time_zone' => 'CST6CDT'), //	 S Mississippi (coastal areas, Biloxi, Gulfport; split from 601)
									601	=> array('country' => 'US', 'province' => 'MS', 'time_zone' => 'CST6CDT'), //	 Mississippi: Meridian, Jackson area (see splits 228, 662; overlay 769)
									662	=> array('country' => 'US', 'province' => 'MS', 'time_zone' => 'CST6CDT'), //	 N Mississippi: Tupelo, Grenada (split from 601)
									769	=> array('country' => 'US', 'province' => 'MS', 'time_zone' => 'CST6CDT'), //	 Mississippi: Meridian, Jackson area (overlaid on 601; perm 7/19/04, mand 3/14/05)
									406	=> array('country' => 'US', 'province' => 'MT', 'time_zone' => 'MST7MDT'), //	 Montana
									252	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 E North Carolina (Rocky Mount; split from 919)
									336	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 Cent. North Carolina: Greensboro, Winston-Salem, High Point (split from 910)
									704	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 W North Carolina: Charlotte (see split 828, overlay 980)
									828	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 W North Carolina: Asheville (split from 704)
									910	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 S Cent. North Carolina: Fayetteville, Wilmington (see 336)
									919	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 E North Carolina: Raleigh (see split 252, overlay 984)
									980	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 North Carolina: (overlay on 704; perm 5/1/00, mand 3/15/01)
									984	=> array('country' => 'US', 'province' => 'NC', 'time_zone' => 'EST5EDT'), //	 E North Carolina: Raleigh (overlaid on 919, perm 8/1/01, mand 2/5/02 POSTPONED)
									701	=> array('country' => 'US', 'province' => 'ND', 'time_zone' => 'CST6CDT'), //	 North Dakota
									308	=> array('country' => 'US', 'province' => 'NE', 'time_zone' => 'CST6CDT'), ///-7	 W Nebraska: North Platte
									402	=> array('country' => 'US', 'province' => 'NE', 'time_zone' => 'CST6CDT'), //	 E Nebraska: Omaha, Lincoln
									603	=> array('country' => 'US', 'province' => 'NH', 'time_zone' => 'EST5EDT'), //	 New Hampshire
									201	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 N New Jersey: Jersey City, Hackensack (see split 973, overlay 551)
									551	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 N New Jersey: Jersey City, Hackensack (overlaid on 201)
									609	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 S New Jersey: Trenton (see 856)
									732	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 Cent. New Jersey: Toms River, New Brunswick, Bound Brook (see overlay 848)
									848	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 Cent. New Jersey: Toms River, New Brunswick, Bound Brook (see overlay 732)
									856	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 SW New Jersey: greater Camden area, Mt Laurel (split from 609)
									862	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 N New Jersey: Newark Paterson Morristown (overlaid on 973)
									908	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 Cent. New Jersey: Elizabeth, Basking Ridge, Somerville, Bridgewater, Bound Brook
									973	=> array('country' => 'US', 'province' => 'NJ', 'time_zone' => 'EST5EDT'), //	 N New Jersey: Newark, Paterson, Morristown (see overlay 862; split from 201)
									505	=> array('country' => 'US', 'province' => 'NM', 'time_zone' => 'MST7MDT'), //	 North central and northwestern New Mexico (Albuquerque, Santa Fe, Los Alamos; see split 575, eff 10/07/07)
									575	=> array('country' => 'US', 'province' => 'NM', 'time_zone' => 'MST7MDT'), //	 New Mexico (Las Cruces, Alamogordo, Roswell; split from 505, eff 10/07/07)
									957	=> array('country' => 'US', 'province' => 'NM', 'time_zone' => 'MST7MDT'), //	 New Mexico (pending; region unknown)
									702	=> array('country' => 'US', 'province' => 'NV', 'time_zone' => 'PST8PDT'), //	 S. Nevada: Clark County, incl Las Vegas (see 775)
									775	=> array('country' => 'US', 'province' => 'NV', 'time_zone' => 'PST8PDT'), //	 N. Nevada: Reno (all of NV except Clark County area; see 702)
									212	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York City, New York (Manhattan; see 646, 718)
									315	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 N Cent. New York: Syracuse
									347	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York (overlay for 718: NYC area, except Manhattan)
									516	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York: Nassau County, Long Island; Hempstead (see split 631)
									518	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 NE New York: Albany
									585	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 NW New York: Rochester (split from 716)
									607	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 S Cent. New York: Ithaca, Binghamton; Catskills
									631	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York: Suffolk County, Long Island; Huntington, Riverhead (split 516)
									646	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York (overlay 212/917) NYC: Manhattan only
									716	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 NW New York: Buffalo (see split 585)
									718	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York City, New York (Queens, Staten Island, The Bronx, and Brooklyn; see 212, 347)
									845	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York: Poughkeepsie; Nyack, Nanuet, Valley Cottage, New City, Putnam, Dutchess, Rockland, Orange, Ulster and parts of Sullivan counties in New York's lower Hudson Valley and Delaware County in the Catskills (see 914; perm 6/5/00)
									914	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 S New York: Westchester County (see 845)
									917	=> array('country' => 'US', 'province' => 'NY', 'time_zone' => 'EST5EDT'), //	 New York: New York City (cellular, see 646)
									216	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 Cleveland (see splits 330, 440)
									234	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 NE Ohio: Canton, Akron (overlaid on 330; perm 10/30/00)
									283	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 SW Ohio: Cincinnati (cancelled: overlaid on 513)
									330	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 NE Ohio: Akron, Canton, Youngstown; Mahoning County, parts of Trumbull/Warren counties (see splits 216, 440, overlay 234)
									380	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 Ohio: Columbus (overlaid on 614; assigned but not in use)
									419	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 NW Ohio: Toledo (see overlay 567, perm 1/1/02)
									440	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 Ohio: Cleveland metro area, excluding Cleveland (split from 216, see also 330)
									513	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 SW Ohio: Cincinnati (see split 937; overlay 283 cancelled)
									567	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 NW Ohio: Toledo (overlaid on 419, perm 1/1/02)
									614	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 SE Ohio: Columbus (see overlay 380)
									740	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 SE Ohio (rural areas outside Columbus; split from 614)
									937	=> array('country' => 'US', 'province' => 'OH', 'time_zone' => 'EST5EDT'), //	 SW Ohio: Dayton (part of what used to be 513)
									405	=> array('country' => 'US', 'province' => 'OK', 'time_zone' => 'CST6CDT'), //	 W Oklahoma: Oklahoma City (see 580)
									580	=> array('country' => 'US', 'province' => 'OK', 'time_zone' => 'CST6CDT'), //	 W Oklahoma (rural areas outside Oklahoma City; split from 405)
									918	=> array('country' => 'US', 'province' => 'OK', 'time_zone' => 'CST6CDT'), //	 E Oklahoma: Tulsa
									503	=> array('country' => 'US', 'province' => 'OR', 'time_zone' => 'PST8PDT'), //	 Oregon (see 541, 971)
									541	=> array('country' => 'US', 'province' => 'OR', 'time_zone' => 'PST8PDT'), ///-7	 Oregon: Eugene, Medford (split from 503; 503 retains NW part [Portland/Salem], all else moves to 541; eastern oregon is UTC-7)
									971	=> array('country' => 'US', 'province' => 'OR', 'time_zone' => 'PST8PDT'), //	 Oregon: Metropolitan Portland, Salem/Keizer area, incl Cricket Wireless (see 503; perm 10/1/00)
									215	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SE Pennsylvania: Philadelphia (see overlays 267)
									267	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SE Pennsylvania: Philadelphia (see 215)
									412	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 W Pennsylvania: Pittsburgh (see split 724, overlay 878)
									484	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (see 610)
									570	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 NE and N Central Pennsylvania: Wilkes-Barre, Scranton (see 717)
									610	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (see overlays 484, 835)
									717	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 E Pennsylvania: Harrisburg (see split 570)
									724	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SW Pennsylvania (areas outside metro Pittsburgh; split from 412)
									814	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 Cent. Pennsylvania: Erie
									835	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (overlaid on 610, eff 5/1/01; see also 484)
									878	=> array('country' => 'US', 'province' => 'PA', 'time_zone' => 'EST5EDT'), //	 Pittsburgh, New Castle (overlaid on 412, perm 8/17/01, mand t.b.a.)
									787	=> array('country' => 'US', 'province' => 'PR', 'time_zone' => 'AST4ADT'), //*	 Puerto Rico (see overlay 939, perm 8/1/01)
									939	=> array('country' => 'US', 'province' => 'PR', 'time_zone' => 'AST4ADT'), //*	 Puerto Rico (overlaid on 787, perm 8/1/01)
									401	=> array('country' => 'US', 'province' => 'RI', 'time_zone' => 'EST5EDT'), //	 Rhode Island
									803	=> array('country' => 'US', 'province' => 'SC', 'time_zone' => 'EST5EDT'), //	 South Carolina: Columbia, Aiken, Sumter (see 843, 864)
									843	=> array('country' => 'US', 'province' => 'SC', 'time_zone' => 'EST5EDT'), //	 South Carolina, coastal area: Charleston, Beaufort, Myrtle Beach (split from 803)
									864	=> array('country' => 'US', 'province' => 'SC', 'time_zone' => 'EST5EDT'), //	 South Carolina, upstate area: Greenville, Spartanburg (split from 803)
									605	=> array('country' => 'US', 'province' => 'SD', 'time_zone' => 'CST6CDT'), ///-7	 South Dakota
									423	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'EST5EDT'), //	 E Tennessee, except Knoxville metro area: Chattanooga, Bristol, Johnson City, Kingsport, Greeneville (see split 865; part of what used to be 615)
									615	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'CST6CDT'), //	 Northern Middle Tennessee: Nashville metro area (see 423, 931)
									731	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'CST6CDT'), //	 W Tennessee: outside Memphis metro area (split from 901, perm 2/12/01, mand 9/17/01)
									865	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'EST5EDT'), //	 E Tennessee: Knoxville, Knox and adjacent counties (split from 423; part of what used to be 615)
									901	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'CST6CDT'), //	 W Tennessee: Memphis metro area (see 615, 931, split 731)
									931	=> array('country' => 'US', 'province' => 'TN', 'time_zone' => 'CST6CDT'), //	 Middle Tennessee: semi-circular ring around Nashville (split from 615)
									210	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 S Texas: San Antonio (see also splits 830, 956)
									214	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Dallas Metro (overlays 469/972)
									254	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Central Texas (Waco, Stephenville; split, see 817, 940)
									281	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Houston Metro (split 713; overlay 832)
									325	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Central Texas: Abilene, Sweetwater, Snyder, San Angelo (split from 915)
									361	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 S Texas: Corpus Christi (split from 512; eff 2/13/99)
									409	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 SE Texas: Galveston, Port Arthur, Beaumont (splits 936, 979)
									430	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 NE Texas: Tyler (overlaid on 903, eff 7/20/02)
									432	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'MST7MDT'), ///-6	 W Texas: Big Spring, Midland, Odessa (split from 915, eff 4/5/03)
									469	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Dallas Metro (overlays 214/972)
									512	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 S Texas: Austin (see split 361; overlay 737, perm 11/10/01)
									682	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Fort Worth areas (perm 10/7/00, mand 12/9/00)
									713	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Mid SE Texas: central Houston (split, 281; overlay 832)
									737	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 S Texas: Austin (overlaid on 512, suspended; see also 361)
									806	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Panhandle Texas: Amarillo, Lubbock
									817	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 N Cent. Texas: Fort Worth area (see 254, 940)
									830	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: region surrounding San Antonio (split from 210)
									832	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Houston (overlay 713/281)
									903	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 NE Texas: Tyler (see overlay 430, eff 7/20/02)
									915	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'MST7MDT'), ///-6	 W Texas: El Paso (see splits 325 eff 4/5/03; 432, eff 4/5/03)
									936	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 SE Texas: Conroe, Lufkin, Nacogdoches, Crockett (split from 409, see also 979)
									940	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 N Cent. Texas: Denton, Wichita Falls (split from 254, 817)
									956	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Valley of Texas area; Harlingen, Laredo (split from 210)
									972	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 Texas: Dallas Metro (overlays 214/469)
									979	=> array('country' => 'US', 'province' => 'TX', 'time_zone' => 'CST6CDT'), //	 SE Texas: Bryan, College Station, Bay City (split from 409, see also 936)
									385	=> array('country' => 'US', 'province' => 'UT', 'time_zone' => 'MST7MDT'), //	 Utah: Salt Lake City Metro (split from 801, eff 3/30/02 POSTPONED; see also 435)
									435	=> array('country' => 'US', 'province' => 'UT', 'time_zone' => 'MST7MDT'), //	 Rural Utah outside Salt Lake City metro (see split 801)
									801	=> array('country' => 'US', 'province' => 'UT', 'time_zone' => 'MST7MDT'), //	 Utah: Salt Lake City Metro (see split 385, eff 3/30/02; see also split 435)
									236	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 Virginia (region unknown) / Unassigned?
									276	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 S and SW Virginia: Bristol, Stuart, Martinsville (split from 540; perm 9/1/01, mand 3/16/02)
									434	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 E Virginia: Charlottesville, Lynchburg, Danville, South Boston, and Emporia (split from 804, eff 6/1/01; see also 757)
									540	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 Western and Southwest Virginia: Shenandoah and Roanoke valleys: Fredericksburg, Harrisonburg, Roanoke, Salem, Lexington and nearby areas (see split 276; split from 703)
									571	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 Northern Virginia: Arlington, McLean, Tysons Corner (to be overlaid on 703 3/1/00; see earlier split 540)
									703	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 Northern Virginia: Arlington, McLean, Tysons Corner (see split 540; overlay 571)
									757	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 E Virginia: Tidewater / Hampton Roads area -- Norfolk, Virginia Beach, Chesapeake, Portsmouth, Hampton, Newport News, Suffolk (part of what used to be 804)
									804	=> array('country' => 'US', 'province' => 'VA', 'time_zone' => 'EST5EDT'), //	 E Virginia: Richmond (see splits 757, 434)
									340	=> array('country' => 'US', 'province' => 'VI', 'time_zone' => 'AST4ADT'), //*	 US Virgin Islands (see also 809)
									802	=> array('country' => 'US', 'province' => 'VT', 'time_zone' => 'EST5EDT'), //	 Vermont
									206	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 W Washington state: Seattle and Bainbridge Island (see splits 253, 360, 425; overlay 564)
									253	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 Washington: South Tier - Tacoma, Federal Way (split from 206, see also 425; overlay 564)
									360	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 W Washington State: Olympia, Bellingham (area circling 206, 253, and 425; split from 206; see overlay 564)
									425	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 Washington: North Tier - Everett, Bellevue (split from 206, see also 253; overlay 564)
									509	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 E and Central Washington state: Spokane, Yakima, Walla Walla, Ellensburg
									564	=> array('country' => 'US', 'province' => 'WA', 'time_zone' => 'PST8PDT'), //	 W Washington State: Olympia, Bellingham (overlaid on 360; see also 206, 253, 425; assigned but not in use)
									262	=> array('country' => 'US', 'province' => 'WI', 'time_zone' => 'CST6CDT'), //	 SE Wisconsin: counties of Kenosha, Ozaukee, Racine, Walworth, Washington, Waukesha (split from 414)
									414	=> array('country' => 'US', 'province' => 'WI', 'time_zone' => 'CST6CDT'), //	 SE Wisconsin: Milwaukee County (see splits 920, 262)
									608	=> array('country' => 'US', 'province' => 'WI', 'time_zone' => 'CST6CDT'), //	 SW Wisconsin: Madison
									715	=> array('country' => 'US', 'province' => 'WI', 'time_zone' => 'CST6CDT'), //	 N Wisconsin: Eau Claire, Wausau, Superior
									920	=> array('country' => 'US', 'province' => 'WI', 'time_zone' => 'CST6CDT'), //	 NE Wisconsin: Appleton, Green Bay, Sheboygan, Fond du Lac (from Beaver Dam NE to Oshkosh, Appleton, and Door County; part of what used to be 414)
									304	=> array('country' => 'US', 'province' => 'WV', 'time_zone' => 'EST5EDT'), //	 West Virginia
									307	=> array('country' => 'US', 'province' => 'WY', 'time_zone' => 'MST7MDT'), //	 Wyoming
							);
				break;
			case 'timesheet_view':
				$retval = array(
											10	=> TTi18n::gettext('Calendar'),
											20	=> TTi18n::gettext('List')
									);
				break;

			case 'start_week_day':
				$retval = array(
											0	=> TTi18n::gettext('Sunday'),
											1	=> TTi18n::gettext('Monday'),
											2	=> TTi18n::gettext('Tuesday'),
											3	=> TTi18n::gettext('Wednesday'),
											4	=> TTi18n::gettext('Thursday'),
											5	=> TTi18n::gettext('Friday'),
											6	=> TTi18n::gettext('Saturday'),
									);
				break;
			case 'schedule_icalendar_type':
				$retval = array(
										0 => TTi18n::gettext('Disabled'),
										1 => TTi18n::gettext('Enabled (Authenticated)'),
										2 => TTi18n::gettext('Enabled (UnAuthenticated)'),
									);
				break;
			case 'language':
				$retval = TTi18n::getLanguageArray();

				//Because the array keys are strings, flex needs a sort prefix to maintain the order.
				if ( defined('TIMETREX_API') == TRUE AND TIMETREX_API == TRUE ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1020-user_group' => TTi18n::gettext('Group'),
										'-1030-default_branch' => TTi18n::gettext('Default Branch'),
										'-1040-default_department' => TTi18n::gettext('Default Department'),
										'-1040-default_department' => TTi18n::gettext('Default Department'),

										'-1150-city' => TTi18n::gettext('City'),
										'-1160-province' => TTi18n::gettext('Province/State'),
										'-1170-country' => TTi18n::gettext('Country'),

										'-1120-language_display' => TTi18n::gettext('Language'),
										'-1130-date_format_display' => TTi18n::gettext('Date Format'),
										'-1140-time_format_display' => TTi18n::gettext('Time Format'),
										'-1150-time_zone_display' => TTi18n::gettext('TimeZone'),
										'-1160-time_unit_format_display' => TTi18n::gettext('Time Unit Format'),
										'-1170-items_per_page' => TTi18n::gettext('Items Per Page'),
										//'-1180-timesheet_view_display' => TTi18n::gettext('TimeSheet View'),
										'-1190-start_week_day_display' => TTi18n::gettext('Start Weekday'),
										//'-1100-enable_email_notification_exception' => TTi18n::gettext('Email Notification Exception'),
										//'-1110-enable_email_notification_message' => TTi18n::gettext('Email Notification Message'),
										//'-1110-enable_email_notification_pay_stub' => TTi18n::gettext('Email Notification Pay Stub'),
										//'-1120-enable_email_notification_home' => TTi18n::gettext('Email Notification Home'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'date_format_display',
								'time_format_display',
								'time_unit_format_display',
								'time_zone_display',
								);
				break;
		}


		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',

										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_name' => FALSE,
										'user_status_id' => FALSE,
										'user_status' => FALSE,
										'group_id' => FALSE,
										'user_group' => FALSE,
										'title_id' => FALSE,
										'title' => FALSE,
										'default_branch_id' => FALSE,
										'default_branch' => FALSE,
										'default_department_id' => FALSE,
										'default_department' => FALSE,

										'city' => FALSE,
										'province' => FALSE,
										'country' => FALSE,

										'language' => 'Language',
										'date_format' => 'DateFormat',
										'time_format' => 'TimeFormat',
										'time_zone' => 'TimeZone',
										'time_unit_format' => 'TimeUnitFormat',

										//Ignore when setting.
										'language_display' => FALSE,
										'date_format_display' => FALSE,
										'date_format_example' => FALSE,
										'time_format_display' => FALSE,
										'time_zone_display' => FALSE,
										'time_unit_format_display' => FALSE,

										'items_per_page' => 'ItemsPerPage',
										//'timesheet_view' => 'TimeSheetView',
										'start_week_day' => 'StartWeekDay',
										'start_week_day_display' => FALSE,
										'shortcut_key_sequence' => 'ShortcutKeySequence',
										'enable_always_blank_timesheet_rows' => 'EnableAlwaysBlankTimeSheetRows',
										'enable_auto_context_menu' => 'EnableAutoContextMenu',
										'enable_report_open_new_window' => 'EnableReportOpenNewWindow',

										'enable_email_notification_exception' => 'EnableEmailNotificationException',
										'enable_email_notification_message' => 'EnableEmailNotificationMessage',
										'enable_email_notification_pay_stub' => 'EnableEmailNotificationPayStub',
										'enable_email_notification_home' => 'EnableEmailNotificationHome',

										//'schedule_icalendar_url' => 'ScheduleIcalendarURL',
										'schedule_icalendar_type_id' => 'ScheduleIcalendarType',
										//'schedule_icalendar_event_name' => 'ScheduleIcalendarEventName',
										'schedule_icalendar_alarm1_working' => 'ScheduleIcalendarAlarm1Working',
										'schedule_icalendar_alarm2_working' => 'ScheduleIcalendarAlarm2Working',
										'schedule_icalendar_alarm1_absence' => 'ScheduleIcalendarAlarm1Absence',
										'schedule_icalendar_alarm2_absence' => 'ScheduleIcalendarAlarm2Absence',
										'schedule_icalendar_alarm1_modified' => 'ScheduleIcalendarAlarm1Modified',
										'schedule_icalendar_alarm2_modified' => 'ScheduleIcalendarAlarm2Modified',
										'enable_save_timesheet_state' => 'EnableSaveTimesheetState',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getLanguage() {
		if ( isset($this->data['language']) ) {
			return $this->data['language'];
		}

		return FALSE;
	}
	function setLanguage($value) {
		$value = trim($value);

		$language_options = TTi18n::getLanguageArray();

		$key = Option::getByValue($value, $language_options );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'language',
											$value,
											TTi18n::gettext('Incorrect language'),
											$language_options ) ) {

			$this->data['language'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDateFormatExample() {
		$options = Misc::trimSortPrefix( $this->getOptions('date_format') );

		if ( isset($options[$this->getDateFormat()]) ) {
			//Split at the space
			$split_str = explode(' ', $options[$this->getDateFormat()] );
			if ( isset( $split_str[0] ) ) {
				if ( strlen( $split_str[0] ) <= 8 ) {
					return TTi18n::gettext($options[$this->getDateFormat()]);
				} else {
					return TTi18n::gettext($split_str[0]);
				}
			}
			return TTi18n::gettext($options[$this->getDateFormat()]);
		}

		return FALSE;
	}

	function getJSDateFormat() {
		$js_date_format = Option::getByKey($this->getDateFormat(), $this->getOptions('js_date_format') );
		if ( $js_date_format != '' ) {
			Debug::text('Javascript Date Format: '. $js_date_format, __FILE__, __LINE__, __METHOD__, 10);
			return $js_date_format;
		}

		return '%d-%M-%y';
	}
	function getDateFormat() {
		if ( isset($this->data['date_format']) ) {
			return $this->data['date_format'];
		}

		return FALSE;
	}
	function setDateFormat($date_format) {
		$date_format = trim($date_format);

		$key = Option::getByValue($date_format, $this->getOptions('date_format') );
		if ($key !== FALSE) {
			$date_format = $key;
		}
		Debug::text('Date Format: '. $date_format .' Type: '. gettype($date_format), __FILE__, __LINE__, __METHOD__, 10);

		if (	$date_format == ''
				OR
				$this->Validator->inArrayKey(	'date_format',
												$date_format,
												TTi18n::gettext('Incorrect date format'),
												Misc::trimSortPrefix( $this->getOptions('date_format') ) ) ) {

			$this->data['date_format'] = $date_format;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeFormatExample() {
		$options = $this->getOptions('time_format');

		if ( isset($options[$this->getTimeFormat()]) ) {
			return $options[$this->getTimeFormat()];
		}

		return FALSE;
	}
	function getJSTimeFormat() {
		$js_time_format = Option::getByKey($this->getTimeFormat(), $this->getOptions('js_time_format') );
		if ( $js_time_format != '' ) {
			Debug::text('Javascript Time Format: '. $js_time_format, __FILE__, __LINE__, __METHOD__, 10);
			return $js_time_format;
		}

		return '%l:%M %p';
	}
	function getTimeFormat() {
		if ( isset($this->data['time_format']) ) {
			return $this->data['time_format'];
		}

		return FALSE;
	}
	function setTimeFormat($time_format) {
		$time_format = trim($time_format);

		$key = Option::getByValue($time_format, $this->getOptions('time_format') );
		if ($key !== FALSE) {
			$time_format = $key;
		}

		if ( $this->Validator->inArrayKey(	'time_format',
											$time_format,
											TTi18n::gettext('Incorrect time format'),
											$this->getOptions('time_format')) ) {

			$this->data['time_format'] = $time_format;

			return TRUE;
		}

		return FALSE;
	}

	function getLocationTimeZone( $country, $province, $work_phone = FALSE, $home_phone = FALSE, $default = FALSE ) {
		Debug::text('Country: '. $country .' Province: '. $province .' Work Phone: '. $work_phone .' Home Phone: '. $home_phone .' Default: '. $default, __FILE__, __LINE__, __METHOD__, 9);

		$location_timezones = $this->getOptions('location_timezone');
		$area_code_timezone = $this->getOptions('area_code_timezone');

		$possible_timezones = array();

		//Work phone can be the most accurate.
		if ( $work_phone != '' ) {
			$work_area_code = $this->Validator->getPhoneNumberAreaCode( $work_phone );
			//Make sure the area code matches the province, so if a BC province is specified with a ON area code, we use the province instead of area code.
			if ( $work_area_code !== FALSE
					AND isset($area_code_timezone[$work_area_code])
					AND $area_code_timezone[$work_area_code]['time_zone'] != NULL
					AND $area_code_timezone[$work_area_code]['province'] == $province ) {
				Debug::text('Using Work Phone for timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
				return $area_code_timezone[$work_area_code]['time_zone'];
			}
		}

		//Home phone is the next most accurate
		if ( $home_phone != '' ) {
			$home_area_code = $this->Validator->getPhoneNumberAreaCode( $home_phone );
			//Make sure the area code matches the province, so if a BC province is specified with a ON area code, we use the province instead of area code.
			if ( $home_area_code !== FALSE
					AND isset($area_code_timezone[$home_area_code])
					AND $area_code_timezone[$home_area_code]['time_zone'] != NULL
					AND $area_code_timezone[$home_area_code]['province'] == $province ) {
				Debug::text('Using Home Phone for timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
				return $area_code_timezone[$home_area_code]['time_zone'];
			}
		}

		//Country/province is the last option.
		if ( $country != '' AND isset($location_timezones[$country]) ) {
			if ( $province != '' AND is_array($location_timezones[$country]) AND isset($location_timezones[$country][$province]) AND $location_timezones[$country][$province] != NULL) {
				Debug::text('Using Country/Province for timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
				return Misc::trimSortPrefix( $location_timezones[$country][$province] );
			} elseif ( isset($location_timezones[$country]) AND !is_array($location_timezones[$country]) AND $location_timezones[$country] != NULL ) {
				Debug::text('Using Country for timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
				return Misc::trimSortPrefix( $location_timezones[$country] );
			}
		}

		if ( $default != '' ) {
			Debug::text('Using Default for timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
			return $default;
		}

		Debug::text('Using GMT timezone detection...', __FILE__, __LINE__, __METHOD__, 9);
		return 'GMT';
	}

	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		if ( $this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											TTi18n::gettext('Incorrect time zone'),
											Misc::trimSortPrefix( $this->getOptions('time_zone') ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeUnitFormatExample() {
		$options = $this->getOptions('time_unit_format');

		return $options[$this->getTimeUnitFormat()];
	}
	function getTimeUnitFormat() {
		if ( isset($this->data['time_unit_format']) ) {
			return $this->data['time_unit_format'];
		}

		return FALSE;
	}
	function setTimeUnitFormat($time_unit_format) {
		$time_unit_format = trim($time_unit_format);

		$key = Option::getByValue($time_unit_format, $this->getOptions('time_unit_format') );
		if ($key !== FALSE) {
			$time_unit_format = $key;
		}

		if ( $this->Validator->inArrayKey(	'time_unit_format',
											$time_unit_format,
											TTi18n::gettext('Incorrect time units'),
											$this->getOptions('time_unit_format')) ) {

			$this->data['time_unit_format'] = $time_unit_format;

			return TRUE;
		}

		return FALSE;
	}

	function getItemsPerPage() {
		if ( isset($this->data['items_per_page']) ) {
			return $this->data['items_per_page'];
		}

		return FALSE;
	}
	function setItemsPerPage($items_per_page) {
		$items_per_page = trim($items_per_page);

		if	($items_per_page != '' AND $items_per_page >= 5 AND $items_per_page <= 2000) {

			$this->data['items_per_page'] = $items_per_page;

			return TRUE;
		} else {

			$this->Validator->isTrue(		'items_per_page',
											FALSE,
											TTi18n::gettext('Items per page must be between 5 and 2000'));
		}

		return FALSE;
	}

	//A quick function to change just the timezone, without having to change
	//date formats and such in the process.
	function setTimeZonePreferences() {
		return TTDate::setTimeZone( $this->getTimeZone() );
	}

	function setDateTimePreferences() {
		//TTDate::setTimeZone( $this->getTimeZone() );
		if ( $this->setTimeZonePreferences() == FALSE ) {
			//In case setting the time zone failed, most likely due to MySQL timezone issues.
			return FALSE;
		}

		TTDate::setDateFormat( $this->getDateFormat() );
		TTDate::setTimeFormat( $this->getTimeFormat() );
		TTDate::setTimeUnitFormat(	$this->getTimeUnitFormat() );

		return TRUE;
	}

	function getTimeSheetView() {
		if ( isset($this->data['timesheet_view']) ) {
			return $this->data['timesheet_view'];
		}

		return FALSE;
	}
	function setTimeSheetView($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('timesheet_view') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'timesheet_view',
											$value,
											TTi18n::gettext('Incorrect default TimeSheet view'),
											$this->getOptions('timesheet_view')) ) {

			$this->data['timesheet_view'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getStartWeekDay() {
		if ( isset($this->data['start_week_day']) ) {
			return $this->data['start_week_day'];
		}

		return FALSE;
	}
	function setStartWeekDay($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('start_week_day') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'start_week_day',
											$value,
											TTi18n::gettext('Incorrect day to start a week on'),
											$this->getOptions('start_week_day')) ) {

			$this->data['start_week_day'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Used in Flex interface only, currently its hardcoded for now at least.
	function getShortcutKeySequence() { //Default: CTRL+ALT
		if ( isset($this->data['shortcut_key_sequence']) ) {
			return $this->data['shortcut_key_sequence'];
		}

		return FALSE;
	}
	function setShortcutKeySequence($value) {
		$value = trim($value);

		if	(
				$value == ''
				OR
				(
					$this->Validator->isLength(		'shortcut_key_sequence',
													$value,
													TTi18n::gettext('Shortcut key sequence is too short or too long'),
													0,
													250)
				)
				) {

			$this->data['shortcut_key_sequence'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableAlwaysBlankTimeSheetRows() {
		if ( isset($this->data['enable_always_blank_timesheet_rows']) ) {
			return $this->fromBool( $this->data['enable_always_blank_timesheet_rows'] );
		}

		return FALSE;
	}
	function setEnableAlwaysBlankTimeSheetRows($bool) {
		$this->data['enable_always_blank_timesheet_rows'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableAutoContextMenu() {
		if ( isset($this->data['enable_auto_context_menu']) ) {
			return $this->fromBool( $this->data['enable_auto_context_menu'] );
		}

		return FALSE;
	}
	function setEnableAutoContextMenu($bool) {
		$this->data['enable_auto_context_menu'] = $this->toBool($bool);

		return TRUE;
	}

	function getEnableReportOpenNewWindow() {
		if ( isset($this->data['enable_report_open_new_window']) ) {
			return $this->fromBool( $this->data['enable_report_open_new_window'] );
		}

		return FALSE;
	}
	function setEnableReportOpenNewWindow($bool) {
		$this->data['enable_report_open_new_window'] = $this->toBool($bool);

		return TRUE;
	}

	function getEnableEmailNotificationException() {
		if ( isset($this->data['enable_email_notification_exception']) ) {
			return $this->fromBool( $this->data['enable_email_notification_exception'] );
		}

		return FALSE;
	}
	function setEnableEmailNotificationException($bool) {
		$this->data['enable_email_notification_exception'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableEmailNotificationMessage() {
		if ( isset($this->data['enable_email_notification_message']) ) {
			return $this->fromBool( $this->data['enable_email_notification_message'] );
		}

		return FALSE;
	}
	function setEnableEmailNotificationMessage($bool) {
		$this->data['enable_email_notification_message'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableEmailNotificationPayStub() {
		if ( isset($this->data['enable_email_notification_pay_stub']) ) {
			return $this->fromBool( $this->data['enable_email_notification_pay_stub'] );
		}

		return FALSE;
	}
	function setEnableEmailNotificationPayStub($bool) {
		$this->data['enable_email_notification_pay_stub'] = $this->toBool($bool);

		return TRUE;
	}
	function getEnableEmailNotificationHome() {
		if ( isset($this->data['enable_email_notification_home']) ) {
			return $this->fromBool( $this->data['enable_email_notification_home'] );
		}

		return FALSE;
	}
	function setEnableEmailNotificationHome($bool) {
		$this->data['enable_email_notification_home'] = $this->toBool($bool);

		return TRUE;
	}
	function getScheduleIcalendarType() {
		if ( isset($this->data['schedule_icalendar_type_id']) ) {
			return (int)$this->data['schedule_icalendar_type_id'];
		}

		return FALSE;
	}
	function setScheduleIcalendarType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'schedule_icalendar_type_id',
											$type,
											TTi18n::gettext('Incorrect option to enable calendar synchronization'),
											$this->getOptions('schedule_icalendar_type')) ) {

			$this->data['schedule_icalendar_type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	//Helper functions for dealing with unauthenticated calendar access, required by Google Calendar for now.
	function getScheduleIcalendarURL( $user_name = NULL, $type_id = NULL ) {
		if ( $user_name == '' ) {
			$user_name = $this->getUserObject()->getUserName();
		}

		if ( $type_id == '' ) {
			$type_id = $this->getScheduleIcalendarType();
		}
		
		$retval = Environment::getBaseURL().'ical/ical.php';
		if ( $type_id == 2 ) {
			$retval .= '?u='. $user_name .'&k='. $this->getScheduleIcalendarKey();
		}

		return $retval;
	}
	function checkScheduleICalendarKey( $key ) {
		Debug::text( 'Checking Key: '. $key .' Should Match: '. $this->getScheduleIcalendarKey(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( trim($key) == $this->getScheduleIcalendarKey() ) {
			return TRUE;
		}

		return FALSE;
	}
	function getScheduleIcalendarKey() {
		$salt = $this->getUserObject()->getPasswordSalt();
		$user_id = $this->getUserObject()->getID();
		
		return substr( md5( $this->getScheduleIcalendarEventName().$salt.$user_id ), 0, 12);
	}

	//Currently used as part of the unauthenticated key, so if this changes the key to access the calendar changes too.
	function getScheduleIcalendarEventName() {
		return $this->fromBool( $this->data['schedule_icalendar_event_name'] );
	}
	function setScheduleIcalendarEventName($bool) {
		$this->data['schedule_icalendar_event_name'] = $this->toBool($bool);

		return TRUE;
	}
	function getScheduleIcalendarAlarm1Working() {

		if ( isset($this->data['schedule_icalendar_alarm1_working']) ) {
			return (int)$this->data['schedule_icalendar_alarm1_working'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm1Working($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm1_working',
													$int,
													TTi18n::gettext('Invalid time for alarm #1')) ) {
			$this->data['schedule_icalendar_alarm1_working'] = $int;
			return TRUE;
		}

		return FALSE;

	}
	function getScheduleIcalendarAlarm2Working() {
		if ( isset($this->data['schedule_icalendar_alarm2_working']) ) {
			return (int)$this->data['schedule_icalendar_alarm2_working'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm2Working($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm2_working',
													$int,
													TTi18n::gettext('Invalid time for alarm #2')) ) {
			$this->data['schedule_icalendar_alarm2_working'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function getScheduleIcalendarAlarm1Absence() {
		if ( isset($this->data['schedule_icalendar_alarm1_absence']) ) {
			return (int)$this->data['schedule_icalendar_alarm1_absence'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm1Absence($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm1_absence',
													$int,
													TTi18n::gettext('Invalid time for alarm #1')) ) {
			$this->data['schedule_icalendar_alarm1_absence'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function getScheduleIcalendarAlarm2Absence() {
		if ( isset($this->data['schedule_icalendar_alarm2_absence']) ) {
			return (int)$this->data['schedule_icalendar_alarm2_absence'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm2Absence($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm2_absence',
													$int,
													TTi18n::gettext('Invalid time for alarm #2')) ) {
			$this->data['schedule_icalendar_alarm2_absence'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function getScheduleIcalendarAlarm1Modified() {

		if ( isset($this->data['schedule_icalendar_alarm1_modified']) ) {
			return (int)$this->data['schedule_icalendar_alarm1_modified'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm1Modified($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm1_modified',
													$int,
													TTi18n::gettext('Invalid time for alarm #1')) ) {
			$this->data['schedule_icalendar_alarm1_modified'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function getScheduleIcalendarAlarm2Modified() {
		if ( isset($this->data['schedule_icalendar_alarm2_modified']) ) {
			return (int)$this->data['schedule_icalendar_alarm2_modified'];
		}

		return FALSE;
	}
	function setScheduleIcalendarAlarm2Modified($int) {
		$int = (int)trim($int);
		if	(	$this->Validator->isNumeric(		'schedule_icalendar_alarm2_modified',
													$int,
													TTi18n::gettext('Invalid time for alarm #2')) ) {
			$this->data['schedule_icalendar_alarm2_modified'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function getEnableSaveTimesheetState() {
		if ( isset($this->data['enable_save_timesheet_state']) ) {
			return $this->fromBool( $this->data['enable_save_timesheet_state'] );
		}

		return FALSE;
	}
	function setEnableSaveTimesheetState($bool) {
		$this->data['enable_save_timesheet_state'] = $this->toBool($bool);

		return TRUE;
	}
	function Validate() {
		if ( $this->getUser() == '' ) {
			$this->Validator->isTRUE(	'user',
										FALSE,
										TTi18n::gettext('Invalid User') );

		}

		if ( $this->getDateFormat() == '' ) {
			$this->Validator->isTRUE(	'date_format',
										FALSE,
										TTi18n::gettext('Incorrect date format') );

		}

		return TRUE;
	}

	function isPreferencesComplete() {
		if ( $this->getItemsPerPage() == ''
				OR $this->getTimeZone() == '' ) {
			Debug::text('User Preferences is NOT Complete: ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		Debug::text('User Preferences IS Complete: ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	function preSave() {
		//Check the locale, if its not english, we need to make sure the selected dateformat is correct for the language, or else force it.
		if ( $this->getLanguage() != 'en' ) {
			if ( Option::getByValue( $this->getDateFormat(), $this->getOptions('other_date_format') ) == FALSE ) {
				//Force a change of date format
				$this->setDateFormat('d/m/Y');
				Debug::text('Language changed and date format doesnt match any longer, forcing it to: d/m/Y', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::text('Date format doesnt need fixing...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getUser() );
		if ( is_object( $this->getUserObject() ) ) {
			//CompanyFactory->getEncoding() is used to determine report encodings based on data saved here.
			$this->removeCache( 'encoding_'.$this->getUserObject()->getCompany(), 'company' );
		}

		return TRUE;
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}


	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'user_name':
						case 'user_status_id':
						case 'group_id':
						case 'user_group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'city':
						case 'province':
						case 'country':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;

						//Add the *_display element for each of the below fields.
						case 'language_display':
						case 'date_format_display':
						case 'time_format_display':
						case 'time_zone_display':
						case 'time_unit_format_display':
						case 'timesheet_view_display':
						case 'start_week_day_display':
							switch ( $variable ) {
								case 'language_display':
									$function = 'getLanguage';
									break;
								case 'date_format_display':
									$function = 'getDateFormat';
									break;
								case 'time_format_display':
									$function = 'getTimeFormat';
									break;
								case 'time_zone_display':
									$function = 'getTimeZone';
									break;
								case 'time_unit_format_display':
									$function = 'getTimeUnitFormat';
									break;
								case 'timesheet_view_display':
									$function = 'getTimeSheetView';
									break;
								case 'start_week_day_display':
									$function = 'getStartWeekDay';
									break;
							}

							$variable = str_replace('_display', '', $variable );
							if ( method_exists( $this, $function ) ) {
								$data[$variable.'_display'] = Option::getByKey( $this->$function(), Misc::trimSortPrefix( $this->getOptions( $variable ) ) );
							}
							break;
						case 'date_format_example':
							$data[$variable] = $this->getDateFormatExample();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Preferences').': '. $u_obj->getFullName(FALSE, TRUE), NULL, $this->getTable(), $this );
		}

		return FALSE;
	}
}
?>