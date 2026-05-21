define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'asset/categories/index' + location.search,
                    add_url: 'asset/categories/add',
                    edit_url: 'asset/categories/edit',
                    del_url: 'asset/categories/del',
                    multi_url: 'asset/categories/multi',
                    import_url: 'asset/categories/import',
                    table: 'asset_categories',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'parent_id', title: __('Parent_id'), visible: false, searchList: categoryList},
                        {field: 'parent_name', title: __('Parent_name'), operate: false, formatter: function(value, row, index) {
                            // 通过 parent_id 映射显示分类名称，如果是0则显示 None
                            return categoryList[row.parent_id] || __('None');
                        }},
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
