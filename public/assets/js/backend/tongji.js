define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'tongji/index' + location.search,
                    add_url: 'tongji/add',
                    edit_url: 'tongji/edit',
                    del_url: 'tongji/del',
                    multi_url: 'tongji/multi',
                    import_url: 'tongji/import',
                    table: 'tongji',
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
						{field: 'tjdate', title: __('Tjdate'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'rujin_num', title: __('Rujin_num')},
                        {field: 'rujin_money', title: __('Rujin_money'), operate:'BETWEEN'},
                        {field: 'rujin_supply_usdt', title: __('Rujin_supply_usdt'), operate:'BETWEEN'},
                        {field: 'rujin_user_usdt', title: __('Rujin_user_usdt'), operate:'BETWEEN'},
                        {field: 'rujin_profit_usdt', title: __('Rujin_profit_usdt'), operate:'BETWEEN'},
                        {field: 'chujin_num', title: __('Chujin_num')},
                        {field: 'chujin_money', title: __('Chujin_money'), operate:'BETWEEN'},
                        {field: 'chujin_supply_usdt', title: __('Chujin_supply_usdt'), operate:'BETWEEN'},
                        {field: 'chujin_user_usdt', title: __('Chujin_user_usdt'), operate:'BETWEEN'},
                        {field: 'chujin_profit_usdt', title: __('Chujin_profit_usdt'), operate:'BETWEEN'},
                        {field: 'user_cz_usdt', title: __('User_cz_usdt'), operate:'BETWEEN'},
                        {field: 'supply_tx_usdt', title: __('Supply_tx_usdt'), operate:'BETWEEN'},
                        {field: 'company_profit', title: __('Company_profit'), operate:'BETWEEN'},
                        {field: 'spark_profit', title: __('Spark_profit'), operate:'BETWEEN'},
                        {field: 'one_profit', title: __('One_profit'), operate:'BETWEEN'},
                        {field: 'two_profit', title: __('Two_profit'), operate:'BETWEEN'},
                        {field: 'user_total_usdt', title: __('User_total_usdt'), operate:'BETWEEN'},
                        {field: 'fanyong', title: __('Fanyong'), operate:'BETWEEN'},
                        {field: 'supply_account1', title: __('Supply_account1'), operate:'BETWEEN'},
                        {field: 'supply_account2', title: __('Supply_account2'), operate:'BETWEEN'},
                        {field: 'supply_account3', title: __('Supply_account3'), operate:'BETWEEN'},
                        {field: 'supply_account4', title: __('Supply_account4'), operate:'BETWEEN'},
                        {field: 'supply_account5', title: __('Supply_account5'), operate:'BETWEEN'},
                        {field: 'supply_account6', title: __('Supply_account6'), operate:'BETWEEN'},
                        {field: 'utime', title: __('Utime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
