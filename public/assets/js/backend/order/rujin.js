/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-09-07 11:01:52
 */
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/rujin/index' + location.search,
                    add_url: 'order/rujin/add',
                    edit_url: 'order/rujin/edit',
                    del_url: 'order/rujin/del',
                    multi_url: 'order/rujin/multi',
                    import_url: 'order/rujin/import',
                    table: 'order_rujin',
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
						{field: 'id', title: "ID"},
                        {field: 'orderid', title: __('Orderid'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        // {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        // {field: 'bank_account', title: __('Bank_account'), operate: 'LIKE'},
                        // {field: 'bank_zhihang', title: __('Bank_zhihang'), operate: 'LIKE'},
                        {field: 'huilv', title: __('Huilv')},
                        // {field: 'usdt',  title: __('Act Usdt')},
                        {field: 'user_usdt',  title: __('User Usdt')},
                        // {field: 'user_fee',  title: __('User Fee')},
                        {field: 'supply_usdt',  title: __('Supply Usdt')},
                        // {field: 'supply_fee',  title: __('Supply Fee')},                        
                        {field: 'bi_type', title: __('Bi_Type')},
                        {field: 'order_status',  title: "超时", searchList: {"1":"否","2":"是"}},
                        {field: 'payername', title: __('Payername')},
                        // {field: 'pinzheng_image', title: __('Pinzheng_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4'),"5":__('Pay_status 5')}, formatter: Table.api.formatter.status},
                        {field: 'utime', title: __('Utime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'diqu', title: __('Diqu'), searchList: {"1":__('Diqu 1'),"2":__('Diqu 2'),"3":__('Diqu 3')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'supply.title', title: __('supply title'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });


            table.on('load-success.bs.table',function (e,data){
                console.log(Config.isBoothView);
                if (Config.isBoothView){
                    // table.bootstrapTable('hideColumn', 'user_fee');
                    // table.bootstrapTable('hideColumn', 'supply_fee');
                    // table.bootstrapTable('hideColumn', 'username');
                    table.bootstrapTable('hideColumn', 'supply.title');
                    // table.bootstrapTable('hideColumn', 'user_usdt');
                    // table.bootstrapTable('hideColumn', 'user_fee');
                    table.bootstrapTable('hideColumn', 'diqu');
                } else {
                    // table.bootstrapTable('hideColumn', 'fee');
                }
                table.bootstrapTable('hideColumn', 'diqu');

                $("#supply_price").text(data.extend.supply_price);
                $("#company_price").text(data.extend.company_price);
                $("#user_price").text(data.extend.user_price);

            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        supply: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/rujin/supply' + location.search,
                    add_url: 'order/rujin/add',
                    edit_url: 'order/rujin/editsupply',
                    del_url: 'order/rujin/del',
                    multi_url: 'order/rujin/multi',
                    import_url: 'order/rujin/import',
                    table: 'order_rujin',
                }
            });

            var table = $("#table");
             var extendData = null;

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
                        {field: 'merchantOrderNo', title: __('MerchantOrderNo')},
                        {field: 'orderid', title: __('Orderid'), operate: 'LIKE'},
                        {field: 'pay_type_text', title: __('Pay_type')},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'supply_huilv', title: __('Huilv')},             
                        {field: 'bi_type', title: __('Bi_Type')},
                        {field: 'usdt',  title: __('Order Usdt')},
                        {field: 'supply_fee',  title: __('Supply Fee')},
                        {field: 'supply_usdt',  title: __('Act Usdt')},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4'),"5":__('Pay_status 5')}, formatter: Table.api.formatter.status},
                        // {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'payername', title: __('Payername')},
                        {field: 'ctime_text', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table',function (e,data){
                extendData = data.extend;
                window.tableExtendData = data.extend; // 保留这行以兼容其他可能的使用
                $("#supply_price").text(data.extend.supply_price);
                $("#fee").text(data.extend.supply_fee);
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
