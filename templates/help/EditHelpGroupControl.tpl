{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$hgcf->Validator->isValid()}
					{include file="form_errors.tpl" object="hgcf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('id')">
					<td class="{isvalid object="hgcf" label="status" value="cellLeftEditTable"}">
						{t}ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$help_data.id|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('script_name')">
					<td class="{isvalid object="hgcf" label="script_name" value="cellLeftEditTable"}">
						{t}Script Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="80" name="help_data[script_name]" value="{$help_data.script_name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hgcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="80" name="help_data[name]" value="{$help_data.name}">
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
