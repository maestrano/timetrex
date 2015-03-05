var APICompanyGenericTag = ServiceCaller.extend( {

	key_name: 'CompanyGenericTag',
	className: 'APICompanyGenericTag',

	getCompanyGenericTag: function() {

		return this.argumentsHandler( this.className, 'getCompanyGenericTag', arguments );

	},

} );