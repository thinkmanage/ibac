<?php 
/**
 * @Name Right 
 * @Title 权限验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2020-02-22 
 * @Site http:// www.thinkmanage.cn/
 */
 
namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Right extends Validate{

	/*========验证规则========*/
	protected $rule = [
		/* Id */
		'id' => [
			'require',
			'number',
			'max'=>'10',
		],
		/* SubjectId */
		'identity' => [
			'require',
			'max'=>'20',
		],
		/* TargetId */
		'target_id' => [
			'require',
			'number',
			'max'=>'10',
		],
	];

	/*========提示信息========*/
	protected $message = [
		/* Id */
		'id.require'=>'主键 为 必填',
		'id.number'=>'主键 只能为 数字',
		'id.max'=>'主键 的长度不能超过 10 个字符',

		/* SubjectId */
		'identity.require'=>'身份类型 为 必填',
		'identity.max'=>'身份类型 的长度不能超过 20 个字符',

		/* TargetId */
		'target_id.require'=>'目标主键 为 必填',
		'target_id.number'=>'目标主键 只能为 数字',
		'target_id.max'=>'目标主键 的长度不能超过 10 个字符',

	];

	/*========验证场景========*/
	public function sceneEdit(){
		return $this->only(['id','identity','target_id',]);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}
	public function sceneAdd(){
		return $this->only(['identity','target_id',]);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}

	/*========验证函数========*/

}
