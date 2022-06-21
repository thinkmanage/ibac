<?php
namespace thinkmanage\ibac;

use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\View;

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
		'store_name'	=> 'token',
		//登录超时时间
		'store_timeout' => 7200,
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
		'token_field'	=> ['id','domain','related','resource'],
		//是否开启
		'open_right_domain' => true,
		//身份列表
		'identity'=>[
		]
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
	
	/*
	 * 获取全部配置
	 *
     * @return array
	 */
	public function getConfigs(){
		return $this->config;
	}
	
	/*
	 * 设置配置
	 *
	 * @param array $config 配置
	 */
	public function setConfigs($config){
		$this->config = array_merge($this->config,$config);
	}
	
	/*
	 * 获取配置_单项
	 *
	 * @param string $name 标识
     * @return mixed
	 */
	public function getConfig($name){
		if(!$name || !isset($this->config[$name])){
			throw new \Exception('存储类型不存在');
		}
		return $this->config[$name];
	}
	
	/*
	 * 设置配置_单项
	 *
	 * @param string $name 标识
	 * @param mixed $value 配置
	 */
	public function setConfig($name,$value){
		$this->config[$name] = $value;
	}
	
	
	/**
	 * 获取Store数据
	 *
	 * @return array
	 */
	public function getStore(){
		$info = null;
		switch ($this->getConfig('type')){
			case 'session':
				$info = session($this->getConfig('store_name'));
				break;
			case 'cookie':
				$info = cookie($this->getConfig('store_name'));
				break;
			case 'param':
				$info = Request::param($this->getConfig('store_name'));
				break;
			case 'header':
				$info = Request::header($this->getConfig('store_name'));
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
		if(!$info){
			throw new \Exception('数据不存在');
		}
		$info = json_decode($this->decodeFunc($info),true);
		if(!$info){
			throw new \Exception('数据异常');
		}
		return $info;
	}
	
	/**
	 * 设置Store
	 *
	 * @param array $info 用户基本信息
	 * @return bool
	 */
	public function setStore($info){
		$tokens = [];
		foreach($info as $k => $v){
			if(in_array($k,$this->getConfig('token_field'))){
				$tokens[$k] = $v;
			}
		}
		$tokens['token_time'] = time() + $this->getConfig('store_timeout');
		$token = $this->encodeFunc(json_encode($tokens));
		switch ($this->getConfig('type')){
			case 'session':
				session($this->getConfig('store_name'),$token);
				break;
			case 'cookie':
				cookie($this->getConfig('store_name'),$token);
				break;
			case 'param':
			case 'header':
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
		return $token;
	}
	
	/**
	 * 清除Store
	 *
	 * @param string $id(用户ID)
	 * @return unknown
	 */
	public function clearStore(){
		switch ($this->getConfig('type')){
			case 'session':
				session($this->getConfig('store_name'),null);
				return true;
				break;
			case 'cookie':
				cookie($this->getConfig('store_name'),null);
				return true;
				break;
			case 'param':
			case 'header':
				//非服务器存储类的无法主动注销
				return true;
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
	}
	
	/**
	 * Store续期
	 *
	 * @param string $id(用户ID)
	 * @return unknown
	 */
	public function renewalStore(){
		$store = $this->getStore();
		$store['token_time'] = time() + $this->getConfig('store_timeout');
		switch ($this->getConfig('type')){
			case 'session':
				session($this->getConfig('store_name'),$store);
				break;
			case 'cookie':
				cookie($this->getConfig('store_name'),$store);
				break;
			case 'param':
			case 'header':
				break;
			default:
				throw new \Exception('存储类型不存在');
				;
		}
		return $store;
	}
	
	/**
	 * 获取Store数据中的某一项数据
	 *
	 * @param string	$name 类型
	 * @return mixed
	 */
	public function getStoreAttr(string $name){
		try {
			$store = $this->getStore();
			return $store[$name];
		}catch(\Exception $e){
			throw new \Exception('对应数据在store中不存在');
		}
	}
	
	
	/**
	 * 设置Store数据中的某一项数据
	 *
	 * @param string	$type 类型
	 * @param mixed		$val 默认错误返回数据
	 */
	public function setStoreAttr(string $name,$value){
		try {
			$store = $this->getStore();
			$store[$name] = $val;
			$this->setStore($storeData);
		}catch(\Exception $e){
			throw new \Exception('对应数据在store中不存在');
		}
	}
	
	/**
	 * 根据条件获取用户信息
	 *
	 * @return int
	 */
	public function findUser(array $map=[]){
		$model = $this->getConfig('user_model');
		//从数据库中通过登录账户字段查询用户
		$info = $model::info($map);
		if(!$info){
			throw new \Exception('用户不存在');
		}
		//获取身份
		$info['related']	= $this->findIdentity($info['id']);
		//获取对应数据权限
		$right				= $this->findRight($info['related']);
		$info	= array_merge($info,$right);
		return $info;
	}
	
	/**
	 * 根据用户ID获取身份数组
	 *
	 * @param int $id 用户ID
	 * @return array
	 */
	public function findIdentity(int $id){
		//获取所有关联的身份
		$relatedList = \thinkmanage\ibac\model\Related::where([['user_id','=',$id],['status','=',1]])->field(['subject_id','target_id'])->select();
		//不存在身份
		if(!$relatedList){
			return [];
		}
		$identitys = [];
		foreach($relatedList as $v){
			$identitys[] = $v['subject_id'].'.'.$v['target_id'];
		}
		return $identitys;
	}
	
	/**
	 * 根据用户身份数组获取资源/数据权限
	 *
	 * @param array $identitys 身份数组
	 * @return array
	 */
	public function findRight(array $identitys){
		$rights = [];
		$resources = [];
		$permissions = [];
		foreach($identitys as $v){
			$temp = explode('.',$v);
			$right = \thinkmanage\ibac\model\Right::cache($temp[0],$temp[1]);
			$resources = array_merge($resources,$right['resource_ids']);
			//遍历取回的permission 依次合并去重
			foreach($right['permission_ids'] as $pkey => $pval){
				$pval = explode(',',$pval);
				if(!isset($permissions[$pkey])){
					$permissions[$pkey] = [];
				}
				$permissions[$pkey] = array_unique(array_merge($permissions[$pkey],$pval));
			}
		}
		return [
			'resource'=>$resources,
			'permission'=>$permissions
		];
	}
	
	
	/*
	
	验证store 
	验证store超时
	
	获取超管
	验证资源是否需要
	*/
	
	
	
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
		//从存储中获取uinfo
		if(!$uinfo){
			$uinfo = $this->getStore();
		}
		//判断是否为超管
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
	public function getPermission(string $name,array $uinfo=[]){
		//如果不存在uinfo 从存储中获取
		if(count($uinfo)<1){
			$uinfo = $this->getStore();
		}
		//未获取到uinfo 则返回失败查询
		if(!$uinfo){
			return '1 <> 1';
		}
		//判断是否为超管 超管无视数据权限
		if($this->isSuper($uinfo['id'])){
			return '';
		}
		//获取权限id对照表
		$nti = \thinkmanage\ibac\model\Resource::nti();
		//当前资源是否存在
		if(!isset($nti[$name])){
			return '1 <> 1';
		}
		$iK = $nti[$name];
		//判断用户对当前资源的数据权限
		if(!isset($uinfo['permission'][$iK])){
			//TODO 严格过滤则不返回任何数据
			//非严格过滤返回
			return '1 <> 1';
		}
		//获取数据权限的规则
		$conditions = \thinkmanage\ibac\model\Permission::getConditions($uinfo['permission'][$iK]);
		$permissions = [];
		foreach($conditions as $v){
			if($v != ''){
				$permissions[] = View::display($v,[
					'uinfo' => $uinfo
				]);
			}
		}
		return implode(' AND ',$permissions);
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