<?php 
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Related extends Model {
	
	//表名
	protected $name = 'ibac_related';
	
	//主键
	protected $pk = 'id';
	
	//字段
	protected $field = [
		'id', 
		'user_id', 
		'subject_id', 
		'target_id', 
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
	//public static function onAfterWrite(Model $data){}
	//public static function onBeforeDelete(Model $data){}
	//public static function onAfterDelete(Model $data){}

	//获取器
	protected function getCreateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	
	protected function getUpdateTimeAttr($value,$data){
		return date( "Y-m-d H:i:s", $value);;
	}
	
	protected function getSubjectTitleAttr($value,$data){
		$arr = Config::get('ibac.identity');
		if(isset($arr[$data['subject_id']])){
			return $arr[$data['subject_id']]['title'];
		}else{
			return '数据异常';
		}
	}
	
	protected function getTargetTitleAttr($value,$data){
		$arr = Config::get('ibac.identity');
		if(isset($arr[$data['subject_id']])){
			$model = $arr[$data['subject_id']]['model'];
			$cache = $model::_cache();
			return $cache[$data['target_id']];
		}else{
			return '数据异常';
		}
	}

	//修改器
	
	//关联模型
	
	/**
	 * 关联模型 (用户)
	 *
	 * @return \think\model\relation\HasOne
	 */
	public function user()
	{
		return $this->hasOne('app\core\model\User', 'id', 'user_id')->bind([
			'user_username' => 'username',
			'user_truename' => 'truename'
		]);
	}
	
	//模型方法
	
}
