define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'asset/paymentmethods/index' + location.search,
                    add_url: 'asset/paymentmethods/add',
                    edit_url: 'asset/paymentmethods/edit',
                    del_url: 'asset/paymentmethods/del',
                    multi_url: 'asset/paymentmethods/multi',
                    import_url: 'asset/paymentmethods/import',
                    table: 'asset_payment_methods',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"credit_card":__('Credit_card'),"debit_card":__('Debit_card'),"cash":__('Cash'),"bank":__('Bank')}, formatter: Table.api.formatter.normal},
                        {field: 'provider', title: __('Provider'), operate: 'LIKE', searchList: providerList, formatter: Table.api.formatter.normal}, 
                        {field: 'billing_day', title: __('Billing_day'), searchable: false, formatter: function(value, row, index) {
                            return value ? value + ' ' + __('Day') : '-';
                        }},
                        {field: 'due_day', title: __('Due_day'), searchable: false, formatter: function(value, row, index) {
                            return value ? value + ' ' + __('Day') : '-';
                        }},
                        {field: 'credit_limit', title: __('Credit_limit'), operate:'BETWEEN'},
                        {field: 'currency', title: __('Currency'), searchList: currencyList, formatter: Table.api.formatter.normal},
                        {field: 'is_active', title: __('Is_active'), searchList: {"1":__('Active'),"0":__('Inactive')}, formatter: Table.api.formatter.toggle},
                        {field: 'created_at', title: __('Created_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, searchable: false},
                        {field: 'updated_at', title: __('Updated_at'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, searchable: false},
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
                var form = $("form[role=form]");
                var typeField = form.find("#c-type");
                var creditCardFields = form.find(".credit-card-only");

                var toggleCreditCardFields = function () {
                    var isCreditCard = typeField.val() === 'credit_card';
                    creditCardFields.toggle(isCreditCard);
                };

                typeField.on('change changed.bs.select', toggleCreditCardFields);
                toggleCreditCardFields();

                Form.api.bindevent(form);
            }
        }
    };
    return Controller;
});
