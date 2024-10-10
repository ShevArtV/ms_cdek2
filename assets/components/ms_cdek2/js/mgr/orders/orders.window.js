Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function(){
    if (this.record.addr_properties) {
        if (this.record.addr_properties.point) {
            this.fields.items[0].items[3].items[0].items.push(
                {
                    xtype: 'displayfield',
                    name: 'point',
                    fieldLabel: _('ms_cdek2_point'),
                    anchor: '100%',
                    style: 'border:1px solid #efefef;width:95%;padding:5px;',
                    html: this.record.addr_properties.point
                 }
            );
        }
    }
});