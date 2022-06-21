<?php 
/**
 * @Name Permission 
 * @Title 数据权限验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2022-02-25 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Permission extends Validate{

	/*========验证规则========*/
	protected $rule = [
		'id' => [
			'require',
			'number',
			'max'=>'10',
		],
		'resource_id' => [
			'require',
			'number',
			'max'=>'10',
			'checkResourceId'
		],
		'title' => [
			'require',
			'max'=>'80',
		],
		'condition' => [
			'require',
			'max'=>'200',
		],
		'status' => [
			'number',
			'max'=>'2',
		],
	];

	/*========提示信息========*/
	protected $message = [
		'id.require' => '主键 为 必填',
		'id.number' => '主键 只能为 数字',
		'id.max' => '主键 的长度不能超过 10 个字符',
		'resource_id.require' => '资源 为 必填',
		'resource_id.number' => '资源 只能为 数字',
		'resource_id.max' => '资源 的长度不能超过 10 个字符',
		'title.require' => '名称 为 必填',
		'title.max' => '名称 的长度不能超过 80 个字符',
		'condition.require' => '条件 为 必填',
		'condition.max' => '条件 的长度不能超过 200 个字符',
		'status.number' => '状态 只能为 数字',
		'status.max' => '状态 的长度不能超过 2 个字符',
	];

	/*========验证场景========*/
	public function sceneEdit(){ 
		return $this->only(['id','resource_id','title','condition','status']);
		//->append('字段名', ['追加规则'])->remove('字段名', ['移除规则'])
	}
	public function sceneAdd(){ 
		return $this->only(['id','resource_id','title','condition','status']) 
		->remove('id', ['require']);
		//->append('字段名', ['追加规则'])->remove('字段名', ['移除规则'])
	}

	/*========验证函数========*/
	protected function checkStatus($value, $rule, $data=[]){
		return in_array($value,[0,1])?true:"[状态] 数据异常'";
	}

	/*========验证函数========*/
	protected function checkResourceId($value, $rule, $data=[]){
		$count = \thinkmanage\ibac\model\Resource::where([
			['id','=',$value],
		])->count ();
		return $count>0?true:"[资源] 不存在'";
	}
}
