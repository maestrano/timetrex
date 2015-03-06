var APIUserSkill = ServiceCaller.extend( {

	key_name: 'UserSkill',
	className: 'APIUserSkill',

	getCommonUserSkillData: function() {

		return this.argumentsHandler( this.className, 'getCommonUserSkillData', arguments );

	},

	getUserSkill: function() {

		return this.argumentsHandler( this.className, 'getUserSkill', arguments );

	},

	setUserSkill: function() {

		return this.argumentsHandler( this.className, 'setUserSkill', arguments );

	},

	getUserSkillDefaultData: function() {

		return this.argumentsHandler( this.className, 'getUserSkillDefaultData', arguments );

	},

	deleteUserSkill: function() {

		return this.argumentsHandler( this.className, 'deleteUserSkill', arguments );

	},

	validateUserSkill: function() {

		return this.argumentsHandler( this.className, 'validateUserSkill', arguments );

	},

	copyUserSkill: function() {

		return this.argumentsHandler( this.className, 'copyUserSkill', arguments );

	},

	generateInvoices: function() {

		return this.argumentsHandler( this.className, 'generateInvoices', arguments );

	},

	calcExperience: function() {

		return this.argumentsHandler( this.className, 'calcExperience', arguments );

	}



} );