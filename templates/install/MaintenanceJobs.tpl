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
						<br>This is extremely important and without these maintenance jobs running
						{$APPLICATION_NAME} will fail to operate correctly.
						<br>
						<br>
						<div align="center">
						<div style="background-color: #eee; width:75%;" >
						<br>
						{if PHP_OS == 'WINNT'}
							In Windows simply run this command as Administrator. Be sure that php-win.exe is in your path!
							<br>
							<br>
							schtasks /create /tn "TimeTrex Scheduled Jobs" /tr "<b>php-win.exe</b> {$cron_file}" /sc minute
						{else}
							In Linux simply add this line to your crontab. <br>
							<b>*IMPORTANT*:</b> Be sure to replace <b>"www-data"</b> with the user that your web server runs as, otherwise the permissions will be incorrect.
							<br>
							<br>
							* * * * * su <b>www-data</b> -c "php {$cron_file}" > /dev/null 2>&1
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