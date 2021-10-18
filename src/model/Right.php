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
		'enable', 
		'disable', 
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
		\thinkmanage\ibac\model\Identity::resetCache();
	}
	//public static function onBeforeDelete(Model $data){}
	public static function onAfterDelete(Model $data){
		\thinkmanage\ibac\model\Identity::resetCache();
	}

	//获取器

	//修改器
	
	//关联模型
	
	//模型方法
	public static function getRule($subject_id,$target_id){
		$result = [
			'enable'=>[],
			'disable'=>[]
		];
		$right = self::where([['subject_id','=',$subject_id],['target_id','=',$target_id]])->field(['enable','disable'])->find();
		if($right){
			$right = $right->toArray();
			$result['enable'] = explode(',',$right['enable']);
			$result['disable'] = explode(',',$right['disable']);
		}
		return $result;
	}
}
