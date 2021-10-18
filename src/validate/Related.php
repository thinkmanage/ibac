<?php 
/**
 * @Name AuthRelated 
 * @Title 身份验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2020-11-29 
 * @Site http:// www.thinkmanage.cn/
 */

namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Related extends Validate{

	/*========验证规则========*/
	protected $rule = [
		/* Id */
		'id' => [
			'require',
			'number',
			'max'=>'10',
		],
		/* UserId */
		'user_id' => [
			'require',
			'number',
			'max'=>'10',
		],
		/* SubjectId */
		'subject_id' => [
			'require',
			'max'=>'10',
			'checkSubjectId'
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

		/* UserId */
		'user_id.require'=>'用户主键 为 必填',
		'user_id.number'=>'用户主键 只能为 数字',
		'user_id.max'=>'用户主键 的长度不能超过 10 个字符',

		/* SubjectId */
		'subject_id.require'=>'类型 为 必填',
		'subject_id.max'=>'类型 的长度不能超过 10 个字符',

		/* TargetId */
		'target_id.require'=>'身份 为 必填',
		'target_id.number'=>'身份 只能为 数字',
		'target_id.max'=>'身份 的长度不能超过 10 个字符',

	];

	/*========验证场景========*/
	public function sceneAdd(){
		return $this->only(['user_id','subject_id','target_id']);
		//->append('字段名', ['追加规则'])
		//->remove('字段名', ['移除规则'])
	}

	/*========验证函数========*/
	
	protected function checkSubjectId($value, $rule, $data){
		$subject_list = \thinkmanage\ibac\model\Identity::where([['status','=',1]])->order(['weight'=>'desc'])->column('id,name,title,model', 'name');
		if(!array_key_exists($value,$subject_list)){
			return '类型 错误';
		}
		$subject = $subject_list[$value];
		$count = $subject['model']::where([['id','=',$data['target_id']]])->count ();
		if($count<1){
			return '身份 错误';
		}
		if($subject['multiple'] == 0){
			$count = \thinkmanage\ibac\model\Related::where([
				['user_id','=',$data['user_id']],
				['subject_id','=',$data['subject_id']],
			])->count ();
			if($count>0){
				return $subject['title'].' 不允许多选';
			}
		}
		$count = \thinkmanage\ibac\model\Related::where([
			['user_id','=',$data['user_id']],
			['subject_id','=',$data['subject_id']],
			['target_id','=',$data['target_id']],
		])->count ();
		if($count>0){
			return '此'.$subject['title'].'已添加, 无需重复添加';
		}
		return true;
	}
}
