var ProgressBar = (function() {

	var loading_box = null;

	var process_number = false;

	var can_close = false;

	var timer = null;

	var close_time = null;

	var circle = null;

	var doing_close = false;

	var message_id_dic = {};

	var updating_message_id = false;

	var current_process_id = false;

	var _progress_bar_api = false;

	var get_progress_timer = null;

	var first_start_get_progress_timer = false;

	var auto_clear_message_id_dic = {}; //for which api calls don't have return; for example all report view calls

	var last_iteration = null;

	var temp_message_until_close = '';

	var second_timer;

	var time_offset;

	var showOverlay = function() {
		Global.overlay().addClass( 'overlay' );
	};

	var closeOverlay = function() {
		Global.overlay().removeClass( 'overlay' );
	};

	var showProgressBar = function( message_id, auto_clear ) {

		if ( !timer ) {
//			clearTimeout( timer );
			//Display progress bar after 1 sec
			timer = setTimeout( function() {
				if ( process_number > 0 && loading_box ) {
					loading_box.css( 'display', 'block' );
				}

			}, 1000 );
		}

		if ( message_id ) {

			message_id_dic[message_id] = true;

			if ( auto_clear ) {
				auto_clear_message_id_dic[message_id] = true;
			}

		}

		if ( !get_progress_timer ) {
			get_progress_timer = setInterval( function() {
				getProgressBarProcess();
			}, 3000 );
			first_start_get_progress_timer = true;
		}

		if ( process_number > 0 ) { //has multi call or the last call is not removed yet
			process_number = process_number + 1;
			return;
		}

		process_number = 1;

		Global.addCss( 'global/widgets/loading_bar/LoadingBox.css' );

		clearTimeout( close_time );
		var message_label;
		if ( !loading_box ) {
			var loadingBoxWidget = Global.loadWidget( 'global/widgets/loading_bar/LoadingBox.html' );
			var loadngBox = $( loadingBoxWidget ).attr( 'id', 'progressBar' );

			var close_icon = loadngBox.find( '.close-icon' );

			close_icon.unbind( 'click' ).click( function() {

				clearInterval( get_progress_timer );
				removeProgressBar( current_process_id );
				get_progress_timer = false;
				current_process_id = false;
				last_iteration = null;
				first_start_get_progress_timer = false;

			} );

			if ( ie >= 9 ) {
				circle = new Sonic( {

					width: 50,
					height: 50,
					padding: 50,

					strokeColor: '#ffffff',

					pointDistance: 0.01,
					stepsPerFrame: 3,
					trailLength: 0.7,

					step: 'fader',

					setup: function() {
						this._.lineWidth = 5;
					},

					path: [
						['arc', 25, 25, 25, 0, 360]
					]

				} );

				circle.play();
			}

			$( 'body' ).append( loadngBox );
			loading_box = $( '#progressBar' );
			message_label = loading_box.find( '.processing' );

			if ( circle ) {
				message_label.after( circle.canvas );
			}

			loading_box.css( 'display', 'none' );

		} else {
			message_label = loading_box.find( '.processing' );

			if ( circle ) {
				circle.stop();
				circle.play();
				message_label.after( circle.canvas );
			}

		}

		resetToDefault();
	};

	var resetToDefault = function() {

		if ( temp_message_until_close ) {
			//Default process message, change this when update progress bar.
			loading_box.find( '.processing' ).text( temp_message_until_close );
		} else {
			//Default process message, change this when update progress bar.
			loading_box.find( '.processing' ).text( $.i18n._( 'Processing...' ) );
		}

		var complete_info = loading_box.find( '.complete-info' );
		var progress_bar = loading_box.find( '.progress-bar' );
		var time_remaining = loading_box.find( '.time-remaining' );

		complete_info.css( 'display', 'none' );
		progress_bar.css( 'display', 'none' );
		time_remaining.css( 'display', 'none' );

		complete_info.text( 0 + ' / ' + 0 + ' ' + 0 + '%' );
		progress_bar.attr( 'value', 0 );

		last_iteration = null;

	};

	var changeProgressBarMessage = function( val ) {
		temp_message_until_close = val;
		if ( loading_box ) {
			loading_box.find( '.processing' ).text( val );
		}

	};

	var updateProgressbar = function( data ) {

		if ( !loading_box ) {
			return;
		}

		loading_box.find( '.processing' ).text( data.message );

		var percentage = data.current_iteration / data.total_iterations;

		var complete_info = loading_box.find( '.complete-info' );
		var progress_bar = loading_box.find( '.progress-bar' );
		var time_remaining = loading_box.find( '.time-remaining' );

		complete_info.css( 'display', 'block' );
		progress_bar.css( 'display', 'block' );
		time_remaining.css( 'display', 'block' );

		complete_info.text( data.current_iteration + ' / ' + data.total_iterations + ' ' + (percentage * 100).toFixed( 0 ) + '%' );
		progress_bar.attr( 'value', (percentage * 100) );

		if ( !last_iteration ) {
			time_remaining.text( 'Calculating remaining time...' );
		} else {

			if ( last_iteration !== data.current_iteration || !second_timer ) {

				time_offset = (data.last_update_time - data.start_time) * (data.total_iterations / data.current_iteration);
				time_offset = time_offset - (data.last_update_time - data.start_time);

				if ( isNaN( time_offset ) || time_offset <= 0 ) {
					time_offset = 0;
				}

				//Error: 'console' is undefined in https://ondemand3.timetrex.com/interface/html5/global/ProgressBarManager.js?v=8.0.0-20141117-153515 line 224
				Global.log( 'New time_offset: ' + time_offset );
				time_remaining.text( Global.secondToHHMMSS( time_offset, '99' ) );
			}
			secondDown();

		}

		last_iteration = data.current_iteration;

	};

	var secondDown = function() {

		//calculate down time every one second
		if ( !second_timer ) {
			var time_remaining = loading_box.find( '.time-remaining' );
			second_timer = setInterval( function() {

				if ( isNaN( time_offset ) || time_offset <= 0 ) {
					time_offset = 0;
				}

				if ( time_offset > 0 ) {
					time_offset = (time_offset - 1);
				}

				if ( time_offset <= 0 ) {
					time_offset = 0;
				}

				time_remaining.text( Global.secondToHHMMSS( time_offset, '99' ) );
			}, 1000 );
		}

	};

	var getProgressBarProcess = function() {

		if ( !LocalCacheData.getLoginData() ) {
			return;
		}

		if ( !current_process_id ) {
			for ( var key in message_id_dic ) {
				current_process_id = key;

				delete message_id_dic[key];
				break;
			}
		}

		if ( current_process_id && $.type( 'current_process_id' ) === 'string' ) {
			var progress_api = new APIProgressBar();

			progress_api.getProgressBar( current_process_id, {
				onResult: function( result ) {
					var res_data = result.getResult();

					//Means error in progress bar
					if ( res_data.hasOwnProperty( 'status_id' ) && res_data.status_id === 9999 ) {
						stopProgress();
						TAlertManager.showAlert( res_data.message );
					} else {
						if ( res_data === true ||
							($.type( res_data ) === 'array' && res_data.length === 0) || !res_data.total_iterations ||
							$.type( res_data.total_iterations ) !== 'number' ) {
							stopProgress();
							return;
						} else {
							updateProgressbar( res_data );
							if ( first_start_get_progress_timer ) {
								first_start_get_progress_timer = false;
								clearInterval( get_progress_timer );
								get_progress_timer = setInterval( function() {
									getProgressBarProcess();
								}, 2000 );

							}

						}
					}

				}
			} );
		}

		function stopProgress() {
			clearInterval( get_progress_timer );

			if ( auto_clear_message_id_dic[current_process_id] ) {
				removeProgressBar( current_process_id );
			}

			get_progress_timer = false;
			current_process_id = false;
			last_iteration = null;
			first_start_get_progress_timer = false;
		}

	};

	var removeProgressBar = function( message_id ) {

		if ( message_id ) {
			delete message_id_dic[message_id];
			delete auto_clear_message_id_dic[message_id];

			if ( current_process_id === message_id ) {
				current_process_id = false;
			}
		}

		if ( process_number > 0 ) {

			process_number = process_number - 1;
			if ( process_number === 0 ) {
				removeProgressBar();
			}

		} else {

			if ( loading_box ) {
				doing_close = true;
				clearTimeout( close_time );
				close_time = setTimeout( function() {
					closeOverlay();

					if ( second_timer ) {
						clearInterval( second_timer );
						second_timer = null;
					}
					timer = null;
					temp_message_until_close = '';
					if ( loading_box ) {
						loading_box.css( 'display', 'none' );

						if ( circle ) {
							circle.stop();
						}

					}

				}, 500 );

			}
		}

	};

	return {
		showProgressBar: showProgressBar,
		removeProgressBar: removeProgressBar,
		showOverlay: showOverlay,
		closeOverlay: closeOverlay,
		message_id_dic: message_id_dic,
		changeProgressBarMessage: changeProgressBarMessage
	};

})();