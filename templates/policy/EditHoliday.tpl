{include file="header.tpl" enable_calendar=true}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$hf->Validator->isValid()}
					{include file="form_errors.tpl" object="hf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('date')">
					<td class="{isvalid object="hf" label="date_stamp" value="cellLeftEditTable"}">
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="date" name="data[date_stamp]" value="{getdate type="DATE" epoch=$data.date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('date', 'cal_date', false);">
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[holiday_policy_id]" value="{$data.holiday_policy_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
