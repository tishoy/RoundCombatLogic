<?php
/**
 * Created by Tishoy.
 * Date: 2016/3/4
 * Time: 9:35
 */

class BattleHelper {

    //掉落几率为40%   这里设置为40便于计算
    const DROP_PROBABILITY = 40;

    /***************交战双方**************************/
    //我方
    const BATTLE_TEAM_US = true;
    //敌方
    const BATTLE_TEAM_ENEMY = false;

    /***************卡片战斗 攻击类型 *************************/
    //普通攻击
    const BATTLE_SKILL_TYPE_ATTACK = 'normal';
    //怒气攻击 big
    const BATTLE_SKILL_TYPE_BURSTATTACK = 'big';
    //被动技能
    const BATTLE_SKILL_TYPE_PASSIVE = 'passive';

    /***************技能 效果类型 *************************/
    //造成伤害
    const SKILL_EFFECT_DEMAGE = 1;
    //回复HP
    const SKILL_EFFECT_HPRESUME = 2;
    //Buf
    const SKILL_EFFECT_BUF = 3;
    //神兽被动
    const SKILL_EFFECT_PET = 4;
    //回复怒气
    const SKILL_EFFECT_MPRESUME = 5;
    //单体回复101
    //驱散加回复102
    //单体复活103
    //嘲讽104
    //回满怒气105
    //召唤小怪201
    /***************技能 攻击范围类型 *************************/
    //默认对象  仇恨最高
    const SKILL_TARGET_NUM_DEFAULT = 1;
    //敌方群体  己方群体
    const SKILL_TARGET_NUM_TEAMALL = 6;
    //随机1个目标 敌方
    const SKILL_TARGET_NUM_ROLLENEMY1 = 41;
    //随机2个目标 敌方
    const SKILL_TARGET_NUM_ROLLENEMY2 = 42;
    //随机3个目标 敌方
    const SKILL_TARGET_NUM_ROLLENEMY3 = 43;
    //随机4个目标 敌方
    const SKILL_TARGET_NUM_ROLLENEMY4 = 44;
    //随机1个目标 己方
    const SKILL_TARGET_NUM_ROLLUS1 = 51;
    //随机2个目标 己方
    const SKILL_TARGET_NUM_ROLLUS2 = 52;
    //随机3个目标 己方
    const SKILL_TARGET_NUM_ROLLUS3 = 53;
    //随机4个目标 己方
    const SKILL_TARGET_NUM_ROLLUS4 = 54;
    /***************buf 类型 *****************************/
    //加buf
    const BUFF_TYPE_BUFF = 1;
    //减buf
    const BUFF_TYPE_DEBUFF = 2;
    //上状态
    const BUFF_TYPE_STATUS = 3;
    /***************状态 类型 *************************/
    //常态
    const STATUS_TYPE_NORMAL = 0;
    //狂暴
    const STATUS_TYPE_CRAZY = 1;
    //隐身
    const STATUS_TYPE_HIDEN = 2;
    //沉默
    const STATUS_TYPE_SILENT = 3;
    //眩晕
    const STATUS_TYPE_DIZZY = 4;
    //睡眠
    const STATUS_TYPE_SLEEP = 5;
    /***************buf效果 类型 *************************/
    //无BUFF效果
    const BUFF_EFFECT_NORMAL = 0;
    //HP 上限
    const BUFF_EFFECT_HPMAX = 1;
    //攻击增加
    const BUFF_EFFECT_ATTACK = 2;
    //防御增加
    const BUFF_EFFECT_DEFENSE = 3;
    //回复增加
    const BUFF_EFFECT_HPRESUME = 4;
    //仇恨增加
    const BUFF_EFFECT_HATRED = 5;
    //速度增加
    const BUFF_EFFECT_SPEED = 6;
    //暴击增加
    const BUFF_EFFECT_CRITRATE = 7;
    //暴击伤害增加
    const BUFF_EFFECT_CRITADDTION = 8;
    //气血上限百分比增加
    const BUFF_EFFECT_HPMAX_PERCENT = 9;
    //攻击百分比增加
    const BUFF_EFFECT_ATTACK_PERCENT = 10;
    //防御百分比增加
    const BUFF_EFFECT_DEFENSE_PERCENT = 11;
    //回复百分比增加
    const BUFF_EFFECT_RESUME_PERCENT = 12;
    //速度百分比增加
    const BUFF_EFFECT_SPEED_PERCENT = 13;
    //持续回血/掉血
    const BUFF_EFFECT_HEAL_HURT_LAST = 14;
    //额外命中
    const BUFF_EFFECT_HITRATE = 15;
    //额外闪避
    const BUFF_EFFECT_MISSRATE = 16;
    //额外抗暴
    const BUFF_EFFECT_DEFCRITRATE = 17;
    //额外绝对伤害
    const BUFF_EFFECT_FIXHURT = 18;
    //额外伤害减免
    const BUFF_EFFECT_SUBHURT = 19;
    //回复怒气
    const BUFF_EFFECT_MPRESUME = 20;
    //异常状态抵抗
    const BUFF_EFFECT_DESTATUS = 21;

    /***************buf 目标类型 *************************/
    //释放单位
    const BUFF_TARGET_NUM_CASTER = 1;
    //己方全体
    const BUFF_TARGET_NUM_TEAMUS = 2;
    //效果目标
    const BUFF_TARGET_NUM_AIM = 3;
    //敌方全体
    const BUFF_TARGET_NUM_TEAMENEMY = 4;
    //随机1个目标 敌方
    const BUFF_TARGET_NUM_ROLLENEMY1 = 41;
    //随机2个目标 敌方
    const BUFF_TARGET_NUM_ROLLENEMY2 = 42;
    //随机3个目标 敌方
    const BUFF_TARGET_NUM_ROLLENEMY3 = 43;
    //随机4个目标 敌方
    const BUFF_TARGET_NUM_ROLLENEMY4 = 44;
    //随机1个目标 己方
    const BUFF_TARGET_NUM_ROLLUS1 = 51;
    //随机2个目标 己方
    const BUFF_TARGET_NUM_ROLLUS2 = 52;
    //随机3个目标 己方
    const BUFF_TARGET_NUM_ROLLUS3 = 53;
    //随机4个目标 己方
    const BUFF_TARGET_NUM_ROLLUS4 = 54;
    /***************宠物 目标类型 *************************/
    //释放单位
    const PET_TARGET_NUM_CASTER = 1;
    //己方全体
    const PET_TARGET_NUM_TEAMUS = 6;
    //武者
    const PET_TARGET_WU_ZHE = 11;
    //侠客
    const PET_TARGET_XIA_KE = 12;
    //Buff
    const PET_TARGET_BUFFER = 13;
    //医师
    const PET_TARGET_DOCTOR = 14;
    //剑仙
    const PET_TARGET_JIAN_XIAN = 15;

    /**
     * @param $type
     * 通过释放的buff类型  决定影响属性 与 影响方法
     * 影像属性字符串与Hero属性对应
     * 影响方法为 plus percent divide100 divide1000 debuf
     */
    public static function getValueByBuffEffect($type) {
        switch ($type) {
            case BattleHelper::BUFF_EFFECT_HPMAX:
                return array('attr' => 'HEALTH_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_ATTACK:
                return array('attr' => 'ATK_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_DEFENSE:
                return array('attr' => 'DEF_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_HPRESUME:
                return array('attr' => 'RECU_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_HATRED:
                //attr 中没有
                return array('attr' => 'HATRED_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_SPEED:
                return array('attr' => 'SPEED', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_CRITRATE:
                return array('attr' => 'CRITICAL_RATE', 'method' => 'divide1000');

            case BattleHelper::BUFF_EFFECT_CRITADDTION:
                return array('attr' => 'CRITICAL_DMG_ADD', 'method' => 'divide1000');

            case BattleHelper::BUFF_EFFECT_HPMAX_PERCENT:
                return array('attr' => 'HEALTH_POINT', 'method' => 'percent');

            case BattleHelper::BUFF_EFFECT_ATTACK_PERCENT:
                return array('attr' => 'ATK_POINT', 'method' => 'percent');

            case BattleHelper::BUFF_EFFECT_DEFENSE_PERCENT:
                return array('attr' => 'DEF_POINT', 'method' => 'percent');

            case BattleHelper::BUFF_EFFECT_RESUME_PERCENT:
                return array('attr' => 'RECU_POINT', 'method' => 'percent');

            case BattleHelper::BUFF_EFFECT_SPEED_PERCENT:
                return array('attr' => 'SPEED', 'method' => 'percent');

            case BattleHelper::BUFF_EFFECT_HEAL_HURT_LAST:
                return array('attr' => '', 'method' => 'lose');

            case BattleHelper::BUFF_EFFECT_HITRATE:
                return array('attr' => 'HIT_POINT', 'method' => 'divide1000');

            case BattleHelper::BUFF_EFFECT_MISSRATE:
                return array('attr' => 'SIDESTEP', 'method' => 'divide1000');

            case BattleHelper::BUFF_EFFECT_DEFCRITRATE:
                return array('attr' => 'AG_CRITICAL', 'method' => 'divide1000');

            case BattleHelper::BUFF_EFFECT_FIXHURT:
                return array('attr' => 'ABS_DMG', 'method' => 'divide100');

            case BattleHelper::BUFF_EFFECT_SUBHURT:
                return array('attr' => 'DMG_REDUCE', 'method' => 'divide100');

            case BattleHelper::BUFF_EFFECT_MPRESUME:
                //attr 中没有
                return array('attr' => 'MAGIC_POINT', 'method' => 'plus');

            case BattleHelper::BUFF_EFFECT_DESTATUS:
                //attr 中没有
                return array('attr' => 'STATUS', 'method' => 'debuf');
        }
    }
}

?>
