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
                var form = $("form[role=form]");
                Form.api.bindevent(form);
                Controller.api.bindPlanForm(form);
            },
            bindPlanForm: function (form) {
                var typeInput = form.find("#c-type");
                var cycleInput = form.find("#c-billing_cycle");

                var fields = {
                    one_time_price: form.find('[data-plan-field="one_time_price"]'),
                    purchase_date: form.find('[data-plan-field="purchase_date"]'),
                    recurring_price: form.find('[data-plan-field="recurring_price"]'),
                    billing_cycle: form.find('[data-plan-field="billing_cycle"]'),
                    billing_day: form.find('[data-plan-field="billing_day"]'),
                    start_date: form.find('[data-plan-field="start_date"]'),
                    end_date: form.find('[data-plan-field="end_date"]')
                };

                var toggleField = function (group, required) {
                    if (!group || !group.length) {
                        return;
                    }
                    var controls = group.find("input,select,textarea");
                    group.toggle(required);
                    controls.prop("disabled", !group.is(":visible"));
                    if (required) {
                        controls.attr("data-rule", "required");
                    } else {
                        controls.removeAttr("data-rule");
                    }
                    var picker = controls.filter(".selectpicker");
                    if (picker.length && $.isFunction(picker.selectpicker)) {
                        picker.selectpicker("refresh");
                    }
                };

                var getTypeValue = function () {
                    var value = typeInput.val();
                    if (value !== "one_time" && value !== "recurring") {
                        value = "one_time";
                    }
                    return value;
                };

                var getCycleValue = function () {
                    var value = cycleInput.val();
                    if (value !== "monthly" && value !== "yearly") {
                        value = "monthly";
                    }
                    return value;
                };

                var updateFields = function () {
                    var type = getTypeValue();
                    var cycle = getCycleValue();
                    var isOneTime = type === "one_time";
                    var isRecurring = type === "recurring";
                    var isMonthlyRecurring = isRecurring && cycle === "monthly";

                    toggleField(fields.one_time_price, isOneTime);
                    toggleField(fields.purchase_date, isOneTime);
                    toggleField(fields.recurring_price, isRecurring);
                    toggleField(fields.billing_cycle, isRecurring);
                    toggleField(fields.billing_day, isMonthlyRecurring);
                    toggleField(fields.start_date, isRecurring);
                    toggleField(fields.end_date, isRecurring);
                };

                typeInput.on("change changed.bs.select", updateFields);
                cycleInput.on("change changed.bs.select", updateFields);
                updateFields();
                setTimeout(updateFields, 0);
            }
        }
    };
    return Controller;
});
