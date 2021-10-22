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

if (!function_exists('ibacRightDomain')) {
	function ibacRightDomain($name,$pre='',$uinfo=null){
		return IbacFacade::getRightDomain($name,$pre,$uinfo);
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