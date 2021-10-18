<?php
declare (strict_types = 1);

use thinkmanage\ibac\IbacFacade;

use thinkmanage\ibac\service\Policy;


if (!function_exists('ibacOpen')) {
	/**
	 * 验证是否开启
	 *
	 * @return bool
	 */
	function ibacOpen(){
		return IbacFacade::getConf('is_open') == true;
	}
}

if (!function_exists('ibacLogin')) {
	/**
	 * 登录
	 *
	 * @param string $account(账户/手机/邮箱)
	 * @param string $password(密码)
	 * @throws \think\Exception
	 * @return unknown
	 */
	function ibacLogin($account, $password){
		return ibacLoginByAccount($account,$password);
	}
}

if (!function_exists('ibacLoginByAccount')) {
	/**
	 * 登录
	 *
	 * @param string $account(账户/手机/邮箱)
	 * @param string $password(密码)
	 * @throws \think\Exception
	 * @return unknown
	 */
	function ibacLoginByAccount($account, $password){
		if(!$account){
			throw new \Exception('用户账户不存在');
		}
		if(!$password){
			throw new \Exception('用户密码不存在');
		}
		$field = IbacFacade::getConf('account_field');
		$info = IbacFacade::getUinfo([
			$field => $account,
			'password' => md5($password)
		]);
		if(!$info){
			throw new \Exception('用户不存在');
		}
		if(!isset($info['status']) || $info['status'] != 1){
			throw new \Exception('用户被禁用');
		}
		IbacFacade::_setStore($info);
		return $info;
	}
}

if (!function_exists('ibacLoginById')) {
	/**
	 * 登录
	 *
	 * @param int $id(用户ID)
	 * @throws \think\Exception
	 * @return array
	 */
	function ibacLoginById($id){
		if(!$id){
			throw new \Exception('用户主键不存在');
		}
		$info = IbacFacade::getUinfo(['id'=>$id]);
		if(!$info){
			throw new \Exception('用户不存在');
		}
		if(!isset($info['status']) || $info['status'] != 1){
			throw new \Exception('用户被禁用');
		}
		IbacFacade::_setStore($info);
		return $info;
	}
}

if (!function_exists('ibacLogout')) {
	/**
	 * 退出登录
	 *
	 * @param string $id(用户ID)
	 * @throws \think\Exception
	 * @return unknown
	 */
	function ibacLogout(){
		return IbacFacade::clearStore();
	}
}

if (!function_exists('ibacIsSuper')) {
	/**
	 * 是否为超管
	 *		 
	 * @param int $uid			
	 * @return boolean
	 */
	function ibacIsSuper($id){
		return IbacFacade::isSuper($id);
	}
}

if (!function_exists('_uinfo')) {
	/**
	 * 获取用户信息
	 *
	 * @param int $id	用户ID
	 * @return int
	 */
	function _uinfo($id=0){
		//要取的用户ID>0 且不是当前登录用户
		if($id>0 && _uid()!=$id){
			//从数据库中获取用户数据
			return IbacFacade::getUinfo(['id'=>$id]);
		}
		//从store中获取用户数据
		return IbacFacade::getStore();
	}
}

if (!function_exists('_uid')) {
	/**
	 * 获取用户ID
	 *
	 * @return int
	 */
	function _uid(){
		return IbacFacade::getStoreData('id',0);
	}
}

if (!function_exists('ibacUinfoResource')) {
	function ibacUinfoResource(){
		return IbacFacade::getStoreData('resource',[]);
	}
}

if (!function_exists('ibacStoreData')) {
	function ibacStoreData($type='',$def=[],$uinfo=[]){
		if($type==''){
			return $def;
		}
		if(isset($uinfo[$type])){
			return $uinfo[$type];
		}
		return IbacFacade::getStoreData($type,[]);
	}
}

if (!function_exists('ibacUinfoRelated')) {
	function ibacUinfoRelated($uinfo=[]){
		if(isset($uinfo['related'])){
			return $uinfo['related'];
		}
		return IbacFacade::getStoreData('related',[]);
	}
}

if (!function_exists('ibacHasUinfoRelated')) {
	function ibacHasUinfoRelated($identity,$tagreId,$uinfo=[]){
		$related = ibacUinfoRelated($uinfo);
		if(in_array($identity.'.'.$tagreId,$related)){
			return true;
		}
		return false;
	}
}


if (!function_exists('ibacResourceException')) {
	
	/**
	 * 获取例外的资源
	 *
	 * @param string|array rule
	 * @return boolean
	 */
	function ibacResourceException($name){
		return IbacFacade::resourceException($name);
	}
}

if (!function_exists('ibacCheck')) {
	function ibacCheck($name,$uinfo=null){
		return IbacFacade::checkResource($name,$uinfo);
	}
}

if (!function_exists('ibacPolicy')) {
	/**
     * 根据传入标识获取策略数据
     * 
     * @access	public
     * @param	string	$name	标识
     * @param	array	$param	参数
     * @return	array
     */
	/*
		$policy = ibacPolicy('admin/User/index',[
			'uinfo' => $this->uinfo
		]);
		$policyField = ibacPolicyField($policy,[
			'id' => 'id',
			'username' => 'username',
			'truename' => 'truename',
			'phone' => 'phone',
			'email' => 'email',
			'status' => 'status',
			'create_time' => 'create_time',
			'update_time' => 'update_time',
		]);
		$policyFilter = ibacPolicyFilter($policy);
		$data = Db::name('SysUser')->where([['id','=',1]])->where($policyFilter)->field($policyField)->select();
		print_r(Db::getLastSql());
		print_r($data);
		exit;
	*/
	function ibacPolicy($name,$param=[]){
		return Policy::getPolicy($name,$param);
	}
}

if (!function_exists('ibacPolicyData')) {
	/**
     * 根据传入策略数据(标识)和对应类型获取策略数据中的对应数据
     * 
     * @access	public
     * @param	string	$name	标识
     * @param	string	$type	需求的数据类型
     * @param	array	$def	默认值
     * @return	mixed
     */
	function ibacPolicyData($policy,$type='',$def){
		return Policy::getPolicyData($policy,$type,$def);
	}
}

if (!function_exists('ibacPolicyField')) {
	/**
     * 根据传入策略数据(标识)与字段获取策略数据中的允许字段
     * 
     * @access	public
     * @param	array	$policy	策略
     * @param	array	$field	字段
     * @return	array
     */
	function ibacPolicyField($policy,$field=[]){
		return Policy::policyField($policy,$field);
	}
}

if (!function_exists('ibacPolicyFilter')) {
	/**
     * 根据传入策略数据(标识)获取策略数据中的过滤规则
     * 
     * @access	public
     * @param	array	$policy	策略
     * @return	string
     */
	function ibacPolicyFilter($policy){
		return Policy::policyFilter($policy);
	}
}

if (!function_exists('ibacPolicyValid')) {
	/**
     * 根据传入策略数据(标识)与数据进行验证
     * 
     * @access	public
     * @param	array	$policy	策略
     * @param	array	$data	数据
     * @return	string
     */
	function ibacPolicyValid($policy,$data=[]){
		return Policy::policyValid($policy,$data);
	}
}

if (!function_exists('ibcaRunCode')) {
    /**
     * 运行代码
	 * 
     * @param	string	$code	要运行的代码
     * @param	array	$param	参数
     * @return mixed
     */
	function ibcaRunCode($code,$param=[]){
		return eval('return '.ibcaParseCode($code,$param).';');
	}
}

if (!function_exists('ibcaParseCode')) {
    /**
     * 解析代码
	 * 
     * @param	string	$code	要运行的代码
     * @param	array	$param	参数
     * @return mixed
     */
	function ibcaParseCode($code,$param=[]){
		return \think\facade\View::display($code,$param);
	}
}

if (!function_exists('ibacResetCache')) {

    /**
     * 重置缓存
	 * 
     * @return void
     */
    function ibacResetCache()
    {
		IbacFacade::resetCache();
    }
    function ibcaResourceResetCache(){
		IbacFacade::resourceResetCache();
    }
    function ibcaPolicyResetCache(){
		IbacFacade::policyResetCache();
    }
    function ibcaIdentityResetCache(){
		IbacFacade::identityResetCache();
    }
}