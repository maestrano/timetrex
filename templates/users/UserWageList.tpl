{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="userwage" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="10">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.userwage);">
							{html_options options=$user_options selected=$user_id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.userwage);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>

				{if $user_has_default_wage == FALSE}
					<tr class="tblDataWarning">
						<td colspan="7" align="center">
							<br>
							<b>{t}This employee does not have a wage set for the default wage group, therefore they will not receive any regular time earnings{/t}.</b>
							<br>
							<br>
						</td>
					</tr>
				{/if}
				
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}Group{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="wage_group_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Wage{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="wage_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Effective Date{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="b.effective_date" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$wages item=wage}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $wage.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$wage.wage_group}
						</td>
						<td>
							{$wage.type}
						</td>
						<td>
							{$wage.currency_symbol}{$wage.wage}
						</td>
						<td>
							{$wage.effective_date}
						</td>
						<td>
							{assign var="wage_id" value=$wage.id}
							{if $permission->Check('wage','edit') OR ( $permission->Check('wage','edit_child') AND $wage.is_child === TRUE ) OR ( $permission->Check('wage','edit_own') AND $wage.is_owner === TRUE )}
								[ <a href="{urlbuilder script="EditUserWage.php" values="id=$wage_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$wage.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('wage','add') AND ( $permission->Check('wage','edit') OR ( $permission->Check('wage','edit_child') AND $wage.is_child === TRUE ) OR ( $permission->Check('wage','edit_own') AND $wage.is_owner === TRUE ) )}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('wage','delete') OR ( $permission->Check('wage','delete_child') AND $wage.is_child === TRUE ) OR ( $permission->Check('wage','delete_own') AND $wage.is_owner === TRUE )}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('wage','undelete')}
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
			<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
