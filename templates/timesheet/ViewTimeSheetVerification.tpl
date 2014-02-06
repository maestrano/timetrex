{include file="sm_header.tpl" body_onload="fixWidth();"}
<script	language=JavaScript>

{literal}
function fixWidth() {
	resizeWindowToFit(document.getElementById('body'), 'both');
}

function viewTimeSheet(userID,dateStamp) {
	window.opener.location.href = '{/literal}{$BASE_URL}{literal}timesheet/ViewUserTimeSheet.php?filter_data[user_id]='+ encodeURI(userID) +'&filter_data[date]='+ encodeURI(dateStamp);
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pptsvf->Validator->isValid()}
					{include file="form_errors.tpl" object="pptsvf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.user_full_name}
					</td>
				</tr>

				<tr onClick="showHelpEntry('date_stamp')">
					<td class="{isvalid object="pptsvf" label="datestamp" value="cellLeftEditTable"}">
						{t}Pay Period:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE" epoch=$data.pay_period_start_date} - {getdate type="DATE" epoch=$data.pay_period_end_date}

						[ <a href="javascript:viewTimeSheet('{$data.user_id}','{$data.pay_period_start_date}');">{t}View TimeSheet{/t}</a> ]
					</td>
				</tr>

				<tr>
					<td colspan="2">
						{embeddedauthorizationlist object_type_id=90 object_id=$data.id}
					</td>
				</tr>

				{if $data.authorized == FALSE AND $permission->Check('punch','authorize')}
					<tr class="tblHeader">
						<td colspan="2">
							<input type="submit" class="button" name="action:decline" value="{t}Decline{/t}">
							<input type="submit" class="button" name="action:pass" value="{t}Pass{/t}">
							<input type="submit" class="button" name="action:authorize" value="{t}Authorize{/t}">
						</td>
					</tr>
				{/if}

			</table>
		</div>

		<input type="hidden" name="timesheet_id" value="{$data.id}">
		<input type="hidden" name="selected_level" value="{$selected_level}">
		<input type="hidden" name="timesheet_queue_ids" value="{$timesheet_queue_ids}">

		</form>
	</div>
</div>

{if $data.id != ''}
<br>
<br>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{t}Messages{/t}</span></div>
</div>
<div id="rowContentInner">
	<div id="contentBoxTwoEdit">
		<table class="tblList">
			<tr>
				<td>
					{embeddedmessagelist object_type_id=90 object_id=$data.id object_user_id=$data.user_id}
				</td>
			</tr>
		</table>
	</div>
</div>
{/if}
{*{include file="footer.tpl"}*}