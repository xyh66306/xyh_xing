define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/sale/index' + location.search,
                    add_url: 'order/sale/add',
                    edit_url: 'order/sale/edit',
                    del_url: 'order/sale/del',
                    multi_url: 'order/sale/multi',
                    import_url: 'order/sale/import',
                    table: 'order_sale',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'skr', title: __('Skr'), operate: 'LIKE'},
                        {field: 'skzh', title: __('Skzh'), operate: 'LIKE'},
                        {field: 'skyh', title: __('Skyh'), operate: 'LIKE'},
                        {field: 'zhmc', title: __('Zhmc'), operate: 'LIKE'},
                        {field: 'cny', title: __('Cny'), operate:'BETWEEN'},
                        {field: 'pt', title: __('Pt'), operate: 'LIKE'},
                        {field: 'bh', title: __('Bh'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
