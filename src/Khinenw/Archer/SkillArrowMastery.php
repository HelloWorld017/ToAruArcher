<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class SkillArrowMastery implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()));
	}

	public function canInvestSP($sp){
		if($this->level <= 15) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 4;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){

	}

	public function onActiveUse(PlayerInteractEvent $event){

	}

	public function getPlayer(){
		return $this->player;
	}

	public function getRequiredMana(){
		return 0;
	}

	public static function getRequiredLevel(){
		return 0;
	}

	public static function getName(){
		return "ARROW_MASTERY";
	}

	public static function getItem(){
		return Item::get(Item::AIR, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
		$this->player->getArmorStatus()->dex += 3;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("ARROW_REPEAT_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("ARROW_MASTERY_ATTACK_INCREASE", ($this->level * 5)) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("ARROW_MASTERY_ATTACK_INCREASE", ($this->level * 5));
		}

		return $text;
	}

	public function getCurrentAttackIncrease(){
		return $this->level * 5;
	}

	public function setLevel($level){
		$this->level = $level;
	}
}
