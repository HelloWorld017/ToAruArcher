<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\Job;
use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\Status;
use Khinenw\AruPG\ToAruPG;

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
		return [
			Archery::ARCHER_ID_BASE,
			Archery::ARCHER_ID_BASE + 1,
			Archery::ARCHER_ID_BASE + 2,
			Archery::ARCHER_ID_BASE + 3,
			Archery::ARCHER_ID_BASE + 4,
			Archery::ARCHER_ID_BASE + 5,
			Archery::ARCHER_ID_BASE + 6,
			Archery::ARCHER_ID_BASE + 7,
			Archery::ARCHER_ID_BASE + 8,
			Archery::ARCHER_ID_BASE + 9
		];
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
	 * @method int getAdditionalBaseDamage(RPGPlayer $player) Armor base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage of armor (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getAdditionalBaseDamage(RPGPlayer $player){
		$damage = ($player->getAdditionalValue(Status::DEX) / 2) + 3;

		if($player->hasSkill(SkillArrowMastery::getId())){
			/**
			 * @var $mastery SkillArrowMastery
			 */
			$mastery = $player->getSkillById(SkillArrowMastery::getId());
			$damage += $mastery->getCurrentAttackIncrease();
		}
		return $damage;
	}

	public static function getApproximation(RPGPlayer $player){
		$approximation = $player->getStatus()->level * 2;
		if($player->hasSkill(SkillArrowMastery::getId())){
			/**
			 * @var $mastery SkillArrowMastery
			 */
			$mastery = $player->getSkillById(SkillArrowMastery::getId());
			$approximation -= $mastery->getLevel() * 5;
		}
		return ($approximation < 0) ? 0 : $approximation;
	}

	public static function getFinalDamage(RPGPlayer $player){
		return ToAruPG::randomizeDamage(self::getBaseDamage($player) + self::getAdditionalBaseDamage($player), self::getApproximation($player));
	}
}
