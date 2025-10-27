define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index' + location.search,
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    import_url: 'user/user/import',
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
                        {field: 'id', title: "ID"},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'usdt', title: __('Usdt'), operate:'BETWEEN'},
                        {field: 'usdt_dj', title: __('Usdt_dj'), operate:'BETWEEN'},
                        {field: 'sfz_fimage', title: __('Sfz_fimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image,

                        },
                        // {field: 'sfz_bimage', title: __('Sfz_bimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image,

                        // },
                        // {field: 'sfz_pimage', title: __('Sfz_pimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image,

                        // },
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden'),"check":__('Status check')}, formatter: Table.api.formatter.status},
                        {field: 'sfz_status', title: __('Sfz_status'), searchList: {"0":__('Sfz_status 0'),"1":__('Sfz_status 1'),"2":__('Sfz_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2')}, formatter: Table.api.formatter.status},
                        {field: 'agent.nickname', title: __('admingroup')},
                         {field: 'invite.nickname',title: __('Invite') },
                        {field: 'dq_status_text', title: __('Region')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on('load-success.bs.table', function (e, data) {
                if(!data.sfz_show){
                    table.bootstrapTable('hideColumn', 'sfz_pimage');
                    table.bootstrapTable('hideColumn', 'sfz_bimage');
                    table.bootstrapTable('hideColumn', 'sfz_fimage');
                }
                $("#usdt").text(data.extend.usdt);
                $("#usdt_dj").text(data.extend.usdt_dj);
                // if (Config.isBoothView) {
                //     table.bootstrapTable('hideColumn', 'sfz_pimage');
                //     table.bootstrapTable('hideColumn', 'newversion');
                //     // 可以继续添加更多需要隐藏的列
                // }
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
