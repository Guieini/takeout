define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'xt/vx/user/index' + location.search,
                    add_url: 'xt/vx/user/add',
                    edit_url: 'xt/vx/user/edit',
                    del_url: 'xt/vx/user/del',
                    multi_url: 'xt/vx/user/multi',
                    import_url: 'xt/vx/user/import',
                    table: 'xt_vx_user',
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

                // 隐藏搜索等展示功能
                // commonSearch: false,
                // search: false,
                // showExport: false,
                // showToggle: false,
                // showColumns: false,


                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        // {field: 'user_id', title: __('用户id'), operate: 'LIKE'},
                        // {field: 'username', title: __('密码'), operate: 'LIKE'},
                        {field: 'username', title: __('用户名'), operate: 'LIKE'},
                        // {field: 'phone', title: __('手机号'), operate: 'LIKE'},
                        // {field: 'gender', title: __('性别')},


                        {
                            field: 'gender',
                            title: __('Gender'),
                            // searchList 确保搜索框正常显示
                            searchList: {1: '男', 0: '女'},
                            // custom 定义颜色：1(男)对应蓝色，2(女)对应粉色
                            custom: {1: 'info', 0: 'danger'},
                            // 使用 normal 渲染成漂亮的圆角标签
                            formatter: Table.api.formatter.normal
                        },
                        // {field: 'head_image', title: __('头像'),operate: 'LIKE'},
                        {
                            field: 'head_image',
                            title: __('头像'),
                            events: Table.api.events.image, // 点击图片可以预览大图
                            formatter: Table.api.formatter.image // 核心：自动把 URL 转成 <img> 标签
                        },
                        // {field: 'province', title: __('省份'), operate: 'LIKE'},
                        // {field: 'city', title: __('城市'), operate: 'LIKE'},
                        {field: 'open_id', title: __('openID'), operate: 'LIKE'},
                        // {field: 'union_id', title: __('用户Union_id'), operate: 'LIKE'},
                        // {field: 'level', title: __('用户等级')},
                        // {field: 'rebate_rate', title: __('比例')},
                        // {field: 'refund_order_count', title: __('已退款订单数')},
                        // {field: 'confirmed_order_count', title: __('已确认收货订单数')},
                        // {field: 'can_withdraw_money', title: __('可提现金额'), operate:'BETWEEN'},
                        // {field: 'can_not_withdraw_money', title: __('不可提现金额'), operate: 'LIKE'},
                        // {field: 'can_withdraw_invite_money', title: __('可提现邀请奖励金额'), operate:'BETWEEN'},
                        // {field: 'can_not_withdraw_invite_money', title: __('不可提现邀请奖励金额'), operate:'BETWEEN'},
                        // {field: 'mend_amount', title: __('修正金额'), operate:'BETWEEN'},
                        // {field: 'sum_income', title: __('累计总收入'), operate:'BETWEEN'},
                        // {field: 'order_count', title: __('总订单数')},
                        // {field: 'order_refund', title: __('订单退款数')},
                        {field: 'inviter_user_id', title: __('邀请人用户 ID'), operate: 'LIKE'},
                        {field: 'invite_count', title: __('成功邀请人数')},



                        // {field: 'month_ranking_time', title: __('月度排行榜更新时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'real_name', title: __('真实姓名'), operate: 'LIKE'},
                        // {field: 'zhifubao', title: __('支付宝账号'), operate: 'LIKE'},
                        // {field: 'apply_withdraw_time', title: __('上次申请提现时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'pre_can_withdraw_money', title: __('上一次可提现金额'), operate:'BETWEEN'},
                        // {field: 'zhifubao_error', title: __('支付宝账号错误标记（0 = 正常，1 = 错误 / 无效）')},
                        // {field: 'withdraw_status', title: __('提现状态（如：0 = 正常，1 = 冻结，2 = 禁用提现）')},
                        // {field: 'mayi_import', title: __('蚂蚁导入标记（如：0 = 未导入，1 = 已导入）')},
                        // {field: 'subscribe_type', title: __('订阅类型')},
                        // {field: 'black_time', title: __('拉黑时间'), operate: 'LIKE'},
                        // （0 = 未关注，1 = 已关注）
                        // {field: 'is_subscribe', title: __('是否关注公众号')},

                        {
                            field: 'is_subscribe',
                            title: '关注状态',
                            searchList: {"1": "已关注", "0": "未关注"},
                            formatter: function (value, row, index) {
                                // 1 = 绿色背景白字，0 = 灰色背景白字
                                if (value == 1) {
                                    return '<span class="label label-success" style="padding: 5px 10px; border-radius: 4px;">已关注</span>';
                                } else {
                                    return '<span class="label label-default" style="padding: 5px 10px; border-radius: 4px;">未关注</span>';
                                }
                            }
                        },
                        {field: 'create_time', title: __('关注时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'createtime', title: __('创建时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        // {field: 'delete_time', title: __('删除时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}


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
