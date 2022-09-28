define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cnework/payslip/index' + location.search,
                    del_url: 'cnework/payslip/del',
                    table: 'cnework_payslip',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showExport: false,
                showColumns: false,
                showToggle: false,
                search: false,
                searchFormVisible: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'month', title: __('Month'), operate: 'LIKE', addclass: 'datetimepicker', extend: 'data-date-format="YYYY-MM"', autocomplete: false,},
                        {field: 'category_id', title: __('Category_id'), visible: false, addclass: 'selectpage', extend: 'data-source="cnework/category/index"'},
                        {field: 'category.name', title: __('Category_id'), operate: false},
                        {field: 'logs_count', title: __('Logs_count'), operate: false},
                        {field: 'admin_id', title: __('操作员'), visible: false, addclass: 'selectpage', extend: 'data-source="auth/admin/index" data-field="nickname"'},
                        {field: 'admin.nickname', title: __('操作员'), operate: false},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"1": __('Status 1'), "2": __('Status 2')},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'sendtime',
                            title: __('发送时间'),
                            operate: false,
                            formatter: function (value, row, index) {
                                if (row.status == 2) {
                                    return Table.api.formatter.datetime.call(this, value, row, index);
                                }
                                return '-';
                            }
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('Detail'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-dialog',
                                extend: 'data-area=\'["80%","100%"]\' data-offset="l"',
                                url: function (data) {
                                    return 'cnework/paysliplog/index?id=' + data.id;
                                }
                            }],
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            
            $('#month').focus(function () {
                setTimeout(function(){
                    console.log($('.bootstrap-datetimepicker-widget').prop("outerHTML"))
                }, 5000);
            }).blur(function () {
                
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
