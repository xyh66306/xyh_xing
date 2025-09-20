define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/payewm/index' + location.search,
                    add_url: 'user/payewm/add',
                    edit_url: 'user/payewm/edit',
                    del_url: 'user/payewm/del',
                    multi_url: 'user/payewm/multi',
                    import_url: 'user/payewm/import',
                    table: 'user_payewm',
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
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'pay_skpt', title: __('Pay_skpt'), searchList: {"wxpay":__('Wxpay'),"alipay":__('Alipay')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_nums', title: __('Pay_nums'), operate: 'LIKE'},
                        {field: 'pay_ewm_image', title: __('Pay_ewm_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'shuoming', title: __('Shuoming'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'beizhu', title: __('Beizhu'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'type', title: __('Type'), searchList: {"INR":__('Inr'),"THB":__('Thb'),"CNY":__('Cny')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"hidden":__('Hidden'),"normal":__('Normal')}, formatter: Table.api.formatter.status},
                        {field: 'sys_status', title: __('Sys_status'), searchList: {"normal":__('Sys_status normal'),"hidden":__('Sys_status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
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
