{include file="header.tpl" enable_calendar=true}
<script	language="JavaScript">
{literal}
function setTransactionDate() {
  if ( document.getElementById('transaction_date').value == '' ) {
	  document.getElementById('transaction_date').value = document.getElementById('end_date').value;
  }
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$ppf->Validator->isValid()}
					{include file="form_errors.tpl" object="ppf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="ppf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="start_date" onFocus="showHelpEntry('start_date')" name="data[start_date]" value="{getdate type="DATE+TIME" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', true);">
					</td>
				</tr>

				{if $data.pay_period_schedule_type_id == 40}
				<tr onClick="showHelpEntry('advance_end_date')">
					<td class="{isvalid object="ppf" label="end_date" value="cellLeftEditTable"}">
						{t}Advance End Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="advance_end_date" onFocus="showHelpEntry('advance_end_date')" name="data[advance_end_date]" value="{getdate type="DATE+TIME" epoch=$data.advance_end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_advance_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('advance_end_date', 'cal_advance_end_date', true);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('advance_transaction_date')">
					<td class="{isvalid object="ppf" label="transaction_date" value="cellLeftEditTable"}">
						{t}Advance Transaction Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="advance_transaction_date" onFocus="showHelpEntry('advance_transaction_date')" name="data[advance_transaction_date]" value="{getdate type="DATE+TIME" epoch=$data.advance_transaction_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_advance_transaction_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('advance_transaction_date', 'cal_advance_transaction_date', true);">
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="ppf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="end_date" onFocus="showHelpEntry('end_date')" onChange="setTransactionDate()" name="data[end_date]" value="{getdate type="DATE+TIME" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', true);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('transaction_date')">
					<td class="{isvalid object="ppf" label="transaction_date" value="cellLeftEditTable"}">
						{t}Transaction Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="25" id="transaction_date" onFocus="showHelpEntry('transaction_date')" name="data[transaction_date]" value="{getdate type="DATE+TIME" epoch=$data.transaction_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_transaction_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('transaction_date', 'cal_transaction_date', true);">
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[pay_period_schedule_id]" value="{$data.pay_period_schedule_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}