/*

 To be the common  data model for data return from api

 */

var APIReturnHandler = Base.extend( {


	defaults: {
		result_data: null,
		delegate: null
	},

	isValid: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_retval ) ) {
			return this.get( 'result_data' ).api_retval;
		}

		return true;
	},

	getDetails: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.details ) ) {
			return this.get( 'result_data' ).api_details.details;
		}

		return true;
	},

	getPagerData: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.pager ) ) {
			return this.get( 'result_data' ).api_details.pager;
		}

		return false;
	},

	getResult: function() {

		var result;
		if ( Global.isSet( this.get( 'result_data' ).api_retval ) ) {
			result = this.get( 'result_data' ).api_retval;
		} else {
			result = this.get( 'result_data' );
		}

		if ( typeof result === 'undefined' ) {
			result = null;
		} else if ( $.type( result ) === 'array' && result.length === 0 ) {
			result = {};
		}

		return result;

	},

	getCode: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.code ) ) {
			return this.get( 'result_data' ).api_details.code;
		}

		return false;
	},

	getDescription: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.description ) ) {
			return this.get( 'result_data' ).api_details.description;
		}

		return false;
	},

	getRecordDetails: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) ) {
			return this.get( 'result_data' ).api_details.record_details;
		}

		return false;
	},

	getTotalRecords: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.total_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.total_records;
		}

		return false;
	},

	getValidRecords: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.valid_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.valid_records;
		}

		return false;
	},

	getInValidRecords: function() {
		if ( Global.isSet( this.get( 'result_data' ).api_details ) && Global.isSet( this.get( 'result_data' ).api_details.record_details ) &&
			Global.isSet( this.get( 'result_data' ).api_details.record_details.invalid_records ) ) {
			return this.get( 'result_data' ).api_details.record_details.invalid_records;
		}

		return false;
	},

	getAttributeInAPIDetails: function( attrName ) {
		return    this.get( 'result_data' ).api_details[attrName];
	},

	getDetailsAsString: function() {
		var errorInfo = '';

		$.each( this.getDetails(), function( index, errorItem ) {

			for ( var i in errorItem ) {
				errorInfo += errorItem[i][0] + '\r'
			}
		} );

		return errorInfo;
	}



} )