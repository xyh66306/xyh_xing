define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/rebate/index' + location.search,
                    add_url: 'user/rebate/add',
                    edit_url: 'user/rebate/edit',
                    del_url: 'user/rebate/del',
                    multi_url: 'user/rebate/multi',
                    import_url: 'user/rebate/import',
                    table: 'user_rebate',
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
                        {field: 'type', title: __('Type'), searchList: {"ewm":__('Ewm'),"bank":__('Bank')}, formatter: Table.api.formatter.normal},
                        {field: 'churu', title: __('Churu'), searchList: {"duiru":__('Duiru'),"duichu":__('Duichu')}, formatter: Table.api.formatter.normal},
                        {field: 'bi', title: __('Bi'), searchList: {"INR":__('Inr'),"THB":__('Thb'),"CNY":__('Cny')}, formatter: Table.api.formatter.normal},
                        {field: 'min_usdt', title: __('Min_usdt')},
                        {field: 'max_usdt', title: __('Max_usdt')},
                        {field: 'rate', title: __('Rate')},
                        {field: 'utime', title: __('Utime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
