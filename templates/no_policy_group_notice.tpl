		{if $show_no_policy_group_notice == TRUE}
			<tr class="tblDataWarning">
				<td colspan="7" align="center">
					<br>
					<b>{t}Policies highlighted in yellow may not be active yet because they are not assigned to a{/t} <a href="{$BASE_URL}/policy/PolicyGroupList.php">{t}Policy Group{/t}</a>. </b>
					<br>
					<br>
				</td>
			</tr>
		{/if}