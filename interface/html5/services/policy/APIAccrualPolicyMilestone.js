var APIAccrualPolicyMilestone = ServiceCaller.extend( {

	key_name: 'AccrualPolicyMilestone',
	className: 'APIAccrualPolicyMilestone',

	getAccrualPolicyMilestoneDefaultData: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyMilestoneDefaultData', arguments );

	},

	getAccrualPolicyMilestone: function() {

		return this.argumentsHandler( this.className, 'getAccrualPolicyMilestone', arguments );

	},

	getCommonAccrualPolicyMilestoneData: function() {

		return this.argumentsHandler( this.className, 'getCommonAccrualPolicyMilestoneData', arguments );

	},

	validateAccrualPolicyMilestone: function() {

		return this.argumentsHandler( this.className, 'validateAccrualPolicyMilestone', arguments );

	},

	setAccrualPolicyMilestone: function() {

		return this.argumentsHandler( this.className, 'setAccrualPolicyMilestone', arguments );

	},

	deleteAccrualPolicyMilestone: function() {

		return this.argumentsHandler( this.className, 'deleteAccrualPolicyMilestone', arguments );

	}


} );