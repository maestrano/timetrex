{include file="header.tpl" body_onload="TIMETREX.searchForm.onLoadShowTab();"}
<script	language=JavaScript>

{literal}
function editPunch(punchID,punchControlId,userID,date,statusID) {
	try {
		eP=window.open('{/literal}{$BASE_URL}{literal}punch/EditPunch.php?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=600,height=470,resizable=1");
		if (window.focus) {
			eP.focus()
		}
	} catch (e) {
		//DN
	}
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
			<tr>
				<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>

			{if $permission->Check('punch','view') OR $permission->Check('punch','view_child')}
			<form method="get" name="search_form" action="{$smarty.server.SCRIPT_NAME}">
				{include file="list_tabs.tpl" section="header"}
				<tr id="adv_search" class="tblSearch" style="display: none;">
					<td colspan="{$total_columns}" class="tblSearchMainRow">
						<table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
							<tr>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Status:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[status_id]">
													{html_options options=$filter_data.schedule_status_options selected=$filter_data.status_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Pay Period:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[pay_period_ids]">
													{html_options options=$filter_data.pay_period_options selected=$filter_data.pay_period_ids}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Employee:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[id]">
													{html_options options=$filter_data.user_options selected=$filter_data.id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Employee Status:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="filter_data_status_id" name="filter_data[user_status_id]">
													{html_options options=$filter_data.status_options selected=$filter_data.user_status_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Title:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[title_id]">
													{html_options options=$filter_data.title_options selected=$filter_data.title_id}
												</select>
											</td>
										</tr>

{*
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Severity:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[severity_id]">
													{html_options options=$filter_data.severity_options selected=$filter_data.severity_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Exception:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[exception_policy_type_id]">
													{html_options options=$filter_data.type_options selected=$filter_data.exception_policy_type_id}
												</select>
											</td>
										</tr>
*}
									</table>
								</td>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Group:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[group_id]">
													{html_options options=$filter_data.group_options selected=$filter_data.group_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Default Branch:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[default_branch_id]">
													{html_options options=$filter_data.branch_options selected=$filter_data.default_branch_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Default Department:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[default_department_id]">
													{html_options options=$filter_data.department_options selected=$filter_data.default_department_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Schedule Policy:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[schedule_policy_id]">
													{html_options options=$filter_data.schedule_policy_options selected=$filter_data.schedule_policy_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Schedule Branch:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[schedule_branch_id]">
													{html_options options=$filter_data.branch_options selected=$filter_data.schedule_branch_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Schedule Department:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[schedule_department_id]">
													{html_options options=$filter_data.department_options selected=$filter_data.schedule_department_id}
												</select>
											</td>
										</tr>
{*
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Show Pre-Mature:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="checkbox" class="checkbox" name="filter_data[pre_mature]" value="1" {if $filter_data.pre_mature == 1}checked{/if}>
											</td>
										</tr>
*}
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				{include file="list_tabs.tpl" section="saved_search"}
				{include file="list_tabs.tpl" section="global"}
			</form>
			{/if}

			<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>

					{foreach from=$columns key=column_id item=column name=column}
						<td>
							{include file="column_sort.tpl" label=$column sort_column=$column_id current_column="$sort_column" current_order="$sort_order"}
						</td>
					{/foreach}

					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$rows item=row name=row}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE OR $row.type_id == 5}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.row.iteration}
						</td>

						{foreach from=$columns key=key item=column name=column}
							<td id="{if $key == 'severity' AND $row.severity_id == 20}yellow{elseif $key == 'severity' AND $row.severity_id == 30}error{/if}">
								{if $key == 'exception_policy_type_id'}
									<font color="{$row.exception_color}">
										<b>{$row[$key]|default:"--"}</b>
									</font>
								{else}
									{if $key == 'severity'}<b>{/if}{$row[$key]|default:"--"}{if $key == 'severity'}</b>{/if}
								{/if}
							</td>
						{/foreach}
						<td>
							{assign var="row_id" value=$row.id}
							{assign var="user_id" value=$row.user_id}
							{assign var="start_time" value=$row.start_time}
							{if $permission->Check('schedule','view') OR ( $permission->Check('schedule','view_child') AND $row.is_child === TRUE ) OR ( $permission->Check('schedule','view_own') AND $row.is_owner === TRUE ) }
								[ <a href="{urlbuilder script="../schedule/ViewSchedule.php" values="filter_data[include_user_ids][]=$user_id,filter_data[start_date]=$start_time" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
							{if $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $row.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $row.is_owner === TRUE ) }
								[ <a href="javascript:TIMETREX.schedule.editSchedule({$row_id})">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td class="tblHeader" colspan="{$total_columns}">
							{t}No Scheduled Shifts Found{/t}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						{if $permission->Check('punch','delete') OR $permission->Check('punch','delete_own') OR $permission->Check('punch','delete_child')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
