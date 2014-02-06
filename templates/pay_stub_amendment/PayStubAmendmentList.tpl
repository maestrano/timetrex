{include file="header.tpl" body_onload="TIMETREX.searchForm.onLoadShowTab();"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
			<form method="get" name="search_form" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				{include file="list_tabs.tpl" section="header"}
				<tr id="adv_search" class="tblSearch" style="display: none">
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
												{t}Recurring PS Amendment:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[recurring_ps_amendment_id]">
													{html_options options=$filter_data.recurring_ps_amendment_options selected=$filter_data.recurring_ps_amendment_id}
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
			<form method="get" name="ps_amendment" action="{$smarty.server.SCRIPT_NAME}">
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
				{foreach name=pay_stub_amendment from=$pay_stub_amendments item=pay_stub_amendment}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $pay_stub_amendment.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.pay_stub_amendment.iteration}
						</td>

						{foreach from=$columns key=key item=column name=column}
							<td>
								{$pay_stub_amendment[$key]|default:"--"}
							</td>
						{/foreach}

						<td>
							{assign var="pay_stub_amendment_id" value=$pay_stub_amendment.id}
							{if $permission->Check('pay_stub_amendment','edit') OR $permission->Check('pay_stub_amendment','edit_own')}
								[ <a href="{urlbuilder script="EditPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$pay_stub_amendment.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						{if $permission->Check('pay_stub_amendment','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('pay_stub_amendment','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('pay_stub_amendment','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view')}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						<div align="left">
						<select name="export_type">
							{html_options options=$export_type_options}
						</select>
						<input type="submit" name="action:export" value="{t}Export{/t}">
						</div>
					</td>
				</tr>
				{/if}

			<tr>
				<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
