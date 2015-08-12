<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Int;

class SkillExplosionArrow implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level >= 3));
	}

	public function canInvestSP($sp){
		if($this->level <= 5) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 2;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){}

	public function onActiveUse(PlayerInteractEvent $event){
		$directionVector = $this->player->getPlayer()->getDirectionVector()->multiply(3);

		$pos = $event->getPlayer()->getPosition()->add(0, $event->getPlayer()->getEyeHeight(), 0);

		$arrow = Archery::createEffectArrow(
		$event->getPlayer(),
		$pos->add(0.5 - (1 / mt_rand(1, 3)), 0.5 - (1 / mt_rand(1, 3)), 0.5 - (1 / mt_rand(1, 3))),
		$directionVector,
		$event->getPlayer()->getYaw(),
		$event->getPlayer()->getPitch(),
		255,
		120,
		0,
		false);

		$arrow->namedtag["ExplosionDamage"] = new Int("ExplosionDamage", ($this->level));

		$arrow->setOnFire(PHP_INT_MAX);
		$arrow->namedtag["Custom"] = new Int("Custom", 1);
		$event->getPlayer()->getLevel()->addEntity($arrow);
		$arrow->spawnToAll();

		return true;
	}

	public function getRequiredMana(){
		return Archery::ARCHER_ID_BASE + 1;
	}

	public static function getRequiredLevel(){
		return 40;
	}

	public static function getName(){
		return "EXPLOSION_ARROW";
	}

	public static function getItem(){
		return Item::get(Item::GUNPOWDER, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("ARROW_REPEAT_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("EXPLOSION_DAMAGE", $this->level . "\n" .
			ToAruPG::getTranslation("MANA_USE", (75 + (($this->getLevel()) * 5)))) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("EXPLOSION_DAMAGE", $this->level + 1) . "\n" .
				ToAruPG::getTranslation("MANA_USE", (75 + (($this->getLevel() + 1) * 5)));
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
