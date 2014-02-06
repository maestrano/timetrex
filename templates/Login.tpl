<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>{$APPLICATION_NAME} {t}Time and Attendance - Secure Login{/t}</title>
	<link rel="stylesheet" type="text/css" href="{$BASE_URL}global.css.php">

<style type="text/css">
{literal}
#popUpDiv {
    background-color: #FFB3B3;
    color: #FFF;
    height: 41px;
	padding-top:5px;
    position: absolute;
	left: 25%;
	top: 450px;
    width: 50%;
	text-align: center;
	border-style: solid;
	border-width: 2px;
	border-color: #FF0000;
	z-index:99;
    transition: top 3.5s;
    -moz-transition: top 3.5s;
    -webkit-transition: top 3.5s;
    -o-transition: top 3.5s;
}
#popUpDiv.visible {
	top: 1px;
}
{/literal}
</style>

<script language=JavaScript>
{literal}

function bookmarkSite( title, url ) {
	if ( window.sidebar ) { // FireFox
		window.sidebar.addPanel(title, url, "");
	} else if ( window.opera && window.print ) { // Opera
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',title);
		elem.setAttribute('rel','sidebar');
		elem.click();
	}  else if ( document.all ) { // IE
		window.external.AddFavorite(url, title);
	}
}
{/literal}
</script>
</head>

<body onload="document.login.user_name.focus(); {if !isset($config_vars.branding) OR ( isset($config_vars.branding) AND time() >= strtotime('01-Jan-2014') )}document.getElementById('popUpDiv').className += ' visible';{/if}">
{if !isset($config_vars.branding) OR ( isset($config_vars.branding) AND time() >= strtotime('01-Jan-2014') )}
<div id="popUpDiv"><b><span style="font-size: small;">&nbsp;<a style="color: #FF0000; text-decoration: none;" href="{if $smarty.server.HTTP_HOST == 'www.timetrex.com' OR $smarty.server.HTTP_HOST == 'timetrex.com'}http{if $smarty.server.HTTPS == TRUE}s{/if}://{$config_vars.other.hostname}/interface/{/if}{if isset($config_vars.branding)}flex/{else}BetaTest.php{/if}{if $user_name != '' AND $password != ''}?user_name={$user_name}&password={$password}{/if}">WARNING: This legacy interface will be discontinued by January 31st 2014.<br>Please transition to our new {$APPLICATION_NAME} interface as soon as possible by clicking here!</a>&nbsp;</span></b></div>
{/if}
<div id="container">

<div id="rowHeaderLogin"><a href="http://{$ORGANIZATION_URL}"><img src="{$BASE_URL}/send_file.php?object_type=primary_company_logo" style="width:auto; height:42px;" alt="Time And Attendance"></a></div>

<div id="rowContentLogin">
  <form method="post" name="login" action="{$smarty.server.SCRIPT_NAME}">
  <div id="contentBox">

    <div class="textTitle2"><img src="{$IMAGES_URL}lock.gif" width="28" height="26" alt="Secure Login" class="imgLock">{$title}{if getTTProductEdition() == 10}<span style="float: right"><a href="http://www.timetrex.com/r.php?id=451" target="_blank"><img src="{$IMAGES_URL}/facebook_button.jpg" border="0"></a>&nbsp;<a href="http://www.timetrex.com/r.php?id=455"  target="_blank"><img src="{$IMAGES_URL}/twitter_button.jpg" border="0"></a></span>{/if}<br></div>

    <div id="contentBoxOne"></div>

    <div id="contentBoxTwo">

		{if !$validator->isValid()}
			{include file="form_errors.tpl" object="validator"}
		{/if}

		{if $password_reset == 1}
			<div id="rowWarning" valign="center">
					<br>
					<b>{t}Your password has been changed successfully, you may now login.{/t}</b>
					<br>&nbsp;
			</div>
		{/if}

		<div class="row">
			<div class="cellLeft">{t}User Name{/t}</div><div class="cellRight"><input type="text" name="user_name" value="{$user_name}" size="40"></div>
		</div>
		<div class="row">
			<div class="cellLeft">{t}Password{/t}</div><div class="cellRight"><input type="password" name="password" value="{$password}" size="40" autocomplete="off"></div>
		</div>
		<div class="row">
			<div class="cellLeft">{t}Language{/t}</div>
			<div class="cellRight">
				<select name="language">
					{html_options options=$language_options selected=$language}
				</select>
			</div>
		</div>
    </div>

    <div id="contentBoxThree"></div>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="33%" align="left"><a href="ForgotPassword.php">{t}Forgot Your Password?{/t}</a></td>
			<td width="33%" align="center">{if getTTProductEdition() > 10}<a href="quick_punch/">{t}Quick Punch{/t}</a>{/if}</td>
			<td width="33%" align="right"><a href="javascript:bookmarkSite( '{$APPLICATION_NAME} - {t}Secure Login{/t}', location.href )">{t}Bookmark This Page!{/t}</a></td>
		</tr>
	</table>
	<br>
    <div id="contentBoxFour">
		<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}">
	</div>
  </div>
  </form>
</div>

{include file="footer.tpl"}
