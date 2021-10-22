<?php
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Identity extends Model{
	
	//表名
	protected $name = 'ibac_identity';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'name', 
		'title', 
		'type', 
		'model', 
		'status', 
		'create_time', 
		'update_time', 
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

	//修改器
	
	//关联模型
	
	//模型方法
	/**
     * 设置/获取缓存
     * @access public
     * @return all
     */
	public static function cache(){
		$cache = Cache::get('ibac_identity',null);
		if(!$cache){
			$cache = self::column('id,title,model','name');
			if(!$cache){
				$cache = [];
			}else{
				foreach($cache as $k => $v){
					$model = $v['model'];
					$cache[$k]['children'] = $model::column('title','id');
				}
			}
			Cache::set('ibac_identity',$cache);
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
	public static function resetCache(){
		Cache::delete('ibac_identity');
		return self::cache();
	}
}