<?php
/** buff  */
class Buff {
	
	private $_id = 0;
	
	private $_def = null;
	//释放者ID  整形
	private $_caster = 0;
	//持续到回合数
	public $_tillRounds = 0;

	public function __construct($id){
		$def = GameDef::getGameDefById(GameDef::BUFF_BASE, $id);
		if(empty($def)){
			throwGameException('buff is not exist. id:' . $id, StatusCode::APP_USER_ERROR, __METHOD__);
		}
		
		$this->_id = $id;
		$this->_def = $def;
	}
	
	/**
	 * @see 技能的定义id
	 */
	public function id(){
		return $this->_id;
	}
	
	/**
	 * @see buff类型
	 */
	public function buffType(){
		return $this->_def['buffType'];
	}
	
	/**
	 * @see buff效果类型
	 */
	public function effectType(){
		return $this->_def['effectType'];
	}
	
	/**
	 * @see buff效果值
	 */
	public function effectValue(){
		return $this->_def['effectValue'];
	}
	
	/**
	 * @see buff持续回合数
	 */
	public function rounds(){
		return $this->_def['rounds'];
	}

	/**
	 * @return null
	 */
	public function getCaster()
	{
		return $this->_caster;
	}

	/**
	 * @param null $caster
	 */
	public function setCaster($caster)
	{
		$this->_caster = $caster;
	}

}
?>