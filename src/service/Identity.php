<?php 
namespace thinkmanage\ibac\service;

use thinkmanage\ibac\model\Identity as IdentityModel;
use thinkmanage\ibac\validate\Identity as IdentityValidate;

class Identity{
	
	/**
     * 新增数据
     * 
     * @access	public
     * @param	array	$param	参数
     * @return	bool
     */
	public static function _add(array $param){
		$valid = new IdentityValidate();
		if (!$valid->scene('add')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return IdentityModel::create($param);
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	/**
     * 修改数据
     * 
     * @access	public
     * @param	array	$param	参数
     * @return	bool
     */
	public static function _edit(array $param){
		$valid = new IdentityValidate();
		if (!$valid->scene('edit')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return IdentityModel::update($param,['id'=>$param ['id']]);
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	/**
     * 修改数据
     * 
     * @access	public
     * @param	array	$idList	主键组成的数组
     * @return	bool
     */
	public static function _del(array $idList){
		if(count($idList) < 1){
			throw new \think\Exception('参数异常,主键不存在');
		}
		try {
			IdentityModel::destroy($idList);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	
	/**
     * 修改数据
     * 
     * @access	public
     * @param	array	$idList	主键组成的数组
     * @return	bool
     */
	public static function _toggle(array $idList,string $field,int $val){
		if(count($idList) < 1){
			throw new \think\Exception('参数异常,主键不存在');
		}
		try {
			$result = IdentityModel::where([['id','in',$idList]])->update([$field=>$val]);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
}
