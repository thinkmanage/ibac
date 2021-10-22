<?php 
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Right extends Model{
	
	//表名
	protected $name = 'ibac_right';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'subject_id', 
		'target_id', 
		'resource_ids',
		'domain',
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
		self::resetCache($data['subject_id'],$data['target_id']);
	}
	//public static function onBeforeDelete(Model $data){}
	//.public static function onAfterDelete(Model $data){}

	//获取器

	//修改器
	
	//关联模型
	
	//模型方法
	
	public static function getRightData($subject_id,$target_id){
		$data = [
			'resource_ids' => [],
			'domain' => []
		];
		$info = self::where([['subject_id','=',$subject_id],['target_id','=',$target_id]])->field(['resource_ids','domain'])->find();
		if(!$info){
			return $data;
		}
		$data['resource_ids'] = explode(',',$info['resource_ids']);
		if($info['domain']){
			$domainList = explode('|',$info['domain']);
			foreach($domainList as $v2){
				$rd = explode(':',$v2);
				if(count($rd) == 2){
					$data['domain'][$rd[0]] = explode(',',$rd[1]);
				}
			}
		}
		return $data;
	}
	/**
     * 设置/获取缓存
     * @access public
     * @return all
     */
	public static function cache($subject_id,$target_id){
		$cache = Cache::get('ibac_right_'.$subject_id.'.'.$target_id,null);
		if(!$cache){
			$cache = self::getRightData($subject_id,$target_id);
			Cache::set('ibac_right_'.$subject_id.'.'.$target_id,$cache);
		}
		return $cache;
	}
	
	/**
     * 设置/获取缓存
     * @access public
     * @return all
     */
	public static function resetCache($subject_id,$target_id){
		Cache::delete('ibac_right_'.$subject_id.'.'.$target_id);
		return self::cache($subject_id,$target_id);
	}
	
	public static function resetCacheAll(){
		$list = self::field(['subject_id','target_id'])->select();
		foreach($list as $v){
			Cache::delete('ibac_right_'.$v['subject_id'].'.'.$v['target_id']);
			self::cache($v['subject_id'],$v['target_id']);
		}
		return true;
	}
	
	
}
