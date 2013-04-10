Ext.Loader.setConfig({
    enabled: true
});
Ext.Loader.setPath('Ext.ux', '/bundles/uigengenerator/extjs/ux');

Ext.require([
    'Ext.ux.CheckColumn'
]);

function {{ entity }}Grid(target_el_id){

	function create(){

		Ext.Ajax.request({
			url			: 	'/{{ route_prefix }}/create',
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
									
									store.load();
									break;
								}
							}									    
		});   
	}
	
	var delete_entity_id = 0;
	function confirmDeletion(id){

		Ext.Msg.confirm('delete?','Do you really want to delete this entry',function(btn){
		
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
		
		Ext.Ajax.request({
			method		:	'post',
			url			: 	'/{{ route_prefix }}/draganddrop',
			params		: 	{
								drag_id: drag_id,
								target_id: target_id,
								position:position
							}, 
			success		: 	function(response){
								store.load();
							}									    
		});
	}
	{% endif %}
	
    // create the Data Store
    var store = new Ext.data.Store({
        proxy	:	new Ext.data.HttpProxy({
						url: '/{{ route_prefix }}/read',	
					reader: {
				                root: 'items',
				                totalProperty: 'count'
				            }
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
				}
			],
		bbar: Ext.create('Ext.PagingToolbar', {
		            store: store,
		            displayInfo: true,
		            displayMsg: 'Displaying topics {0} - {1} of {2}',
		            emptyMsg: "No topics to display"
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

}


function {{ entity }}Combo(target_el_id){

	var combo = new Ext.form.ComboBox({
		
	});
}