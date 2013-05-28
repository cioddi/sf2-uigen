Ext.Loader.setConfig({
    enabled: true
});
Ext.Loader.setPath('Ext.ux', '/bundles/uigengenerator/extjs/ux');

Ext.require([
    'Ext.ux.CheckColumn'
]);

Ext.override(Ext.LoadMask, {
	onHide: function(){
		this.callParent();
	}
});

function {{ entity }}Grid(target_el_id){

	function create(){

		Ext.Ajax.request({
			method		: 	'GET',
			url			: 	'/{{ route_prefix }}/create',
			params		: 	store.proxy.extraParams,
			success		: 	function(response){
								store.load();
							}									    
		});   
	}
	
	function update(){
	
		var records = store.getUpdatedRecords();
		var da = [];
		for(var i = 0; i < records.length; i++) {
			var record = records[i];
			var jsonData = Ext.encode(records[i].data);
			
			da.push(jsonData);
		}
		var data = '[' + da.join(',') + ']';
		
		Ext.Ajax.request({
			method		:	'post',
			url			: 	'/{{ route_prefix }}/update',
			params		: 	{
								data: data
							}, 
			success		: 	function(response){
								var result=eval(response.responseText);
								switch(result){
								case 1:
									
									store.load({});
									break;
								}
							}									    
		});   
	}
	
	var delete_entity_id = 0;
	function confirmDeletion(id){

		Ext.Msg.confirm('delete?','Do you really want to delete this item',function(btn){
		
			if(btn == 'yes'){
				
				destroy(id);
			}
		});
	}
	
	function destroy(id){

		Ext.Ajax.request({
			url			: 	'/{{ route_prefix }}/'+id+'/destroy',
			success		: 	function(response){
								store.load();
							}									    
		});   
	}
	
	{%- if 'draganddrop' in actions %}
	
	function DragAndDrop(drag_id,target_id,position){
		params = new Object();
		params['drag_id'] = drag_id;
		params['target_id'] = target_id;
		params['position'] = position;
		
		{%- if dndAndConstraint %}
		
		for(var i in store.proxy.extraParams){
			params[i] = store.proxy.extraParams[i];
		}
		{% endif %}
		
		Ext.Ajax.request({
			method		:	'post',
			url			: 	'/{{ route_prefix }}/draganddrop',
			params		: 	params, 
			success		: 	function(response){
								store.load();
							}									    
		});
	}
	{% endif %}
	
	
	{%- include 'js/combo.js' %}


    // create the Data Store
    var store = new Ext.data.Store({
        proxy	:	new Ext.data.HttpProxy({
						url: '/{{ route_prefix }}/read',	
					reader: {
				                root: 'items',
				                totalProperty: 'count'
				            },
					getFilterValues:function(){
						return { {% for field, metadata in fields %}{%- if metadata.constraint == true %}
						{{field}}:{{metadata.camelized}}Combo.getValue(),

						{%- endif %}
						{% endfor %} };
					},
					extraParams:{ {% for field, metadata in fields %}{%- if metadata.constraint == true %}
					{{field}}:0,

					{%- endif %}
					{% endfor %} }
					}),
		autoLoad:	true,
		fields	: 	[
{% for field, metadata in fields %}
						{
							name: '{{ field }}',
							{% if metadata.type == 'datetime' %}type: 'date',
							dateFormat:'Y-m-d H:i:s'{% else %}type: '{{ metadata.type }}'
							{% endif %}
						},
{% endfor  %}
			        ]
    });

	var gridMenu = Ext.create('Ext.menu.Menu', {    
	    items: [{
				text		:	"delete entry",
				icon		:	"/bundles/uigengenerator/images/delete.png",
				handler	: 	function(){
								confirmDeletion(delete_entity_id);
								delete_entity_id = 0;
							}
							}]
	  });	
	
	var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
	        clicksToEdit: 1
	    });
    var grid = new Ext.grid.Panel({
        store: store,
        plugins: [cellEditing],
		listeners: {
		      beforeitemcontextmenu: function(view, record, item, index, e)
		      {
		        e.stopEvent();
				delete_entity_id = record.data.id;
				
		        gridMenu.showAt(e.getXY());
		      }
		    },
        columns: [
{% for field, metadata in fields %}{% if field != 'id' %}			{
	            header: '{{ field }}',
	            dataIndex: '{{ field }}',
{% if metadata.type == 'datetime' or metadata.type == 'date' %}				xtype:'datecolumn', 
				format:'d.m.Y',
	            editor: {
					xtype : 'datefield',
					format: 'd.m.Y'
	            		}
{% elseif metadata.constraint == true %}	        
				renderer: '{{metadata.constraintEntity|capitalize}}IdRenderer',
				editor: {
								xtype : 'combo',
								typeAhead			:	false,
								lazyRender			:	true,
								store				:	{{metadata.camelized}}Combo.store,
								displayField		:	'name',
								valueField			:	'id',
								mode				:	'local',
							    lastQuery: ''
						}
{% elseif metadata.type == 'integer' %}	            editor: {
					xtype : 'numberfield'
	            		}
{% elseif metadata.type == 'boolean' %}		xtype: 'checkcolumn',
	            editor: {
					xtype : 'checkbox'
	            		}
{% else %}	            editor: {
					xtype : 'textfield'
	            		}

{% endif %}
			},
{% endif %}{% endfor  %}

		],
        renderTo: target_el_id,
        width: '100%',
        height: 500,
        title: '{{ entity }}',
        tbar: [
				{
	           	 	text	:  'update',
		            handler : 	update,
					icon	:	"/bundles/uigengenerator/images/save.gif"
				},{
	           	 	text	: 	'create',
		            handler : 	create,
					icon	:	"/bundles/uigengenerator/images/add.png"
				},	{
			           	 	text	: 	'window',
				            handler : 	function(){
											var win = Ext.widget('window', {
											                width: 400,
											                height: 400,
											                minHeight: 400,
											                layout: 'fit',
											                resizable: true,
											                modal: true,
											                items: new Ext.custom.{{ entity }}Form()
											            });
											win.show();
										},
							icon	:	"/bundles/uigengenerator/images/add.png"
						}{% for field, metadata in fields %}{%- if metadata.constraint == true %}
				,{{metadata.camelized}}Combo
				
				{%- endif %}
				{% endfor %}
			],
		bbar: Ext.create('Ext.PagingToolbar', {
		            store: store,
		            displayInfo: true,
		            displayMsg: 'Displaying items {0} - {1} of {2}',
		            emptyMsg: "No items to display"
		        }){%- if 'draganddrop' in actions %},
        viewConfig: {
            plugins: {
                ptype: 'gridviewdragdrop',
                dragGroup: '{{ entity }}_ddg',
                dropGroup: '{{ entity }}_ddg'
            },
            listeners: {
                drop: function(node, data, dropRec, dropPosition) {
					DragAndDrop(data.records[0].get('id'),dropRec.get('id'),dropPosition);
                }
            }
        }
		
		{% endif %}
    });

	{%- if dndAndConstraint == true %}
	var checkParameterSelection = function(){
		
		var allSelected = true;

		for(var i in store.proxy.extraParams){
			if(!store.proxy.extraParams[i])allSelected = false;
		}

		if(allSelected){
			grid.getDockedComponent(2).items.items[1].enable();
			grid.viewConfig.plugins.cmp.plugins[0].enable();
		}else{ 
			grid.getDockedComponent(2).items.items[1].disable();
			grid.viewConfig.plugins.cmp.plugins[0].disable();
		}
	}
	checkParameterSelection();
{% endif %}
}


Ext.define('Ext.custom.{{ entity }}CustomComboBox',{
	extend:'Ext.form.field.ComboBox',
	alias:'widget.{{ entity }}CustomComboBox',
	constructor:function(cnfg){
	    this.callParent(arguments);
	    this.initConfig(cnfg);
	
		var comboStore = this.store;
		Ext.util.Format.{{ entity|capitalize }}IdRenderer = function(v)
		{
			var idx = comboStore.findExact('id',v);
			var rec = comboStore.getAt(idx);
			if(rec && v != '')return rec.get('name');
			return '';
		};
	},

		typeAhead			:	false,
		lazyRender			:	true,
		store				:	new Ext.data.Store({
								    proxy		:	new Ext.data.HttpProxy({
										url: '/{{ entity }}/list{{ entity }}_idcombo',
									}),
								    fields		: [ 
													{
													   	name	: 	'id', 
													   	type	: 	'integer'
												   	},{
													   	name	: 	'name', 
													   	type	: 	'string'
												   	}
											      ]

								}),
		displayField		:	'name',
		valueField			:	'id',
		mode				:	'local',
		triggerAction		:	'all',
		editable			: 	false,
		width				:	80
});
{%- include 'js/form.js' %}