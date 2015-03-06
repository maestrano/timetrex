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

	{if $action == 'password_reset' OR $action == 'change_password'}
		<div id="contentBoxTwo">

			{if !$validator->isValid()}
				{include file="form_errors.tpl" object="validator"}
			{/if}
			<div class="row">
				<div class="cellLeft">{t}User Name:{/t} </div>
				<div class="cellRight">{$user_name}</div>
			</div>
			<div class="row">
				<div class="cellLeft">{t}New Password:{/t} </div>
				<div class="cellRight"><input type="password" name="password" size="25"></div>
			</div>
			<div class="row">
				<div class="cellLeft">{t}New Password (confirm):{/t} </div>
				<div class="cellRight"><input type="password" name="password2" size="25"></div>
			</div>
		</div>

		<input type="hidden" name="key" value="{$key}">

		<div id="contentBoxThree"></div>
		<div id="contentBoxFour">
			<input type="submit" class="button" name="action:change_password" value="{t}Change Password{/t}">
		</div>

	{elseif $email_sent == 1}
		<div id="contentBoxTwo">
			<div id="rowWarning" valign="center">
				<br>
				{t escape="no" 1=$email}An email has been sent to <b>%1</b> with instructions on how to change your password.{/t}
				<br>&nbsp;
			</div>
		</div>

		<div id="contentBoxThree"></div>
		<div id="contentBoxFour">
		</div>
	{else}
		<div id="contentBoxTwo">

			{if !$validator->isValid()}
				{include file="form_errors.tpl" object="validator"}
			{/if}

			<div class="row">
				<div class="cellLeft">{t}Email Address:{/t} </div>
				<div class="cellRight"><input type="text" name="email" value="{$email}" size="40"></div>
			</div>

		</div>

		<div id="contentBoxThree"></div>
		<div id="contentBoxFour">
			<input type="submit" class="button" name="action:reset_password" value="{t}Reset Password{/t}">
		</div>

	{/if}

  </div>
  </form>
</div>
{include file="footer.tpl"}
