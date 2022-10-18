<?php

namespace Clsms;

class Sms
{
    protected static ?Sms $_instance = null;

    //参数的配置 请登录zz.253.com 获取以下API信息 ↓↓↓↓↓↓↓
    const API_SEND_URL = 'https://smssh1.253.com/msg/v1/send/json'; //创蓝发送短信接口URL

    const API_VARIABLE_URL = 'https://smssh1.253.com/msg/variable/json';//创蓝变量短信接口URL

    const API_BALANCE_QUERY_URL = 'https://smssh1.253.com/msg/balance/json';//创蓝短信余额查询接口URL

    public static array $config = [];
    public static array $state = array(
        0 => array('state' => 2000, 'msg' => '提交成功',),
        101 => array('state' => 1001, 'msg' => '无此用户',),
        102 => array('state' => 1002, 'msg' => '密码错',),
        103 => array('state' => 1003, 'msg' => '提交过快（提交速度超过流速限制）',),
        104 => array('state' => 1004, 'msg' => '系统忙（因平台侧原因，暂时无法处理提交的短信）',),
        105 => array('state' => 1005, 'msg' => '敏感短信（短信内容包含敏感词）',),
        106 => array('state' => 1006, 'msg' => '消息长度错（>536或<=0）',),
        107 => array('state' => 1007, 'msg' => '包含错误的手机号码',),
        108 => array('state' => 1008, 'msg' => '手机号码个数错（群发>50000或<=0;单发>200或<=0）',),
        109 => array('state' => 1009, 'msg' => '无发送额度（该用户可用短信数已使用完）',),
        110 => array('state' => 1010, 'msg' => '不在发送时间内',),
        111 => array('state' => 1011, 'msg' => '超出该账户当月发送额度限制',),
        112 => array('state' => 1012, 'msg' => '无此产品，用户没有订购该产品',),
        113 => array('state' => 1013, 'msg' => 'extno格式错（非数字或者长度不对）',),
        115 => array('state' => 1015, 'msg' => '自动审核驳回',),
        116 => array('state' => 1016, 'msg' => '签名不合法，未带签名（用户必须带签名的前提下）',),
        117 => array('state' => 1017, 'msg' => 'IP地址认证错,请求调用的IP地址不是系统登记的IP地址',),
        118 => array('state' => 1018, 'msg' => '用户没有相应的发送权限',),
        119 => array('state' => 1019, 'msg' => '用户已过期',),
        120 => array('state' => 1020, 'msg' => '未知错误,请联系开发者.',),
    );

    private function __construct($config)
    {
        self::$config = $config;
    }

    public static function getInstance($config): ?Sms
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    /**
     * 发送短信
     *
     * @param string $mobile 手机号码
     * @param string $msg 短信内容
     * @param string $needstatus 是否需要状态报告
     */
    public function sendSMS(string $mobile, string $msg, string $needstatus = 'true'): bool|string
    {
        $sign = empty(self::$config['sign']) ? '253云通讯' : self::$config['sign'];
        if (!str_contains($msg, $sign)) {
            $msg = "【" . $sign . "】" . $msg;
        }

        //创蓝接口参数
        $postArr = array(
            'account' => self::$config['account'],
            'password' => self::$config['password'],
            'msg' => mb_convert_encoding($msg, 'UTF-8', 'auto'),
            'phone' => $mobile,
            'report' => $needstatus,
        );
        return $this->curlPost(self::API_SEND_URL, $postArr);
    }

    /**
     * 发送变量短信
     *
     * @param string $msg 短信内容
     * @param string $params 最多不能超过1000个参数组
     */
    public function sendVariableSMS(string $msg, string $params): bool|string
    {

        //创蓝接口参数
        $postArr = array(
            'account' => self::$config['account'],
            'password' => self::$config['password'],
            'msg' => $msg,
            'params' => $params,
            'report' => 'true'
        );

        return $this->curlPost(self::API_VARIABLE_URL, $postArr);
    }


    /**
     * 查询额度
     *
     *  查询地址
     */
    public function queryBalance(): bool|string
    {

        //查询参数
        $postArr = array(
            'account' => self::$config['account'],
            'password' => self::$config['password'],
        );
        return $this->curlPost(self::API_BALANCE_QUERY_URL, $postArr);
    }


    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return string|bool
     *
     */
    private function curlPost(string $url, array $postFields): string|bool
    {
        $postFields = json_encode($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
            )
        );
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //若果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 " . $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        return $result;
    }
}
