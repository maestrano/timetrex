{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ripf->Validator->isValid()}
					{include file="form_errors.tpl" object="ripf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="ripf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('punch_type')">
					<td class="{isvalid object="ripf" label="punch_type" value="cellLeftEditTable"}">
						{t}Punch Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[punch_type_id]">
							{html_options options=$data.punch_type_options selected=$data.punch_type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('round_type')">
					<td class="{isvalid object="ripf" label="round_type" value="cellLeftEditTable"}">
						{t}Round Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="round_type_id" name="data[round_type_id]">
							{html_options options=$data.round_type_options selected=$data.round_type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('interval')">
					<td class="{isvalid object="ripf" label="interval" value="cellLeftEditTable"}">
						{t}Interval:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="6" name="data[interval]" value="{gettimeunit value=$data.interval}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('grace')">
					<td class="{isvalid object="ripf" label="grace" value="cellLeftEditTable"}">
						{t}Grace Period:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="6" name="data[grace]" value="{gettimeunit value=$data.grace}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('strict')">
					<td class="{isvalid object="ripf" label="strict" value="cellLeftEditTable"}">
						{t}Strict Schedule:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[strict]" value="1"{if $data.strict == TRUE}checked{/if}>  ({t}Employee can't work more than scheduled time{/t})
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
