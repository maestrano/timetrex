var APIAccrualBalance = ServiceCaller.extend( {

	key_name: 'AccrualBalance',
	className: 'APIAccrualBalance',

	getAccrualBalance: function() {

		return this.argumentsHandler( this.className, 'getAccrualBalance', arguments );

	}


} );