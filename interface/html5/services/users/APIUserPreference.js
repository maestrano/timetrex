var APIUserPreference = ServiceCaller.extend( {


	key_name: 'UserPreference',
	className: 'APIUserPreference',

	getUserPreferenceDefaultData: function() {
		return this.argumentsHandler( this.className, 'getUserPreferenceDefaultData', arguments );
//		  this.call(this.get('className'),'getUserPreferenceDefaultData',onResult,onError,args,delegate);

	},

	getUserPreference: function() {
		return this.argumentsHandler( this.className, 'getUserPreference', arguments );

	},

	getCommonUserPreferenceData: function() {
		return this.argumentsHandler( this.className, 'getCommonUserPreferenceData', arguments );

	},

	validateUserPreference: function() {
		return this.argumentsHandler( this.className, 'validateUserPreference', arguments );

	},

	setUserPreference: function() {
		return this.argumentsHandler( this.className, 'setUserPreference', arguments );

	},

	deleteUserPreference: function() {
		return this.argumentsHandler( this.className, 'deleteUserPreference', arguments );

	},

	copyUserPreference: function() {
		return this.argumentsHandler( this.className, 'copyUserPreference', arguments );

	},

	getScheduleIcalendarURL: function() {
		return this.argumentsHandler( this.className, 'getScheduleIcalendarURL', arguments );

	},

} );