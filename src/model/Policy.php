<?php
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Policy extends Model {
	
	//表名
	protected $name = 'ibac_policy';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'name', 
		'title', 
		'type', 
		'inherit', 
		'condition', 
		'policy', 
		'weight', 
		'status', 
		'create_time', 
		'update_time', 
	];

	//模型字典
	public static $typeDict = ['0' => '标准','1' => '继承'];
	
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
	protected function getTypeTextAttr($value,$data){
		return isset($this->typeDict[$data['type']])?$this->typeDict[$data['type']]:'未知';
	}
	
	protected function getCreateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	protected function getUpdateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	
	//修改器
	
	//关联模型
	
	/**
     * 获取/设置缓存
     * @access public
     * @return all
     */
	public static function cache()
	{
		//验证是否进行过初始化
		$cache = Cache::get('ibac_init_policy',false);
		if(!$cache){
			//获取所有的策略
			$list = self::where([['status','=',1]])->order(['weight'=>'desc'])->field(['id','name','type','inherit','condition','policy','weight'])->select();
			if(!$list){
				Cache::set('ibac_init_policy',true);
				Cache::set('ibac_policyitn',[]);
			}
			$list = $list->toArray();
			$cache = [];
			$itn = [];
			//通过身份模型 获取所有的身份数据
			foreach($list as $v){
				if(!isset($cache[$v['name']])){
					$cache[$v['name']] = [];
				}
				$cache[$v['name']]['_'.$v['id']] = $v;
				$itn[$v['id']] = md5($v['name']);
			}
			foreach($cache as $k => $v){
				Cache::set('ibac_policy_'.md5($k),$v);
			}
			Cache::set('ibac_policyitn',$itn);
			Cache::set('ibac_init_policy',true);
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
		Cache::set('ibac_init_policy',false);
		self::cache();
	}
}