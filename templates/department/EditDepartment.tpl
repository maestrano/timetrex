{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$df->Validator->isValid()}
					{include file="form_errors.tpl" object="df"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="df" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="department_data[status]">
							{html_options options=$department_data.status_options selected=$department_data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="df" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="department_data[name]" value="{$department_data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('manual_id')">
					<td class="{isvalid object="df" label="manual_id" value="cellLeftEditTable"}">
						{t}Code:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="department_data[manual_id]" value="{$department_data.manual_id|default:$department_data.next_available_manual_id}">
						{if $department_data.next_available_manual_id != ''}
						{t}Next available code{/t}: {$department_data.next_available_manual_id}
						{/if}
					</td>
				</tr>

				{if isset($department_data.other_field_names.other_id1) }
					<tr onClick="showHelpEntry('other_id1')">
						<td class="{isvalid object="df" label="other_id1" value="cellLeftEditTable"}">
							{$department_data.other_field_names.other_id1}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="department_data[other_id1]" value="{$department_data.other_id1}">
						</td>
					</tr>
				{/if}

				{if isset($department_data.other_field_names.other_id2) }
				<tr onClick="showHelpEntry('other_id2')">
					<td class="{isvalid object="df" label="other_id2" value="cellLeftEditTable"}">
						{$department_data.other_field_names.other_id2}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="department_data[other_id2]" value="{$department_data.other_id2}">
					</td>
				</tr>
				{/if}
				{if isset($department_data.other_field_names.other_id3) }
				<tr onClick="showHelpEntry('other_id3')">
					<td class="{isvalid object="df" label="other_id3" value="cellLeftEditTable"}">
						{$department_data.other_field_names.other_id3}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="department_data[other_id3]" value="{$department_data.other_id3}">
					</td>
				</tr>
				{/if}
				{if isset($department_data.other_field_names.other_id4) }
					<tr onClick="showHelpEntry('other_id4')">
						<td class="{isvalid object="df" label="other_id4" value="cellLeftEditTable"}">
							{$department_data.other_field_names.other_id4}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="department_data[other_id4]" value="{$department_data.other_id4}">
						</td>
					</tr>
				{/if}
				{if isset($department_data.other_field_names.other_id5) }
					<tr onClick="showHelpEntry('other_id5')">
						<td class="{isvalid object="df" label="other_id5" value="cellLeftEditTable"}">
							{$department_data.other_field_names.other_id5}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="department_data[other_id5]" value="{$department_data.other_id5}">
						</td>
					</tr>
				{/if}

				{if is_array($department_data.branch_list_options)}
				<tr onClick="showHelpEntry('branch')">
					<td class="{isvalid object="df" label="branch" value="cellLeftEditTable"}">
						{t}Branches:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="department_data[branch_list][]" multiple>
							{html_options options=$department_data.branch_list_options selected=$department_data.branch_list}
						</select>
					</td>
				</tr>
				{/if}

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="department_data[id]" value="{$department_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
