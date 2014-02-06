{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
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
						{include file="column_sort.tpl" label=$label sort_column="calculation_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Calculation Order{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="calculation_order" current_column="$sort_column" current_order="$sort_order"}
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
							{$row.calculation_order}
						</td>
						<td>
							{assign var="row_id" value=$row.id}
							{if $permission->Check('company_tax_deduction','edit')}
								[ <a href="{urlbuilder script="EditCompanyDeduction.php" values="id=$row_id" merge="FALSE"}">{t}Edit{/t}</a> ]
								[ <a href="{urlbuilder script="../users/EditUserDeduction.php" values="company_deduction_id=$row_id" merge="FALSE"}">{t}Employee Settings{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('company_tax_deduction','add')}
							<input type="submit" class="button" name="action:Add_Presets" value="{t}Add Presets{/t}" onClick="return confirmSubmit('{t}Are you sure you want to add all presets based on your company location?{/t}')">
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('company_tax_deduction','add')}
							<input type="submit" class="button" name="action:copy" value="{t}Copy{/t}">
						{/if}
						{if $permission->Check('company_tax_deduction','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('company_tax_deduction','undelete')}
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
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
