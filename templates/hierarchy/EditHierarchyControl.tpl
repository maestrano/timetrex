{include file="header.tpl" body_onload="filterUserCount();"}

<script	language=JavaScript>

{literal}
function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$hcf->Validator->isValid() OR !$hlf->Validator->isValid()}
					{include file="form_errors.tpl" object="hcf,hlf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="hierarchy_control_data[name]" value="{$hierarchy_control_data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="hcf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="hierarchy_control_data[description]" value="{$hierarchy_control_data.description}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('objects')">
					<td class="{isvalid object="hcf" label="objects" value="cellLeftEditTable"}">
						{t}Objects:{/t}
						<Br>
						{t}(Select one or more){/t}
					</td>
					<td class="cellRightEditTable">
						<select name="hierarchy_control_data[object_type_ids][]" multiple>
							{html_options options=$hierarchy_control_data.object_type_options selected=$hierarchy_control_data.object_type_ids}
						</select>
					</td>
				</tr>

				<tbody id="filter_employees_on" style="display:none" >
				<tr>
					<td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Subordinates:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td>
								{t}UnAssigned Employees{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Assigned Employees{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
								<br>
								<select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$hierarchy_control_data.user_options}" multiple>
									{html_options options=$hierarchy_control_data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$hierarchy_control_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$hierarchy_control_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="hierarchy_control_data[user_ids][]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
									{html_options options=$filter_user_options selected=$hierarchy_control_data.user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_employees_off">
				<tr>
					<td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Subordinates:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$hierarchy_control_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>
				<tr>
				  <td colspan="3">
					<table class="tblList">
						<tr class="tblHeader">
							<td colspan="3">
								<b>{t}NOTE:{/t}</b> {t}Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities.{/t}
							</td>
						</tr>
						<tr class="tblHeader">
							<td width="50%">
								{t}Level{/t}
							</td>
							<td width="50%">
								{t}Superiors{/t}
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
							</td>
						</tr>
						{foreach name="level" from=$hierarchy_level_data item=hierarchy_level}
						  {assign var="hierarchy_level_id" value=$hierarchy_level.id}
						  {cycle assign=row_class values="tblDataWhite,tblDataGrey"}

						  <tr class="{$row_class}">
							<td>
								<input type="hidden" name="hierarchy_level_data[{$hierarchy_level.id}][id]" value="{$hierarchy_level.id}">
								{if $hierarchy_level.level > 1}
									{'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:$hierarchy_level.level-1}
								{/if}
								<input type="text" size="4" name="hierarchy_level_data[{$hierarchy_level.id}][level]" value="{$hierarchy_level.level}">
							</td>
							<td>
								{if $hierarchy_level.level > 1}
									{'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:$hierarchy_level.level-1}
								{/if}
							  <select id="hierarchy_level-{$hierarchy_level.id}" name="hierarchy_level_data[{$hierarchy_level.id}][user_id]">
								  {html_options options=$hierarchy_control_data.level_user_options selected=$hierarchy_level.user_id}
							  </select>
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="ids[]" value="{$hierarchy_level.id}">
							</td>
						  </tr>
						{/foreach}
					</table>
				  </td>
				</tr>

				<tr>
					<td class="tblActionRow" colspan="3">
						<input type="submit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))">
						<input type="submit" name="action:add_level" value="{t}Add Level{/t}" onClick="selectAll(document.getElementById('filter_user'))">
						<input type="submit" name="action:delete_level" value="{t}Delete Level{/t}" onClick="selectAll(document.getElementById('filter_user'))">
					</td>
				</tr>

			</table>
		</div>
{*
		<div id="contentBoxFour">
		</div>
*}
		<input type="hidden" name="hierarchy_control_data[id]" value="{$hierarchy_control_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
