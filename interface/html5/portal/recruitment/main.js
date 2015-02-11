require.config( {

	waitSeconds: 500,
	urlArgs: 'v=' + APIGlobal.pre_login_data.application_build,

	paths: {
		'jquery_cookie': '../../framework/jquery.cookie',
		'jquery_json': '../../framework/jquery.json',
		'jquery_tablednd': '../../framework/jquery.tablednd',
		'jquery_ba_resize': '../../framework/jquery.ba-resize',
		'fastclick': '../../framework/fastclick',
		'stacktrace': '../../framework/stacktrace',
		'html2canvas': '../../framework/html2canvas',
		'datejs': '../../framework/date',
		'moment': '../../framework/moment.min',
		'timepicker_addon': '../../framework/widgets/datepicker/jquery-ui-timepicker-addon',
		'grid_locale': '../../framework/widgets/jqgrid/grid.locale-en',
		'jqGrid': '../../framework/widgets/jqgrid/jquery.jqGrid.src',
		'ImageAreaSelect': '../../framework/jquery.imgareaselect',

		'jqGrid_extend': '../../framework/widgets/jqgrid/jquery.jqGrid.extend',
		'SearchPanel': '../../global/widgets/search_panel/SearchPanel',
		'FormItemType': '../../global/widgets/search_panel/FormItemType',
		'TGridHeader': '../../global/widgets/jqgrid/TGridHeader',
		'ADropDown': '../../global/widgets/awesomebox/ADropDown',
		'AComboBox': '../../global/widgets/awesomebox/AComboBox',
		'ASearchInput': '../../global/widgets/awesomebox/ASearchInput',
		'ALayoutCache': '../../global/widgets/awesomebox/ALayoutCache',
		'ALayoutIDs': '../../global/widgets/awesomebox/ALayoutIDs',
		'ColumnEditor': '../../global/widgets/column_editor/ColumnEditor',
		'SaveAndContinueBox': '../../global/widgets/message_box/SaveAndContinueBox',
		'NoResultBox': '../../global/widgets/message_box/NoResultBox',
		'SeparatedBox': '../../global/widgets/separated_box/SeparatedBox',
		'TTextInput': '../../global/widgets/text_input/TTextInput',
		'TPasswordInput': '../../global/widgets/text_input/TPasswordInput',
		'TText': '../../global/widgets/text/TText',
		'TList': '../../global/widgets/list/TList',
		'TToggleButton': '../../global/widgets/toggle_button/TToggleButton',
		'SwitchButton': '../../global/widgets/switch_button/SwitchButton',
		'TCheckbox': '../../global/widgets/checkbox/TCheckbox',
		'TComboBox': '../../global/widgets/combobox/TComboBox',
		'TTagInput': '../../global/widgets/tag_input/TTagInput',
		'TRangePicker': '../../global/widgets/datepicker/TRangePicker',
		'TDatePicker': '../../global/widgets/datepicker/TDatePicker',
		'TTextArea': '../../global/widgets/textarea/TTextArea',
		'TImageBrowser': '../../global/widgets/filebrowser/TImageBrowser',
		'TImageAdvBrowser': '../../global/widgets/filebrowser/TImageAdvBrowser',
		'TImage': '../../global/widgets/filebrowser/TImage',
		'TImageCutArea': '../../global/widgets/filebrowser/TImageCutArea',
		'CameraBrowser': '../../global/widgets/filebrowser/CameraBrowser',
		'InsideEditor': '../../global/widgets/inside_editor/InsideEditor',
		'ErrorTipBox': '../../global/widgets/error_tip/ErrorTipBox',
		'Paging2': '../../global/widgets/paging/Paging2',
		'ViewMinTabBar': '../../global/widgets/view_min_tab/ViewMinTabBar',
		'RibbonSubMenuNavWidget': '../../global/widgets/ribbon/RibbonSubMenuNavWidget',
		'TopNotification': '../../global/widgets/top_alert/TopNotification',

		'ContextMenuConstant': '../../global/ContextMenuConstant',
		'ProgressBarManager': '../../global/ProgressBarManager',
		'TAlertManager': '../../global/TAlertManager',
		'PermissionManager': '../../global/PermissionManager',
		'TopMenuManager': '../../global/TopMenuManager',
		'IndexController': 'IndexController',

		'Base': '../../model/Base',
		'SearchField': '../../model/SearchField',
		'ResponseObject': '../../model/ResponseObject',
		'RibbonMenu': '../../model/RibbonMenu',
		'RibbonSubMenu': '../../model/RibbonSubMenu',
		'RibbonSubMenuGroup': '../../model/RibbonSubMenuGroup',
		'RibbonSubMenuNavItem': '../../model/RibbonSubMenuNavItem',
		'ServiceCaller': '../../services/ServiceCaller',
		'APIProgressBar': '../../services/core/APIProgressBar',
		'APIReturnHandler': '../../model/APIReturnHandler',
		'BaseViewController': '../../views/BaseViewController',
		'BaseWindowController': '../../views/BaseWindowController',
		'BaseWizardController': '../../views/wizard/BaseWizardController',
		'UserGenericStatusWindowController': '../../views/wizard/user_generic_data_status/UserGenericStatusWindowController',
		'ReportBaseViewController': '../../views/reports/ReportBaseViewController',

		'sonic': '../../framework/sonic',

		'qtip': '../../framework/jquery.qtip.min'
	},

	shim: {

		//Make sure jqGrid_extend load after jgGrid
		'jqGrid_extend': {
			deps: ['jqGrid']
		},
		'BaseViewController': {
			deps: ['ContextMenuConstant']
		}
	}
} );

require( [
	'jquery_cookie',
	'jquery_json',
	'jquery_tablednd',
	'jquery_ba_resize',
	'fastclick',
	'stacktrace',
	'html2canvas',
	'datejs',
	'Base',
	'ServiceCaller',
	'BaseViewController',
	'BaseWindowController'

], function() {

	require( [
		'APIProgressBar',
		'ImageAreaSelect',
		'moment',
		'grid_locale',
		'jqGrid',
		'jqGrid_extend',
		'TTextInput',
		'TPasswordInput',
		'SearchPanel',
		'FormItemType',
		'TGridHeader',
		'ADropDown',
		'AComboBox',
		'ASearchInput',
		'ALayoutCache',
		'ALayoutIDs',
		'ColumnEditor',
		'SaveAndContinueBox',
		'NoResultBox',
		'SeparatedBox',
		'TTextInput',
		'TPasswordInput',
		'TText',
		'TList',
		'TToggleButton',
		'SwitchButton',
		'TCheckbox',
		'TComboBox',
		'TTagInput',
		'timepicker_addon',
		'TDatePicker',
		'TRangePicker',
		'TTextArea',
		'TImageBrowser',
		'CameraBrowser',
		'TImageAdvBrowser',
		'TImage',
		'TImageCutArea',
		'InsideEditor',
		'ErrorTipBox',
		'Paging2',
		'ViewMinTabBar',
		'TopNotification',

		'ContextMenuConstant',
		'ProgressBarManager',
		'TAlertManager',
		'PermissionManager',
		'TopMenuManager',
		'IndexController',
		'SearchField',
		'ResponseObject',
		'ResponseObject',
		'RibbonMenu',
		'RibbonSubMenu',
		'RibbonSubMenuGroup',
		'RibbonSubMenuNavItem',
		'RibbonSubMenuNavWidget',
		'APIReturnHandler',
		'BaseWizardController',
		'UserGenericStatusWindowController',
		'ReportBaseViewController',
		'sonic',
		'qtip'

	], function() {
		if ( window.sessionStorage ) {
			LocalCacheData.isSupportHTML5LocalCache = true;
		} else {
			LocalCacheData.isSupportHTML5LocalCache = false;
		}

		is_browser_iOS = ( navigator.userAgent.match( /(iPad|iPhone|iPod)/g ) ? true : false );

		ie = (function() {

			var undef,
				v = 3,
				div = document.createElement( 'div' ),
				all = div.getElementsByTagName( 'i' );

			while (
				div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
					all[0]
				);

			return v > 4 ? v : 11;

		}());

		$( function() {

//			if ( LocalCacheData.isSupportHTML5LocalCache ) {
//				sessionStorage.setItem( "is_reloaded", true );
//			}

//			$.cookie( 'js_debug', 'true', {expires: 10000, path: LocalCacheData.cookie_path} );

			$.support.cors = true; // For IE
			cleanProgress();

			currentMousePos = {x: -1, y: -1};
			$( document ).mousemove( function( event ) {
				currentMousePos.x = event.pageX;
				currentMousePos.y = event.pageY;
			} );

			var api_authentication = new (APIFactory.getAPIClass( 'APIAuthentication' ))();

			window.onerror = function() {
				Global.sendErrorReport( arguments[0], arguments[1], arguments[2], arguments[3], arguments[4] );
			};

			$( 'body' ).addClass( 'login-bg' );

			FastClick.attach( $( 'body' )[0] );
			//Load need API class

			$( 'body' ).unbind( 'keydown' ).bind( 'keydown', function( e ) {

				if ( e.keyCode === 27 || e.keyCode === 13 ) {
					//Mouse down to collect data so for some actions like search can read select data in its click event
					if ( LocalCacheData.openAwesomeBox ) {
						LocalCacheData.openAwesomeBox.onClose();
					}

					if ( LocalCacheData.openAwesomeBoxColumnEditor ) {
						LocalCacheData.openAwesomeBoxColumnEditor.onClose();
					}
				}

				if ( LocalCacheData.openAwesomeBox ) {
					if ( e.keyCode !== 16 &&
						e.keyCode !== 17 &&
						e.keyCode !== 91 ) {
						LocalCacheData.openAwesomeBox.selectNextItem( e );
					}

				}

				if ( (e.keyCode === 65 && e.metaKey === true) || (e.keyCode === 65 && e.ctrlKey === true ) ) {
					e.preventDefault();
					selectAll();
				}

				if ( e.keyCode === 36 ) {
					gridScrollTop();
				}

				if ( e.keyCode === 35 ) {
					gridScrollDown();
				}

				// keyboard event to quick search permission adropdown
				if ( LocalCacheData.current_open_primary_controller &&
					LocalCacheData.current_open_primary_controller.viewId === 'PermissionControl' &&
					LocalCacheData.current_open_primary_controller.edit_view ) {

					LocalCacheData.current_open_primary_controller.onKeyDown( e );

				}

			} );

			$( 'body' ).unbind( 'click' ).click( function( e ) {

				var ui_clicked_date = new Date();
				var ui_stack = {
					target_class: $( e.target ).attr( 'class' ) ? $( e.target ).attr( 'class' ) : '',
					target_id: $( e.target ).attr( 'id' ) ? $( e.target ).attr( 'id' ) : '',
					html: e.target.outerHTML,
					ui_clicked_date: ui_clicked_date.format( 'hh:mm:ss' ) + '.' + ui_clicked_date.getMilliseconds()
				};
				if ( LocalCacheData.ui_click_stack.length === 8 ) {
					LocalCacheData.ui_click_stack.pop();
				}

				LocalCacheData.ui_click_stack.unshift( ui_stack );

			} );

			function gridScrollDown() {
				if ( LocalCacheData.openAwesomeBox ) {
					LocalCacheData.openAwesomeBox.gridScrollDown();
					return;
				}

				if ( LocalCacheData.current_open_sub_controller ) {

					if ( !LocalCacheData.current_open_sub_controller.edit_view ) {
						LocalCacheData.current_open_sub_controller.gridScrollDown();
					}

					return;
				}

				if ( LocalCacheData.current_open_primary_controller ) {

					if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
						LocalCacheData.current_open_primary_controller.gridScrollDown();
					}
					return;
				}
			}

			function gridScrollTop() {
				if ( LocalCacheData.openAwesomeBox ) {
					LocalCacheData.openAwesomeBox.gridScrollTop();
					return;
				}

				if ( LocalCacheData.current_open_sub_controller ) {

					if ( !LocalCacheData.current_open_sub_controller.edit_view ) {
						LocalCacheData.current_open_sub_controller.gridScrollTop();
					}

					return;
				}

				if ( LocalCacheData.current_open_primary_controller ) {

					if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
						LocalCacheData.current_open_primary_controller.gridScrollTop();
					}
					return;
				}
			}

			function selectAll() {

				if ( LocalCacheData.openAwesomeBox ) {
					LocalCacheData.openAwesomeBox.selectAll();
					return;
				}

				if ( LocalCacheData.current_open_sub_controller ) {

					if ( !LocalCacheData.current_open_sub_controller.edit_view ) {
						LocalCacheData.current_open_sub_controller.selectAll();
					}

					return;
				}

				if ( LocalCacheData.current_open_primary_controller ) {

					if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
						LocalCacheData.current_open_primary_controller.selectAll();
					}
					return;
				}
			};

			$( 'body' ).unbind( 'mousedown' ).bind( 'mousedown', function() {


				// MUST COLLLECT DATA WHEN MOUSE down, otherwise when do save in edit view when awesomebox open, the data can't be saved.
				//Mouse down to collect data so for some actions like search can read select data in its click event
				if ( LocalCacheData.openAwesomeBox && !LocalCacheData.openAwesomeBox.getIsMouseOver() ) {
					LocalCacheData.openAwesomeBox.onClose();
				}

				if ( LocalCacheData.openRangerPicker && !LocalCacheData.openRangerPicker.getIsMouseOver() ) {
					LocalCacheData.openRangerPicker.close();
				}

				if ( LocalCacheData.openAwesomeBoxColumnEditor && !LocalCacheData.openAwesomeBoxColumnEditor.getIsMouseOver() ) {
					LocalCacheData.openAwesomeBoxColumnEditor.onClose();
				}

				if ( LocalCacheData.openRibbonNaviMenu && !LocalCacheData.openRibbonNaviMenu.getIsMouseOver() ) {
					LocalCacheData.openRibbonNaviMenu.close();
				}

			} );

			var cUrl = window.location.href;

			if ( $.cookie( 'js_debug' ) ) {
				var script = Global.loadScript( 'local_testing/LocalURL.js' );
				if ( script === true ) {
					cUrl = LocalURL.url();
				}
			}

			cUrl = getRelatedURL( cUrl );

			ServiceCaller.baseUrl = cUrl + 'api/json/api.php';
			ServiceCaller.staticURL = ServiceCaller.baseUrl;

			ServiceCaller.rootURL = getRootURL( cUrl );

			var loginData = {};

			//Set in APIGlobal.php
			if ( !need_load_pre_login_data ) {
				loginData = APIGlobal.pre_login_data;
			} else {
				need_load_pre_login_data = false;
			}

			if ( !loginData.hasOwnProperty( 'api_base_url' ) ) {
				api_authentication.getPreLoginData( null, {
					onResult: function( e ) {

						var result = e.getResult();

						LocalCacheData.setLoginData( result );
						APIGlobal.pre_login_data = result;

						loginData = LocalCacheData.getLoginData();
						initApps();

					}
				} );
			} else {
				LocalCacheData.setLoginData( loginData ); //set here because the loginData is set from php
				initApps();
			}

			function initApps() {
				if ( ie <= 8 ) {
					TAlertManager.showBrowserTopBanner();
					return;
				}

				//Optimization: Only change locale if its *not* en_US or enable_default_language_translation = TRUE
				if ( loginData.locale !== 'en_US' || loginData.enable_default_language_translation == true ) {
					Global.loadLanguage( loginData.locale );
					Global.log( 'Using Locale: ' + loginData.locale );
				} else {
					LocalCacheData.setI18nDic( {} );
				}
				$.i18n.load( LocalCacheData.getI18nDic() );

				Global.initStaticStrings();

				ServiceCaller.import_csv_emample = ServiceCaller.rootURL + loginData.base_url + 'html5/views/wizard/import_csv/';
				ServiceCaller.fileDownloadURL = ServiceCaller.rootURL + loginData.base_url + 'send_file.php';
				debugger
				ServiceCaller.uploadURL = ServiceCaller.rootURL + loginData.base_url + 'upload_file.php';

				ServiceCaller.companyLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=company_logo';

				ServiceCaller.invoiceLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=invoice_config';

				ServiceCaller.userPhoto = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=user_photo';

				ServiceCaller.mainCompanyLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=primary_company_logo';
				ServiceCaller.poweredByLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=smcopyright';

				ServiceCaller.login_page_powered_by_logo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=copyright';

				LocalCacheData.enablePoweredByLogo = loginData.powered_by_logo_enabled;

				LocalCacheData.appType = loginData.deployment_on_demand;

				LocalCacheData.productEditionId = loginData.product_edition;

				var controller = new IndexViewController();

				if ( $.cookie( 'PreviousSessionID' ) ) {
					TAlertManager.showPreSessionAlert();
				}
			}
		} );

		function getRelatedURL( url ) {
			var a = url.split( '/' );

			var targetIndex = (a.length - 5);
			var newUrl = '';
			for ( var i = 0; i < targetIndex; i++ ) {
				if ( i !== 1 ) {
					newUrl = newUrl + a[i] + '/';
				} else {
					newUrl = newUrl + '/';
				}

			}


			return newUrl;
		}

		function getRootURL( url ) {
			var a = url.split( '/' );
			var targetIndex = 3;
			var newUrl = '';
			for ( var i = 0; i < targetIndex; i++ ) {
				if ( i !== 1 && i < 2 ) {
					newUrl = newUrl + a[i] + '/';
				} else if ( i === 1 ) {
					newUrl = newUrl + '/';
				} else if ( i === 2 ) {
					newUrl = newUrl + a[i];
				}

			}

			return newUrl;
		}

	} );

	// some code here
} );

