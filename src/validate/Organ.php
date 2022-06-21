<?php 
/**
 * @Name IbacOrgan 
 * @Title 组织验证器
 * @Author Ghj<912524639@qq.com>
 * @Time 2021-10-22 
 * @Site http:// www.thinkmanage.cn/
 */


namespace thinkmanage\ibac\validate;

use think\facade\Db;
use think\Validate;

class Organ extends Validate{

	/*========验证规则========*/
	protected $rule = [
		'id' => [
			'require',
			'number',
			'max'=>'10'
		],
		'p_id' => [
			'require',
			'number',
			'max'=>'10',
			'checkPid'
		],
		'title' => [
			'require',
			'max'=>'120',
		],
		'code' => [
			'max'=>'120',
			'checkCode'
		],
		'sort' => [
			'require',
			'number',
			'max'=>'5',
		],
	];

	/*========提示信息========*/
	protected $message = [
		'id.require' => '主键 为 必填',
		'id.number' => '主键 只能为 数字',
		'id.max' => '主键 的长度不能超过 10 个字符',
		'p_id.require' => '上级 为 必填',
		'p_id.number' => '上级 只能为 数字',
		'p_id.max' => '上级 的长度不能超过 10 个字符',
		'title.require' => '名称 为 必填',
		'title.max' => '名称 的长度不能超过 120 个字符',
		'code.require' => '代码 为 必填',
		'code.max' => '代码 的长度不能超过 120 个字符',
		'sort.require' => '排序 为 必填',
		'sort.number' => '排序 只能为 数字',
		'sort.max' => '排序 的长度不能超过 5 个字符',
	];

	/*========验证场景========*/
	public function sceneEdit(){ 
		return $this->only(['id','p_id','title','code','sort']);
		//->append('字段名', ['追加规则'])->remove('字段名', ['移除规则'])
	}
	public function sceneAdd(){ 
		return $this->only(['id','p_id','title','code','sort']) 
		->remove('id', ['require']);
		//->append('字段名', ['追加规则'])->remove('字段名', ['移除规则'])
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
	protected function checkPid($value, $rule, $data){
		if($data['p_id'] == 0){
			return true;
		}
		$p_id = \thinkmanage\ibac\model\Organ::where([['id','=',$data['p_id']]])->value('id');
		if(!$p_id){
			return '父组织不存在';
		}
		if(isset($data['id']) && $data['id'] == $data['p_id']){
			return '组织的父组织不能为自身';
		}
		$r = self::TraversePid($data['id']);
		if(!$r){
			return '组织的父组织不能为自身的子组织';
		}
		return true;
	}
	
	//遍历所有上级节点
	protected static function TraversePid($id){
		$newId = \thinkmanage\ibac\model\Organ::where([['p_id','=',$id]])->value('id');
		if($newId == $id){
			return false;
		}
		if($newId){
			return self::TraversePid($newId);
		}
		return true;
	}
	/**
	 * 验证代码
	 *
	 * @param unknown $value
	 * @param unknown $rule
	 * @param unknown $data
	 * @return string|boolean
	 */
	protected function checkCode($value, $rule, $data){
		if($value == null){
			return true;
		}
		$map = [
			['code','=',$value]
		];
		if(isset($data['id']) && $data['id']>0){
			$map[] = ['id','<>',$data['id']];
		}
		$info = \thinkmanage\ibac\model\Organ::where($map)->find();
		if($info){
			return '代码与 '.$info['title'].'['.$info['id'].'] 重复';
		}
		return true;
	}
}
