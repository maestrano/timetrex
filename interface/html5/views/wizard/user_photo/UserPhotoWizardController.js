UserPhotoWizardController = BaseWizardController.extend( {

	el: '.wizard',

	initialize: function() {
		this._super( 'initialize' );

		this.title = $.i18n._( 'Image upload Wizard' );
		this.steps = 3;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	//Create each page UI
	buildCurrentStepUI: function() {

		var $this = this;

		this.content_div.empty();
		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				label.text( $.i18n._( 'Please choose the image source' ) );

				var combo_box = this.getComboBox( 'image_type' );

				combo_box.setSourceData( [
					{value: 'file', label: $.i18n._('File')},
					{value: 'camera', label: $.i18n._('Camera')}
				] );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][combo_box.getField()] = combo_box;

				this.content_div.append( label );
				this.content_div.append( combo_box );
				break;
			case 2:

				var step_1_Data = this.stepsDataDic[1];
				label = this.getLabel();
				var img;
				if ( step_1_Data.image_type === 'file' ) {
					label.text( $.i18n._( 'Choose image file to upload' ) );
					img = this.getFileBrowser( 'image_data', null, 500, 350 );
				} else {
					label.text( $.i18n._( 'Take picture from camera' ) );
					img = this.getCameraBrowser( 'image_data' );

					img.unbind( 'NoImageChange' ).bind( 'NoImageChange', function() {
						Global.setWidgetEnabled( $this.next_btn, false );
					} );
				}

				img.unbind( 'change' ).bind( 'change', function() {
					Global.setWidgetEnabled( $this.next_btn, true );
				} );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][img.getField()] = img;

				this.content_div.append( label );
				this.content_div.append( img );

				break;
			case 3:

				label = this.getLabel();
				label.text( $.i18n._( 'Crop and resize image' ) );

				img = this.getImageCutArea( 'image_cut' );
				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][img.getField()] = img;

				var hide_form = $( ' <form enctype="multipart/form-data" class="browser-form"></form>' );

				this.stepsWidgetDic[this.current_step]['hide_form'] = hide_form;

				this.content_div.append( label );
				this.content_div.append( img );
				this.content_div.append( hide_form );

				break;
		}
	},

	buildCurrentStepData: function() {
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 1:
				if ( current_step_data ) {
					current_step_ui.image_type.setValue( current_step_data.image_type );
				}

				break;
			case 2:
				if ( this.stepsDataDic[1].image_type === 'camera' ) {
					current_step_ui.image_data.showCamera();
				}

				Global.setWidgetEnabled( this.next_btn, false );

				break;
			case 3:
				current_step_ui.image_cut.setImage( this.stepsDataDic[2].img_src );
		}
	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();

		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var current_step_data = this.stepsDataDic[this.current_step];

		var form_data = new FormData( current_step_ui.hide_form[0] );

		var image_source_array = current_step_data.after_img_src.split( ',' );

		var args = {};

		form_data.append( 'file_data', image_source_array[1] );
		form_data.append( 'base64_encoded', true );
		form_data.append( 'mime_type', image_source_array[0].split( ';' )[0].split( ':' )[1] );
		form_data.append( 'file_name', this.stepsDataDic[2].file_name );

		if ( this.call_back ) {
			this.call_back( form_data );
		}

		$this.onCloseClick();

	},

	onCloseClick: function() {
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		if ( this.current_step === 3 ) {
			current_step_ui.image_cut.clearSelect();
		}

		if ( this.current_step === 2 && this.stepsDataDic[1].image_type === 'camera' ) {
			current_step_ui.image_data.stopCamera();
		}

		$( this.el ).remove();
		LocalCacheData.current_open_wizard_controller = null;

	},

	onNextClick: function() {
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		if ( this.current_step === 3 ) {
			current_step_ui.image_cut.clearSelect();
		}
		if ( this.current_step === 2 && this.stepsDataDic[1].image_type === 'camera' ) {
			current_step_ui.image_data.stopCamera();
		}
		this.saveCurrentStep();
		this.current_step = this.current_step + 1;
		this.initCurrentStep();
	},

	onBackClick: function() {
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		if ( this.current_step === 3 ) {
			current_step_ui.image_cut.clearSelect();
		}
		if ( this.current_step === 2 && this.stepsDataDic[1].image_type === 'camera' ) {
			current_step_ui.image_data.stopCamera();
		}
		this.saveCurrentStep();
		this.current_step = this.current_step - 1;
		this.initCurrentStep();
	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				current_step_data.image_type = current_step_ui.image_type.getValue();
				break;
			case 2:
				current_step_data.img_src = current_step_ui.image_data.getImageSrc();
				current_step_data.file_name = current_step_ui.image_data.getFileName();

				break;
			case 3:

				current_step_data.after_img_src = current_step_ui.image_cut.getAfterImageSrc();
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

		this.stepsDataDic[2] = {};
		this.stepsDataDic[3] = {};

		if ( this.getDefaultData( 'user_id' ) ) {
			this.stepsDataDic[3].user_id = this.getDefaultData( 'user_id' );
		}

		if ( this.getDefaultData( 'pay_period_id' ) ) {
			this.stepsDataDic[2].pay_period_id = this.getDefaultData( 'pay_period_id' );
		}

	}


} );