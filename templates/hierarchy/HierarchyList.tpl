{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{t}Name{/t}
					</td>
					<td>
						{t}Shared{/t}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$users item=user}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}">
						<td align="left">
							{$user.spacing} ({$user.level}) {$user.name}
						</td>
						<td>
							{if $user.shared == TRUE}{t}Yes{/t}{else}{t}No{/t}{/if}
						</td>
						<td>
							{assign var="id" value=$user.id}
							{assign var="hierarchy_id" value=$hierarchy_id}
							{if ($user.id > 0)}
								{if $permission->Check('hierarchy','view')}
									[ <a href="{urlbuilder script="ViewHierarchy.php" values="hierarchy_id=$hierarchy_id,id=$id" merge="FALSE"}">{t}View{/t}</a> ]
								{/if}
								{if $permission->Check('hierarchy','edit')}
									[ <a href="{urlbuilder script="EditHierarchy.php" values="hierarchy_id=$hierarchy_id,id=$id" merge="FALSE"}">{t}Edit{/t}</a> ]
								{/if}
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$user.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('hierarchy','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('hierarchy','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('hierarchy','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

			<input type="hidden" name="hierarchy_id" value="{$hierarchy_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
