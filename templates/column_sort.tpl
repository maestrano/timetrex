<!-- BEGIN COLUMN SORT -->
{if $sort_column == $current_column}
	  {if $current_order == 'asc' OR $current_order == 1}
			{assign var="asc_on" value="-on"}
	  {elseif $current_order == 'desc' OR $current_order == -1}
			{assign var="desc_on" value="-on"}
	  {/if}
{/if}
<table align="center" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td rowspan="2" class="tblHeader">
			{$label}
		</td>
		<td valign="bottom">
			<a href="{urlbuilder values="sort_column=$sort_column,sort_order=desc"}"><img src="{$IMAGES_URL}/arrow-up{$desc_on}.gif"></a>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<a href="{urlbuilder values="sort_column=$sort_column,sort_order=asc"}"><img src="{$IMAGES_URL}/arrow-down{$asc_on}.gif"></a>
		</td>
	</tr>
</table>
<!-- END COLUMN SORT -->