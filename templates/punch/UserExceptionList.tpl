{include file="header.tpl" body_onload="TIMETREX.searchForm.onLoadShowTab();"}

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
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Employee Status:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="filter_data_status_id" name="filter_data[status_id]">
													{html_options options=$filter_data.status_options selected=$filter_data.status_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Pay Period:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[pay_period_id]">
													{html_options options=$filter_data.pay_period_options selected=$filter_data.pay_period_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Employee:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[user_id]">
													{html_options options=$filter_data.user_options selected=$filter_data.user_id}
												</select>
											</td>
										</tr>
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
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Show Pre-Mature:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="checkbox" class="checkbox" name="filter_data[pre_mature]" value="1" {if $filter_data.pre_mature == 1}checked{/if}>
											</td>
										</tr>
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
							<td {if $key == 'severity'}bgcolor="{$row.exception_background_color}"{/if}>
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
							{assign var="date_stamp" value=$row.date_stamp_epoch}
							{if $permission->Check('punch','view') OR $permission->Check('punch','view_own')}
								[ <a href="{urlbuilder script="../timesheet/ViewUserTimeSheet.php" values="filter_data[user_id]=$user_id,filter_data[date]=$date_stamp" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$request.id}">
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td class="tblHeader" colspan="{$total_columns}">
							{t}No Exceptions Found{/t}
						</td>
					</tr>
				{/foreach}
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
