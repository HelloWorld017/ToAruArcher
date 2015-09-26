<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\DustParticle;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\Server;


class SkillDash implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return ($player->getCurrentJob() instanceof JobArcher);
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 10) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 1;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){}

	public function onActiveUse(PlayerInteractEvent $event){
		$directionVector = $this->player->getPlayer()->getDirectionVector()->multiply(3);
		$position = $this->player->getPlayer();
		$this->player->getPlayer()->setMotion($directionVector);
		/*(new Explosion(
			new Position($position->getX(), $position->getY() + $this->player->getPlayer()->getEyeHeight(), $position->getZ(), $position->getLevel()),
			$this->level,
			$this->player->getPlayer()
		))->explodeB();*/

		$pk = new ExplodePacket();
		$pk->x = $position->getX();
		$pk->y = $position->getY() + $this->player->getPlayer()->getEyeHeight();
		$pk->z = $position->getZ();
		$pk->radius = $this->level;

		Server::broadcastPacket($position->getLevel()->getChunkPlayers($position->chunk->getX(), $position->chunk->getZ()), $pk);

		for($i = 1; $i < 50; $i++){
			$this->player->getPlayer()->getLevel()->addParticle(new DustParticle($position->add(
				$directionVector->getX() / $i,
				$directionVector->getY() / $i + $this->player->getPlayer()->getEyeHeight(),
				$directionVector->getZ() / $i
			), 255, 100, 0));
		}
		return true;
	}

	public function getRequiredMana(){
		return 30 - $this->level;
	}

	public static function getRequiredLevel(){
		return 1;
	}

	public static function getName(){
		return "Dash";
	}

	public static function getItem(){
		return Item::get(Item::FEATHER, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("DASH_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("MANA_USE", 30 - $this->level) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("MANA_USE", 30 - $this->level);
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
