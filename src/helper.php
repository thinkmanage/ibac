<?php
declare (strict_types = 1);

use thinkmanage\ibac\IbacFacade;


if (!function_exists('ibacOpen')) {
	/**
	 * 验证是否开启
	 *
	 * @return bool
	 */
	function ibacOpen(){
		return IbacFacade::getConfig('is_open') == true;
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
		$field = IbacFacade::getConfig('account_field');
		$info = IbacFacade::findUser([
			$field => $account,
			'password' => md5($password)
		]);
		if(!$info){
			throw new \Exception('用户不存在');
		}
		if(!isset($info['status']) || $info['status'] != 1){
			throw new \Exception('用户被禁用');
		}
		return IbacFacade::setStore($info);
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
		return IbacFacade::setStore($info);
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
	function _uinfo(){
		return IbacFacade::getStore();
	}
}

if (!function_exists('_getUinfoByDb')) {
	/**
	 * 获取用户信息
	 *
	 * @param int $id	用户ID
	 * @return int
	 */
	function _getUinfoByDb($id=0){
		return IbacFacade::getUinfo(['id'=>$id]);
	}
}

if (!function_exists('_uid')) {
	/**
	 * 获取用户ID
	 *
	 * @return int
	 */
	function _uid(){
		try {
			return IbacFacade::getStoreAttr('id');
		}catch(\Exception $e){
			return 0;
		}
	}
}

if (!function_exists('ibacUinfoResource')) {
	function ibacUinfoResource(){
		return IbacFacade::getStoreAttr('resource',[]);
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
		return IbacFacade::getStoreAttr($type,[]);
	}
}

if (!function_exists('ibacUinfoRelated')) {
	function ibacUinfoRelated($uinfo=[]){
		if(isset($uinfo['related'])){
			return $uinfo['related'];
		}
		return IbacFacade::getStoreAttr('related',[]);
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

if (!function_exists('ibacPermission')) {
	function ibacPermission($name,$uinfo=[]){
		return IbacFacade::getPermission($name,$uinfo);
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
}

if (!function_exists('ibacGetConf')) {

    /**
     * 获取配置
	 * 
     * @return void
     */
    function ibacGetConf($name)
    {
		return IbacFacade::getConfig($name);
    }
}
if (!function_exists('ibacSetConf')) {

    /**
     * 获取配置
	 * 
     * @return void
     */
    function ibacSetConf($name,$value)
    {
		return IbacFacade::setConf($name,$value);
    }
}