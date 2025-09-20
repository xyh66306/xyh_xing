define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/profit/index' + location.search,
                    add_url: 'company/profit/add',
                    edit_url: 'company/profit/edit',
                    del_url: 'company/profit/del',
                    multi_url: 'company/profit/multi',
                    import_url: 'company/profit/import',
                    table: 'company_profit',
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
                        {field: 'source_id', title: __('Source_id'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'),searchList: {"1":__('Type 1'),"2":__('Type 2'),"3":__('Type 3'),"4":__('Type 4'),"5":__('Type 5')}, formatter: Table.api.formatter.normal},
                        {field: 'user_type', title: __('User_type'), searchList: {"1":__('User_type 1'),"2":__('User_type 2'),"3":__('User_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'flow_type', title: __('Flow_type'), searchList: {"1":__('Flow_type 1'),"2":__('Flow_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'order_usdt', title:"交易数量", operate:'BETWEEN'},
                        {field: 'usdt', title: __('Usdt'), operate:'BETWEEN'},
                        {field: 'before', title: __('Before'), operate:'BETWEEN'},
                        {field: 'after', title: __('After'), operate:'BETWEEN'},                        
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
