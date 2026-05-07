<?php

namespace app\controller\api;

use app\common\CoreController;
use think\facade\Db;

class DatabaseController extends CoreController
{
    // 定义数据库表前缀
    protected $tablePrefix = "xt_vx_";
    // 定义数据库名
    protected $dbName = "xiaotuan";

    public function index()
    {
        // check db connection
        try {
            Db::connect();
        } catch (\Exception $e) {
            return "Error connecting to database: " . $e->getMessage() . '<br/>';
        }

        echo "table prefix: " . $this->tablePrefix . '<br/>';
        $result = $this->createDatabase();
        if ($result === true) {
            echo "Database created successfully <br/>";
        } else {
            return "Error creating database: " . $result . '<br/>';
        }

        $this->createAccessToken();
        $this->creatSysSetting();
        $this->createUserInfo();
        $this->createTaobaoBillList();

        $this->createStrictSelect();

        $this->createUserDayRanking();
        $this->createUserMonthRanking();

        $this->createUserWithdrawRecord();

        $this->createIllegalOrderList();

        $this->createFlagTimeTable();
        $this->createUserOtherTaobaoBllId();
        $this->createUserZoneId();
    }

    public function createDatabase()
    {
        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS {$this->dbName} default character set utf8mb4 collate utf8mb4_general_ci";
        try {
            Db::execute($sql);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function createAccessToken()
    {
        $tableName = $this->tablePrefix . 'access_token';
        $sql = "create table IF NOT EXISTS $tableName (
             access_token text,
             expire_time timestamp NULL,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table $tableName created successfully <br/>";
    }

    private function creatSysSetting()
    {
        $sql = "create table IF NOT EXISTS {$this->tablePrefix}sys_setting (
             config_name varchar(20),
             config_value varchar(20),
             config_desc varchar(60),
             id int not null auto_increment, primary key(id))";
        if (Db::execute($sql) === TRUE) {
            echo "table $this->tablePrefix sys_setting created successfully <br/>";
        } else {
            echo "Error creating table: $this->tablePrefix sys_setting <br/>";
        }
    }

    private function createStrictSelect()
    {
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}strict_select_goods (
             goods_name varchar(200),
             price varchar(20),
             origin_price VARCHAR(20),
             goods_img VARCHAR(300),
             goods_param VARCHAR(300),
             id int not null auto_increment, primary key(id))";
        if (Db::execute($sql) === TRUE) {
            echo "table $this->tablePrefix strict_select_goods created successfully <br/>";
        } else {
            echo "Error creating table: $this->tablePrefix strict_select_goods <br/>";
        }
    }

    private function createUserInfo()
    {
        //user_id 用户唯一id,邀请时可以用到
        //taobao_bill_id 淘宝订单尾号id，用来和订单关联
        //refund_order_count 退款订单数量
        //confirmed_order_count 已确认订单数量
        //rebate_rate 返现百分比，和level对应
        //withdraw_status 0未提现，1提现中，2提现完成
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}user (
             user_id int, UNIQUE INDEX(user_id),
             account VARCHAR(60),
             password VARCHAR(30),
             username varchar(300),
             gender int DEFAULT 0,
             head_image text,
             province VARCHAR(30),
             city VARCHAR(60),
             source VARCHAR(30),
             open_id VARCHAR(60), INDEX(open_id),
             union_id VARCHAR(60) DEFAULT '',
             taobao_bill_id VARCHAR(30) DEFAULT '', INDEX(taobao_bill_id),
             jd_bill_id VARCHAR(30) DEFAULT '',
             pdd_bill_id VARCHAR(30) DEFAULT '',
             level  int DEFAULT  1,
             rebate_rate  int DEFAULT 50,
             refund_order_count int DEFAULT 0,
             confirmed_order_count int DEFAULT 0,
             can_withdraw_money FLOAT DEFAULT 0.0,
             can_not_withdraw_money VARCHAR(10) DEFAULT '0',
             can_withdraw_invite_money FLOAT DEFAULT 0.0,
             can_not_withdraw_invite_money FLOAT DEFAULT 0.0,
             mend_amount float DEFAULT 0,
             sum_income FLOAT DEFAULT 0.0,
             order_count int DEFAULT 0,
             order_refund int DEFAULT 0,
             inviter_user_id VARCHAR(60) DEFAULT '',
             invite_count int DEFAULT 0,
             create_time timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
             month_ranking_time timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
             real_name VARCHAR(60),
             zhifubao VARCHAR(60),
             apply_withdraw_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
             pre_can_withdraw_money FLOAT DEFAULT 0,
             zhifubao_error int DEFAULT 0,
             withdraw_status int DEFAULT 0,
             mayi_import int DEFAULT 0,
             subscribe_type int DEFAULT 0,
             black_time VARCHAR(60),
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table user created successfully <br/>";
    }

    private function createUserDayRanking()
    {
        $tableName = $this->tablePrefix . "user_day_ranking";
        $sql = "create table  IF NOT EXISTS $tableName (
             date timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
             open_id varchar(60),
             taobao_bill_id varchar(11),
             refund_order_count int DEFAULT 0,
             order_count int DEFAULT 0,
             invite_count int DEFAULT 0,
             income int DEFAULT 0,
             update_time timestamp DEFAULT CURRENT_TIMESTAMP,
             username varchar(60) DEFAULT '',
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table $tableName created successfully <br/>";
    }

    //dt_order_msg 退订模板消息，当月有效，下个月自动开启
    private function createUserMonthRanking()
    {
        $tableName = $this->tablePrefix . "user_month_ranking";
        //is_transfer_balance 该月是否转入余额
        //already_withdraw_money 该月已提现金额 already_withdraw_money = invite_income + sum_income
        //is_withdraw  该月是否提现，体现完成后该字段置为1
        //sum_income 返现收入
        //invite_income 邀请收入
        $sql = "create table  IF NOT EXISTS $tableName (
             the_month varchar(30) DEFAULT '',
             username varchar(300),
             open_id varchar(60), INDEX(open_id),
             taobao_bill_id varchar(11), INDEX(taobao_bill_id),
             refund_order_count int DEFAULT 0,
             order_count int DEFAULT 0,
             valid_count int DEFAULT 0,
             illegal_count int DEFAULT 0,
             invite_count int DEFAULT 0,
             invite_income FLOAT DEFAULT 0.0,
             sum_income FLOAT DEFAULT 0.0,
             settle_income FLOAT DEFAULT 0.0,
             already_withdraw_money FLOAT DEFAULT 0,
             update_time timestamp  DEFAULT CURRENT_TIMESTAMP,
             is_transfer_balance int DEFAULT 0,
             is_withdraw int DEFAULT 0,
             dt_order_msg int DEFAULT 0,
             tixian_amount FLOAT DEFAULT 0,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table $tableName created successfully <br/>";

        //$indexSql = "ALTER TABLE $tableName ADD INDEX taobao_bill_id ( taobao_bill_id) ";
        // M()->execute($indexSql);
    }

    //创建用户提现表
    // withdraw_type 0普通提现， withdraw_type 1 邀请提现金额
    private function createUserWithdrawRecord()
    {
        //transfer_time 用户提现时间
        //pay_time   载盟打款时间
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}user_withdraw_record (
             open_id varchar(60),
             taobao_bill_id varchar(11), INDEX(taobao_bill_id),
             withdraw_money float DEFAULT 0.0,
             apply_time timestamp DEFAULT CURRENT_TIMESTAMP,
             transfer_time timestamp  DEFAULT CURRENT_TIMESTAMP,
             pay_time timestamp DEFAULT CURRENT_TIMESTAMP,
             real_name varchar(60),
             zhifubao varchar(60),
             withdraw_type int DEFAULT 0,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table user_withdraw_record created successfully <br/>";
    }

    //淘宝订单
    //tk_status 3：订单结算， 12：订单付款，  13：订单失效，14: 订单成功
    //pub_share_pre_fee  阿里妈妈后台的分成金额
    //real_share_pre_fee 给用户结算的返利金额 pub_share_pre_fee*用户表的比例
    //order_status 0：淘宝未结算; 3：淘宝已结算; 5: 转入用户余额中，转入vx_user表; 7: 违规订单
    // taobao_bill_id 淘宝订单尾号后6位数
    //invite_order_status, 邀请订单状态，0未结算，1已结算，进入余额
    // order_feature = 1  表示是绑定广告id
    private function createTaobaoBillList()
    {
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}taobao_bill_list (
             trade_parent_id varchar(60),
             trade_id VARCHAR(60), UNIQUE INDEX(trade_id),
             taobao_bill_id VARCHAR(11), INDEX(taobao_bill_id),
             num_iid VARCHAR(30),
             item_img text,
             item_title VARCHAR(100),
             short_title VARCHAR(60),
             item_num  int,
             price  float DEFAULT 0,
             pay_price float DEFAULT 0,
             seller_nick VARCHAR(40),
             seller_shop_title VARCHAR(40),
             commission FLOAT ,
             commission_rate FLOAT,
             create_time timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
             earning_time timestamp NULL,
             tk_status int, INDEX(tk_status),
             order_status int DEFAULT 0, INDEX(order_status),
             invite_order_status int DEFAULT 0,
             tk3rd_type VARCHAR(30),
             tk3rd_pub_id int,
             order_type VARCHAR(20),
             income_rate VARCHAR(20) ,
             pub_share_pre_fee VARCHAR(20),
             real_share_pre_fee VARCHAR(20),
             subsidy_rate VARCHAR(20),
             subsidy_type VARCHAR(20),
             terminal_type VARCHAR(20),
             auction_category VARCHAR(20),
             site_id VARCHAR(20),
             site_name VARCHAR(20),
             adzone_id VARCHAR(20),
             adzone_name VARCHAR(20),
             alipay_total_price VARCHAR(20),
             total_commission_rate VARCHAR(20),
             total_commission_fee VARCHAR(20),
             subsidy_fee VARCHAR(20),
             relation_id int,
             special_id int,
             open_id VARCHAR(60),
             is_weiquan int DEFAULT 0,
             is_send_message int DEFAULT 0,
             technology_service_fee FLOAT DEFAULT 0,
             order_feature int DEFAULT 0,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table taobao_bill_list created successfully <br/>";
    }

    private function createIllegalOrderList()
    {
        //transfer_time 用户提现时间
        //pay_time   载盟打款时间
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}illegal_order_list (
             weixin_id varchar(60),
             taobao_bill_id varchar(11), UNIQUE INDEX(taobao_bill_id),
             sum_order_count int DEFAULT 0,
             illegal_order_count int DEFAULT 0,
             invalid_order_count int DEFAULT 0,
             refund_order_count int DEFAULT 0,
             valid_order_count int DEFAULT 0,
             create_time timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP,
             id int not null auto_increment, primary key(id))";
        if (Db::execute($sql) === TRUE) {
            echo "table black_name_list created successfully <br/>";
        } else {
            echo "Error creating table: black_name_list <br/>";
        }
    }

    //需要时间间隔操作，单独一张表
    private function createFlagTimeTable()
    {
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}flag_time (
             taobao_bill_id varchar(11),
             user_id int,
             update_user_time timestamp NULL,
             balance_time timestamp NULL,
             month_ranking_time  timestamp NULL,
             month_day_time  timestamp NULL,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table flag_time created successfully <br/>";
    }

    //userid associate createUserOtherTaobaoBllId
    //允许一个用户绑定多个淘宝账号的订单
    // other_taobao_bill_id :  324561,123456,654321, 123654
    private function createUserOtherTaobaoBllId()
    {
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}user_other_taobao_bill_id (
             user_id int, INDEX(user_id),
             taobao_bill_id varchar(30) ,
             other_taobao_bill_id varchar(40) ,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table user_other_taobao_bill_id created successfully <br/>";
    }

    //userid associate zone_id
    private function createUserZoneId()
    {
        $sql = "create table  IF NOT EXISTS {$this->tablePrefix}user_zone_id (
             user_id varchar(11), UNIQUE INDEX(user_id),
             adzone_id varchar(120) ,
             id int not null auto_increment, primary key(id))";
        Db::execute($sql);
        echo "table user_zone_id created successfully <br/>";
    }
}
