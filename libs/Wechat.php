<?php
//你好 Léo & Luka
namespace libs;
//Modified by Bruno on Feb 1st, 2017
//Work only with PUBLIC account

//https://coding.net/u/cjango/p/wechat_sdk/git/blob/master/Wechat.class.php
//https://gist.github.com/guweigang/a1f6eddcb96c5486ffd0
/**
 * 微信PHP-SDK，用于开发单用户版微信公众号管理系统
 * 服务器端必须要有 CURL 支持
 * 2015年8月修正版本
 * 增加：多客服管理相关接口
 * 增加了企业付款和红包的接口
 * @author 、小陈叔叔 <cjango@163.com>
 * http://git.oschina.net/cjango/cjango-sdk
 */
//namespace Tools;

class Wechat {
	/* 获取ACCESS_TOKEN URL */
	const AUTH_URL                = 'https://api.weixin.qq.com/cgi-bin/token';
	/* 菜单相关URL */
	const MENU_CREATE_URL         = 'https://api.weixin.qq.com/cgi-bin/menu/create';
	const MENU_GET_URL            = 'https://api.weixin.qq.com/cgi-bin/menu/get';
	const MENU_DELETE_URL         = 'https://api.weixin.qq.com/cgi-bin/menu/delete';
	/* 用户及用户分组URL */
	const USER_GET_URL            = 'https://api.weixin.qq.com/cgi-bin/user/get';
	const USER_INFO_URL           = 'https://api.weixin.qq.com/cgi-bin/user/info';
	const USER_IN_GROUP           = 'https://api.weixin.qq.com/cgi-bin/groups/getid';
	const GROUP_GET_URL           = 'https://api.weixin.qq.com/cgi-bin/groups/get';
	const GROUP_CREATE_URL        = 'https://api.weixin.qq.com/cgi-bin/groups/create';
	const GROUP_UPDATE_URL        = 'https://api.weixin.qq.com/cgi-bin/groups/update';
	const GROUP_MEMBER_UPDATE_URL = 'https://api.weixin.qq.com/cgi-bin/groups/members/update';
	/* 发送客服消息URL */
	const CUSTOM_SEND_URL         = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
	/* 二维码生成 URL*/
	const QRCODE_URL              = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
	const QRCODE_SHOW_URL         = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
	/* OAuth2.0授权地址 */
	const OAUTH_AUTHORIZE_URL     = 'https://open.weixin.qq.com/connect/oauth2/authorize';
	const OAUTH_USER_TOKEN_URL    = 'https://api.weixin.qq.com/sns/oauth2/access_token';
	const OAUTH_GET_USERINFO	  = 'https://api.weixin.qq.com/sns/userinfo';
	/* 获取模板ID */
	const GET_TEMPLATE_ID         = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template';
	/* 消息模板 */
	const TEMPLATE_SEND			  = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
	/* JSAPI_TICKET获取地址 */
	const JSAPI_TICKET_URL        = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
	/* 统一下单地址 */
	const UNIFIED_ORDER_URL       = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	/* 订单状态查询 */
	const ORDER_QUERY_URL         = 'https://api.mch.weixin.qq.com/pay/orderquery';
	/* 关闭订单 */
	const CLOSE_ORDER_URL         = 'https://api.mch.weixin.qq.com/pay/closeorder';
	/* 退款地址 需要证书*/
	const PAY_REFUND_ORDER	      = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
	/* 退款查询地址 */
	const REFUND_QUERY_URL        = 'https://api.mch.weixin.qq.com/pay/refundquery';
	/* 企业付款 */
	const PAY_TRANSFERS_URL       = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
	/* 企业付款查询 */
	const GET_PAY_TRANSFERS       = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
	/* 下载对账单 */
	const DOWNLOAD_BILL_URL       = 'https://api.mch.weixin.qq.com/pay/downloadbill';
	/* 发放红包高级接口 */
	const SEND_RED_PACK           = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	/* 发送裂变红包接口 */
	const SEND_GROUP_RED_PACK     = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
	/* 红包查询接口 */
	const GET_RED_PACK_INFO       = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
	/* 转换短链接 */
	const GET_SHORT_URL           = 'https://api.mch.weixin.qq.com/tools/shorturl';
	/* 多客服相关URL */
	const GET_KF_LIST             = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist';
	const GET_ONLINE_KF_LIST      = 'https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist';
	const ADD_KF_URL              = 'https://api.weixin.qq.com/customservice/kfaccount/add';
	const UPDATE_KF_URL           = 'https://api.weixin.qq.com/customservice/kfaccount/update';
	const DELETE_KF_URL           = 'https://api.weixin.qq.com/customservice/kfaccount/del';
	const UPLOAD_KF_HEADIMG       = 'Http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg';
	const GET_KF_MSGRECORD        = 'https://api.weixin.qq.com/customservice/msgrecord/getrecord';
	/* 素材管理 */
	const MEDIA_UPLOAD_URL        = 'https://api.weixin.qq.com/cgi-bin/media/upload';               // 新增临时素材
	const MEDIA_GET_URL           = 'https://api.weixin.qq.com/cgi-bin/media/get';                  // 获取临时素材
	const MATERIAL_NEWS_URL       = 'https://api.weixin.qq.com/cgi-bin/material/add_news';          // 新增永久图文素材
	const MATERIAL_MATERIAL_URL   = 'https://api.weixin.qq.com/cgi-bin/material/add_material';      // 新增永久素材
	const MATERIAL_GET_URL        = 'https://api.weixin.qq.com/cgi-bin/material/get_material';      // 获取永久素材 1
	const MATERIAL_DEL_URL        = 'https://api.weixin.qq.com/cgi-bin/material/del_material';      // 删除永久素材 1
	const MATERIAL_UPDATE_URL     = 'https://api.weixin.qq.com/cgi-bin/material/update_news';       // 修改永久图文素材
	const MATERIAL_COUNT_URL      = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount'; // 获取永久素材数量 1
	const MATERIAL_LISTS_URL      = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material'; // 获取永久素材列表 1

	private $account;
	private $token;
	private $appid;
	private $secret;
	private $access_token;
	private $user_token;
	private $debug = false;
	private $data  = array();
	private $send  = array();
	private $error;
	private $ticket;
	private $result;
	private $encode;
	private $AESKey;
	private $mch_id;
	private $payKey;
	private $pemCret;
	private $pemKey;

	public function __construct($options = array()) {
		$this->account      =  isset($options['account'])      ? $options['account']      : '';
		$this->token        =  isset($options['token'])        ? $options['token']        : '';
		$this->appid        =  isset($options['appid'])        ? $options['appid']        : '';
		$this->secret       =  isset($options['secret'])       ? $options['secret']       : '';
		$this->access_token =  isset($options['access_token']) ? $options['access_token'] : '';
		$this->debug        =  isset($options['debug'])        ? $options['debug']        : false;
		$this->encode       = !empty($options['encode'])       ? true                     : false;
		$this->AESKey       =  isset($options['aeskey'])       ? $options['aeskey']       : '';
		$this->mch_id       =  isset($options['mch_id'])       ? $options['mch_id']       : '';
		$this->payKey       =  isset($options['paykey'])       ? $options['paykey']       : '';
		$this->pem          =  isset($options['pem'])          ? $options['pem']          : '';
		if ($this->encode && strlen($this->AESKey) != 43) {
			$this->error = 'AESKey Lenght Error';
			return false;
		}
	}

	/**
	 * 动态设置参数
	 * @param  string $config 配置名称
	 * @param  string $value  配置内容
	 */
	public function setConfig($config, $value) {
		$this->$config = $value;
	}

	public function __get($key) {
		return $this->$key;
	}

	public function __set($key, $value) {
		$this->$key = $value;
	}

	/**
	 * 验证URL有效性,校验数据签名
	 * @return string|boolean
	 */
	public function valid() {
		$echoStr = $_GET["echostr"];
		if (isset($echoStr)) {
			$this->checkSignature() && exit($echoStr);
		} else {
			!$this->checkSignature() && exit('Access Denied!');
		}
		return true;
	}
	/**
	 * 检查用户签名信息
	 * @return boolean
	 */
	public function checkSignature() {
		//如果调试状态，直接返回真
		if ($this->debug) return true;
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce     = $_GET['nonce'];
		if (empty($signature) || empty($timestamp) || empty($nonce)) {
			return false;
		}
		$token = $this->token;
		if (!$token) return false;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		return sha1($tmpStr) == $signature;
	}

	/**
	 * 取得 access_token
	 * @return string|boolean
	 */
	public function getToken() {
		$access_token = $this->access_token;
		if (!empty($access_token)) {
			return $this->access_token;
		}else {
			if ($this->getAccessToken()) {
				return $this->access_token;
			}else {
				return false;
			}
		}
	}

	/**
	 * 从远端接口获取ACCESS_TOKEN
	 * @return string|boolean
	 */
	private function getAccessToken() {
		$params = array(
			'grant_type' => 'client_credential',
			'appid'      => $this->appid,
			'secret'     => $this->secret
		);

		$jsonStr = $this->http(self::AUTH_URL, $params);
		if ($jsonStr) {
			$jsonArr = $this->parseJson($jsonStr);
			if ($jsonArr) {
				return $this->access_token = $jsonArr['access_token'];
			}else {
				return false;
			}
		}else {
			return false;
		}
	}

	/**
	 * 获取自定义菜单
	 * @return array|boolean
	 */
	public function menus() {
		$params = array(
			'access_token' => $this->access_token
		);
		$jsonStr = $this->http(self::MENU_GET_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['menu'];
		}else {
			return false;
		}
	}

	/**
	 * 创建自定义菜单
	 * @param  array $menus 自定义菜单数组
	 * @return boolen
	 */
	public function menu_create($menus = array()) {
		if (empty($menus)) {
			$this->error = '菜单内容必须要填写';
			return false;
		}
		//创建菜单之前，执行删除操作
		//$this->menu_delete();
		$params  = $this->json_encode($menus);
		$url     = self::MENU_CREATE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 删除自定义菜单
	 * @return boolean
	 */
	public function menu_delete() {
		$params = array(
			'access_token' => $this->access_token
		);
		$jsonStr = $this->http(self::MENU_DELETE_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 从远端获取用户分组
	 * @return array|boolean
	 */
	public function groups() {
		$url = self::GROUP_GET_URL.'?access_token='.$this->access_token;
		$jsonStr = $this->http($url);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['groups'];
		}else {
			return false;
		}
	}

	/**
	 * 添加用户分组
	 * @param string $name 分组名称
	 * @return boolean
	 */
	public function group_add($name = '') {
		if (empty($name)) {
			$this->error = '请输入一个分组名称';
			return false;
		}
		$params = array(
			'group' => array(
				'name' => $name
			)
		);
		$params = $this->json_encode($params);
		$url    = self::GROUP_CREATE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['group'];
		}else {
			return false;
		}
	}

	/**
	 * 修改分组名
	 * @param integer $gid 分组编号
	 * @param string $name 分组名称
	 * @return boolean
	 */
	public function group_edit($gid = '', $name = '') {
		if (empty($name) || empty($gid)) {
			$this->error = '请选择一个分组，并输入一个新的名称';
			return false;
		}
		$params = array(
			'group' => array(
				'id'   => $gid,
				'name' => $name
			)
		);
		$params = $this->json_encode($params);
		$url    = self::GROUP_UPDATE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 获取关注者列表
	 * @param  sting $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
	 * @return array|boolean 返回用户信息的一个数组
	 */
	public function users($next_openid = '') {
		!empty($next_openid) && $params['next_openid'] = $next_openid;
		$params['access_token'] = $this->access_token;

		$jsonStr = $this->http(self::USER_GET_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			//优化返回数组的结构
			$openId = $jsonArr['data']['openid'];
			unset($jsonArr['data']);
			while ($jsonArr['count'] == 10000) {
				$next   = self::users($jsonArr['next_openid']);
				$openId = array_merge($openId, $next);
			}
			unset($jsonArr['count']);
			unset($jsonArr['next_openid']);
			$jsonArr['openid'] = $openId;
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 获取用户基本信息
	 * @param  string $openid 用户的OPENID
	 * @return array|boolean  返回用户信息的一个数组
	 */
	public function user($openid = '') {
		if (empty($openid)) {
			$this->error = '请输入一个用户的OpenID';
			return false;
		}
		$params = array(
			'access_token' => $this->access_token,
			'lang'         => 'zh_CN',
			'openid'       => $openid
		);
		$jsonStr = $this->http(self::USER_INFO_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr['subscribe'] == 1) {
			unset($jsonArr['subscribe']);
			return $jsonArr;
		} else {
			$this->error = '用户未关注';
			return false;
		}
	}

	/**
	 * 查询用户所在分组
	 * @param  string $openid  用户OPENID
	 * @return integer|boolean 用户所在分组ID
	 */
	public function user_in_group($openid = '') {
		if (empty($openid)) {
			$this->error = '请输入一个用户的OpenID';
			return false;
		}
		$params = array(
			'openid' => $openid
		);
		$params = json_encode($params);
		$url    = self::USER_IN_GROUP . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['groupid'];
		}else {
			return false;
		}
	}

	/**
	 * 移动用户分组
	 * @param string  $openid 用户OPENID
	 * @param integer $gid 移动到的分组编号
	 * @return boolean
	 */
	public function user_to_group($openid = '', $gid = '') {
		if (empty($openid) || !is_numeric($gid)) {
			$this->error = '请选择一个用户，并指定一个新的分组';
			return false;
		}
		$params = array(
			'openid'     => $openid,
			'to_groupid' => $gid
		);
		$params = json_encode($params);
		$url    = self::GROUP_MEMBER_UPDATE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 获取微信推送的数据,将键值全部转换为小写后返回
	 * @return array 转换为数组后的数据
	 */
	public function request(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)) {
			$data = self::_extractXml($postStr);
			if ($this->encode) {
				$data = $this->AESdecode($data['encrypt']);
			}
			return $this->data = $data;
		}else {
			return false;
		}
	}

	/**
	 * XML文档解析成数组，并将键值转成小写
	 * @param  xml $xml
	 * @return array
	 */
	private function _extractXml($xml) {
		$data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return array_change_key_case($data, CASE_LOWER);
	}

	/**
	 * * 被动响应微信发送的信息（自动回复）
	 * @param  string $to      接收用户名
	 * @param  string $from    发送者用户名
	 * @param  array  $content 回复信息，文本信息为string类型
	 * @param  string $type    消息类型
	 * @param  string $flag    是否新标刚接受到的信息
	 * @return string          XML字符串
	 */
	public function response($content, $type = 'text', $flag = 0){
		/* 基础数据 */
		$this->data = array(
			'ToUserName'   => $this->data['fromusername'],
			'FromUserName' => $this->data['tousername'],
			'CreateTime'   => time(),
			'MsgType'      => $type,
		);
		/* 添加类型数据 */
		$this->$type($content);
		/* 添加状态 */
		$this->data['FuncFlag'] = $flag;
		/* 转换数据为XML */
		$response = self::_array2Xml($this->data);
		if ($this->encode) {
			$nonce                  = $_GET['nonce'];
			$xmlStr['Encrypt']      = $this->AESencode($response);
			$xmlStr['MsgSignature'] = self::getSHA1($xmlStr['Encrypt'], $nonce);
			$xmlStr['TimeStamp']    = NOW_TIME;
			$xmlStr['Nonce']        = $nonce;
			$response = '';
			$response = self::_array2Xml($xmlStr);
		}
		exit($response);
	}

	/**
	 * 对数据进行SHA1签名
	 * @return string
	 */
	public function getSHA1($encrypt_msg, $nonce = '') {
		$array = array($encrypt_msg, $this->token, NOW_TIME, $nonce);
		sort($array, SORT_STRING);
		$str = implode($array);
		return sha1($str);
	}

	/**
	 * 回复文本信息
	 * @param  string $content 要回复的信息
	 */
	private function text($content){
		$this->data['Content'] = $content;
	}

	/**
	 * 回复音乐信息
	 * @param  string $content 要回复的音乐
	 */
	private function music($music){
		list(
			$music['Title'],
			$music['Description'],
			$music['MusicUrl'],
			$music['HQMusicUrl']
		) = $music;
		$this->data['Music'] = $music;
	}

	/**
	 * 回复图文信息
	 * @param  string $news 要回复的图文内容
	 */
	private function news($news){
		$articles = array();
		foreach ($news as $key => $value) {
			list(
				$articles[$key]['Title'],
				$articles[$key]['Description'],
				$articles[$key]['PicUrl'],
				$articles[$key]['Url']
			) = $value;
			if($key >= 9) { break; } //最多只允许10调新闻
		}
		$this->data['ArticleCount'] = count($articles);
		$this->data['Articles'] = $articles;
	}

	/**
	 * 将数组转换成XML
	 */
	private function _array2Xml($array) {
		$xml  = new \SimpleXMLElement('<xml></xml>');
		$this->_data2xml($xml, $array);
		return $xml->asXML();
	}

	/**
	 * 数据XML编码
	 * @param  object $xml  XML对象
	 * @param  mixed  $data 数据
	 * @param  string $item 数字索引时的节点名称
	 * @return string xml
	 */
	private function _data2xml($xml, $data, $item = 'item') {
		foreach ($data as $key => $value) {
			/* 指定默认的数字key */
			is_numeric($key) && $key = $item;
			/* 添加子元素 */
			if(is_array($value) || is_object($value)){
				$child = $xml->addChild($key);
				$this->_data2xml($child, $value, $item);
			} else {
				if(is_numeric($value)){
					$child = $xml->addChild($key, $value);
				} else {
					$child = $xml->addChild($key);
					$node  = dom_import_simplexml($child);
					$node->appendChild($node->ownerDocument->createCDATASection($value));
				}
			}
		}
	}

	/**
	 * 获取模板ID
	 * @param  string $tplId MP中的模板编号
	 * @return string        模板ID
	 */
	public function getTemplateId($tplId) {
		$params = array(
			'template_id_short:' => $tplId
		);
		$params = json_encode($params);
		$url    = self::GET_TEMPLATE_ID . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['template_id'];
		}else {
			return false;
		}

	}

	/**
	 * 发送模板消息
	 * @param  array
	 * @return boolean
	 */
	public function sendTemplate($content) {
		$params = self::json_encode($content);
		$url    = self::TEMPLATE_SEND . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 发送客服消息
	 * @param  string  $openid
	 * @param  string  $content
	 * @param  string  $msgtype
	 * @return boolean
	 */
	public function sendMsg($openid, $content, $msgtype = 'text') {
		/* 基础数据 */
		$this->send['touser']  = $openid;
		$this->send['msgtype'] = $msgtype;
		/* 添加类型数据 */
		$sendtype = 'send' . $msgtype;
		$this->$sendtype(urldecode(urlencode($content)));
		/* 发送 */
		$params = json_encode($this->send,JSON_UNESCAPED_UNICODE);
		
		$url = self::CUSTOM_SEND_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');

		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 发送文本消息
	 * @param string $content 要发送的信息
	 */
	private function sendtext($content) {
		$this->send['text'] = array(
			'content' => $content
		);
	}

	/**
	 * 发送图片消息
	 * @param string $content 要发送的信息
	 */
	private function sendimage($content) {
		$this->send['image'] = array(
			'media_id' => $content
		);
	}

	/**
	 * 发送视频消息
	 * @param  string $content 要发送的信息
	 */
	private function sendvideo($video){
		list (
			$video ['media_id'],
			$video ['title'],
			$video ['description']
		) = $video;

		$this->send ['video'] = $video;
	}

	/**
	 * 发送语音消息
	 * @param string $content 要发送的信息
	 */
	private function sendvoice($content) {
		$this->send['voice'] = array(
			'media_id' => $content
		);
	}

	/**
	 * 发送音乐消息
	 * @param string $content 要发送的信息
	 */
	private function sendmusic($music) {
		list (
			$music['title'],
			$music['description'],
			$music['musicurl'],
			$music['hqmusicurl'],
			$music['thumb_media_id']
		) = $music;
		$this->send['music'] = $music;
	}

	/**
	 * 发送图文消息
	 * @param  string $news 要回复的图文内容
	 */
	private function sendnews($news){
		$articles = array();
		foreach ($news as $key => $value) {
			list(
				$articles[$key]['title'],
				$articles[$key]['description'],
				$articles[$key]['url'],
				$articles[$key]['picurl']
			) = $value;
			if($key >= 9) { break; } //最多只允许10条图文信息
		}
		$this->send['articles'] = $articles;
		$this->send['news'] = array(
			'articles' => $articles, //bruno
		);
	}

	/**
	 * OAuth 授权跳转接口
	 * @param string $callback 回调URI，填写完整地址，带http://
	 * @param sting $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
	 * @param string snsapi_userinfo获取用户授权信息，snsapi_base直接返回openid
	 * @return string
	 */
	public function getOAuthRedirect($callback, $state='', $scope='snsapi_base'){
		return self::OAUTH_AUTHORIZE_URL.'?appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
	}

	/**
	 * 通过code获取Access Token
	 * @return array|boolean
	 */
	public function getOauthAccessToken(){
		$code = isset($_GET['code']) ? $_GET['code'] : '';
		if (!$code) return false;
		$params = array(
			'appid' => $this->appid,
			'secret'=> $this->secret,
			'code'  => $code,
			'grant_type' => 'authorization_code'
		);
		$jsonStr = $this->http(self::OAUTH_USER_TOKEN_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 网页获取用户信息
	 * @param  string $access_token  通过getOauthAccessToken方法获取到的token
	 * @param  string $openid        用户的OPENID
	 * @return array
	 */
	public function getOauthUserInfo($access_token, $openid) {
		$params = array(
			'access_token'  => $access_token,
			'openid'        => $openid,
			'lang'          => 'zh_CN'
		);
		$jsonStr = $this->http(self::OAUTH_GET_USERINFO, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 获取jsapi_ticket
	 */
	public function getJsapiTicket() {
		$params = array(
			'access_token'  => $this->access_token,
			'type'          => 'jsapi'
		);
		$jsonStr = $this->http(self::JSAPI_TICKET_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $this->result['ticket'];
		}else {
			return false;
		}
	}

	/**
	 * 获取二维码图像地址
	 * @param  integer $scene_id 场景值 1-100000整数
	 * @param  boolean $limit    true永久二维码 false 临时
	 * @param  integer $expire   临时二维码有效时间
	 * @return string|boolean    二维码图片地址
	 */
	public function getQRUrl($scene_id = '', $limit = true, $expire = 1800) {
		if (!isset($this->ticket) && !$this->qrcode($scene_id, $limit, $expire)) {
			return false;
		}
		return self::QRCODE_SHOW_URL.'?ticket=' . $this->ticket;
	}

	/**
	 * 生成推广二维码
	 * @param  integer $scene_id 场景值 1-100000整数
	 * @param  boolean $limit    true永久二维码 false 临时
	 * @param  integer $expire   临时二维码有效时间
	 * @return string|boolean
	 */
	private function qrcode($scene_id = '', $limit = true, $expire = 1800) {
		if($limit){
			if (empty($scene_id) || !is_numeric($scene_id) || $scene_id > 100000 || $scene_id < 1) {
				$this->error = '场景值必须是1-100000之间的整数';
				return false;
			}
		}
		$params['action_name'] = $limit?'QR_LIMIT_SCENE':'QR_SCENE';
		if (!$limit) $params['expire_seconds'] = $expire;
		$params['action_info'] = array('scene' => array('scene_id' => $scene_id));
		$params = json_encode($params);
		$url = self::QRCODE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $this->ticket = $jsonArr['ticket'];
		}else {
			return false;
		}
	}

	/**
	 * 获取二维码图像地址
	 * @param  integer $scene_id 场景值 1-100000整数
	 * @param  boolean $limit    true永久二维码 false 临时
	 * @param  integer $expire   临时二维码有效时间
	 * @return string|boolean    二维码图片地址
	 */
	public function getQRUrlStr($scene_str = '', $expire = 1800) {
		if (!isset($this->ticket) && !$this->qrcode_bystr($scene_str, $expire)) {
			return false;
		}
		return self::QRCODE_SHOW_URL.'?ticket=' . $this->ticket;
	}

	/**
	 * 生成推广二维码
	 * @param  integer $scene_id 场景值 1-100000整数
	 * @param  boolean $limit    true永久二维码 false 临时
	 * @param  integer $expire   临时二维码有效时间
	 * @return string|boolean
	 */
	private function qrcode_bystr($scene_str = '', $expire = 1800) {
		$params['action_name'] = 'QR_LIMIT_STR_SCENE';
		$params['expire_seconds'] = $expire;
		$params['action_info'] = array('scene' => array('scene_str' => $scene_str));
		$params = json_encode($params);
		$url = self::QRCODE_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $this->ticket = $jsonArr['ticket'];
		}else {
			return false;
		}
		
	}

	/**
	 * 不转义中文字符和\/的 json 编码方法
	 * @param  array $array
	 * @return json
	 */
	private function json_encode($array = array()) {
		//return unicode_to_utf8(json_encode($array));
		$array = str_replace("\\/", "/", json_encode($array));
		$search = '#\\\u([0-9a-f]+)#i'; //bruno
		if (strpos(strtoupper(PHP_OS), 'WIN') === false) {
			$replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
		} else {
			$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
		}
		return preg_replace($search, $replace, $array);
	}




	/**
	 * 解析JSON编码，如果有错误，则返回错误并设置错误信息d
	 * @param json $json json数据
	 * @return array
	 */
	private function parseJson($json) {
		$jsonArr = json_decode($json, true);
		if (isset($jsonArr['errcode'])) {
			if ($jsonArr['errcode'] == 0) {
				$this->result = $jsonArr;
				return true;
			} else {
				$this->error = $this->ErrorCode($jsonArr['errcode']);
				return false;
			}
		}else {
			return $jsonArr;
		}
	}

	/**
	 * @param appid	 是	 公众号的唯一标识
	 * @param redirect_uri	 是	 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
	 * @param response_type	 是	 返回类型，请填写code
	 * @param scope	 是	 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （未关注也可以得到信息）
	 * @param state	 否	 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
	 * @param fun	 授权成功以后的地址
	 * #wechat_redirect	 是	 无论直接打开还是做页面302重定向时候，必须带此参数
	 */
	public function oauth2($userid = '', $scope='snsapi_base',$fun){
		$arr = array(
			"appid"				=> $this->appid,
			"redirect_uri"		=> 'http://wx.cnskl.com/'.$fun,
			"response_type"		=> 'code',
			"scope"				=> $scope,
			'state'				=> $userid
		);
		return self::OAUTH_AUTHORIZE_URL . '?' . http_build_query($arr).'#wechat_redirect';
	}

	/**
	 * AES 解密方法
	 * @param  string $encrypted 加密后的字符串
	 * @return xml|boolean
	 */
	public function AESdecode($encrypted) {
		$key            = base64_decode($this->AESKey . "=");
		// 使用BASE64对需要解密的字符串进行解码
		$ciphertext_dec = base64_decode($encrypted);
		$module         = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv             = substr($key, 0, 16);
		mcrypt_generic_init($module, $key, $iv);
		// 解密
		$decrypted      = mdecrypt_generic($module, $ciphertext_dec);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		// 去除补位字符
		$pad = ord(substr($decrypted, -1));
		if ($pad < 1 || $pad > 32) {
			$pad = 0;
		}
		$result = substr($decrypted, 0, (strlen($decrypted) - $pad));
		// 去除16位随机字符串,网络字节序和AppId
		if (strlen($result) < 16) {
			$this->error = 'AESdecode Result Length Error';
			return false;
		}
		$content     = substr($result, 16);
		$len_list    = unpack("N", substr($content, 0, 4));
		$xml_len     = $len_list[1];
		$xml_content = substr($content, 4, $xml_len);
		$from_appid  = substr($content, $xml_len + 4);
		if ($from_appid != $this->appid) {
			$this->errir = 'AESdecode AppId Error';
			return false;
		} else {
			return self::_extractXml($xml_content);
		}
	}

	/**
	 * AES 加密方法
	 * @param  string $text 需要加密的字符串
	 * @return boolean
	 */
	public function AESencode($text) {
		$key    = base64_decode($this->AESKey . "=");
		$random = self::_getRandomStr();
		$text   = $random . pack("N", strlen($text)) . $text . $this->appid;
		$size   = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv     = substr($key, 0, 16);
		// 使用自定义的填充方式对明文进行补位填充
		$text_length = strlen($text);
		//计算需要填充的位数
		$amount_to_pad = 32 - ($text_length % 32);
		if ($amount_to_pad == 0) {
			$amount_to_pad = 32;
		}
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = "";
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}
		$text = $text . $tmp;
		mcrypt_generic_init($module, $key, $iv);
		// 加密
		$encrypted = mcrypt_generic($module, $text);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		// 使用BASE64对加密后的字符串进行编码
		return base64_encode($encrypted);
	}

	/**
	 * 生成一个20位的订单号,最好是使用1位的前缀
	 * @param  string $prefix 订单号前缀，区分业务类型
	 * @return string
	 */
	public static function createOrderId($prefix = '') {
		$code = date('ymdHis').sprintf("%08d", mt_rand(1, 99999999));
		if (!empty($prefix)) {
			$code = $prefix.substr($code, strlen($prefix));
		}
		return $code;
	}

	/**
	 * 返回随机填充的字符串
	 */
	private function _getRandomStr($lenght = 16)	{
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		return substr(str_shuffle($str_pol), 0, $lenght);
	}

	/**
	 * 发送HTTP请求方法，目前只支持CURL发送请求
	 * @param  string  $url    请求URL
	 * @param  array   $params 请求参数
	 * @param  string  $method 请求方法GET/POST
	 * @param  boolean $ssl    是否进行SSL双向认证
	 * @return array   $data   响应数据
	 */
	private function http($url, $params = array(), $method = 'GET', $ssl = false){
		$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false
		);
		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$getQuerys = !empty($params) ? '?'. http_build_query($params) : '';
				$opts[CURLOPT_URL] = $url . $getQuerys;
				break;
			case 'POST':
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
		}
		if ($ssl) {
			$pemPath = dirname(__FILE__).'/Wechat/';
			$pemCret = $pemPath.$this->pem.'_cert.pem';
			$pemKey  = $pemPath.$this->pem.'_key.pem';
			if (!file_exists($pemCret)) {
				$this->error = '证书不存在';
				return false;
			}
			if (!file_exists($pemKey)) {
				$this->error = '密钥不存在';
				return false;
			}
			$opts[CURLOPT_SSLCERTTYPE] = 'PEM';
			$opts[CURLOPT_SSLCERT]     = $pemCret;
			$opts[CURLOPT_SSLKEYTYPE]  = 'PEM';
			$opts[CURLOPT_SSLKEY]      = $pemKey;
		}
		/* 初始化并执行curl请求 */
		$ch     = curl_init();
		curl_setopt_array($ch, $opts);
		$data   = curl_exec($ch);
		$err    = curl_errno($ch);
		$errmsg = curl_error($ch);
		curl_close($ch);
		if ($err > 0) {
			$this->error = $errmsg;
			return false;
		}else {
			return $data;
		}
	}

	/**
	 * 新增永久图文素材
	 */
	public function material_news($articles) {
		self::MATERIAL_NEWS_URL . '?access_token=' . $this->access_token;
		$params  = $this->json_encode($articles);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 新增永久素材
	 */
	public function material_add($file, $type) {
		$url    = self::MATERIAL_MATERIAL_URL . '?access_token=' . $this->access_token . '&type=' . $type;
		$params = array(
			'media' => '@' . $file . ";type=" . $type . ";filename=" . basename($file)
		);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}
	/**
	 * 新增临时素材
	 * @param  string $file  服务器上的绝对路径
	 * @param  string $type  图片（image）、语音（voice）、视频（video）、缩略图（thumb）
	 * @return array
	 */
	public function media_upload($file, $type) {
		$url    = self::MEDIA_UPLOAD_URL . '?access_token=' . $this->access_token . '&type=' . $type;
		$params = array(
			'media' => '@' . $file . ";type=" . $type . ";filename=" . basename($file)
		);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 获取临时素材
	 * @param  string $media_id
	 * @return array
	 */
	public function media_get($media_id) {
		$url    = self::MEDIA_GET_URL;
		$params = array(
			'access_token' => $this->access_token,
			'media_id'     => $media_id
		);
		$jsonStr = $this->http($url, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 获取永久素材
	 * @param  string $media_id
	 * @return array
	 */
	public function material_get($media_id) {
		$url    = self::MATERIAL_GET_URL . '?access_token=' . $this->access_token;
		$params = array(
			'media_id' => $media_id
		);
		$params  = json_encode($params);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 删除永久素材
	 * @param  string $media_id
	 * @return boolean
	 */
	public function material_del($media_id) {
		$url    = self::MATERIAL_DEL_URL . '?access_token=' . $this->access_token;
		$params = array(
			'media_id' => $media_id
		);
		$params  = json_encode($params);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 获取素材数量
	 * @return array
	 */
	public function material_count() {
		$params = array(
			'access_token' => $this->access_token
		);
		$jsonStr = $this->http(self::MATERIAL_COUNT_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 获取素材列表
	 * @param  string  $type    素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @param  integer $offset  起始位置偏移量
	 * @param  integer $count   返回数量
	 * @return array
	 */
	public function material_lists($type, $offset = 0, $count = 20) {
		$params = array(
			'type'   => $type,
			'offset' => $offset,
			'count'  => $count,
		);
		$url     = self::MATERIAL_LISTS_URL . '?access_token=' . $this->access_token;
		$params  = json_encode($params);
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr;
		}else {
			return false;
		}
	}

	/**
	 * 统一下单接口生成支付请求
	 * @param  $openid      string  用户OPENID相对于当前公众号
	 * @param  $body        string  商品描述 少于127字节
	 * @param  $orderId     string  系统中唯一订单号
	 * @param  $money       integer 支付金额
	 * @param  $notify_url  string  通知URL
	 * @param  $extend      array|string   扩展参数
	 * @return json|boolean json 直接可赋给JSAPI接口使用，boolean错误
	 */
	public function unifiedOrder($openid, $body, $orderId, $money, $notify_url = '', $extend = array()) {
		if (strlen($body) > 127) $body = substr($body, 0, 127);
		$params = array(
			'openid'           => $openid,
			'appid'            => $this->appid,
			'mch_id'           => $this->mch_id,
			'nonce_str'        => self::_getRandomStr(),
			'body'             => $body,
			'out_trade_no'     => $orderId,
			'total_fee'        => $money * 100, // 转换成分
			'spbill_create_ip' => get_client_ip(),
			'notify_url'       => $notify_url,
			'trade_type'       => 'JSAPI',
		);
		if (is_string($extend)) {
			$params['attach']  = $extend;
		} elseif (is_array($extend) && !empty($extend)) {
			$params = array_merge($params, $extend);
		}
		// 生成签名
		$params['sign'] = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::UNIFIED_ORDER_URL, $data, 'POST');
		$data = self::_extractXml($data);
		if ($data) {
			if ($data['return_code'] == 'SUCCESS') {
				if ($data['result_code'] == 'SUCCESS') {
					return $this->createPayParams($data['prepay_id']);
				} else {
					$this->error = $data['err_code'];
					return false;
				}
			} else {
				$this->error = $data['return_msg'];
				return false;
			}
		} else {
			$this->error = '创建订单失败';
			return false;
		}
	}

	/**
	 * 生成支付参数
	 * @param  string $prepay_id 预支付单号
	 * @return json
	 */
	private function createPayParams($prepay_id) {
		if (empty($prepay_id)) {
			$this->error = 'prepay_id参数错误';
			return false;
		}
		$params['appId']     = $this->appid;
		$params['timeStamp'] = (string)NOW_TIME;
		$params['nonceStr']  = self::_getRandomStr();
		$params['package']   = 'prepay_id='.$prepay_id;
		$params['signType']  = 'MD5';
		$params['paySign']   = self::_getOrderMd5($params);
		return json_encode($params);
	}

	/**
	 * 查询订单
	 * @param  string $orderId 外部订单号或支付单号
	 * @param  integer $type   1 支付单号 0 外部单号
	 * @return array
	 */
	public function getOrderInfo($orderId, $type = 0) {
		$params['appid']          = $this->appid;
		$params['mch_id']         = $this->mch_id;
		if ($type == 1) {
			$params['transaction_id'] = $orderId;
		} else {
			$params['out_trade_no']   = $orderId;
		}
		$params['nonce_str']      = self::_getRandomStr();
		$params['sign']           = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::ORDER_QUERY_URL, $data, 'POST');
		return self::parsePayRequest($data);
	}

	/**
	 * 关闭订单
	 * @param  string $orderId 外部订单号
	 * @return array
	 */
	public function closeOrder($orderId) {
		$params['appid']          = $this->appid;
		$params['mch_id']         = $this->mch_id;
		$params['out_trade_no']   = $orderId;
		$params['nonce_str']      = self::_getRandomStr();
		$params['sign']           = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::CLOSE_ORDER_URL, $data, 'POST');
		return self::parsePayRequest($data);
	}

	/**
	 * 申请退款 需要证书操作
	 * @param  string $orderId     外部订单号
	 * @param  string $refundId    退款单号
	 * @param  string $total_fee   订单金额
	 * @param  string $refund_fee  退款金额，为空则全额退款
	 * @return array
	 */
	public function refundOrder($orderId, $refundId, $total_fee, $refund_fee = '') {
		$params['appid']          = $this->appid;
		$params['mch_id']         = $this->mch_id;
		$params['nonce_str']      = self::_getRandomStr();
		$params['out_trade_no']   = $orderId;
		$params['out_refund_no']  = $refundId;
		$params['total_fee']      = $total_fee * 100;
		$refund_fee = $refund_fee ?: $total_fee;
		$params['refund_fee']     = $refund_fee * 100;
		$params['op_user_id']     = $this->mch_id;
		$params['sign']           = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::PAY_REFUND_ORDER, $data, 'POST', true);
		return self::parsePayRequest($data);
	}

	/**
	 * 获取退款状态
	 * @param  string $orderId     外部订单号
	 * @return array
	 */
	public function getRefundStatus($orderId) {
		$params['appid']          = $this->appid;
		$params['mch_id']         = $this->mch_id;
		$params['nonce_str']      = self::_getRandomStr();
		$params['out_trade_no']   = $orderId;
		$params['sign']           = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::REFUND_QUERY_URL, $data, 'POST');
		return self::parsePayRequest($data);
	}

	/**
	 * 下载对账单
	 * @param  integer $bill_date
	 * @param  string  $bill_type ALL所有 SUCCESS，成功支付 REFUND，当日退款 REVOKED，已撤销
	 */
	public function downloadBill($bill_date, $bill_type = 'ALL') {
		$params['appid']          = $this->appid;
		$params['mch_id']         = $this->mch_id;
		$params['bill_date']      = $bill_date;
		$params['bill_type']      = $bill_type;
		$params['nonce_str']      = self::_getRandomStr();
		$params['sign']           = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::DOWNLOAD_BILL_URL, $data, 'POST');
		$result = self::_extractXml($data);
		if (isset($result['return_code'])) {
			$this->error = $result['return_msg'];
			return false;
		} else {
			return $data;
		}
	}

	/**
	 * 创建一个商户订单号
	 * @return integer  28位订单号
	 */
	private function createMchBillNo() {
		$micro = microtime(true) * 100;
		$micro = ceil($micro);
		$rand  = substr($micro, -8) . sprintf("%02d", mt_rand(0, 99));
		return   $this->mch_id . date('Ymd') . $rand;
	}

	/**
	 * 向用户付款
	 * @param  string $openid 收款用户OPENID
	 * @param  float  $amount 付款金额
	 * @param  string $check_name 真实姓名校验
	 *         NO_CHECK：不校验真实姓名
	 *         FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）
	 *         OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功） 
	 * @param  string $desc 描述
	 * @return boolean|array
	 */
	public function transfers($openid, $amount, $desc, $check_name = 'NO_CHECK') {
		$params['openid']           = $openid;
		if ($check_name == 'NO_CHECK') {
			$params['check_name']   = $check_name;
		} else {
			$params['check_name']   = 'OPTION_CHECK';
			$params['re_user_name'] = $check_name;
		}
		$params['amount']           = $amount * 100;
		$params['desc']             = $desc;
		$params['spbill_create_ip'] = get_client_ip();
		$params['partner_trade_no'] = self::createMchBillNo();
		$params['mch_appid']        = $this->appid;
		$params['mchid']            = $this->mch_id;
		$params['nonce_str']        = self::_getRandomStr();
		$params['sign']             = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::PAY_TRANSFERS_URL, $data, 'POST', true);
		return self::parsePayRequest($data);
	}

	/**
	 * 获取付款信息
	 * @param  string $orderId 商户订单号
	 * @return boolean|array
	 */
	public function getTransfersInfo($orderId) {
		$params['partner_trade_no'] = $orderId;
		$params['appid']            = $this->appid;
		$params['mch_id']           = $this->mch_id;
		$params['nonce_str']        = self::_getRandomStr();
		$params['sign']             = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::GET_PAY_TRANSFERS, $data, 'POST');
		return self::parsePayRequest($data);
	}

	/**
	 * 发送分享红包
	 * @param  string  $openid 用户OPENID
	 * @param  string  $money  发送金额RMB元
	 * @param  integer $num    裂变红包数量
	 * @param  array   $data   红包数据
	 * @return boolean|array
	 */
	public function sendGroupRedPack($openid, $money, $num = 1, $data) {
		$params['mch_billno']   = self::createMchBillNo();
		$params['send_name']    = $data['send_name'];
		$params['re_openid']    = $openid;
		$params['total_amount'] = $money * 100;
		$params['total_num']    = $num;
		$params['amt_type']     = 'ALL_RAND';
		$params['wishing']      = $data['wishing'];
		$params['act_name']     = $data['act_name'];
		$params['remark']       = $data['remark'];
		$params['mch_id']       = $this->mch_id;
		$params['wxappid']      = $this->appid;
		$params['nonce_str']    = self::_getRandomStr();
		$params['sign']         = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::SEND_RED_PACK, $data, 'POST', true);
		return self::parsePayRequest($data, false);
	}

	/**
	 * 发送红包接口
	 * @param  string $openid 用户OPENID
	 * @param  string $money  发送金额RMB元
	 * @param  array  $data   红包数据 send_name，wishing，act_name，remark
	 * @return boolean|array
	 */
	public function sendRedPack($openid, $money, $data) {
		$params['mch_billno']   = self::createMchBillNo();
		$params['nick_name']    = $data['send_name'];
		$params['send_name']    = $data['send_name'];
		$params['re_openid']    = $openid;
		$params['total_amount'] = $money * 100;
		$params['min_value']    = $money * 100;
		$params['max_value']    = $money * 100;
		$params['total_num']    = 1;
		$params['wishing']      = $data['wishing'];
		$params['act_name']     = $data['act_name'];
		$params['remark']       = $data['remark'];
		$params['client_ip']    = get_client_ip();
		$params['mch_id']       = $this->mch_id;
		$params['wxappid']      = $this->appid;
		$params['nonce_str']    = self::_getRandomStr();
		$params['sign']         = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::SEND_RED_PACK, $data, 'POST', true);
		return self::parsePayRequest($data, false);
	}

	/**
	 * 获取红包信息
	 * @param  string $billNo 商户发放红包的商户订单号
	 * @return array
	 */
	public function getRedPack($billNo) {
		$params['mch_billno'] = $billNo;
		$params['mch_id']     = $this->mch_id;
		$params['appid']      = $this->appid;
		$params['bill_type']  = 'MCHT';
		$params['nonce_str']  = self::_getRandomStr();
		$params['sign']       = self::_getOrderMd5($params);
		$data = self::_array2Xml($params);
		$data = $this->http(self::GET_RED_PACK_INFO, $data, 'POST', true);
		return self::parsePayRequest($data, false);
	}

	/**
	 * 解析支付接口的返回结果
	 */
	public function parsePayRequest($data) {
		$data = self::_extractXml($data);
		if (empty($data)) {
			$this->error = '支付返回内容解析失败';
			return false;
		}
		// 有返回结果 并且是SUCCESS的时候
		if ($data['return_code'] == 'SUCCESS') {
			if (!self::_checkSign($data)) {
				return false;
			} elseif ($data['result_code'] == 'SUCCESS') {
				return $data;
			} else {
				$this->error = $data['err_code'];
				return false;
			}
		} else {
			$this->error = $data['return_msg'];
			return false;
		}
	}

	/**
	 * 接口通知接收
	 */
	public function getNotify() {
		$data = $GLOBALS["HTTP_RAW_POST_DATA"];
		return self::parsePayRequest($data);
	}

	/**
	 * 对支付回调接口返回成功通知
	 */
	public function returnNotify($return_msg = true) {
		if ($return_msg === true) {
			$data = array(
				'return_code' => 'SUCCESS',
			);
		} else {
			$data = array(
				'return_code' => 'FAIL',
				'return_msg'  => $return_msg
			);
		}
		exit(self::_array2Xml($data));
	}

	/**
	 * 接收数据签名校验
	 */
	private function _checkSign($data) {
		$sign = $data['sign'];
		unset($data['sign']);
		if (self::_getOrderMd5($data) != $sign) {
			$this->error = '签名校验失败';
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 本地MD5签名
	 */
	private function _getOrderMd5($params) {
		ksort($params);
		$params['key'] = $this->payKey;
		return strtoupper(md5(urldecode(http_build_query($params))));
	}

	/**
	 * 获取客服列表
	 * @return boolean|array
	 */
	public function get_kf() {
		$url    = self::GET_KF_LIST . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['kf_list'];
		}else {
			return false;
		}
	}

	/**
	 * 获取在线客服列表
	 * @return boolean|array
	 */
	public function get_online_kf() {
		$url    = self::GET_ONLINE_KF_LIST . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['kf_online_list'];
		}else {
			return false;
		}
	}

	/**
	 * 添加客服
	 * @param  string $account   客服账号
	 * @param  string $nickname  昵称
	 * @param  string $password  密码（明文）
	 * @return boolean
	 */
	public function add_kf($account, $nickname, $password) {
		$data = array(
			'kf_account' => $account . '@' . $this->account,
			'nickname'   => $nickname,
			'password'   => MD5($password)
		);
		$params = $this->json_encode($data);
		$url    = self::ADD_KF_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 修改客服
	 * @param  string $account  客服账号
	 * @param  string $nickname  昵称
	 * @param  string $password  密码（明文）
	 * @return boolean
	 */
	public function update_kf($account, $nickname, $password) {
		$data = array(
			'kf_account' => $account . '@' . $this->account,
			'nickname'   => $nickname,
			'password'   => MD5($password)
		);
		$params = $this->json_encode($data);
		$url    = self::UPDATE_KF_URL . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 上传客服头像，待完善
	 * @param  string $account  客服账号
	 * @param  image  $image    图片文件
	 * @return boolean
	 */
	public function upload_kf_img($account, $image) {
		$url    = self::UPLOAD_KF_HEADIMG . '?access_token=' . $this->access_token . '&kf_account=' . $account . '@' . $this->account;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 删除客服
	 * @param  string $account  客服账号
	 */
	public function delete_kf($account) {
		$params = array(
			'access_token'   => $this->access_token,
			'kf_account'     => $account . '@' . $this->account,
		);
		$jsonStr = $this->http(self::AUTH_URL, $params);
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * 将用户转接到多客服系统
	 * @param  string $account 客服账号
	 */
	public function transfer_service($account = '') {
		$data = array(
			'ToUserName'   => $this->data['fromusername'],
			'FromUserName' => $this->data['tousername'],
			'CreateTime'   => time(),
			'MsgType'      => 'transfer_customer_service',
		);
		if (!empty($account)) {
			$data['TransInfo']['KfAccount'] = $account . '@' . $this->account;
		}
		$response = self::_array2Xml($data);
		exit($response);
	}

	/**
	 * 获取客服聊天记录
	 * @param  datetime $date 2015-08-10 只能获取一天内的聊天记录
	 * @param  integer  $page
	 * @return boolean|array
	 */
	public function get_msgrecord($date, $page = 1) {
		$data = array(
			'pageindex' => $page,
			'pagesize'  => 50,
			'starttime' => strtotime($date),
			'endtime'   => strtotime($date) + 86400
		);
		$params = $this->json_encode($data);
		$url    = self::GET_KF_MSGRECORD . '?access_token=' . $this->access_token;
		$jsonStr = $this->http($url, $params, 'POST');
		$jsonArr = $this->parseJson($jsonStr);
		if ($jsonArr) {
			return $jsonArr['recordlist'];
		}else {
			return false;
		}
	}

	/**
	 * 捕获错误信息
	 * @return string 中文错误信息
	 * @author 、小陈叔叔 <cjango@163.com>
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 获取全局返回错误码
	 * @param integer $code 错误码
	 * @return string 错误信息
	 * @author 、小陈叔叔 <cjango@163.com>
	 */
	private function ErrorCode($code) {
		switch ($code) {
			case -1    : return '系统繁忙 ';
			case 40001 : return '获取access_token时AppSecret错误，或者access_token无效 ';
			case 40002 : return '不合法的凭证类型';
			case 40003 : return '不合法的OpenID ';
			case 40004 : return '不合法的媒体文件类型';
			case 40005 : return '不合法的文件类型';
			case 40006 : return '不合法的文件大小';
			case 40007 : return '不合法的媒体文件id ';
			case 40008 : return '不合法的消息类型 ';
			case 40009 : return '不合法的图片文件大小';
			case 40010 : return '不合法的语音文件大小';
			case 40011 : return '不合法的视频文件大小';
			case 40012 : return '不合法的缩略图文件大小';
			case 40013 : return '不合法的APPID';
			case 40014 : return '不合法的access_token ';
			case 40015 : return '不合法的菜单类型 ';
			case 40016 : return '不合法的按钮个数 ';
			case 40017 : return '不合法的按钮个数';
			case 40018 : return '不合法的按钮名字长度';
			case 40019 : return '不合法的按钮KEY长度 ';
			case 40020 : return '不合法的按钮URL长度 ';
			case 40021 : return '不合法的菜单版本号';
			case 40022 : return '不合法的子菜单级数';
			case 40023 : return '不合法的子菜单按钮个数';
			case 40024 : return '不合法的子菜单按钮类型';
			case 40025 : return '不合法的子菜单按钮名字长度';
			case 40026 : return '不合法的子菜单按钮KEY长度 ';
			case 40027 : return '不合法的子菜单按钮URL长度 ';
			case 40028 : return '不合法的自定义菜单使用用户';
			case 40029 : return '不合法的oauth_code';
			case 40030 : return '不合法的refresh_token';
			case 40031 : return '不合法的openid列表 ';
			case 40032 : return '不合法的openid列表长度 ';
			case 40033 : return '不合法的请求字符，不能包含\uxxxx格式的字符 ';
			case 40035 : return '不合法的参数';
			case 40038 : return '不合法的请求格式';
			case 40039 : return '不合法的URL长度 ';
			case 40050 : return '不合法的分组id';
			case 40051 : return '分组名字不合法';
			case 41001 : return '缺少access_token参数';
			case 41002 : return '缺少appid参数';
			case 41003 : return '缺少refresh_token参数';
			case 41004 : return '缺少secret参数';
			case 41005 : return '缺少多媒体文件数据';
			case 41006 : return '缺少media_id参数';
			case 41007 : return '缺少子菜单数据';
			case 41008 : return '缺少oauth code';
			case 41009 : return '缺少openid';
			case 42001 : return 'access_token超时';
			case 42002 : return 'refresh_token超时';
			case 42003 : return 'oauth_code超时';
			case 43001 : return '需要GET请求';
			case 43002 : return '需要POST请求';
			case 43003 : return '需要HTTPS请求';
			case 43004 : return '需要接收者关注';
			case 43005 : return '需要好友关系';
			case 44001 : return '多媒体文件为空';
			case 44002 : return 'POST的数据包为空';
			case 44003 : return '图文消息内容为空';
			case 44004 : return '文本消息内容为空';
			case 45001 : return '多媒体文件大小超过限制';
			case 45002 : return '消息内容超过限制';
			case 45003 : return '标题字段超过限制';
			case 45004 : return '描述字段超过限制';
			case 45005 : return '链接字段超过限制';
			case 45006 : return '图片链接字段超过限制';
			case 45007 : return '语音播放时间超过限制';
			case 45008 : return '图文消息超过限制';
			case 45009 : return '接口调用超过限制';
			case 45010 : return '创建菜单个数超过限制';
			case 45015 : return '回复时间超过限制';
			case 45016 : return '系统分组，不允许修改';
			case 45017 : return '分组名字过长';
			case 45018 : return '分组数量超过上限';
			case 46001 : return '不存在媒体数据';
			case 46002 : return '不存在的菜单版本';
			case 46003 : return '不存在的菜单数据';
			case 46004 : return '不存在的用户';
			case 47001 : return '解析JSON/XML内容错误';
			case 48001 : return 'api功能未授权';
			case 50001 : return '用户未授权该api';
			default    : return '未知错误';
		}
	}
}
