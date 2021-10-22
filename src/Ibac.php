<?php
namespace thinkmanage\ibac;

use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;

use thinkmanage\ibac\model\Identity;
use thinkmanage\ibac\model\Resource;
use thinkmanage\ibac\model\Related;

class Ibac {
	
	/*
	 * 默认配置
	 *
	 */
	public $config = [
		//是否开启
		'is_open'		=> true,
		//用户模型
		'user_model'	=> '\app\core\model\User',
		//登录类型
		'type'	=> 'session',
		//登录标识
		'store_name'	=> 'uinfo',
		//登录账户字段
		'account_field'	=> 'username|phone|email',
		//例外资源
		'exception_list'	=> [],
		//超管用户ID数组
		'super_admin'	=>[1],
		//加解函数
		'encode_func' => '',
		//解密函数
		'decode_func' => '',
		//store中存储的字段
		'store_field'	=> 'all',
		//token中存储的字段
		'token_field'	=> ['id','username','related','resource'],
		//是否开启
		'open_right_domain' => true,
    ];
	
	/*
	 * 初始化
	 *
     * @return void
	 */
    public function __construct(){
        $this->config = array_merge(
			$this->config,
			Config::get('ibac')
		);
    }
	
	public function getConf($name=''){
		if($name != ''){
			return $this->config[$name];
		}else{
			return $this->config;
		}
	}
	
	public function setConf($data,$name=''){
		if($name != ''){
			$this->config[$name] = $data;
		}else{
			$this->config = array_merge($this->config,$data);
		}
	}
	
	/**
	 * 根据条件获取用户信息
	 *
	 * @return int
	 */
	public function getUinfo($map=[]){
		$model = $this->config['user_model'];
		//从数据库中通过登录账户字段查询用户
		$info = $model::info($map);
		if(!$info){
			return false;
		}
		$info = $this->userIdentity($info);
		$store = $this->userStore($info);
		$store['token'] = $this->userToken($info);
		//根据用户基本信息格式化其他数据
		return $store;
	}
	
	
	/**
	 * 根据条件设置token字段
	 *
	 * @return array
	 */
	public function userStore($info=[]){
		if($this->config['store_field'] == 'all'){
			return $info;
		}
		$storeField = $this->config['store_field'];
		$storeList = [];
		foreach($info as $k => $v){
			if(in_array($k,$storeField)){
				$storeList[$k] = $v;
			}
		}
		return $storeList;
	}
	
	/**
	 * 根据条件设置token字段
	 *
	 * @return array
	 */
	public function userToken($info=[]){
		$tokenField = $this->config['token_field'];
		$tokenList = [];
		foreach($info as $k => $v){
			if(in_array($k,$tokenField)){
				$tokenList[$k] = $v;
			}
		}
		$tokenList['token_time'] = time();
		return $this->encodeFunc(json_encode($tokenList));
	}
	
	/**
	 * 设置Store
	 *
	 * @param array $info 用户基本信息
	 * @return bool
	 */
	public function _setStore($info){
		switch ($this->config['type']){
			case 'session':
				session($this->config['store_name'],$info);
				return $info;
				break;
			case 'cookie':
				cookie($this->config['store_name'],$this->encodeFunc(json_encode($info)));
				return $info;
				break;
			case 'param':
				return $info;
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
	}
	
	/**
	 * 清除Store
	 *
	 * @param string $id(用户ID)
	 * @return unknown
	 */
	public function clearStore(){
		switch ($this->config['type']){
			case 'session':
				session($this->config['store_name'],null);
				return true;
				break;
			case 'cookie':
				cookie($this->config['store_name'],null);
				return true;
				break;
			case 'param':
				//非存储类的无法主动注销
				return true;
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
	}
	
	/**
	 * 获取Store数据
	 *
	 * @return array
	 */
	public function getStore(){
		switch ($this->config['type']){
			case 'session':
				$info = session($this->config['store_name']);
				break;
			case 'cookie':
				$info = cookie($this->config['store_name']);
				if(!$info){
					return false;
				}
				$info = json_decode($this->decodeFunc($info),true);
				break;
			case 'param':
				$info = Request::param('token');
				if(!$info){
					return false;
				}
				$info = json_decode($this->decodeFunc($info),true);
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
		if(!$info){
			return false;
		}
		return $info;
	}
	
	
	/**
	 * 获取Store数据中的某一项数据
	 *
	 * @param string	$type 类型
	 * @param mixed		$def 默认错误返回数据
	 * @return mixed
	 */
	public function getStoreData(string $type,$def=false){
		$storeData = $this->getStore();
		if(!$storeData || !isset($storeData[$type])){
			return $def;
		}
		return $storeData[$type];
	}
	
	/**
	 * 是否为超级管理员
	 *
	 * @param string $id(用户ID)
	 * @return bool
	 */
	public function isSuper($id=0){
		if($id < 1){
			return false;
		}
		return in_array($id,$this->config['super_admin']);
	}
	
	/**
	 * 根据用户基本信息格式化其他数据
	 *
	 * @param array $info 用户基本信息
	 * @return array
	 */
	public function userIdentity(array $info){
		//设置用户身份
		$info['related'] = [];
		//设置资源
		$info['resource'] = [];
		//获取所有关联的身份
		$relatedList = \thinkmanage\ibac\model\Related::where([['user_id','=',$info['id']],['status','=',1]])->field(['subject_id','target_id'])->select();
		//不存在身份
		if(!$relatedList){
			return $info;
		}
		$relatedList = $relatedList->toArray();
		foreach($relatedList as $v){
			$related = $v['subject_id'].'.'.$v['target_id'];
			$info['related'][] = $related;
			$right = \thinkmanage\ibac\model\Right::cache($v['subject_id'],$v['target_id']);
			$info['resource'] = array_merge($info['resource'],$right['resource_ids']);
		}
		$info['resource'] = array_unique($info['resource']);
		return $info;
	}
	
	/**
	 * 根据用户信息和资源标识验证用户是否拥有某些资源资源
	 *
	 * @param array $name 资源标识
	 * @param array $uinfo 用户基本信息
	 * @return array
	 */
	public function checkResource(string $name,array $uinfo=null){
		//是否为非验证资源
		if($this->resourceException($name)){
			return true;
		}
		if(!$uinfo){
			$uinfo = $this->getStore();
		}
		if($this->isSuper($uinfo['id'])){
			return true;
		}
		//获取权限id对照表
		$nti = \thinkmanage\ibac\model\Resource::nti();
		if(!isset($nti[$name])){
			return false;
		}
		$id = $nti[$name];
		if(in_array($id,$uinfo['resource'])){
			return true;
		}
		return false;
	}
	
	/**
	 * 根据用户信息和资源标识验证用户是否拥有某些资源资源
	 *
	 * @param array $name 资源标识
	 * @param array $uid 用户基本信息
	 * @return array
	 */
	public function checkResourceByUid(string $name,$uid=0){
		if($uid == 0){
			$uinfo = $this->getStore();
		}else{
			$uinfo = $this->getUinfo(['id'=>$uid]);
		}
		if(!$uinfo || !is_array($uinfo)){
			return false;
		}
		return $this->checkResource($name,$uinfo);
	}
	
	
	/**
	 * 通过资源标识和用户获取数据域
	 *
	 * @param array $name 资源标识
	 * @param array $uinfo 用户基本信息
	 * @return array
	 */
	public function getRightDomain(string $name,string $pre='',array $uinfo=null){
		if(!$this->config['open_right_domain']){
			return '1 = 1';
		}
		if(!$uinfo){
			$uinfo = $this->getStore();
		}
		if($this->isSuper($uinfo['id'])){
			return '1 = 1';
		}
		if(!$uinfo['related']){
			return '1 <> 1';
		}
		//获取权限id对照表
		$nti = \thinkmanage\ibac\model\Resource::nti();
		if(!isset($nti[$name])){
			return '1 <> 1';
		}
		$iK = $nti[$name];
		$return = [];
		if($pre != ''){
			$pre = $pre.'.';
		}
		foreach($uinfo['related'] as $v){
			$related = explode('.',$v);
			$right = \thinkmanage\ibac\model\Right::cache($related[0],$related[1]);
			if(!isset($right['domain'][$iK]) || count($right['domain'][$iK])<1){
				continue;
			}
			if(in_array('*',$right['domain'][$iK])){
				return '1 = 1';
			}
			if(in_array('#',$right['domain'][$iK])){
				return 'create_id = '.$uinfo['id'];
			}
			foreach($right['domain'][$iK] as $v2){
				if($v2){
					$return[] = "LEFT (".$pre."`domain`,".strlen($v2).") = '".$v2."'";
				}
			}
		}
		if(count($return)<1){
			return '1 <> 1';
		}
		return implode(' OR ',$return);
	}
	
	/**
	 * 判断当前资源是否为例外资源
	 *
	 * @param string	$name 资源标识
	 * @return bool
	 */
	public function resourceException($name = ''){
		if($name==''){
			return false;
		}
		if (strpos($name, '/') !== false){
			$name = explode('/', $name);
		}
		$exceptionList = $this->config['exception_list'];
		// 判断当前的mca是否在非验证资源中
		if (in_array($name[0].'/'.$name[1].'/*', $exceptionList)){
			return true;
		}
		if (in_array($name[0].'/*/'.$name[2], $exceptionList)){
			return true;
		}
		if (in_array('*/*/'.$name[2], $exceptionList)){
			return true;
		}
		if (in_array('*/'.$name[1].'/'.$name[2], $exceptionList)){
			return true;
		}
		if (in_array($name[0].'/'.$name[1].'/'.$name[2], $exceptionList)){
			return true;
		}
		return false;
	}
	
	/**
	 * 重置缓存
	 */
    public function resetCache(){
		\thinkmanage\ibac\model\Organ::resetCache();
		\thinkmanage\ibac\model\Resource::resetCache();
		\thinkmanage\ibac\model\Identity::resetCach();
		\thinkmanage\ibac\model\Right::resetCacheAll();
    }
	
	/**
	 * 加密方法
	 *
	 * @param string	$code 要加密的数据
	 * @return string
	 */
	public function encodeFunc(string $code){
		if($this->config['encode_func'] != ''){
			$func = $this->config['encode_func'];
			return $func($code);
		}
		return $code;
    }
	
	
	/**
	 * 解密方法
	 *
	 * @param string	$code 要解密的数据
	 * @return string
	 */
	public function decodeFunc(string $code){
		if($this->config['decode_func'] != ''){
			$func = $this->config['decode_func'];
			return $func($code);
		}
		return $code;
    }
	
}