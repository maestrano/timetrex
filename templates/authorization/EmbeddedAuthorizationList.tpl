{foreach name="history" from=$authorization_data item=authorization}
	{if $smarty.foreach.history.first}
	<table class="tblList" style="width: 75%;" align="center">
		<tr class="tblHeader">
			<td colspan="3">
				{t}Authorization History{/t}
			</td>
		</tr>

		<tr class="tblHeader">
			<td>
				{t}Name{/t}
			</td>
			<td>
				{t}Authorized{/t}
			</td>
			<td>
				{t}Date{/t}
			</td>
		</tr>
	{/if}

	{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
		<tr class="{$row_class}">
			<td nowrap>
				{$authorization.created_by_full_name}
			</td>
			<td>
				{if $authorization.authorized === TRUE}{t}Yes{/t}{elseif $authorization.authorized === NULL}Pending{else}{t}No{/t}{/if}
			</td>
			<td nowrap>
				{getdate type="DATE+TIME" epoch=$authorization.created_date default=TRUE}
			</td>
		</tr>

	{if $smarty.foreach.history.last}
	</table>
	{/if}
{/foreach}

