{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form name="help_group" method="post" action="{$smarty.server.SCRIPT_NAME}" onsubmit="select_all(document.help_group.elements['help_data[selected_help_ids][]'])">
		    <div id="contentBoxTwoEdit">
				{if !$hgcf->Validator->isValid()}
					{include file="form_errors.tpl" object="hgcf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('id')">
					<td class="{isvalid object="hgcf" label="id" value="cellLeftEditTable"}">
						{t}ID:{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						{$help_data.id|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hgcf" label="name" value="cellLeftEditTable"}">
						{t}Script:{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						{$help_data.script_name|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hgcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td colspan="2" class="cellRightEditTable">
						{$help_data.name|default:"N/A"}
					</td>
				</tr>

				<tr class="tblHeader">
					<td>
						{t}Help:{/t}
					</td>
					<td><br></td>
					<td>
						{t}Selected{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('selected')">
					<td class="{isvalid object="hgcf" label="selected" value="cellLeftEditTable"}" style="text-align: center">
						<select name="help_data[help_ids][]" multiple>
							{html_options options=$help_data.help_options}
						</select>
					</td>
					<td style="text-align: center">
						<input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.help_group.elements['help_data[help_ids][]'], document.help_group.elements['help_data[selected_help_ids][]'])">
						<br>
						<input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item( document.help_group.elements['help_data[selected_help_ids][]'] )">
						<br>
						<br>
						<input type="button" class="select" name="select" value="Move Up" onClick="up('selected_help_ids')">
						<br>
						<input type="button" class="select" name="select" value="Move Down" onClick="down('selected_help_ids')">
					</td>
					<td class="cellLeftEditTable" style="text-align: center">
						<select id="selected_help_ids" name="help_data[selected_help_ids][]" multiple>
							{html_options options=$help_data.selected_help_options selected=$help_data.help_ids}
						</select>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action" value="Submit">
		</div>

		<input type="hidden" name="help_data[id]" value="{$help_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}

{*
				{cycle assign=row_class values="even,odd"}
				<tr id="{$row_class}">
					<td id="{isvalid object="hgcf" label="help" value="label"}" style="text-align: center">
						{t}Help{/t}
					</td id="label">
					<td><br></td>
					<td id="{isvalid object="hgcf" label="help" value="label"}" style="text-align: center">
						{t}Selected{/t}
					</td>
				</tr>

				{cycle assign=row_class values="even,odd"}
				<tr id="{$row_class}">
					<td id="form">
						<select name="help_data[help_ids][]" multiple>
							{html_options options=$help_data.help_options}
						</select>
					</td>
					<td>
						<input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="select_item(document.help_group.elements['help_data[help_ids][]'], document.help_group.elements['help_data[selected_help_ids][]'])">
						<br>
						<input type="button" class="deselect" name="deselect" value="&nbsp;&lt;&lt;&nbsp;" onClick="deselect_item( document.help_group.elements['help_data[selected_help_ids][]'] )">
						<br>
						<br>
						<input type="button" class="select" name="select" value="Move Up" onClick="up('selected_help_ids')">
						<br>
						<input type="button" class="select" name="select" value="Move Down" onClick="down('selected_help_ids')">
					</td>
					<td id="form">
						<select id="selected_help_ids" name="help_data[selected_help_ids][]" multiple>
							{html_options options=$help_data.selected_help_options selected=$help_data.help_ids}
						</select>
					</td>
				</tr>

				<tr id="head">
					<td colspan="3">
						<input type="submit" class="button" name="action" value="Submit">
					</td>
				</tr>
			</table>
		<input type="hidden" name="help_data[id]" value="{$help_data.id}">
		</form>
{include file="footer.tpl"}
*}