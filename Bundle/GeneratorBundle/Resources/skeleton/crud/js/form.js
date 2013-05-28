Ext.define('Ext.custom.{{ entity }}Form',{
	extend:'Ext.form.Panel',
	constructor:function(cnfg){
		
	    this.callParent(arguments);
	    this.initConfig(cnfg);
		this.form.url = '/{{ route_prefix }}/create';
	},
	bodyPadding: 5,
    fieldDefaults: {
        labelAlign: 'left',
        labelWidth: 90,
        anchor: '100%'
    },
	url: '/{{ route_prefix }}/create',
	items:[
	{% for field, metadata in fields %}{% if field != 'id' %}
	
		{% if metadata.type == 'datetime' or metadata.type == 'date' %}
			{
				xtype 			: 	'datefield',
				format			: 	'd.m.Y',
				fieldLabel		:	'{{field}}',
				name				:	'{{field}}'
            },
		{% elseif metadata.constraint == true %}	        
			{
				xtype			:	'{{metadata.constraintEntity}}CustomComboBox',
				fieldLabel		:	'{{field}}',
				name				:	'{{field}}',
			    lastQuery: '',
				listeners: {
				      render: function()
				      {
						this.store.load();
				      }
				    }
			},
		{% elseif metadata.type == 'integer' %}
			{
				xtype 			: 	'numberfield',
				fieldLabel		:	'{{field}}',
				name			:	'{{field}}',
            },
		{% elseif metadata.type == 'boolean' %}
			{
				xtype 			: 	'checkbox',
				fieldLabel		:	'{{field}}',
				name				:	'{{field}}',
				inputValue : true
            },
		{% elseif metadata.type == 'text' %}
			{
				xtype 			: 	'textarea',
				fieldLabel		:	'{{field}}',
				name				:	'{{field}}'
            },
		{% else %}
			{
				xtype 			: 	'textfield',
				fieldLabel		:	'{{field}}',
				name				:	'{{field}}'
           	},

		{% endif %}
	{% endif %}{% endfor  %}
	],

      buttons: [{
          text: 'Cancel',
          handler: function() {
              console.log(this.up('form').getForm().getValues());
          }
      }, {
          text: 'Send',
          handler: function() {
              if (this.up('form').getForm().isValid()) {
				this.up('form').getForm().submit({params:{form:Ext.encode(this.up('form').getForm().getValues())}});
                  this.up('window').hide();
              }
          }
      }]
});