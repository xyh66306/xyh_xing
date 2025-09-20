define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/cashin/index' + location.search,
                    add_url: 'order/cashin/add',
                    edit_url: 'order/cashin/edit',
                    del_url: 'order/cashin/del',
                    multi_url: 'order/cashin/multi',
                    import_url: 'order/cashin/import',
                    table: 'order_cashin',
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
                        {field: 'order_id', title: __('Order_id'), operate: 'LIKE'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"1":__('Pay_type 1'),"2":__('Pay_type 2'),"3":__('Pay_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'bi_type', title: __('Bi_type'), searchList: {"1":__('Bi_type 1'),"2":__('Bi_type 2'),"3":__('Bi_type 3')}, formatter: Table.api.formatter.normal},
                        {field: 'act_num', title: __('Act_num'), operate:'BETWEEN'},
                        {field: 'num', title: __('Num'), operate:'BETWEEN'},
                        {field: 'rate', title: __('Rate'), operate:'BETWEEN'},
                        {field: 'receive_name', title: __('Receive_name'), operate: 'LIKE'},
                        {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'bank_account', title: __('Bank_account'), operate: 'LIKE'},
                        {field: 'bank_zhihang', title: __('Bank_zhihang'), operate: 'LIKE'},
                        {field: 'pinzheng_image', title: __('Pinzheng_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4')}, formatter: Table.api.formatter.status},
                        {field: 'pintai', title: __('Pintai'), operate: 'LIKE'},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
