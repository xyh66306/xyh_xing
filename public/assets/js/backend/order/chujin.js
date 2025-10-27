define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/chujin/index' + location.search,
                    add_url: 'order/chujin/add',
                    edit_url: 'order/chujin/edit',
                    del_url: 'order/chujin/del',
                    multi_url: 'order/chujin/multi',
                    import_url: 'order/chujin/import',
                    table: 'order_chujin',
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
                        {field: 'merchantOrderNo', title: __('Merchantorderno'), operate: 'LIKE'},
                        {field: 'realName', title: __('Realname'), operate: 'LIKE'},
                        {field: 'cardNumber', title: __('Cardnumber'), operate: 'LIKE'},
                        {field: 'bankName', title: __('Bankname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'bankBranchName', title: __('Bankbranchname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'pay_type', title: __('Pay_type'), searchList: {"qtpay":__('Pay_type qtpay'),"wxpay":__('Pay_type wxpay'),"alipay":__('Pay_type alipay')}, formatter: Table.api.formatter.normal},
                        {field: 'pay_ewm_image', title: __('Pay_ewm_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'withdrawCurrency', title: "提现金额(CNY)", operate: 'LIKE'},
                        // {field: 'usdt', title: "结算数量(USDT)", operate: 'LIKE'},
                        // {field: 'supply_fee', title: "手续费(USDT)", operate: 'LIKE'},
                        {field: 'user_usdt', title: "承兑商结算数量(USDT)", operate: 'LIKE'},
                        {field: 'supply_usdt', title: "商户结算数量(USDT)", operate: 'LIKE'},
                        {field: 'pay_status', title: __('Pay Status'),searchList: {"0":__('payStatus 0'),"1":__('payStatus 1'),"2":__('payStatus 2'),"3":__('payStatus 3'),"4":__('payStatus 4'),"5":__('payStatus 5'),"6":__('payStatus 6')},formatter: Table.api.formatter.status},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table',function (e,data){
                
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
                    index_url: 'order/chujin/supply' + location.search,
                    add_url: 'order/chujin/add',
                    edit_url: 'order/chujin/editsply',
                    del_url: 'order/chujin/del',
                    multi_url: 'order/chujin/multi',
                    import_url: 'order/chujin/import',
                    cjorder_url: 'order/chujin/cjorder',
                    table: 'order_chujin',
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
                        {field: 'orderid', title: __('Orderid'), operate: 'LIKE'},
                        {field: 'merchantOrderNo', title: __('Merchantorderno'), operate: 'LIKE'},
                        {field: 'realName', title: __('Realname'), operate: 'LIKE'},
                        {field: 'cardNumber', title: __('Cardnumber'), operate: 'LIKE'},
                        {field: 'bankName', title: __('Bankname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'bankBranchName', title: __('Bankbranchname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'pay_ewm_image', title: __('Pay_ewm_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'withdrawCurrency', title: "提现金额(CNY)", operate: 'LIKE'},
                        {field: 'usdt', title: "提现数量(USDT)", operate: 'LIKE'},
                        {field: 'supply_fee', title: "手续费(USDT)", operate: 'LIKE'},
                        {field: 'supply_usdt', title: "结算数量(USDT)", operate: 'LIKE'},
                        {field: 'pay_status', title: __('Pay Status'),searchList: {"0":__('payStatus 0'),"1":__('payStatus 1'),"2":__('payStatus 2'),"3":__('payStatus 3'),"4":__('payStatus 4'),"5":__('payStatus 5'),"6":__('payStatus 6')},formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table',function (e,data){
                $("#supply_price").text(data.extend.supply_price);
                $("#fee").text(data.extend.supply_fee);
            });            
            // 为表格绑定事件
            Table.api.bindevent(table);
        },        
        add: function () {
            Controller.api.bindevent();
        },
        cjorder: function () {
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
