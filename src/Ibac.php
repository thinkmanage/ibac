<?php
namespace thinkmanage\ibac;

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
		//token中存储的字段
		'token_field'	=> ['id','username','related','resource'],
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
		$info['token'] = $this->userToken($info);
		//根据用户基本信息格式化其他数据
		return $info;
	}
	
	/**
	 * 根据条件获取用户信息
	 *
	 * @return int
	 */
	public function userToken($info=[]){
		$tokenField = $this->config['token_field'];
		$tokenList = [];
		foreach($info as $k => $v){
			if(in_array($k,$tokenField)){
				$tokenList[$k] = $v;
			}
		}
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
			case 'header':
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
			case 'header':
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
			case 'header':
				$info = Request::header('token');
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
			$info['related'][] = $v['subject_id'].'.'.$v['target_id'];
		}
		//获取系统定义的所有身份
		$identityList = \thinkmanage\ibac\model\Identity::cache();
		//获取当前身份的所有资源
		$identityList = array_filter($identityList, function ($v, $k) use ($info) {
			if (in_array($k,$info['related'])) {
				return true;
			}
		}, ARRAY_FILTER_USE_BOTH);
		//合并所有身份的资源
		foreach($identityList as $v){
			$info['resource'] = array_diff(array_merge($info['resource'],$v['enable']),$v['disable']);
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
			$uinfo = $this->getUinfoById($id);
		}
		if(!$uinfo || !is_array($uinfo)){
			return false;
		}
		return $this->checkResource($name,$uinfo);
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
		//重置资源缓存
		$this->resourceResetCache();
		//重置身份缓存
		$this->identityResetCache();
		//重置策略缓存
		$this->policyResetCache();
    }
	/**
	 * 重置身份缓存
	 *
	 * @return unknown
	 */
    public function identityResetCache(){
		\thinkmanage\ibac\model\Identity::resetCache();
    }
	/**
	 * 重置策略缓存
	 */
    public function policyResetCache(){
		\thinkmanage\ibac\model\Policy::resetCache();
    }
	/**
	 * 重置资源缓存
	 */
    public function resourceResetCache(){
		\thinkmanage\ibac\model\Resource::resetCache();
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