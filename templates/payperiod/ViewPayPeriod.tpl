{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="pay_period" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$ppf->Validator->isValid()}
					{include file="form_errors.tpl" object="ppf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="ppf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="status_id">
							{html_options options=$status_options selected=$pay_period_data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="ppf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE+TIME" epoch=$pay_period_data.start_date  default=TRUE}
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="ppf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE+TIME" epoch=$pay_period_data.end_date  default=TRUE}
					</td>
				</tr>

				<tr onClick="showHelpEntry('transaction_date')">
					<td class="{isvalid object="ppf" label="transaction_date" value="cellLeftEditTable"}">
						{t}Transaction Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE+TIME" epoch=$pay_period_data.transaction_date  default=TRUE}
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_punches')">
					<td class="{isvalid object="ppf" label="total_punches" value="cellLeftEditTable"}">
						{t}Total Punches:{/t}
					</td>
					<td class="cellRightEditTable">
						{$pay_period_data.total_punches}
					</td>
				</tr>

				<tr onClick="showHelpEntry('pending_requests')">
					<td class="{isvalid object="ppf" label="pending_requests" value="cellLeftEditTable"}">
						{t}Pending Requests:{/t}
					</td>
					<td class="cellRightEditTable" style="background: {if $pay_period_data.pending_requests > 0}red{else}green{/if};">
						{$pay_period_data.pending_requests}
					</td>
				</tr>

				<tr onClick="showHelpEntry('pending_shift_amendments')">
					<td class="{isvalid object="ppf" label="pending_shift_amendments" value="cellLeftEditTable"}">
						{t}Exceptions:{/t}
					</td>
					<td class="cellRightEditTable">
						<b>
						{$exceptions.low}
						/ <font color="blue">{$exceptions.med}</font>
						/ <font color="orange">{$exceptions.high}</font>
						/ <font color="red">{$exceptions.critical}</font>
						</b>
					</td>
				</tr>

				{if $pay_period_data.status_id != 20}
				<tr onClick="showHelpEntry('pending_shift_amendments')">
					<td class="cellLeftEditTable">
						{t}Action:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="submit" class="button" name="action:generate_paystubs" value="{t}Generate Paystubs{/t}">
						<input type="submit" class="button" name="action:import" value="{t}Import Data{/t}" onClick="return confirmSubmit('{t}This will import employee attendance data from other pay periods into this pay period. Are you sure you want to continue?{/t}')">
						<input type="submit" class="button" name="action:delete_data" value="{t}Delete Data{/t}" onClick="return confirmSubmit('{t}This will delete all attendance data assigned to this pay period. Are you sure you want to continue?{/t}')">
					</td>
				</tr>
				{/if}

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="javascript: if (document.pay_period.status_id.value == 20) {literal}{ return confirmSubmit('{/literal}{t}Once a Pay Period is closed it cannot be re-opened, and Pay Stubs cannot be modified. Are you sure you want to close this Pay Period now?{/t}{literal}') }{/literal}">
		</div>

		<input type="hidden" name="pay_period_id" value="{$pay_period_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
