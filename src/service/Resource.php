<?php 
namespace thinkmanage\ibac\service;

use think\Model;

use thinkmanage\ibac\model\Resource as ResourceModel;
use thinkmanage\ibac\validate\Resource as ResourceValidate;

class Resource{
	
	/**
     * 新增数据
     * 
     * @access	public
     * @param	array	$param	参数
     * @return	bool
     */
	public static function _add(array $param){
		$valid = new ResourceValidate();
		if (!$valid->scene('add')->check($param)){
			throw new \Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return ResourceModel::create($param);
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
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
		$valid = new ResourceValidate();
		if (!$valid->scene('edit')->check($param)){
			throw new \Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return ResourceModel::update($param,['id'=>$param ['id']]);
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
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
			throw new \Exception('参数异常,主键不存在');
		}
		try {
			ResourceModel::destroy($idList);
			return true;
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
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
			throw new \Exception('参数异常,主键不存在');
		}
		try {
			$result = ResourceModel::where([['id','in',$idList]])->update([$field=>$val]);
			return true;
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
	}
}
