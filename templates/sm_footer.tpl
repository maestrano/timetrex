<!-- Begin Footer -->
<a name="bottom">
<br>
<div>
	{php}
		Debug::writeToLog();
		Debug::Display();
		if (Debug::getEnableDisplay() == TRUE AND Debug::getVerbosity() >= 10) {
			{/php}
			{$profiler->printTimers(TRUE)}
			{php}
		}
	{/php}
</div>

</div>
{if $config_vars.debug.production == 1 AND $config_vars.other.disable_google_analytics != 1}<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript">_uacct="UA-333702-3"; __utmSetVar('Company: {if is_object($current_company)}{$current_company->getName()|escape}{else}N/A{/if}'); __utmSetVar('Edition: {php}echo getTTProductEditionName(){/php}'); __utmSetVar('Key: {$system_settings.registration_key}'); __utmSetVar('Host: {$smarty.server.HTTP_HOST}'); __utmSetVar('Version: {$system_settings.system_version}'); urchinTracker();</script><img src="{$IMAGES_URL}spacer.gif">{/if}
</body>
</html>