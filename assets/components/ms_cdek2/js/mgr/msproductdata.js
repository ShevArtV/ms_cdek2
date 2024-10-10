miniShop2.plugin.mscdek = {
    getFields: function (config) {
        return {
            mscdek_size: {
                fieldLabel: _('ms_cdek2_size'),
                xtype: 'textfield',
                description: '<b>[[+mscdek_size]]</b><br />' + _('ms_cdek2_size_help')
            }
        }
    },
    getColumns: function (config) {
        return {
            mscdek_size: {
                header: _('ms_cdek2_size'),
                width: 50,
                sortable: false,
                editor: {
                    xtype: 'textfield',
                    name: 'mscdek_size'
                }
            }
        }
    }
};