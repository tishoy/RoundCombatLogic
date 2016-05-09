<?php
/**
 * Created by Tishoy.
 * Date: 2016/3/3
 * Time: 10:26
 */
class Hero {
    //英雄卡牌ID
    private $_card_Id = 0;
    // 英雄战场ID
    private $_hero_battleId = 0;    //英雄战斗时ID
    //属于的队伍
    private $_belong_team = BattleHelper::BATTLE_TEAM_US;
    //英雄是否阵亡
    private $_isDead = false;
    //英雄当前状态 眩晕  沉默
    private $_status = array();
    //存储英雄当前所拥有的所有buff
    private $_buffs = array();
    //存储影响该属性的Buff所影响的值
    private $_buff_value = array();
    //Buff增加百分比
    private $_buff_percent = array();
    //最近到期Buff   还有几回合
    public $_least_buff_round = 0;
    //是否释放技能
    private $_release_skill = false;
    //属性
    private $_attrs = array();
    //当前生命
    private $_current_hp = 0;
    //当前怒气
    private $_current_mp = 0;
    //攻击技能
    private $_skill;
    //大招
    private $_bigSkill;
    //攻击序列帧
    private $_action_attack;
    //大招序列帧
    private $_action_burstattack;
    //怒气满值
    private $_mpMax;
    //初始怒气
    private $_init_mp;
    //持续掉血加血
    private $_continuous = false;
    //持续掉血值
    private $_lossHP;
    //造成伤害
    private $_total_damage = 0;
    //英雄类型  凭此type判断是否受宠物buff影响
    private $_hero_type = 0;
    //宠物才有的光环类技能
    private $_passive_skills = array();

    public function __construct($data, $isPlayerTeam){
        $this->_card_Id = intval($data['BASE_ID']);
        $this->_attrs = $data;
        $this->_belong_team = $isPlayerTeam;

        $this->loadData();
    }

    //----------------------------------------------初始化部分---------------------------------------------------------
    /**
     * 初始化战斗数值
     */
    private function loadData(){
        //TODO  获取英雄配置

        $this->_current_hp = $this->_attrs['HEALTH_POINT'];
        $this->_mpMax = $cardDef['moraleMax'];
        $this->_init_mp = $cardDef['moraleInit'];
        $this->_current_mp = $this->_init_mp;

        $this->resetBuffs();

        $this->_status = array(
            BattleHelper::STATUS_TYPE_NORMAL => 1,
            BattleHelper::STATUS_TYPE_SILENT => 0,
            BattleHelper::STATUS_TYPE_SLEEP => 0,
            BattleHelper::STATUS_TYPE_DIZZY => 0
        );

        $this->_skill = new AttackSkill($this->_attrs['SKILL'], $this->_hero_battleId);
        //技能等级
        $this->_bigSkill = new AttackSkill($this->_attrs['BIG_SKILL'], $this->_hero_battleId);

        $this->_bigSkillLevel = intval($this->_bigSkill->level());

        $this->_resId = $cardDef['resID2'];

        $this->_hero_type = $cardDef['HeroType'];

        if ($this->isPet() == true) {
            for($i=1; $i<=5; $i++){
                if(isset($this->_attrs['PASSIVE_SKILLS']['skill' . $i])){
                    $passiveSkillDef = GameDef::getGameDefById(GameDef::SKILL_PET_PASSIVE, $this->_attrs['PASSIVE_SKILLS']['skill' . $i]);
                    $this->_passiveSkillLevel += intval($passiveSkillDef['color']); //等级尚不知作用
                    $passiveBuff = new PassiveSkill($passiveSkillDef["id"], $this->getHeroBattleId());
                    array_push($this->_passive_skills, $passiveBuff);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getHeroBattleId()
    {
        return $this->_hero_battleId;
    }

    /**
     * @param int $hero_battleId
     */
    public function setHeroBattleId($hero_battleId)
    {
        $this->_hero_battleId = $hero_battleId;
    }

    /**
     * @return int
     */
    public function getCurrentHp()
    {
        return $this->_current_hp;
    }

    /**
     * @return int
     */
    public function getCurrentHpMax()
    {
        return $this->_attrs['HEALTH_POINT'];
    }

    /**
     * 获取英雄ID
     */
    public function getBaseId() {
        return $this->_attrs["BASE_ID"];
    }

    /**
     * @param $name 属性名
     * @return mixed
     */
    public function getAttrValueByName($name) {
        return ($this->_attrs[$name] + $this->_buff_value[$name]) * (1 + $this->_buff_percent[$name]);
    }

    /**
     * @return int
     */
    public function getBelongTeam()
    {
        return $this->_belong_team;
    }

    /**
     * @return array
     */
    public function getBuffs()
    {
        return $this->_buffs;
    }

    /**
     * @return array
     */
    public function getActionBySkill($type)
    {
        if ($type == BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK) {
            $this->_action_burstattack = GlobalEngine::getAction($this->_resId, BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK);
            return $this->_action_burstattack;
        }elseif ($type == BattleHelper::BATTLE_SKILL_TYPE_ATTACK) {
            $this->_action_attack = GlobalEngine::getAction($this->_resId, BattleHelper::BATTLE_SKILL_TYPE_ATTACK);
            return $this->_action_attack;
        }
    }

    /**
     * @return array
     */
    public function getSkill($type)
    {
        if ($type == BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK) {
            return $this->_bigSkill;
        }
        elseif ($type == BattleHelper::BATTLE_SKILL_TYPE_ATTACK) {
            return $this->_skill;
        }
        elseif ($type == BattleHelper::BATTLE_SKILL_TYPE_PASSIVE) {
            return $this->_passive_skills;
        }
    }

    /**
     * @return mixed
     */
    public function getInitMp()
    {
        return $this->_init_mp;
    }

    /**
     * @return int
     */
    public function getHeroType()
    {
        return $this->_hero_type;
    }

    /**
     * @return boolean
     */
    public function isPet()
    {
        if (intval($this->_attrs["BASE_ID"]) > 4000 && intval($this->_attrs["BASE_ID"]) <= 4500) {
            return true;
        }
        return false;
    }
    //----------------------------------------------战斗计算部分-------------------------------------------------------
    /**
     * 受到伤害
     */
    public function hurtHeal($damage) {
        if ($this->_current_hp > 0) {
            $this->_current_hp = $this->_current_hp - $damage;
            if ($this->_current_hp <= 0) {
                $this->_isDead = true;
                $this->_attrs['SPEED'] = 0;
                $this->getAttrValueByName('SPEED');
            }
        }
        if ($this->_current_hp > $this->_attrs['HEALTH_POINT']) {
            $this->_current_hp = $this->_attrs['HEALTH_POINT'];
        }
    }

    /**
     * 持续回血 掉血Buff 作用
     */
    public function lastBuff() {
        if ($this->_continuous) {
            $this->hurtHeal($this->_lossHP);
        }
    }

    /**
     * 回复魔力
     */
    public function resumeMp($mp) {
        if ($this->_current_mp < $this->_mpMax) {
            $this->_current_mp += $mp;
        }
        if ($this->_current_mp >= $this->_mpMax) {
            $this->_current_mp = $this->_mpMax;
            if ($this->_release_skill == false) {
                $this->_release_skill = true;
            }
        }
    }

    /**
     * Buff
     */
    public function buff($buff, $caster, $round) {
        if ($buff instanceof Buff) {
            switch ($buff->buffType()) {
                case BattleHelper::BUFF_TYPE_BUFF:
                    //不同施法者 同一种buff BUFF 数值上叠加
                    //相同Buff
                    $hasBuff = false;
                    foreach ($this->_buffs as $k => $v) {
                        if ($v instanceof Buff && $v->getCaster() == $caster->getHeroBattleId() && $v->id() == $buff->id()) {
                            //说明上过此Buff
                            $hasBuff = true;
                            $buff = $v;
                            break;
                        }
                        elseif ($v instanceof Buff && $v->getCaster() != $caster->getHeroBattleId() && $v->id() == $buff->id()) {
                            //说明上过同类Buff
                        }
                    }
                    if ($hasBuff == false) {
                        array_push($this->_buffs, $buff);
                        $this->effectBuff($buff);
                    }
                    //更新Buff轮数
                    $buff->_tillRounds = $buff->rounds() + $round - 1;
                    break;

                case BattleHelper::BUFF_TYPE_DEBUFF:
                    //消除BUFF，暂时没有
                    break;

                case BattleHelper::BUFF_TYPE_STATUS:
                    //相同状态 不论释放者 时间不叠加  直接替换
                    if ($this->_status[$buff->effectType()] == 0) {
                        if ($buff->effectType() == BattleHelper::STATUS_TYPE_NORMAL) {
                            $this->setStatusNormal();
                        }
                        else {
                            $this->_status[$buff->effectType()] = $buff->rounds() - 1;
                            $this->_status[BattleHelper::STATUS_TYPE_NORMAL] = 0;
                        }
                    }
                    break;
            }
        }
    }

    /**
     * 实现buff效果
     */
    public function effectBuff($buff) {
        if ($buff instanceof Buff) {
            if ($buff->effectType() == BattleHelper::BUFF_EFFECT_DESTATUS) {
                $this->setStatusNormal();
            }
            else {
                $buffValue = BattleHelper::getValueByBuffEffect($buff->buffType());
                $attr = $buffValue['attr'];
                $method = $buffValue['method'];
                switch ($method) {
                    case 'plus':
                        $this->_buff_value[$attr] += $buff->effectValue();
                        break;

                    case 'percent':
                        $this->_buff_percent[$attr] += $buff->effectValue();
                        break;

                    case 'divide100':
                        $this->_buff_value[$attr] += $buff->effectValue() / 100;
                        break;

                    case 'divide1000':
                        $this->_buff_value[$attr] += $buff->effectValue() / 1000;
                        break;

                    case 'last':
                        $this->_continuous = true;
                        $this->_lossHP = $buff->effectValue();
                        break;
                }
            }
        }
    }

    /**
     * 实现状态效果
     */
    public function buffRoundOver($buff) {
        if ($buff instanceof Buff) {
            $offset = array_search($buff, $this->_buffs);
            array_splice($this->_buffs, $offset, 1);
            if ($buff->buffType() == BattleHelper::BUFF_TYPE_BUFF) {
                $this->resetBuffByAttr($buff->effectType());
            }
        }
    }

    /**
     * @return boolean
     */
    public function hasReleaseSkill()
    {
        if ($this->isStautsSilent() != 0) {
            return BattleHelper::BATTLE_SKILL_TYPE_ATTACK;
        }
        if ($this->_mpMax == 999) {
            return BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK;
        }
        if ($this->_release_skill) {
            return BattleHelper::BATTLE_SKILL_TYPE_BURSTATTACK;
        }
        return BattleHelper::BATTLE_SKILL_TYPE_ATTACK;
    }

    /**
     * @param boolean $release_skill    需要在英雄行动完将其置为false
     */
    public function releaseSkillCD()
    {
        $this->_current_mp = 0;
        $this->_release_skill = false;
    }

    /**
     * 是否正常
     */
    public function isStatusNormal() {
        return $this->_status[BattleHelper::STATUS_TYPE_NORMAL];
    }

    /**
     * 设置正常
     */
    public function setStatusNormal() {
        $this->_status[BattleHelper::STATUS_TYPE_NORMAL] = 1;
        $this->_status[BattleHelper::STATUS_TYPE_SILENT] = 0;
        $this->_status[BattleHelper::STATUS_TYPE_SLEEP] = 0;
        $this->_status[BattleHelper::STATUS_TYPE_DIZZY] = 0;
    }

    /**
     * 回合状态刷新
     */
    public function updateStatus() {
        $this->_status[BattleHelper::STATUS_TYPE_SILENT]--;
        $this->_status[BattleHelper::STATUS_TYPE_SLEEP]--;
        $this->_status[BattleHelper::STATUS_TYPE_DIZZY]--;
        if ($this->_status[BattleHelper::STATUS_TYPE_SILENT] <= 0){
            $this->_status[BattleHelper::STATUS_TYPE_SILENT] = 0;
        }
        if ($this->_status[BattleHelper::STATUS_TYPE_SLEEP] <= 0){
            $this->_status[BattleHelper::STATUS_TYPE_SLEEP] = 0;
        }
        if ($this->_status[BattleHelper::STATUS_TYPE_DIZZY] <= 0) {
            $this->_status[BattleHelper::STATUS_TYPE_DIZZY] = 0;
        }
        if ($this->_status[BattleHelper::STATUS_TYPE_SILENT] == 0 &&
            $this->_status[BattleHelper::STATUS_TYPE_SLEEP] == 0 &&
            $this->_status[BattleHelper::STATUS_TYPE_DIZZY] == 0) {
            $this->setStatusNormal();
        }
    }

    /**
     * 是否沉默
     * 返回int 非零代表状态有效
     */
    public function isStautsSilent() {
        return $this->_status[BattleHelper::STATUS_TYPE_SILENT];
    }

    /**
     * 是否沉默
     */
    public function isStautsDizzy() {
        return $this->_status[BattleHelper::STATUS_TYPE_DIZZY];
    }

    /**
     * 是否沉默
     */
    public function isStautsSleep() {
        return $this->_status[BattleHelper::STATUS_TYPE_SLEEP];
    }

    /**
     * 是否有持续恢复  掉血状态    是Buff   不是 状态
     */
    public function isContinuous() {
        return $this->_continuous;
    }

    /**
     * 重置某一Attr的buff
     */
    public function resetBuffByAttr($effect) {
        if ($effect == BattleHelper::BUFF_EFFECT_HEAL_HURT_LAST) {
            $this->_continuous = false;
            $this->_lossHP = 0;
            return;
        }
        $effectAttr = BattleHelper::getValueByBuffEffect($effect);
        $this->_buff_value[$effectAttr['attr']] = 0;
        $this->_buff_percent[$effectAttr['attr']] = 0;
        foreach ($this->_buffs as $k => $v) {
            if ($v->buffType() == BattleHelper::BUFF_TYPE_BUFF && $v->effectType() == $effect) {
                $this->effectBuff($v);
            }
        }
    }

    /**
     * 重置Buff
     */
    private function resetBuffs()
    {
        $this->_buff_value = array(
            "ATK_POINT" => 0, // 攻击基础值
            "DEF_POINT" => 0, //防御基础值
            "HEALTH_POINT" => 0,//血量基础值
            "RECU_POINT" => 0,//回复基础值
            "SPEED" => 0,//速度基础值
            "CRITICAL_RATE" => 0, //暴击率
            "CRITICAL_DMG_ADD" => 0,//暴击伤害加成
            "HATRED_POINT" => 0,    //仇恨值
            "HIT_POINT" => 0, //命中
            "SIDESTEP" => 0,//闪避
            "AG_CRITICAL" => 0,//抗暴
            "ABS_DMG" => 0,// 绝对伤害
            "DMG_REDUCE" => 0// 伤害减免
        );
        $this->_buff_percent = array(
            "ATK_POINT" => 0, // 攻击基础值
            "DEF_POINT" => 0, //防御基础值
            "HEALTH_POINT" => 0,//血量基础值
            "RECU_POINT" => 0,//回复基础值
            "SPEED" => 0,//速度基础值
            "CRITICAL_RATE" => 0, //暴击率
            "CRITICAL_DMG_ADD" => 0,//暴击伤害加成
            "HATRED_POINT" => 0,    //仇恨值
            "HIT_POINT" => 0, //命中
            "SIDESTEP" => 0,//闪避
            "AG_CRITICAL" => 0,//抗暴
            "ABS_DMG" => 0,// 绝对伤害
            "DMG_REDUCE" => 0// 伤害减免
        );
    }

    /**
     * @return boolean
     */
    public function isDead()
    {
        return $this->_isDead;
    }

    /**
     * @return int
     */
    public function getTotalDamage()
    {
        return $this->_total_damage;
    }

    /**
     * @param int $total_damage
     */
    public function setTotalDamage($_damage)
    {
        $this->_total_damage += $_damage;
    }
}

?>