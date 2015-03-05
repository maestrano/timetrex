<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>{$APPLICATION_NAME} - Down For Maintenance</title>
	<link rel="stylesheet" type="text/css" href="{$BASE_URL}global.css.php">
</head>

<body>

<div id="container">

<div id="rowHeaderLogin"><a href="http://{$ORGANIZATION_URL}"><img src="{if $exception == 'dberror'}{$IMAGES_URL}/timetrex_logo_wbg_small2.jpg{else}{$BASE_URL}/send_file.php?object_type=primary_company_logo{/if}" alt="{$ORGANIZATION_NAME}"></a></div>

<div id="rowContentLogin">
  <form method="post" name="login" action="{$smarty.server.SCRIPT_NAME}">
  <div id="contentBox">

    <div class="textTitle2">{$title}</div>
    <div id="contentBoxOne"></div>

    <div id="contentBoxTwo">
		<div id="rowWarning" valign="center">
		{if $DEPLOYMENT_ON_DEMAND == TRUE}
			{if strtolower($exception) == 'dbtimeout'}
				{$APPLICATION_NAME} {t}database query has timed-out, if you were trying to run a report it may be too large, please narrow your search criteria and try again.{/t}
			{else}
				{$APPLICATION_NAME} {t}is currently undergoing maintenance. We're sorry for any inconvenience this may cause. Please try back later.{/t}
			{/if}
		{else}
			{if strtolower($exception) == 'dberror'}
				{$APPLICATION_NAME} {t}is unable to connect to its database, please make sure that the database service on your own local {$APPLICATION_NAME} server has been started and is running. If you are unsure, try rebooting your server.{/t}
			{elseif strtolower($exception) == 'dbtimeout'}
				{$APPLICATION_NAME} {t}database query has timed-out, if you were trying to run a report it may be too large, please narrow your search criteria and try again.{/t}
			{elseif strtolower($exception) == 'dbinitialize'}
				{$APPLICATION_NAME} {t}database has not been initialized yet, please run the installer again and follow the on screen instructions.{/t} <a href="{$BASE_URL}/install/install.php?external_installer=1">{t}Click here to run the installer now.{/t}</a>
			{else}
				{$APPLICATION_NAME} {t}experienced a general error, please contact technical support.{/t}
			{/if}
		{/if}
		</div>
    </div>

    <div id="contentBoxThree"></div>

  </div>
  </form>
</div>

{include file="footer.tpl"}
