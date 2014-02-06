{include file="header.tpl" body_onload="filterUserCount(); uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user'));"}

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

		<form method="post" name="edit_permission" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pcf->Validator->isValid()}
					{include file="form_errors.tpl" object="pcf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="pcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="pcf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[description]" value="{$data.description}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('level')">
					<td class="{isvalid object="pcf" label="level" value="cellLeftEditTable"}">
						{t}Level:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[level]">
							{html_options options=$data.level_options selected=$data.level}
						</select>
						{t}(Higher levels can only assign employees to lower levels){/t}
					</td>
				</tr>

				<tr>
					<td colspan="2">
						<table class="tblList">
							<tr class="tblHeader">
								<td colspan="6">
									{t}Permission Presets:{/t}
										<select id="preset" name="data[preset]">
											{html_options options=$preset_options}
										</select>
										<input type="checkbox" name="data[preset_flags][]" value="10" checked> {t}Scheduling{/t}
										<input type="checkbox" name="data[preset_flags][]" value="20" checked> {t}Time & Attendance{/t}
										<input type="checkbox" name="data[preset_flags][]" value="30" checked> {t}Payroll{/t}
										{if $product_edition == 20}
										<input type="checkbox" name="data[preset_flags][]" value="40" checked> {t}Job Tracking{/t}
										<input type="checkbox" name="data[preset_flags][]" value="60" checked> {t}Invoicing{/t}
										<input type="checkbox" name="data[preset_flags][]" value="50" checked> {t}Documents{/t}
										{/if}
									<input type="submit" class="button" name="action:Apply_Preset" value="{t}Apply Preset{/t}" onClick="selectAll(document.getElementById('filter_user')); return singleSubmitHandler(this)">
								</td>
							</tr>

							<tr class="tblHeader">
								<td colspan="6">
									{t}Display Permissions:{/t}
										<select id="group" name="group_id" onChange="submitModifiedForm('group', '', document.edit_permission);">
											{html_options options=$section_group_options selected=$group_id}
										</select>
										<input type="hidden" id="old_group" value="{$group_id}">
								</td>
							</tr>

							{foreach name="toc" from=$permission_data item=section}
								{if $ignore_permissions[$section.name] != 'ALL'}
									{if $smarty.foreach.toc.first}
										<tr class="tblHeader">
											<td colspan="6">
												<a name="top">{t}Table of Contents{/t}</a>
											</td>
										</tr>

										{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
										<tr class="{$row_class}">
									{/if}

										<td colspan="2">
											<a href="#{$section.name}">{$section.display_name}</a>
										</td>

									{if $smarty.foreach.toc.iteration % 3 == 0}
										{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
										</tr>
										<tr class="{$row_class}">
									{/if}

									{if $smarty.foreach.toc.last}
										<td colspan="2">
											<a href="#employees">{t}Employee List{/t}</a>
										</td>
										{if ($smarty.foreach.toc.total+1) % 3 != 0}
											<td colspan="6">
												<br>
											</td>
										{/if}

										</tr>
									{/if}
								{/if}
								{if $smarty.foreach.toc.last}
									<tr>
										<td colspan="6">
											<br>
										</td>
									</tr>
								{/if}
							{/foreach}

							{foreach from=$permission_data item=section}
								{if $ignore_permissions[$section.name] != 'ALL'}
								<tr class="tblHeader">
									<td>
										[ <a href="#top">{t}Top{/t}</a> ] [ <a href="#employees">{t}Bottom{/t}</a> ]
									</td>
									<td colspan="2">
										<a name="{$section.name}">{$section.display_name}</a>
									</td>
									<td>
										{t}Allow{/t}
									</td>
									<td>
										{t}Deny{/t}
									</td>
								</tr>
								{foreach from=$section.permissions item=perm}

									{if !isset($ignore_permissions[$section.name])
										OR
										(
											(
											isset($ignore_permissions[$section.name])
											AND is_array($ignore_permissions[$section.name])
											AND in_array($perm.name, $ignore_permissions[$section.name])
											AND $permission->Check('company','edit')
											)
											OR
											(
											isset($ignore_permissions[$section.name])
											AND is_array($ignore_permissions[$section.name])
											AND !in_array($perm.name, $ignore_permissions[$section.name])
											)
										)}

									{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
									<tr class="{$row_class}">
										<td colspan="3" class="cellLeftBlueEditTable">
											{$perm.display_name}: {$ignore_permissions[$section.name][$perm.name]}
										</td>
										<td>
											<input type="radio" name="data[permissions][{$section.name}][{$perm.name}]" value="1" {if ( $perm.result === TRUE)}checked{/if}>
										</td>
										<td>
											<input type="radio" name="data[permissions][{$section.name}][{$perm.name}]" value="0" {if ( $perm.result !== TRUE)}checked{/if}>
										</td>
										<input type="hidden" name="old_data[permissions][{$section.name}][{$perm.name}]" value="{if ( $perm.result === TRUE)}1{else}0{/if}">
									</tr>
									{/if}
								{/foreach}
								{/if}
							{/foreach}
						</table>
						<a name="employees"></a>
					</td>
				</tr>

				<tbody id="filter_employees_on" style="display:none" >
				<tr>
					<td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
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
								<select name="src_user_id[]" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="data[user_ids][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
									{html_options options=$filter_user_options selected=$data.user_options}
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
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('src_filter_user')); selectAll(document.getElementById('filter_user')); return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="id" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
