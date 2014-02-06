<html>
<head>
<link rel="stylesheet" href="{$BASE_URL}{$css_file}" type="text/css" />

<title>{$APPLICATION_NAME}</title>

{if !isset($disable_global_js)}
<SCRIPT language=JavaScript src="{$BASE_URL}global.js.php{if $authenticate != ''}?authenticate={$authenticate}{/if}" type=text/javascript></SCRIPT>
{/if}

<SCRIPT language=JavaScript>
{literal}
function help_window(group) {
	help=window.open('{/literal}{$BASE_URL}{literal}help/WindowViewHelp.php?script='+ encodeURI(group) +'&title='+ encodeURI('{/literal}{$title|escape}{literal}'),"Help","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=400,height=600,resizable=1");
}

{/literal}
</SCRIPT>

</head>

<body id="body" onLoad="{$body_onload};{if $is_report != TRUE}firstElementFocus();{/if}">

{if $enable_ajax == TRUE}
<script type='text/javascript' src='{$BASE_URL}ajax_server.php?client=all&stub=AJAX_Server'></script>
{/if}

{if $enable_calendar == TRUE}
<style type="text/css">@import url({$BASE_URL}jscalendar/skins/aqua/theme.css);</style>
<script type="text/javascript" src="{$BASE_URL}jscalendar/calendar.js"></script>
<script type="text/javascript" src="{$BASE_URL}jscalendar/lang/calendar-{$CALENDAR_LANG}.js"></script>
<script type="text/javascript" src="{$BASE_URL}jscalendar/calendar-setup.js"></script>
{/if}

{*
//This causes small iframes to be at least 600px in height.
{getquickhelpdata}
*}

<a name="top"></a>
<!-- End Header -->
