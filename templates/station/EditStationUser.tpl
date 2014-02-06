{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$sf->Validator->isValid()}
					{include file="form_errors.tpl" object="sf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						{$station_data.status_options[$station_data.status]}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						{$station_data.type_options[$station_data.type]}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Station ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$station_data.station}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Source:{/t}
					</td>
					<td class="cellRightEditTable">
						{$station_data.source}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						{$station_data.description}
					</td>
				</tr>


				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="sf" label="user" value="cellLeftEditTable"}">
						{t}Employees:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="station_data[user_ids][]" multiple>
							{html_options options=$station_data.user_options selected=$station_data.user_ids}
						</select>
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="station_data[id]" value="{$station_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
