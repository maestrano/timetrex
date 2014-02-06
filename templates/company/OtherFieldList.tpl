{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="8" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Other ID1{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="other_id1" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Other ID2{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="other_id2" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Other ID3{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="other_id3" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Other ID4{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="other_id4" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Other ID5{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="other_id5" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$rows item=row}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$row.type}
						</td>
						<td>
							{$row.other_id1|default:"--"}
						</td>
						<td>
							{$row.other_id2|default:"--"}
						</td>
						<td>
							{$row.other_id3|default:"--"}
						</td>
						<td>
							{$row.other_id4|default:"--"}
						</td>
						<td>
							{$row.other_id5|default:"--"}
						</td>

						<td>
							{assign var="id" value=$row.id}
							{if $permission->Check('other_field','edit')}
								[ <a href="{urlbuilder script="EditOtherField.php" values="id=$id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="8">
						{if $permission->Check('other_field','add')}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('other_field','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('other_field','undelete')}
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
			<input type="hidden" name="type_id" value="{$type_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
