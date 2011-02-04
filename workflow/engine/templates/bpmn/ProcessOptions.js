ProcessOptions=function(id){
Workflow.call(this,id);
};
ProcessOptions.prototype=new Workflow;
ProcessOptions.prototype.type="ProcessOptions";


/**
   * 'addDynaform' function that will allow adding new dynaforms and showing list of
   * dynaforms available
*/
ProcessOptions.prototype.addDynaform= function(_5625)
{
  var pro_uid = workflow.getUrlVars();

  var dynaFields = Ext.data.Record.create([
            { name: 'DYN_UID', type: 'string'},
            { name: 'DYN_TYPE', type: 'string'},
            { name: 'DYN_TITLE', type: 'string'},
            { name: 'DYN_DISCRIPTION',type: 'string'},
            {name: 'ACTION', type: 'string'}
            ]);

  var editor = new Ext.ux.grid.RowEditor({
            saveText: 'Update'
        });
  var btnAdd = new Ext.Button({
            id: 'btnAdd',
            text: 'New',
            iconCls: 'button_menu_ext ss_sprite ss_add',
            //iconCls: 'application_add',
            handler: function () {
                dynaformDetails.getForm().reset();
                formWindow.show();
            }
  });

  var btnRemove = new Ext.Button({
            id: 'btnRemove',
            text: 'Delete',
            iconCls: 'button_menu_ext ss_sprite ss_delete',
            handler: function (s) {
                editor.stopEditing();
                var s = dynaformGrid.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){

                    //First Deleting dynaform from Database using Ajax
                    var dynUID      = r.data.DYN_UID;

                    //if STEP_UID is properly defined (i.e. set to valid value) then only delete the row
                    //else its a BLANK ROW for which Ajax should not be called.
                    if(r.data.DYN_UID != "")
                    {
                        Ext.Ajax.request({
                          url   : '../dynaforms/dynaforms_Delete.php',
                          method: 'POST',
                          params: {
                                functions      : 'getDynaformAssign',
                                PRO_UID        : pro_uid,
                                DYN_UID        : dynUID
                          },
                          success: function(response) {
                             //First check whether selected Dynaform is assigned to a task steps or not.
                            //If response.responseText == 1 i.e it is assigned, => it cannot be deleted
                             if(response.responseText == "")
                              {
                               Ext.Ajax.request({
                                  url   : '../dynaforms/dynaforms_Delete.php',
                                  method: 'POST',
                                  params: {
                                        functions      : 'getRelationInfDynaform',
                                        DYN_UID        : dynUID
                                  },
                                  success: function(response) {
                                    //Second check whether selected Dynaform is assigned to a processes supervisors or not.
                                    //If response.responseText == 1 i.e it is assigned, => it cannot be deleted
                                    if(response.responseText == "")
                                      {
                                         Ext.Ajax.request({
                                          url   : '../dynaforms/dynaforms_Delete.php',
                                          method: 'POST',
                                          params: {
                                                functions      : 'deleteDynaform',
                                                DYN_UID        : dynUID
                                          },
                                          success: function(response) {
                                            Ext.MessageBox.alert ('Status','Dynaform has been removed successfully.');
                                            //Secondly deleting from Grid
                                            taskDynaform.remove(r);
                                            //Reloading store after deleting dynaform
                                            taskDynaform.reload();
                                          }
                                        });
                                      }
                                     else
                                       Ext.MessageBox.alert ('Status','Dynaform assigned to a process supervisors cannot be deleted.');
                                  }
                                });
                              }
                              else
                                Ext.MessageBox.alert ('Status','Dynaform assigned to a task steps cannot be deleted.');
                          }
                     });
                    }

                }
            }
        });

  var tb = new Ext.Toolbar({
            items: [btnAdd, btnRemove]
        });

  var taskDynaform = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : dynaFields,
            proxy        : new Ext.data.HttpProxy({
                           url: 'proxyExtjs?pid='+pro_uid+'&action=getDynaformList'
                           })
          });
 taskDynaform.load();

 //Creating store for getting list of additional PM tables
 var additionalTablesFields = Ext.data.Record.create([
            { name: 'ADD_TAB_UID', type: 'string'},
            { name: 'ADD_TAB_NAME', type: 'string'},
            { name: 'ADD_TAB_DESCRIPTION',type: 'string'}
            ]);

 var additionalTables = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : additionalTablesFields,
            proxy: new Ext.data.HttpProxy({
              url: 'proxyExtjs?action=getAdditionalTables'
            })
          });
 //additionalTables.load();

 //Creating store for getting list of Fields of additional PM tables
  var TablesFields = Ext.data.Record.create([
            { name: 'FLD_UID',type: 'string'},
            { name: 'FLD_NAME',type: 'string'},
            { name: 'FLD_DESCRIPTION',type: 'string'},
            { name: 'FLD_TYPE',type: 'string'}
            ]);

 var tablesFieldsStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : TablesFields,
            proxy: new Ext.data.HttpProxy({
              url: 'proxyDynaform'
            })
          });
 //tablesFieldsStore.load();

 // Renderer function
    function renderInstall(value, id, r)
    {

            var id = Ext.id();
            var uidbutton = createGridButton.defer(1, this, ['Install', id, r]);
            return uidbutton;
            //return('&lt;div id="' + id + '"&gt;&lt;/div&gt;');
            /*if (r.data.registered == false)
            {
                createGridButton.defer(1, this, ['Install', id, r]);
                return('&lt;div id="' + id + '"&gt;&lt;/div&gt;');
            }else
            {
                createGridButton.defer(1, this, ['ReInstall', id, r]);
                return('&lt;div id="' + id + '"&gt;&lt;/div&gt;');
            }*/

    }
    function createGridButton(value, id, record) {
        new Ext.Button({
            text: 'UID'
            ,iconCls: 'button_menu_ext ss_sprite ss_delete'
            ,handler : function(btn, e) {
                // do whatever you want here
            }
        });
    }

 var dynaformColumns = new Ext.grid.ColumnModel({
            columns: [
                new Ext.grid.RowNumberer(),
                    {
                        id: 'DYN_TITLE',
                        header: 'Title',
                        dataIndex: 'DYN_TITLE',
                        width: 280,
                        editable: false,
                        editor: new Ext.form.TextField({
                        allowBlank: false
                        })
                    },{
                        id: 'ACTION',
                        header: 'Type',
                        dataIndex: 'ACTION',
                        width: 280,
                        editable: false,
                        editor: new Ext.form.TextField({
                        allowBlank: false
                        })
                    },
                    {
                        sortable: false,
                        renderer: function(val, meta, record)
                           {
                                return String.format("<a href='../dynaforms/dynaforms_Editor?PRO_UID={0}&DYN_UID={1}' >Edit</a>",pro_uid,record.data.DYN_UID);
                           }
                    },
                    {
                        sortable: false,
                        renderer: function(val, meta, record)
                           {
                                return String.format("<input type='submit' name='UID' value='UID' onclick='renderInstall()'>",record.data.DYN_UID);
                           }
                    }
                ]
        });

 var addTableColumns = new Ext.grid.ColumnModel({
            columns: [
                new Ext.grid.RowNumberer(),
                    {
                        id: 'FLD_NAME',
                        header: 'Primary Key',
                        dataIndex: 'FLD_NAME',
                        width: 200,
                        editable: false,
                        sortable: true,
                        editor: {
                            xtype: 'textfield',
                            //allowBlank: false,
                            name      : 'FLD_NAME'
                        }
                    },{
                        id: 'PRO_VARIABLE',
                        header: 'Variables',
                        dataIndex: 'PRO_VARIABLE',
                        width: 200,
                        sortable: true,
                        editor: {
                            xtype: 'textfield',
                           // allowBlank: false,
                            name      : 'PRO_VARIABLE'
                        }
                    },{
                        sortable: false,
                        renderer: function(val){return '<input type="button" value="@@" id="'+val+'"/>';}
                    }
                ]
        });

 var dynaformGrid = new Ext.grid.GridPanel({
        store: taskDynaform,
        id : 'mygrid',
        loadMask: true,
        loadingText: 'Loading...',
        renderTo: 'cases-grid',
        frame: false,
        autoHeight:false,
        clicksToEdit: 1,
        minHeight:400,
        height   :400,
        layout: 'fit',
        cm: dynaformColumns,
        stripeRows: true,
        tbar: tb,
        viewConfig: {forceFit: true}
   });

 var dynaformDetails = new Ext.FormPanel({
        labelWidth: 100,
        buttonAlign: 'center',
        bodyStyle :'padding:5px 5px 0',
        width     : 490,
        monitorValid : true,
        autoHeight: true,
        items:
                [{
                    xtype: 'fieldset',
                    layout: 'fit',
                    border:true,
                    title: 'Please select the Dynaform Type',
                    width: 500,
                    collapsible: false,
                    labelAlign: 'top',
                    items:[{
                            xtype: 'radiogroup',
                            //id:    'dynaformType',
                            layout: 'fit',
                            fieldLabel: 'Type',
                            itemCls: 'x-check-group-alt',
                            columns: 1,
                            items: [
                                {
                                    boxLabel: 'Blank Dynaform',
                                    name: 'DYN_SOURCE',
                                    inputValue: 'blankDyna',
                                    checked: true
                                },

                                {
                                    boxLabel: 'PM Table Dynaform',
                                    name: 'DYN_SOURCE',
                                    inputValue: 'pmTableDyna'
                                }],
                            listeners: {
                            change: function(radiogroup, radio) {
                            if(radio.inputValue == 'blankDyna')
                                {
                                    Ext.get("blankDynaform").show();
                                    Ext.get("pmTableDynaform").hide();
                                }
                            else
                                {
                                    Ext.get("blankDynaform").hide();
                                    Ext.get("pmTableDynaform").show();
                                }
                        }
                         }
                     }]
                },
                {
                    xtype: 'fieldset',
                    id:    'blankDynaform',
                    border:true,
                    hidden: false,
                    title: 'Dynaform Information',
                    width: 500,
                    items:[{
                            xtype     : 'textfield',
                            fieldLabel: 'Title',
                            name      : 'DYN_TITLE',
                            width     : 350,
                            allowBlank: false
                         },{
                            width     : 350,
                            xtype:          'combo',
                            allowBlank: false,
                            mode:           'local',
                            editable:       false,
                            fieldLabel:     'Type',
                            triggerAction:  'all',
                            forceSelection: true,
                            name:           'ACTION',
                            displayField:   'name',
                            valueField:     'value',
                            value        : 'Normal',
                            store:          new Ext.data.JsonStore({
                                        fields : ['name', 'value'],
                                        data   : [
                                            {name : 'Normal',   value: 'Normal'},
                                            {name : 'Grid',   value: 'Grid'},
                                        ]
                                    })
                         },{
                            xtype     : 'textarea',
                            fieldLabel: 'Description',
                            name      : 'DYN_DESCRIPTION',
                            height    : 120,
                            width     : 350
                         }
                     ]
                },{
                    xtype: 'fieldset',
                    id:    'pmTableDynaform',
                    border:true,
                    hidden: true,
                    title: 'Dynaform Information',
                    width: 500,
                    items:[{
                            width:          150,
                            xtype:          'combo',
                            mode:           'local',
                            editable:       false,
                            triggerAction:  'all',
                            forceSelection: true,
                            fieldLabel:     'Create from a PM Table',
                            //name:           'ADD_TABLE',
                            displayField:   'ADD_TAB_NAME',
                            valueField:     'ADD_TAB_UID',
                            value        : '---------------------------',
                            store        : additionalTables,
                            onSelect: function(record, index){
                                var link = 'proxyExtjs?tabId='+record.data.ADD_TAB_UID+'&action=getPMTableDynaform';
                                tablesFieldsStore.proxy.setUrl(link, true);
                                tablesFieldsStore.load();

                                Ext.getCmp("fieldsGrid").show();
                                Ext.getCmp("pmTable").setValue(record.data.ADD_TAB_UID);

                                this.setValue(record.data[this.valueField || this.displayField]);
                                this.collapse();
                             }
                         },{
                            xtype:'hidden',//<--hidden field
                            name:'ADD_TABLE',
                            id  :'pmTable'
                         },
                         {
                            xtype     : 'textfield',
                            fieldLabel: 'Title',
                            name      : 'DYN_TITLE'
                           // allowBlank: false
                         },{
                            xtype     : 'textarea',
                            fieldLabel: 'Description',
                            name      : 'DYN_DESCRIPTION',
                            height    : 120,
                            width     : 350
                         },
                         {
                            xtype: 'grid',
                            id:'fieldsGrid',
                            hidden: true,
                            ds: tablesFieldsStore,
                            cm: addTableColumns,
                            width: 550,
                            //height: 300,
                            autoHeight: true,
                            plugins: [editor],
                            //loadMask    : true,
                            loadingText : 'Loading...',
                            border: true,
                            renderTo : Ext.getBody()
                         }
                     ]
                }
            ], buttons: [{
            text: 'Save',
            formBind    :true,
            handler: function(){
                var getForm   = dynaformDetails.getForm().getValues();
                //var sDynaType = getForm.DYN_SOURCE;
                if(getForm.DYN_SOURCE == 'blankDyna')
                    {
                        var sAction   = getForm.ACTION;
                        var sTitle    = getForm.DYN_TITLE[0];
                       var sDesc     = getForm.DYN_DESCRIPTION[0];
                    }
                else
                    {
                        var sAddTab     = getForm.ADD_TABLE;
                        var aStoreFields  = tablesFieldsStore.data.items;
                        var aData = '';
                        //Creating string in JSON format
                        for(var i=0;i<aStoreFields.length;i++)
                            {
                                var fName = aStoreFields[i].data.FLD_NAME;
                                var pVar  = aStoreFields[i].data.PRO_VARIABLE;
                                aData += '"FLD_NAME":"'+fName+'","PRO_VARIABLE":"'+pVar+'",';
                            }
                        var sData = '{'+aData.slice(0,aData.length-1)+'}';
                        sTitle    = getForm.DYN_TITLE[1];
                        sDesc     = getForm.DYN_DESCRIPTION[1];
                    }
                        Ext.Ajax.request({
                          url   : '../dynaforms/dynaforms_Save.php',
                          method: 'POST',
                          params:{
                              functions       : 'saveDynaform',
                              ACTION          : sAction,
                              FIELDS          : sData,
                              ADD_TABLE       : sAddTab,
                              PRO_UID         : pro_uid,
                              DYN_TITLE       : sTitle,
                              DYN_TYPE        : 'xmlform',
                              DYN_DESCRIPTION : sDesc
                          },
                          success: function(response) {
                              Ext.MessageBox.alert ('Status','Dynaform has been created successfully.');
                              taskDynaform.reload();
                              formWindow.hide()
                          }
                        });

            }

    },{
            text: 'Cancel',
            handler: function(){
                // when this button clicked,
                formWindow.hide();
            }
        }]

    });

 var gridWindow = new Ext.Window({
        title: 'Dynaform',
        autoScroll: true,
        collapsible: false,
        maximizable: true,
        width: 600,
        //autoHeight: true,
        height: 420,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        buttonAlign: 'center',
        items: dynaformGrid
    });

 var formWindow = new Ext.Window({
        title: 'Dynaform',
        autoScroll: true,
        collapsible: false,
        maximizable: true,
        width: 550,
        //autoHeight: true,
        //height: 500,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        buttonAlign: 'center',
        items: dynaformDetails
       
    });
   gridWindow.show();
}


ProcessOptions.prototype.dbConnection = function()
{
  var pro_uid = workflow.getUrlVars();
  //Database store code starts here

  var dbConnFields = Ext.data.Record.create([
            { name: 'DBS_UID',type: 'string'},
            { name: 'DBS_TYPE',type: 'string'},
            { name: 'DBS_SERVER',type: 'string'},
            { name: 'DBS_DATABASE_NAME',type: 'string'},
            { name: 'DBS_USERNAME',type: 'string'},
            { name: 'DBS_PASSWORD',type: 'string'},
            { name: 'DBS_PORT',type: 'string'},
            { name: 'DBS_DESCRIPTION',type: 'string'}
        ]);

  var btnNew = new Ext.Button({
            id: 'btnNew',
            text: 'New',
            iconCls: 'button_menu_ext ss_sprite ss_add',
            handler: function () {
                formWindow.show();
                dbconnForm.getForm().reset();
            }
  });

  var btnEdit = new Ext.Button({
            id: 'btnEdit',
            text: 'Edit',
            iconCls: 'button_menu_ext ss_sprite ss_pencil',
            handler: function (s) {
                var selectedRow = dbGrid.getSelectionModel().getSelections();
                var dbConnUID   = selectedRow[0].data.DBS_UID;
                dbconnForm.form.load({
                url:'proxyExtjs.php?pid='+pro_uid+'&dbs_uid='+dbConnUID+'&action=editDatabaseConnection',
                    method:'GET',
                    waitMsg:'Loading',
                    success:function(form, action) {
                        formWindow.show();
                      //Ext.MessageBox.alert('Message', 'Loaded OK');
                     // setTaskAssignType(form);
                    },
                    failure:function(form, action) {
                        Ext.MessageBox.alert('Message', 'Load failed');
                    }
                });
            }
  });

   var btnRemove = new Ext.Button({
            id: 'btnRemove',
            text: 'Remove',
            iconCls: 'button_menu_ext ss_sprite ss_delete',
            handler: function (s) {
                editor.stopEditing();
                var s = dbGrid.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){

                    //First Deleting step from Database using Ajax
                    var dbUID      = r.data.DBS_UID;
                    //if STEP_UID is properly defined (i.e. set to valid value) then only delete the row
                    //else its a BLANK ROW for which Ajax should not be called.
                    if(r.data.DBS_UID != "")
                    {
                        Ext.Ajax.request({
                          url   : '../dbConnections/dbConnectionsAjax.php',
                          method: 'POST',
                          params: {
                                dbs_uid         : dbUID,
                                action          :'deleteDbConnection'
                          },

                          success: function(response) {
                            Ext.MessageBox.alert ('Status','Step has been removed successfully.');
                            //Secondly deleting from Grid
                            dbStore.remove(r);
                            //Reloading store after removing steps
                            dbStore.reload();
                          }
                        });
                    }
                    else
                       dbStore.remove(r);
                }
            }
        });

        var tb = new Ext.Toolbar({
            items: [btnNew,btnEdit, btnRemove]
        });

  var editor = new Ext.ux.grid.RowEditor({
            saveText: 'Update'
        });

  var dbStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : dbConnFields,
            proxy: new Ext.data.HttpProxy({
              url: 'proxyExtjs.php?pid='+pro_uid+'&action=getDatabaseConnectionList'
            })
          });
  dbStore.load();

  var dbGridColumn =  new Ext.grid.ColumnModel({
      columns: [
                new Ext.grid.RowNumberer(),
                    {
                        id: 'DBS_TYPE',
                        header: 'Type',
                        dataIndex: 'DBS_TYPE',
                        //width: 100,
                        editable: false,
                        sortable: true,
                        editor: new Ext.form.TextField({
                        //allowBlank: false
                            })
                    },{
                        id: 'DBS_SERVER',
                        header: 'Server',
                        dataIndex: 'DBS_SERVER',
                        //width: 100,
                        sortable: true,
                        editor: new Ext.form.TextField({
                            //allowBlank: false
                            })
                    },{
                        id: 'DBS_DATABASE_NAME',
                        header: 'Database Name',
                        dataIndex: 'DBS_DATABASE_NAME',
                        //width: 100,
                        sortable: true,
                        editor: new Ext.form.TextField({
                       // allowBlank: false
                            })
                    },{
                        id: 'DBS_DESCRIPTION',
                        header: 'Description',
                        dataIndex: 'DBS_DESCRIPTION',
                        width: 100,
                        sortable: true,
                        editor: new Ext.form.TextField({
                            //allowBlank: false
                            })
                    }
                ]
     });

  var dbGrid = new Ext.grid.GridPanel({
        store: dbStore,
        id : 'mygrid',
        loadMask: true,
        loadingText: 'Loading...',
        renderTo: 'cases-grid',
        frame: false,
        autoHeight:false,
        clicksToEdit: 1,
        width:450,
        minHeight:400,
        height   :400,
        layout: 'fit',
        cm: dbGridColumn,
        stripeRows: true,
        tbar: tb,
        viewConfig: {forceFit: true}
   });

  var dbconnForm =new Ext.FormPanel({
   //   title:"Add new Database Source",
      collapsible: false,
      maximizable: true,
      //allowBlank:false,
      width:450,
      frame:true,
      monitorValid : true,
      plain: true,
      bodyStyle: 'padding:5px;',
      buttonAlign: 'center',

                      items:[{
                        xtype: 'combo',
                        width:  150,
                        mode: 'local',
                        editable:       false,
                        fieldLabel: 'Engine',
                        triggerAction: 'all',
                        forceSelection: true,
                        name: 'DBS_TYPE',
                        displayField:  'name',
                        //emptyText    : 'Select Format',
                        valueField   : 'value',
                        value        : 'Select',
                        store: new Ext.data.JsonStore({
                                 fields : ['name', 'value'],
                                 data   : [
                                          {name : 'Select', value: 'select'},
                                          {name : 'MySql',   value: 'MySql'},
                                          {name : 'PostGreSql',   value: 'PostGreSql'},
                                          {name : 'Microsoft SQL server',   value: 'Microsoft SQL server'}
                                          ]}),
                        onSelect: function(record, index) {
                                 //Show-Hide Format Type Field
                                if(record.data.value == 'MySql')
                                        {Ext.getCmp("encode").show();
                                         Ext.getCmp("postgre").hide();
                                         Ext.getCmp("port").setValue('3306')}
                                else if(record.data.value == 'PostGreSql')
                                        {Ext.getCmp("postgre").show();
                                         Ext.getCmp("encode").hide();
                                         Ext.getCmp("port").setValue('5432')}
                                else
                                        {Ext.getCmp("sqlserver").show();
                                         Ext.getCmp("postgre").hide();
                                         Ext.getCmp("port").setValue('1433')}
                                         this.setValue(record.data[this.valueField || this.displayField]);
                                         this.collapse();
                        }
                      },{
                              xtype: 'fieldset',
                              id:    'encode',
                              
                              border:false,
                              hidden: true,
                              items: [{
                                      xtype: 'combo',
                                      width:  150,
                                      mode: 'local',
                                   //   hidden: true,
                                      editable:       false,
                                      fieldLabel: 'Encode',
                                      triggerAction: 'all',
                                      forceSelection: true,
                                      //dataIndex : 'ENGINE',
                                      displayField:   'name',
                                      valueField:     'value',
                                      name: 'DBS_ENCODE',
                                      store: new Ext.data.JsonStore({
                                             fields : ['name', 'value'],
                                             data   :  [
                                      {name:'big5', value: 'big5'},
                                      {name:'dec8',     value:'dec8' },
                                      {name:'cp850',    value:'cp850'},
                                      {name:'hp8',      value:'hp8' },
                                      {name:'koi8r',    value:'koi8r'},
                                      {name:'latin1',   value:'latin1'},
                                      {name:'latin2',   value:'latin2'},
                                      {name:'swe7',     value:'swe7' },
                                      {name:'ascii',    value:'ascii'},
                                      {name:'ujis',     value:'ujis' },
                                      {name:'sjis',     value:'sjis' },
                                      {name:'hebrew',   value:'hebrew'},
                                      {name:'tis620',   value:'tis620' },
                                      {name:'euckr',    value:'euckr'},
                                      {name:'koi8u',    value:'koi8u' },
                                      {name:'gb2312',   value:'gb2312'},
                                      {name:'greek',    value:'greek'},
                                      {name:'cp1250',   value:'cp1250'},
                                      {name:'gbk',      value:'gbk' },
                                      {name:'latin5',   value:'latin5' },
                                      {name:'armscii8', value:'armscii8'},
                                      {name:'utf8',     value:'utf8' },
                                      {name:'ucs2',     value:'ucs2'},
                                      {name:'cp866',    value:'cp866'},
                                      {name:'keybcs2',  value:'keybcs2' },
                                      {name:'macce',    value:'macce'},
                                      {name:'macroman', value:'macroman' },
                                      {name:'cp852',    value:'cp852' },
                                      {name:'latin7',   value:'atin7' },
                                      {name:'cp1251',   value:'cp1251'},
                                      {name:'cp1256',   value:'cp1256'},
                                      {name:'cp1257',   value:'cp1257'},
                                      {name:'binary',   value:'binary'},
                                      {name:'geostd8',  value:'geostd8'},
                                      {name:'cp932',    value:'cp932'},
                                      {name:'eucjpms',  value:'eucjpms'}
                                    ]})
                             }]
          
                      },{
                         xtype: 'fieldset',
                         id: 'postgre',
                         border:false,
                         hidden: true,
                         items:[{
                                  xtype: 'combo',
                                   width:  150,
                                  mode: 'local',
                                 // hidden: true,
                                  editable:false,
                                  fieldLabel: 'Encode',
                                  triggerAction: 'all',
                                  forceSelection: true,
                                  //dataIndex : 'ENGINE',
                                  displayField:   'name',
                                  valueField:     'value',
                                  name: 'DBS_ENCODE',
                                  store: new Ext.data.JsonStore({
                                         fields : ['name', 'value'],
                                         data   :  [
                                                      {name:"BIG5",        value:"BIG5"},
                                                      {name:"EUC_CN",      value:"EUC_CN"},
                                                      {name:"EUC_JP",      value:"EUC_JP"},
                                                      {name:"EUC_KR",      value:"EUC_KR"},
                                                      {name:"EUC_TW",      value:"EUC_TW"},
                                                      {name:"GB18030",     value:"GB18030"},
                                                      {name:"GBK",         value:"GBK"},
                                                      {name:"ISO_8859_5",  value:"ISO_8859_5"},
                                                      {name:"ISO_8859_6",  value:"ISO_8859_6"},
                                                      {name:"ISO_8859_7",  value:"ISO_8859_7"},
                                                      {name:"ISO_8859_8",  value: "ISO_8859_8"},
                                                      {name:"JOHAB",       value:"JOHAB"},
                                                      {name:"KOI8",        value: "KOI8"},
                                                      {name:"selected",    value:  "LATIN1"},
                                                      {name:"LATIN2",      value:"LATIN2"},
                                                      {name:"LATIN3",      value:"LATIN3"},
                                                      {name:"LATIN4",      value: "LATIN4"},
                                                      {name:"LATIN5",      value:"LATIN5"},
                                                      {name:"LATIN6",      value: "LATIN6"},
                                                      {name:"LATIN7",      value:"LATIN7"},
                                                      {name:"LATIN8",      value:"LATIN8"},
                                                      {name:"LATIN9",      value:"LATIN9"},
                                                      {name:"LATIN10",     value:"LATIN10"},
                                                      {name:"SJIS",        value:"SJIS"},
                                                      {name:"SQL_ASCII",   value:"SQL_ASCII"},
                                                      {name:"UHC",         value: "UHC"},
                                                      {name:"UTF8",        value: "UTF8"},
                                                      {name:"WIN866",      value: "WIN866"},
                                                      {name:"WIN874",      value:"WIN874"},
                                                      {name:"WIN1250",     value:"WIN1250"},
                                                      {name:"WIN1251",     value:"WIN1251"},
                                                      {name:"WIN1252",     value:"WIN1252"},
                                                      {name:"WIN1256",     value:"WIN1256"},
                                                      {name:"WIN1258",     value:"WIN1258"}
                                                   ]})
                               }]
                      },{
                         xtype: 'fieldset',
                         id: 'sqlserver',
                         border:false,
                         hidden: true,
                         items:[{
                                  xtype: 'combo',
                                  width:  150,
                                  mode: 'local',
                                  editable:       false,
                                  fieldLabel: 'Encode',
                                  triggerAction: 'all',
                                  forceSelection: true,
                                  //dataIndex : 'ENGINE',
                                  displayField:   'name',
                                  valueField:     'value',
                                  name: 'DBS_ENCODE',
                                  store: new Ext.data.JsonStore({
                                         fields : ['name', 'value'],
                                  data   :  [
                                            {name:'utf8',    value: 'utf8'}
                                  ]})
                               }]
                  
                      },{
                         xtype: 'textfield',
                         fieldLabel: 'Server',
                         name: 'DBS_SERVER',
                         allowBlank: false
                      },{
                         xtype: 'textfield',
                         fieldLabel: 'Database name',
                         name: 'DBS_DATABASE_NAME',
                         allowBlank: false
                      },{
                        xtype: 'textfield',
                        fieldLabel: 'Username',
                        name: 'DBS_USERNAME',
                        allowBlank: false
                      },{
                        xtype: 'textfield',
                        fieldLabel: 'Password',
                        inputType:'password',
                        name: 'DBS_PASSWORD',
                        allowBlank: true
                      },{
                        xtype: 'textfield',
                        fieldLabel: 'Port',
                        name: 'DBS_PORT',
                        id:'port',
                        //allowBlank: false,
                        editable:false
                      },{
                        xtype: 'textarea',
                        fieldLabel: 'Description',
                        name: 'DBS_DESCRIPTION',
                        allowBlank: true,
                        width: 200,
                        height:100
                      },{
                        id : 'DBS_UID',
                        xtype: 'hidden',
                        name : 'DBS_UID'
                      },{}], 
         buttons: [{text:'Test Connection',
                                   id: 'test',
                                    //formbind: true,
                                    handler: function(){
                                       // testConnWindow.show();

                                       Ext.fly('p3text').update('Working');
                                        mybar.wait({
                                            interval:200,
                                            duration:5000,
                                            increment:15,
                                            fn:function(){
                                                btn3.dom.disabled = false;
                                                Ext.fly('p3text').update('Done');
                                            }
                                        });

                                       var getForm         = dbconnForm.getForm().getValues();
                                        //var dbConnUID       = getForm.DBS_UID;
                                        var Type            = getForm.DBS_TYPE;
                                        var Server          = getForm.DBS_SERVER;
                                        var DatabaseName    = getForm.DBS_DATABASE_NAME;
                                        var Username        = getForm.DBS_USERNAME;
                                        var Password        = getForm.DBS_PASSWORD;
                                        var Port            = getForm.DBS_PORT;
                                       
                                       //var Description     = getForm.DBS_DESCRIPTION;
                                       for(var Step=1;Step<=4;Step++)
                                          // {
                                               {
                                               Ext.Ajax.request({
                                                   url   : '../dbConnections/dbConnectionsAjax.php',
                                                   method: 'POST',
                                                   params:{
                                                           step     :Step,
                                                           type     :Type,
                                                           server   :Server,
                                                           db_name  :DatabaseName,
                                                           user     :Username ,
                                                           passwd   :Password,
                                                           port     :Port,
                                                           //desc     :Description,
                                                           action   :'testConnection'
                                                          },
                                                    success: function(response) {
                                                    Ext.MessageBox.alert ('Status','Connection Tested Successfully.');
                                                    }
                                              });
                                            }
                                           }
                                    
                     },{

        text: 'Save',
        formBind    :true,
        handler: function(){
            var getForm         = dbconnForm.getForm().getValues();
            var dbConnUID       = getForm.DBS_UID;
            var Type            = getForm.DBS_TYPE;
            var Server          = getForm.DBS_SERVER;
            var DatabaseName    = getForm.DBS_DATABASE_NAME;
            var Username        = getForm.DBS_USERNAME;
            var Password        = getForm.DBS_PASSWORD;
            var Port            = getForm.DBS_PORT;
            var Description     = getForm.DBS_DESCRIPTION;
            var encode          = getForm.DBS_ENCODE;


            if(dbConnUID=='')
                {
                   Ext.Ajax.request({
                       url   : '../dbConnections/dbConnectionsAjax.php',
                       method: 'POST',
                       params:{
                               DBS_uid  :dbConnUID,
                               type     :Type,
                               server   :Server,
                               db_name  :DatabaseName,
                               user     :Username ,
                               passwd   :Password,
                               port     :Port,
                               desc     :Description,
                               PROCESS  :pro_uid,
                               enc      :encode,
                               action   :'saveConnection'
                              },
                        success: function(response) {
                        Ext.MessageBox.alert ('Status','Connection Saved Successfully.');
                        }
                  });
                }
            else
                {
                 Ext.Ajax.request({
                     url   : '../dbConnections/dbConnectionsAjax.php',
                     method: 'POST',
                     params:{
                              dbs_uid  :dbConnUID,
                              type     :Type,
                              server   :Server,
                              db_name  :DatabaseName,
                              user     :Username ,
                              passwd   :Password,
                              port     :Port,
                              PROCESS  :pro_uid,
                              desc     :Description,
                              enc      :encode,
                              action   :'saveEditConnection'
                            },
                    success: function(response) {
                    Ext.MessageBox.alert ('Status','Connection Edited Successfully.');
                    }
                });
            }
           formWindow.hide();
           dbStore.reload();
        }
    },{
        text: 'Cancel',
        handler: function(){
            // when this button clicked,
            formWindow.hide();
        }
    }]
  })

   /*var testConnBar = new Ext.ProgressBar({
    id: 'loadBar',
    text: 'Loading...'
});

var testConnWindow = new Ext.Window({
    closable: false,
    collapsible: false,
    draggable: false,
    resizable: false,
    el: 'gridDiv',
    layout:'fit',
    width:500,
    height:40,
    plain: true,
    modal: true,
    items: testConnBar
});*/

  var formWindow = new Ext.Window({
    title: 'Add new Database Source',
    collapsible: false,
    maximizable: true,
    width: 450,
    //autoHeight: true,
    //height: 400,
    //layout: 'fit',
    plain: true,
    bodyStyle: 'padding:5px;',
    buttonAlign: 'center',
    items: dbconnForm
   
  });

  var gridWindow = new Ext.Window({
    title: 'Database Source List',
    collapsible: false,
    maximizable: true,
    width: 480,
    //autoHeight: true,
    height: 400,
    //layout: 'fit',
    plain: true,
    bodyStyle: 'padding:5px;',
    buttonAlign: 'center',
    items: dbGrid
  });
  gridWindow.show();
}


ProcessOptions.prototype.addInputDoc= function(_5625)
{
  var ProcMapObj= new ProcessMapContext();
  var pro_uid = workflow.getUrlVars();

  var dynaFields = Ext.data.Record.create([
            {
                name: 'INP_DOC_UID',
                type: 'string'
            },
            {
                name: 'PRO_UID',
                type: 'string'
            },
            {
                name: 'INP_DOC_TITLE',
                type: 'string'
            },
            {
                name: 'INP_DOC_DESCRIPTION',
                type: 'string'
            },{
                name: 'INP_DOC_VERSIONING',
                type: 'string'
            },{
                name: 'INP_DOC_DESTINATION_PATH',
                type: 'string'
            }
            ]);

    var editor = new Ext.ux.grid.RowEditor({
    saveText: 'Update'
    });

  var inputDocStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : dynaFields,
            proxy: new Ext.data.HttpProxy({
              url: 'proxyExtjs?pid='+pro_uid+'&action=getInputDocumentList'
            })
          });
  inputDocStore.load();

  var btnRemove = new Ext.Button({
            id: 'btnRemove',
            text: 'Delete',
            iconCls: 'button_menu_ext ss_sprite ss_delete',
            handler: function (s) {
                editor.stopEditing();
                var s = inputDocGrid.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){

                    //First Deleting dynaform from Database using Ajax
                    var inputDocUID      = r.data.INP_DOC_UID;

                    //if STEP_UID is properly defined (i.e. set to valid value) then only delete the row
                    //else its a BLANK ROW for which Ajax should not be called.
                    if(r.data.INP_DOC_UID != "")
                    {
                        Ext.Ajax.request({
                          url   : '../inputdocs/inputdocs_Delete.php',
                          method: 'POST',
                          params: {
                                functions          : 'getRelationInfDoc',
                                INP_DOC_UID        : inputDocUID
                          },
                          success: function(response) {
                            //First check whether selected input document is assigned to a process supervisor or not.
                            //If response.responseText == 1 i.e it is assigned, => it cannot be deleted
                            if(response.responseText == "")
                             {
                                Ext.Ajax.request({
                                  url   : '../inputdocs/inputdocs_Delete.php',
                                  method: 'POST',
                                  params: {
                                        functions          : 'deleteInputDocument',
                                        INP_DOC_UID        : inputDocUID
                                  },
                                  success: function(response) {
                                    Ext.MessageBox.alert ('Status','Input document has been removed successfully.');
                                    //Secondly deleting from Grid
                                    inputDocStore.remove(r);
                                    //reloading store after deleting input document
                                    inputDocStore.reload();
                                  }
                                });
                             }
                             else
                               Ext.MessageBox.alert ('Status','Input document assigned to a process supervisors cannot be deleted.');
                          }
                        });
                    }
                    else
                        inputDocStore.remove(r);
                }
            }
        });

  var btnAdd = new Ext.Button({
            id: 'btnAdd',
            text: 'New',
           iconCls: 'button_menu_ext ss_sprite ss_add',
            handler: function () {
               newIOWindow.show();
               inputDocForm.getForm().reset();
            }
        });
 

 var btnEdit = new Ext.Button({
            id: 'btnEdit',
            text: 'Edit',
            iconCls: 'button_menu_ext ss_sprite ss_pencil',
            handler: function (s) {
                var selectedRow = inputDocGrid.getSelectionModel().getSelections();
                var inputDocUID   = selectedRow[0].data.INP_DOC_UID;

                 //Loading Task Details into the form
                  inputDocForm.form.load({
                        url:'proxyExtjs.php?INP_DOC_UID=' +inputDocUID+'&action=editInputDocument',
                        method:'GET',
                        waitMsg:'Loading',
                        success:function(form, action) {
                           //Ext.MessageBox.alert('Message', 'Loaded OK');
                           newIOWindow.show();
                           Ext.getCmp("INP_DOC_UID").setValue(inputDocUID);
                        },
                        failure:function(form, action) {
                            Ext.MessageBox.alert('Message', 'Load failed');
                        }
                    });
            }
        });

  var inputDocForm = new Ext.FormPanel({
        labelWidth: 100,
        bodyStyle :'padding:5px 5px 0',
        width     : 500,
        height    : 380,
        monitorValid : true,
        autoHeight: true,
        items:[{
                    xtype: 'fieldset',
                    layout: 'form',
                    border:true,
                    title: 'Input Document Information',
                    width: 500,
                    //height:500,
                    collapsible: false,
                    labelAlign: '',
                    items:[{
                            xtype     : 'textfield',
                            fieldLabel: 'Title',
                            width     : 200,
                            name      : 'INP_DOC_TITLE',
                            allowBlank: false
                         },{
                            width     : 200,
                            xtype:          'combo',
                            mode:           'local',
                            editable:       false,
                            fieldLabel:     'Type',
                            triggerAction:  'all',
                            forceSelection: true,
                            name:           'INP_DOC_FORM_NEEDED',
                            displayField:   'name',
                            value       :   'Digital',
                            valueField:     'value',
                            store:          new Ext.data.JsonStore({
                                        fields : ['name', 'value'],
                                        data   : [
                                            {name : 'Digital',   value: 'VIRTUAL'},
                                            {name : 'Printed',   value: 'REAL'},
                                            {name : 'Digital/Printed',   value: 'VREAL'}]}),

                           onSelect: function(record, index) {
                                //Show-Hide Format Type Field
                                if(record.data.value != 'VIRTUAL')
                                        Ext.getCmp("formType").show();
                                else
                                        Ext.getCmp("formType").hide();

                                this.setValue(record.data[this.valueField || this.displayField]);
                                this.collapse();
                             }
                         },{
                            xtype: 'fieldset',
                            layout: 'form',
                            id:'formType',
                            border: false,
                            width     : 300,
                            hidden:true,
                            labelAlign: '',
                            items:[{
                                xtype:          'combo',
                                width:          150,
                                mode:           'local',
                                editable:       false,
                                fieldLabel:     'Format',
                                triggerAction:  'all',
                                forceSelection: true,
                                name:           'INP_DOC_ORIGINAL',
                                displayField:   'name',
                                //emptyText    : 'Select Format',
                                valueField:     'value',
                                value        : 'ORIGINAL',
                                store:          new Ext.data.JsonStore({
                                            fields : ['name', 'value'],
                                            data   : [
                                                {name : 'original',   value: 'ORIGINAL'},
                                                {name : 'legal copy',   value: 'COPYLEGAL'},
                                                {name : 'copy',   value: 'COPY'}
                                                ]})
                                }]
                        },{
                            xtype     : 'textarea',
                            fieldLabel: 'Description',
                            name      : 'INP_DOC_DESCRIPTION',
                            height    : 120,
                            width     : 300
                         },{
                            width     : 200,
                            xtype:          'combo',
                            mode:           'local',
                            editable:       false,
                            fieldLabel:     'Enable Versioning',
                            triggerAction:  'all',
                            forceSelection: true,
                            name:           'INP_DOC_VERSIONING',
                            displayField:   'name',
                            valueField:     'value',
                            value         : 'No',
                            store:          new Ext.data.JsonStore({
                                        fields : ['name', 'value'],
                                        data   : [
                                            {name : 'No',   value: ''},
                                            {name : 'Yes',   value: '1'},
                                            ]})
                         },{
                    layout      :'column',
                    border      :false,
                    items       :[{
                        //columnWidth :.6,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                                xtype       : 'textfield',
                                width     : 200,
                                //id          : 'DestPath',
                                fieldLabel  : 'Destination Path',
                                name        : 'INP_DOC_DESTINATION_PATH',
                                anchor      :'100%'
                        }]
                    },{
                        //columnWidth     :.4,
                        layout          : 'form',
                        border          :false,
                        items           : [{
                                xtype           :'button',
                                title           : ' ',
                                width:50,
                                text            : '@@',
                               name            : 'selectorigin',
                                 handler: function (s) {
                                                    workflow.variablesAction = 'form';
                                                    workflow.fieldName         = 'INP_DOC_DESTINATION_PATH' ;
                                                    workflow.variable        = '@@',
                                                    workflow.formSelected    = inputDocForm;
                                                    var rowData = ProcMapObj.ExtVariables();
                                            }
                            }]
                    }]
                },{
                    layout      :'column',
                    border      :false,
                    items       :[{
                        //columnWidth :.6,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                        xtype       : 'textfield',
                        width     : 200,
                        //id          :'tags',
                        fieldLabel  : 'Tags',
                        name        : 'INP_DOC_TAGS',
                        anchor      :'100%'
                        }]
                    },{
                        columnWidth :.4,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                                xtype       :'button',
                                title       : ' ',
                                width:50,
                                text        : '@@',
                                name        : 'selectorigin',
                                handler: function (s) {
                                                workflow.variablesAction = 'form';
                                                workflow.fieldName         = 'INP_DOC_TAGS' ;
                                                workflow.variable        = '@@',
                                                workflow.formSelected    = inputDocForm;
                                                var rowData = ProcMapObj.ExtVariables();
                                   }
                              }]
                        }]
                     },{
                      id : 'INP_DOC_UID',
                      xtype: 'hidden',
                      name : 'INP_DOC_UID'
               }]
        }],
        buttons: [{
            text: 'Save',
            formBind    :true,
            handler: function(){
                var getForm         = inputDocForm.getForm().getValues();
                var sDocUID        = getForm.INP_DOC_UID;
                var sDocTitle        = getForm.INP_DOC_TITLE;
                var sFormNeeded     = getForm.INP_DOC_FORM_NEEDED;
                var sOrig           = getForm.INP_DOC_ORIGINAL;
                if(sOrig == "")
                    sOrig           = 'ORIGINAL';
                
                if(sOrig == 'legal copy')
                    sOrig           = 'COPYLEGAL';

                if(sFormNeeded == 'Digital')
                     sFormNeeded     = 'VIRTUAL';
                else if(sFormNeeded == 'Printed')
                    sFormNeeded     = 'REAL';
                else
                    sFormNeeded     = 'VREAL';


                var sDesc = getForm.INP_DOC_DESCRIPTION;
                var sVers           = getForm.INP_DOC_VERSIONING;
                if(sVers == 'Yes')
                    sVers = '1';
                else
                    sVers = '';

                var sDestPath       = getForm.INP_DOC_DESTINATION_PATH;
                var sTags           = getForm.INP_DOC_TAGS;

               if(sDocUID == "")
               {
                    Ext.Ajax.request({
                      url   : '../inputdocs/inputdocs_Save.php',
                      method: 'POST',
                      params:{
                          functions                : 'lookForNameInput',
                          NAMEINPUT                : sDocTitle,
                          proUid                   : pro_uid
                      },
                      success: function(response) {
                        if(response.responseText == "1")
                        {
                           Ext.Ajax.request({
                              url   : '../inputdocs/inputdocs_Save.php',
                              method: 'POST',
                              params:{
                                  functions                     : '',
                                  INP_DOC_TITLE                 : sDocTitle,
                                  INP_DOC_UID                   : sDocUID,
                                  PRO_UID                       : pro_uid,
                                  INP_DOC_FORM_NEEDED           : sFormNeeded,
                                  INP_DOC_ORIGINAL              : sOrig,
                                  INP_DOC_VERSIONING            : sVers,
                                  INP_DOC_TAGS                  : sTags,
                                  INP_DOC_DESCRIPTION           : sDesc,
                                  INP_DOC_DESTINATION_PATH      : sDestPath
                              },
                              success: function(response) {
                                  Ext.MessageBox.alert ('Status','Input document has been created successfully.');
                                  newIOWindow.hide();
                                  inputDocStore.reload();
                              }
                            });
                        }
                        else
                            Ext.MessageBox.alert ('Status','There is an Input Document with the same  name in  this process. It is not saving');
                  }
                 })
                }
                else
                {
                    Ext.Ajax.request({
                          url   : '../inputdocs/inputdocs_Save.php',
                          method: 'POST',
                          params:{
                              functions                     : '',
                              INP_DOC_TITLE                 : sDocTitle,
                              INP_DOC_UID                   : sDocUID,
                              PRO_UID                       : pro_uid,
                              INP_DOC_FORM_NEEDED           : sFormNeeded,
                              INP_DOC_ORIGINAL              : sOrig,
                              INP_DOC_VERSIONING            : sVers,
                              INP_DOC_TAGS                  : sTags,
                              INP_DOC_DESCRIPTION           : sDesc,
                              INP_DOC_DESTINATION_PATH      : sDestPath
                          },
                          success: function(response) {
                              Ext.MessageBox.alert ('Status','Input document has been updated successfully.');
                              newIOWindow.hide();
                              inputDocStore.reload();
                          }
                        });
                }
           }
        },{
            text: 'Cancel',
            handler: function(){
                // when this button clicked,
                newIOWindow.hide();
            }
        }],
       buttonAlign : 'center'
    });

  var inputDocColumns = new Ext.grid.ColumnModel({
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    id: 'INP_DOC_TITLE',
                    header: 'Title',
                    dataIndex: 'INP_DOC_TITLE',
                    width: 280,
                    editable: false,
                    editor: new Ext.form.TextField({
                    //allowBlank: false
                    })
                },{
                    id: 'INP_DOC_VERSIONING',
                    header: 'Versioning',
                    dataIndex: 'INP_DOC_VERSIONING',
                    width: 280,
                    editable: false,
                    editor: new Ext.form.TextField({
                    //allowBlank: false
                    })
                },{
                    id: 'INP_DOC_DESTINATION_PATH',
                    header: 'Destination Path',
                    dataIndex: 'INP_DOC_DESTINATION_PATH',
                    width: 280,
                    editable: false,
                    editor: new Ext.form.TextField({
                    //allowBlank: false
                    })
                },
                /*{
                    sortable: false,
                    renderer: function(val, meta, record)
                       {
                            return String.format("<a href='../dynaforms/dynaforms_Editor?PRO_UID={0}&DYN_UID={1}'>Edit</a>",pro_uid,pro_uid);
                       }
                }*/
                ]
        });


  var tb = new Ext.Toolbar({
            items: [btnAdd, btnRemove, btnEdit]
            });

  var inputDocGrid = new Ext.grid.GridPanel({
        store: inputDocStore,
        id : 'mygrid',
        loadMask: true,
        //loadingText: 'Loading...',
        renderTo: 'cases-grid',
        frame: false,
        autoHeight:false,
        clicksToEdit: 1,
        minHeight:400,
        height   :350,
        layout: 'fit',
        cm: inputDocColumns,
        stripeRows: true,
        tbar: tb,
        viewConfig: {forceFit: true}
   });


  var gridWindow = new Ext.Window({
        title: 'Input Document',
        width: 550,
        height: 420,
        minWidth: 200,
        minHeight: 150,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        items: inputDocGrid,
        autoScroll: true
 });

 var newIOWindow = new Ext.Window({
        title: 'New Input Document',
        width: 550,
        height: 400,
        minWidth: 200,
        minHeight: 150,
        autoScroll: true,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        items: inputDocForm
    });
   gridWindow.show();
}



ProcessOptions.prototype.addOutputDoc= function(_5625)
{
  var pro_uid = workflow.getUrlVars();
   var ProcMapObj= new ProcessMapContext();

  var dynaFields = Ext.data.Record.create([
            {
                name: 'OUT_DOC_UID',
                type: 'string'
            },
            {
                name: 'OUT_DOC_TYPE',
                type: 'string'
            },
            {
                name: 'OUT_DOC_TITLE',
                type: 'string'
            },
            {
                name: 'OUT_DOC_DESCRIPTION',
                type: 'string'
            }
            ]);


  var editor = new Ext.ux.grid.RowEditor({
            saveText: 'Update'
            });



  var outputDocStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : dynaFields,
            proxy        : new Ext.data.HttpProxy({
                           url: 'proxyExtjs?pid='+pro_uid+'&action=getOutputDocument'
                           })
  });
  outputDocStore.load();

  var btnRemove = new Ext.Button({
            id: 'btnRemove',
            text: 'Delete',
            iconCls: 'button_menu_ext ss_sprite ss_delete',
            handler: function (s) {
                editor.stopEditing();
                var s = outputDocGrid.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){

                    //First Deleting dynaform from Database using Ajax
                    var outputDocUID      = r.data.OUT_DOC_UID;

                    //if STEP_UID is properly defined (i.e. set to valid value) then only delete the row
                    //else its a BLANK ROW for which Ajax should not be called.
                    if(r.data.OUT_DOC_UID != "")
                    {
                        Ext.Ajax.request({
                          url   : '../outputdocs/outputdocs_Delete.php',
                          method: 'POST',
                          params: {
                                OUT_DOC_UID        : outputDocUID
                          },
                          success: function(response) {
                            Ext.MessageBox.alert ('Status','Output document has been removed successfully.');

                            //Secondly deleting from Grid
                            outputDocStore.remove(r);
                            //reloading store after deleting output document
                            outputDocStore.reload();
                          }
                        });
                    }
                }
            }
        });


  var btnAdd = new Ext.Button({
            id: 'btnAdd',
            text: 'New',
            iconCls: 'button_menu_ext ss_sprite ss_add',
            handler: function () {
            outputDocForm.getForm().reset();
            newOPWindow.show();
            }
        });

  var btnEdit = new Ext.Button({
            id: 'btnEdit',
            text: 'Edit',
            iconCls: 'button_menu_ext ss_sprite ss_pencil',
            handler: function (s) {
                var s = outputDocGrid.getSelectionModel().getSelections();
                var outputDocUID = s[0].data.OUT_DOC_UID;
                outputDocForm.form.load({
                    url:'proxyExtjs.php?tid='+outputDocUID+'&action=editOutputDocument',
                    method:'GET',
                    waitMsg:'Loading',
                    success:function(form, action) {
                        newOPWindow.show();
                     //  Ext.MessageBox.alert('Message', 'Loaded OK');
                      //  setTaskAssignType(form);
                    },
                    failure:function(form, action) {
                        Ext.MessageBox.alert('Message', 'Load failed');
                    }
                });
            }

        });

  var btnProperties = new Ext.Button({
            id: 'btnProperties',
            text: 'Properties',
            iconCls: 'application_add',
            handler: function (s) {
                outputDocGrid.stopEditing();
                var selectedRow = outputDocGrid.getSelectionModel().getSelections();
                var outDocUID   = selectedRow.data[0].OUT_DOC_UID;
            }
        });

  var tb = new Ext.Toolbar({
            items: [btnAdd, btnRemove,btnEdit,btnProperties]
       });

  var outputDocColumns = new Ext.grid.ColumnModel({
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    id: 'OUT_DOC_TITLE',
                    header: 'Title',
                    dataIndex: 'OUT_DOC_TITLE',
                    width: 280,
                    editable: false,
                    editor: new Ext.form.TextField({
                    //allowBlank: false
                    })
                },
                {
                    id: 'OUT_DOC_TYPE',
                    header: 'Type',
                    dataIndex: 'OUT_DOC_TYPE',
                    editable: false,
                    editor: new Ext.form.TextField({
                    //allowBlank: false
                    })
                }
            ]
        });


  var outputDocGrid = new Ext.grid.GridPanel({
        store       : outputDocStore,
        id          : 'mygrid',
        loadMask    : true,
        loadingText : 'Loading...',
        renderTo    : 'cases-grid',
        frame       : false,
        autoHeight  :false,
        clicksToEdit: 1,
        minHeight   :400,
        height      :400,
        layout      : 'fit',
        cm          : outputDocColumns,
        stripeRows  : true,
        tbar        : tb,
        viewConfig  : {forceFit: true}
   });

  var outputDocForm = new Ext.FormPanel({
        monitorValid    :true,
        labelWidth      : 100,
        bodyStyle       :'padding:5px 5px 0',
        defaults        :{ autoScroll:true },
        width           : 500,

        items           :[{
                    xtype       : 'fieldset',
                    layout      : 'form',
                    border      :true,
                    title       : 'Output Document Information',
                    width       : 500,
                    collapsible : false,
                    labelAlign  : '',
                    items       :[{
                                    xtype       : 'textfield',
                                    fieldLabel  : 'Title',
                                    allowBlank  : false,
                                    blankText   : 'Enter Title of Output Document',
                                    name        : 'OUT_DOC_TITLE'
                                },{
                                    //xtype: 'fieldset',
                                    layout:'column',
                                    border:false,
                                    items:[{
                                        columnWidth:.6,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype       : 'textfield',
                                            //id          : 'filenameGenerated',
                                            fieldLabel  : 'Filename generated',
                                            name        : 'OUT_DOC_FILENAME',
                                            allowBlank  : false,
                                            blankText   : 'Select Filename generated',
                                            anchor      : '100%'
                                         }]
                                },{
                                    columnWidth:.4,
                                    layout: 'form',
                                    border:false,
                                    items: [{
                                            xtype:'button',
                                            title: ' ',
                                            text: '@@',
                                            name: 'selectorigin',
                                             handler: function (s) {
                                                workflow.variablesAction = 'form';
                                                workflow.fieldName         = 'OUT_DOC_FILENAME' ;
                                                workflow.variable        = '@@',
                                                workflow.formSelected    = outputDocForm;
                                                var rowData = ProcMapObj.ExtVariables();
                                                console.log(rowData);
                                        }
                                    }]
                                 }]
                                },{
                                    xtype           : 'textarea',
                                    fieldLabel      : 'Description',
                                    name            : 'OUT_DOC_DESCRIPTION',
                                    height          : 120,
                                    width           : 280
                                },{
                                    width           :150,
                                    xtype           :'combo',
                                    mode            :'local',
                                    editable        :false,
                                    fieldLabel      :'Orientation',
                                    triggerAction   :'all',
                                    forceSelection  : true,
                                    name            :'OUT_DOC_LANDSCAPE',
                                    displayField    :'name',
                                    value           :'Portrait',
                                    valueField      :'value',
                                    store           :new Ext.data.JsonStore({
                                                                        fields : ['name', 'value'],
                                                                        data   : [
                                                                        {name : 'Portrait',   value: '0'},
                                                                        {name : 'Landscape',   value: '1'}]})
                                },{
                                    width           :150,
                                    xtype           :'combo',
                                    mode            :'local',
                                    editable        :false,
                                    fieldLabel      :'Media',
                                    triggerAction   :'all',
                                    forceSelection  : true,
                                    name            :'OUT_DOC_MEDIA',
                                    displayField    :'name',
                                    value           :'Letter',
                                    valueField      :'value',
                                    store           :new Ext.data.JsonStore({
                                                                            fields : ['name', 'value'],
                                                                            data   : [
                                                                            {name : 'Letter',   value: 'Letter'},
                                                                            {name : 'Legal',   value: 'Legal'},
                                                                            {name : 'Executive',   value: 'Executive'},
                                                                            {name : 'B5',   value: 'B5'},
                                                                            {name : 'Folio',   value: 'Folio'},
                                                                            {name : 'A0Oversize',   value: 'A0Oversize'},
                                                                            {name : 'A0',   value: 'A0'},
                                                                            {name : 'A1',   value: 'A1'},
                                                                            {name : 'A2',   value: 'A2'},
                                                                            {name : 'A3',   value: 'A3'},
                                                                            {name : 'A4',   value: 'A4'},
                                                                            {name : 'A5',   value: 'A5'},
                                                                            {name : 'A6',   value: 'A6'},
                                                                            {name : 'A7',   value: 'A7'},
                                                                            {name : 'A8',   value: 'A8'},
                                                                            {name : 'A9',   value: 'A9'},
                                                                            {name : 'A10',   value: 'A10'},
                                                                            {name : 'Screenshot640',   value: 'Screenshot640'},
                                                                            {name : 'Screenshot800',   value: 'Screenshot800'},
                                                                            {name : 'Screenshot1024',   value: 'Screenshot1024'}
                                                                           ]
                                                                       })
                                },{
                                                    xtype       : 'numberfield',
                                                    fieldLabel  : 'Left Margin',
                                                    name        : 'OUT_DOC_LEFT_MARGIN'
                                                },{
                                                    xtype       : 'numberfield',
                                                    fieldLabel  : 'Right Margin',
                                                    name        : 'OUT_DOC_RIGHT_MARGIN'
                                                },{
                                                    xtype       : 'numberfield',
                                                    fieldLabel  : 'Top Margin',
                                                    name        : 'OUT_DOC_TOP_MARGIN'
                                                },{
                                                    xtype       : 'numberfield',
                                                    fieldLabel  : 'Bottom Margin',
                                                    name        : 'OUT_DOC_BOTTOM_MARGIN'
                                                },{
                                                    width           :150,
                                                    xtype           :'combo',
                                                    mode            :'local',
                                                    editable        :false,
                                                    fieldLabel      :'Output Document to Generate',
                                                    triggerAction   :'all',
                                                    forceSelection  :true,
                                                    name            :'OUT_DOC_GENERATE',
                                                    displayField    :'name',
                                                    value           :'Doc',
                                                    valueField      :'value',
                                                    store           :new Ext.data.JsonStore({
                                                    fields          :['name', 'value'],
                                                    data            :[
                                                                    {name : 'BOTH',   value: 'BOTH'},
                                                                    {name : 'DOC',   value: 'DOC'},
                                                                    {name : 'PDF',   value: 'PDF'}]})
                },{
                    width           : 150,
                    xtype           :'combo',
                    mode            :'local',
                    editable        :false,
                    fieldLabel      :'Enable Versioning',
                    triggerAction   :'all',
                    forceSelection  :true,
                    name            :'OUT_DOC_VERSIONING',
                    displayField    :'name',
                    value           :'NO',
                    valueField      :'value',
                    store           :new Ext.data.JsonStore({
                    fields          : ['name', 'value'],
                    data            : [
                                    {name : 'NO',   value: '0'},
                                    {name : 'YES',   value: '1'}]})
                },{
                    layout      :'column',
                    border      :false,
                    items       :[{
                        columnWidth :.6,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                                xtype       : 'textfield',
                                //id          : 'DestPath',
                                fieldLabel  : 'Destination Path',
                                name        : 'OUT_DOC_DESTINATION_PATH',
                                anchor      :'100%'
                        }]
                    },{
                        columnWidth     :.4,
                        layout          : 'form',
                        border          :false,
                        items           : [{
                                xtype           :'button',
                                title           : ' ',
                                text            : '@@',
                                name            : 'selectorigin',
                                 handler: function (s) {
                                                    workflow.variablesAction = 'form';
                                                    workflow.fieldName         = 'OUT_DOC_DESTINATION_PATH' ;
                                                    workflow.variable        = '@@',
                                                    workflow.formSelected    = outputDocForm;
                                                    var rowData = ProcMapObj.ExtVariables();
                                            }
                            }]
                    }]
                },{
                    layout      :'column',
                    border      :false,
                    items       :[{
                        columnWidth :.6,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                        xtype       : 'textfield',
                        //id          :'tags',
                        fieldLabel  : 'Tags',
                        name        : 'OUT_DOC_TAGS',
                        anchor      :'100%'
                        }]
                    },{
                        columnWidth :.4,
                        layout      : 'form',
                        border      :false,
                        items       : [{
                                xtype       :'button',
                                title       : ' ',
                                text        : '@@',
                                name        : 'selectorigin',
                                handler: function (s) {
                                                workflow.variablesAction = 'form';
                                                workflow.fieldName         = 'OUT_DOC_TAGS' ;
                                                workflow.variable        = '@@',
                                                workflow.formSelected    = outputDocForm;
                                                var rowData = ProcMapObj.ExtVariables();
                                   }
                              }]
                        }]
                     },{
                      id : 'OUT_DOC_UID',
                      xtype: 'hidden',
                      name : 'OUT_DOC_UID'
                  }
                 ]
                  }],
     buttons     : [{
        text        : 'Save',
        formBind    :true,
        handler     : function(){
                var getForm       = outputDocForm.getForm().getValues();
                var sDocUID       = getForm.OUT_DOC_UID;
                var sDocTitle     = getForm.OUT_DOC_TITLE;
                var sFilename     = getForm.OUT_DOC_FILENAME;
                var sDesc         = getForm.OUT_DOC_DESCRIPTION;
                var sLandscape    = getForm.OUT_DOC_LANDSCAPE;
                if(getForm.OUT_DOC_LANDSCAPE == 'Portrait')
                        sLandscape=0;
                if(getForm.OUT_DOC_LANDSCAPE == 'Landscape')
                        sLandscape=1;
                var sMedia        = getForm.OUT_DOC_MEDIA;
                var sLeftMargin   = getForm.OUT_DOC_LEFT_MARGIN;
                var sRightMargin  = getForm.OUT_DOC_RIGHT_MARGIN;
                var sTopMargin    = getForm.OUT_DOC_TOP_MARGIN;
                var sBottomMargin = getForm.OUT_DOC_BOTTOM_MARGIN;
                var sGenerated    = getForm.OUT_DOC_GENERATE;
                var sVersioning   = getForm.OUT_DOC_VERSIONING;
                if(getForm.OUT_DOC_VERSIONING == 'NO')
                        sVersioning=0;
                if(getForm.OUT_DOC_VERSIONING == 'YES')
                        sVersioning=1;
                var sDestPath     = getForm.OUT_DOC_DESTINATION_PATH;
                var sTags         = getForm.OUT_DOC_TAGS;
            if(sDocUID == "")
               {
              Ext.Ajax.request({
                  url   : '../outputdocs/outputdocs_Save.php',
                  method: 'POST',
                  params:{
                      functions                : 'lookForNameOutput',
                      NAMEOUTPUT               : sDocTitle,
                      proUid                   : pro_uid
                  },
                  success: function(response) {
                    if(response.responseText == "1")
                    {
                      Ext.Ajax.request({
                          url   : '../outputdocs/outputdocs_Save.php',
                          method: 'POST',
                          params:{
                              functions                : '',
                              OUT_DOC_UID              : sDocUID,
                              OUT_DOC_TITLE            : sDocTitle,
                              OUT_DOC_FILENAME         : sFilename,
                              OUT_DOC_DESCRIPTION      : sDesc,
                              OUT_DOC_LANDSCAPE        : sLandscape,
                              OUT_DOC_MEDIA            : sMedia,
                              OUT_DOC_LEFT_MARGIN      : sLeftMargin,
                              OUT_DOC_RIGHT_MARGIN     : sRightMargin,
                              OUT_DOC_TOP_MARGIN       : sTopMargin,
                              OUT_DOC_BOTTOM_MARGIN    : sBottomMargin,
                              OUT_DOC_GENERATE         : sGenerated,
                              OUT_DOC_VERSIONING       : sVersioning,
                              OUT_DOC_DESTINATION_PATH : sDestPath,
                              OUT_DOC_TAGS             : sTags,
                              PRO_UID                  : pro_uid
                          },
                          success: function(response) {
                            Ext.MessageBox.alert ('Status','Output document has been created successfully.');
                            outputDocStore.reload();
                            newOPWindow.hide();
                          }
                      });

                  }


                  else
                      Ext.MessageBox.alert ('Status','There is an Output Document with the same  name in  this process. It is not saving');
                }
        });
               }
         else
               {
             Ext.Ajax.request({
                          url   : '../outputdocs/outputdocs_Save.php',
                          method: 'POST',
                          params:{
                              functions                : '',
                              OUT_DOC_UID              : sDocUID,
                              OUT_DOC_TITLE            : sDocTitle,
                              OUT_DOC_FILENAME         : sFilename,
                              OUT_DOC_DESCRIPTION      : sDesc,
                              OUT_DOC_LANDSCAPE        : sLandscape,
                              OUT_DOC_MEDIA            : sMedia,
                              OUT_DOC_LEFT_MARGIN      : sLeftMargin,
                              OUT_DOC_RIGHT_MARGIN     : sRightMargin,
                              OUT_DOC_TOP_MARGIN       : sTopMargin,
                              OUT_DOC_BOTTOM_MARGIN    : sBottomMargin,
                              OUT_DOC_GENERATE         : sGenerated,
                              OUT_DOC_VERSIONING       : sVersioning,
                              OUT_DOC_DESTINATION_PATH : sDestPath,
                              OUT_DOC_TAGS             : sTags,
                              PRO_UID                  : pro_uid
                          },
                          success: function(response) {
                            Ext.MessageBox.alert ('Status','Output document has been updated successfully.');
                            outputDocStore.reload();
                            newOPWindow.hide();
                          }
                      });

               }
        }
       },{
            text: 'Cancel',
            handler: function(){
                // when this button clicked,
                newOPWindow.hide();
            }
        }],
     buttonAlign : 'center'
       });

  var newOPWindow = new Ext.Window({
        title       : 'Output Document',
        width       : 550,
        defaults    :{ autoScroll:true },
        height      : 450,
        minWidth    : 200,
        minHeight   : 150,
        layout      : 'fit',
        plain       : true,
        bodyStyle   : 'padding:5px;',
        items       : outputDocForm,
        buttonAlign : 'center'
    });

 var gridWindow = new Ext.Window({
        title       : 'Output Document',
        collapsible : false,
        maximizable : false,
        width       : 550,
        defaults    :{ autoScroll:true },
        height      : 420,
        minWidth    : 200,
        minHeight   : 150,
        layout      : 'fit',
        plain       : true,
        bodyStyle   : 'padding:5px;',
        items       : outputDocGrid,
        buttonAlign : 'center'
     });
 gridWindow.show();
}

ProcessOptions.prototype.addReportTable= function(_5625)
{
  var pro_uid = workflow.getUrlVars();

  var reportFields = Ext.data.Record.create([
            {
                name:'REP_TAB_UID',
                type: 'string'
            },
            {
                name: 'REP_TAB_TITLE',
                type: 'string'
            },
            {
                name: 'REP_TAB_UID',
                type: 'string'
            },
            {
                name: 'FIELD_NAME',
                type: 'string'
            },
            {
                name: 'FIELD_UID',
                type: 'string'
            }
       ]);

 var editor = new Ext.ux.grid.RowEditor({
            saveText: 'Update'
        });
        
 var reportStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : reportFields,
            proxy        : new Ext.data.HttpProxy({
                           url : 'proxyExtjs?pid='+pro_uid+'&action=getReportTables'
                           })
 });
  reportStore.load();

 var reportTableTypeStore = new Ext.data.JsonStore({
            root         : 'data',
            totalProperty: 'totalCount',
            idProperty   : 'gridIndex',
            remoteSort   : true,
            fields       : reportFields,
            proxy        : new Ext.data.HttpProxy({
                           url : 'proxyExtjs?pid='+pro_uid+'&type=NORMAL&action=getReportTableType'
                           })
 });
  reportTableTypeStore.load();
  
  var reportColumns = new Ext.grid.ColumnModel({
            columns: [
                new Ext.grid.RowNumberer(),
                {
                    id: 'REP_TAB_TITLE',
                    header: 'Title',
                    dataIndex: 'REP_TAB_TITLE',
                    width: 380,
                    editable: false,
                    editor: new Ext.form.TextField({
                        //allowBlank: false
                    })
                }
            ]
  });

  var btnAdd = new Ext.Button({
            id: 'btnAdd',
            text: 'New',
            iconCls: 'button_menu_ext ss_sprite ss_add',
            handler: function () {
                formWindow.show();
                reportForm.getForm().reset();
            }
  });

   var btnEdit = new Ext.Button({
            id: 'btnEdit',
            text: 'Edit',
            iconCls: 'button_menu_ext ss_sprite ss_pencil',
            handler: function (s) {
                var s = reportGrid.getSelectionModel().getSelections();
                var repTabUID = s[0].data.REP_TAB_UID;
                reportForm.form.load({
                url:'proxyExtjs.php?REP_TAB_UID='+repTabUID+'&action=editReportTables',
                    method:'GET',
                    waitMsg:'Loading',
                    success:function(form, action) {
                        formWindow.show();
                      //Ext.MessageBox.alert('Message', 'Loaded OK');
                     // setTaskAssignType(form);
                    },
                    failure:function(form, action) {
                        Ext.MessageBox.alert('Message', 'Load failed');
                    }
                });
            }
  });


  var btnRemove = new Ext.Button({
            id: 'btnRemove',
            text: 'Delete',
            iconCls: 'button_menu_ext ss_sprite ss_delete',
            handler: function () {
                editor.stopEditing();
                var s = reportGrid.getSelectionModel().getSelections();
                for(var i = 0, r; r = s[i]; i++){

                    //if REP_TAB_UID is properly defined (i.e. set to valid value) then only delete the row
                    //else its a BLANK ROW for which Ajax should not be called.
                    if(r.data.REP_TAB_UID != "")
                    {
                        Ext.Ajax.request({
                          url   : '../reportTables/reportTables_Delete.php',
                          method: 'POST',
                          params: {
                                REP_TAB_UID    : r.data.REP_TAB_UID
                          },
                          success: function(response) {
                                Ext.MessageBox.alert ('Status','Report Table has been removed successfully.');
                                //Secondly deleting from Grid
                                reportGrid.remove(r);
                                //Reloading store after deleting report table
                                reportStore.reload();
                              }
                     });
                    }
                }
            }
  });


  var tb = new Ext.Toolbar({
            items: [btnAdd,btnRemove, btnEdit]
        });
       

  var reportGrid = new Ext.grid.GridPanel({
        store       : reportStore,
        id          : 'mygrid',
        loadMask    : true,
        //loadingText : 'Loading...',
        renderTo    : 'cases-grid',
        frame       : false,
        autoHeight  :false,
        clicksToEdit: 1,
        width       :400,
        height      :400,
        layout      : 'fit',
        cm          : reportColumns,
        stripeRows: true,
        tbar: tb,
        viewConfig: {forceFit: true}
   });

  var gridWindow = new Ext.Window({
        title       : 'Report Tables',
        collapsible : false,
        maximizable : false,
        width       : 400,
        defaults    :{ autoScroll:true },
        height      : 450,
        minWidth    : 200,
        minHeight   : 150,
        layout      : 'fit',
        plain       : true,
        bodyStyle   : 'padding:5px;',
        items       : reportGrid,
        buttonAlign : 'center'
     });
 gridWindow.show();

var reportForm =new Ext.FormPanel({
      collapsible: false,
      maximizable: true,
      width:450,
      height:380,
      frame:true,
      monitorValid : true,
      plain: true,
      bodyStyle: 'padding:5px;',
      buttonAlign: 'center',
      items:[{
                              xtype: 'textfield',
                              fieldLabel: 'Title',
                              width: 150,
                              name: 'REP_TAB_TITLE',
                               allowBlank: false
                          },{

                              xtype: 'textfield',
                              fieldLabel: 'Table Name',
                              width: 150,
                              name: 'REP_TAB_NAME',
                               allowBlank: false
                          },
                          {
                              xtype: 'combo',
                              width:  150,
                              mode: 'local',
                              editable:false,
                              fieldLabel: 'Type',
                              triggerAction: 'all',
                              forceSelection: true,
                              name: 'REP_TAB_TYPE',
                              displayField:  'name',
                              valueField   : 'value',
                              value        : 'Global',
                              store: new Ext.data.JsonStore({
                                     fields : ['name', 'value'],
                                     data   : [
                                                {name : 'Global', value: 'NORMAL'},
                                                {name : 'Grid',   value: 'GRID'}
                                             ]}),
                              onSelect: function(record, index) {
                                        //Show-Hide Format Type Field
                                        if(record.data.value == 'NORMAL')
                                                {
                                                    Ext.getCmp("fields").show();
                                                    Ext.getCmp("gridfields").hide();
                                                }
                                        else
                                             {
                                                Ext.getCmp("gridfields").show();
                                                Ext.getCmp("fields").hide();
                                             }
                                        var link = 'proxyExtjs?pid='+pro_uid+'&type='+record.data.value+'&action=getReportTableType';
                                        reportTableTypeStore.proxy.setUrl(link, true);
                                        reportTableTypeStore.load();

                                        this.setValue(record.data[this.valueField || this.displayField]);
                                        this.collapse();
                      }
                      },
                      {
                          xtype: 'fieldset',
                          id:    'fields',
                          border:false,
                          hidden: false,
                          items: [{
                                  xtype: 'multiselect',
                                  width:  150,
                                  mode: 'local',
                                  style : 'margin-bottom:10px',
                                  editable:true,
                                  fieldLabel: 'Fields',
                                  triggerAction: 'all',
                                  allowblank: true,
                                  forceSelection: false,
                                  dataIndex : 'FIELD_NAME',
                                  name: 'FIELDS',
                                  valueField: 'FIELD_UID',
                                  displayField: 'FIELD_NAME',
                                  store: reportTableTypeStore
                                 }]
          }, {
                 xtype: 'fieldset',
                 id: 'gridfields',
                 border:false,
                 hidden: true,
                 align:'left',
                  items:[{
                                  xtype: 'combo',
                                  width:  150,
                                  mode: 'local',
                                  editable:false,
                                  fieldLabel: 'Grid Fields',
                                  triggerAction: 'all',
                                  forceSelection: true,
                                  displayField:   'name',
                                  valueField:     'value',
                                  name: 'REP_TAB_GRID',
                                  store: new Ext.data.JsonStore({
                                         fields : ['name', 'value'],
                                         data   :  []
                                     })
                       }]
          }
      ], buttons: [{
            text: 'Save',
            formBind    :true,
            handler: function(){
                var getForm         = reportForm.getForm().getValues();
                //var pro_uid         = getForm.PRO_UID;
                var tableUID        = getForm.REP_TAB_UID;
                var Title           = getForm.REP_TAB_TITLE;
                var Name            = getForm.REP_TAB_NAME;
                var Type            = getForm.REP_TAB_TYPE;
                if(Type == 'Global')
                    Type = 'NORMAL';
                else
                    Type = 'GRID';

                var Grid            = getForm.REP_TAB_GRID;
                var Fields          = getForm.FIELDS;

            if(typeof tableUID=='undefined')
               {
                Ext.Ajax.request({
                  url   : '../reportTables/reportTables_Save.php',
                  method: 'POST',
                  params:{
                      PRO_UID         :pro_uid,
                      REP_TAB_UID     :'',
                      REP_TAB_TITLE   :Title,
                      REP_TAB_NAME    :Name,
                      REP_TAB_TYPE    :Type ,
                      REP_TAB_GRID    :Grid,
                      FIELDS          :Fields
                  },
                  success: function(response) {
                      Ext.MessageBox.alert ('Status','Report Table Saved Successfully.');
                  }
                });
               }
                    else
                         {
                Ext.Ajax.request({
                  url   : '../reportTables/reportTables_Edit.php',
                  method: 'POST',
                  params:{
                      PRO_UID         :pro_uid,
                      REP_TAB_UID     :tableUID,
                      REP_TAB_TITLE   :Title,
                      REP_TAB_NAME    :Name,
                      REP_TAB_TYPE    :Type ,
                      REP_TAB_GRID    :Grid,
                      FIELDS          :Fields,
                      //REP_VAR_NAME    : VariableName,
                      //REP_VAR_TYPE    : VariableType,
                      REP_TAB_CONNECTION: Connection
                  },
                  success: function(response) {
                      Ext.MessageBox.alert ('Status','Report Table Edited Successfully.');
                  }


                });
                    }
            formWindow.hide();
            //reportStore.reload();

          }
        },{
            text: 'Cancel',
            handler: function(){
                // when this button clicked,
                formWindow.hide();
          }
        }]
  })

var formWindow = new Ext.Window({
        title: 'New Report Table',
        collapsible: false,
        maximizable: true,
        width: 400,
        //autoHeight: true,
        height: 400,
        layout: 'fit',
        plain: true,
        bodyStyle: 'padding:5px;',
        buttonAlign: 'center',
        items: reportForm
    });
   //gridWindow.show();
}

ProcessOptions.prototype.addTriggers= function()
{
  var pro_uid = workflow.getUrlVars();
  

}