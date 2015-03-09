var APIDate = ServiceCaller.extend( {

	key_name: '',
	className: 'APITTDate',

	getHours: function() {
		return this.argumentsHandler( this.className, 'getHours', arguments );
//		  this.call(this.className,'getHours',onResult,onError,args,delegate);

	},

	parseTimeUnit: function() {
		return this.argumentsHandler( this.className, 'parseTimeUnit', arguments );

	},

	getTimeZoneOffset: function() {
		return this.argumentsHandler( this.className, 'getTimeZoneOffset', arguments );
//		  this.call(this.className,'getTimeZoneOffset',onResult,onError,args,delegate);

	},

	parseDateTime: function() {
		return this.argumentsHandler( this.className, 'parseDateTime', arguments );
	},

	getMonthOfYearArray: function() {
		return this.argumentsHandler( this.className, 'getMonthOfYearArray', arguments );
	},

	getDayOfMonthArray: function() {
		return this.argumentsHandler( this.className, 'getDayOfMonthArray', arguments );
	},

	getDayOfWeekArray: function() {
		return this.argumentsHandler( this.className, 'getDayOfWeekArray', arguments );
	},

	getAPIDate: function() {
		return this.argumentsHandler( this.className, 'getAPIDate', arguments );
	}

} );