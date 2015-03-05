{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t escape="no"}<b>Congratulations!</b> You have successfully{/t} {if $upgrade == 1}{t}upgraded{/t}{else}{t}installed{/t}{/if} {$APPLICATION_NAME}.
						<br>
						<br>
						{*
						{t escape="no"}We recommend that you visit our Online University or read our{/t} <a href="http://www.timetrex.com/help/help.php?product=admin&edition={php}echo getTTProductEdition(){/php}&version={$APPLICATION_VERSION}&language=en">{t}Administrator Guide{/t}</a> {t escape="no" 1=$APPLICATION_NAME}to help you get started with %1.{/t}<br>
						{t}At anytime you can access either from the navigation menu, as shown below.{/t}
						<br>
						<br>
						<img src="{$IMAGES_URL}administrator_guide_menu.png">
						<br>
						<br>
						*}
						{t escape="no"}You may now <a href="{$BASE_URL}">login</a> with the user name/password that you created earlier.{/t}
					</td>
				</tr>
			</table>
		</form>
		<br>
	</div>
</div>
{include file="footer.tpl"}