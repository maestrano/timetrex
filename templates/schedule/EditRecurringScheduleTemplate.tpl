{include file="header.tpl" enable_ajax=TRUE body_onload="showAbsencePolicy();getRecurringScheduleTotalTime(-1);"}

<script	language=JavaScript>
{literal}

function showAbsencePolicy() {
		var elements = document.getElementsByTagName( 'select' );
		total_elements = elements.length;
		for( i=0; i < total_elements; i++ ) {
				if( elements[i].id && elements[i].id.indexOf( 'status_id' ) === 0 ) {
						tmp_element_id = elements[i].id.replace('status_id','absence');
						if ( document.getElementById( elements[i].id ).value == 10 ) {
								hideObject( tmp_element_id );
						} else {
								showObject( tmp_element_id );
						}
				}
		}

		return true;
}

var week_row = '';

var hwCallback = {
		getScheduleTotalTime: function(result) {
			if ( result != false ) {
				//alert('aWeek Row: '+ week_row);
				document.getElementById('total_time-'+week_row).innerHTML = result;
			}
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getRecurringScheduleTotalTime(this_week_row) {
		if ( document.getElementById('start_time-'+this_week_row) == null ) {
				return false;
		}

		start_time = document.getElementById('start_time-'+this_week_row).value;
		end_time = document.getElementById('end_time-'+this_week_row).value;
		schedule_policy_obj = document.getElementById('schedule_policy_id-'+this_week_row);
		schedule_policy_id = schedule_policy_obj[schedule_policy_obj.selectedIndex].value;


		if ( start_time != '' && end_time != '' ) {
			week_row = this_week_row;
			remoteHW.getScheduleTotalTime( start_time, end_time, schedule_policy_id );
		}
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$rstcf->Validator->isValid() OR !$rstf->Validator->isValid()}
					{include file="form_errors.tpl" object="rstcf,rstf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="rstcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="rstcf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[description]" value="{$data.description}">
					</td>
				</tr>

				<tr>
					<td colspan="2">
						<table class="tblList">
							<tr class="tblHeader">
								<td colspan="16">
									<b>{t}NOTE:{/t}</b> {t}To set different In/Out times for each day of the week, add additional weeks all with the same week number.{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Week{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td width="15">
									{t}S{/t}
								</td>
								<td width="15">
									{t}M{/t}
								</td>
								<td width="15">
									{t}T{/t}
								</td>
								<td width="15">
									{t}W{/t}
								</td>
								<td width="15">
									{t}T{/t}
								</td>
								<td width="15">
									{t}F{/t}
								</td>
								<td width="15">
									{t}S{/t}
								</td>
								<td>
									{t}Shift Time{/t}
								</td>
								<td>
									{t}Total{/t}
								</td>
								<td>
									{t}Schedule Policy{/t}
								</td>
								<td>
									{t}Branch{/t} / {t}Department{/t}
								</td>
								{if $permission->Check('job','enabled') }
								<td>
									{t}Job{/t} / {t}Task{/t}
								</td>
								{/if}
								{if $current_company->getProductEdition() > 10}
								<td>
									{t}Open Shift Multiplier{/t}
								</td>
								{/if}
								<td>
									<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
								</td>
							</tr>
							{foreach name="week" from=$week_rows item=week_row}
								{assign var="week_row_id" value=$week_row.id}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}

								<tr class="{$row_class}">
									<td id="{isvalid object="rstf" label="week$week_row_id" value="value"}">
										<input type="text" size="4" name="week_rows[{$week_row.id}][week]" value="{$week_row.week}">
										<input type="hidden" name="week_rows[{$week_row.id}][id]" value="{$week_row.id}">
										<input type="hidden" name="week_rows[{$week_row.id}][total_time]" value="{$week_row.total_time}">
									</td>
									<td>
										<select id="status_id-{$week_row.id}" name="week_rows[{$week_row.id}][status_id]" onChange="showAbsencePolicy();">
											{html_options options=$data.status_options selected=$week_row.status_id}
										</select>

										<span id="absence-{$week_row.id}" style="display:none">
												<br>
												<select id="absence_policy_id-{$week_row.id}" name="week_rows[{$week_row.id}][absence_policy_id]">
													{html_options options=$data.absence_policy_options  selected=$week_row.absence_policy_id}
												</select>
										</span>

									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][sun]" value="1" {if $week_row.sun == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][mon]" value="1" {if $week_row.mon == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][tue]" value="1" {if $week_row.tue == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][wed]" value="1" {if $week_row.wed == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][thu]" value="1" {if $week_row.thu == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][fri]" value="1" {if $week_row.fri == TRUE}checked{/if}>
									</td>
									<td width="15">
										<input type="checkbox" class="checkbox" name="week_rows[{$week_row.id}][sat]" value="1" {if $week_row.sat == TRUE}checked{/if}>
									</td>
									<td id="{isvalid object="rstf" label="start_time$week_row_id" value="value"}" align="center">
										<table>
												<tr>
														<td>
																<b>{t}In:{/t}</b>
														</td>
														<td>
																<input type="text" size="8" id="start_time-{$week_row.id}" name="week_rows[{$week_row.id}][start_time]" value="{getdate type="TIME" epoch=$week_row.start_time}" onChange="getRecurringScheduleTotalTime({$week_row.id});"><br>
														</td>
												</tr>
												<tr>
														<td>
																<b>{t}Out:{/t}</b>
														</td>
														<td>
																<input type="text" size="8" id="end_time-{$week_row.id}" name="week_rows[{$week_row.id}][end_time]" value="{getdate type="TIME" epoch=$week_row.end_time}" onChange="getRecurringScheduleTotalTime({$week_row.id});">
														</td>
												</tr>
										</table>
									</td>
									<td>
										<span id="total_time-{$week_row.id}">
											{gettimeunit value=$week_row.total_time default=true}
										</span>
									</td>
									<td>
										<select id="schedule_policy_id-{$week_row.id}" name="week_rows[{$week_row.id}][schedule_policy_id]" onChange="getRecurringScheduleTotalTime({$week_row.id});">
											{html_options options=$data.schedule_options selected=$week_row.schedule_policy_id}
										</select>
									</td>
									<td>
										<table>
												<tr>
														<td>
																<b>{t}Branch:{/t}</b>
														</td>
														<td>
																<select id="branch_id" name="week_rows[{$week_row.id}][branch_id]">
																	{html_options options=$data.branch_options selected=$week_row.branch_id}
																</select>
														</td>
												</tr>
												<tr>
														<td>
																<b>{t}Department:{/t}</b>
														</td>
														<td>
																<select id="branch_id" name="week_rows[{$week_row.id}][department_id]">
																	{html_options options=$data.department_options selected=$week_row.department_id}
																</select>
														</td>
												</tr>
										</table>
									</td>
									{if $permission->Check('job','enabled') }
									<td>
										<table>
												<tr>
														<td>
																<b>{t}Job:{/t}</b>
														</td>
														<td>
																<select id="job_id-{$week_row.id}" name="week_rows[{$week_row.id}][job_id]">
																	{html_options options=$data.job_options selected=$week_row.job_id}
																</select>
														</td>
												</tr>
												<tr>
														<td>
																<b>{t}Task:{/t}</b>
														</td>
														<td>
																<select id="job_item_id-{$week_row.id}" name="week_rows[{$week_row.id}][job_item_id]">
																	{html_options options=$data.job_item_options selected=$week_row.job_item_id}
																</select>
														</td>
												</tr>
										</table>
									</td>
									{/if}
									{if $current_company->getProductEdition() > 10}
									<td>
										<input type="text" size="2" id="open_shift_multiplier-{$week_row.id}" name="week_rows[{$week_row.id}][open_shift_multiplier]" value="{$week_row.open_shift_multiplier}">
									</td>
									{/if}
									<td>
										<input type="checkbox" class="checkbox" name="ids[]" value="{$week_row.id}">
									</td>
								</tr>
							{/foreach}

							<tr>
								<td class="tblActionRow" colspan="16">
									<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
									<input type="submit" class="btnSubmit" name="action:add_week" value="{t}Add Week{/t}">
									<input type="submit" class="btnSubmit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
								</td>
							</tr>
						</table>
					</td>
				</tr>

			</table>
		</div>
		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
