define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'asset/transactions/index' + location.search,
                    add_url: 'asset/transactions/add',
                    edit_url: 'asset/transactions/edit',
                    del_url: 'asset/transactions/del',
                    multi_url: 'asset/transactions/multi',
                    import_url: 'asset/transactions/import',
                    table: 'asset_transactions',
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
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'plan_id', title: __('Plan_id')},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'currency', title: __('Currency')},
                        {field: 'type', title: __('Type'), searchList: {"expense":__('Expense'),"income":__('Income'),"refund":__('Refund'),"transfer":__('Transfer')}, formatter: Table.api.formatter.normal},
                        {field: 'payment_method_id', title: __('Payment_method_id')},
                        {field: 'transaction_date', title: __('Transaction_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Pending'),"success":__('Success'),"failed":__('Failed')}, formatter: Table.api.formatter.status},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
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
