var APIStation = ServiceCaller.extend( {

	key_name: 'Station',
	className: 'APIStation',

	getCurrentStation: function() {

		return this.argumentsHandler( this.className, 'getCurrentStation', arguments );

	},

	getStation: function() {

		return this.argumentsHandler( this.className, 'getStation', arguments );

	},

	getStationDefaultData: function() {

		return this.argumentsHandler( this.className, 'getStationDefaultData', arguments );

	},

	getCommonStationData: function() {

		return this.argumentsHandler( this.className, 'getCommonStationData', arguments );

	},

	validateStation: function() {

		return this.argumentsHandler( this.className, 'validateStation', arguments );

	},

	setStation: function() {

		return this.argumentsHandler( this.className, 'setStation', arguments );

	},

	deleteStation: function() {

		return this.argumentsHandler( this.className, 'deleteStation', arguments );

	},

	copyStation: function() {

		return this.argumentsHandler( this.className, 'copyStation', arguments );

	},

	runManualCommand: function() {
		return this.argumentsHandler( this.className, 'runManualCommand', arguments );
	}

} );