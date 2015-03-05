<html>
<head>
<link rel="stylesheet" href="{$BASE_URL}{$css_file}" type="text/css" />

<title>{$APPLICATION_NAME}{if $title != ''} - {$title}{/if}</title>
<SCRIPT language=JavaScript src="{$BASE_URL}global.js.php" type=text/javascript></SCRIPT>

<SCRIPT language=JavaScript>
{literal}
function help_window(group) {
	help=window.open('{/literal}{$BASE_URL}{literal}help/WindowViewHelp.php?script='+ encodeURI(group) +'&title='+ encodeURI('{/literal}{$title|escape}{literal}'),"Help","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=400,height=600,resizable=1");
}
{/literal}
</SCRIPT>

<SCRIPT language=JavaScript src="{$BASE_URL}menu/milonic_src.js" type=text/javascript></SCRIPT>
<script language=JavaScript>
if(ns4)_d.write("<scr"+"ipt language=JavaScript src={$BASE_URL}menu/mmenuns4.js><\/scr"+"ipt>");
else _d.write("<scr"+"ipt language=JavaScript src={$BASE_URL}menu/mmenudom.js><\/scr"+"ipt>");
</script>
<SCRIPT language=JavaScript src="{$BASE_URL}menu.js.php" type=text/javascript></SCRIPT>

</head>
<body id="body" onLoad="{*onload_quick_help();*}handleMenuOverlapLogo();onload_column_expand();{$body_onload};firstElementFocus();{if (!isset($newMailPopUp) AND $unread_messages > 0)}newMailPopUp('{$BASE_URL}');{/if}{if time() >= strtotime('15-Jan-2014')}document.getElementById('popUpDiv').className += ' visible';{/if}">
{if time() >= strtotime('15-Jan-2014')}{include file="migration_notice.tpl"}{/if}

{if $enable_ajax == TRUE}
<script type='text/javascript' src='{$BASE_URL}ajax_server.php?client=all&stub=AJAX_Server'></script>
{/if}

{if $enable_calendar == TRUE}
<style type="text/css">@import url({$BASE_URL}jscalendar/skins/aqua/theme.css);</style>
<script type="text/javascript" src="{$BASE_URL}jscalendar/calendar_stripped.js"></script>
<script type="text/javascript" src="{$BASE_URL}jscalendar/lang/calendar-{$CALENDAR_LANG}.js"></script>
<script type="text/javascript" src="{$BASE_URL}jscalendar/calendar-setup_stripped.js"></script>
{/if}

{if $enable_auto_suggest == TRUE}
<link rel="stylesheet" href="{$BASE_URL}auto_suggest/autosuggest.css" type="text/css" />
<script language="Javascript" src="{$BASE_URL}auto_suggest/autosuggest.js"></script>
<div id="autosuggest"><ul></ul></div>
{/if}

{* {getquickhelpdata} *}

<div id="container">
	<div id="rowHeader">
		<span class="imgClientLogo">
			<a href="{$BASE_URL}index.php"><img src="{if $current_company->getLogoFileName() != FALSE}{$BASE_URL}/send_file.php?object_type=company_logo{else}{$IMAGES_URL}timetrex_logo_wbg_small2.jpg{/if}" style="width:auto; height:42px; visibility: hidden;" id="header_logo" alt="{$APPLICATION_NAME}"></a>
		</span>
		<div id="rowHeaderContainer">
		<div id="rowHeaderText">&nbsp;{$current_company->getName()} - {$current_user->getFullName()}</div>
		<div id="rowHeaderMenu">M</script>
		</div>
	</div>
</div>
<div id="rowBreadcrumbs">
	{*
	<span class="imgRight">
		{if $permission->Check('help','enabled') AND ( $permission->Check('help','view') OR $permission->Check('help','view_own') )}
			<a href="javascript:hideAllHelpEntries();showHelpEntry('default');placeQuickHelpWindow();showQuickHelpWindow()">
				<img src="{$IMAGES_URL}quick_help.gif" width="73" height="16" alt="">
			</a>
		{/if}
	</span>
	*}
	{displaybreadcrumbs}
	{if $DB_TIME_ZONE_ERROR == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME}WARNING: %1 was unable to set your time zone. Please contact your %1 administrator immediately.{/t} {if $permission->Check('company','enabled') AND $permission->Check('company','edit_own')}<a href="http://forums.timetrex.com/viewtopic.php?t=40">{t}For more information please click here.{/t}</a>{/if}</div>
	{/if}
	{if $CRON_OUT_OF_DATE == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME}WARNING: %1 maintenance jobs have not run in the last 48hours. Please contact your %1 administrator immediately.{/t}</div>
	{/if}
	{if $INSTALLER_ENABLED == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME}WARNING: %1 is currently in INSTALL MODE. Please go to your timetrex.ini.php file and set "installer_enabled" to "FALSE".{/t}</div>
	{/if}
	{if $VALID_INSTALL_REQUIREMENTS == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME}WARNING: %1 system requirement check has failed! Please contact your %1 administrator immediately to re-run the %1 installer to correct the issue.{/t}</div>
	{/if}
	{if $VERSION_MISMATCH == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME}WARNING: %1 application version does not match database version. Please re-run the TimeTrex installer to complete the upgrade process.{/t}</div>
	{/if}
	{if $VERSION_OUT_OF_DATE == 1}
	<div id="rowError">{t escape="no" 1=$APPLICATION_NAME 2=$APPLICATION_VERSION}WARNING: This %1 version (v%2) is severely out of date and may no longer be supported. Please upgrade to the latest version as soon as possible as invalid calculations may already be occurring.{/t}</div>
	{/if}
</div>
<a name="top"></a>
<!-- End Header -->
