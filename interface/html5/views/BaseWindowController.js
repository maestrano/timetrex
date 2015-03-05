BaseWindowController = Backbone.View.extend( {

	initialize: function() {
		this.content_div = $( this.el ).find( '.content' );
		this.stepsWidgetDic = {};
		this.stepsDataDic = {};

		this.default_data = BaseWizardController.default_data;
		this.call_back = BaseWizardController.call_back;

		BaseWizardController.default_data = null;
		BaseWizardController.call_back = null;

		this.setDefaultDataToSteps()
	},

	render: function() {

	}

} );