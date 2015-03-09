var APIRecurringScheduleTemplate = ServiceCaller.extend( {

	key_name: 'RecurringScheduleTemplate',
	className: 'APIRecurringScheduleTemplate',

	getRecurringScheduleTemplateDefaultData: function() {

		return this.argumentsHandler( this.className, 'getRecurringScheduleTemplateDefaultData', arguments );

	},

	getRecurringScheduleTemplate: function() {

		return this.argumentsHandler( this.className, 'getRecurringScheduleTemplate', arguments );

	},

	getCommonRecurringScheduleTemplateData: function() {

		return this.argumentsHandler( this.className, 'getCommonRecurringScheduleTemplateData', arguments );

	},

	validateRecurringScheduleTemplate: function() {

		return this.argumentsHandler( this.className, 'validateRecurringScheduleTemplate', arguments );

	},

	setRecurringScheduleTemplate: function() {

		return this.argumentsHandler( this.className, 'setRecurringScheduleTemplate', arguments );

	},

	deleteRecurringScheduleTemplate: function() {

		return this.argumentsHandler( this.className, 'deleteRecurringScheduleTemplate', arguments );

	},

	copyRecurringScheduleTemplate: function() {
		return this.argumentsHandler( this.className, 'copyRecurringScheduleTemplate', arguments );
	}


} );