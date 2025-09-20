define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supply/supply/index' + location.search,
                    add_url: 'supply/supply/add',
                    edit_url: 'supply/supply/edit',
                    del_url: 'supply/supply/del',
                    multi_url: 'supply/supply/multi',
                    import_url: 'supply/supply/import',
                    table: 'supply',
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
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        // {field: 'money', title: __('Money')},
                        // {field: 'freeze_money', title: __('Freeze Money')},
                        {field: 'usdt', title: __('Usdt')},
                        {field: 'freeze_usdt', title: __('Freeze Usdt')},
                        {field: 'access_key', title: __('Access_key')},
                        {field: 'access_secret', title: __('Access_secret')},
                        {field: 'ip', title: __('Ip'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
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
