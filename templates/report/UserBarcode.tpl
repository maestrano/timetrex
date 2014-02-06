{include file="header.tpl" body_onload="countAllReportCriteria();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user',
									'filter_column');

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}" target="_self">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable">

				<tr class="tblHeader">
					<td colspan="3">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>
				
				{capture assign=report_display_name}{t}Employee Status{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employee Statuses{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_status' display_name=$report_display_name display_plural_name=$report_display_plural_name}
				
				{capture assign=report_display_name}{t}Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}
				
				{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}
				
				{capture assign=report_display_name}{t}Default Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Columns{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Columns{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='column' order=TRUE display_name=$report_display_name display_plural_name=$report_display_plural_name}
				
				{htmlreportsort filter_data=$filter_data}
				
{*
				<tbody id="filter_user_status_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Employee Status:{/t}</b><a href="javascript:hideReportCriteria('filter_user_status');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Employee Statuses{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Employee Statuses{/t}
									</td>
								</tr>
								<tr>
									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user_status'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user_status'))">
										<br>
										<select id="src_filter_user_status" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_user_status_options}" multiple>
											{html_options options=$filter_data.src_user_status_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_user_status', 'filter_user_status', {select_size array=$filter_data.src_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_user_status', 'src_filter_user_status', {select_size array=$filter_data.src_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user_status'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user_status'))">
										<br>
										<select name="filter_data[user_status_ids][]" id="filter_user_status" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.select_user_status_options}" multiple>
											{html_options options=$filter_data.selected_user_status_options selected=$filter_data.selected_user_status_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_user_status_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Employee Status:{/t}</b><a href="javascript:showReportCriteria('filter_user_status')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_user_status_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tbody id="filter_group_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Group:{/t}</b><a href="javascript:hideReportCriteria('filter_group');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Groups{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Groups{/t}
									</td>
								</tr>
								<tr>
									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_group'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_group'))">
										<br>
										<select id="src_filter_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_group_options}" multiple>
											{html_options options=$filter_data.src_group_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_group', 'filter_group', {select_size array=$filter_data.src_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_group', 'src_filter_group', {select_size array=$filter_data.src_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_group'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_group'))">
										<br>
										<select name="filter_data[group_ids][]" id="filter_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.select_group_options}" multiple>
											{html_options options=$filter_data.selected_group_options selected=$filter_data.selected_group_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_group_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Group:{/t}</b><a href="javascript:showReportCriteria('filter_group')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_group_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tbody id="filter_branch_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Default Branch:{/t}</b><a href="javascript:hideReportCriteria('filter_branch');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Branches{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Branches{/t}
									</td>
								</tr>

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_branch'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_branch'))">
										<br>
										<select id="src_filter_branch" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_branch_options}" multiple>
											{html_options options=$filter_data.src_branch_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_branch', 'filter_branch', {select_size array=$filter_data.src_branch_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_branch', 'src_filter_branch', {select_size array=$filter_data.src_branch_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_branch'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_branch'))">
										<br>
										<select name="filter_data[branch_ids][]" id="filter_branch" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_branch_options}" multiple>
											{html_options options=$filter_data.selected_branch_options selected=$filter_data.selected_branch_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_branch_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Default Branch:{/t}</b><a href="javascript:showReportCriteria('filter_branch')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_branch_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>


				<tbody id="filter_department_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Default Department:{/t}</b><a href="javascript:hideReportCriteria('filter_department');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Departments{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Departments{/t}
									</td>
								</tr>

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_department'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_department'))">
										<br>
										<select id="src_filter_department" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_department_options}" multiple>
											{html_options options=$filter_data.src_department_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_department', 'filter_department', {select_size array=$filter_data.src_department_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_department', 'src_filter_department', {select_size array=$filter_data.src_department_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_department'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_department'))">
										<br>
										<select name="filter_data[department_ids][]" id="filter_department" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_department_options}" multiple>
											{html_options options=$filter_data.selected_department_options selected=$filter_data.selected_department_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_department_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Default Department:{/t}</b><a href="javascript:showReportCriteria('filter_department')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_department_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>


				<tbody id="filter_user_title_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Employee Title:{/t}</b><a href="javascript:hideReportCriteria('filter_user_title');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Titles{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Titles{/t}
									</td>
								</tr>

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user_title'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user_title'))">
										<br>
										<select id="src_filter_user_title" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_user_title_options}" multiple>
											{html_options options=$filter_data.src_user_title_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_user_title', 'filter_user_title', {select_size array=$filter_data.src_user_title_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_user_title', 'src_filter_user_title', {select_size array=$filter_data.src_user_title_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user_title'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user_title'))">
										<br>
										<select name="filter_data[user_title_ids][]" id="filter_user_title" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_user_title_options}" multiple>
											{html_options options=$filter_data.selected_user_title_options selected=$filter_data.selected_user_title_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_user_title_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Employee Title:{/t}</b><a href="javascript:showReportCriteria('filter_user_title')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_user_title_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tbody id="filter_include_user_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Include Employees:{/t}</b><a href="javascript:hideReportCriteria('filter_include_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
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

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_include_user'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_include_user'))">
										<br>
										<select id="src_filter_include_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_include_user_options}" multiple>
											{html_options options=$filter_data.src_include_user_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_include_user', 'filter_include_user', {select_size array=$filter_data.src_include_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_include_user', 'src_filter_include_user', {select_size array=$filter_data.src_include_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
										<br>
										<br>
										<br>
										<a href="javascript:UserSearch('src_filter_include_user','filter_include_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_include_user'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_include_user'))">
										<br>
										<select name="filter_data[include_user_ids][]" id="filter_include_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_include_user_options}" multiple>
											{html_options options=$filter_data.selected_include_user_options selected=$filter_data.selected_include_user_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_include_user_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Include Employees:{/t}</b><a href="javascript:showReportCriteria('filter_include_user')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_include_user_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tbody id="filter_exclude_user_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Exclude Employees:{/t}</b><a href="javascript:hideReportCriteria('filter_exclude_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
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

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_exclude_user'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_exclude_user'))">
										<br>
										<select id="src_filter_exclude_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_exclude_user_options}" multiple>
											{html_options options=$filter_data.src_exclude_user_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_exclude_user', 'filter_exclude_user', {select_size array=$filter_data.src_exclude_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_exclude_user', 'src_filter_exclude_user', {select_size array=$filter_data.src_exclude_user_options} );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
										<br>
										<br>
										<br>
										<a href="javascript:UserSearch('src_filter_exclude_user','filter_exclude_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_exclude_user'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_exclude_user'))">
										<br>
										<select name="filter_data[exclude_user_ids][]" id="filter_exclude_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_exclude_user_options}" multiple>
											{html_options options=$filter_data.selected_exclude_user_options selected=$filter_data.selected_exclude_user_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_exclude_user_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Exclude Employees:{/t}</b><a href="javascript:showReportCriteria('filter_exclude_user')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_exclude_user_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tbody id="filter_column_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Columns:{/t}</b><a href="javascript:hideReportCriteria('filter_column');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
								<tr class="tblHeader">
									<td>
										{t}UnSelected Columns{/t}
									</td>
									<td>
										<br>
									</td>
									<td>
										{t}Selected Columns{/t}
									</td>
								</tr>

									<td class="cellRightEditTable" width="50%" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_column'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_column'))">
										<br>
										<select id="src_filter_column" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.src_column_options}" multiple>
											{html_options options=$filter_data.src_column_options}
										</select>
									</td>
									<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
										<a href="javascript:moveReportCriteriaItems('src_filter_column', 'filter_column', {select_size array=$filter_data.src_column_options}, true, 'value' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
										<a href="javascript:moveReportCriteriaItems('filter_column', 'src_filter_column', {select_size array=$filter_data.src_column_options}, true, 'value' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
										<br>
										<br>
										<br>
										<a href="javascript:select_item_move_up(document.getElementById('filter_column') );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_up.gif"></a>
										<a href="javascript:select_item_move_down(document.getElementById('filter_column') );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_down.gif"></a>

									</td>
									<td class="cellRightEditTable" align="center">
										<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_column'))">
										<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_column'))">
										<br>
										<select name="filter_data[columns][]" id="filter_column" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_data.selected_column_options}" multiple>
											{html_options options=$filter_data.selected_column_options selected=$filter_data.selected_column_options}
										</select>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
				<tbody id="filter_column_off">
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}Columns:{/t}</b><a href="javascript:showReportCriteria('filter_column')"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTableHeader" colspan="3">
							<span id="filter_column_count">{t}N/A{/t}</span> {t}currently selected, click the arrow to modify.{/t}
						</td>
					</tr>
				</tbody>

				<tr onClick="showHelpEntry('sort')">
					<td class="{isvalid object="uf" label="type" value="cellLeftEditTableHeader"}">
						{t}Sort By:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<select id="columns" name="filter_data[primary_sort]">
							{html_options options=$filter_data.sort_options selected=$filter_data.primary_sort}
						</select>
						<select id="columns" name="filter_data[primary_sort_dir]">
							{html_options options=$filter_data.sort_direction_options selected=$filter_data.primary_sort_dir}
						</select>
						<b>{t}then:{/t}</b>
						<select id="columns" name="filter_data[secondary_sort]">
							{html_options options=$filter_data.sort_options selected=$filter_data.secondary_sort}
						</select>
						<select id="columns" name="filter_data[secondary_sort_dir]">
							{html_options options=$filter_data.sort_direction_options selected=$filter_data.secondary_sort_dir}
						</select>

					</td>
				</tr>
*}
				</table>
			</div>

			<div id="contentBoxFour">
			<input type="BUTTON" id="display_report" name="action" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); document.getElementById('action').name = 'action:Display Report'; this.form.submit();">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}