<?php
/**
 * Created by Tishoy.
 * Date: 2016/3/4
 * Time: 12:48
 */
class TeamInBattle {
    //属于我方队伍
    private $_isPlayerTeam;
    //玩家等级
    private $_user_level = 0;
    //战场英雄
    private $_herosInBattle = array();
    //是否结算命中
    private $_isHitMiss = false;
    //是否结算暴击
    private $_isCrit = false;
    //掉落红心
    public $_heartDrop = 0;
    //pet是否参战斗
    private $_pet_attend_battle = false;
    //队伍中的PET
    private $_pet;

    public function __construct($uid){

        if ($uid == GlobalEngine::$loginUser) {
            $this->_isPlayerTeam = BattleHelper::BATTLE_TEAM_US;
        } else {
            $this->_isPlayerTeam = BattleHelper::BATTLE_TEAM_ENEMY;
        }

        if (true) {
            $this->_isHitMiss = true;
            $this->_isCrit = true;
            return;
        }

        $PlayerInfo = new PlayerInfo($uid);
        $player_info = $PlayerInfo->get();
        $level = $player_info['level'];
        if ($level >= 15) {
            $this->_isHitMiss = true;
        }
        if ($level >= 10) {
            $this->_isCrit = true;
        }
    }


    /**
     * @return 通过仇恨寻找被攻击目标
     */
    public function getHatredAim() {
        $maxHatred = 0;
        $result = array();
        $helper = array();
        foreach ($this->getAliveHerosInBattle(false) as $key => $value) {
            if ($maxHatred < $value->getAttrValueByName('HATRED_POINT')) {
                $maxHatred = $value->getAttrValueByName('HATRED_POINT');
                $helper = array();
                array_push($helper, $value);
            }
            elseif ($maxHatred == $value->getAttrValueByName('HATRED_POINT')) {
                array_push($helper, $value);
            }
        }
        $index = array_rand($helper, 1);
        array_push($result, $helper[$index]);
        return $result;
    }

    /**
     * 获取随机攻击目标
     * return 英雄array
     */
    public function getRandomAim($num, $heal = false) {
        $result = array();
        $helper = $this->getAliveHerosInBattle($heal);
        if (count($helper) == 0) {
            return $result;
        }
        if (count($helper) == 1) {
            return $helper;
        } elseif ($num == 1 && count($helper) == 1) {
            array_push($result, $helper[0]);
            return $result;
        }
        elseif ($num == 1 && count($helper) != 1) {
            array_push($result, $helper[array_rand($helper, min($num, count($helper)))]);
            return $result;
        }
        foreach (array_rand($helper, min($num, count($helper))) as $k => $index) {
            array_push($result, $helper[$index]);
        }
        return $result;
    }

    /**
     * 随机分配红心
     * 返回随机增加英雄列表
     */
    public function randomHeart() {
        $hero = $this->getRandomAim(1, true);
        if (count($hero) == 0) {
            $this->_heartDrop = 0;
            return null;
        }
        else {
            $this->_heartDrop--;
        }
        return $hero[0];
    }

    /**
     * 宠物被动技能释放目标
     * @param $type
     * @return array
     */
    public function getAliveHeroesByType($type)
    {
        $result = array();
        foreach ($this->getAliveHerosInBattle() as $k => $v) {
            if ("1" . $v->getHeroType() == strval($type)) {
                array_push($result, $v);
            }
        }
        return $result;
    }

    /**
     * 获取队伍中存活的英雄
     * @param boolean //是否包括满血的英雄
     * @return array
     */
    public function getAliveHerosInBattle($lifeFull = false)
    {
        $result = array();
        foreach ($this->_herosInBattle as $key => $value) {
            if ($lifeFull && $value->getCurrentHp() >= $value->getCurrentHpMax()) {
                continue;
            }
            if ($value->isDead() == false) {
                array_push($result, $value);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getHerosInBattle()
    {
        return $this->_herosInBattle;
    }

    /**
     * @param array $herosInBattle
     */
    public function setHerosInBattle($herosInBattle)
    {
        $this->_herosInBattle[count($this->_herosInBattle)] = $herosInBattle;
//        array_push($this->_herosInBattle, $herosInBattle);
    }

    /**
     * @return int
     */
    public function getAliveNum()
    {
        return count($this->getAliveHerosInBattle());
    }

    /**
     * 用于判断是否需要计算命中闪避
     * @return mixed
     */
    public function getIsHitMiss()
    {
        return $this->_isHitMiss;
    }

    /**
     * 用于判断是否需要计算暴击
     * @return boolean
     */
    public function getIsIsCrit()
    {
        return $this->_isCrit;
    }

    /**
     * 通过BattleID获取英雄
     */
    public function getHeroByID($id) {
        return $this->_herosInBattle[$id];
    }

    /**
     * @return null
     */
    public function getUserLevel()
    {
        return $this->_user_level;
    }

    /**
     * 设置宠物
     */
    public function setPet($pet)
    {
        $this->_pet = $pet;
    }

    /**
     * @return mixed
     */
    public function getPet()
    {
        return $this->_pet;
    }

    /**
     * @param boolean $pet_attend_battle
     */
    public function setPetAttendBattle($pet_attend_battle)
    {
        $this->_pet_attend_battle = $pet_attend_battle;
        if ($this->_pet_attend_battle == true) {
            $this->setHerosInBattle($this->getPet());
        }
    }
}

?>