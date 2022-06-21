<?php 
/**
 * @Name Permission 
 * @Title 数据权限模型
 * @Author Ghj<912524639@qq.com>
 * @Time 2022-02-25 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Permission extends Model{

	//模型名
	protected $name = 'ibac_permission';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'resource_id', 
		'title', 
		'condition', 
		'status', 
		'create_time', 
		'update_time', 
	];
	
	//模型字段自动转换
	//protected $type = [ ];
	
	//json字段
	//protected $json = [ ];
	
	//只读字段
	//protected $readonly = [ ];
	
	//属性字典

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
	public static function getConditions($ids){
		$cache	= self::cache();
		$res	= [];
		foreach($ids as $id){
			if(isset($cache[$id])){
				$res[$id] = $cache[$id];
			}
		}
		return $res;
	}
	
	
	/**
     * 设置/获取缓存
     * @access public
	 * type 返回类型 list列表 tree树形 
     * @return all
     */
	public static function cache(){
		$cache = Cache::get('ibac_permission',null);
		if(!$cache){
			$cache = self::where([['status','=',1]])->column('condition','id');
			Cache::set('ibac_permission',$cache);
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
		Cache::delete('ibac_permission');
		return self::cache($type);
	}
}
