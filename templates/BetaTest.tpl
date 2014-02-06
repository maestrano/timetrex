<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>{$APPLICATION_NAME}</title>
	<link rel="stylesheet" type="text/css" href="{$BASE_URL}global.css.php">

<script language=JavaScript>
{literal}
function confirm(form) {

	if ( document.getElementById('agree').checked == true ) {
		window.location.href = '{/literal}{$BASE_URL}flex/{if $user_name != '' AND $password != ''}?user_name={$user_name}&password={$password}{/if}{literal}';
	} else {
		alert('Please click the checkbox acknowledging the conditions of beta testing.');
	}

}
{/literal}
</script>
</head>

<iframe src="http://www.timetrex.com/embedded/viewlets/intro_v5/index.php" width="100%" height="600" scrolling="no" frameborder="0" marginheight="0">
  <p>{t}Unfortunately your browser does not support frames, please upgrade your browser.{/t}</p>
</iframe>
<table align="center" width="910" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="4" bgcolor="#779BBE" align="center">
			<b><h3><input type="checkbox" name="agree" id="agree" value="1">By checking this box I hereby acknowledge that {$APPLICATION_NAME} v5.0 shares the same live production database as the previous version of {$APPLICATION_NAME}, therefore if I add, modify or delete any data while using this new version it will also affect data in the previous version.</h3></b>
		</td>
	</tr>
	<tr>
		<td colspan="4" bgcolor="#779BBE" align="center">
			<input type="submit" name="action" value="Take me to {$APPLICATION_NAME} v5.0" onClick="return confirm(this)">
			<br>
			<br>
		</td>
	</tr>
</table>
{include file="footer.tpl"}
