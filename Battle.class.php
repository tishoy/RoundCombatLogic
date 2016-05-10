<?php
/**
 * @see 战斗
 */
header("Content-type: text/html; charset=utf-8");

class Battle
{
    /***************战斗 类型 *************************/
    //关卡战斗
    const TYPE_MISSION = 'mission';
    //竞技场战斗
    const TYPE_ARENA = 'arena';

//	//己方数据
//	protected static $mine_data = array();
//	//对方数据
//	protected static $target_data = array();
//	//己方阵型
//	protected $mine = null;
//	//对方阵型
//	protected $target = null;

    private $_type = Battle::TYPE_MISSION;
    private $_my_heroes = array();
    private $_enemy_heroes = array();
    private $_enemy_uid = 0;

    //我方Team
    private $_my_team = null;
    //敌方Team
    private $_enemy_team = null;
    //英雄列表
    private $_hero_list = array();
    //伤害列表
    private $_demage_list = array();
    //动作序列
    private $_action_sequence = array();
    //战报
    private $_report = array();
    //战斗结束
    private $_is_end = false;
    //行动者索引
    private $_actor_index = 0;
    //战场存活人数
    private $_alive_number = 0;
    //战斗回合数
    private $_round = 0;
    //速度变化灯
    private $_signal_speed_change = false;
    //排序函数
    private $sort;
    //全部帧数
    private $_total_frame = 0;
    //当前所在帧
    private $_curr_frame = 0;
    //12000帧时 强制结束战斗
    private $_handle_end = false;
    //我方是否胜利
    private $_isWin = false;

    public function __construct($type, $myHeroes, $targetHeroes, $uid = 0)
    {
        $this->_type = $type;
        switch ($this->_type) {
            case Battle::TYPE_ARENA:
                $this->setInBattleHeroes($myHeroes, BattleHelper::BATTLE_TEAM_US);
                $this->setInBattleHeroes($targetHeroes, BattleHelper::BATTLE_TEAM_ENEMY);
                $this->_enemy_uid = $uid;
                break;
        }

        $this->sort = function ($a, $b) {
            if ($a instanceof HeroInBattle && $b instanceof HeroInBattle) {
                if ($a->getAttrValueByName('SPEED') == $b->getAttrValueByName('SPEED')) {
                    if ($a->getBelongTeam() == BattleHelper::BATTLE_TEAM_US && $b->getBelongTeam() == BattleHelper::BATTLE_TEAM_ENEMY) {
                        return 1;
                    } elseif ($a->getBelongTeam() == BattleHelper::BATTLE_TEAM_ENEMY && $b->getBelongTeam() == BattleHelper::BATTLE_TEAM_US) {
                        return -1;
                    } else {
                        return 0;
                    }
                }
                return ($a->getAttrValueByName('SPEED') > $b->getAttrValueByName('SPEED')) ? -1 : 1;
            }
            return 0;
        };
    }

    //-------------------------------------------战斗进行过程---------------------------------------------------------//
    /**
     * 设置双方英雄阵容
     */
    private function setInBattleHeroes($heroes, $teamBelong)
    {
        foreach ($heroes as $k => $v) {
            if ($v != null && $v['BASE_ID'] > 0) {
                $heroInBattle = new HeroInBattle($v, $teamBelong);
                $heroInBattle->setHeroBattleId(count($this->_hero_list));
                $this->_hero_list[count($this->_hero_list)] = $heroInBattle;
                if ($k == GlobalEngine::PET_DBID) {
                    $this->getTeamByBelong($teamBelong)->setPet($heroInBattle);
                    $this->getTeamByBelong($teamBelong)->setPetAttendBattle($this->_type != Battle::TYPE_ARENA);
                } else {
                    $this->getTeamByBelong($teamBelong)->setHerosInBattle($heroInBattle);
                }
            }
        }
    }

    /**
     * 获取队伍
     */
    private function getTeamByBelong($teamBelong)
    {
        if ($teamBelong == BattleHelper::BATTLE_TEAM_US) {
            if ($this->_my_team == null) {
                $this->_my_team = new TeamInBattle(GlobalEngine::$loginUser);
            }
            return $this->_my_team;
        } elseif ($teamBelong == BattleHelper::BATTLE_TEAM_ENEMY) {
            if ($this->_enemy_team == null) {
                $this->_enemy_team = new TeamInBattle(GlobalEngine::$loginUser);
            }
            return $this->_enemy_team;
        }
    }

    /**
     * 战斗准备 创建对战双方阵容  刷新战斗中所有英雄状态
     */
    private function prepareBattle()
    {
        $this->_hero_list = $this->calcHeroSpeed($this->_hero_list);
        $this->_actor_index = 0;
        $this->_alive_number = $this->_my_team->getAliveNum() + $this->_enemy_team->getAliveNum();
    }

    /**
     * 战斗主循环
     */
    public function startBattle()
    {
        $this->prepareBattle();
        // 暂时没有宠物
        // if ($this->_type == Battle::TYPE_ARENA) {
        //     $this->petReleaseBuff(BattleHelper::BATTLE_TEAM_US);
        //     $this->petReleaseBuff(BattleHelper::BATTLE_TEAM_ENEMY);
        // }
        //双方阵容中还有英雄存活 否则战斗结束
        while (!$this->_handle_end && $this->_alive_number > 0 && $this->_my_team->getAliveNum() > 0 && $this->_enemy_team->getAliveNum() > 0) {
            
            $this->runBattle();
        }
        $this->_is_end = true;
        $this->finishBattle();
    }

    /**
     * 宠物释放技能
     */
    private function petReleaseBuff($teamBelong)
    {
        $pet = $this->getTeamByBelong($teamBelong)->getPet();
        foreach ($pet->getSkill(BattleHelper::BATTLE_SKILL_TYPE_PASSIVE) as $id => $skill) {
            if ($skill instanceof PassiveSkill && $pet instanceof HeroInBattle) {

            }
            foreach ($this->getPetAim($teamBelong, $skill->effectTarget()) as $key => $aim) {
                $this->calcBuff($skill->getBuff(), $pet, $aim, BattleHelper::BATTLE_SKILL_TYPE_PASSIVE);
            }
        }
    }

    /**
     * 战斗开始
     */
    private function runBattle()
    {
        if (empty($this->_hero_list)) {
            throwGameException("战斗英雄列表为空");
        }
        $actor = $this->getActor();            //获取本次行动者
        
        $this->excuteAction($actor);
        $this->_actor_index++;
    }

    //-------------------------------------------计算行动者-----------------------------------------------------------//
    /**
     * 行动英雄
     * return int
     */
    private function getActor()
    {
        if ($this->_actor_index >= $this->_alive_number) {
            $this->getBattleRoundResult();
        }

        if ($this->_signal_speed_change == true) {
            $this->resetHeroCauseSpeed();
            $this->_signal_speed_change = false;
        }
        $hero = $this->_hero_list[$this->_actor_index];

        // if ($this->_actor_index > $this->_alive_number) {
        //     throwGameException("行动者序列出错");
        // }
        if ($hero->isStautsDizzy() > 0 || $hero->isStautsSleep() > 0) {    //英雄状态不出手
            $this->_actor_index++;
            return $this->getActor();
        }
        return $hero;
    }

    /**
     * @see 计算出手顺序
     * return array
     */
    private function calcHeroSpeed($list)
    {
        usort($list, $this->sort);
        return $list;
    }

    /**
     * @see 修改部分英雄Speed
     * 重新排列  actionIndex 跟 存活人数 间的序列   已经行动跟死亡者不予处理
     */
    private function resetHeroCauseSpeed()
    {
        $length = $this->_alive_number - $this->_actor_index;
        $resetList = array_slice($this->_hero_list, $this->_actor_index, $length);    //重新排序部分
        usort(array_slice($this->_hero_list, $this->_actor_index, $length), $this->sort);
        array_splice($this->_hero_list, $this->_actor_index, $length, $resetList);
    }

    /**
     * @see 英雄死亡
     * 死亡的人 速度置为0  并插入到队列最后
     */
    private function heroDead($hero)
    {
        $this->_alive_number--;
        $this->_signal_speed_change = true;
        $offset = array_search($hero, $this->_hero_list);
        if ($offset) {
            array_push($this->_hero_list, $this->_hero_list[$offset]);
            array_splice($this->_hero_list, $offset, 1);
        }
    }

    //-------------------------------------------计算目标者-----------------------------------------------------------//
    /**
     * @see  获取目标
     * 技能， 施法英雄， 是否是buf
     * return array
     */
    private function getAim($skill, $actor, $isBuff = false, $buffId = 1)
    {
        $result = array();
        $actionTeam = $this->getTeamByBelong($actor->getBelongTeam());
        $inactionTeam = $this->getTeamByBelong(!$actor->getBelongTeam());
        if ($isBuff) {
            switch ($skill->buffTarget($buffId)) {
                case BattleHelper::BUFF_TARGET_NUM_AIM:
                    //与战斗目标相同，已在外部处理
                    return $result;

                case BattleHelper::BUFF_TARGET_NUM_CASTER:
                    $hero = $actor;
                    array_push($result, $hero);
//                    $result[$hero->getHeroBattleId()] = $hero;
                    return $result;

                case BattleHelper::BUFF_TARGET_NUM_ROLLENEMY1:
                    return $inactionTeam->getRandomAim(1);

                case BattleHelper::BUFF_TARGET_NUM_ROLLENEMY2:
                    return $inactionTeam->getRandomAim(2);

                case BattleHelper::BUFF_TARGET_NUM_ROLLENEMY3:
                    return $inactionTeam->getRandomAim(3);

                case BattleHelper::BUFF_TARGET_NUM_ROLLENEMY4:
                    return $inactionTeam->getRandomAim(4);

                case BattleHelper::BUFF_TARGET_NUM_ROLLUS1:
                    return $actionTeam->getRandomAim(1);

                case BattleHelper::BUFF_TARGET_NUM_ROLLUS2:
                    return $actionTeam->getRandomAim(2);

                case BattleHelper::BUFF_TARGET_NUM_ROLLUS3:
                    return $actionTeam->getRandomAim(3);

                case BattleHelper::BUFF_TARGET_NUM_ROLLUS4:
                    return $actionTeam->getRandomAim(4);

                case BattleHelper::BUFF_TARGET_NUM_TEAMENEMY:
                    return $inactionTeam->getAliveHerosInBattle();

                case BattleHelper::BUFF_TARGET_NUM_TEAMUS:
                    return $actionTeam->getAliveHerosInBattle();
            }
        } else {
            switch ($skill->targetNum()) {
                case BattleHelper::SKILL_TARGET_NUM_DEFAULT:
                    if ($skill->effectType() == BattleHelper::SKILL_EFFECT_HPRESUME || $skill->effectType() == BattleHelper::SKILL_EFFECT_MPRESUME) {
                        array_push($actor);
                    } else {
                        $result = $inactionTeam->getHatredAim();
                    }
                    return $result;

                case BattleHelper::SKILL_TARGET_NUM_TEAMALL:
                    if ($skill->effectType() == BattleHelper::SKILL_EFFECT_HPRESUME || $skill->effectType() == BattleHelper::SKILL_EFFECT_MPRESUME) {
                        $result = $actionTeam->getAliveHerosInBattle();
                    } else {
                        $result = $inactionTeam->getAliveHerosInBattle();
                    }
                    return $result;

                case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY1:
                    return $inactionTeam->getRandomAim(1);

                case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY2:
                    return $inactionTeam->getRandomAim(2);

                case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY3:
                    return $inactionTeam->getRandomAim(3);

                case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY4:
                    return $inactionTeam->getRandomAim(4);

                case BattleHelper::SKILL_TARGET_NUM_ROLLUS1:
                    return $actionTeam->getRandomAim(1);

                case BattleHelper::SKILL_TARGET_NUM_ROLLUS2:
                    return $actionTeam->getRandomAim(2);

                case BattleHelper::SKILL_TARGET_NUM_ROLLUS3:
                    return $actionTeam->getRandomAim(3);

                case BattleHelper::SKILL_TARGET_NUM_ROLLUS4:
                    return $actionTeam->getRandomAim(4);
            }
        }
    }

    /**
     * 获取宠物技能目标
     */
    private function getPetAim($isMyTeam, $type)
    {
        return $this->getTeamByBelong($isMyTeam)->getAliveHeroesByType($type);
    }

    /**
     * 处理Action
     */
    private function excuteAction($actor)
    {
        $isBurstattack = $actor->hasReleaseSkill();
        $skill = $actor->getSkill($isBurstattack);
        $aims = $this->getAim($skill, $actor);
        $actions = $actor->getActionBySkill($isBurstattack);
        $weightIndex = 0;
        foreach ($actions as $frame => $events) {
            $this->_curr_frame = $this->_total_frame + $frame;
            foreach ($events as $index => $action) {
                if ($action[0] == 1) {
                    foreach ($aims as $id => $aim) {
                        if ($aim->isDead() == true) {
                            continue;
                        }
                        switch ($skill->effectType()) {
                            case BattleHelper::SKILL_EFFECT_HPRESUME:
                                //治疗值 取反 加在生命里
                                $heal = -($skill->effectValue() * $aim->getCurrentResume());
                                $aim->hurtHeal($heal);
                                $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_HEAL, $isBurstattack, $heal));
                                break;

                            case BattleHelper::SKILL_EFFECT_MPRESUME:
                                $mpResum = $skill->effectValue();
                                $aim->resumeMp($mpResum);
                                $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_ADDMP, $isBurstattack, $mpResum));
                                break;

                            case BattleHelper::SKILL_EFFECT_DEMAGE:
                                $weightList = $skill->batterWeight();
                                if ($weightList[0] == 100 && $weightIndex > 0) {
                                    throwGameException("WEIGHTINDEX ERROR, HERO" . $actor->getBaseId() . $isBurstattack, StatusCode::GAME_ERROR_IN_BATTLE);
                                } elseif ($weightIndex >= count($skill->batterWeight())) {
                                    print_r($skill->batterWeight());
                                    throwGameException("WEIGHTINDEX ERROR, HERO" . $actor->getBaseId() . $isBurstattack . "ActionList" . $weightIndex . "Table" . count($skill->batterWeight()), StatusCode::GAME_ERROR_IN_BATTLE);
                                } else {
                                    $damage = $this->calcValue($skill, $actor, $aim, $weightList[$weightIndex] / 100, $isBurstattack);
                                    if ($damage > 0) {
                                        if ($damage > $aim->getCurrentHp()) {
                                            $actor->setTotalDamage($aim->getCurrentHp());
                                        } else {
                                            $actor->setTotalDamage($damage);
                                        }
                                    }
                                    $aim->hurtHeal($damage);
                                    $isDrop = $this->calcDrop($skill->targetNum());
                                    if ($isDrop) {
                                        $this->heartBelong($actor->getBelongTeam());
                                        $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_DROP_HEART));
                                    }
                                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_DAMAGE, $isBurstattack, $damage));
                                }
                                break;
                        }
                        if ($aim->isDead() == true) {
                            $this->heroDead($aim);
                            $this->pushActionSeq(ActionFactory::createActions($aim->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_DEATH));
                        }
                    }
                    if ($skill->effectType() == BattleHelper::SKILL_EFFECT_DEMAGE) {
                        $weightIndex++;
                    }
                } elseif ($action[0] == 6) {
                    if ($skill->getBuff() != null) {
                        if ($skill->buffTarget(1) == BattleHelper::BUFF_TARGET_NUM_AIM) {
                            $bufAims = $this->getAim($skill, $actor, true);
                        } else {
                            $bufAims = $this->getAim($skill, $actor, true, 1);
                        }
                        if (count($bufAims) > 0) {
                            foreach ($bufAims as $id => $aim) {
                                if ($aim->isDead() == true) {
                                    continue;
                                }
                                $this->calcBuff($skill->getBuff(), $actor, $aim, $isBurstattack);
                            }
                        }
                    }
                    if ($skill->getBuff2() != null) {
                        if ($skill->buffTarget(2) == BattleHelper::BUFF_TARGET_NUM_AIM) {
                            $bufAims = $this->getAim($skill, $actor, true);
                        } else {
                            $bufAims = $this->getAim($skill, $actor, true, 2);
                        }
                        if (count($bufAims) > 0) {
                            foreach ($bufAims as $id => $aim) {
                                if ($aim->isDead() == true) {
                                    continue;
                                }
                                $this->calcBuff($skill->getBuff2(), $actor, $aim, $isBurstattack);
                            }
                        }
                    }
                } elseif ($action[0] == 99) {
                    $this->_total_frame += $frame;
                    if ($this->_total_frame >= 12000) {
                        $this->_handle_end = true;
                    }
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_END, $isBurstattack));
                } else {
                    foreach ($aims as $id => $aim) {
                        if ($aim->isDead() == true) {
                            continue;
                        }
                        $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), $action[0], $isBurstattack));
                    }
                }
            }
        }
        if ($isBurstattack == BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK) {
            $actor->releaseSkillCD();
        }
    }

    //-------------------------------------------计算伤害-------------------------------------------------------------//
    /**
     * @see 计算攻击伤害
     */
    private function calcValue($skill, $actor, $aim, $rate = 1, $isBurstattack)
    {
        $miss = false;
        if ($this->getTeamByBelong($aim->getBelongTeam())->getIsHitMiss()) {
            if (rand(0, 1000) > 1000 * ($actor->getAttrValueByName('HIT_POINT') - $aim->getAttrValueByName('SIDESTEP'))) {
                $miss = true;
                $damage = 0;
                $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_MISS, $isBurstattack));
            }
        }
        if (!$miss) {
            if ($this->getTeamByBelong($aim->getBelongTeam())->getIsIsCrit()) {
                if (rand(0, 1000) < 1000 * ($actor->getAttrValueByName('CRITICAL_RATE') - $aim->getAttrValueByName('AG_CRITICAL'))) {
                    $crit = true;
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_CRIT, $isBurstattack));
                } else {
                    $crit = false;
                }
            }
            $base = max($actor->getAttrValueByName('ATK_POINT') * $skill->effectValue() - $aim->getAttrValueByName('DEF_POINT'), 0.05 * $actor->getAttrValueByName('ATK_POINT') * $skill->effectValue());
            $damage = $base * ($crit ? (1.5 + $actor->getAttrValueByName('CRITICAL_DMG_ADD')) : 1) * (1 - $aim->getAttrValueByName('DMG_REDUCE')) + $actor->getAttrValueByName('ABS_DMG') * $rate;
        }
        return $damage;
    }

    /**
     * 计算buff
     */
    private function calcBuff($buff, $actor, $aim, $isBurstattack)
    {
        if ($aim != null) {
            $aim->buff($buff, $actor, $this->_round);
        }
        if ($buff->buffType() == BattleHelper::BUFF_TYPE_BUFF && ($buff->effectType() == BattleHelper::BUFF_EFFECT_SPEED || $buff->effectType() == BattleHelper::BUFF_EFFECT_SPEED)) {
            $offset = array_search($aim, $this->_hero_list);
            if ($offset < $this->_alive_number && $offset > $this->_actor_index) {
                $this->_signal_speed_change = true;
            }
        }
        if ($aim->_least_buff_round == 0) {
            $aim->_least_buff_round = intval($buff->rounds());
        } else {
            $aim->_least_buff_round = min($aim->_least_buff_round, intval($buff->rounds()));
        }
        //记录buff
        if ($buff->buffType() == BattleHelper::BUFF_TYPE_BUFF) {
            $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_BUFF, $isBurstattack, $buff->id(), $buff->rounds()));
        } elseif ($buff->buffType() == BattleHelper::BUFF_TYPE_STATUS) {
            switch ($buff->effectType()) {
                case BattleHelper::STATUS_TYPE_SILENT:
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_SILENT, $isBurstattack, $buff->id(), $buff->rounds()));
                    break;

                case BattleHelper::STATUS_TYPE_CRAZY:
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_CRAZY, $isBurstattack, $buff->id(), $buff->rounds()));
                    break;

                case BattleHelper::STATUS_TYPE_HIDEN:
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_HIDEN, $isBurstattack, $buff->id(), $buff->rounds()));
                    break;

                case BattleHelper::STATUS_TYPE_DIZZY:
                    $this->pushActionSeq(ActionFactory::createActions($actor->getHeroBattleId(), $this->_curr_frame, $aim->getHeroBattleId(), ActionFactory::ACTION_DIZZY, $isBurstattack, $buff->id(), $buff->rounds()));
                    break;
            }
        }
    }

    //-------------------------------------------计算一次攻击---------------------------------------------------------//
    /**
     * @see 计算掉落
     */
    private function calcDrop($target)
    {
        $rollNum = rand(0, 1000);
        switch ($target) {
            case BattleHelper::SKILL_TARGET_NUM_TEAMALL:
                if ($rollNum < 100) {
                    return true;
                }

            case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY4:
                if ($rollNum < 100) {
                    return true;
                }

            case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY3:
                if ($rollNum < 133) {
                    return true;
                }
                break;

            case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY2:
                if ($rollNum < 200) {
                    return true;
                }

            case BattleHelper::SKILL_TARGET_NUM_ROLLENEMY1:
                if ($rollNum < 400) {
                    return true;
                }

            case BattleHelper::SKILL_TARGET_NUM_DEFAULT:
                if ($rollNum < 400) {
                    return true;
                }
        }
        return false;
    }

    /*
     * 红心归属
     */
    private function heartBelong($isMine)
    {
        $this->getTeamByBelong($isMine)->_heartDrop++;
    }

    //-------------------------------------------计算回合结果---------------------------------------------------------//
    /**
     * @see 获取回合结果
     */
    private function getBattleRoundResult()
    {
        //重置actor index
        $this->_actor_index = 0;
        //分配drop
        while ($this->_my_team->_heartDrop > 0) {
            if ($hero = $this->_my_team->randomHeart()) {
                $heal = -0.1 * $hero->getAttrValueByName('RECU_POINT');
                $hero->hurtHeal($heal);
                $this->pushActionSeq(ActionFactory::createActions($hero->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_GET_HEART, "", $heal));
            }
        }

        while ($this->_enemy_team->_heartDrop > 0) {
            if ($hero = $this->_enemy_team->randomHeart()) {
                $heal = -0.1 * $hero->getAttrValueByName('RECU_POINT');
                $hero->hurtHeal($heal);
                $this->pushActionSeq(ActionFactory::createActions($hero->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_GET_HEART, "", $heal));
            }
        }

        //计算Buf round 回复怒气
        foreach ($this->_hero_list as $k => $v) {

            if ($v->isDead() == true) {
                continue;
            }
            //回复怒气
            $v->resumeMp(5);
            //Buff 与 状态
            if ($v->isStatusNormal() != 1) {
                $v->updateStatus();
                $this->pushActionSeq(ActionFactory::createActions($v->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_STATUS_NORMAL));
            }
            if ($v->_least_buff_round > 0) {
                $v->lastBuff();
                if ($v->isContinuous()) {
                    $this->pushActionSeq(ActionFactory::createActions($v->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_BUFF_EFFECT));
                }
                if ($v->_least_buff_round == 0) {
                    $next_buff_round = 0;
                    foreach ($v->getBuffs() as $k2 => $v2) {
                        if ($v2->_tillRounds == $this->_round) {
                            if ($v2->buffType() == BattleHelper::BUFF_TYPE_BUFF) {
                                $v->buffRoundOver($v2);
                                $this->pushActionSeq(ActionFactory::createActions($v->getHeroBattleId(), $this->_curr_frame, "", ActionFactory::ACTION_BUFF_END));
                                if ($v2->effectType() == BattleHelper::BUFF_EFFECT_SPEED || $v2->effectType() == BattleHelper::BUFF_EFFECT_SPEED_PERCENT) {
                                    $this->_signal_speed_change = true;
                                }
                            }
                        }
                        if ($next_buff_round == 0) {
                            $next_buff_round = $v2->_tillRounds - $this->_round;
                        } else {
                            $next_buff_round = min($next_buff_round, $v2->_tillRounds - $this->_round);
                        }
                    }
                    $v->_least_buff_round = $next_buff_round;
                } else {
                    $v->_least_buff_round--;
                }
            }
        }
        $this->_round++;
    }

    /**
     * @see 战斗结束
     */
    private function finishBattle()
    {
        print_r("回合数" . $this->_round . "<br />");
        foreach ($this->_hero_list as $k => $v) {
            $reportTip = array("Id" => $v->getHeroBattleId(), "damage" => $v->getTotalDamage());
            array_push($this->_report, $reportTip);
            print_r("英雄" . $v->getHeroBattleId() . "造成" . $v->getTotalDamage() . "<br/>");
        }
        if ($this->_my_team->getAliveNum() == 0 && $this->_enemy_team->getAliveNum() != 0) {
            $this->_isWin = false;
            print 'battle finished' . '失败';
        } elseif ($this->_my_team->getAliveNum() != 0 && $this->_enemy_team->getAliveNum() == 0) {
            $this->_isWin = true;
            print 'battle finished' . '胜利';
        }
    }

    //-------------------------------------------返回客户端项---------------------------------------------------------//
    /**
     * 战斗前阵容数据
     */
    public function getTeamInfo()
    {
        $result = array();
        foreach ($this->_hero_list as $k => $v) {
            $result[$v->getHeroBattleId()] = array(
                "ID" => $v->getBaseId(),
                "HP" => $v->getCurrentHpMax(),
                "MP" => $v->getInitMp(),
                "team" => $v->getBelongTeam(),
                "damage" => $v->getTotalDamage()
            );
        }
        return $result;
    }

    /**
     * @see 插入动作序列
     */
    private function pushActionSeq($action)
    {
        array_push($this->_action_sequence, $action);
    }

    /**
     * @return array
     */
    public function getActionSequence()
    {
        return $this->_action_sequence;
    }

    /**
     * @return array
     */
    public function getReport()
    {
        return $this->_report;
    }

    /**
     * @return mixed
     */
    public function getIsWin()
    {
        return $this->_isWin;
    }

    //-------------------------------------------其他-----------------------------------------------------------------//

    /**
     * @see 加载数据
     */
    public function loadData()
    {
        $PlayerInfo = new PlayerInfo(GlobalEngine::$loginUser);
        $player_info = $PlayerInfo->get();

        $UserCard = new UserCard(GlobalEngine::$loginUser);
        $user_card = $UserCard->get();

        $UserEquipment = new UserEquipment(GlobalEngine::$loginUser);
        $user_equipment = $UserEquipment->get();

        foreach ($user_equipment as $equipment) {
            if ($equipment['ownerId'] > 0) {
                $ownerId = $equipment['ownerId'];
                if (isset($user_card[$ownerId])) {
                    if (empty($user_card[$ownerId]['equipments'])) {
                        $user_card[$ownerId]['equipments'] = array();
                    }
                    $user_card[$ownerId]['equipments'][] = $equipment;
                }
            }
        }

        $UserFormation = new UserFormation(GlobalEngine::$loginUser);
        $user_formation = $UserFormation->get();

        $formation = array();
        $leader_skill = array();

        foreach ($user_formation['data'][$user_formation['currId']] as $pos => $dbid) {
            if ($dbid > 0) {
                $formation[$pos] = $user_card[$dbid];
                if ($pos == 0) {
                    $cardDef = GameDef::getGameDefById(GameDef::CARD_BASE, $user_card[$dbid]['cardId']);
                    if ($cardDef['leaderSkillId'] > 0) {
                        $leader_skill[] = new LeaderSkill($cardDef['leaderSkillId']);
                    }

                }
            }
        }
        self::$mine_data['formation'] = $formation;
        self::$mine_data['player_info'] = $player_info;
        self::$mine_data['leader_skill'] = $leader_skill;

        if ($this->_type == Battle::TYPE_MISSION) {
            //pve 战斗
            $UserMission = new UserMission(GlobalEngine::$loginUser);
            $user_mission = $UserMission->get();
            if (empty($user_mission['currMission'])) {
                throwGameException('not in mission.', StatusCode::SERVICES_EXEC_ERROR, __METHOD__);
            }

            foreach ($user_mission['currMission']['monsters'] as $subMission) {
                $monsterIds = array();
                foreach ($subMission['monsters'] as $tmp) {
                    $monsterIds[] = $tmp['id'];
                }
                self::$target_data[$subMission['id']] = $monsterIds;
            }

            //助战好友id
            if ($user_mission['currMission']['fuid'] > 0) {
                $fuid = $user_mission['currMission']['fuid'];
                $CachedData = new CachedData(GlobalEngine::$loginUser);
                $currHelpFriends = $CachedData->getDataByName('currHelpFriends');

                $friend = null;
                $isFriend = false;
                if (!empty($currHelpFriends['friends'])) {
                    foreach ($currHelpFriends['friends'] as $fuser) {
                        if ($fuser['uid'] == $fuid) {
                            $friend = $fuser;
                            $isFriend = true;
                            break;
                        }
                    }
                }
                if (!$friend && !empty($currHelpFriends['random'])) {
                    foreach ($currHelpFriends['random'] as $ruser) {
                        if ($ruser['uid'] == $fuid) {
                            $friend = $ruser;
                            break;
                        }
                    }
                }
                if ($friend) {
                    $friend_card = $friend['user_card'][$friend['player_info']['currLeader']];
                    $friend_card['equipments'] = array();
                    if (!empty($friend['user_equipment'])) {
                        foreach ($friend['user_equipment'] as $equipment) {
                            $friend_card['equipments'][] = $equipment;
                        }
                    }
                    $formation[6] = $friend_card;
                    self::$mine_data['formation'] = $formation;

                    if ($isFriend) {
                        $cardDef = GameDef::getGameDefById(GameDef::CARD_BASE, $friend_card['cardId']);
                        if ($cardDef['leaderSkillId'] > 0) {
                            self::$mine_data['leader_skill'][] = new LeaderSkill($cardDef['leaderSkillId']);
                        }
                    }
                }
            }
        } elseif ($this->_type == Battle::TYPE_ARENA) {
            //pvp 战斗
            $CachedData = new CachedData(GlobalEngine::$loginUser);
            $myArena = $CachedData->getDataByName('myArena');

            if (empty($myArena['currUser']) || $myArena['currUser']['uid'] <= 0) {
                throwGameException('not in arena battle.', StatusCode::SERVICES_EXEC_ERROR, __METHOD__);
            }

            $target_player_info = $myArena['currUser']['player_info'];

            $target_card = $myArena['currUser']['user_card'];
            $target_equipment = $myArena['currUser']['user_equipment'];

            foreach ($target_card as &$card) {
                $card['equipments'] = array();
                if (!empty($card['equipment'])) {
                    foreach ($card['equipment'] as $eid) {
                        $card['equipments'][] = $target_equipment[$eid];
                    }
                }
            }

            $target_formation = array();
            $target_leader_skill = array();
            foreach ($myArena['currUser']['user_formation']['data'][$myArena['currUser']['user_formation']['currId']] as $pos => $dbid) {
                if ($dbid > 0) {
                    $target_formation[$pos] = $target_card[$dbid];
                    if ($pos == 0) {
                        $cardDef = GameDef::getGameDefById(GameDef::CARD_BASE, $target_card[$dbid]['cardId']);
                        if ($cardDef['leaderSkillId'] > 0) {
                            $target_leader_skill[] = new LeaderSkill($cardDef['leaderSkillId']);
                        }
                    }
                }
            }

            self::$target_data['formation'] = $target_formation;
            self::$target_data['player_info'] = $target_player_info;
            self::$target_data['leader_skill'] = $target_leader_skill;
        }
    }

    /**
     * @return array
     */
    public static function getMineData()
    {
        return self::$mine_data;
    }

    /**
     * @return array
     */
    public static function getTargetData()
    {
        return self::$target_data;
    }

}

?>