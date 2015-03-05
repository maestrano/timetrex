var APIMealPolicy = ServiceCaller.extend( {

	key_name: 'MealPolicy',
	className: 'APIMealPolicy',

	getMealPolicyDefaultData: function() {

		return this.argumentsHandler( this.className, 'getMealPolicyDefaultData', arguments );

	},

	getMealPolicy: function() {

		return this.argumentsHandler( this.className, 'getMealPolicy', arguments );

	},

	getCommonMealPolicyData: function() {

		return this.argumentsHandler( this.className, 'getCommonMealPolicyData', arguments );

	},

	validateMealPolicy: function() {

		return this.argumentsHandler( this.className, 'validateMealPolicy', arguments );

	},

	setMealPolicy: function() {

		return this.argumentsHandler( this.className, 'setMealPolicy', arguments );

	},

	deleteMealPolicy: function() {

		return this.argumentsHandler( this.className, 'deleteMealPolicy', arguments );

	},

	copyMealPolicy: function() {
		return this.argumentsHandler( this.className, 'copyMealPolicy', arguments );
	}


} );