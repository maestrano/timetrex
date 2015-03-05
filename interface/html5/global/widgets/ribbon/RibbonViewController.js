RibbonViewController = Backbone.View.extend( {

	el: '#ribbon_view_container', //Must set el here and can only set string, so events can work
	user_api: null,

	subMenuNavMap: null,

	initialize: function() {

		this.render();

		TopMenuManager.ribbon_view_controller = this;

	},

	events: {
//		  'click .ribbon-sub-menu-icon': 'onSubMenuClick'
	},

	onMenuSelect: function( e, ui ) {

		if ( TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) >= 0 ) {
			$( '.context-menu-active' ).removeClass( 'context-menu-active' );
		}

		TopMenuManager.selected_menu_id = $( ui.tab ).attr( 'ref' );

		if ( TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) >= 0 ) {
			$( ui.tab ).parent().addClass( 'context-menu-active' );
		}
	},

	onSubMenuNavClick: function( target, id ) {
		var $this = this;
		var sub_menu = this.subMenuNavMap[id];

		if ( LocalCacheData.openRibbonNaviMenu ) {

			if ( LocalCacheData.openRibbonNaviMenu.attr( 'id' ) === 'sub_nav' + id ) {
				LocalCacheData.openRibbonNaviMenu.close();
				return;
			} else {
				LocalCacheData.openRibbonNaviMenu.close();
			}

		}

//		  alert( sub_menu.get('items')[0].get('label') );
		showNavItems();

		function showNavItems() {
			var items = sub_menu.get( 'items' );
			var box = $( "<ul id='sub_nav" + id + "' class='ribbon-sub-menu-nav'> </ul>" );
			for ( var i = 0; i < items.length; i++ ) {
				var item = items[i];
				var item_node = $( "<li class='ribbon-sub-menu-nav-item' id='" + item.get( 'id' ) + "'><span class='label'>" + item.get( 'label' ) + "</span></li>" )
				box.append( item_node );

				item_node.unbind( 'click' ).click( function() {

					var id = $( this ).attr( 'id' );
					$this.onReportMenuClick( id )
				} );

			}

			box = box.RibbonSubMenuNavWidget();

			LocalCacheData.openRibbonNaviMenu = box;

			$( target ).append( box )
		}

	},
	onReportMenuClick: function( id ) {

		if ( id === 'AffordableCareReport' && !(LocalCacheData.getCurrentCompany().product_edition_id > 10) ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
		} else {
			IndexViewController.openReport( LocalCacheData.current_open_primary_controller, id );
		}

	},

	onSubMenuClick: function( id ) {
		this.setSelectSubMenu( id );
		this.openSelectView( id );

	},

	buildRibbonMenus: function() {

		var $this = this;
		this.subMenuNavMap = {};
		var ribbon_menu_array = TopMenuManager.ribbon_menus;
		var ribbon_menu_label_node = $( '.ribbonTabLabel' );
		var ribbon_menu_root_node = $( '.ribbon' );

		var len = ribbon_menu_array.length;

		for ( var i = 0; i < len; i++ ) {

			var ribbon_menu = ribbon_menu_array[i];

			if ( ribbon_menu.get( 'permission_result' ) === false ) {
				continue;
			}

			var ribbon_menu_group_array = ribbon_menu.get( 'sub_menu_groups' );
			var ribbon_menu_ui = $( '<div id="' + ribbon_menu.get( 'id' ) + '" class="ribbon-tab-out-side"><div class="ribbon-tab"><div class="ribbon-sub-menu"></div></div></div>' );

			var len1 = ribbon_menu_group_array.length;
			for ( var x = 0; x < len1; x++ ) {
				var ribbon_menu_group = ribbon_menu_group_array[x];
				var ribbon_sub_menu_array = ribbon_menu_group.get( 'sub_menus' );
				var sub_menu_ui_nodes = $( "<ul></ul>" );
				var ribbon_menu_group_ui = $( '<div class="menu top-ribbon-menu" ondragstart="return false;" />' );

				var len2 = ribbon_sub_menu_array.length;
				for ( var y = 0; y < len2; y++ ) {

					var ribbon_sub_menu = ribbon_sub_menu_array[y];

					var sub_menu_ui_node = $( '<li><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</sapn></div></li>' );

					if ( ribbon_sub_menu.get( 'type' ) === RibbonSubMenuType.NAVIGATION ) {

						if ( ribbon_sub_menu.get( 'items' ).length > 0 ) {
							sub_menu_ui_nodes.append( sub_menu_ui_node );
							sub_menu_ui_node.children().eq( 0 ).addClass( 'ribbon-sub-menu-nav-icon' );
							$this.subMenuNavMap[ribbon_sub_menu.get( 'id' )] = ribbon_sub_menu;

							sub_menu_ui_node.click( function( e ) {
								var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
								$this.onSubMenuNavClick( this, id );
							} );
						}

					} else {

						sub_menu_ui_nodes.append( sub_menu_ui_node );

						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onSubMenuClick( id );
						} );
					}

//					  sub_menu_ui_node.click( function( e ) {
//						  var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
//						  $this.onSubMenuClick( id );
//					  } );
				}

				//If there is any menu
				if ( sub_menu_ui_nodes.children().length > 0 ) {
					ribbon_menu_group_ui.append( sub_menu_ui_nodes );
					ribbon_menu_group_ui.append( $( '<div class="menu-bottom"><span class="menu-bottom-span">' + ribbon_menu_group.get( 'label' ) + '</span></div>' ) );
					ribbon_menu_ui.find( '.ribbon-sub-menu' ).append( ribbon_menu_group_ui );
				}

			}

			if ( ribbon_menu_ui.find( '.ribbon-sub-menu' ).children().length > 0 ) {
				ribbon_menu_label_node.append( $( '<li><a ref="' + ribbon_menu.get( 'id' ) + '" href="#' + ribbon_menu.get( 'id' ) + '">' + ribbon_menu.get( 'label' ) + '</a></li>' ) );
				ribbon_menu_root_node.append( ribbon_menu_ui );
			}

		}

		this.setRibbonMenuVisibility()

	},

	setRibbonMenuVisibility: function() {
		// Set Employee tab visibility

		var tab_array = ['companyMenu', 'employeeMenu', 'payrollMenu'];

		var len = tab_array.length;

		for ( var i = 0; i < len; i++ ) {
			var menu_id = tab_array[i];

			var tab_content = Global.topContainer().find( '#' + menu_id ).find( 'li' );
			if ( tab_content.length < 1 ) {
				var tab = Global.topContainer().find( "a[ref='" + menu_id + "']" );
				tab.parent().hide();
			}
		}

//		  // Set COmpany tab visibility
//		  var employee_tab_content = Global.topContainer().find('#employeeMenu ' ).find('li');
//		  if(employee_tab_content.length < 1){
//			  var employee_tab = Global.topContainer().find("a[ref='employeeMenu']" );
//			  employee_tab.parent().hide();
//		  }

	},

	render: function() {

		this.buildRibbonMenus();

		$( this.el ).tabs();

		$( this.el ).bind( 'tabsselect', this.onMenuSelect );

		this.setSelectMenu( TopMenuManager.selected_menu_id );

		this.setSelectSubMenu( TopMenuManager.selected_sub_menu_id );

		if ( LocalCacheData.loginData.is_application_branded ) {
			$( '#leftLogo' ).attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/images/logo1.png' ) );
		} else {
			$( '#leftLogo' ).attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/images/logo.png' ) );
		}

//		$( '#rightLogo' ).load( function() {
//
//		} );

		$( '#rightLogo' ).attr( 'src', ServiceCaller.companyLogo + '&t=' + new Date().getTime() );

	},

	setSelectMenu: function( name ) {

		$( this.el ).tabs( {selected: name} );
		TopMenuManager.selected_menu_id = name;
	},

	openSelectView: function( name ) {
		switch ( name ) {
			case 'ImportCSV':
				IndexViewController.openWizard( 'ImportCSVWizard', null, function() {
					LocalCacheData.current_open_primary_controller.search();
				} );
				break;
			case 'QuickStartWizard':
				IndexViewController.openWizard( 'QuickStartWizard' );
				break;
			case 'InOut':
			case 'UserDefault':
			case 'Company':
			case 'CompanyBankAccount':
			case 'LoginUserContact':
			case 'LoginUserBankAccount':
			case 'LoginUserPreference':
			case 'ChangePassword':
			case 'InvoiceConfig':
			case 'About':
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, name );
				break;
			case 'Logout':
				this.doLogout();
				break;
			case 'PortalLogout':
				this.doPortalLogout();
				break;
			case 'AdminGuide':
				var url = 'http://www.timetrex.com/h.php?id=admin_guide&v=' + LocalCacheData.getLoginData().application_version;
				window.open( url, '_blank' );
				break;
			case 'FAQS':
				url = 'http://www.timetrex.com/h.php?id=faq&v=' + LocalCacheData.getLoginData().application_version;
				window.open( url, '_blank' );
				break;
			case 'WhatsNew':
				url = 'http://www.timetrex.com/h.php?id=changelog&v=' + LocalCacheData.getLoginData().application_version;
				window.open( url, '_blank' );
				break;
			case 'EmailHelp':

				if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
					url = 'mailto:support@timetrex.com?subject=Company: ' + LocalCacheData.getCurrentCompany().name + '&body=Company: ' + LocalCacheData.getCurrentCompany().name + '  ' +
					'Registration Key: ' + LocalCacheData.getLoginData().registration_key;
				} else {
					url = 'http://www.timetrex.com/r.php?id=29';
				}

				window.open( url, '_blank' );
				break;
			case 'ProcessPayrollWizard':
				IndexViewController.openWizard( 'ProcessPayrollWizard', null, function() {
					LocalCacheData.current_open_primary_controller.search();
				} );
				break;
			default:
				TopMenuManager.goToView( TopMenuManager.selected_sub_menu_id );
		}
	},

	setSelectSubMenu: function( name ) {
		switch ( name ) {
			case 'InOut':
			case 'UserDefault':
			case 'Company':
			case 'CompanyBankAccount':
			case 'LoginUserContact':
			case 'LoginUserBankAccount':
			case 'ImportCSV':
			case 'QuickStartWizard':
			case 'InvoiceConfig':
			case 'LoginUserPreference':
				break;
			case 'Logout':
				break;
			case 'AdminGuide':
				break;
			case 'FAQS':
				break;
			case 'WhatsNew':
				break;
			case 'EmailHelp':
				break;
			case 'ProcessPayrollWizard':
				break;
			default:
				if ( TopMenuManager.selected_sub_menu_id ) {

					try {
						$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
					} catch ( e ) {
						TopMenuManager.selected_sub_menu_id = '';
						TopMenuManager.selected_menu_id = '';
						TAlertManager.showAlert( $.i18n._( 'Invalid view name' ) );
						return;
					}

				}

				$( '#' + name ).addClass( 'selected-menu' );
				TopMenuManager.selected_sub_menu_id = name;

		}

	},

	doPortalLogout: function() {
		var current_user_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();

		current_user_api.Logout( {
			onResult: function( result ) {

				$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );
				LocalCacheData.current_open_view_id = ''; //#1528  -  Logout icon not working.
				TopMenuManager.goToView( 'PortalLogin' );

			}
		} )
	},

	doLogout: function() {

		var current_user_api = new (APIFactory.getAPIClass( 'APICurrentUser' ))();

		current_user_api.Logout( {
			onResult: function( result ) {

				$.cookie( 'SessionID', null, {expires: 30, path: LocalCacheData.cookie_path} );
				LocalCacheData.current_open_view_id = ''; //#1528  -  Logout icon not working.
				TopMenuManager.goToView( 'Login' );

			}
		} )
	}

} );

RibbonViewController.loadView = function() {
	Global.topContainer().css( 'display', 'block' );

//	Global.addCss( 'global/widgets/ribbon/RibbonView.css' );

	var result = Global.loadPageSync( 'global/widgets/ribbon/RibbonView.html' );
	var template = _.template( result );

	Global.topContainer().html( template );

}
