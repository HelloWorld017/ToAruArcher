<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\Job;
use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;

class JobArcher implements Job{

	/**
	 * @method int getId() Returns ID of the job
	 * @return int Id of job
	 */
	public static function getId(){
		return Archery::ARCHER_ID_BASE;
	}

	/**
	 * @method string getName() Returns name of the job
	 * @return string Returns name of the job which is key of translation.
	 */
	public static function getName(){
		return "Archer";
	}

	/**
	 * @method Skill[] getSkills() Skill list which can be get
	 * @return Skill[] Skill list which can be get
	 */
	public static function getSkills(){
		return [Archery::ARCHER_ID_BASE, Archery::ARCHER_ID_BASE + 1, Archery::ARCHER_ID_BASE + 2, Archery::ARCHER_ID_BASE + 3, Archery::ARCHER_ID_BASE + 4];
	}

	/**
	 * @method int getBaseDamage(RPGPlayer $player) Base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getBaseDamage(RPGPlayer $player){
		return ($player->getStatus()->dex / 2) + 3;
	}

	/**
	 * @method int getArmorBaseDamage(RPGPlayer $player) Armor base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage of armor (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getArmorBaseDamage(RPGPlayer $player){
		$damage = ($player->getArmorStatus()->dex / 2) + 3;

		if($player->hasSkill(Archery::ARCHER_ID_BASE + 4)){
			/**
			 * @var $mastery ArrowMastery
			 */
			$mastery = $player->getSkillById(Archery::ARCHER_ID_BASE + 4);
			$damage += $mastery->getCurrentAttackIncrease();
		}
		return $damage;
	}

}