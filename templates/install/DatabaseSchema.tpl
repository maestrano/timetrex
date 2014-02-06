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
					<td class="tblDataWhiteNH" colspan="2" align="center">
						<br>
						<div align="center">
						{if $install_obj->getIsUpgrade() == TRUE}{t}Upgrading{/t}{else}{t}Initializing{/t}{/if} {t}database, please wait...{/t}
						<br>
						<iframe scrolling="no" frameborder="0" style="width:30%; height:45px; border: 0px" id="ProgressBar" name="ProgressBar" src="./DatabaseSchema.php?action:install_schema=1&external_installer={$external_installer}"></iframe>
						</div>
					</td>
				</tr>
			</table>
			<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}