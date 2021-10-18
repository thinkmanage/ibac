<?php
namespace thinkmanage\ibac\service;

use think\facade\Config;
use think\facade\Cache;

use thinkmanage\ibac\model\Policy as PolicyModel;
use thinkmanage\ibac\validate\Policy as PolicyValidate;

class Policy{
	
	/**
	 * 新增数据
	 * 
	 * @access	public
	 * @param	array	$param	参数
	 * @return	bool
	 */
	public static function _add(array $param){
		$valid = new PolicyValidate();
		if (!$valid->scene('add')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return PolicyModel::create($param);
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
		$valid = new PolicyValidate();
		if (!$valid->scene('edit')->check($param)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		try {
			return PolicyModel::update($param,['id'=>$param ['id']]);
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
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
			throw new \think\Exception('参数异常,主键不存在');
		}
		try {
			PolicyModel::destroy($idList);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
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
			throw new \think\Exception('参数异常,主键不存在');
		}
		try {
			$result = PolicyModel::where([['id','in',$idList]])->update([$field=>$val]);
			return true;
		}catch(\Exception $e){
			throw new \think\Exception($e->getMessage());
		}
	}
	
	/**
	 * 根据传入标识获取策略
	 * 
	 * @access	public
	 * @param	string	$name	标识
	 * @param	array	$param	参数
	 * @throws	\think\Exception
	 * @return	mixed
	 */
	public static function getPolicy(string $name,array $param=[]){
		//筛选当前标识下的策略
		$policy = self::choice($name,$param);
		$policyData = [
			'field'		=> false,//即没有任何字段
			'filter'	=> false,//即如何也不通过过滤
			'valid'		=> false,//即如何也不通过验证
		];
		//不存在策略或者策略类型为继承策略 返回默认结果
		if($policy == false || $policy['type'] == 1){
			return $policyData;
		}
		//存在策略数据 进行解析
		if($policy['policy'] && is_string($policy['policy'])){
			//将策略数据进行替换并且解析替换
			$temp = ibcaParseCode($policy['policy'],$param);
			$temp = json_decode($temp,true);
			$temp['field']	= isset($temp['field'])?$temp['field']:false;
			$temp['filter']	= isset($temp['filter'])?$temp['filter']:false;
			$temp['valid']	= isset($temp['valid'])?$temp['valid']:false;
			return $temp;
		}
		return $policyData;
	}
	
	/**
	 * 遍历标识下的所有策略
	 * 
	 * @param string name 策略标识
	 * @param array param 参数
	 * @return array
	 */
	public static function choice($name,$param=[]){
		//从模型层根据标识获取策略的缓存数据
		$policyList = self::getPolicyByName($name);
		if(count($policyList) < 1){
			return false;
		}
		//遍历标识下的所有策略
		foreach($policyList as $policy){
			if($policy['type'] == 1){
				//继承策略
				$temp = self::getPolicyById($policy['inherit']);
				//要继承策略不存在 或者 要继承的策略最终为继承类型
				if(!$temp || $temp['type']==1){
					continue;
				}
				//只合并类型条件和策略
				$policy['type']			= $temp['type'];
				$policy['policy']		= $temp['policy'];
				$policy['condition']	= $temp['condition'];
			}
			//判断策略条件是否满足(为空默认为满足条件) 返回结果
			if($policy['condition'] == '' || ibcaRunCode($policy['condition'],$param) === true){
				return $policy;
			}
		}
		return false;
	}
	
	/**
	 * 通过标识获取缓存(标识对应id:一条标识对应多条缓存)
	 */
	public static function getPolicyByName($name){
		$cache = Cache::get('ibac_policy_'.md5($name),null);
		if(!$cache){
			//判断是否初始过缓存
			if(Cache::get('ibac_init_policy',false)){
				return [];
			}else{
				//未初始化 进行初始化
				PolicyModel::cache();
				//再次查询
				return self::getPolicyByName($name);
			}
		}
		return $cache;
	}
	
	/**
	 * 通过ID获取缓存(id对应标识:一条id对一条缓存)
	 */
	public static function getPolicyById($id){
		if($id<1){
			return false;
		}
		$cache = Cache::get('ibac_policyitn',false);
		//判断是否有数据,无数据进行初始化
		if(!$cache){
			PolicyModel::cache();
			$cache = Cache::get('ibac_policyitn',false);
		}
		//没有对应ID的策略
		if(!isset($cache[$id])){
			return false;
		}
		$policyList = Cache::get('ibac_policy_'.$cache[$id],[]);
		if(!isset($policyList['_'.$id])){
			return false;
		}
		$policy = $policyList['_'.$id];
		//类型为继承策略 获取上级策略
		if($policy['type'] == 1){
			return self::getPolicyById($policy['inherit']);
		}
		return $policy;
	}
	
	
	/**
	 * 获取策略数据中的某一项数据
	 *
	 * @param	array	$policy	策略
	 * @param	string	$type	类型
	 * @param	mixed	$def	默认错误返回数据
	 * @return	mixed
	 */
	public static function getPolicyData(array $policy,string $type,$def=false){
		if(!isset($policy[$type])){
			return $def;
		}
		return $policy[$type];
	}
	
	/**
	 * 根据传入策略数据(标识)与字段获取策略数据中的允许字段
	 * 
	 * @access	public
	 * @param	string	$name	标识
	 * @param	array	$field	字段
	 * @param	array	$param	参数
	 * @return	array
	 */
	public static function policyField($policy,$field=[]){
		if($policy == false){
			return [];
		}
		if(!is_array($field)){
			$field = explode(',',$field);
			if(count($field)<1){
				return [];
			}
			$newField = [];
			foreach($field as $k => $v){
				if(strpos($v,' as ')){
					$temp = explode(' as ',$v);
					$newField[$temp[0]] = $temp[1];
				}else{
					$newField[$v] = $v;
				}
			}
			$field = $newField;
		}else{
			$newField = [];
			foreach($field as $k => $v){
				if(is_int($k)){
					$newField[$v] = $v;
				}else{
					$newField[$k] = $v;
				}
			}
			$field = $newField;
		}
		$res = self::getPolicyData($policy,'field',false);
		if($res == '*'){
			return $field;
		}
		if($res == false){
			return [];
		}
		$res = explode(',',$res);
		return array_filter($field, function ($v, $k) use ($res) {
			if (in_array($k, $res)) {
				return true;
			}
		}, ARRAY_FILTER_USE_BOTH);
	}
	
	/**
	 * 根据传入策略数据(标识)获取策略数据中的过滤规则
	 * 
	 * @access	public
	 * @param	string	$name	标识
	 * @param	array	$param	参数
	 * @return	string
	 */
	public static function policyFilter($policy){
		if($policy == false){
			return [];
		}
		$res = self::getPolicyData($policy,'filter',false);
		if($res == '*'){
			return '1 = 1';
		}
		if($res == false){
			return '1 <> 1';
		}
		return $res;
	}
	
	/**
	 * 根据传入策略数据(标识)与数据进行验证
	 * 
	 * @access	public
	 * @param	string	$name	标识
	 * @param	array	$data	参数
	 * @param	array	$param	参数
	 * @return	string
	 */
	public static function policyValid($policy,$data=[]){
		if($policy == false){
			throw new \think\Exception('验证失败');
		}
		$res = self::getPolicyData($policy,'valid',false);
		if($res == '*'){
			return true;
		}
		if($res == false || $res == '' || !is_array($res)){
			//验证规则出错/为空/不是数组 抛出异常
			throw new \think\Exception('验证失败');
		}
		//创建验证对象
		$rule	= [];
		$msg	= [];
		foreach($res as $k => $v){
			if(isset($rule[$k])){
				$rule[$k] = [];
			}
			foreach($v as $k2 => $v2){
				if(!isset($v2['name']) || $v2['name'] == ''){
					continue;
				}
				//是存在规则同时规则不为空
				if(isset($v2['rule']) && $v2['rule'] != ''){
					$rule[$k][$v2['name']] = $v2['rule'];
				}else{
					$rule[$k][] = $v2['name'];
				}
				if(isset($v2['msg'])){
					$msg[$k.'.'.$v2['name']] = $v2['msg'];
				}
			}
		}
		try {
			$valid = \think\facade\Validate::rule($rule)->message($msg);
		} catch (ValidateException $e) {
			throw new \think\Exception($e->getError());
		}
		if (!$valid->check($data)){
			throw new \think\Exception($valid->getError()?$valid->getError():'验证失败');
		}
		return true;
	}
}