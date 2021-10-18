<?php 
namespace thinkmanage\ibac\service;

use think\Model;

use thinkmanage\ibac\model\Right as RightModel;
use thinkmanage\ibac\validate\Right as RightValidate;

class Right{
	
	/**
     * 新增数据
     * 
     * @access	public
     * @param	array	$param	参数
     * @return	bool
     */
	public static function _add(array $param){
		$valid = new RightValidate();
		if (!$valid->scene('add')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return RightModel::create($param);
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
		$valid = new RightValidate();
		if (!$valid->scene('edit')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return RightModel::update($param,['id'=>$param ['id']]);
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	/**
     * 删除数据
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
			RightModel::destroy($idList);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	/**
     * 删除数据(条件)
     * 
     * @access	public
     * @param	array	$map	删除条件
     * @return	bool
     */
	public static function _delByMap(array $map){
		$idList = RightModel::where($map)->column('id');
		if(count($idList)<1){
			return true;
		}
		return self::_del($idList);
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
			$result = RightModel::where([['id','in',$idList]])->update([$field=>$val]);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
}
