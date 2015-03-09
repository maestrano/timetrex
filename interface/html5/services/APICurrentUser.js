var APICurrentUser = ServiceCaller.extend( {


	key_name: '',
	className: 'APIAuthentication',

	getCurrentUser: function() {
		return this.argumentsHandler( this.className, 'getCurrentUser', arguments );
//		  this.call(this.className,'getCurrentUser',onResult,onError,args,delegate);

	},

	getCurrentUserPreference: function() {
		return this.argumentsHandler( this.className, 'getCurrentUserPreference', arguments );
//		  this.call(this.className,'getCurrentUserPreference',onResult,onError,args,delegate);

	},

	Logout: function() {
		return this.argumentsHandler( this.className, 'Logout', arguments );
//		  this.call(this.className,'Logout',onResult,onError,args,delegate);

	}





} );