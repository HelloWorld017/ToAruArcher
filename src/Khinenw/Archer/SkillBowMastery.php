<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\PassiveSkill;
use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class SkillBowMastery extends PassiveSkill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level > 75));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 20) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 8;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){
		$this->player->getSkillStatus()->dex += $this->level * 3;
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
		return 75;
	}

	public static function getName(){
		return "BOW_MASTERY";
	}

	public static function getItem(){
		return Item::get(Item::GOLD_INGOT, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
		$this->player->getSkillStatus()->dex += 3;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("ARROW_MASTERY_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("DEX_INCREASE", ($this->level * 3)) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("DEX_INCREASE", ($this->level * 3) + 3);
		}

		return $text;
	}

	public function onSkillStatusReset(){
		$this->player->getSkillStatus()->dex += 3 * $this->level;
	}

	public function setLevel($level){
		$this->level = $level;
	}
}
