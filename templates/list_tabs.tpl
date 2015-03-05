{if $section == 'header'}
	<tr>
		<td colspan="{$total_columns}" align="left">
			<ul id="tabmenu">
				<li><img style="vertical-align: top" id="tab_hide_img" src="{$IMAGES_URL}/nav_bottom_sm.gif" onClick="TIMETREX.searchForm.toggleTabBlock()"></li>
				<li><a id="tab_basic_search" href="javascript:TIMETREX.searchForm.showTab('basic_search'); {$tab_onclick}">{t}Basic Search{/t}</a></li>
				<li><a id="tab_adv_search" href="javascript:TIMETREX.searchForm.showTab('adv_search'); {$tab_onclick}">{t}Advanced Search{/t}</a></li>
				<li><a id="tab_saved_search" href="javascript:TIMETREX.searchForm.showTab('saved_search')">{t}Saved Search & Layout{/t}</a></li>
				{if is_array($filter_data.saved_search_options) AND count($filter_data.saved_search_options) > 0}
				<li>
				<select name="saved_search_id" onChange="this.form.submit()">
					{html_options options=$filter_data.saved_search_options selected=$saved_search_id}
				</select>
				</li>
				{/if}
			</ul>
		</td>
	</tr>
{elseif $section == 'saved_search'}
	<tr id="saved_search" class="tblSearch" style="display: none">
		<td colspan="{$total_columns}">
			<table class="editTable" bgcolor="#7a9bbd">
				<tr>
					<td class="cellLeftEditTableHeader">
						{t}Columns:{/t}
					</td>
					<td colspan="3">
						<table class="editTable">
							<tr class="tblHeader">
								<td>
									{t}Hide Columns{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Display Columns{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="50%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_column'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_column'))">
									<br>
									<select id="src_filter_column" style="width:200px;margin:5px 0 5px 0;" size="5" multiple>
										{html_options options=$filter_data.src_column_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveReportCriteriaItems('src_filter_column', 'filter_column', 5, true, 'value' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<a href="javascript:moveReportCriteriaItems('filter_column', 'src_filter_column', 5, true, 'value' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									<br>
									<br>
									<br>
									<a href="javascript:select_item_move_up(document.getElementById('filter_column') );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_up.gif"></a>
									<a href="javascript:select_item_move_down(document.getElementById('filter_column') );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_down.gif"></a>

								</td>
								<td class="cellRightEditTable" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_column'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_column'))">
									<br>
									<select name="filter_data[columns][]" id="filter_column" style="width:200px;margin:5px 0 5px 0;" size="5" multiple>
										{html_options options=$filter_data.selected_column_options selected=$filter_data.selected_column_options}
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="cellLeftEditTableHeader">
						{t}Sort By:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<select id="columns" name="filter_data[sort_column]">
							{html_options options=$filter_data.sort_options selected=$filter_data.sort_column}
						</select>
						<select id="columns" name="filter_data[sort_order]">
							{html_options options=$filter_data.sort_direction_options selected=$filter_data.sort_order}
						</select>
					</td>
				</tr>
				<tr>
					<td class="cellLeftEditTableHeader">
						{t}Save Search As:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="text" name="filter_data[saved_search_name]" value="">
						<input type="submit" name="action:save" value="{t}Save{/t}" onClick="selectAll( document.getElementById('filter_column') );">
						{if is_array($filter_data.saved_search_options) AND count($filter_data.saved_search_options) > 0}
						{t}Previous Saved Searches:{/t}
						<select name="filter_data[saved_search_id]">
							{html_options options=$filter_data.saved_search_options selected=$saved_search_id|default:$filter_data.saved_search_id}
						</select>
						<input type="submit" name="action:update" value="{t}Update{/t}" onClick="selectAll( document.getElementById('filter_column') );">
						<input type="submit" name="action:delete" value="{t}Delete{/t}">
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
{elseif $section == 'global'}
	<tr id="tab_global" style="display: none">
		<td colspan="{$total_columns}" class="tblHeader">
			<input type="submit" name="action:search" value="{t}Search{/t}" onClick="selectAll( document.getElementById('filter_column') );">
			<input type="submit" name="action:clear" value="{t}Clear{/t}" onClick="TIMETREX.searchForm.clearForm(); selectAll( document.getElementById('filter_column') );">
			<input type="hidden" name="filter_data[selected_tab]" id="selected_tab" value="{$filter_data.selected_tab}">
			<input type="hidden" name="form" value="search_form">
		</td>
	</tr>
{/if}