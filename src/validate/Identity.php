<?php 
/**
 * @Name AuthIdentity 
 * @Title 身份验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2020-11-19 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Identity  extends Validate{

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
			'max'=>'20',
		],
		/* Model */
		'model' => [
			'require',
			'max'=>'200',
		],
		/* Status */
		'status' => [
			'require',
			'checkStatus'
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
		'title.max'=>'标题 的长度不能超过 20 个字符',

		/* Model */
		'model.require'=>'标题 为 必填',
		'model.max'=>'标题 的长度不能超过 200 个字符',
		
		/* Status */
		'status.require'=>'权重 为 必填',

	];

	/*========验证场景========*/
	public function sceneEdit(){
		return $this->only(['id','name','title','model','status'])
		->remove('name', ['require'])
		->remove('title', ['require'])
		->remove('model', ['require'])
		->remove('status', ['require']);
	}
	
	public function sceneAdd(){
		return $this->only(['name','title','model','status']);
	}

	/*========验证函数========*/
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
