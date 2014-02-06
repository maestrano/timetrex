{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$hf->Validator->isValid()}
					{include file="form_errors.tpl" object="hf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('id')">
					<td class="{isvalid object="hf" label="id" value="cellLeftEditTable"}">
						{t}ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$help_data.id|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="hf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="help_data[status]">
							{html_options options=$help_data.status_options selected=$help_data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="hf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="help_data[type]">
							{html_options options=$help_data.type_options selected=$help_data.type}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('private')">
					<td class="{isvalid object="hf" label="private" value="cellLeftEditTable"}">
						{t}Private:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" name="help_data[private]" value="1" {if $help_data.private == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('heading')">
					<td class="{isvalid object="hf" label="heading" value="cellLeftEditTable"}">
						{t}Heading:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="80" name="help_data[heading]" value="{$help_data.heading}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('body')">
					<td class="{isvalid object="hf" label="body" value="cellLeftEditTable"}">
						{t}Body:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="100" name="help_data[body]">{$help_data.body}</textarea>
					</td>
				</tr>

				<tr onClick="showHelpEntry('keywords')">
					<td class="{isvalid object="hf" label="keywords" value="cellLeftEditTable"}">
						{t}Keywords:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="80" name="help_data[keywords]" value="{$help_data.keywords}">
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
