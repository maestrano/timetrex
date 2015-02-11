var APIUserMembership = ServiceCaller.extend( {

	key_name: 'UserMembership',
	className: 'APIUserMembership',

	getCommonUserMembershipData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserMembershipData', arguments );

	},

	getUserMembership: function() {

		return this.argumentsHandler( this.className, 'getUserMembership', arguments );

	},

	setUserMembership: function() {

		return this.argumentsHandler( this.className, 'setUserMembership', arguments );

	},

	getUserMembershipDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserMembershipDefaultData', arguments );

	},

	deleteUserMembership: function() {

		return this.argumentsHandler( this.className, 'deleteUserMembership', arguments );

	},

	validateUserMembership: function() {

		return this.argumentsHandler( this.className, 'validateUserMembership', arguments );

	},

	copyUserMembership: function() {

		return this.argumentsHandler( this.className, 'copyUserMembership', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	}



} );