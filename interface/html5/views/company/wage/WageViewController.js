WageViewController = BaseViewController.extend({

    el: '#wage_view_container', //Must set el here and can only set string, so events can work

    user_api: null,
    user_group_api: null,
    company_api: null,
    currency_api: null,

    type_array: null,
    status_array: null,
    sex_array: null,
    user_group_array: null,
    country_array: null,
    province_array: null,

    e_province_array: null,

    currency: '',
    code: '',

    initialize: function() {

        if (Global.isSet(this.options.sub_view_mode)) {
            this.sub_view_mode = this.options.sub_view_mode;
        }

        this._super('initialize');
        this.edit_view_tpl = 'WageEditView.html';
        this.permission_id = 'wage';
        this.script_name = 'WageView';
        this.viewId = 'Wage';
        this.table_name_key = 'user_wage';
        this.context_menu_name = $.i18n._('Wage');
        this.navigation_label = $.i18n._('Wage') + ':';
        this.document_object_type_id = 110;
        this.api = new (APIFactory.getAPIClass('APIUserWage'))();
        this.user_group_api = new (APIFactory.getAPIClass('APIUserGroup'))();
        this.company_api = new (APIFactory.getAPIClass('APICompany'))();
        this.user_api = new (APIFactory.getAPIClass('APIUser'))();
        this.currency_api = new (APIFactory.getAPIClass('APICurrency'))();
        this.render();

        if (this.sub_view_mode) {
            this.buildContextMenu(true);
        } else {
            this.buildContextMenu();
        }

        //call init data in parent view
        if (!this.sub_view_mode) {
            this.initData();
        }

        this.setSelectRibbonMenuIfNecessary();

    },

    initOptions: function() {
        var $this = this;

        this.initDropDownOption('type');
        this.initDropDownOption('status', '', this.user_api);
        this.initDropDownOption('country', 'country', this.company_api);

        this.user_group_api.getUserGroup('', false, false, {onResult: function(res) {
            res = res.getResult();

            res = Global.buildTreeRecord(res);
            $this.user_group_array = res;

            if (!$this.sub_view_mode) {
                $this.adv_search_field_ui_dic['group_id'].setSourceData(res);
            }

        }});

    },

    buildContextMenuModels: function() {
        //Context Menu
        var menu = new RibbonMenu({
            label: this.context_menu_name,
            id: this.viewId + 'ContextMenu',
            sub_menu_groups: []
        });

        //menu group
        var editor_group = new RibbonSubMenuGroup({
            label: $.i18n._('Editor'),
            id: this.viewId + 'Editor',
            ribbon_menu: menu,
            sub_menus: []
        });

        var other_group = new RibbonSubMenuGroup({
            label: $.i18n._('Other'),
            id: this.viewId + 'other',
            ribbon_menu: menu,
            sub_menus: []
        });

        var add = new RibbonSubMenu({
            label: $.i18n._('New'),
            id: ContextMenuIconName.add,
            group: editor_group,
            icon: Icons.new_add,
            permission_result: true,
            permission: null
        });

        var view = new RibbonSubMenu({
            label: $.i18n._('View'),
            id: ContextMenuIconName.view,
            group: editor_group,
            icon: Icons.view,
            permission_result: true,
            permission: null
        });

        var edit = new RibbonSubMenu({
            label: $.i18n._('Edit'),
            id: ContextMenuIconName.edit,
            group: editor_group,
            icon: Icons.edit,
            permission_result: true,
            permission: null
        });

        var mass_edit = new RibbonSubMenu({
            label: $.i18n._('Mass<br>Edit'),
            id: ContextMenuIconName.mass_edit,
            group: editor_group,
            icon: Icons.mass_edit,
            permission_result: true,
            permission: null
        });

        var del = new RibbonSubMenu({
            label: $.i18n._('Delete'),
            id: ContextMenuIconName.delete_icon,
            group: editor_group,
            icon: Icons.delete_icon,
            permission_result: true,
            permission: null
        });

        var delAndNext = new RibbonSubMenu({
            label: $.i18n._('Delete<br>& Next'),
            id: ContextMenuIconName.delete_and_next,
            group: editor_group,
            icon: Icons.delete_and_next,
            permission_result: true,
            permission: null
        });

        var copy = new RibbonSubMenu({
            label: $.i18n._('Copy'),
            id: ContextMenuIconName.copy,
            group: editor_group,
            icon: Icons.copy_as_new,
            permission_result: true,
            permission: null
        });

        var copy_as_new = new RibbonSubMenu({
            label: $.i18n._('Copy<br>as New'),
            id: ContextMenuIconName.copy_as_new,
            group: editor_group,
            icon: Icons.copy,
            permission_result: true,
            permission: null
        });

        var save = new RibbonSubMenu({
            label: $.i18n._('Save'),
            id: ContextMenuIconName.save,
            group: editor_group,
            icon: Icons.save,
            permission_result: true,
            permission: null
        });

        var save_and_continue = new RibbonSubMenu({
            label: $.i18n._('Save<br>& Continue'),
            id: ContextMenuIconName.save_and_continue,
            group: editor_group,
            icon: Icons.save_and_continue,
            permission_result: true,
            permission: null
        });

        var save_and_next = new RibbonSubMenu({
            label: $.i18n._('Save<br>& Next'),
            id: ContextMenuIconName.save_and_next,
            group: editor_group,
            icon: Icons.save_and_next,
            permission_result: true,
            permission: null
        });

        var save_and_copy = new RibbonSubMenu({
            label: $.i18n._('Save<br>& Copy'),
            id: ContextMenuIconName.save_and_copy,
            group: editor_group,
            icon: Icons.save_and_copy,
            permission_result: true,
            permission: null
        });

        var save_and_new = new RibbonSubMenu({
            label: $.i18n._('Save<br>& New'),
            id: ContextMenuIconName.save_and_new,
            group: editor_group,
            icon: Icons.save_and_new,
            permission_result: true,
            permission: null
        });

        var cancel = new RibbonSubMenu({
            label: $.i18n._('Cancel'),
            id: ContextMenuIconName.cancel,
            group: editor_group,
            icon: Icons.cancel,
            permission_result: true,
            permission: null
        });

        var import_csv = new RibbonSubMenu({
            label: $.i18n._('Import'),
            id: ContextMenuIconName.import_icon,
            group: other_group,
            icon: Icons.import_icon,
            permission_result: PermissionManager.checkTopLevelPermission('ImportCSVWage'),
            permission: null
        });

        return [menu];

    },

    onFormItemChange: function(target, doNotValidate) {

        this.setIsChanged(target);
        this.setMassEditingFieldsWhenFormChange(target);
        var key = target.getField();
        var c_value = target.getValue();
        this.current_edit_record[key] = c_value;

        switch (key) {
            case 'type_id':
                this.onTypeChange(true);
                break;
            case 'user_id':
                this.setCurrency();
                break;
            case 'wage':
            case 'weekly_time':
                this.getHourlyRate();
                break;
        }

        LocalCacheData.debuger = this.current_edit_record;
        if (!doNotValidate) {
            this.validate();
        }

    },
    /* jshint ignore:start */
    setDefaultMenu: function(doNotSetFocus) {


        //Error: Uncaught TypeError: Cannot read property 'length' of undefined in https://ondemand2001.timetrex.com/interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
        if (!this.context_menu_array) {
            return;
        }

        if (!Global.isSet(doNotSetFocus) || !doNotSetFocus) {
            this.selectContextMenu();
        }

        this.setTotalDisplaySpan();


        var len = this.context_menu_array.length;

        var grid_selected_id_array = this.getGridSelectIdArray();

        var grid_selected_length = grid_selected_id_array.length;

        for (var i = 0; i < len; i++) {
            var context_btn = this.context_menu_array[i];
            var id = $(context_btn.find('.ribbon-sub-menu-icon')).attr('id');
            context_btn.removeClass('invisible-image');
            context_btn.removeClass('disable-image');

            switch (id) {
                case ContextMenuIconName.add:
                    this.setDefaultMenuAddIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.edit:
                    this.setDefaultMenuEditIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.view:
                    this.setDefaultMenuViewIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.mass_edit:
                    this.setDefaultMenuMassEditIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.copy:
                    this.setDefaultMenuCopyIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.delete_icon:
                    this.setDefaultMenuDeleteIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.delete_and_next:
                    this.setDefaultMenuDeleteAndNextIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.save:
                    this.setDefaultMenuSaveIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.save_and_next:
                    this.setDefaultMenuSaveAndNextIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.save_and_continue:
                    this.setDefaultMenuSaveAndContinueIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.save_and_new:
                    this.setDefaultMenuSaveAndAddIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.save_and_copy:
                    this.setDefaultMenuSaveAndCopyIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.copy_as_new:
                    this.setDefaultMenuCopyAsNewIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.login:
                    this.setDefaultMenuLoginIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.cancel:
                    this.setDefaultMenuCancelIcon(context_btn, grid_selected_length);
                    break;
                case ContextMenuIconName.import_icon:
                    this.setDefaultMenuImportIcon(context_btn, grid_selected_length);
                    break;
            }

        }

        this.setContextMenuGroupVisibility();

    },

    /* jshint ignore:end */

    //Make sure this.current_edit_record is updated before validate
    validate: function() {
        var $this = this;

        var record = {};

        if (this.is_mass_editing) {
            for (var key in this.edit_view_ui_dic) {
                var widget = this.edit_view_ui_dic[key];

                if (Global.isSet(widget.isChecked)) {
                    if (widget.isChecked() && widget.getEnabled()) {
                        record[key] = widget.getValue();
                    }
                }
            }

        } else {
            record = Global.clone(this.current_edit_record);
            record.user_id = false;
        }

        this.api['validate' + this.api.key_name](record, {onResult: function(result) {

            $this.validateResult(result);

        }});
    },

    setDefaultMenuImportIcon: function(context_btn, grid_selected_length, pId) {

    },

    onContextMenuClick: function(context_btn, menu_name) {

        this._super('onContextMenuClick', context_btn, menu_name);

        var id;

        if (Global.isSet(menu_name)) {
            id = menu_name;
        } else {
            context_btn = $(context_btn);

            id = $(context_btn.find('.ribbon-sub-menu-icon')).attr('id');

            if (context_btn.hasClass('disable-image')) {
                return;
            }
        }

        switch (id) {
            case ContextMenuIconName.import_icon:
                ProgressBar.showOverlay();
                this.onImportClick();
                break;

        }
    },

    onImportClick: function() {
        var $this = this;

        IndexViewController.openWizard('ImportCSVWizard', 'userwage', function() {
            $this.search();
        });
    },

    onSaveClick: function() {

        var $this = this;
        var record;
        LocalCacheData.current_doing_context_action = 'save';
        if (this.is_mass_editing) {

            var checkFields = {};
            for (var key in this.edit_view_ui_dic) {
                var widget = this.edit_view_ui_dic[key];

                if (Global.isSet(widget.isChecked)) {
                    if (widget.isChecked()) {
                        checkFields[key] = widget.getValue();
                    }
                }
            }

            record = [];
            $.each(this.mass_edit_record_ids, function(index, value) {
                var commonRecord = Global.clone(checkFields);
                commonRecord.id = value;
                record.push(commonRecord);

            });
        } else {
            if (Global.isArray(this.current_edit_record.user_id) && this.current_edit_record.user_id.length > 0) {
                record = [];
                $.each(this.current_edit_record.user_id, function(index, value) {

                    var commonRecord = Global.clone($this.current_edit_record);
                    commonRecord.user_id = value;
                    record.push(commonRecord);

                });
            } else {
                record = this.current_edit_record;
            }

        }

        this.api['set' + this.api.key_name](record, {onResult: function(result) {

            if (result.isValid()) {
                var result_data = result.getResult();
                if (result_data === true) {
                    $this.refresh_id = $this.current_edit_record.id;
                } else if (result_data > 0) {
                    $this.refresh_id = result_data;
                }
                $this.search();
                $this.current_edit_record = null;
                $this.removeEditView();

            } else {
                $this.setErrorTips(result);
                $this.setErrorMenu();
            }

        }});
    },

    removeEditView: function() {

        this._super('removeEditView');
        this.sub_document_view_controller = null;

    },

    setEditMenuSaveAndContinueIcon: function(context_btn, pId) {
        this.saveAndContinueValidate(context_btn);

        if (!this.current_edit_record || !this.current_edit_record.id) {
            context_btn.addClass('disable-image');
        }
    },

    setEditMenuSaveAndAddIcon: function(context_btn, pId) {
        this.saveAndNewValidate(context_btn);

        if (!this.current_edit_record || !this.current_edit_record.id) {
            context_btn.addClass('disable-image');
        }
    },

    setEditMenuSaveAndCopyIcon: function(context_btn, pId) {
        this.saveAndContinueValidate(context_btn);

        if (!this.current_edit_record || !this.current_edit_record.id) {
            context_btn.addClass('disable-image');
        }
    },

    setEditViewData: function() {
        var $this = this;
        this._super('setEditViewData'); //Set Navigation
        this.setCurrency();

        if (!this.sub_view_mode) {
            var widget = $this.edit_view_ui_dic['user_id'];
            if (( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing) {

                widget.setAllowMultipleSelection(true);

            } else {
                widget.setAllowMultipleSelection(false);
            }
        }

        $this.onTypeChange(false);

    },

    setCurrency: function() {

        var $this = this;
        if (Global.isSet(this.current_edit_record.user_id)) {
            var filter = {};
            filter.filter_data = {user_id: this.current_edit_record.user_id};

            this.currency_api.getCurrency(filter, false, false, {onResult: function(res) {
                res = res.getResult();
                if (Global.isArray(res)) {
                    $this.currency.text(res[0].symbol);
                    $this.code.text(res[0].iso_code);
                } else {
                    $this.currency.text('');
                    $this.code.text('');
                }

            }});
        }
    },

    getHourlyRate: function() {

        var $this = this;
        if (this.current_edit_record.wage &&
            this.current_edit_record.weekly_time &&
            this.current_edit_record.type_id &&
            this.current_edit_record.type_id !== 10) {


            //wwkly_time need value before pasrse to seconds.
            this.api.getHourlyRate(this.current_edit_record.wage, $this.edit_view_ui_dic['weekly_time'].getInputValue(), this.current_edit_record.type_id, {onResult: function(result) {
                var res = result.getResult();

                if (result.isValid()) {
                    $this.current_edit_record.hourly_rate = res;
                    $this.edit_view_ui_dic['hourly_rate'].val(res);
                }

            }});
        }

    },

    onTypeChange: function(getRate) {

        if (parseInt(this.current_edit_record.type_id) !== 10) {
            this.edit_view_form_item_dic['weekly_time'].css('display', 'block');
            this.edit_view_form_item_dic['hourly_rate'].css('display', 'block');

            if (getRate) {
                this.getHourlyRate();
            }

        } else {
            this.edit_view_form_item_dic['weekly_time'].css('display', 'none');
            this.edit_view_form_item_dic['hourly_rate'].css('display', 'none');
        }
    },

    buildEditViewUI: function() {

        this._super('buildEditViewUI');

        var $this = this;

        this.setTabLabels({
            'tab_wage': $.i18n._('Wage'),
            'tab_attachment': $.i18n._('Attachment'),
            'tab_audit': $.i18n._('Audit')
        });

        this.navigation.AComboBox({
            api_class: (APIFactory.getAPIClass('APIUserWage')),
            id: this.script_name + '_navigation',
            allow_multiple_selection: false,
            layout_name: ALayoutIDs.WAGE,
            show_search_inputs: true,
            navigation_mode: true
        });

        this.setNavigation();

        //Tab 0 start

        var tab_wage = this.edit_view_tab.find('#tab_wage');

        var tab_wage_column1 = tab_wage.find('.first-column');

        var form_item_input;

        if (!this.sub_view_mode) {
            //Employee

            form_item_input = Global.loadWidgetByName(FormItemType.AWESOME_BOX);
            form_item_input.AComboBox({
                api_class: (APIFactory.getAPIClass('APIUser')),
                allow_multiple_selection: false,
                layout_name: ALayoutIDs.USER,
                show_search_inputs: true,
                set_empty: true,
                field: 'user_id'

            });

            var default_args = {};
            default_args.permission_section = 'user_wage';
            form_item_input.setDefaultArgs(default_args);
            this.addEditFieldToColumn($.i18n._('Employee'), form_item_input, tab_wage_column1, '');
        }

        //Wage Group

        form_item_input = Global.loadWidgetByName(FormItemType.AWESOME_BOX);
        form_item_input.AComboBox({
            api_class: (APIFactory.getAPIClass('APIWageGroup')),
            allow_multiple_selection: false,
            layout_name: ALayoutIDs.WAGE_GROUP,
            show_search_inputs: true,
            set_default: true,
            field: 'wage_group_id'
        });

        if (this.sub_view_mode) {
            this.addEditFieldToColumn($.i18n._('Wage Group'), form_item_input, tab_wage_column1, '');
        } else {
            this.addEditFieldToColumn($.i18n._('Wage Group'), form_item_input, tab_wage_column1);
        }

        //Type
        form_item_input = Global.loadWidgetByName(FormItemType.COMBO_BOX);

        form_item_input.TComboBox({field: 'type_id'});
        form_item_input.setSourceData(Global.addFirstItemToArray($this.type_array));
        this.addEditFieldToColumn($.i18n._('Type'), form_item_input, tab_wage_column1);

        //Wage

        form_item_input = Global.loadWidgetByName(FormItemType.TEXT_INPUT);

        form_item_input.TTextInput({field: 'wage', width: 90});

        var widgetContainer = $("<div class='widget-h-box'></div>");
        this.currency = $("<span class='widget-left-label'></span>");
        this.code = $("<span class='widget-right-label'></span>");

        widgetContainer.append(this.currency);
        widgetContainer.append(form_item_input);
        widgetContainer.append(this.code);

        this.addEditFieldToColumn($.i18n._('Wage'), form_item_input, tab_wage_column1, '', widgetContainer);

        //Average Time / Week

        form_item_input = Global.loadWidgetByName(FormItemType.TEXT_INPUT);
        form_item_input.TTextInput({field: 'weekly_time', need_parser_sec: true});

        widgetContainer = $("<div class='widget-h-box'></div>");
        var label = $("<span class='widget-right-label'>( " + $.i18n._('ie') + ': ' + $.i18n._('40 hours / week') + " )</span>");

        widgetContainer.append(form_item_input);
        widgetContainer.append(label);

        this.addEditFieldToColumn($.i18n._('Average Time / Week'), form_item_input, tab_wage_column1, '', widgetContainer, true);

        //Annual Hourly Rate

        form_item_input = Global.loadWidgetByName(FormItemType.TEXT_INPUT);
        form_item_input.TTextInput({field: 'hourly_rate'});

        this.addEditFieldToColumn($.i18n._('Annual Hourly Rate'), form_item_input, tab_wage_column1, '', null, true);

        //Labor Burden Percent

        form_item_input = Global.loadWidgetByName(FormItemType.TEXT_INPUT);
        form_item_input.TTextInput({field: 'labor_burden_percent', width: 50});

        widgetContainer = $("<div class='widget-h-box'></div>");
        label = $("<span class='widget-right-label'>% ( " + $.i18n._('ie') + ': ' + $.i18n._('25% burden') + " )</span>");

        widgetContainer.append(form_item_input);
        widgetContainer.append(label);

        this.addEditFieldToColumn($.i18n._('Labor Burden Percent'), form_item_input, tab_wage_column1, '', widgetContainer);

        //Effective Date
        form_item_input = Global.loadWidgetByName(FormItemType.DATE_PICKER);

        form_item_input.TDatePicker({field: 'effective_date', width: 120});
        this.addEditFieldToColumn($.i18n._('Effective Date'), form_item_input, tab_wage_column1);

        //Note
        form_item_input = Global.loadWidgetByName(FormItemType.TEXT_AREA);
        form_item_input.TTextArea({field: 'note', width: 389, height: 117 });
        this.addEditFieldToColumn($.i18n._('Note'), form_item_input, tab_wage_column1, '', null, null, true);

    },

    //Override for: Do not show first 2 columns in sub wage view
    setSelectLayout: function() {

        if (this.sub_view_mode) {
            this._super('setSelectLayout', 2);
        } else {
            this._super('setSelectLayout');
        }

    },

    initCountryList: function() {

    },

    setProvince: function(val, m) {
        var $this = this;

        if (!val || val === '-1' || val === '0') {
            $this.province_array = [];
            this.adv_search_field_ui_dic['province'].setSourceData([]);
        } else {
            this.company_api.getOptions('province', val, {onResult: function(res) {
                res = res.getResult();
                if (!res) {
                    res = [];
                }

                $this.province_array = Global.buildRecordArray(res);
                $this.adv_search_field_ui_dic['province'].setSourceData($this.province_array);

            }});
        }
    },

    eSetProvince: function(val) {
        var $this = this;
        var province_widget = $this.edit_view_ui_dic['province'];

        if (!val || val === '-1' || val === '0') {
            $this.e_province_array = [];
            province_widget.setSourceData([]);
        } else {
            this.company_api.getOptions('province', val, {onResult: function(res) {
                res = res.getResult();
                if (!res) {
                    res = [];
                }

                $this.e_province_array = Global.buildRecordArray(res);
                province_widget.setSourceData($this.e_province_array);

            }});
        }
    },

    onSetSearchFilterFinished: function() {

        if (this.search_panel.getSelectTabIndex() === 1) {
            var combo = this.adv_search_field_ui_dic['country'];
            var select_value = combo.getValue();
            this.setProvince(select_value);
        }

    },

    onBuildBasicUIFinished: function() {
        var basicSearchTabPanel = this.search_panel.find('div #basic_search');
    },

    onBuildAdvUIFinished: function() {

        this.adv_search_field_ui_dic['country'].change($.proxy(function() {
            var combo = this.adv_search_field_ui_dic['country'];
            var selectVal = combo.getValue();

            this.setProvince(selectVal);

            this.adv_search_field_ui_dic['province'].setValue(null);

        }, this));
    },

    events: {

    },

    buildSearchFields: function() {

        this._super('buildSearchFields');

        var default_args = {permission_section: 'user_wage'};
        this.search_fields = [
            new SearchField({label: $.i18n._('Employee'),
                in_column: 1,
                field: 'user_id',
                layout_name: ALayoutIDs.USER,
                default_args: default_args,
                api_class: (APIFactory.getAPIClass('APIUser')),
                multiple: true,
                basic_search: true,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Type'),
                in_column: 1,
                field: 'type_id',
                multiple: true,
                basic_search: true,
                adv_search: true,
                layout_name: ALayoutIDs.OPTION_COLUMN,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Wage Group'),
                in_column: 1,
                field: 'wage_group_id',
                layout_name: ALayoutIDs.WAGE_GROUP,
                api_class: (APIFactory.getAPIClass('APIWageGroup')),
                multiple: true,
                basic_search: true,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Status'),
                in_column: 1,
                field: 'status_id',
                multiple: true,
                basic_search: false,
                adv_search: true,
                layout_name: ALayoutIDs.OPTION_COLUMN,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Group'),
                in_column: 1,
                multiple: true,
                field: 'group_id',
                layout_name: ALayoutIDs.TREE_COLUMN,
                tree_mode: true,
                basic_search: false,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Created By'),
                in_column: 2,
                field: 'created_by',
                layout_name: ALayoutIDs.USER,
                api_class: (APIFactory.getAPIClass('APIUser')),
                multiple: true,
                basic_search: true,
                adv_search: true,
                script_name: 'EmployeeView',
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Updated By'),
                in_column: 2,
                field: 'updated_by',
                layout_name: ALayoutIDs.USER,
                api_class: (APIFactory.getAPIClass('APIUser')),
                multiple: true,
                basic_search: true,
                adv_search: true,
                script_name: 'EmployeeView',
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Default Branch'),
                in_column: 2,
                field: 'default_branch_id',
                layout_name: ALayoutIDs.BRANCH,
                api_class: (APIFactory.getAPIClass('APIBranch')),
                multiple: true,
                basic_search: false,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),
            new SearchField({label: $.i18n._('Default Department'),
                field: 'default_department_id',
                in_column: 2,
                layout_name: ALayoutIDs.DEPARTMENT,
                api_class: (APIFactory.getAPIClass('APIDepartment')),
                multiple: true,
                basic_search: false,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),

            new SearchField({label: $.i18n._('Title'),
                field: 'title_id',
                in_column: 2,
                layout_name: ALayoutIDs.JOB_TITLE,
                api_class: (APIFactory.getAPIClass('APIUserTitle')),
                multiple: true,
                basic_search: false,
                adv_search: true,
                form_item_type: FormItemType.AWESOME_BOX}),
            new SearchField({label: $.i18n._('Country'),
                in_column: 3,
                field: 'country',
                multiple: true,
                basic_search: false,
                adv_search: true,
                layout_name: ALayoutIDs.OPTION_COLUMN,
                form_item_type: FormItemType.COMBO_BOX}),
            new SearchField({label: $.i18n._('Province/State'),
                in_column: 3,
                field: 'province',
                multiple: true,
                basic_search: false,
                adv_search: true,
                layout_name: ALayoutIDs.OPTION_COLUMN,
                form_item_type: FormItemType.AWESOME_BOX})

        ];
    },

    cleanWhenUnloadView: function(callBack) {

        $('#wage_view_container').remove();
        this._super('cleanWhenUnloadView', callBack);

    },

    initTabData: function() {
        //Handle most case that one tab and one audit tab
        if (this.edit_view_tab.tabs('option', 'selected') === 1) {

            if (this.current_edit_record.id) {
                this.edit_view_tab.find('#tab_attachment').find('.first-column-sub-view').css('display', 'block');
                this.initSubDocumentView();
            } else {
                this.edit_view_tab.find('#tab_attachment').find('.first-column-sub-view').css('display', 'none');
                this.edit_view.find('.save-and-continue-div').css('display', 'block');
            }

        } else if (this.edit_view_tab.tabs('option', 'selected') === 2) {
            if (this.current_edit_record.id) {
                this.edit_view_tab.find('#tab_audit').find('.first-column-sub-view').css('display', 'block');
                this.initSubLogView('tab_audit');
            } else {
                this.edit_view_tab.find('#tab_audit').find('.first-column-sub-view').css('display', 'none');
                this.edit_view.find('.save-and-continue-div').css('display', 'block');
            }
        }
    },

    onTabShow: function(e, ui) {
        var key = this.edit_view_tab_selected_index;
        this.editFieldResize(key);

        if (!this.current_edit_record) {
            return;
        }

        if (this.edit_view_tab_selected_index === 1) {

            if (this.current_edit_record.id) {
                this.edit_view_tab.find('#tab_attachment').find('.first-column-sub-view').css('display', 'block');
                this.initSubDocumentView();
            } else {
                this.edit_view_tab.find('#tab_attachment').find('.first-column-sub-view').css('display', 'none');
                this.edit_view.find('.save-and-continue-div').css('display', 'block');
            }

        } else if (this.edit_view_tab_selected_index === 2) {

            if (this.current_edit_record.id) {
                this.edit_view_tab.find('#tab_audit').find('.first-column-sub-view').css('display', 'block');
                this.initSubLogView('tab_audit');
            } else {

                this.edit_view_tab.find('#tab_audit').find('.first-column-sub-view').css('display', 'none');
                this.edit_view.find('.save-and-continue-div').css('display', 'block');
            }

        } else {
            this.buildContextMenu(true);
            this.setEditMenu();
        }

    },

    initSubDocumentView: function() {
        var $this = this;

        if (this.sub_document_view_controller) {
            this.sub_document_view_controller.buildContextMenu(true);
            this.sub_document_view_controller.setDefaultMenu();
            $this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
            $this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
            $this.sub_document_view_controller.initData();
            return;
        }

        Global.loadScriptAsync('views/document/DocumentViewController.js', function() {
            var tab_attachment = $this.edit_view_tab.find('#tab_attachment');
            var firstColumn = tab_attachment.find('.first-column-sub-view');
            Global.trackView('Sub' + 'Document' + 'View');
            DocumentViewController.loadSubView(firstColumn, beforeLoadView, afterLoadView);

        });

        function beforeLoadView() {

        }

        function afterLoadView(subViewController) {
            $this.sub_document_view_controller = subViewController;
            $this.sub_document_view_controller.parent_key = 'object_id';
            $this.sub_document_view_controller.parent_value = $this.current_edit_record.id;
            $this.sub_document_view_controller.document_object_type_id = $this.document_object_type_id;
            $this.sub_document_view_controller.parent_edit_record = $this.current_edit_record;
            $this.sub_document_view_controller.parent_view_controller = $this;
            $this.sub_document_view_controller.initData();
        }

    },

    setTabStatus: function() {
        //Handle most cases that one tab and on audit tab
        if (this.is_mass_editing) {

            $(this.edit_view_tab.find('ul li a[ref="tab_attachment"]')).parent().hide();
            $(this.edit_view_tab.find('ul li a[ref="tab_audit"]')).parent().hide();
            this.edit_view_tab.tabs('select', 0);

        } else {
            if (this.subDocumentValidate()) {
                $(this.edit_view_tab.find('ul li a[ref="tab_attachment"]')).parent().show();
            } else {
                $(this.edit_view_tab.find('ul li a[ref="tab_attachment"]')).parent().hide();
                this.edit_view_tab.tabs('select', 0);
            }
            if (this.subAuditValidate()) {
                $(this.edit_view_tab.find('ul li a[ref="tab_audit"]')).parent().show();
            } else {
                $(this.edit_view_tab.find('ul li a[ref="tab_audit"]')).parent().hide();
                this.edit_view_tab.tabs('select', 0);
            }

        }

        this.editFieldResize(0);
    }


});

WageViewController.loadView = function(container) {

    Global.loadViewSource('Wage', 'WageView.html', function(result) {

        var args = { };
        var template = _.template(result, args);

        if (Global.isSet(container)) {
            container.html(template);
        } else {
            Global.contentContainer().html(template);
        }

    });

};

WageViewController.loadSubView = function(container, beforeViewLoadedFun, afterViewLoadedFun) {

    Global.loadViewSource('Wage', 'SubWageView.html', function(result) {

        var args = { };
        var template = _.template(result, args);

        if (Global.isSet(beforeViewLoadedFun)) {
            beforeViewLoadedFun();
        }

        if (Global.isSet(container)) {
            container.html(template);

            if (Global.isSet(afterViewLoadedFun)) {
                afterViewLoadedFun(sub_wage_view_controller);
            }

        }

    });

};