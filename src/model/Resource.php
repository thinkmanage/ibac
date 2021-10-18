<?php
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Resource extends Model{
	
	//表名
	protected $name = 'ibac_resource';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'name', 
		'title', 
		'type', 
		'model', 
		'weight', 
		'status', 
		'create_time', 
		'update_time', 
	];
	
	public static $typeDict = [
		'0' => '菜单',
		'1' => '节点',
		'2' => '外链'
	];

	//模型事件
	//public static function onAfterRead(Model $data){}
	public static function onBeforeInsert(Model $data){
		$data["create_time"] = time();;
	}
	//public static function onAfterInsert(Model $data){}
	//public static function onBeforeUpdate(Model $data){}
	//public static function onAfterUpdate(Model $data){}
	public static function onBeforeWrite(Model $data){
		$data["update_time"] = time();;
	}
	public static function onAfterWrite(Model $data){
		self::resetCache();
	}
	//public static function onBeforeDelete(Model $data){}
	public static function onAfterDelete(Model $data){
		self::resetCache();
	}

	//获取器
	protected function getCreateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	protected function getUpdateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	public function getPIdTextAttr($value, $data){
		if($data['p_id'] == 0){
			return '根节点';
		}
		$title = self::where(['id'=>$data['p_id']])->value('title');
		if($title){
			return $title;
		}else{
			return '数据异常';
		}
	}

	//修改器
	
	//关联模型
	
	//模型方法
	/**
     * 设置/获取配置项
     * @access public
     * @return all
     */
	public static function nti()
	{
		$cache = Cache::get('ibac_resource_nti',null);
		if(!$cache){
			$cache = self::where([['status','=',1],['type','<>',0]])->column('id','name');
			if(!$cache){
				$cache = [];
			}
			Cache::set('ibac_resource_nti',$cache);
		}
		return $cache;
	}
	/**
     * 设置/获取缓存
     * @access public
     * @return all
     */
	public static function cache()
	{
		$cache = Cache::get('ibac_resource',null);
		if(!$cache){
			$cache = self::where([['status','=',1]])->column('ext,condition','name');
			if(!$cache){
				$cache = [];
			}
			Cache::set('ibac_resource',$cache);
		}
		return $cache;
	}
	
	/**
     * 重置配置
     * @access public
     * @param string	$name
     * @param all		$config
     * @return void|all
     */
	public static function resetCache()
	{
		Cache::delete('ibac_resource');
		Cache::delete('ibac_resource_nti');
		self::nti();
		return self::cache();
	}
}