<?php
/**
 * Created by Tishoy.
 * Date=> 2016/3/7
 * Time=> 12:53
 */
require_once 'Battle.class.php';
require_once 'Hero.class.php';
require_once 'BattleHelper.class.php';
require_once 'Team.php';
require_once 'AttackSkill.class.php';
require_once 'Buff.class.php';

//require_once '../../common/common.func.php';
//require_once '../definition/GameDef.class.php';
//require_once '../../adapter/MemAdapter.php';
//require_once '../../GameConfig.inc.php';
$enemyTeam = array(
    "5281" => array("BASE_ID" => 5281, "LEVEL" => 76, "ATK_POINT" => 45577.9, "ATK_ADD" => 1, "DEF_POINT" => 25602, "DEF_ADD" => 1, "HEALTH_POINT" => 128716.5, "HP_ADD" => 1, "RECU_POINT" => 20368.55, "RECU_ADD" => 1, "SPEED" => 359, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 106296, "HIT_POINT" => 1.15, "SIDESTEP" => 0.75, "AG_CRITICAL" => 0.8, "ABS_DMG" => 0, "DMG_REDUCE" => 0.7, "HATRED_POINT" => 100),
    "5282" => array("BASE_ID" => 5433, "LEVEL" => 84, "ATK_POINT" => 56352.25, "ATK_ADD" => 1, "DEF_POINT" => 25216, "DEF_ADD" => 1, "HEALTH_POINT" => 136598.65, "HP_ADD" => 1, "RECU_POINT" => 23102.93, "RECU_ADD" => 1, "SPEED" => 328, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.651, "CRITICAL_DMG_ADD" => 2.09, "BATTLE_RATING" => 118863, "HIT_POINT" => 1.25, "SIDESTEP" => 0.4, "AG_CRITICAL" => 0.45, "ABS_DMG" => 0, "DMG_REDUCE" => 0.65, "HATRED_POINT" => 100),
    "5283" => array("BASE_ID" => 5533, "LEVEL" => 85, "ATK_POINT" => 58680.5, "ATK_ADD" => 1, "DEF_POINT" => 27620.16, "DEF_ADD" => 1, "HEALTH_POINT" => 142207.15, "HP_ADD" => 1, "RECU_POINT" => 23435, "RECU_ADD" => 1, "SPEED" => 395.04, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.4, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 124279, "HIT_POINT" => 1.25, "SIDESTEP" => 0.525, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0.7, "HATRED_POINT" => 100),
    "5284" => array("BASE_ID" => 5284, "LEVEL" => 80, "ATK_POINT" => 45947.52, "ATK_ADD" => 1, "DEF_POINT" => 25328.4, "DEF_ADD" => 1, "HEALTH_POINT" => 130213.5, "HP_ADD" => 1, "RECU_POINT" => 29538.62, "RECU_ADD" => 1, "SPEED" => 326, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 109126, "HIT_POINT" => 1.1, "SIDESTEP" => 1, "AG_CRITICAL" => 1, "ABS_DMG" => 0, "DMG_REDUCE" => 0.7, "HATRED_POINT" => 100),
    "5285" => array("BASE_ID" => 5281, "LEVEL" => 88, "ATK_POINT" => 62763.1, "ATK_ADD" => 1, "DEF_POINT" => 27192.07, "DEF_ADD" => 1, "HEALTH_POINT" => 153432.5, "HP_ADD" => 1, "RECU_POINT" => 24771.61, "RECU_ADD" => 1, "SPEED" => 382, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.765, "CRITICAL_DMG_ADD" => 1.57, "BATTLE_RATING" => 131802, "HIT_POINT" => 1.65, "SIDESTEP" => 0.6, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0.7, "HATRED_POINT" => 100),
    "111" => array("BASE_ID" => 4005, "LEVEL" => 91, "ATK_POINT" => 0, "ATK_ADD" => 1, "DEF_POINT" => 0, "DEF_ADD" => 1, "HEALTH_POINT" => 96150, "HP_ADD" => 1, "RECU_POINT" => 19248, "RECU_ADD" => 1, "SPEED" => 1, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 37079, "HATRED_POINT" => 50, "HIT_POINT" => 1, "SIDESTEP" => 0, "AG_CRITICAL" => 0, "ABS_DMG" => 4000, "DMG_REDUCE" => 0.5, "HATRED_POINT" => 100)
);

$myTeam = array(
    "5281" => array("BASE_ID" => 5281, "LEVEL" => 90, "ATK_POINT" => 64432.39, "ATK_ADD" => 1, "DEF_POINT" => 24141.39, "DEF_ADD" => 1, "HEALTH_POINT" => 125685.5, "HP_ADD" => 1, "RECU_POINT" => 22234.725, "RECU_ADD" => 1, "SPEED" => 374, "SPEED_ADD" => 1, "CRITICAL_RATE" => 1.1, "CRITICAL_DMG_ADD" => 2.5, "BATTLE_RATING" => 122307, "HIT_POINT" => 1.45, "SIDESTEP" => 0.025, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0, "HATRED_POINT" => 100),
    "5282" => array("BASE_ID" => 5284, "LEVEL" => 90, "ATK_POINT" => 52625.85, "ATK_ADD" => 1, "DEF_POINT" => 23530.725, "DEF_ADD" => 1, "HEALTH_POINT" => 135328.7, "HP_ADD" => 1, "RECU_POINT" => 25448.355, "RECU_ADD" => 1, "SPEED" => 367, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.22, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 113511, "HIT_POINT" => 1.2, "SIDESTEP" => 1.04, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0.5, "HATRED_POINT" => 100),
    "5283" => array("BASE_ID" => 5283, "LEVEL" => 90, "ATK_POINT" => 56490.885, "ATK_ADD" => 1, "DEF_POINT" => 25064.75, "DEF_ADD" => 1, "HEALTH_POINT" => 115727.45, "HP_ADD" => 1, "RECU_POINT" => 22088.83, "RECU_ADD" => 1, "SPEED" => 382.2, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.35, "CRITICAL_DMG_ADD" => 2.25, "BATTLE_RATING" => 114068, "HIT_POINT" => 1.5, "SIDESTEP" => 0.325, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0.5, "HATRED_POINT" => 100),
    "5284" => array("BASE_ID" => 5282, "LEVEL" => 65, "ATK_POINT" => 41943.52, "ATK_ADD" => 1, "DEF_POINT" => 23105.5, "DEF_ADD" => 1, "HEALTH_POINT" => 116686, "HP_ADD" => 1, "RECU_POINT" => 25164.78, "RECU_ADD" => 1, "SPEED" => 445, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 98269, "HIT_POINT" => 1.15, "SIDESTEP" => 0.75, "AG_CRITICAL" => 0.35, "ABS_DMG" => 0, "DMG_REDUCE" => 0.7, "HATRED_POINT" => 100),
    "5285" => array("BASE_ID" => 5281, "LEVEL" => 86, "ATK_POINT" => 52222.2, "ATK_ADD" => 1, "DEF_POINT" => 25558.975, "DEF_ADD" => 1, "HEALTH_POINT" => 131459.65, "HP_ADD" => 1, "RECU_POINT" => 24702.2, "RECU_ADD" => 1, "SPEED" => 380.04, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0.35, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 113513, "HIT_POINT" => 1.2, "SIDESTEP" => 0.625, "AG_CRITICAL" => 0.2, "ABS_DMG" => 0, "DMG_REDUCE" => 0.5, "HATRED_POINT" => 100),
    "111" => array("BASE_ID" => 4005, "LEVEL" => 92, "ATK_POINT" => 0, "ATK_ADD" => 1, "DEF_POINT" => 0, "DEF_ADD" => 1, "HEALTH_POINT" => 97024, "HP_ADD" => 1, "RECU_POINT" => 19423, "RECU_ADD" => 1, "SPEED" => 1, "SPEED_ADD" => 1, "CRITICAL_RATE" => 0, "CRITICAL_DMG_ADD" => 1.5, "BATTLE_RATING" => 38289, "HATRED_POINT" => 50, "HIT_POINT" => 1, "SIDESTEP" => 0, "AG_CRITICAL" => 0, "ABS_DMG" => 4000, "DMG_REDUCE" => 0.5, "HATRED_POINT" => 100)
);

$team3 = array(
    "4093" => array("BASE_ID" => 1201,"LEVEL" => 89,"ATK_POINT" => 41018.67,"ATK_ADD" => 1,"DEF_POINT" => 25254.49,"DEF_ADD" => 1,"HEALTH_POINT" => 139400,"HP_ADD" => 1,"RECU_POINT" => 28782.22,"RECU_ADD" => 1,"SPEED" => 326,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 103779,"HIT_POINT" => 1.1,"SIDESTEP" => 0.3,"AG_CRITICAL" => 1.37,"ABS_DMG" => 0,"DMG_REDUCE" => 0.2, "HATRED_POINT" => 100),
    "11593" => array("BASE_ID" => 1161,"LEVEL" => 87,"ATK_POINT" => 42742.36,"ATK_ADD" => 1,"DEF_POINT" => 23815.75,"DEF_ADD" => 1,"HEALTH_POINT" => 126872.2,"HP_ADD" => 1,"RECU_POINT" => 25909.4,"RECU_ADD" => 1,"SPEED" => 453,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 101184,"HIT_POINT" => 1.15,"SIDESTEP" => 0.65,"AG_CRITICAL" => 0.27,"ABS_DMG" => 500,"DMG_REDUCE" => 0.2, "HATRED_POINT" => 100),
    "17007" => array("BASE_ID" => 1111,"LEVEL" => 91,"ATK_POINT" => 61653,"ATK_ADD" => 1,"DEF_POINT" => 27423.35,"DEF_ADD" => 1,"HEALTH_POINT" => 131993,"HP_ADD" => 1,"RECU_POINT" => 23973.75,"RECU_ADD" => 1,"SPEED" => 373.56,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.6,"CRITICAL_DMG_ADD" => 2.5,"BATTLE_RATING" => 123989,"HIT_POINT" => 1.45,"SIDESTEP" => 0.1,"AG_CRITICAL" => 0.12,"ABS_DMG" => 0,"DMG_REDUCE" => 0, "HATRED_POINT" => 100),
    "6798" => array("BASE_ID" => 1191,"LEVEL" => 81,"ATK_POINT" => 40894.85,"ATK_ADD" => 1,"DEF_POINT" => 25107,"DEF_ADD" => 1,"HEALTH_POINT" => 122580.5,"HP_ADD" => 1,"RECU_POINT" => 23889,"RECU_ADD" => 1,"SPEED" => 344,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 99265,"HIT_POINT" => 1.15,"SIDESTEP" => 0.85,"AG_CRITICAL" => 0.27,"ABS_DMG" => 0,"DMG_REDUCE" => 0.2, "HATRED_POINT" => 100),
    "17008" => array("BASE_ID" => 1101,"LEVEL" => 92,"ATK_POINT" => 63555.67,"ATK_ADD" => 1,"DEF_POINT" => 27777.875,"DEF_ADD" => 1,"HEALTH_POINT" => 143972.5,"HP_ADD" => 1,"RECU_POINT" => 26210.8,"RECU_ADD" => 1,"SPEED" => 367,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.6,"CRITICAL_DMG_ADD" => 2.5,"BATTLE_RATING" => 129690,"HIT_POINT" => 1.45,"SIDESTEP" => 0.4,"AG_CRITICAL" => 0.12,"ABS_DMG" => 0,"DMG_REDUCE" => 0, "HATRED_POINT" => 100)
);

$team4 = array(
    "11490" => array("BASE_ID" => 1246,"LEVEL" => 60,"ATK_POINT" => 43350.06,"ATK_ADD" => 1,"DEF_POINT" => 20485.72,"DEF_ADD" => 1,"HEALTH_POINT" => 125076.8,"HP_ADD" => 1,"RECU_POINT" => 27268.52,"RECU_ADD" => 1,"SPEED" => 375,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.25,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 100404,"HIT_POINT" => 1.25,"SIDESTEP" => 1.3,"AG_CRITICAL" => 0.45,"ABS_DMG" => 0,"DMG_REDUCE" => 0.5, "HATRED_POINT" => 100),
    "34962" => array("BASE_ID" => 1566,"LEVEL" => 90,"ATK_POINT" => 44637.04,"ATK_ADD" => 1,"DEF_POINT" => 18461.4,"DEF_ADD" => 1,"HEALTH_POINT" => 122759.3,"HP_ADD" => 1,"RECU_POINT" => 18529.6,"RECU_ADD" => 1,"SPEED" => 390,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.4,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 97456,"HIT_POINT" => 1.25,"SIDESTEP" => 0.825,"AG_CRITICAL" => 0.45,"ABS_DMG" => 0,"DMG_REDUCE" => 0.9, "HATRED_POINT" => 100),
    "7683" => array("BASE_ID" => 1376,"LEVEL" => 60,"ATK_POINT" => 38603.92,"ATK_ADD" => 1,"DEF_POINT" => 17457.3,"DEF_ADD" => 1,"HEALTH_POINT" => 112193.95,"HP_ADD" => 1,"RECU_POINT" => 23217.8,"RECU_ADD" => 1,"SPEED" => 395,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.4,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 89243,"HIT_POINT" => 1.25,"SIDESTEP" => 0.825,"AG_CRITICAL" => 0.45,"ABS_DMG" => 0,"DMG_REDUCE" => 0.9, "HATRED_POINT" => 100),
    "30210" => array("BASE_ID" => 1601,"LEVEL" => 92,"ATK_POINT" => 64963.4312,"ATK_ADD" => 1,"DEF_POINT" => 25228.64,"DEF_ADD" => 1,"HEALTH_POINT" => 150863.1,"HP_ADD" => 1,"RECU_POINT" => 30280.68,"RECU_ADD" => 1,"SPEED" => 381.12,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.4,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 132506,"HIT_POINT" => 1.634,"SIDESTEP" => 0.325,"AG_CRITICAL" => 0.45,"ABS_DMG" => 250,"DMG_REDUCE" => 0.884, "HATRED_POINT" => 100),
    "28829" => array("BASE_ID" => 1586,"LEVEL" => 92,"ATK_POINT" => 58194.24,"ATK_ADD" => 1,"DEF_POINT" => 23843,"DEF_ADD" => 1,"HEALTH_POINT" => 142263.3,"HP_ADD" => 1,"RECU_POINT" => 28927.64,"RECU_ADD" => 1,"SPEED" => 328,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0.25,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 122425,"HIT_POINT" => 1.65,"SIDESTEP" => 0.4,"AG_CRITICAL" => 0.45,"ABS_DMG" => 250,"DMG_REDUCE" => 0.9, "HATRED_POINT" => 100)
);

$myTeam = array("11490"=>array("BASE_ID"=>1246,"LEVEL"=>60,"ATK_POINT"=>43350.06,"ATK_ADD"=>1,"DEF_POINT"=>20485.72,"DEF_ADD"=>1,"HEALTH_POINT"=>125076.8,"HP_ADD"=>1,"RECU_POINT"=>27268.52,"RECU_ADD"=>1,"SPEED"=>375,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.25,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>100404,"HIT_POINT"=>1.25,"SIDESTEP"=>1.3,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.5, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 2549),
    "34962"=>array("BASE_ID"=>1566,"LEVEL"=>90,"ATK_POINT"=>44637.04,"ATK_ADD"=>1,"DEF_POINT"=>18461.4,"DEF_ADD"=>1,"HEALTH_POINT"=>122759.3,"HP_ADD"=>1,"RECU_POINT"=>18529.6,"RECU_ADD"=>1,"SPEED"=>390,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>97456,"HIT_POINT"=>1.25,"SIDESTEP"=>0.825,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 556, "BIG_SKILL" => 3556),
    "7683"=>array("BASE_ID"=>1376,"LEVEL"=>60,"ATK_POINT"=>38603.92,"ATK_ADD"=>1,"DEF_POINT"=>17457.3,"DEF_ADD"=>1,"HEALTH_POINT"=>112193.95,"HP_ADD"=>1,"RECU_POINT"=>23217.8,"RECU_ADD"=>1,"SPEED"=>395,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>89243,"HIT_POINT"=>1.25,"SIDESTEP"=>0.825,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 376, "BIG_SKILL" => 3376),
    "30210"=>array("BASE_ID"=>1601,"LEVEL"=>92,"ATK_POINT"=>64963.4312,"ATK_ADD"=>1,"DEF_POINT"=>25228.64,"DEF_ADD"=>1,"HEALTH_POINT"=>150863.1,"HP_ADD"=>1,"RECU_POINT"=>30280.68,"RECU_ADD"=>1,"SPEED"=>381.12,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>132506,"HIT_POINT"=>1.634,"SIDESTEP"=>0.325,"AG_CRITICAL"=>0.45,"ABS_DMG"=>250,"DMG_REDUCE"=>0.884, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 2549),
    "28829"=>array("BASE_ID"=>1586,"LEVEL"=>92,"ATK_POINT"=>58194.24,"ATK_ADD"=>1,"DEF_POINT"=>23843,"DEF_ADD"=>1,"HEALTH_POINT"=>142263.3,"HP_ADD"=>1,"RECU_POINT"=>28927.64,"RECU_ADD"=>1,"SPEED"=>328,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.25,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>122425,"HIT_POINT"=>1.65,"SIDESTEP"=>0.4,"AG_CRITICAL"=>0.45,"ABS_DMG"=>250,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 3586),
    "111" => array("BASE_ID" => 4005,"LEVEL" => 90,"ATK_POINT" => 0,"ATK_ADD" => 1,"DEF_POINT" => 0,"DEF_ADD" => 1,"HEALTH_POINT" => 95276,"HP_ADD" => 1,"RECU_POINT" => 19073,"RECU_ADD" => 1,"SPEED" => 1,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 36369,"HATRED_POINT" => 50,"HIT_POINT" => 1,"SIDESTEP" => 0,"AG_CRITICAL" => 0,"ABS_DMG" => 4000,"DMG_REDUCE" => 0.5, "SKILL" => 2549, "BIG_SKILL" => 2549, "PASSIVE_SKILLS" => array('skill1'=> 1048, 'skill2'=> 1049, 'skill3'=> 1050, 'skill4'=> 1051, 'skill5'=> 1052)));

$enemyTeam = array("11490"=>array("BASE_ID"=>1246,"LEVEL"=>60,"ATK_POINT"=>43350.06,"ATK_ADD"=>1,"DEF_POINT"=>20485.72,"DEF_ADD"=>1,"HEALTH_POINT"=>125076.8,"HP_ADD"=>1,"RECU_POINT"=>27268.52,"RECU_ADD"=>1,"SPEED"=>375,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.25,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>100404,"HIT_POINT"=>1.25,"SIDESTEP"=>1.3,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.5, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 2549),
    "34962"=>array("BASE_ID"=>1566,"LEVEL"=>90,"ATK_POINT"=>44637.04,"ATK_ADD"=>1,"DEF_POINT"=>18461.4,"DEF_ADD"=>1,"HEALTH_POINT"=>122759.3,"HP_ADD"=>1,"RECU_POINT"=>18529.6,"RECU_ADD"=>1,"SPEED"=>390,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>97456,"HIT_POINT"=>1.25,"SIDESTEP"=>0.825,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 556, "BIG_SKILL" => 3556),
    "7683"=>array("BASE_ID"=>1376,"LEVEL"=>60,"ATK_POINT"=>38603.92,"ATK_ADD"=>1,"DEF_POINT"=>17457.3,"DEF_ADD"=>1,"HEALTH_POINT"=>112193.95,"HP_ADD"=>1,"RECU_POINT"=>23217.8,"RECU_ADD"=>1,"SPEED"=>395,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>89243,"HIT_POINT"=>1.25,"SIDESTEP"=>0.825,"AG_CRITICAL"=>0.45,"ABS_DMG"=>0,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 376, "BIG_SKILL" => 3376),
    "30210"=>array("BASE_ID"=>1601,"LEVEL"=>92,"ATK_POINT"=>64963.4312,"ATK_ADD"=>1,"DEF_POINT"=>25228.64,"DEF_ADD"=>1,"HEALTH_POINT"=>150863.1,"HP_ADD"=>1,"RECU_POINT"=>30280.68,"RECU_ADD"=>1,"SPEED"=>381.12,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.4,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>132506,"HIT_POINT"=>1.634,"SIDESTEP"=>0.325,"AG_CRITICAL"=>0.45,"ABS_DMG"=>250,"DMG_REDUCE"=>0.884, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 2549),
    "28829"=>array("BASE_ID"=>1586,"LEVEL"=>92,"ATK_POINT"=>58194.24,"ATK_ADD"=>1,"DEF_POINT"=>23843,"DEF_ADD"=>1,"HEALTH_POINT"=>142263.3,"HP_ADD"=>1,"RECU_POINT"=>28927.64,"RECU_ADD"=>1,"SPEED"=>328,"SPEED_ADD"=>1,"CRITICAL_RATE"=>0.25,"CRITICAL_DMG_ADD"=>1.5,"BATTLE_RATING"=>122425,"HIT_POINT"=>1.65,"SIDESTEP"=>0.4,"AG_CRITICAL"=>0.45,"ABS_DMG"=>250,"DMG_REDUCE"=>0.9, "HATRED_POINT" => 100, "SKILL" => 2549, "BIG_SKILL" => 3586),
    "111" => array("BASE_ID" => 4005,"LEVEL" => 90,"ATK_POINT" => 0,"ATK_ADD" => 1,"DEF_POINT" => 0,"DEF_ADD" => 1,"HEALTH_POINT" => 95276,"HP_ADD" => 1,"RECU_POINT" => 19073,"RECU_ADD" => 1,"SPEED" => 1,"SPEED_ADD" => 1,"CRITICAL_RATE" => 0,"CRITICAL_DMG_ADD" => 1.5,"BATTLE_RATING" => 36369,"HATRED_POINT" => 50,"HIT_POINT" => 1,"SIDESTEP" => 0,"AG_CRITICAL" => 0,"ABS_DMG" => 4000,"DMG_REDUCE" => 0.5, "SKILL" => 2549, "BIG_SKILL" => 2549, "PASSIVE_SKILLS" => array('skill1'=> 1048, 'skill2'=> 1049, 'skill3'=> 1050, 'skill4'=> 1051, 'skill5'=> 1052)));


$battle = new Battle(Battle::TYPE_ARENA);
$battle->startBattle();
//$start = microtime(true);
//for ($i = 0; $i < 100; $i++) {
//    $battle = new Battle(Battle::TYPE_ARENA);
//    $battle->startBattle();
//    print $i . "=========================================================================================";
//}
//$end = microtime(true);
//echo $end - $start;
?>