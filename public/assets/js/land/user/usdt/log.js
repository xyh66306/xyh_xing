define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/usdt/log/index' + location.search,
                    add_url: 'user/usdt/log/add',
                    edit_url: 'user/usdt/log/edit',
                    del_url: 'user/usdt/log/del',
                    multi_url: 'user/usdt/log/multi',
                    import_url: 'user/usdt/log/import',
                    table: 'user_usdt_log',
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
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1'),"2":__('Type 2'),"3":__('Type 3'),"4":__('Type 4'),"5":__('Type 5'),"6":__('Type 6')}, formatter: Table.api.formatter.normal},
                        {field: 'flow_type', title: __('Flow_type'), searchList: {"1":__('Flow_type 1'),"2":__('Flow_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'usdt', title: __('Usdt'), operate:'BETWEEN'},
                        {field: 'before', title: __('Before'), operate:'BETWEEN'},
                        {field: 'after', title: __('After'), operate:'BETWEEN'},
                        {field: 'memo', title: __('Memo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'beizhu', title: __('Beizhu'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
