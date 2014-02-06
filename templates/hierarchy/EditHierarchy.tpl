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

				<tr onClick="showHelpEntry('parent')">
					<td class="{isvalid object="hf" label="parent" value="cellLeftEditTable"}">
						{t}Parent:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<select name="user_data[parent_id]">
							{html_options options=$parent_list_options selected=$selected_node.parent_id}
						</select>
					</td>
				</tr>
				<tr onClick="showHelpEntry('share')">
					<td class="{isvalid object="hf" label="share" value="cellLeftEditTable"}">
						{t}Shared:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="checkbox" name="user_data[share]" value="1" {if $selected_node.shared == TRUE}checked{/if}>
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="hf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b>
					</td>

					<td colspan="3">
						<table class="editTable">
							<tr class="tblHeader">
								<td>
									{t}UnSelected Employees{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Employees{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="1" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
									<br>
									<select name="src_user_id" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$src_user_options array2=$selected_user_options}" multiple>
										{html_options options=$src_user_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$src_user_options array2=$selected_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$src_user_options array2=$selected_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									<br>
									<br>
									<br>
									<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
								</td>
								<td class="cellRightEditTable" width="1"  align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
									<br>
									<select name="user_data[user_id][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$src_user_options array2=$selected_user_options}" multiple>
										{html_options options=$selected_user_options selected=$user_id}
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))">
		</div>

		<input type="hidden" name="id" value="{$id}">
		<input type="hidden" name="old_id" value="{$old_id}">
		<input type="hidden" name="hierarchy_id" value="{$hierarchy_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
