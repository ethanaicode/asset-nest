define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'asset/items/index' + location.search,
                    add_url: 'asset/items/add',
                    edit_url: 'asset/items/edit',
                    del_url: 'asset/items/del',
                    multi_url: 'asset/items/multi',
                    import_url: 'asset/items/import',
                    table: 'asset_items',
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
                        {field: 'id', title: __('Id')},
                        {field: 'category_id', title: __('Category_id'), visible: false, searchList: categoryList},
                        {field: 'category_name', title: __('Category_name'), operate: false, formatter: function(value, row, index) {
                            // 通过 category_id 映射显示分类名称，如果是0则显示 None
                            return categoryList[row.category_id] || __('None');
                        }},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'cover_url', title: __('Cover_url'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'type', title: __('Type'), searchList: {"service":__('Service'),"asset":__('Asset'),"account":__('Account')}, formatter: Table.api.formatter.normal},
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
                var hasPlanInput = form.find("#c-plan-has_plan");
                var typeInput = form.find("#c-plan-type");
                var cycleInput = form.find("#c-plan-billing_cycle");

                if (!hasPlanInput.length || !typeInput.length || !cycleInput.length) {
                    return;
                }

                var fields = {
                    type: form.find('[data-plan-field="type"]'),
                    currency: form.find('[data-plan-field="currency"]'),
                    one_time_price: form.find('[data-plan-field="one_time_price"]'),
                    purchase_date: form.find('[data-plan-field="purchase_date"]'),
                    recurring_price: form.find('[data-plan-field="recurring_price"]'),
                    billing_cycle: form.find('[data-plan-field="billing_cycle"]'),
                    billing_day: form.find('[data-plan-field="billing_day"]'),
                    start_date: form.find('[data-plan-field="start_date"]'),
                    end_date: form.find('[data-plan-field="end_date"]'),
                    default_payment_method_id: form.find('[data-plan-field="default_payment_method_id"]')
                };

                var toggleField = function (group, enabled, required) {
                    if (!group || !group.length) {
                        return;
                    }
                    var controls = group.find("input,select,textarea");
                    group.toggle(enabled);
                    controls.prop("disabled", !enabled);
                    controls.removeAttr("data-rule");
                    if (enabled && required) {
                        controls.attr("data-rule", "required");
                    }
                    var picker = controls.filter(".selectpicker");
                    if (picker.length && $.isFunction(picker.selectpicker)) {
                        picker.selectpicker("refresh");
                    }
                };

                var getTypeValue = function () {
                    var value = typeInput.val();
                    return (value === "recurring") ? "recurring" : "one_time";
                };

                var getCycleValue = function () {
                    var value = cycleInput.val();
                    return (value === "yearly") ? "yearly" : "monthly";
                };

                var updateFields = function () {
                    var hasPlan = hasPlanInput.is(":checked");
                    var type = getTypeValue();
                    var cycle = getCycleValue();
                    var isOneTime = type === "one_time";
                    var isRecurring = type === "recurring";
                    var isMonthlyRecurring = isRecurring && cycle === "monthly";

                    toggleField(fields.type, hasPlan, true);
                    toggleField(fields.currency, hasPlan, true);
                    toggleField(fields.one_time_price, hasPlan && isOneTime, true);
                    toggleField(fields.purchase_date, hasPlan && isOneTime, true);
                    toggleField(fields.recurring_price, hasPlan && isRecurring, true);
                    toggleField(fields.billing_cycle, hasPlan && isRecurring, true);
                    toggleField(fields.billing_day, hasPlan && isMonthlyRecurring, true);
                    toggleField(fields.start_date, hasPlan && isRecurring, true);
                    toggleField(fields.end_date, hasPlan && isRecurring, true);
                    toggleField(fields.default_payment_method_id, hasPlan, true);
                };

                hasPlanInput.on("change", updateFields);
                typeInput.on("change changed.bs.select", updateFields);
                cycleInput.on("change changed.bs.select", updateFields);

                updateFields();
                setTimeout(updateFields, 0);
            }
        }
    };
    return Controller;
});
