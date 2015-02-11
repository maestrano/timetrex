var LocalCacheData = function() {

};

LocalCacheData.i18nDic = null;

LocalCacheData.notification_bar = null;

LocalCacheData.ui_click_stack = [];

LocalCacheData.api_stack = [];

LocalCacheData.last_timesheet_selected_date = null;

LocalCacheData.last_timesheet_selected_user = null;

LocalCacheData.last_schedule_selected_date = null;

LocalCacheData.current_open_wizard_controller = null; // cache opened wizard conroller, only one wizard open at a time

LocalCacheData.default_filter_for_next_open_view = null;

LocalCacheData.extra_filter_for_next_open_view = null;

LocalCacheData.default_edit_id_for_next_open_edit_view = null; //First use in save report jump to report

LocalCacheData.current_open_view_id = ''; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.login_error_string = ''; //Error message show on Login Screen

LocalCacheData.all_url_args = null; //All args from URL

LocalCacheData.current_open_primary_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_sub_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_edit_only_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_report_controller = null; //save open report view controller

LocalCacheData.current_doing_context_action = ''; //Save what context action is doing right now

LocalCacheData.current_selet_date = ''; // Save

LocalCacheData.edit_id_for_next_open_view = '';

LocalCacheData.url_args = null;

LocalCacheData.result_cache = {};

LocalCacheData.paging_type = 10;  //0 is CLick to show more, 10 is normal paging

LocalCacheData.currentShownContextMenuName = '';

LocalCacheData.isSupportHTML5LocalCache = false;

//LocalCacheData.isApplicationBranded = null;

LocalCacheData.loginData = null;

LocalCacheData.currentLanguage = 'en_us';

LocalCacheData.currentLanguageDic = {};

LocalCacheData.enablePoweredByLogo = null;

LocalCacheData.appType = null;

LocalCacheData.productEditionId = null;

LocalCacheData.debuger = null;

LocalCacheData.applicationName = null;

LocalCacheData.loginUser = null;

LocalCacheData.loginUserPreference = null;

LocalCacheData.openAwesomeBox = null; //To help make sure only one Awesomebox is shown at one time. Do mouse click outside job

LocalCacheData.openAwesomeBoxColumnEditor = null; //To Make sure only one column editor of Awesomebox is shown at one time Do mouse click outside job

LocalCacheData.openRibbonNaviMenu = null;

LocalCacheData.loadedWidgetCache = {};

LocalCacheData.loadedScriptNames = {}; //Save load javascript, prevent multiple load

LocalCacheData.permissionData = null;

LocalCacheData.uniqueCountryArray = null;

LocalCacheData.currentSelectMenuId = null;

LocalCacheData.currentSelectSubMenuId = null;

LocalCacheData.timesheet_sub_grid_expended_dic = {};

LocalCacheData.view_min_map = {};

LocalCacheData.view_min_tab_bar = null;

LocalCacheData.cookie_path = '/';

LocalCacheData.domain_name = '';

LocalCacheData.fullUrlParameterStr = '';

LocalCacheData.setLocalCache = function( key, val, format ) {
	if ( LocalCacheData.isSupportHTML5LocalCache ) {

		if ( format === 'JSON' ) {

			sessionStorage.setItem( key, JSON.stringify( val ) );
		} else {
			sessionStorage.setItem( key, val );
		}

	}

	LocalCacheData[key] = val;
};

LocalCacheData.getLocalCache = function( key, format ) {
	if ( LocalCacheData[key] ) {

		return LocalCacheData[key];

	} else if ( !LocalCacheData[key] && sessionStorage[key] ) {

		var result = sessionStorage.getItem( key );

		if ( format === 'JSON' ) {
			result = JSON.parse( result )
		}

		if ( result === 'true' ) {
			result = true;
		} else if ( result === 'false' ) {
			result = false;
		}

		LocalCacheData[key] = result;

		return LocalCacheData[key];
	}

	return null;
};

LocalCacheData.getI18nDic = function() {
	return LocalCacheData.getLocalCache( 'i18nDic', 'JSON' );
};

LocalCacheData.setI18nDic = function( val ) {

	LocalCacheData.setLocalCache( 'i18nDic', val, 'JSON' );
};

LocalCacheData.getViewMinMap = function() {
	return LocalCacheData.getLocalCache( 'viewMinMap', 'JSON' );
};

LocalCacheData.setViewMinMap = function( val ) {

	LocalCacheData.setLocalCache( 'viewMinMap', val, 'JSON' );
};

//LocalCacheData.getIsApplicationBranded = function() {
//	return LocalCacheData.getLocalCache( 'isApplicationBranded' );
//};

//LocalCacheData.setIsApplicationBranded = function( val ) {
//
//	LocalCacheData.setLocalCache( 'isApplicationBranded', val );
//};

//LocalCacheData.getOrgUrl = function() {
//	return LocalCacheData.getLocalCache( 'OrgUrl' );
//};
//
//LocalCacheData.setOrgUrl = function( val ) {
//	LocalCacheData.setLocalCache( 'OrgUrl', val );
//};

LocalCacheData.getCopyRightInfo = function() {
	return LocalCacheData.getLocalCache( 'copyRightInfo' );
};

LocalCacheData.setCopyRightInfo = function( val ) {
	LocalCacheData.setLocalCache( 'copyRightInfo', val );
};

LocalCacheData.getApplicationName = function() {
	return LocalCacheData.getLocalCache( 'applicationName' );
};

LocalCacheData.setApplicationName = function( val ) {
	LocalCacheData.setLocalCache( 'applicationName', val );
};

LocalCacheData.getCurrentCompany = function() {
	return LocalCacheData.getLocalCache( 'current_company', 'JSON' );
};

LocalCacheData.setCurrentCompany = function( val ) {

	LocalCacheData.setLocalCache( 'current_company', val, 'JSON' );
};

LocalCacheData.getLoginUser = function() {
	return LocalCacheData.getLocalCache( 'loginUser', 'JSON' );
};

LocalCacheData.setLoginUser = function( val ) {
	LocalCacheData.setLocalCache( 'loginUser', val, 'JSON' );
};

LocalCacheData.getCurrentCurrencySymbol = function() {
	return LocalCacheData.getLocalCache( 'currentCurrencySymbol' );
};

LocalCacheData.setCurrentCurrencySymbol = function( val ) {
	LocalCacheData.setLocalCache( 'currentCurrencySymbol', val );
};

LocalCacheData.getLoginUserPreference = function() {
	return LocalCacheData.getLocalCache( 'loginUserPreference', 'JSON' );
};

LocalCacheData.setLoginUserPreference = function( val ) {
	LocalCacheData.setLocalCache( 'loginUserPreference', val, 'JSON' );
};

LocalCacheData.getPermissionData = function() {
	return LocalCacheData.getLocalCache( 'permissionData', 'JSON' );
};

LocalCacheData.setPermissionData = function( val ) {
	LocalCacheData.setLocalCache( 'permissionData', val, 'JSON' );
};

LocalCacheData.getUniqueCountryArray = function() {
	return LocalCacheData.getLocalCache( 'uniqueCountryArray', 'JSON' );
};

LocalCacheData.setUniqueCountryArray = function( val ) {
	LocalCacheData.setLocalCache( 'uniqueCountryArray', val, 'JSON' );
};

LocalCacheData.getStationID = function() {

	var result = LocalCacheData.getLocalCache( 'StationID' );
	if ( !result ) {
		result = ''
	}

	return result;
};

LocalCacheData.setStationID = function( val ) {
	LocalCacheData.setLocalCache( 'StationID', val );
};

LocalCacheData.getSessionID = function() {

	var result = LocalCacheData.getLocalCache( 'SessionID' );
	if ( !result ) {
		result = ''
	}

	return result;
};

LocalCacheData.setSessionID = function( val ) {

	LocalCacheData.setLocalCache( 'SessionID', val );
};

LocalCacheData.getLoginData = function() {
	return LocalCacheData.getLocalCache( 'loginData', 'JSON' );
};

LocalCacheData.setLoginData = function( val ) {

	LocalCacheData.setLocalCache( 'loginData', val, 'JSON' );
};

LocalCacheData.getCurrentSelectMenuId = function() {
	return LocalCacheData.getLocalCache( 'currentSelectMenuId' );
};

LocalCacheData.setCurrentSelectMenuId = function( val ) {

	LocalCacheData.setLocalCache( 'currentSelectMenuId', val );
};

LocalCacheData.getCurrentSelectSubMenuId = function() {
	return LocalCacheData.getLocalCache( 'currentSelectSubMenuId' );
};

LocalCacheData.setCurrentSelectSubMenuId = function( val ) {

	LocalCacheData.setLocalCache( 'currentSelectSubMenuId', val );
};
