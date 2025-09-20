define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/bankcard/index' + location.search,
                    add_url: 'user/bankcard/add',
                    edit_url: 'user/bankcard/edit',
                    del_url: 'user/bankcard/del',
                    multi_url: 'user/bankcard/multi',
                    import_url: 'user/bankcard/import',
                    table: 'user_bankcard',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'bianhao', title: __('Bianhao')},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'bank_nums', title: __('Bank_nums'), operate: 'LIKE'},
                        {field: 'bank_zhmc', title: __('Bank_zhmc'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'sort', title: __('Sort')},
                        {field: 'type', title: __('Type'), searchList: {"CNY":__('Cny'),"THB":__('Thb'),"INR":__('Inr'),"TWD":__('Twd')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'sys_status', title: __('Sys_status'), searchList: {"normal":__('Sys_status normal'),"hidden":__('Sys_status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
