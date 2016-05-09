<?php
class AttackSkill {

	private $_id = 0;

	private $_def = null;

	//两个Buff
	private $buff = null;
	private $buff2 = null;

	public function __construct($id, $caster){
		//TODO 从表中获取技能配置
		
		
		$this->_id = $id;
		$this->_caster = $caster;
		$this->_def = $def;
	}
	
	/**
	 * @see 技能的定义id
	 */
	public function id(){
		return $this->_id;
	}
	
	/**
	 * @see 技能的等级
	 */
	public function level(){
		return $this->_def['level'];
	}
	
	/**
	 * @see 技能效果类型
	 */
	public function effectType(){
		return $this->_def['effectType'];
	}
	
	/**
	 * @see 效果目标数量
	 */
	public function targetNum(){
		return $this->_def['targetNum'];
	}
	
	/**
	 * @see 技能效果值
	 */
	public function effectValue(){
		return $this->_def['effectValue'];
	}

	/**
	 * @see buff目标
	 */
	public function buffTarget($id){
		if ($id == 1) {
			return $this->_def['buffTarget'];
		} else {
			return $this->_def['buff2Target'];
		}
	}

	/**
	 * @see getBuff
	 */
	public function getBuff(){
		if($this->buff == null && $this->_def['buffId'] != 0){
			$this->buff = new Buff($this->_def['buffId']);
			$this->buff->setCaster($this->_caster);
		}
		return $this->buff;
	}

	public function getBuff2() {
		if($this->buff2 == null && $this->_def['buff2Id'] != 0){
			$this->buff2 = new Buff($this->_def['buff2Id']);
			$this->buff2->setCaster($this->_caster);
		}
		return $this->buff2;
	}

	/**
	 * @see 连击权重
	 */
	public function batterWeight(){
		return $this->_def['batterWeight'];
	}
}
?>