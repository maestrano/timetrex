{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="userdeduction" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="10">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.userdeduction);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.userdeduction);">
							{html_options options=$user_options selected=$user_id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.userdeduction);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Calculation{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="debit_account" current_column="$sort_column" current_order="$sort_order"}
					</td>

					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$rows item=row name=deduction}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE OR $row.status_id != 10}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.deduction.iteration}
						</td>
						<td>
							{$row.type}
						</td>
						<td>
							{$row.name}
						</td>
						<td>
							{$row.calculation}
						</td>
						<td>
							{assign var="row_id" value=$row.id}
							{if $permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row.is_owner === TRUE )}
								[ <a href="{urlbuilder script="EditUserDeduction.php" values="id=$row_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">

						{if $permission->Check('user_tax_deduction','add') AND ( $permission->Check('user_tax_deduction','edit') OR ( $permission->Check('user_tax_deduction','edit_child') AND $row.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','edit_own') AND $row.is_owner === TRUE ) )}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('user_tax_deduction','delete') OR ( $permission->Check('user_tax_deduction','delete_child') AND $row.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','delete_own') AND $row.is_owner === TRUE )}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('user_tax_deduction','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
