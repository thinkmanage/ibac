<?php
namespace thinkmanage\ibac\model;

use think\Model;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Organ extends Model{
	
	//表名
	protected $name = 'ibac_organ';
	
	//主键
	protected $pk = 'id';
	
	//ddLen
	public static $ddLen = 3;
	
	//字段
	protected $field = [
		'id',
		'p_id',
		'code',
		'title',
		'sort',
		'domain',
		'create_time',
		'update_time',
	];

	//模型事件
	//public static function onAfterRead(Model $data){}
	public static function onBeforeInsert(Model $data){
		$data['domain'] = self::newDataDomain($data['p_id']);
		$data["create_time"] = time();
	}
	//public static function onAfterInsert(Model $data){}
	public static function onBeforeUpdate(Model $data){
		//判断p_id是否改变
		$oInfo = Db::name('ibac_organ')->where([['id','=',$data['id']]])->find();
		if($oInfo['p_id'] != $data['p_id']){
			//获取新的数据域
			$data['domain'] = self::newDataDomain($data['p_id']);
			//替换所有子集的数据域
			$childList = Db::name('ibac_organ')->where("id <> ".$data['id']." and LEFT (`domain`,".strlen($oInfo['domain']).") = '".$oInfo['domain']."'")->column('domain','id');
			foreach($childList as $k => $v){
				Db::name('ibac_organ')->where([['id','=',$k]])->update(['domain'=>$data['domain'].substr($v,strlen($oInfo['domain']))]);
			}
		}
	}
	//public static function onAfterUpdate(Model $data){}
	public static function onBeforeWrite(Model $data){
		$data["update_time"] = time();;
	}
	public static function onAfterWrite(Model $data){
		self::resetCache();
	}
	public static function onBeforeDelete(Model $data){
		if($data['id'] == 1){
			throw new \Exception('此组织禁止删除');
		}
		if(!isset($data['domain'])){
			$dd = Db::name('ibac_organ')->where(['id'=>$data['id']])->value('domain');
		}else{
			$dd = $data['domain'];
		}
		Db::name('ibac_organ')->where("id <> ".$data['id']." and LEFT (`domain`,".strlen($dd).") = '".$dd."'")->delete();
	}
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
			return '根组织';
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
     * 获取新数据域代码
     * @access public
     * @return all
     */
	public static function newDataDomain($id=0){
		//取最后一个顶级数据域
		$pdd = Db::name('ibac_organ')->where([['id','=',$id]])->value('domain');
		if(!$pdd){
			$pdd = '';
		}
		$dorg = Db::name('ibac_organ')->where([['p_id','=',$id]])->order(['domain'=>'desc'])->find();
		if($dorg){
			$dpdd = $dorg['domain'];
		}else{
			$dpdd = '0';
		}
		return self::formatDataDomain($dpdd,$pdd);
	}
	/**
     * 根据位数设置数据域代码
     * @access public
     * @return all
     */
	public static function formatDataDomain($dd='0',$pdd){
		if($dd=='0'){
			return $pdd.str_pad(1,self::$ddLen,"0",STR_PAD_LEFT);
		}
		if(strlen($dd)<self::$ddLen){
			throw new \Exception('数据域位数错误');
		}
		if(strlen($dd)>self::$ddLen){
			$dd = substr($dd,(self::$ddLen*-1));
		}
		$dd = (int)$dd+1;
		if(strlen($dd.'')>self::$ddLen){
			throw new \Exception('数据域超出限制');
		}
		return $pdd.str_pad($dd,self::$ddLen,"0",STR_PAD_LEFT);
	}
	
	/**
     * 设置/获取缓存
     * @access public
	 * type 返回类型 list列表 tree树形 
     * @return all
     */
	public static function cache(){
		$cache = Cache::get('ibac_organ',null);
		if(!$cache){
			$organ = self::order(['sort'=>'asc'])->column('title','domain');
			if(!$organ){
				$cache = [];
			}else{
				Cache::set('ibac_organ',$organ);
			}
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
	public static function resetCache($type="list"){
		Cache::delete('ibac_organ');
		return self::cache($type);
	}
}