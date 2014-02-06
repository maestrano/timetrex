{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				{if $install_obj->isInstallMode() == TRUE}
					<tr class="tblDataWhiteNH">
						<td>
							{if $install_obj->getLicenseText() != FALSE }
								<b>
								{t}Please read through the following license and if you agree and accept it, click
								the "I Accept" checkbox at the bottom.{/t}
								</b>
								<br>
								<br>
								<textarea rows="20" cols="80" name="data[license]">{$install_obj->getLicenseText()|escape}</textarea>
								<br>
							{else}
								{t}NO LICENSE FILE FOUND, Your installation appears to be corrupt!{/t}
							{/if}
							<input type="checkbox" class="checkbox" name="data[license_accept]" id="license_accept" value="1" onClick='toggleNextButton();'><a href='javascript:void(0)' onClick='toggleLicenseAccept();toggleNextButton();'>{t}I Accept{/t}</a>
							<br>
							<Br>
						</td>
					</tr>
					<tr>
						<td class="tblPagingLeft" colspan="7" align="right">
							<input type="submit" class="btnSubmit" id="next_button" name="action:Start" value="{t}Start{/t}" disabled>
						</td>
					</tr>
				{else}
				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t}The installer has already been run, as a safety measure it has been disabled from running again. If you are absolutely sure you want to run it again, or upgrade your system, please go to your timetrex.ini.php file and set "installer_enabled" to "TRUE". The line should look like:{/t}
						<br>
						<br>
						"<b>{t}installer_enabled = TRUE{/t}</b>"
						<br>
						<br>
						{t escape="no"}After this change has been made, you can click the "Start" button below to begin your installation. <b>After the installation is complete, you will want to change "installer_enabled" to "FALSE".</b>{/t}
						<br>
						<br>
						{t}For help, please visit{/t} <a href="http://www.timetrex.com">www.timetrex.com</a>
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<input type="submit" class="btnSubmit" id="next_button" name="action:start" value="{t}Start{/t}">
					</td>
				</tr>
				{/if}
			</table>
			<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}