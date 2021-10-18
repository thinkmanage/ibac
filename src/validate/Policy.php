<?php 
/**
 * @Name AuthPolicy 
 * @Title 策略验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2020-11-19 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Policy extends Validate{

	/*========验证规则========*/
	protected $rule = [
		/* Id */
		'id' => [
			'require',
			'number',
			'max'=>'10',
		],
		/* Name */
		'name' => [
			'require',
			'max'=>'80',
		],
		/* Title */
		'title' => [
			'require',
			'max'=>'80',
		],
		/* Condition */
		'condition' => [
			'require',
		],
		/* Weight */
		'weight' => [
			'require',
			'number',
			'max'=>'5',
			'checkWeight'
		],
	];


	/*========提示信息========*/
	protected $message = [
		/* Id */
		'id.require'=>'主键 为 必填',
		'id.number'=>'主键 只能为 数字',
		'id.max'=>'主键 的长度不能超过 10 个字符',

		/* Name */
		'name.require'=>'标识 为 必填',
		'name.max'=>'标识 的长度不能超过 80 个字符',

		/* Title */
		'title.require'=>'标题 为 必填',
		'title.max'=>'标题 的长度不能超过 80 个字符',

		/* Condition */
		'condition.require'=>'条件 为 必填',

		/* Weight */
		'weight.require'=>'权重 为 必填',
		'weight.number'=>'权重 只能为 数字',
		'weight.max'=>'权重 的长度不能超过 5 个字符',

	];

	/*========验证场景========*/
	public function sceneEdit(){
		return $this->only(['id','name','title','weight']);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}
	public function sceneAdd(){
		return $this->only(['name','title','weight']);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}

	/*========验证函数========*/
	/**
	 * 验证唯一
	 *
	 * @param unknown $value			
	 * @param unknown $rule			
	 * @param unknown $data			
	 * @return string|boolean
	 */
	protected function checkWeight($value, $rule, $data){
		if (isset($data['id']) && $data['id'] > 0){
			$map[] = ['id','<>',$data['id']];
		}else{
			$map = [];
		}
		$count = \thinkmanage\ibac\model\Policy::where($map)->count();
		if ($count > 0){
			return '权重 重复';
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
		if(!in_array($value,[0,1])){
			return '状态 错误';
		}
		return true;
	}
}
