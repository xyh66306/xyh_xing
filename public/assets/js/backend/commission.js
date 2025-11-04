define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'commission/index' + location.search,
                    add_url: 'commission/add',
                    edit_url: 'commission/edit',
                    del_url: 'commission/del',
                    multi_url: 'commission/multi',
                    import_url: 'commission/import',
                    table: 'commission',
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
                        {field: 'fy_orderid', title: __('Fy_orderid'), operate: 'LIKE'},
                        {field: 'p4b_orderid', title: __('P4b_orderid'), operate: 'LIKE'},
                        {field: 'number', title: __('Number'), operate:'BETWEEN'},
                        // {field: 'rate', title: __('Rate'), operate:'BETWEEN'},
                        {field: 'rate', title: __('Rate'), operate: 'BETWEEN', formatter: function (value, row, index) {
                            return value + ' %';
                        }},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'type', title: __('Type'), searchList: {"1":__('Type 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'level', title: __('Level')},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'chaoshi', title: __('Chaoshi'), searchList: {"1":__('Chaoshi 1'),"2":__('Chaoshi 2')}, formatter: Table.api.formatter.normal},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'utime', title: __('Utime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'puser.username', title: __('Puser.username'), operate: 'LIKE'},
                        {field: 'user.username', title: __('User.username'), operate: 'LIKE'},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table',function (e,data){
                
                $("#total").text(data.extend.total);
                $("#duiru").text(data.extend.duiru);
                $("#duichu").text(data.extend.duichu);

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
