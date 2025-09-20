define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload'], function ($, undefined, Backend, Table, Form, Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supply/tmoney/index' + location.search,
                    add_url: 'supply/tmoney/add',
                    edit_url: 'supply/tmoney/edit',
                    del_url: 'supply/tmoney/del',
                    multi_url: 'supply/tmoney/multi',
                    import_url: 'supply/tmoney/import',
                    table: 'supply_money',
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
                        {field: 'order_id', title: __('Orderid'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'fee', title: __('Fee'), operate:'BETWEEN'},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'bank_account', title: __('Bank_account'), operate: 'LIKE'},
                        {field: 'bank_zhihang', title: __('Bank_zhihang'), operate: 'LIKE'},
                        {field: 'pay_account', title: __('Pay_account'), operate: 'LIKE'},
                        {field: 'pay_ewm_image', title: __('Pay_ewm_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'pinzheng_image', title: __('Pinzheng_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'supply.title', title: __('Supply.title'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
