<!-- BEGIN PAGER -->
{assign var="previous_page" value=$paging_data.previous_page}
{assign var="next_page" value=$paging_data.next_page}
{assign var="last_page_number" value=$paging_data.last_page_number}
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="125" align="center">
			<nobr>[ <a href="#top">{t}Top{/t}</a> | <a href="#bottom">{t}Bottom{/t}</a> ]</nobr>
		</td>
		<td align="left">
			{if $paging_data.is_first_page
					OR $paging_data.last_page_number == 0
					OR $paging_data.current_page == -1}

				<span style="color: 336699">
			   <img src="{$IMAGES_URL}/paging_first_off.gif"> {t}Start{/t}
			   &nbsp;<img src="{$IMAGES_URL}/paging_prev_off.gif"> {t}Previous{/t}
			   </span>
			{else}
			   <a href="{urlbuilder script=$smarty.server.SCRIPT_NAME values="page=1"}" class="pagingLink"><img src="{$IMAGES_URL}/paging_first.gif"> {t}Start{/t}</a>
			   &nbsp;<a href="{urlbuilder script=$smarty.server.SCRIPT_NAME values="page=$previous_page"}" class="pagingLink"><img src="{$IMAGES_URL}/paging_prev.gif"> {t}Previous{/t}</a>
			{/if}
		</td>
		<td width="10"><br></td>
		<td>
			{*Page:*}
			[
			<span class="textPaging">
				{if $paging_data.current_page > 6 }
					...
				{/if}

				{section name="pages" start=1 loop=$last_page_number+1}
					{assign var="page_number" value=$smarty.section.pages.index}
					{if $smarty.section.pages.index == $paging_data.current_page}
						<b>{$smarty.section.pages.index}</b>
					{else}
						{if $smarty.section.pages.index-$paging_data.current_page < 6 AND $smarty.section.pages.index > $paging_data.current_page-6 }
								<a href="{urlbuilder script=$smarty.server.SCRIPT_NAME values="page=$page_number"}" class="pagingLink">{$smarty.section.pages.index}</a>
						{/if}
					{/if}
				{sectionelse}
					<b>1</b>
				{/section}

				{if $last_page_number-$paging_data.current_page > 5}
					...
				{/if}
			</span>
			]
		</td>
		<td width="10"><br></td>
		<td align="right">
			{if $paging_data.is_last_page
				OR $paging_data.last_page_number == 0
				OR $paging_data.current_page == -1}
				<span style="color: 336699">
			   {t}Next{/t} <img src="{$IMAGES_URL}/paging_next_off.gif">
			   &nbsp;{t}End{/t} <img src="{$IMAGES_URL}/paging_last_off.gif">
			   </span>
			{else}
			   <a href="{urlbuilder script=$smarty.server.SCRIPT_NAME values="page=$next_page"}" class="pagingLink">{t}Next{/t} <img src="{$IMAGES_URL}/paging_next.gif"></a>
			   &nbsp;<a href="{urlbuilder script=$smarty.server.SCRIPT_NAME values="page=$last_page_number"}" class="pagingLink">{t}End{/t} <img src="{$IMAGES_URL}/paging_last.gif"></a>
			{/if}
		</td>
	</tr>
</table>
<!-- END PAGER -->
