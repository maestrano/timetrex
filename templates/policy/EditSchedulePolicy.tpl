{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$spf->Validator->isValid()}
					{include file="form_errors.tpl" object="spf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="spf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('meal_policy')">
					<td class="{isvalid object="spf" label="meal_policy" value="cellLeftEditTable"}">
						{t}Meal Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="meal_policy_id" name="data[meal_policy_id]">
							{html_options options=$data.meal_options selected=$data.meal_policy_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('break_policy')">
					<td class="{isvalid object="spf" label="break_policy" value="cellLeftEditTable"}">
						{t}Break Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="break_policy_ids" name="data[break_policy_ids][]" size="{select_size array=$data.break_options}" multiple>
							{html_options options=$data.break_options selected=$data.break_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('absence_policy')">
					<td class="{isvalid object="spf" label="absence_policy" value="cellLeftEditTable"}">
						{t}Undertime Absence Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="absence_policy_id" name="data[absence_policy_id]">
							{html_options options=$data.absence_options selected=$data.absence_policy_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('overtime_policy')">
					<td class="{isvalid object="spf" label="over_time_policy" value="cellLeftEditTable"}">
						{t}Overtime Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="over_time_policy_id" name="data[over_time_policy_id]">
							{html_options options=$data.over_time_options selected=$data.over_time_policy_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('premium_policy')">
					<td class="{isvalid object="spf" label="premium_policy" value="cellLeftEditTable"}">
						{t}Premium Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="premium_policy_ids" name="data[premium_policy_ids][]" size="{select_size array=$data.premium_options}" multiple>
							{html_options options=$data.premium_options selected=$data.premium_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_stop_window')">
					<td class="{isvalid object="spf" label="start_stop_window" value="cellLeftEditTable"}">
						{t}Start / Stop Window:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="6" name="data[start_stop_window]" value="{gettimeunit value=$data.start_stop_window}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

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
