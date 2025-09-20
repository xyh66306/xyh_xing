define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/index' + location.search,
                    add_url: 'user/add',
                    edit_url: 'user/edit',
                    del_url: 'user/del',
                    multi_url: 'user/multi',
                    import_url: 'user/import',
                    table: 'user',
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
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'usdt', title: __('Usdt'), operate:'BETWEEN'},
                        {field: 'usdt_dj', title: __('Usdt_dj'), operate:'BETWEEN'},
                        {field: 'sfz_fimage', title: __('Sfz_fimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'sfz_bimage', title: __('Sfz_bimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'sfz_pimage', title: __('Sfz_pimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden'),"check":__('Status check')}, formatter: Table.api.formatter.status},
                        {field: 'sfz_status', title: __('Sfz_status'), searchList: {"0":__('Sfz_status 0'),"1":__('Sfz_status 1'),"2":__('Sfz_status 2')}, formatter: Table.api.formatter.sfz_status},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2')}, formatter: Table.api.formatter.pay_status},
                        {field: 'group.name', title: __('Group.name'), operate: 'LIKE'},
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
