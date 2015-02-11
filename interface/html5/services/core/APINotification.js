var APINotification = ServiceCaller.extend( {

	key_name: 'Notification',
	className: 'APINotification',

	getNotificationDefaultData: function() {

		return this.argumentsHandler( this.className, 'getNotificationDefaultData', arguments );

	},

	getNotification: function() {

		return this.argumentsHandler( this.className, 'getNotifications', arguments );

	},

	getCommonNotificationData: function() {

		return this.argumentsHandler( this.className, 'getCommonNotificationData', arguments );

	},

	validateNotification: function() {

		return this.argumentsHandler( this.className, 'validateNotification', arguments );

	},

	setNotification: function() {

		return this.argumentsHandler( this.className, 'setNotification', arguments );

	},

	deleteNotification: function() {

		return this.argumentsHandler( this.className, 'deleteNotification', arguments );

	}


} );