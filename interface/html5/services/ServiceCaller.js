var ServiceCaller = Backbone.Model.extend( {

	getOptions: function() {
		if ( !arguments || arguments.length < 2 ) {
			return;
		}
		return this.argumentsHandler( this.className, 'getOptions', arguments );
	},

	getPagerData: function() {

		return this.argumentsHandler( this.className, 'getPagerData', arguments );
	},

	getOtherConfig: function() {
		return this.argumentsHandler( this.className, 'getOtherConfig', arguments );
	},

	getChartConfig: function() {
		return this.argumentsHandler( this.className, 'getChartConfig', arguments );
	},

	argumentsHandler: function() {
		var className = arguments[0];
		var function_name = arguments[1];
		var apiArgsAndResponseObject = arguments[2];
		var lastApiArgsAndResponseObject = arguments[2][(apiArgsAndResponseObject.length - 1)];
		var apiArgs = {};
		var responseObject;
		var len;

		if ( Global.isSet( lastApiArgsAndResponseObject.onResult ) || Global.isSet( lastApiArgsAndResponseObject.async ) ) {
			len = (apiArgsAndResponseObject.length - 1);

			responseObject = new ResponseObject( lastApiArgsAndResponseObject );

		} else {
			len = apiArgsAndResponseObject.length;
			responseObject = null;
		}

		for ( var i = 0; i < len; i++ ) {
			apiArgs[i] = apiArgsAndResponseObject[i];

			if ( i === 0 && len === 1 &&
				Global.isSet( apiArgs[i] ) &&
				Global.isSet( apiArgs[i].second_parameter ) ) {
				apiArgs[1] = apiArgs[i].second_parameter;
			}

		}

		return this.call( className, function_name, responseObject, apiArgs );

	},

	getOptionsCacheKey: function( api_args, key ) {

		$.each( api_args, function( index, value ) {

			if ( $.type( value ) === 'object' ) {
				key = key + '_' + JSON.stringify( value )
			} else {
				key = key + '_' + value;
			}

		} );

		return key;

	},

	uploadFile: function( form_data, paramaters, responseObj ) {

//		var data = {filedata: form_data, SessionID: LocalCacheData.getSessionID()};
//
		ProgressBar.showProgressBar();

		//On IE 9
		if ( typeof FormData == "undefined" ) {
			form_data.attr( 'method', 'POST' );
			form_data.attr( 'action', ServiceCaller.uploadURL + '?' + paramaters + '&SessionID=' + LocalCacheData.getSessionID() );
			form_data.attr( 'enctype', 'multipart/form-data' );

			ProgressBar.changeProgressBarMessage( 'File Uploading' );
			form_data.ajaxForm().ajaxSubmit( {
				success: function( result ) {
					ProgressBar.removeProgressBar();
					if ( responseObj.onResult ) {
						responseObj.onResult( result );
					}

				}
			} );
			return;
		}

		ProgressBar.changeProgressBarMessage( 'File Uploading' );
		$.ajax( {
			url: ServiceCaller.uploadURL + '?' + paramaters + '&SessionID=' + LocalCacheData.getSessionID(), //Server script to process data
			type: 'POST',
//			xhr: function() {     // Custom XMLHttpRequest
//				var myXhr = $.ajaxSettings.xhr();
//				if ( myXhr.upload ) { // Check if upload property exists
//					myXhr.upload.addEventListener( 'progress', progressHandlingFunction, false ); // For handling the progress of the upload
//				}
//
//				function progressHandlingFunction() {
//				}
//
//				return myXhr;
//
//			},

			success: function( res ) {
				if ( responseObj.onResult ) {
					responseObj.onResult( res );
				}

				ProgressBar.removeProgressBar();
			},
			// Form data
			data: form_data,
			cache: false,
			contentType: false,
			processData: false
		} );

	},

	call: function( className, function_name, responseObject, apiArgs ) {

		var $this = this;
		var message_id;
		var url = ServiceCaller.getURLWithSessionId( 'Class=' + className + '&Method=' + function_name + '&v=2' );

		if ( LocalCacheData.getStationID() ) {
			url = url + '&StationID=' + LocalCacheData.getStationID();
		}

		var apiReturnHandler;
		var async;

		if ( responseObject && responseObject.get( 'async' ) === false ) {
			async = responseObject.get( 'async' );
		} else {
			async = true;
		}
		var cache_key;
		switch ( function_name ) {
			case 'getOptions':
			case 'getOtherField':
			case 'isBranchAndDepartmentAndJobAndJobItemEnabled':
				cache_key = this.getOptionsCacheKey( apiArgs, className + '.' + function_name );

				if ( responseObject.get( 'noCache' ) === true ) {
					LocalCacheData.result_cache[cache_key] = false;
				}

				if ( cache_key && LocalCacheData.result_cache[cache_key] ) {
					var result = LocalCacheData.result_cache[cache_key];

					apiReturnHandler = new APIReturnHandler();

					apiReturnHandler.set( 'result_data', result );
					apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
					apiReturnHandler.set( 'function_name', function_name );
					apiReturnHandler.set( 'args', apiArgs );

					if ( responseObject.get( 'onResult' ) ) {
						responseObject.get( 'onResult' )( apiReturnHandler );
					}

					return apiReturnHandler;

				}
				break;

		}

		if ( className !== 'APIProgressBar' && function_name !== 'Logout' ) {
			message_id = UUID.guid();
			url = url + '&MessageID=' + message_id;
		}

		if ( !apiArgs ) {
			apiArgs = {};

		}

		if ( ie <= 8 ) {
			apiArgs = {json: $.toJSON( apiArgs )};
		} else {
			apiArgs = {json: JSON.stringify( apiArgs )};
		}

		//#1568  -  Add "fragment" to POST variables in API calls so the server can get it...
		//apiArgs.REQUEST_URI_FRAGMENT = LocalCacheData.fullUrlParameterStr;

		var api_called_date = new Date();
		var api_stack = {
			api: className + '.' + function_name,
			args: apiArgs.json,
			api_called_date: api_called_date.format( 'hh:mm:ss' ) + '.' + api_called_date.getMilliseconds()
		};

		if ( LocalCacheData.api_stack.length === 8 ) {
			LocalCacheData.api_stack.pop();
		}

		if ( function_name !== 'sendErrorReport' ) {
			LocalCacheData.api_stack.unshift( api_stack );
		}

		if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
			ProgressBar.showProgressBar( message_id );
		}

		$.ajax(
			{
				dataType: 'JSON',
				data: apiArgs,
				headers: {
					"REQUEST_URI_FRAGMENT": LocalCacheData.fullUrlParameterStr
				},
				type: 'POST',
				async: async,
				url: url,
				success: function( result ) {

					if ( !Global.isSet( result ) ) {
						result = true;
					}
					if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
						ProgressBar.removeProgressBar( message_id );
					}

					switch ( function_name ) {
						case 'getOptions':
						case 'getOtherField':
						case 'isBranchAndDepartmentAndJobAndJobItemEnabled':
							LocalCacheData.result_cache[cache_key] = result;
							break;
					}

					apiReturnHandler = new APIReturnHandler();
					apiReturnHandler.set( 'result_data', result );
					apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
					apiReturnHandler.set( 'function_name', function_name );
					apiReturnHandler.set( 'args', apiArgs );

					if ( !apiReturnHandler.isValid() && apiReturnHandler.getCode() === 'EXCEPTION' ) {
						TAlertManager.showAlert( apiReturnHandler.getDescription(), 'Error' );
						return;
					} else if ( !apiReturnHandler.isValid() && apiReturnHandler.getCode() === 'SESSION' ) {
						ServiceCaller.cancelAllError = true;

						LocalCacheData.login_error_string = $.i18n._( 'Session expired, please login again.' );

						$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );
						window.location = Global.getBaseURL() + '#!m=' + 'Login';

						return;
					} else {
						if ( responseObject.get( 'onResult' ) ) {
							responseObject.get( 'onResult' )( apiReturnHandler );
						}
					}

				},

				error: function( error ) { //Server exceptions

					if ( className !== 'APIProgressBar' && function_name !== 'Login' && function_name !== 'getPreLoginData' ) {
						ProgressBar.removeProgressBar( message_id );
					}

					if ( ServiceCaller.cancelAllError ) {
						return;
					}
					if ( error.responseText && error.responseText.indexOf( 'User not authenticated' ) >= 0 ) {

						ServiceCaller.cancelAllError = true;

						LocalCacheData.login_error_string = $.i18n._( 'Session timed out, please login again.' );

						$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );
						window.location = Global.getBaseURL() + '#!m=' + 'Login';

						return;

					} else {

						if ( error.responseText && $.type( error.responseText ) === 'string' ) {
							TAlertManager.showAlert( error.responseText, 'Error' );
						}

					}

					if ( error.status === 200 && !error.responseText ) {
						apiReturnHandler = new APIReturnHandler();
						apiReturnHandler.set( 'result_data', true );
						apiReturnHandler.set( 'delegate', responseObject.get( 'delegate' ) );
						apiReturnHandler.set( 'function_name', function_name );
						apiReturnHandler.set( 'args', apiArgs );

						if ( responseObject.get( 'onResult' ) ) {
							responseObject.get( 'onResult' )( apiReturnHandler );
						}
						return apiReturnHandler;

					} else {
						return null;
					}

				}

			}
		);

		return apiReturnHandler;
	}
} );

ServiceCaller.getURLWithSessionId = function( rest_url ) {

	//Error: Object doesn't support property or method 'cookie' in https://ondemand2001.timetrex.com/interface/html5/services/ServiceCaller.js?v=8.0.0-20150126-192230 line 326
	if ( $ && $.cookie && !$.cookie( 'SessionID' ) ) {
		LocalCacheData.setSessionID( '' );
	}

	if ( $ && $.cookie && $.cookie( 'js_debug' ) ) {
		return ServiceCaller.baseUrl + '?SessionID=' + LocalCacheData.getSessionID() + '&' + rest_url;
	} else {
		return ServiceCaller.baseUrl + '?' + rest_url;
	}

};

ServiceCaller.hosts = null;

ServiceCaller.baseUrl = null;

ServiceCaller.fileDownloadURL = null;

ServiceCaller.uploadURL = null;

ServiceCaller.companyLogo = null;

ServiceCaller.mainCompanyLogo = null;

ServiceCaller.poweredByLogo = null;

ServiceCaller.staticURL = null;

ServiceCaller.rootURL = null;

ServiceCaller.sessionID = '';

ServiceCaller.cancelAllError = false;

ServiceCaller.ozUrl = false;
