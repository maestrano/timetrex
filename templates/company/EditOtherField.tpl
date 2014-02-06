{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$off->Validator->isValid()}
					{include file="form_errors.tpl" object="off"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="off" label="type_id" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[type_id]">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('other_id1')">
					<td class="{isvalid object="off" label="other_id1" value="cellLeftEditTable"}">
						{t}Other ID1:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[other_id1]" value="{$data.other_id1}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('other_id2')">
					<td class="{isvalid object="off" label="other_id2" value="cellLeftEditTable"}">
						{t}Other ID2:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[other_id2]" value="{$data.other_id2}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('other_id3')">
					<td class="{isvalid object="off" label="other_id3" value="cellLeftEditTable"}">
						{t}Other ID3:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[other_id3]" value="{$data.other_id3}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('other_id4')">
					<td class="{isvalid object="off" label="other_id4" value="cellLeftEditTable"}">
						{t}Other ID4:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[other_id4]" value="{$data.other_id4}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('other_id5')">
					<td class="{isvalid object="off" label="other_id5" value="cellLeftEditTable"}">
						{t}Other ID5:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[other_id5]" value="{$data.other_id5}">
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
