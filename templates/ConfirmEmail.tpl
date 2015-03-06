<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>{$APPLICATION_NAME} - Login</title>
	<link rel="stylesheet" type="text/css" href="{$BASE_URL}global.css.php">
	<meta name="referrer" content="never"> {* Don't send referrer to prevent password reset link from leaking. *}
</head>

<div id="container">

<div id="rowHeaderLogin"><a href="{$BASE_URL}"><img src="{$BASE_URL}/send_file.php?object_type=primary_company_logo" style="width:auto; height:42px;" alt="{$ORGANIZATION_NAME}"></a></div>

<div id="rowContentLogin">
  <form method="post" name="password_reset" action="{$smarty.server.SCRIPT_NAME}">
  <div id="contentBox">

    <div class="textTitle2"><img src="{$IMAGES_URL}lock.gif" width="28" height="28" alt="" class="imgLock">{$title}</div>
    <div id="contentBoxOne"></div>

	{if $email_confirmed == TRUE}
		<div id="contentBoxTwo">
			<div id="rowWarning" valign="center">
				<br>
				{t escape="no" 1=$email}Email address <b>%1</b> has been confirmed and activated.{/t}
				<br>&nbsp;
			</div>
		</div>

		<div id="contentBoxThree"></div>
		<div id="contentBoxFour">
		</div>
	{elseif $email_confirmed == FALSE}
		<div id="contentBoxTwo">
			<div id="rowWarning" valign="center">
				<br>
				{t}Invalid or expired confirmation key, please try again.{/t}
				<br>&nbsp;
			</div>
		</div>

		<div id="contentBoxThree"></div>
		<div id="contentBoxFour">
		</div>
	{/if}

  </div>
  </form>
</div>
{include file="footer.tpl"}
