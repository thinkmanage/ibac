<?php 
/**
 * @Name Resource 
 * @Title 资源验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2020-02-22 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Resource extends Validate{

	/*========验证规则========*/
	protected $rule = [
		'id' => [
			'require',
			'number',
			'max'=>'10',
			'checkId'
		],
		'p_id' => [
			'require',
			'number',
			'max'=>'10',
		],
		'identifying' => [
			'require',
			'max'=>'32',
		],
		'name' => [
			'max'=>'80',
		],
		'title' => [
			'max'=>'20',
		],
		'icon' => [
			'max'=>'120',
		],
		'ext' => [
			'max'=>'255',
		],
		'condition' => [
			'max'=>'100',
		],
		'sort' => [
			'require',
			'number',
			'max'=>'5',
		],
		'type' => [
			'require',
			'number',
			'max'=>'1',
			'checkType',
		],
		'is_permission' => [
			'require',
			'number',
			'max'=>'1',
			'checkIsDomain',
		],
		'hide' => [
			'require',
			'number',
			'max'=>'1',
			'checkHide',
		],
		'status' => [
			'require',
			'number',
			'max'=>'1',
			'checkStatus',
		],
	];

	/*========提示信息========*/
	protected $message = [
		'id.require'=>'主键 为 必填',
		'id.number'=>'主键 只能为 数字',
		'id.max'=>'主键 的长度不能超过 10 个字符',
		'p_id.require'=>'上级 为 必填',
		'p_id.number'=>'上级 只能为 数字',
		'p_id.max'=>'上级 的长度不能超过 10 个字符',
		'identifying.require'=>'所属 为 必填',
		'identifying.max'=>'所属 的长度不能超过 32 个字符',
		'name.max'=>'名称 的长度不能超过 80 个字符',
		'title.max'=>'标题 的长度不能超过 20 个字符',
		'icon.max'=>'图标 的长度不能超过 120 个字符',
		'ext.max'=>'附加参数 的长度不能超过 255 个字符',
		'condition.max'=>'附加规则 的长度不能超过 100 个字符',
		'sort.require'=>'排序 为 必填',
		'sort.number'=>'排序 只能为 数字',
		'sort.max'=>'排序 的长度不能超过 5 个字符',
		'type.require'=>'类型 为 必填',
		'type.number'=>'类型 只能为 数字',
		'type.max'=>'类型 的长度不能超过 1 个字符',
		'hide.require'=>'隐藏 为 必填',
		'hide.number'=>'隐藏 只能为 数字',
		'hide.max'=>'隐藏 的长度不能超过 1 个字符',
		'status.require'=>'状态 为 必填',
		'status.number'=>'状态 只能为 数字',
		'status.max'=>'状态 的长度不能超过 1 个字符',

	];

	/*========验证场景========*/
	public function sceneEdit(){
		return $this->only(['id','p_id','identifying','name','title','icon','ext','condition','sort','type','is_permission','hide','status']);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}
	public function sceneAdd(){
		return $this->only(['p_id','identifying','name','title','icon','ext','condition','sort','type','is_permission','hide','status']);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}

	/*========验证函数========*/
	/**
	 * 验证上级
	 *
	 * @param unknown $value
	 * @param unknown $rule
	 * @param unknown $data
	 * @return string|boolean
	 */
	protected function checkId($value, $rule, $data){
		if($value == $data['p_id']){
			return '当前资源的父对象不能为自身';
		}
		$r = self::TraversePid($value,$data['p_id']);
		if(!$r){
			return '当前资源的父对象不能为自身的子对象';
		}
		return true;
	}
	
	//遍历所有上级节点
	protected static function TraversePid($id){
		$newId = \thinkmanage\ibac\model\Resource::where([['p_id','=',$id]])->value('id');
		if($newId == $id){
			return false;
		}
		if($newId){
			return self::TraversePid($newId);
		}
		return true;
	}
	
	/**
	 * 验证类型
	 *
	 * @param unknown $value
	 * @param unknown $rule
	 * @param unknown $data
	 * @return string|boolean
	 */
	protected function checkType($value, $rule, $data)
	{
		$typeDict = \thinkmanage\ibac\model\Resource::$typeDict;
		if (!isset($typeDict[$value])){
			return '类型 数据异常';
		}
		if($value == 1){
			if($data['name'] == ''){
				return '内联资源名称不能为空';
			}
			if($data['title'] == ''){
				return '内联资源标题不能为空';
			}
		}
		return true;
	}

	
	/**
	 * 验证隐藏
	 *
	 * @param unknown $value
	 * @param unknown $rule
	 * @param unknown $data
	 * @return string|boolean
	 */
	protected function checkHide($value, $rule, $data){
		if (!in_array($value,[0,1])){
			return '隐藏 数据异常';
		}
		return true;
	}
	protected function checkIsDomain($value, $rule, $data){
		if (!in_array($value,[0,1])){
			return '数据域 数据异常';
		}
		return true;
	}
	
	/**
	 * 验证状态
	 *
	 * @param unknown $value
	 * @param unknown $rule
	 * @param unknown $data
	 * @return string|boolean
	 */
	protected function checkStatus($value, $rule, $data){
		if (!in_array($value,[0,1])){
			return '状态 数据异常';
		}
		return true;
	}
}
