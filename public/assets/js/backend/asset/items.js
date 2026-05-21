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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
