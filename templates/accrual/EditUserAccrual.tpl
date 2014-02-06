{include file="header.tpl" enable_calendar=true body_onload="showWeeklyTime('weekly_time');"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$af->Validator->isValid()}
					{include file="form_errors.tpl" object="af"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="af" label="user_id" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id != ''}
							{$data.user_options[$data.user_id]}
							<input type="hidden" name="data[user_id]" value="{$data.user_id}">
						{else}
							<select id="user_id" name="data[user_id]">
								{html_options options=$data.user_options selected=$data.user_id}
							</select>
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('accrual_policy')">
					<td class="{isvalid object="af" label="accural_policy_id" value="cellLeftEditTable"}">
						{t}Accrual Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id == ''}
						<select id="accrual_policy_id" name="data[accrual_policy_id]">
							{html_options options=$data.accrual_policy_options selected=$data.accrual_policy_id}
						</select>
						{else}
							{$data.accrual_policy_options[$data.accrual_policy_id]}
							<input type="hidden" name="data[accrual_policy_id]" value="{$data.accrual_policy_id}">
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="af" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('amount')">
					<td class="{isvalid object="af" label="amount" value="cellLeftEditTable"}">
						{t}Amount:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[amount]" value="{gettimeunit value=$data.amount}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('time_stamp')">
					<td class="{isvalid object="af" label="time_stamp" value="cellLeftEditTable"}">
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="time_stamp" name="data[time_stamp]" value="{getdate type="DATE" epoch=$data.time_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_time_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('time_stamp', 'cal_time_stamp', false);">
						ie: {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>


{*
				<tr onClick="showHelpEntry('trigger_time')">
					<td class="{isvalid object="af" label="trigger_time" value="cellLeftEditTable"}">
						{t}Active After:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[trigger_time]" value="{gettimeunit value=$data.trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('rate')">
					<td class="{isvalid object="af" label="rate" value="cellLeftEditTable"}">
						{t}Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[rate]" value="{$data.rate}">
					</td>
				</tr>
*}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
