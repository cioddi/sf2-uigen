


{% for field, metadata in fields %}
{%- if metadata.constraint == true %}

// *** {{metadata.camelized}} COMBO *** //

{{metadata.camelized}}Combo = Ext.create('Ext.custom.{{metadata.constraintEntity}}CustomComboBox',{
	targetGrid:grid,
    lastQuery: ''
});
{{metadata.camelized}}Combo.on('select',function(){
	store.proxy.extraParams = store.proxy.getFilterValues();
	store.load();
	
	{%- if dndAndConstraint %}
	checkParameterSelection();
	{%- endif %}
});
{{metadata.camelized}}Combo.store.on('load',function(){
	grid.getView().refresh();
});
{{metadata.camelized}}Combo.store.load();
{%- endif %}
{% endfor %}