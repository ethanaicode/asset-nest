define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'asset/plans/index' + location.search,
                    add_url: 'asset/plans/add',
                    edit_url: 'asset/plans/edit',
                    del_url: 'asset/plans/del',
                    multi_url: 'asset/plans/multi',
                    import_url: 'asset/plans/import',
                    table: 'asset_plans',
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
                        {field: 'type', title: __('Type'), searchList: {"one_time":__('One_time'),"recurring":__('Recurring')}, formatter: Table.api.formatter.normal},
                        {field: 'currency', title: __('Currency')},
                        {field: 'one_time_price', title: __('One_time_price'), operate:'BETWEEN'},
                        {field: 'purchase_date', title: __('Purchase_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'recurring_price', title: __('Recurring_price'), operate:'BETWEEN'},
                        {field: 'billing_cycle', title: __('Billing_cycle'), searchList: {"monthly":__('Monthly'),"yearly":__('Yearly')}, formatter: Table.api.formatter.normal},
                        {field: 'billing_day', title: __('Billing_day')},
                        {field: 'start_date', title: __('Start_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'end_date', title: __('End_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'default_payment_method_id', title: __('Default_payment_method_id')},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
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
