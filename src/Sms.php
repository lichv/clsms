<?php
namespace Clsms;

class Sms{
	protected static $_instance = null;
	public static $openurl='http://222.73.117.156/msg/HttpBatchSendSM?needstatus=true';

	public static $config=[];
	public static $state = array(
					0    => array('state'=>200,	'msg'=>'提交成功', ),
					101  => array('state'=>101, 'msg'=>'无此用户', ),
					102  => array('state'=>102, 'msg'=>'密码错', ),
					103  => array('state'=>103, 'msg'=>'提交过快（提交速度超过流速限制）', ),
					104  => array('state'=>104, 'msg'=>'系统忙（因平台侧原因，暂时无法处理提交的短信）', ),
					105  => array('state'=>105, 'msg'=>'敏感短信（短信内容包含敏感词）', ),
					106  => array('state'=>106, 'msg'=>'消息长度错（>536或<=0）', ),
					107  => array('state'=>107, 'msg'=>'包含错误的手机号码', ),
					108  => array('state'=>108, 'msg'=>'手机号码个数错（群发>50000或<=0;单发>200或<=0）', ),
					109  => array('state'=>109, 'msg'=>'无发送额度（该用户可用短信数已使用完）', ),
					110  => array('state'=>110, 'msg'=>'不在发送时间内', ),
					111  => array('state'=>111, 'msg'=>'超出该账户当月发送额度限制', ),
					112  => array('state'=>112, 'msg'=>'无此产品，用户没有订购该产品', ),
					113  => array('state'=>113, 'msg'=>'extno格式错（非数字或者长度不对）', ),
					115  => array('state'=>115, 'msg'=>'自动审核驳回', ),
					116  => array('state'=>116, 'msg'=>'签名不合法，未带签名（用户必须带签名的前提下）', ),
					117  => array('state'=>117, 'msg'=>'IP地址认证错,请求调用的IP地址不是系统登记的IP地址', ),
					118  => array('state'=>118, 'msg'=>'用户没有相应的发送权限', ),
					119  => array('state'=>119, 'msg'=>'用户已过期', ),
					120  => array('state'=>120, 'msg'=>'未知错误,请联系开发者.', ),
				);

	protected function __construct($config){
		self::$config = $config;
	}

	public static function getInstance($config){
		if (!isset(self::$_instance)) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	//发送消息
	public static function send($data){
		if(empty($data['to'])|| empty($data['content'])){
			return ['state'=>5001,'msg'=>'缺少参数'];
		}
		
		$post_data = array();
		$post_data['account'] = iconv('GB2312', 'GB2312', self::$config['account']);
		$post_data['pswd'] = iconv('GB2312', 'GB2312', self::$config['password']);
		$post_data['mobile'] = $data['to'];
		$post_data['msg'] = mb_convert_encoding($data['content'],'UTF-8', 'auto');

		$o = "";
		foreach ($post_data as $k=>$v){
		   $o.= "$k=".urlencode($v)."&";
		}
		$post_data = substr($o,0,-1);
		ob_start();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, self::$openurl);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$result = curl_exec($ch);

		$res = ob_get_contents();
		ob_end_clean();
		$r = explode(PHP_EOL,$res );
		if( isset($r[0]) ) {
			$tmp = explode(',',$r[0] );
			if( isset($tmp[1]) && isset(self::$state[$tmp[1]]) ){
				return self::$state[$tmp[1]];
			}
		}
		return self::$state[120];
	}
}