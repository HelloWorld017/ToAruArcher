<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\PassiveSkill;
use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class SkillManaIncrease extends PassiveSkill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level > 40));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 20) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 7;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){
		$this->player->getSkillStatus()->maxMp += $this->level * 100;
	}

	public function onActiveUse(PlayerInteractEvent $event){
		return false;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getRequiredMana(){
		return 0;
	}

	public static function getRequiredLevel(){
		return 40;
	}

	public static function getName(){
		return "SKILL_MANA_INCREASE";
	}

	public static function getItem(){
		return Item::get(Item::DIAMOND, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
		$this->player->getSkillStatus()->maxMp += 100;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("ARROW_MASTERY_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("MAX_MANA_INCREASE", ($this->level * 100)) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("MAX_MANA_INCREASE", ($this->level * 100) + 100);
		}

		return $text;
	}

	public function onSkillStatusReset(){
		$this->player->getSkillStatus()->maxMp += 100 * $this->level;
	}

	public function setLevel($level){
		$this->level = $level;
	}
}
