var APIBranch = ServiceCaller.extend( {

	key_name: 'Branch',
	className: 'APIBranch',

	getBranch: function() {

		return this.argumentsHandler( this.className, 'getBranch', arguments );

	},

	getBranchDefaultData: function() {

		return this.argumentsHandler( this.className, 'getBranchDefaultData', arguments );

	},

	getCommonBranchData: function() {

		return this.argumentsHandler( this.className, 'getCommonBranchData', arguments );

	},

	validateBranch: function() {

		return this.argumentsHandler( this.className, 'validateBranch', arguments );

	},

	setBranch: function() {

		return this.argumentsHandler( this.className, 'setBranch', arguments );

	},

	deleteBranch: function() {

		return this.argumentsHandler( this.className, 'deleteBranch', arguments );

	},

	copyBranch: function() {

		return this.argumentsHandler( this.className, 'copyBranch', arguments );

	}



} );