{include file="header.tpl" body_onload="TIMETREX.searchForm.onLoadShowTab();"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
				<tr>
					<td class="tblPagingLeft" colspan="8" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

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
													{t}Template:{/t}
												</td>
												<td class="cellRightEditTable">
													<select name="filter_data[template_id]">
														{html_options options=$filter_data.template_options selected=$filter_data.template_id}
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
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					{include file="list_tabs.tpl" section="saved_search"}
					{include file="list_tabs.tpl" section="global"}
				</form>

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
				{foreach from=$rows item=row name=schedule}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.schedule.iteration}
						</td>

						{foreach from=$columns key=key item=column name=column}
							<td>
								{if $key == 'start_date' }
								  {getdate type="DATE" epoch=$row.start_date}
								{elseif $key == 'end_date' }
								  {if $row.end_date == NULL}
									  {t}Never{/t}
								  {else}
									  {getdate type="DATE" epoch=$row.end_date}
								  {/if}
								{else}
								  {$row[$key]|default:"--"}
								{/if}
							</td>
						{/foreach}
						<td>
							{assign var="row_id" value=$row.id}
							{if $permission->Check('recurring_schedule','edit') OR ( $permission->Check('recurring_schedule','edit_child') AND $row.is_child === TRUE ) OR ( $permission->Check('recurring_schedule','edit_own') AND $row.is_owner === TRUE )}
								[ <a href="{urlbuilder script="EditRecurringSchedule.php" values="id=$row_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[{$row.id}][]" value="{$row.user_id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						{if $permission->Check('recurring_schedule','add')}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('recurring_schedule','delete') OR $permission->Check('recurring_schedule','delete_own') OR $permission->Check('recurring_schedule','delete_child')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('recurring_schedule','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="8" align="right">
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