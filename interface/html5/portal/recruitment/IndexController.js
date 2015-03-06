var ApplicationRouter = Backbone.Router.extend( {
	controller: null,

	routes: {
		'': 'onViewChange',
		'!:viewName': 'onViewChange',
		'*notFound': 'notFound'
	},

	buildArgDic: function( array ) {
		var len = array.length;
		var result = {};
		for ( var i = 0; i < len; i++ ) {
			var item = array[i];
			item = item.split( '=' );
			result[item[0]] = item[1];
		}

		return result;
	},

	reloadView: function( view_id ) {
		TopMenuManager.selected_sub_menu_id = ''; // clear select ribbon menu, set in view init;
		this.removeCurrentView();
		BaseViewController.loadView( view_id );
	},

	notFound: function( url ) {

		var new_url = Global.getBaseURL().split( '#' )[0];

		Global.setURLToBrowser( new_url + '#!m=PortalLogin' );
	},

	/* jshint ignore:start */
	onViewChange: function( viewName ) {
		var args = {};
		var view_id;
		var edit_id;
		var action;

		if ( Global.needReloadBrowser ) {
			Global.needReloadBrowser = false;
			window.location.reload();
			return;
		}

		if ( viewName ) {
			args = this.buildArgDic( viewName.split( '&' ) );
		}
		if ( viewName && viewName.indexOf( 'm=' ) >= 0 ) {
			view_id = args.m;
		} else {
			view_id = 'PortalLogin';
		}

		LocalCacheData.fullUrlParameterStr = viewName;

		LocalCacheData.all_url_args = args;

		var reg = new RegExp( '^[0-9]*$' );

		if ( reg.test( args.id ) ) {
			edit_id = parseInt( args.id );
		} else {
			edit_id = args.id; //Accrual balance go here, because it's id is combined. x_x
		}

		action = args.a;

		if ( LocalCacheData.current_open_view_id === view_id ) {

			if ( LocalCacheData.current_open_primary_controller ) {

				if ( action ) {
					switch ( action ) {
						case 'edit':
							if ( !LocalCacheData.current_open_primary_controller.edit_view ||
								(LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id) ) {

								//Makes ure when doing copy_as_new, don't open this
								if ( LocalCacheData.current_doing_context_action === 'edit' ) {
									openEditView( edit_id );
								}

							}
							break;
						case 'new':
							if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
								openEditView();
							}

							break;
						case 'view':

							switch ( view_id ) {
								case 'MessageControl':
									if ( args.t === 'message' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											(!checkIds()) ) {
											openEditView( edit_id, true );
										}
									} else if ( args.t === 'request' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											(LocalCacheData.current_open_primary_controller.current_select_message_control_data.id != edit_id) ) {
											openEditView( edit_id, true );
										}
									}
									break;
								default:
									if ( !LocalCacheData.current_open_primary_controller.edit_view ||
										(LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id) ) {
										openEditView( edit_id, true );
									}
									break;
							}

					}

					return;
				} else {
					if ( LocalCacheData.current_open_primary_controller.edit_view &&
						LocalCacheData.current_open_primary_controller.current_edit_record ) {

						if ( LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
							if ( LocalCacheData.current_open_primary_controller.is_mass_editing ) {
								return;
							}
						}

						LocalCacheData.current_open_primary_controller.buildContextMenu( true );
						LocalCacheData.current_open_primary_controller.removeEditView();
						this.cleanAnySubViewUI();

					}
				}

			}
			return;

		} else {
			LocalCacheData.edit_id_for_next_open_view = edit_id;

			if ( action ) {
				LocalCacheData.current_doing_context_action = action;
			}

			switch ( view_id ) {
				case 'TimeSheet':
				case 'Schedule':
					if ( args.date ) {
						LocalCacheData.current_selet_date = args.date;
					}
					break;

			}

		}

		this.removeCurrentView();

		if ( view_id !== 'PortalLogin' && !LocalCacheData.getLoginUser() ) {
			Global.setURLToBrowser( Global.getBaseURL() + '#!m=PortalLogin' );
			return;
		} else if ( view_id !== 'PortalLogin' && Global.isSet( view_id ) ) {

			if ( !TopMenuManager.ribbon_menus ) {
				TopMenuManager.initPortalRibbonMenu();
				TopMenuManager.selected_sub_menu_id = view_id;
				TopMenuManager.selected_menu_id = TopMenuManager.menus_quick_map[view_id];

			}

			//Add copy right
			Global.bottomContainer().css( 'display', 'block' );

			$( '#copy_right_info_1' ).css( 'display', 'inline' );

			$( '#copy_right_logo_link' ).attr( 'href', 'http://' + LocalCacheData.getLoginData().organization_url );

			if ( !$( '#copy_right_logo' ).attr( 'src' ) ) {
				$( '#copy_right_logo' ).attr( 'src', ServiceCaller.poweredByLogo + '&t=' + new Date().getTime() );

			}

		}
		//Show ribbon menu UI
		if ( view_id && view_id !== 'PortalLogin' && !TopMenuManager.ribbon_view_controller ) {
			this.addTopMenu();
			$( 'body' ).removeClass( 'login-bg' );
			$( 'body' ).addClass( 'application-bg' );
			this.setContentDivHeight();
			this.setLoginInformationLabel();

		} else if ( view_id && view_id !== 'PortalLogin' && TopMenuManager.ribbon_view_controller ) {
			Global.topContainer().css( 'display', 'block' );
			$( 'body' ).removeClass( 'login-bg' );
			$( 'body' ).addClass( 'application-bg' );
		}

		Global.loadViewSource( view_id, view_id + 'ViewController.js', function() {

			var permission_id = view_id;

			switch ( view_id ) {
				case 'ClientGroup':
					permission_id = 'Client';
					break;
				case 'ProductGroup':
					permission_id = 'Product';
					break;

			}

			if ( view_id === 'PortalLogin' || PermissionManager.checkTopLevelPermission( permission_id ) ) {
				BaseViewController.loadView( view_id );
			} else {
				TAlertManager.showAlert( 'Permission denied' );
			}

		} );

		function checkIds() {

			if ( Global.isArray( LocalCacheData.current_open_primary_controller.current_edit_record ) ) {
				for ( var i = 0; i < LocalCacheData.current_open_primary_controller.current_edit_record.length; i++ ) {
					var item = LocalCacheData.current_open_primary_controller.current_edit_record[i];

					if ( item.id === edit_id ) {
						return true;
					}
				}
			} else {
				item = LocalCacheData.current_open_primary_controller.current_edit_record;
				if ( item.id === edit_id ) {
					return true;
				}
			}

			return false;
		}

		function openEditView( edit_id, view_mode ) {
			var type;
			switch ( view_id ) {
				case 'MessageControl':
					type = args.t;
					var item = {};
					if ( type === 'message' ) {
						item.id = edit_id;
					} else {
						item.object_id = edit_id;
						item.object_type_id = 50;
					}
					LocalCacheData.current_open_primary_controller.onViewClick( item );
					break;

				case 'TimeSheet':
					type = args.t;

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id, type );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id, type );
						}
					}

					break;
				default:

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id );
						}
					}

					break;
			}
		}

	},

	/* jshint ignore:end */

	cleanAnySubViewUI: function() {
		var children = Global.contentContainer().children();

		if ( children.length > 1 ) {
			for ( var i = 1; i < children.length; i++ ) {
				// Object doesn't support property or method 'remove', Not sure why, add try catch to ingore this error since this should no harm
				try {

					if ( $( children[i] ).attr( 'id' ) === LocalCacheData.current_open_primary_controller.ui_id ) {
						continue;
					} else {
						children[i].remove();
					}

				} catch ( e ) {
					//Do nothing
				}

			}
		}
	},

	//CompanyName - User name at top left
	setLoginInformationLabel: function() {

		var current_company = LocalCacheData.getCurrentCompany();
		var current_user = LocalCacheData.getLoginUser();

		var label = current_company.name + ' - ' + current_user.first_name + ' ' + current_user.last_name;
		var label_container = $( "<div class='login-information-div'><span class='login-information'></span></div>" );

		label_container.children().eq( 0 ).text( label );

		Global.topContainer().append( label_container );
	},

	setContentDivHeight: function() {
		Global.contentContainer().css( 'height', (Global.bodyHeight() - Global.topContainer().height() - 5) );

		$( window ).resize( function() {
			Global.contentContainer().css( 'height', (Global.bodyHeight() - Global.topContainer().height() - 5) );
		} );

		Global.contentContainer().removeClass( 'content-container' );
		Global.contentContainer().addClass( 'content-container-after-login' );

	},

	addTopMenu: function() {

		Global.loadScript( 'global/widgets/ribbon/RibbonViewController.js' );

		//Error: ReferenceError: Can't find variable: RibbonViewController in https://ondemand1.timetrex.com/interface/html5/IndexController.js?v=8.0.0-20141117-091433 line 346
		if ( RibbonViewController ) {
			RibbonViewController.loadView();
		}

	},

	removeCurrentView: function( callBack ) {

		if ( LocalCacheData.current_open_primary_controller ) {
			Global.contentContainer().empty();
			LocalCacheData.current_open_primary_controller.cleanWhenUnloadView( callBack );
		} else {

			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}
	}

} );

IndexViewController = Backbone.View.extend( {
	el: 'body', //So we can add event listener for all elements
	router: null,

	initialize: function() {
		this.router = new ApplicationRouter();

		//Set title in index.php instead.
		//$( 'title' ).html( '' );

		this.router.controller = this;
		Backbone.history.start();

		IndexViewController.instance = this;

	}

} );

IndexViewController.goToView = function( view_name, filter ) {
	if ( TopMenuManager.selected_sub_menu_id ) {
		$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
	}

	$( '#' + view_name ).addClass( 'selected-menu' );
	LocalCacheData.default_filter_for_next_open_view = filter;

	TopMenuManager.goToView( view_name, true );

};

IndexViewController.goToViewByViewLabel = function( view_label ) {
	var view_name;
	switch ( view_label ) {
		case 'Exceptions':
			view_name = 'Exception';
			break;
		case 'Messages':
			view_name = 'MessageControl';
			break;
		case 'Requests':
			view_name = 'Request';
			break;
		case 'Contact Information':
			IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'LoginUserContact' );
			return;
			break;
		default:
			var reg = /\s/g;
			view_name = view_label.replace( reg, '' );
			break
	}

	if ( TopMenuManager.selected_sub_menu_id ) {
		$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
	}

	$( '#' + view_name ).addClass( 'selected-menu' );

	TopMenuManager.goToView( view_name, true );

};

IndexViewController.openWizard = function( wizardName, defaultData, callBack ) {
	BaseWizardController.default_data = defaultData;
	BaseWizardController.call_back = callBack;
	switch ( wizardName ) {
		default:
			// track edit view only view
			Global.trackView( wizardName );
			Global.loadViewSource( wizardName, wizardName + 'Controller.js', function() {
				BaseWizardController.openWizard( wizardName, wizardName + '.html' );
			} );
			break;
	}

};

IndexViewController.openReport = function( parent_view_controller, view_name, id ) {
	var view_controller = null;

	if ( LocalCacheData.current_open_report_controller ) {
		LocalCacheData.current_open_report_controller.removeEditView();
	}

	ProgressBar.showOverlay();

	switch ( view_name ) {
		default:
			Global.loadViewSource( view_name, view_name + 'ViewController.js', function() {
				/* jshint ignore:start */
				view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
				/* jshint ignore:end */
				view_controller.parent_view_controller = parent_view_controller;
				view_controller.openEditView();

				var current_url = window.location.href;
				if ( current_url.indexOf( '&sm' ) > 0 ) {
					current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				}
				current_url = current_url + '&sm=' + view_name;

				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					current_url = current_url + '&sid=' + LocalCacheData.default_edit_id_for_next_open_edit_view;
				}

				Global.setURLToBrowser( current_url );

			} );
			break;
	}

};

//Open edit view
IndexViewController.openEditView = function( parent_view_controller, view_name, id ) {
	var view_controller = null;

	if ( !PermissionManager.checkTopLevelPermission( view_name ) ) {
		TAlertManager.showAlert( 'Permission denied' );
		return;
	}

	// track edit view only view
	Global.trackView( view_name );

	switch ( view_name ) {

		default:
			Global.loadViewSource( view_name, view_name + 'ViewController.js', function() {
				/* jshint ignore:start */
				view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
				/* jshint ignore:end */
				view_controller.parent_view_controller = parent_view_controller;
				view_controller.openEditView( id );

				var current_url = window.location.href;
				if ( current_url.indexOf( '&sm' ) > 0 ) {
					current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				}
				if ( id ) {
					current_url = current_url + '&sm=' + view_name + '&sid=' + id;
				} else {
					current_url = current_url + '&sm=' + view_name;
				}

				Global.setURLToBrowser( current_url );

				LocalCacheData.current_open_edit_only_controller = view_controller;

			} );
			break;

	}

};

IndexViewController.setNotificationBar = function( target ) {

	var api = new (APIFactory.getAPIClass( 'APINotification' ))();

	//Error: TypeError: api.getNotification is not a function in https://ondemand2001.timetrex.com/interface/html5/IndexController.js?v=8.0.0-20141117-095711 line 529
	if ( !api || !api.getNotification || typeof(api.getNotification) !== 'function' ) {
		return;
	}

	api.getNotification( target, {
		onResult: function( result ) {
			var result_data = result.getResult();

			if ( !LocalCacheData.notification_bar ) {
				var notification_box_tpl = $( Global.loadWidgetByName( WidgetNamesDic.NOTIFICATION_BAR ) );
				LocalCacheData.notification_bar = notification_box_tpl.TopNotification();
			}

			LocalCacheData.notification_bar.show( result_data );

		}
	} );

};

IndexViewController.instance = null;