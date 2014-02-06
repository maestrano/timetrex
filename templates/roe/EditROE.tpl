{include file="header.tpl" enable_calendar=true}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$roef->Validator->isValid()}
					{include file="form_errors.tpl" object="roef"}
				{/if}

				<table class="editTable">

				{if !isset($setup_data.insurable_earnings_psea_ids) OR !isset($setup_data.absence_policy_ids)}
					<tr class="tblDataError">
						<td colspan="100">
							<b>{t}ERROR: Form has not been setup yet! Please click the arrow below to do so now.{/t}</b>
						</td>
					</tr>
				{/if}

				<tr>
					<td class="{isvalid object="roef" label="user" value="cellLeftEditTable"}" nowrap>
						<a href="javascript:toggleRowObject('setup');toggleImage(document.getElementById('setup_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif');"><img style="vertical-align: middle" id="setup_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a><b> {t}Form Setup:{/t}</b>
					</td>
					<td class="cellRightEditTable" colspan="100">
						{t}Specify which criteria is used for each box in the form. Click arrow to modify.{/t}
					</td>
				</tr>

				<tbody id="setup" style="display:none" >
				<tr>
					<td class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Insurable Absence Policies:{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						<select id="columns" name="setup_data[absence_policy_ids][]" size="{select_size array=$filter_data.absence_policy_options}" multiple>
							{html_options options=$absence_policy_options selected=$setup_data.absence_policy_ids}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Insurable Earnings (Box 15B):{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						<select id="columns" name="setup_data[insurable_earnings_psea_ids][]" size="{select_size array=$filter_data.earning_pay_stub_entry_account_options}" multiple>
							{html_options options=$earning_pay_stub_entry_account_options selected=$setup_data.insurable_earnings_psea_ids}
						</select>
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Vacation Pay (Box 17A):{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						<select id="columns" name="setup_data[vacation_psea_ids][]" size="{select_size array=$filter_data.earning_pay_stub_entry_account_options}" multiple>
							{html_options options=$earning_pay_stub_entry_account_options selected=$setup_data.vacation_psea_ids}
						</select>
					</td>
				</tr>

				</tbody>

				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="roef" label="user" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="roe_data[user_id]">
							{html_options options=$user_options selected=$roe_data.user_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('code_id')">
					<td class="{isvalid object="roef" label="code_id" value="cellLeftEditTable"}">
						{t}Reason:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="roe_data[code_id]">
							{html_options options=$roe_data.code_options selected=$roe_data.code_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('pay_period_type_id')">
					<td class="{isvalid object="roef" label="pay_period_type_id" value="cellLeftEditTable"}">
						{t}Pay Period Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="roe_data[pay_period_type_id]">
							{html_options options=$roe_data.pay_period_type_options selected=$roe_data.pay_period_type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('first_date')">
					<td class="{isvalid object="roef" label="first_date" value="cellLeftEditTable"}">
						{t}First Day Worked:{/t}
						<br>
						{t}(Or first day since last ROE){/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="first_date" name="roe_data[first_date]" value="{getdate type="DATE" epoch=$roe_data.first_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_first_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('first_date', 'cal_first_date', false);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('last_date')">
					<td class="{isvalid object="roef" label="last_date" value="cellLeftEditTable"}">
						{t}Last Day For Which Paid:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="last_date" name="roe_data[last_date]" value="{getdate type="DATE" epoch=$roe_data.last_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_last_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('last_date', 'cal_last_date', false);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('pay_period_end_date')">
					<td class="{isvalid object="roef" label="pay_period_end_date" value="cellLeftEditTable"}">
						{t}Final Pay Period Ending Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="pay_period_end_date" name="roe_data[pay_period_end_date]" value="{getdate type="DATE" epoch=$roe_data.pay_period_end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_pp_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('pay_period_end_date', 'cal_pp_end_date', false);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('recall_date')">
					<td class="{isvalid object="roef" label="recall_date" value="cellLeftEditTable"}">
						{t}Expected Date of Recall:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="recall_date" name="roe_data[recall_date]" value="{getdate type="DATE" epoch=$roe_data.recall_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_recall_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('recall_date', 'cal_recall_date', false);">
					</td>
				</tr>
				<tr onClick="showHelpEntry('serial')">
					<td class="{isvalid object="roef" label="serial" value="cellLeftEditTable"}">
						{t}Serial No:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="20" name="roe_data[serial]" value="{$roe_data.serial}"> ({t}Optional{/t})
					</td>
				</tr>

				<tr onClick="showHelpEntry('comments')">
					<td class="{isvalid object="roef" label="comments" value="cellLeftEditTable"}">
						{t}Comments:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="50" name="roe_data[comments]" value="{$roe_data.comments}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('release_accruals')">
					<td class="{isvalid object="roef" label="release_accruals" value="cellLeftEditTable"}">
						{t}Release All Accruals:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="roe_data[release_accruals]" value="1" checked>
					</td>
				</tr>

				<tr onClick="showHelpEntry('generate_pay_stub')">
					<td class="{isvalid object="roef" label="generate_pay_stub" value="cellLeftEditTable"}">
						{t}Generate Final Pay Stub:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="roe_data[generate_pay_stub]" value="1" checked>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="roe_data[id]" value="{$roe_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}