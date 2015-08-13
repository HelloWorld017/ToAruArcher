<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Int;

class SkillDualArrow implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level >= 1));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 10) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 3;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){
		//$this->player->getPlayer()->addEffect(Effect::getEffect(Effect::STRENGTH)->setDuration(PHP_INT_MAX)->setAmplifier(3));
	}

	public function onActiveUse(PlayerInteractEvent $event){
		$directionVector = $this->player->getPlayer()->getDirectionVector()->multiply(3);

		$pos = $event->getPlayer()->getPosition()->add(0, $event->getPlayer()->getEyeHeight(), 0);

		for($i = 0; $i < 2; $i++){
			$arrow = Archery::createEffectArrow(
				$event->getPlayer(),
				$pos->add(0.5 - (1 / mt_rand(1, 3)), 0.5 - (1 / mt_rand(1, 3)), 0.5 - (1 / mt_rand(1, 3))),
				$directionVector,
				$event->getPlayer()->getYaw(),
				$event->getPlayer()->getPitch(),
				0,
				255,
				120,
				true
			);

			$arrow->namedtag["ArcheryDamage"] = new Double("ArcheryDamage", ((
					$this->player->getCurrentJob()->getArmorBaseDamage($this->player) +
					$this->player->getCurrentJob()->getBaseDamage($this->player)
				) *
				(1 + ($this->level / 10))));
			$arrow->namedtag["Custom"] = new Int("Custom", 1);
			$event->getPlayer()->getLevel()->addEntity($arrow);
			$arrow->spawnToAll();
		}

		return true;
	}

	public function getRequiredMana(){
		return 25 + $this->level * 5;
	}

	public static function getRequiredLevel(){
		return 1;
	}

	public static function getName(){
		return "DUAL_ARROW";
	}

	public static function getItem(){
		return Item::get(Item::DYE, 10, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("DUAL_ARROW_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("ARROW_DAMAGE", "1" . ($this->level) . "0%") . "\n" .
			ToAruPG::getTranslation("MANA_USE", (25 + (($this->getLevel()) * 5))) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("ARROW_DAMAGE", "1" . ($this->level + 1) . "0%") . "\n" .
				ToAruPG::getTranslation("MANA_USE", (25 + (($this->getLevel() + 1) * 5)));
		}

		return $text;
	}

	public function setLevel($level){
		$this->level = $level;
	}

	public function getPlayer(){
		return $this->player;
	}
}
