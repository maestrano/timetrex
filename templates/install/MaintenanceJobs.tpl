{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="post" action="{$smarty.server.SCRIPT_NAME}" onSubmit="return submitButtonPressed">

				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{$APPLICATION_NAME} <b>requires</b> that maintenance jobs be run regularly throughout the day.
						<br><font color="red"><b>This is extremely important and without these maintenance jobs running
						{$APPLICATION_NAME} will fail to operate correctly.</b></font>
						<br>
						<br>
						<div align="center">
						<div style="background-color: #eee; width:75%;" >
						<br>
						{if PHP_OS == 'WINNT'}
							In Windows simply run this command as Administrator.
							<br>
							<br>
							{$schedule_maintenance_job_command}
						{else}
							In most Linux distributions, you can run the following command{if $is_sudo_installed == FALSE} as root{/if}:<br>
							<b>{if $is_sudo_installed}sudo{/if} crontab -u {$web_server_user} -e</b><br>
							<br>
							Then add the following line to the bottom of the file:<br>
							<b>* * * * * php {$cron_file} > /dev/null 2>&1</b>
						{/if}
						<br>
						<br>
						</div>
						</div>
					</td>
				</tr>
			</table>
		<br>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" id="next_button" name="action:back" value="{t}Back{/t}" onMouseDown="submitButtonPressed = true">
			<input type="submit" class="btnSubmit" id="next_button" name="action:next" value="{t}Next{/t}" onMouseDown="submitButtonPressed = true">
		</div>

		</form>
	</div>
</div>
{include file="footer.tpl"}