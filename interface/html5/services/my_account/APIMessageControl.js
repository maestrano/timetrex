var APIMessageControl = ServiceCaller.extend( {

	key_name: 'MessageControl',
	className: 'APIMessageControl',

	getMessageControlDefaultData: function() {

		return this.argumentsHandler( this.className, 'getMessageControlDefaultData', arguments );

	},

	getMessageControl: function() {

		return this.argumentsHandler( this.className, 'getMessageControl', arguments );

	},

	getMessage: function() {

		return this.argumentsHandler( this.className, 'getMessage', arguments );

	},

	getEmbeddedMessage: function() {

		return this.argumentsHandler( this.className, 'getEmbeddedMessage', arguments );

	},

	getCommonMessageControlData: function() {

		return this.argumentsHandler( this.className, 'getCommonMessageControlData', arguments );

	},

	validateMessageControl: function() {

		return this.argumentsHandler( this.className, 'validateMessageControl', arguments );

	},

	setMessageControl: function() {
		return this.argumentsHandler( this.className, 'setMessageControl', arguments );
	},

	deleteMessageControl: function() {
		return this.argumentsHandler( this.className, 'deleteMessageControl', arguments );
	},

	copyMessageControl: function() {
		return this.argumentsHandler( this.className, 'copyMessageControl', arguments );
	},

	getUser: function() {
		return this.argumentsHandler( this.className, 'getUser', arguments );
	},

	isNewMessage: function() {
		return this.argumentsHandler( this.className, 'isNewMessage', arguments );
	},

	markRecipientMessageAsRead: function() {
		return this.argumentsHandler( this.className, 'markRecipientMessageAsRead', arguments );
	}


} );