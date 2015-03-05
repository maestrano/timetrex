//Global variables and functions will be used everywhere
var Global = function() {
};

Global.app_min_width = 990;

Global.theme = 'default';

Global.KEYCODES = {
	'48': '0',
	'49': '1',
	'50': '2',
	'51': '3',
	'52': '4',
	'53': '5',
	'54': '6',
	'55': '7',
	'56': '8',
	'59': '9',
	'65': 'a',
	'66': 'b',
	'67': 'c',
	'68': 'd',
	'69': 'e',
	'70': 'f',
	'71': 'g',
	'72': 'h',
	'73': 'i',
	'74': 'j',
	'75': 'k',
	'76': 'l',
	'77': 'm',
	'78': 'n',
	'79': 'o',
	'80': 'p',
	'81': 'q',
	'82': 'r',
	'83': 's',
	'84': 't',
	'85': 'u',
	'86': 'v',
	'87': 'w',
	'88': 'x',
	'89': 'y',
	'90': 'z'
};

Global.needReloadBrowser = false; // Need reload browser after set new cookie. To make router work for new session.

// this attribute use to block UI in speical case that we allow users to click part of them and block other parts.
// For example, when open edit view to block context menu.
Global.block_ui = false;

Global.sendErrorReport = function() {
	var api_authentication = new (APIFactory.getAPIClass( 'APIAuthentication' ))();
	var error_string = arguments[0];
	var from_file = arguments[1];
	var line = arguments[2];
	var error_stack = arguments[4];

	var login_user = LocalCacheData.getLoginUser();
	if ( error_string.indexOf( "TypeError: 'null' is not an object" ) >= 0 ) {
		return;
	}

	var error = 'Client Version: ' + APIGlobal.pre_login_data.application_build + '\n\n Uncaught Error From: ' + LocalCacheData.current_open_primary_controller.script_name + '\n\n' + 'Error: ' + error_string + ' in ' + from_file + ' line ' + line + ' ' + '\n\nUser: ' + login_user.user_name + ' ' + '\n\nURL: ' + window.location.href + ' ' + '\n\nUser-Agent: ' + navigator.userAgent + ' ' + '\n\nIE:' + ie;

	//Don't send error report if exception not happens in our codes.
	//from_file should always contains the root url
	if ( from_file.indexOf( ServiceCaller.rootURL ) < 0 ) {
		Global.log( 'Exception caught from unauthorized source, not sending report. Source: "' + ServiceCaller.rootURL + '" Script: ' + from_file );
		return;
	}

	if ( LocalCacheData.getCurrentCompany() ) {
		error = error + '\n\n' + 'Product Edition: ' + LocalCacheData.getCurrentCompany().product_edition_id;
	}

	error = error + '\n\n\n' + 'Clicked target stacks: ' + JSON.stringify( LocalCacheData.ui_click_stack, undefined, 2 );
	error = error + '\n\n\n' + 'API stacks: ' + JSON.stringify( LocalCacheData.api_stack, undefined, 2 );

	if ( error_stack ) {
		var trace = error_stack.stack;
		error = error + '\n\n\n' + 'Function called stacks: ' + trace;
	}

	if ( Global.isCanvasSupported() && ie > 9 ) {
		html2canvas( [document.body], {
			onrendered: function( canvas ) {

				var image_string = canvas.toDataURL().split( ',' )[1];
				api_authentication.sendErrorReport( error, image_string, {
					onResult: function( result ) {
						if ( APIGlobal.pre_login_data.production === true && result.getResult() != APIGlobal.pre_login_data.application_build ) {
							TAlertManager.showAlert( $.i18n._( 'Your web browser is caching incorrect data, please press the refresh button on your web browser or log out, clear your web browsers cache and try logging in again.' ) + '<br><br>' + $.i18n._( 'Local Version' ) + ':  ' + result.getResult() + '<br>' + $.i18n._( 'Remote Version' ) + ': ' + APIGlobal.pre_login_data.application_build, '', function() {
								window.location.reload( true );
							} );
						}

					}
				} );

			}
		} );
	} else {

		api_authentication.sendErrorReport( error, '', {
			onResult: function( result ) {

			}
		} );
	}
}

Global.initStaticStrings = function() {

	Global.any_item = '-- ' + $.i18n._( 'Any' ) + ' --';

	Global.all_item = '-- ' + $.i18n._( 'All' ) + ' --';

	Global.root_item = $.i18n._( 'Root' );

	Global.customize_item = '-- ' + $.i18n._( 'Customize' ) + ' --';

	Global.default_item = '-- ' + $.i18n._( 'Default' ) + ' --';

	Global.open_item = '-- ' + $.i18n._( 'Open' ) + ' --';

	Global.empty_item = '-- ' + $.i18n._( 'None' ) + ' --';

	Global.view_mode_message = $.i18n._( "You are currently in 'View' mode, instead click the 'Edit' icon to modify fields" );

	Global.no_result_message = $.i18n._( 'No Results Found' );

	Global.save_and_continue_message = $.i18n._( 'Please save this record before modifying any related data' );

	Global.no_hierarchy_message = $.i18n._( 'No Hierarchies Defined' );

	Global.modify_alert_message = $.i18n._( 'You have modified data without saving, are you sure you want to continue and lose your changes' );

	Global.delete_confirm_message = $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered Are you sure you wish to continue?' );

};

Global.getUpgradeMessage = function() {
	var message = $.i18n._( 'This functionality is only available in' ) +
		' ' + LocalCacheData.getLoginData().application_name + ' ' + $.i18n._( 'Professional, Corporate, or Enterprise Editions.' ) +
		' ' + $.i18n._( 'For more information please visit' ) + ' <a href="http://www.timetrex.com/r.php?id=810" target="_blank">www.timetrex.com</a>'

	Global.trackView( 'CommunityUpgrade' );
	return message;
}

Global.setupPing = function() {

	var api = new (APIFactory.getAPIClass( 'APIMisc' ))();
	var idle_time = 0;
	$( 'body' ).mousemove( function( e ) {
		doPingIfNecessary();
	} );
	$( 'body' ).keypress( function( e ) {
		doPingIfNecessary();

	} );

	var idle_interval = setInterval( timerIncrement, 60000 ); // 1 minute

	function timerIncrement() {
		idle_time = idle_time + 1;
	}

	function doPingIfNecessary() {

		if ( idle_time < 15 ) {
			idle_time = 0;
			return;
		}
		idle_time = 0;
		if ( LocalCacheData.current_open_primary_controller.viewId === 'LoginView' ) {
			return;
		}

		//Error: Uncaught TypeError: undefined is not a function in https://ondemand2001.timetrex.com/interface/html5/global/Global.js?v=8.0.0-20141230-124906 line 182
		if ( !api || (typeof api.isLoggedIn) !== 'function' ) {
			return;
		}

		api.isLoggedIn( false, {
			onResult: function( result ) {
				var res_data = result.getResult();

				if ( res_data !== true ) {
					api.ping( {
						onResult: function() {

						}
					} );
				}

			}
		} );
	}
};

Global.clearCache = function( function_name ) {
	for ( var key in LocalCacheData.result_cache ) {
		if ( key.indexOf( function_name ) >= 0 ) {
			delete LocalCacheData.result_cache[key];
		}
	}
};

Global.getHost = function() {
	var host = window.location.hostname;

	host = host.substring( (host.indexOf( '.' ) + 1) );

	return host;
};

Global.setWidgetEnabled = function( widget, val ) {
	if ( !val ) {
		widget.attr( 'disabled', 'true' );
		widget.addClass( 'disable-filter' );
	} else {
		widget.removeAttr( 'disabled' );
		widget.removeClass( 'disable-filter' );
	}
};

Global.createViewTabs = function() {

	if ( !LocalCacheData.view_min_tab_bar ) {
		var view_min_tab_bar = Global.loadWidgetByName( WidgetNamesDic.VIEW_MIN_TAB_BAR );
		view_min_tab_bar = $( view_min_tab_bar ).ViewMinTabBar();
		$( 'body' ).append( view_min_tab_bar );

		LocalCacheData.view_min_tab_bar = view_min_tab_bar;
	}

	LocalCacheData.view_min_tab_bar.buildTabs( LocalCacheData.view_min_map );

};

Global.addViewTab = function( view_id, view_name, url ) {

	LocalCacheData.view_min_map[view_id] = view_name;

	LocalCacheData.view_min_map[view_id + '_url'] = url;

	Global.createViewTabs();
};

Global.removeViewTab = function( view_id ) {

	delete LocalCacheData.view_min_map[view_id];
	Global.createViewTabs();
};

Global.cleanViewTab = function() {

	LocalCacheData.view_min_map = {};
	Global.createViewTabs();
};

Global.upCaseFirstLetter = function( str ) {
	str = str.toLowerCase().replace( /\b[a-z]/g, function( letter ) {
		return letter.toUpperCase();
	} );
	return str;
};

Global.convertDateFormatToDatePickerFormat = function( format ) {
//		if ( format.indexOf( 'yyyy' ) >= 0 ) {
//			format = format.replace( 'yyyy', 'yy' );
//		} else {
//			format = format.replace( 'yy', 'y' );
//		}
//
//		if ( format.indexOf( 'mmmm' ) >= 0 ) {
//			format = format.replace( 'mmmm', 'MM' );
//		} else {
//			format = format.replace( 'mmm', 'M' );
//		}
//
//		if ( format.indexOf( 'dddd' ) >= 0 ) {
//			format = format.replace( 'dddd', 'dddd' );
//		} else {
//			format = format.replace( 'ddd', 'dd' );
//		}
//
//		format = format.replace( new RegExp( 'y', 'gm' ), 'Y' );
//
//		format = format.replace( new RegExp( 'm', 'gm' ), 'M' );
//
//		format = format.replace( new RegExp( 'd', 'gm' ), 'D' );

	return format;
};

Global.strToDateTime = function( date_string ) {

	//Why change PM to AM? comments this out see if we meet any date problems
//	if ( date_string.indexOf( '12:' ) >= 0 && date_string.indexOf( 'PM' ) > 0 ) {
//		date_string = date_string.replace( 'PM', 'AM' );
//	}

	//Error: TypeError: Global.strToDateTime(...) is null in https://ondemand3.timetrex.com/interface/html5/framework/jquery.min.js?v=8.0.0-20141117-153515 line 4862
	if ( !date_string ) {
		return null;
	}

	if ( LocalCacheData.loginUserPreference.time_format === 'G:i T' || LocalCacheData.loginUserPreference.time_format === 'g:i A T' ) {
		var date_str_array = date_string.split( ' ' );
		date_str_array.pop();
		date_string = date_str_array.join( ' ' );
	}

	return Date.parse( date_string );
};

//Convert all kinds of date time to mm/dd/yyyy so Date.parse can parse it correct
Global.getStandardDateTimeStr = function( date_str, time_str ) {
	var result = Global.strToDate( date_str ).format( 'MM/DD/YYYY' ) + ' ' + time_str;

	return result;
};

Global.strToDate = function( date_string, format ) {

	//better to use Date.parse, let's see
	if ( !Global.isSet( format ) ) {
		format = LocalCacheData.getLoginUserPreference().date_format;
	}

	format = Global.convertDateFormatToDatePickerFormat( format );

	var date = moment( date_string, format ).toDate();

	return date;
};

Global.updateUserPreference = function( callBack, message ) {
	var user_preference_api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();
	var current_user_aou = new (APIFactory.getAPIClass( 'APICurrentUser' ))();
	var date_api = new (APIFactory.getAPIClass( 'APIDate' ))();

	if ( message ) {
		ProgressBar.changeProgressBarMessage( message );
	}

	current_user_aou.getCurrentUserPreference( {
		onResult: function( result ) {
			var result_data = result.getResult();
			LocalCacheData.loginUserPreference = result_data;
			date_api.getTimeZoneOffset( {
				onResult: function( timeZoneRes ) {

					date_api.getHours( timeZoneRes.getResult(), {
						onResult: function( hoursRes ) {
							var hoursResultData = hoursRes.getResult();

							//Flex way, Need this in js? Let's see
							if ( hoursResultData.indexOf( '-' ) > -1 ) {
								hoursResultData = hoursResultData.replace( '-', '+' );
							} else {
								hoursResultData = hoursResultData.replace( '+', '-' );
							}

							LocalCacheData.loginUserPreference.time_zone_offset = hoursResultData;

							user_preference_api.getOptions( 'jquery_date_format', {
								onResult: function( jsDateFormatRes ) {

									var jsDateFormatResultData = jsDateFormatRes.getResult();

									//For moment date parser
									LocalCacheData.loginUserPreference.js_date_format = {
										'D, F d Y': 'ddd, MMMM DD YYYY',
										'D, M d Y': 'ddd, MMM DD YYYY',
										'D, d-M-Y': 'ddd, DD-MMM-YYYY',
										'D, dMY': 'ddd, DDMMMYYYY',
										'M-d-Y': 'MMM-DD-YYYY',
										'M-d-y': 'MMM-DD-YY',
										'Y-m-d': 'YYYY-MM-DD',
										'd-M-Y': 'DD-MMM-YYYY',
										'd-M-y': 'DD-MMM-YY',
										'd-m-Y': 'DD-MM-YYYY',
										'd-m-y': 'DD-MM-YY',
										'd/m/Y': 'DD/MM/YYYY',
										'd/m/y': 'DD/MM/YY',
										'dMY': 'DDMMMYYYY',
										'l, F d Y': 'dddd, MMMM DD YYYY',
										'm-d-Y': 'MM-DD-YYYY',
										'm-d-y': 'MM-DD-YY',
										'm/d/Y': 'MM/DD/YYYY',
										'm/d/y': 'MM/DD/YY'
									};

									var date_format = LocalCacheData.loginUserPreference.date_format;

									if ( !date_format ) {
										date_format = 'DD-MMM-YY';
									}

									LocalCacheData.loginUserPreference.date_format = LocalCacheData.loginUserPreference.js_date_format[date_format];

									//For date picker
									LocalCacheData.loginUserPreference.js_date_format_1 = jsDateFormatResultData;

									LocalCacheData.loginUserPreference.date_format_1 = LocalCacheData.loginUserPreference.js_date_format_1[date_format];

									user_preference_api.getOptions( 'js_time_format', {
										onResult: function( jsTimeFormatRes ) {

											var jsTimeFormatResultData = jsTimeFormatRes.getResult();

											LocalCacheData.loginUserPreference.js_time_format = jsTimeFormatResultData;

											LocalCacheData.setLoginUserPreference( LocalCacheData.loginUserPreference );

											if ( callBack ) {
												callBack();
											}

										}
									} );

								}
							} );

						}
					} );

				}
			} );

		}
	} );
};

Global.secondToHHMMSS = function( sec_num, force_time_unit ) {

	var add_minus = false;

	var time;

	if ( typeof sec_num === 'undefined' || sec_num === null || sec_num === false ) {
		return null;
	}

//	sec_num = parseFloat( sec_num );

	if ( sec_num < 0 ) {
		sec_num = -sec_num;
		add_minus = true;
	}

	var time_unit = LocalCacheData.getLoginUserPreference().time_unit_format.toString();

	if ( force_time_unit ) {
		time_unit = force_time_unit;
	}

	var hours = sec_num / 3600;
	var minutes = (sec_num - (hours * 3600)) / 60;
	var seconds = (sec_num - (hours * 3600) - (minutes * 60)).toFixed( 0 );

	switch ( time_unit ) {
		case '10':
		case '12':
		case '99':
			hours = Math.floor( sec_num / 3600 );
			minutes = Math.floor( (sec_num - (hours * 3600)) / 60 );
			seconds = (sec_num - (hours * 3600) - (minutes * 60)).toFixed( 0 );
			if ( hours < 10 ) {hours = "0" + hours;}
			if ( minutes < 10 ) {minutes = "0" + minutes;}
			if ( seconds < 10 ) {seconds = "0" + seconds;}

			if ( time_unit === '10' ) {
				time = hours + ':' + minutes;
			} else if ( time_unit === '12' ) {
				time = hours + ':' + minutes + ':' + seconds;
			} else if ( time_unit === '99' ) { //For local use only, in progress bar always show minutes and seconds
				time = minutes + ':' + seconds;
			}
			break;
		case '20':
			hours = hours.toFixed( 2 );
			time = hours;
			break;
		case '22':
			hours = hours.toFixed( 3 );
			time = hours;
			break;
		case '23':
			hours = hours.toFixed( 4 );
			time = hours;
			break;
		case '30':
			minutes = (hours * 60) + minutes;
			minutes = minutes.toFixed( 0 );
			time = minutes;
			break;
		case '40':
			time = sec_num;
			break;
	}

//	if ( include_ss ) {
//
//		if ( no_hour ) {
//			time = minutes + ':' + seconds;
//		} else {
//			time = hours + ':' + minutes + ':' + seconds;
//		}
//
//	} else {
//		time = hours + ':' + minutes;
//	}
//
	if ( add_minus ) {
		time = '-' + time;
	}

	return time;
};

Global.isCanvasSupported = function() {
	var elem = document.createElement( 'canvas' );
	return !!(elem.getContext && elem.getContext( '2d' ));
};

Global.getRandomNum = function() {

	var number = Math.floor( Math.random() * 999 );//0-23

	return number;

};

/* jshint ignore:start */

Global.getScriptNameByAPI = function( api_class ) {

	if ( !api_class ) {
		return null;
	}

	var script_name = '';

	var api_instance = new api_class();

	switch ( api_instance.className ) {
		case 'APIUser':
			script_name = 'EmployeeView';
			break;
		case 'APIBranch':
			script_name = 'BranchView';
			break;
		case 'APIDepartment':
			script_name = 'DepartmentView';
			break;
		case 'APIUserWage':
			script_name = 'WageView';
			break;
		case 'APIUserContact':
			script_name = 'UserContactView';
			break;
		case 'APIUserTitle':
			script_name = 'UserTitleView';
			break;
		case 'APIWageGroup':
			script_name = 'WageGroupView';
			break;
		case 'APILog':
			script_name = 'LogView';
			break;
		case 'APIUserGroup':
			script_name = 'UserGroupView';
			break;
		case 'APIPayStubEntryAccount':
			script_name = 'PayStubEntryAccountView';
			break;
		case 'APIPayPeriod':
		case 'APIPayPeriodSchedule':
			script_name = 'PayPeriodsView';
			break;
		case 'APIAccrual':
			script_name = 'APIAccrual';
			break;
		case 'APIAccrualBalance':
			script_name = 'AccrualBalanceView';
			break;
		case 'APIException':
			script_name = 'ExceptionView';
			break;
		case 'APIJobGroup':
			script_name = 'JobGroupView';
			break;
		case 'APIJob':
			script_name = 'JobView';
			break;
		case 'APIJobItemGroup':
			script_name = 'JobItemGroupView';
			break;
		case 'APIJobItem':
			script_name = 'JobItemView';
			break;
		case 'APIJobItemAmendment':
			script_name = 'JobItemAmendment';
			break;
		case 'APIPunch':
			script_name = 'PunchesView';
			break;
		case 'APIRecurringScheduleControl':
			script_name = 'RecurringScheduleControlView';
			break;

		case 'APIRecurringScheduleTemplateControl':
			script_name = 'RecurringScheduleTemplateControlView';
			break;
		case 'APISchedule':
			script_name = 'ScheduleShiftView';
			break;
		case 'APIBankAccount':
			script_name = 'BankAccountView';
			break;
		case 'APICompany':
			script_name = 'CompanyView';
			break;
		case 'APICurrency':
			script_name = 'CurrencyView';
			break;
		case 'APICurrencyRate':
			script_name = 'CurrencyRate';
			break;
		case 'APIHierarchyControl':
			script_name = 'HierarchyControlView';
			break;
		case 'APIEthnicGroup':
			script_name = 'EthnicGroupView';
			break;
		case 'APIOtherField':
			script_name = 'OtherFieldView';
			break;
		case 'APIPermissionControl':
			script_name = 'PermissionControlView';
			break;
		case 'APIStation':
			script_name = 'StationView';
			break;
		case 'APIDocumentRevision':
			script_name = 'DocumentRevisionView';
			break;
		case 'APIDocumentGroup':
			script_name = 'DocumentGroupView';
			break;
		case 'APIDocument':
			script_name = 'DocumentView';
			break;
		case 'APIROE':
			script_name = 'ROEView';
			break;
		case 'APIUserDefault':
			script_name = 'UserDefaultView';
			break;
		case 'APIUserPreference':
			script_name = 'UserPreferenceView';
			break;
		case 'APIKPI':
			script_name = 'KPIView';
			break;
		case 'APIUserReviewControl':
			script_name = 'UserReviewControlView';
			break;
		case 'APIQualification':
			script_name = 'QualificationView';
			break;
		case 'APIUserEducation':
			script_name = 'UserTitleView';
			break;
		case 'APIUserLanguage':
			script_name = 'UserTitleView';
			break;
		case 'APIUserLicense':
			script_name = 'UserLicenseView';
			break;
		case 'APIUserMembership':
			script_name = 'UserMembershipView';
			break;
		case 'APIUserSkill':
			script_name = 'UserSkillView';
			break;
		case 'APIJobApplicantEducation':
			script_name = 'JobApplicantEducationView';
			break;
		case 'APIJobApplicantEmployment':
			script_name = 'JobApplicantEducationView';
			break;
		case 'APIJobApplicantLanguage':
			script_name = 'JobApplicantLanguageView';
			break;
		case 'APIJobApplicantLicense':
			script_name = 'JobApplicantLicenseView';
			break;
		case 'APIJobApplicantLocation':
			script_name = 'JobApplicantLicenseView';
			break;
		case 'APIJobApplicantMembership':
			script_name = 'JobApplicantMembershipView';
			break;
		case 'APIJobApplicantReference':
			script_name = 'JobApplicantReferenceView';
			break;
		case 'APIJobApplicantSkill':
			script_name = 'JobApplicantSkillView';
			break;
		case 'APIJobApplicant':
			script_name = 'JobApplicantSkillView';
			break;
		case 'APIJobApplication':
			script_name = 'JobApplicationView';
			break;
		case 'APIJobVacancy':
			script_name = 'JobVacancyView';
			break;
		case 'APIAreaPolicy':
			script_name = 'JobVacancyView';
			break;
		case 'APIClient':
			script_name = 'ClientView';
			break;
		case 'APIClientContact':
			script_name = 'ClientContactView';
			break;
		case 'APIClientGroup':
			script_name = 'ClientGroupView';
			break;
		case 'APIClientPayment':
			script_name = 'ClientPaymentView';
			break;
		case 'APIInvoiceDistrict':
			script_name = 'InvoiceDistrictView';
			break;
		case 'APIInvoice':
			script_name = 'InvoiceView';
			break;
		case 'APITransaction':
			script_name = 'InvoiceTransactionView';
			break;
		case 'APIPaymentGateway':
			script_name = 'PaymentGatewayView';
			break;
		case 'APIProductGroup':
			script_name = 'ProductGroupView';
			break;
		case 'APIProduct':
			script_name = 'ProductView';
			break;
		case 'APIInvoiceConfig':
			script_name = 'InvoiceConfigView';
			break;
		case 'APIShippingPolicy':
			script_name = 'ShippingPolicyView';
			break;
		case 'APITaxPolicy':
			script_name = 'TaxPolicyView';
			break;
		case 'APICompanyDeduction':
			script_name = 'CompanyTaxDeductionView';
			break;
		case 'APIPayStub':
			script_name = 'PayStubView';
			break;
		case 'APIPayStubEntry':
			script_name = 'PayStubEntryView';
			break;
		case 'APIPayStubAmendment':
			script_name = 'PayStubAmendmentView';
			break;
		case 'APIRecurringPayStubAmendment':
			script_name = 'RecurringPayStubAmendmentView';
			break;
		case 'APIUserExpense':
			script_name = 'UserExpenseView';
			break;
		case 'APIAbsencePolicy':
			script_name = 'AbsencePolicyView';
			break;
		case 'APIAccrualPolicyAccount':
			script_name = 'AccrualPolicyAccountView';
			break;
		case 'APIAccrualPolicy':
			script_name = 'AccrualPolicyView';
			break;
		case 'APIAccrualPolicyUserModifier':
			script_name = 'AccrualPolicyUserModifierView';
			break;
		case 'APIBreakPolicy':
			script_name = 'BreakPolicyView';
			break;
		case 'APIExceptionPolicyControl':
			script_name = 'ExceptionPolicyControlView';
			break;
		case 'APIExpensePolicy':
			script_name = 'ExpensePolicyView';
			break;
		case 'APIHoliday':
			script_name = 'HolidayView';
			break;
		case 'APIHolidayPolicy':
			script_name = 'HolidayPolicyView';
			break;
		case 'APIMealPolicy':
			script_name = 'MealPolicyView';
			break;
		case 'APIOvertimePolicy':
			script_name = 'OvertimePolicyView';
			break;
		case 'APIPolicyGroup':
			script_name = 'PolicyGroupView';
			break;
		case 'APIPremiumPolicy':
			script_name = 'PremiumPolicyView';
			break;
		case 'APIRecurringHoliday':
			script_name = 'RecurringHolidayView';
			break;
		case 'APIRoundIntervalPolicy':
			script_name = 'RoundIntervalPolicyView';
			break;
		case 'APISchedulePolicy':
			script_name = 'SchedulePolicyView';
			break;
		case 'APIUserReportData':
			script_name = 'UserReportDataView';
			break;
		case 'APIInstall':
			script_name = 'InstallView';
			break;

	}

	return script_name;
};

/* jshint ignore:end */

Global.isArray = function( obj ) {

	if ( Object.prototype.toString.call( obj ) !== '[object Array]' ) {
		return false;
	}

	return true;
};

Global.isString = function( obj ) {

	if ( Object.prototype.toString.call( obj ) !== '[object String]' ) {
		return false;
	}

	return true;
};

Global.buildColumnArray = function( array ) {
	var columns = [];

	var id = 1000;
	for ( var key in array ) {

		var column = {
			label: array[key],
			value: key.replace( /^-[0-9]{3,4}-/i, '' ),
			orderValue: key.substring( 1, 5 ),
			id: id
		};
		columns.push( column );
		id = id + 1;
	}
	return columns;
};

Global.buildTreeRecord = function( array, parentId ) {
	var finalArray = [];

	$.each( array, function( key, item ) {
		item.expanded = true;
		item.loaded = true;

		if ( Global.isSet( parentId ) ) {
			item.parent = parentId;
		}

		finalArray.push( item );

		if ( Global.isSet( item.children ) ) {
			var childrenArray = Global.buildTreeRecord( item.children, item.id );
			finalArray = finalArray.concat( childrenArray );
		} else {
			item.isLeaf = true;
		}

	} );

	return finalArray;
};

Global.getParentIdByTreeRecord = function( array, selectId ) {

	var retval = [];
	for ( var i = 0; i < array.length; i++ ) {
		var item = array[i];
		if ( item.id.toString() === selectId.toString() ) {
			if ( Global.isSet( item.parent ) ) {
				retval.push( {parent_id: item.parent.toString(), name: item.name} );
			} else {
				retval.push( {name: item.name} );
			}
			break;
		}
	}

	return retval;

};

Global.addFirstItemToArray = function( array, firstItemType ) {

	//Error: Unable to get property 'unshift' of undefined or null reference in https://villa.timetrex.com/interface/html5/global/Global.js?v=8.0.0-20141230-153942 line 903 
	if ( array ) {
		if ( firstItemType === 'any' ) {
			array.unshift( {label: Global.any_item, value: '-1', fullValue: '-1', orderValue: ''} );
		} else if ( firstItemType === 'empty' ) {
			array.unshift( {label: Global.empty_item, value: '0', fullValue: '0', orderValue: ''} );
		}
	}

	return array;
};

Global.convertRecordArrayToOptions = function( array ) {
	var len = array.length;
	var options = {};

	for ( var i = 0; i < len; i++ ) {
		var item = array[i];

		options[item.value] = item.label;
	}

	return options;
};

Global.removeSortPrefix = function( array ) {
	var finalArray = {};

	if ( Global.isSet( array ) ) {

		$.each( array, function( key, item ) {
			var has_sort_by;
			if ( typeof key === 'number' ) {
				has_sort_by = null;
			} else {
				has_sort_by = key.match( /-\d{4}-.*/i );
			}

			if ( has_sort_by ) {
				var new_key = key.substring( 6 );
				finalArray[new_key] = item;
			} else {
				finalArray[key] = item;
			}

		} );

		return finalArray;
	}

	return array;
};

Global.convertToNumberIfPossible = function( val ) {
	//if value is number convert to number type
	var reg = new RegExp( '^[0-9]*$' );

	if ( reg.test( val ) && val !== '00' ) {
		val = parseFloat( val )
	}

	if ( val == '-1' ) {
		val = -1;
	}

	return val;
}

Global.buildRecordArray = function( array, first_item, orderType ) {
	var finalArray = [];

	var id = 1000;

	if ( Global.isSet( array ) ) {

		$.each( array, function( key, item ) {
			var has_sort_by;
			if ( typeof key === 'number' ) {
				has_sort_by = null;
			} else {
				has_sort_by = key.match( /-\d{4}-.*/i );
			}

			var value = 0;
			var order_value = 0;

			if ( has_sort_by ) {
				value = key.substring( 6 );
				order_value = key.substring( 1, 5 );
			} else {
				value = key;

			}

			value = Global.convertToNumberIfPossible( value );

//
//			if ( value && value.indexOf( '-' ) === -1 && parseInt( value ) >= 0  ) {
//				value = parseInt( value );
//			}

			// 6/4 changed id to same as value to make flex show correct data when show search result saved in html5, flex use id if it existed.
			var record = {label: item, value: value, fullValue: key, orderValue: order_value, id: value};

			id = id + 1;

			finalArray.push( record );
		} );

	}

	return finalArray;

};

Global.dataConvert = function( result, target ) {

	target.fromJSONToAttributes( result );

	return target;
};

Global.topContainer = function() {
	return $( '#topContainer' );
};

Global.overlay = function() {
	return $( '#overlay' );
};

Global.bottomContainer = function() {
	return $( '#bottomContainer' );
};

Global.contentContainer = function() {
	return $( '#contentContainer' );
};

Global.bodyWidth = function() {
	return $( window ).width();
};

Global.bodyHeight = function() {
	return $( window ).height();
};

Global.loadScriptAsync = function( path, onResult ) {
	if ( LocalCacheData.loadedScriptNames[path] ) {
		onResult();
		return;
	}
	var realPath = path + '?v=' + APIGlobal.pre_login_data.application_build

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	jQuery.getScript( realPath, function() {
		LocalCacheData.loadedScriptNames[path] = true;

		onResult();
	} );
};

Global.getRealImagePath = function( path ) {

	var realPath = 'theme/' + Global.theme + '/' + path;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	return realPath;
};

Global.getRibbonIconRealPath = function( icon ) {
	var realPath = 'theme/' + Global.theme + '/css/global/widgets/ribbon/icons/' + icon;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	return realPath;
};

Global.loadLanguage = function( name ) {
	var successflag = false;
	ProgressBar.showProgressBar();
	var res_data = {};

	if ( LocalCacheData.getI18nDic() ) {
		ProgressBar.removeProgressBar();
		return LocalCacheData.getI18nDic();
	}
	var realPath = '../locale/' + name + '/LC_MESSAGES/messages.json' + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	jQuery.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		success: function( result ) {
			successflag = true;
		},
		dataType: 'script'
	} );

	ProgressBar.removeProgressBar();

	if ( successflag ) {
		LocalCacheData.setI18nDic( i18n_dictionary );
	} else {
		LocalCacheData.setI18nDic( {} );
	}

	return (successflag);
}

Global.checkProperProductEdition = function( edition_id ) {

	if ( !edition_id ) {
		edition_id = 10;
	}

	if ( LocalCacheData.getCurrentCompany().product_edition_id > edition_id ) {
		return true;
	}

	return false;
}

Global.setURLToBrowser = function( new_url ) {

	if ( new_url !== window.location.href ) {
		window.location = new_url;
	}
}

Global.loadScript = function( scriptPath ) {

	if ( LocalCacheData.loadedScriptNames[scriptPath] ) {
		return true;
	}

	var successflag = false;

	var realPath = scriptPath + '?v=' + APIGlobal.pre_login_data.application_build

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	jQuery.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		success: function() {
			successflag = true;
		},
		dataType: 'script'
	} );

	LocalCacheData.loadedScriptNames[scriptPath] = true;

	return (successflag);
};

Global.clone = function( obj ) {

	return jQuery.extend( true, {}, obj );
};

Global.getFirstKeyFromObject = function( obj ) {
	for ( var key in obj ) {

		if ( obj.hasOwnProperty( key ) ) {
			return key;
		}

	}
};

Global.getFuncName = function( _callee ) {
	var _text = _callee.toString();
	var _scriptArr = document.scripts;
	for ( var i = 0; i < _scriptArr.length; i++ ) {
		var _start = _scriptArr[i].text.indexOf( _text );
		if ( _start !== -1 ) {
			if ( /^function\s*\(.*\).*\r\n/.test( _text ) ) {
				var _tempArr = _scriptArr[i].text.substr( 0, _start ).split( '\r\n' );
				return _tempArr[_tempArr.length - 1].replace( /(var)|(\s*)/g, '' ).replace( /=/g, '' );
			} else {
				return _text.match( /^function\s*([^\(]+).*\r\n/ )[1];
			}
		}
	}
};

Global.concatArraysUniqueWithSort = function( thisArray, otherArray ) {
	var newArray = thisArray.concat( otherArray ).sort( function( a, b ) {
		return a > b ? 1 : a < b ? -1 : 0;
	} );

	return newArray.filter( function( item, index ) {
		return newArray.indexOf( item ) === index;
	} );
};

Global.addCss = function( path ) {

	if ( LocalCacheData.loadedScriptNames[path] ) {
		return true;
	}

	LocalCacheData.loadedScriptNames[path] = true;

	var realPath = 'theme/' + Global.theme + '/css/' + path;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	var style = $( "link[href='" + realPath + '?v=' + APIGlobal.pre_login_data.application_build + "']" );

	if ( style.length < 1 ) {
		$( "head" ).append( "<link>" );
		css = $( 'head' ).children( ':last' );
		css.attr( {
			rel: 'stylesheet',
			type: 'text/css',
			href: realPath + '?v=' + APIGlobal.pre_login_data.application_build
		} );
	}

	style = $( "link[href='" + realPath + '?v=' + APIGlobal.pre_login_data.application_build + "']" );

};

//JS think 0 is false, so use this to get 0 correctly.
Global.isFalseOrNull = function( object ) {

	if ( object === false || object === null || object == 0 ) {
		return true;
	} else {
		return false;
	}

};

Global.isSet = function( object ) {

	if ( typeof object === 'undefined' || object === null ) {
		return false;
	} else {
		return true;
	}

};

Global.getIconPathByContextName = function( id ) {

	switch ( id ) {
		case ContextMenuIconName.add:
			return Global.getRealImagePath( 'css/global/widgets/ribbon/icons/copy-35x35.png' );
			break;
	}
}

Global.isEmpty = function( obj ) {

	// null and undefined are "empty"
	if ( obj == null ) return true;

	// Assume if it has a length property with a non-zero value
	// that that property is correct.
	if ( obj.length > 0 )    return false;
	if ( obj.length === 0 )  return true;

	// Otherwise, does it have any properties of its own?
	// Note that this doesn't handle
	// toString and valueOf enumeration bugs in IE < 9
	for ( var key in obj ) {
		if ( hasOwnProperty.call( obj, key ) ) return false;
	}

	return true;

};

Global.convertColumnsTojGridFormat = function( columns, layout_name, setWidthCallBack ) {
	var column_info_array = [];
	var len = columns.length;

	var total_width = 0;
	for ( var i = 0; i < len; i++ ) {
		var view_column_data = columns[i];
		var column_info;
		var text_width_span = $( '<span></span>' );
		text_width_span.text( view_column_data.label );
		text_width_span.appendTo( $( 'body' ) );
		var text_width = text_width_span.width() + 10;
		text_width_span.remove();

		total_width = total_width + text_width;

		if ( view_column_data.label === '' ) {
			column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				key: true,
				width: 100,
				sortable: false,
				hidden: true,
				title: false
			};
		} else if ( layout_name === ALayoutIDs.SORT_COLUMN ) {

			if ( view_column_data.value === 'sort' ) {
				column_info = {
					name: view_column_data.value,
					index: view_column_data.value,
					label: view_column_data.label,
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: {value: 'asc:ASC;desc:DESC'}
				};
			} else {
				column_info = {
					name: view_column_data.value,
					index: view_column_data.value,
					label: view_column_data.label,
					width: 100,
					sortable: false,
					title: false
				};
			}

		} else {
			column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
		}

		column_info_array.push( column_info );
	}

	if ( setWidthCallBack ) {
		setWidthCallBack( total_width );
	}

	return column_info_array;
};
/* jshint ignore:start */
Global.loadWidgetByName = function( widgetName ) {
	var input;
	switch ( widgetName ) {
		case WidgetNamesDic.NOTIFICATION_BAR:
			input = Global.loadWidget( 'global/widgets/top_alert/NotificationBox.html' );
			input = $( input );
			break;
		case FormItemType.FORMULA_BUILDER:
			input = Global.loadWidget( 'global/widgets/formula_builder/FormulaBuilder.html' );
			input = $( input );
			break;
		case FormItemType.AWESOME_BOX:
			input = Global.loadWidget( 'global/widgets/awesomebox/AComboBox.html' );
			input = $( input );
			break;
		case FormItemType.AWESOME_DROPDOWN:
			input = Global.loadWidget( 'global/widgets/awesomebox/ADropDown.html' );
			input = $( input );
			break;
		case FormItemType.TEXT_INPUT:
			input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
			input = $( input );
			break;
		case FormItemType.TEXT_INPUT_NO_AUTO:
			input = Global.loadWidget( 'global/widgets/text_input/TTextInputNoAuto.html' );
			input = $( input );
			break;
		case FormItemType.PASSWORD_INPUT:
			input = Global.loadWidget( 'global/widgets/text_input/TPasswordInput.html' );
			input = $( input );
			break;
		case FormItemType.TEXT:
			input = Global.loadWidget( 'global/widgets/text/TText.html' );
			input = $( input );
			break;
		case FormItemType.CHECKBOX:
			input = Global.loadWidget( 'global/widgets/checkbox/TCheckbox.html' );
			input = $( input );
			break;
		case FormItemType.COMBO_BOX:
			input = Global.loadWidget( 'global/widgets/combobox/TComboBox.html' );
			input = $( input );
			break;
		case FormItemType.LIST:
			input = Global.loadWidget( 'global/widgets/list/TList.html' );
			input = $( input );
			break;
		case FormItemType.TAG_INPUT:
			input = Global.loadWidget( 'global/widgets/tag_input/TTagInput.html' );
			input = $( input );
			break;
		case FormItemType.DATE_PICKER:
		case FormItemType.RANGE_PICKER:
			input = Global.loadWidget( 'global/widgets/datepicker/TDatePicker.html' );
			input = $( input );
			break;
		case FormItemType.TEXT_AREA:
			input = Global.loadWidget( 'global/widgets/textarea/TTextArea.html' );
			input = $( input );
			break;
		case FormItemType.SEPARATED_BOX:
			input = Global.loadWidget( 'global/widgets/separated_box/SeparatedBox.html' );
			input = $( input );
			break;
		case FormItemType.IMAGE_BROWSER:
			input = Global.loadWidget( 'global/widgets/filebrowser/TImageBrowser.html' );
			input = $( input );
			break;
		case FormItemType.FILE_BROWSER:
			input = Global.loadWidget( 'global/widgets/filebrowser/TFileBrowser.html' );
			input = $( input );
			break;
		case FormItemType.IMAGE_AVD_BROWSER:
			input = Global.loadWidget( 'global/widgets/filebrowser/TImageAdvBrowser.html' );
			input = $( input );
			break;
		case FormItemType.CAMERA_BROWSER:
			input = Global.loadWidget( 'global/widgets/filebrowser/CameraBrowser.html' );
			input = $( input );
			break;
		case FormItemType.IMAGE_CUT:
			input = Global.loadWidget( 'global/widgets/filebrowser/TImageCutArea.html' );
			input = $( input );
			break;
		case FormItemType.IMAGE:
			input = $( "<img class='t-image'>" );
			input = $( input );
			break;
		case FormItemType.INSIDE_EDITOR:
			input = Global.loadWidget( 'global/widgets/inside_editor/InsideEditor.html' );
			input = $( input );
			break;
		case WidgetNamesDic.PAGING:
			input = Global.loadWidget( 'global/widgets/paging/Paging.html' );
			input = $( input );
			break;
		case WidgetNamesDic.PAGING_2:
			input = Global.loadWidget( 'global/widgets/paging/Paging2.html' );
			input = $( input );
			break;
		case WidgetNamesDic.ERROR_TOOLTIP:
			input = Global.loadWidget( 'global/widgets/error_tip/ErrorTipBox.html' );
			input = $( input );
			break;
		case WidgetNamesDic.EDIT_VIEW_FORM_ITEM:
			input = Global.loadWidget( 'global/widgets/edit_view/EditViewFormItem.html' );
			input = $( input );
			break;
		case WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM:
			input = Global.loadWidget( 'global/widgets/edit_view/EditViewSubFormItem.html' );
			input = $( input );
			break;
		case WidgetNamesDic.NO_RESULT_BOX:
			input = Global.loadWidget( 'global/widgets/message_box/NoResultBox.html' );
			input = $( input );
			break;
		case WidgetNamesDic.VIEW_MIN_TAB:
			input = Global.loadWidget( 'global/widgets/view_min_tab/ViewMinTab.html' );
			input = $( input );
			break;
		case WidgetNamesDic.VIEW_MIN_TAB_BAR:
			input = Global.loadWidget( 'global/widgets/view_min_tab/ViewMinTabBar.html' );
			input = $( input );
			break;

	}

	return input;

};

/* jshint ignore:end */

Global.loadWidget = function( url ) {

	if ( LocalCacheData.loadedWidgetCache[url] ) {
		return (LocalCacheData.loadedWidgetCache[url]);
	}

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	ProgressBar.showProgressBar();
	var responseData = $.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		success: function() {
			successflag = true;
		}
	} );

	ProgressBar.removeProgressBar();

	LocalCacheData.loadedWidgetCache[url] = responseData.responseText;

	return (responseData.responseText);

};

Global.removeCss = function( path ) {
	var realPath = 'theme/' + Global.theme + '/css/' + path;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	$( "link[href='' + realPath + '?v=' + APIGlobal.pre_login_data.application_build + '']" ).remove();
};

/* jshint ignore:start */

Global.getViewPathByViewId = function( viewId ) {
	var path;
	switch ( viewId ) {
		case 'PortalJobVacancy':
			path = 'views/portal/hr/recruitment/';
			break;
		case 'PortalLogin':
			path = 'views/portal/login/';
			break;
		case 'UserDateTotalParent':
		case 'UserDateTotal':
			path = 'views/attendance/timesheet/';
			break;
		case 'Product':
			path = 'views/invoice/products/';
			break;
		case 'InvoiceDistrict':
			path = 'views/invoice/district/';
			break;
		case 'PaymentGateway':
			path = 'views/invoice/payment_gateway/';
			break;
		case 'InvoiceConfig':
			path = 'views/invoice/settings/';
			break;
		case 'ShippingPolicy':
			path = 'views/invoice/shipping_policy/';
			break;
		case 'AreaPolicy':
			path = 'views/invoice/area_policy/';
			break;
		case 'TaxPolicy':
			path = 'views/invoice/tax_policy/';
			break;
		case 'ClientGroup':
			path = 'views/invoice/client_group/';
			break;
		case 'ProductGroup':
			path = 'views/invoice/product_group/';
			break;
		case 'Exception':
			path = 'views/attendance/exceptions/';
			break;
		case 'Employee':
			path = 'views/employees/employee/';
			break;
		case 'Wage':
			path = 'views/company/wage/';
			break;
		case 'Login':
			path = 'views/login/';
			break;
		case 'TimeSheet':
			path = 'views/attendance/timesheet/';
			break;
		case 'InOut':
			path = 'views/attendance/in_out/';
			break;
		case 'RecurringScheduleControl':
			path = 'views/attendance/recurring_schedule_control/';
			break;
		case 'RecurringScheduleTemplateControl':
			path = 'views/attendance/recurring_schedule_template_control/';
			break;
		case 'ScheduleShift':
		case 'Schedule':
			path = 'views/attendance/schedule/';
			break;
		case 'Accrual':
			path = 'views/attendance/accrual/';
			break;
		case 'AccrualBalance':
			path = 'views/attendance/accrual_balance/';
			break;
		case 'Punches':
			path = 'views/attendance/punches/';
			break;
		case 'JobGroup':
		case 'Job':
			path = 'views/attendance/job/';
			break;
		case 'JobItemGroup':
		case 'JobItem':
			path = 'views/attendance/job_item/';
			break;
		case 'JobItemAmendment':
			path = 'views/attendance/job_item_amendment/';
			break;
		case 'UserTitle':
			path = 'views/employees/user_title/';
			break;
		case 'UserContact':
			path = 'views/employees/user_contact/';
			break;
		case 'UserPreference':
			path = 'views/employees/user_preference/';
			break;
		case 'UserGroup':
			path = 'views/employees/user_group/';
			break;
		case 'Log':
			path = 'views/core/log/';
			break;
		case 'EmployeeBankAccount':
			path = 'views/employees/bank_account/';
			break;
		case 'UserDefault':
			path = 'views/employees/user_default/';
			break;
		case 'ROE':
			path = 'views/employees/roe/';
			break;
		case 'Company':
			path = 'views/company/company/';
			break;
		case 'Companies':
			path = 'views/company/companies/';
			break;
		case 'PayPeriodSchedule':
			path = 'views/payperiod/';
			break;
		case 'PayPeriods':
			path = 'views/payroll/pay_periods/';
			break;
		case 'Branch':
			path = 'views/company/branch/';
			break;
		case 'Department':
			path = 'views/company/department/';
			break;
		case 'HierarchyControl':
			path = 'views/company/hierarchy_control/';
			break;
		case 'WageGroup':
			path = 'views/company/wage_group/';
			break;
		case 'EthnicGroup':
			path = 'views/company/ethnic_group/';
			break;
		case 'Currency':
		case 'CurrencyRate':
			path = 'views/company/currency/';
			break;
		case 'PermissionControl':
			path = 'views/company/permission_control/';
			break;
		case 'CompanyBankAccount':
			path = 'views/company/bank_accounts/';
			break;
		case 'OtherField':
			path = 'views/company/other_field/';
			break;
		case 'Station':
			path = 'views/company/station/';
			break;
		case 'PayStub':
			path = 'views/payroll/pay_stub/';
			break;
		case 'Request':
			path = 'views/my_account/request/';
			break;
		case 'ChangePassword':
			path = 'views/my_account/password/';
			break;
		case 'RequestAuthorization':
			path = 'views/my_account/request_authorization/';
			break;
		case 'TimeSheetAuthorization':
			path = 'views/my_account/timesheet_authorization/';
			break;
		case 'MessageControl':
			path = 'views/my_account/message_control/';
			break;
		case 'LoginUserBankAccount':
			path = 'views/my_account/bank_account/';
			break;
		case 'LoginUserContact':
			path = 'views/my_account/user_contact/';
			break;
		case 'LoginUserPreference':
			path = 'views/my_account/user_preference/';
			break;
		case 'LoginUserExpense':
		case 'ExpenseAuthorization':
			path = 'views/my_account/expense/';
			break;
		case 'PayStubAmendment':
			path = 'views/payroll/pay_stub_amendment/';
			break;
		case 'RecurringPayStubAmendment':
			path = 'views/payroll/recurring_pay_stub_amendment/';
			break;
		case 'PayStubEntryAccount':
			path = 'views/payroll/pay_stub_entry_account/';
			break;
		case 'CompanyTaxDeduction':
			path = 'views/payroll/company_tax_deduction/';
			break;
		case 'UserExpense':
			path = 'views/payroll/user_expense/';
			break;
		case 'PolicyGroup':
			path = 'views/policy/policy_group/';
			break;
		case 'PayCode':
			path = 'views/policy/pay_code/';
			break;
		case 'PayFormulaPolicy':
			path = 'views/policy/pay_formula_policy/';
			break;
		case 'ContributingPayCodePolicy':
			path = 'views/policy/contributing_pay_code_policy/';
			break;
		case 'ContributingShiftPolicy':
			path = 'views/policy/contributing_shift_policy/';
			break;
		case 'RoundIntervalPolicy':
			path = 'views/policy/round_interval_policy/';
			break;
		case 'MealPolicy':
			path = 'views/policy/meal_policy/';
			break;
		case 'BreakPolicy':
			path = 'views/policy/break_policy/';
			break;
		case 'RegularTimePolicy':
			path = 'views/policy/regular_time_policy/';
			break;
		case 'ExpensePolicy':
			path = 'views/policy/expense_policy/';
			break;
		case 'OvertimePolicy':
			path = 'views/policy/overtime_policy/';
			break;
		case 'AbsencePolicy':
			path = 'views/policy/absence_policy/';
			break;
		case 'PremiumPolicy':
			path = 'views/policy/premium_policy/';
			break;
		case 'ExceptionPolicyControl':
			path = 'views/policy/exception_policy/';
			break;

		case 'RecurringHoliday':
			path = 'views/policy/recurring_holiday/';
			break;
		case 'HolidayPolicy':
			path = 'views/policy/holiday_policy/';
			break;
		case 'Holiday':
			path = 'views/policy/holiday/';
			break;
		case 'SchedulePolicy':
			path = 'views/policy/schedule_policy/';
			break;
		case 'AccrualPolicy':
		case 'AccrualPolicyAccount':
		case 'AccrualPolicyUserModifier':
			path = 'views/policy/accrual_policy/';
			break;
		case 'DocumentRevision':
		case 'Document':
		case 'DocumentGroup':
			path = 'views/document/';
			break;
		case 'About':
			path = 'views/help/';
			break;
		case 'ActiveShiftReport':
			path = 'views/reports/whos_in_summary/';
			break;
		case 'UserSummaryReport':
			path = 'views/reports/employee_information/';
			break;
		case 'SavedReport':
			path = 'views/reports/saved_report/';
			break;
		case 'ReportSchedule':
			path = 'views/reports/report_schedule/';
			break;
		case 'ScheduleSummaryReport':
			path = 'views/reports/schedule_summary/';
			break;
		case 'TimesheetSummaryReport':
			path = 'views/reports/timesheet_summary/';
			break;
		case 'TimesheetDetailReport':
			path = 'views/reports/timesheet_detail/';
			break;
		case 'PunchSummaryReport':
			path = 'views/reports/punch_summary/';
			break;
		case 'ExceptionSummaryReport':
			path = 'views/reports/exception_summary/';
			break;
		case 'PayStubSummaryReport':
			path = 'views/reports/pay_stub_summary/';
			break;
		case 'KPI':
		case 'KPIGroup':
		case 'UserReviewControl':
			path = 'views/hr/kpi/';
			break;
		case 'QualificationGroup':
		case 'Qualification':
		case 'UserSkill':
		case 'UserEducation':
		case 'UserMembership':
		case 'UserLicense':
		case 'UserLanguage':
			path = 'views/hr/qualification/';
			break;
		case 'JobApplication':
		case 'JobVacancy':
		case 'JobApplicant':
		case 'JobApplicantEmployment':
		case 'JobApplicantReference':
		case 'JobApplicantLocation':
		case 'JobApplicantSkill':
		case 'JobApplicantEducation':
		case 'JobApplicantMembership':
		case 'JobApplicantLicense':
		case 'JobApplicantLanguage':
			path = 'views/hr/recruitment/';
			break;
		case 'PayrollExportReport':
			path = 'views/reports/payroll_export/';
			break;
		case 'GeneralLedgerSummaryReport':
			path = 'views/reports/general_ledger_summary/';
			break;
		case 'ExpenseSummaryReport':
			path = 'views/reports/expense_summary/';
			break;
		case 'AccrualBalanceSummaryReport':
			path = 'views/reports/accrual_balance_summary/';
			break;
		case 'JobSummaryReport':
			path = 'views/reports/job_summary/';
			break;
		case 'JobAnalysisReport':
			path = 'views/reports/job_analysis/';
			break;
		case 'JobInformationReport':
			path = 'views/reports/job_info/';
			break;
		case 'JobItemInformationReport':
			path = 'views/reports/job_item_info/';
			break;
		case 'InvoiceTransactionSummaryReport':
			path = 'views/reports/invoice_transaction_summary/';
			break;
		case 'RemittanceSummaryReport':
			path = 'views/reports/remittance_summary/';
			break;
		case 'T4SummaryReport':
			path = 'views/reports/t4_summary/';
			break;
		case 'T4ASummaryReport':
			path = 'views/reports/t4a_summary/';
			break;
		case 'TaxSummaryReport':
			path = 'views/reports/tax_summary/';
			break;
		case 'Form940Report':
			path = 'views/reports/form940/';
			break;
		case 'Form941Report':
			path = 'views/reports/form941/';
			break;
		case 'Form1099MiscReport':
			path = 'views/reports/form1099/';
			break;
		case 'FormW2Report':
			path = 'views/reports/formw2/';
			break;
		case 'AffordableCareReport':
			path = 'views/reports/affordable_care/';
			break;
		case 'UserQualificationReport':
			path = 'views/reports/qualification_summary/';
			break;
		case 'KPIReport':
			path = 'views/reports/review_summary/';
			break;
		case 'UserRecruitmentSummaryReport':
			path = 'views/reports/recruitment_summary/';
			break;
		case 'UserRecruitmentDetailReport':
			path = 'views/reports/recruitment_detail/';
			break;
		case 'Client':
			path = 'views/invoice/client/';
			break;
		case 'ClientContact':
			path = 'views/invoice/client_contact/';
			break;
		case 'ClientPayment':
			path = 'views/invoice/client_payment/';
			break;
		case 'InvoiceTransaction':
			path = 'views/invoice/invoice_transaction/';
			break;
		case 'Invoice':
			path = 'views/invoice/invoice/';
			break;
		case 'CustomColumn':
			path = 'views/reports/custom_column/';
			break;
		case 'AuditTrailReport':
			path = 'views/reports/audittrail/';
			break;
		case 'ReCalculateTimeSheetWizard':
			path = 'views/wizard/re_calculate_timesheet/';
			break;
		case 'GeneratePayStubWizard':
			path = 'views/wizard/generate_pay_stub/';
			break;
		case 'UserGenericStatus':
			path = 'views/wizard/user_generic_data_status/';
			break;
		case 'ProcessPayrollWizard':
			path = 'views/wizard/process_payroll/';
			break;
		case 'ImportCSVWizard':
			path = 'views/wizard/import_csv/';
			break;
		case 'JobInvoiceWizard':
			path = 'views/wizard/job_invoice/';
			break;
		case 'LoginUserWizard':
		case 'LoginUser':
			path = 'views/wizard/login_user/';
			break;
		case 'QuickStartWizard':
			path = 'views/wizard/quick_start/';
			break;
		case 'UserPhotoWizard':
			path = 'views/wizard/user_photo/';
			break;
		case 'FindAvailableWizard':
		case 'FindAvailable':
			path = 'views/wizard/find_available/';
			break;
		case 'PermissionWizard':
			path = 'views/wizard/permission_wizard/';
			break;
		case 'FormulaBuilderWizard':
			path = 'views/wizard/formula_builder_wizard/';
			break;
		case 'ReCalculateAccrualWizard':
			path = 'views/wizard/re_calculate_accrual/';
			break;
		case 'ResetPasswordWizard':
			path = 'views/wizard/reset_password/';
			break;
		case 'ShareReportWizard':
			path = 'views/wizard/share_report/';
			break;
		case 'PayCodeWizard':
			path = 'views/wizard/pay_code/';
			break;
		case 'InstallWizard':
			path = 'views/wizard/install/';
			break;
	}

	return path;
};

/* jshint ignore:end */

Global.removeViewCss = function( viewId, fileName ) {
	Global.removeCss( Global.getViewPathByViewId( viewId ) + fileName );
};

Global.loadViewSource = function( viewId, fileName, onResult, sync ) {
	if ( fileName.indexOf( '.js' ) > 0 ) {
		if ( sync ) {
			return Global.loadScript( Global.getViewPathByViewId( viewId ) + fileName );
		} else {
			Global.loadScriptAsync( Global.getViewPathByViewId( viewId ) + fileName, onResult );
		}

	} else if ( fileName.indexOf( '.css' ) > 0 ) {
		Global.addCss( Global.getViewPathByViewId( viewId ) + fileName );
	} else {
		if ( sync ) {
			return Global.loadPageSync( Global.getViewPathByViewId( viewId ) + fileName );
		} else {
			Global.loadPage( Global.getViewPathByViewId( viewId ) + fileName, onResult );
		}

	}

};

Global.loadPageSync = function( url ) {

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	ProgressBar.showProgressBar();
	var responseData = $.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		success: function() {
			successflag = true;
		}
	} );

	ProgressBar.removeProgressBar();

	return (responseData.responseText);

};

Global.loadPage = function( url, onResult ) {

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	ProgressBar.showProgressBar();
	$.ajax( {
		async: true,
		type: 'GET',
		url: realPath,
		data: null,
		success: function( result ) {
			ProgressBar.removeProgressBar();
			onResult( result );

		}
	} );

};

Global.getBaseURL = function() {

	var url = location.href;  // entire url including querystring - also: window.location.href;

	if ( url.indexOf( '#!m' ) !== -1 ) {
		url = url.substring( 0, url.indexOf( '#!m' ) );
	} else if ( url.indexOf( '#!user_name' ) !== -1 ) {
		url = url.substring( 0, url.indexOf( '#!user_name' ) );
	}

//	else {
//		var baseURL = url.substring( 0, url.indexOf( '/', 14 ) );
//
//		if ( baseURL.indexOf( 'http://localhost' ) !== -1 ) {
//			// Base Url for localhost
//			url = location.href;  // window.location.href;
//			var pathname = location.pathname;  // window.location.pathname;
//			var index1 = url.indexOf( pathname );
//			var index2 = url.indexOf( '/', index1 + 1 );
//			var baseLocalUrl = url.substr( 0, index2 );
//
//			return baseLocalUrl;
//		}
//		else {
//			// Root Url for domain name
//			return baseURL + window.location.pathname;
//		}
//	}

	return url;

};

Global.isArrayAndHasItems = function( object ) {

	if ( $.type( object ) === 'array' && object.length > 0 ) {
		return true;
	}

	return false;

};

Global.convertLayoutFilterToAPIFilter = function( layout ) {
	var convert_filter_data = {};

	if ( !layout ) {
		return null;
	}

	var filter_data = layout.data.filter_data;

	if ( !filter_data ) {
		return null;
	}

	$.each( filter_data, function( key, content ) {

		if ( ( content.value instanceof Array && content.value.length > 0 ) || ( content.value instanceof Object ) ) {
			var values = [];
			var obj = content.value;
			if ( content.value instanceof Array ) {

				var len = content.value.length;
				for ( var i = 0; i < len; i++ ) {

					if ( Global.isSet( content.value[i].value ) ) {
						values.push( content.value[i].value ); //Options,
					} else if ( content.value[i].id || content.value[i].id == 0 ) {
						values.push( content.value[i].id ); //Awesomebox
					} else {
						values.push( content.value[i] ); // default_filter_data_for_next_view
					}

				}

				convert_filter_data[key] = values;
				//only add search filter which not equal to false, see if this cause any bugs
			} else if ( content.value instanceof Object ) {
				var final_value = '';
				if ( Global.isSet( content.value.value ) ) {
					final_value = content.value.value; //Options,
				} else if ( content.value.id || content.value.id == 0 ) {
					final_value = content.value.id; //Awesomebox
				} else {
					final_value = content.value; // default_filter_data_for_next_view
				}

				convert_filter_data[key] = final_value;

			} else if ( obj.value === false ) {
				return;//continue;
			} else {
				if ( Global.isSet( obj.value ) ) {

					convert_filter_data[key] = obj.value;
				}
			}

		} else if ( filter_data[key].value === false ) {
			return; //continue;
		} else if ( Global.isSet( filter_data[key].value ) ) {
			convert_filter_data[key] = filter_data[key].value;
		} else {
			convert_filter_data[key] = filter_data[key];
		}
	} );

	if ( LocalCacheData.extra_filter_for_next_open_view ) { //MUST removed this when close the view which used this attribute.

		for ( var key in LocalCacheData.extra_filter_for_next_open_view.filter_data ) {
			convert_filter_data[key] = LocalCacheData.extra_filter_for_next_open_view.filter_data[key];
		}

	}

	return convert_filter_data;

};

//ASC
Global.compare = function( a, b, orderKey, order_type ) {

	if ( !Global.isSet( order_type ) ) {
		order_type = 'asc';
	}

	if ( order_type === 'asc' ) {
		if ( a[orderKey] > b[orderKey] ) {
			return true;
		} else {
			return false;
		}
	} else {
		if ( a[orderKey] < b[orderKey] ) {
			return true;
		} else {
			return false;
		}
	}

};

Global.buildFilter = function() {
	var filterCondition = arguments[0];
	var filter = [];

	if ( filterCondition ) {

		for ( var key in filterCondition ) {
			filter[key] = filterCondition[key];
		}

	}

	return filter;

};

Global.getLoginUserDateFormat = function() {
	var userPreference = LocalCacheData.getLoginUserPreference();

	var format = userPreference.date_format;

	return format;
};
/* jshint ignore:start */
Global.formatGridData = function( grid_data, key_name ) {

	if ( $.type( grid_data ) !== 'array' ) {
		return grid_data;
	}

	for ( var i = 0; i < grid_data.length; i++ ) {
		for ( var key in grid_data[i] ) {

			if ( !grid_data[i].hasOwnProperty( key ) ) {
				return;
			}

			// The same format for all views.
			switch ( key ) {
				case 'maximum_shift_time':
				case 'new_day_trigger_time':
				case 'trigger_time':
				case 'minimum_punch_time':
				case 'maximum_punch_time':
				case 'window_length':
				case 'start_window':
				case 'round_interval':
				case 'grace':
				case 'estimate_time':
				case 'minimum_time':
				case 'maximum_time':
				case 'total_time':
				case 'start_stop_window':
					if ( $.isNumeric( grid_data[i][key] ) ) {
						grid_data[i][key] = Global.secondToHHMMSS( grid_data[i][key] );
					}
					break;
				case 'include_break_punch_time':
				case 'include_multiple_breaks':
				case 'include_lunch_punch_time':
				case 'is_default':
				case 'is_base':
				case 'auto_update':
				case 'currently_employed':
				case 'criminal_record':
				case 'immediate_drug_test':
				case 'is_current_employer':
				case 'is_contact_available':
				case 'enable_pay_stub_balance_display':
				case 'ytd_adjustment':
				case 'authorized':
				case 'is_reimbursable':
				case 'reimbursable':
				case 'tainted':
				case 'auto_fill':
				case 'private':
					if ( grid_data[i][key] === true ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
					} else if ( grid_data[i][key] === false ) {
						grid_data[i][key] = $.i18n._( 'No' );
					}
					break;
				case 'override':
					if ( grid_data[i][key] === true ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
						grid_data[i]['is_override'] = true;
					} else if ( grid_data[i][key] === false ) {
						grid_data[i][key] = $.i18n._( 'No' );
						grid_data[i]['is_override'] = false;
					}
					break;
				case 'is_scheduled':
					if ( grid_data[i][key] === '1' ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
					} else if ( grid_data[i][key] === '0' ) {
						grid_data[i][key] = $.i18n._( 'No' );
					}
					break;
				case 'in_use':
					if ( grid_data[i][key] === '1' ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
						grid_data[i]['is_in_use'] = true;
					} else if ( grid_data[i][key] === '0' ) {
						grid_data[i][key] = $.i18n._( 'No' );
						grid_data[i]['is_in_use'] = false;
					}
					break;
				default:
					if ( grid_data[i][key] === false ) {
						grid_data[i][key] = '';
					}
					break;
			}

			// Handle the specially format columns which are not different with others.
			switch ( key_name ) {
				case 'BreakPolicy':
				case 'MealPolicy':
				case 'Accrual':
					switch ( key ) {
						case 'amount':
							if ( $.isNumeric( grid_data[i][key] ) ) {
								grid_data[i][key] = Global.secondToHHMMSS( grid_data[i][key] );
							}
							break;

					}
					break;
				case 'AccrualBalance':
					switch ( key ) {
						case 'balance':
							if ( $.isNumeric( grid_data[i][key] ) ) {
								grid_data[i][key] = Global.secondToHHMMSS( grid_data[i][key] );
							}
							break;

					}
					break;
				case 'RecurringScheduleControl':
					switch ( key ) {
						case 'end_date':
							if ( grid_data[i][key] === '' ) {
								grid_data[i][key] = 'Never';
							}
							break;
					}
					break;
			}

		}
	}

	return grid_data;

};
/* jshint ignore:end */
//make backone support a simple super funciton
Backbone.Model.prototype._super = function( funcName ) {
	return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
};

//make backone support a simple super function
Backbone.View.prototype._super = function( funcName ) {

	if ( this.real_this ) {
		return this.real_this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
	} else {
		return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
	}

};

//make backone support a simple super funciton for second level class
Backbone.View.prototype.__super = function( funcName ) {

	if ( !this.real_this ) {
		this.real_this = this.constructor.__super__;
	}

	return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );

};

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function() {
	var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|'[^']*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function( val, len ) {
			val = String( val );
			len = len || 2;
			while ( val.length < len ) {
				val = "0" + val;
			}
			return val;
		};

	// Regexes and supporting functions are cached through closure

	/* jshint ignore:start */
	return function( date, mask, utc ) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the 'UTC:' mask prefix)
		if ( arguments.length === 1 && Object.prototype.toString.call( date ) === '[object String]' && !/\d/.test( date ) ) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date( date ) : new Date();
		if ( isNaN( date ) ) {
			throw SyntaxError( 'invalid date' );
		}

		mask = String( dF.masks[mask] || mask || dF.masks['default'] );

		// Allow setting the utc argument via the mask
		if ( mask.slice( 0, 4 ) === 'UTC:' ) {
			mask = mask.slice( 4 );
			utc = true;
		}

		var _ = utc ? 'getUTC' : 'get',
			d = date[_ + 'Date'](),
			D = date[_ + 'Day'](),
			m = date[_ + 'Month'](),
			y = date[_ + 'FullYear'](),
			H = date[_ + 'Hours'](),
			M = date[_ + 'Minutes'](),
			s = date[_ + 'Seconds'](),
			L = date[_ + 'Milliseconds'](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d: d,
				dd: pad( d ),
				ddd: dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m: m + 1,
				mm: pad( m + 1 ),
				mmm: dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy: String( y ).slice( 2 ),
				yyyy: y,
				h: H % 12 || 12,
				hh: pad( H % 12 || 12 ),
				H: H,
				HH: pad( H ),
				M: M,
				MM: pad( M ),
				s: s,
				ss: pad( s ),
				l: pad( L, 3 ),
				L: pad( L > 99 ? Math.round( L / 10 ) : L ),
				t: H < 12 ? "a" : "p",
				tt: H < 12 ? "am" : "pm",
				T: H < 12 ? "A" : "P",
				TT: H < 12 ? "AM" : "PM",
				Z: utc ? 'UTC' : (String( date ).match( timezone ) || ['']).pop().replace( timezoneClip, '' ),
				o: (o > 0 ? '-' : '+') + pad( Math.floor( Math.abs( o ) / 60 ) * 100 + Math.abs( o ) % 60, 4 ),
				S: ['th', 'st', 'nd', 'rd'][d % 10 > 3 ? 0 : (d % 100 - d % 10 !== 10) * d % 10]
			};

		return mask.replace( token, function( $0 ) {
			return $0 in flags ? flags[$0] : $0.slice( 1, $0.length - 1 );
		} );
	};

	/* jshint ignore:end */
}();

// Some common format strings
dateFormat.masks = {
	'default': 'ddd mmm dd yyyy HH:MM:ss',
	shortDate: 'm/d/yy',
	mediumDate: 'mmm d, yyyy',
	longDate: 'mmmm d, yyyy',
	fullDate: 'dddd, mmmm d, yyyy',
	shortTime: 'h:MM TT',
	mediumTime: 'h:MM:ss TT',
	longTime: 'h:MM:ss TT Z',
	isoDate: 'yyyy-mm-dd',
	isoTime: 'HH:MM:ss',
	isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
		'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
	],
	monthNames: [
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
		'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
	]
};

// For convenience...
Date.prototype.format = function( mask, utc ) {

	if ( !Global.isSet( mask ) ) {
		mask = LocalCacheData.loginUserPreference.date_format;
	}

	var format_str = moment( this ).format( mask );

	return format_str;
};

var RightClickMenuType = function() {

};

RightClickMenuType.LISTVIEW = '1';
RightClickMenuType.EDITVIEW = '2';
RightClickMenuType.NORESULTBOX = '3';
RightClickMenuType.ABSENCE_GRID = '4';

Global.htmlEncode = function( str ) {
	var s = "";
	if ( str.length == 0 ) return "";
	s = str.replace( /&/g, "&gt;" );
	s = s.replace( /</g, "&lt;" );
	s = s.replace( />/g, "&gt;" );
	s = s.replace( / /g, "&nbsp;" );
	s = s.replace( /\'/g, "&#39;" );
	s = s.replace( /\"/g, "&quot;" );
	s = s.replace( /\n/g, "<br>" );
	return s;
}

Global.htmlDecode = function( str ) {
	var s = "";
	if ( str.length == 0 ) return "";
	s = str.replace( /&gt;/g, "&" );
	s = s.replace( /&lt;/g, "<" );
	s = s.replace( /&gt;/g, ">" );
	s = s.replace( /&nbsp;/g, " " );
	s = s.replace( /&#39;/g, "\'" );
	s = s.replace( /&quot;/g, "\"" );
	s = s.replace( /<br>/g, "\n" );
	return s;
}

//Sort by module

Global.m_sort_by = (function() {
	// utility functions

	var default_cmp = function( a, b ) {

			if ( a === b ) {
				return 0;
			}

			//Speical handle OPEN option to make it always stay together
			if ( a === false || a === 'OPEN' ) {
				return -1;
			}

			if ( b === false || b === 'OPEN' ) {
				return 1;
			}

			return a < b ? -1 : 1;
		},
		getCmpFunc = function( primer, reverse ) {
			var cmp = default_cmp;
			if ( primer ) {
				cmp = function( a, b ) {
					return default_cmp( primer( a ), primer( b ) );
				};
			}
			if ( reverse ) {
				return function( a, b ) {
					return -1 * cmp( a, b );
				};
			}
			return cmp;
		};

	// actual implementation
	var sort_by = function( sort_by_array ) {
		var fields = [],
			n_fields = sort_by_array.length,
			field, name, reverse, cmp;

		// preprocess sorting options
		for ( var i = 0; i < n_fields; i++ ) {
			field = sort_by_array[i];
			if ( typeof field === 'string' ) {
				name = field;
				cmp = default_cmp;
			}
			else {
				name = field.name;
				cmp = getCmpFunc( field.primer, field.reverse );
			}
			fields.push( {
				name: name,
				cmp: cmp
			} );
		}

		return function( A, B ) {
			var a, b, name, cmp, result;
			for ( var i = 0, l = n_fields; i < l; i++ ) {
				result = 0;
				field = fields[i];
				name = field.name;
				cmp = field.cmp;

				result = cmp( A[name], B[name] );
				if ( result !== 0 ) {
					break;
				}
			}
			return result;
		};
	};

	return sort_by;

}());

var UUID = (function() {

	var s4 = function() {
		return Math.floor( (1 + Math.random()) * 0x10000 )
			.toString( 16 )
			.substring( 1 );
	};

	var guid = function() {
		return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
			s4() + '-' + s4() + s4() + s4();
	};

	return {guid: guid};

})();

$.fn.invisible = function() {
	return this.each( function() {
		$( this ).css( "opacity", "0" );
	} );
};
$.fn.visible = function() {
	return this.each( function() {
		$( this ).css( "opacity", "1" );
	} );
};

if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
	(function( i, s, o, g, r, a, m ) {
		i['GoogleAnalyticsObject'] = r;
		i[r] = i[r] || function() {
			(i[r].q = i[r].q || []).push( arguments )
		}, i[r].l = 1 * new Date();
		a = s.createElement( o ),
			m = s.getElementsByTagName( o )[0];
		a.async = 1;
		a.src = g;
		m.parentNode.insertBefore( a, m )
	})( window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga' );
	ga( 'create', 'UA-333702-3', 'auto' );
}

Global.trackView = function( name, action ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		var track_address;

		//Hostname is already sent separately, so this should just be the view/action in format:
		// '#!m=' + name + '&a=' + action
		if ( name ) {
			track_address = '#!m=' + name;

			if ( action ) {
				track_address += '&a=' + action;
			}
		} else {
			//Default to only data after (and including) the #.
			track_address = window.location.hash.substring( 1 );
		}

		//Track address is sent in sendAnalytics as the 3rd parameter.
		Global.sendAnalytics( track_address );
	}
};

Global.log = function( val ) {
	if ( typeof console !== "undefined" && typeof (console.log) !== "undefined" ) {
		console.log( val );
	}
};

Global.setAnalyticDimensions = function( user_name, company_name ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		ga( 'set', 'dimension1', APIGlobal.pre_login_data.application_version );
		ga( 'set', 'dimension2', APIGlobal.pre_login_data.http_host );
		ga( 'set', 'dimension3', APIGlobal.pre_login_data.product_edition_name );
		ga( 'set', 'dimension4', APIGlobal.pre_login_data.registration_key );
		ga( 'set', 'dimension5', APIGlobal.pre_login_data.primary_company_name );

		if ( user_name != 'undefined' && user_name != null ) {
			if ( APIGlobal.pre_login_data.production !== true ) {
				Global.log( 'Analytics User: ' + user_name );
			}
			ga( 'set', 'dimension6', user_name );
		}

		if ( company_name != 'undefined' && company_name != null ) {
			if ( APIGlobal.pre_login_data.production !== true ) {
				Global.log( 'Analytics Company: ' + company_name );
			}
			ga( 'set', 'dimension7', company_name );
		}			//code
	}
};

Global.sendAnalytics = function( track_address ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		if ( APIGlobal.pre_login_data.production !== true ) {
			Global.log( 'Sending Analytics w/Address: ' + track_address );
		}
		ga( 'send', 'pageview', track_address );
	}
};

