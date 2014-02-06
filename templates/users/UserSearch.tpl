{include file="sm_header.tpl" body_onload="fixHeight();"}
<script	language=JavaScript>
{literal}
function fixHeight() {
	resizeWindowToFit(document.getElementById('body'), 'both', 10);
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="user_search" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{*
				{if !$pcf->Validator->isValid() OR !$pf->Validator->isValid()}
					{include file="form_errors.tpl" object="pcf,pf"}
				{/if}
				*}

				<table class="editTable">

				<tr class="tblHeader">
					<td colspan="2">
						{t}Criteria{/t}
					</td>
					<td colspan="2">
						{$data.total_users|default:0} {t}Matching Employees{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[status_id][]" size="{select_size array=$data.status_options max=2}" multiple>
							{html_options options=$data.status_options selected=$data.status_id}
						</select>
					</td>
					<td class="cellRightEditTable" rowspan="4">
						<div valign="top">
						<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user_id') )">
						<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user_id') )">
						<br>
						<select id="filter_user_id" name="data[filter_user_ids][]" size="{select_size array=$data.user_options max=20}" multiple>
							{html_options options=$data.user_options selected=$data.filter_user_ids}
						</select>
						</div>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Group:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="group_id" name="data[group_id][]" size="{select_size array=$data.group_options max=5}" multiple>
							{html_options options=$data.group_options selected=$data.group_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Branch{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="branch_id" name="data[default_branch_id][]" size="{select_size array=$data.branch_options max=5}" multiple>
							{html_options options=$data.branch_options selected=$data.default_branch_id}
						</select>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="department_id" name="data[default_department_id][]" size="{select_size array=$data.department_options max=5}" multiple>
							{html_options options=$data.department_options selected=$data.default_department_id}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						<input type="submit" name="action:Search" value="{t}Search{/t}" onClick="unselectAll(document.getElementById('filter_user_id') )">
					</td>
					<td colspan="2">
						<input type="button" name="action:Select" value="{t}Select{/t}" onClick="window.opener.moveItem(document.getElementById('filter_user_id'), window.opener.document.getElementById('{$dst_element_id}')); uniqueSelect(window.opener.document.getElementById('{$dst_element_id}'), window.opener.document.getElementById('{$src_element_id}')); ">
					</td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="src_element_id" value="{$src_element_id}">
		<input type="hidden" name="dst_element_id" value="{$dst_element_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}