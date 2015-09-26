<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Int;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class SkillQuickShot implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level >= 75));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 10) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 6;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){
		//$this->player->getPlayer()->addEffect(Effect::getEffect(Effect::STRENGTH)->setDuration(PHP_INT_MAX)->setAmplifier(3));
	}

	public function onActiveUse(PlayerInteractEvent $event){
		$task = new QuickShotTask(Archery::getInstance(), $this->getPlayer(), $this->level, $this->level + 5);
		$task->setHandler(Server::getInstance()->getScheduler()->scheduleRepeatingTask($task, 5));
		return true;
	}

	public function getRequiredMana(){
		return 500 + $this->level * 5;
	}

	public static function getRequiredLevel(){
		return 75;
	}

	public static function getName(){
		return "QUICK_SHOT";
	}

	public static function getItem(){
		return Item::get(Item::DYE, 11, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("QUICK_SHOT_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("ARROW_DAMAGE", (($this->level * 10) + 50) . "%") . "\n" .
			ToAruPG::getTranslation("DURATION", ($this->level + 5) . ToAruPG::getTranslation("SECOND")) . "\n" .
			ToAruPG::getTranslation("MANA_USE", (500 + (($this->getLevel()) * 5))) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("ARROW_DAMAGE", (($this->level * 10) + 60) . "%") . "\n" .
				ToAruPG::getTranslation("DURATION", ($this->level + 6) . ToAruPG::getTranslation("SECOND")) . "\n" .
				ToAruPG::getTranslation("MANA_USE", (500 + (($this->getLevel() + 1) * 5)));
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

class QuickShotTask extends PluginTask{
	private $player;
	private $level;
	private $startTick = -1;
	private $duration;

	public function __construct(Plugin $plugin, RPGPlayer $player, $level, $duration){
		parent::__construct($plugin);
		$this->player = $player;
		$this->level = $level;
		$this->duration = $duration * 20;
	}

	public function onRun($currentTick){
		if($this->startTick === -1){
			$this->startTick = $currentTick;
		}

		$arrow = Archery::createEffectArrow(
			$this->player->getPlayer(),
			$this->player->getPlayer()->getPosition()->add(0, $this->player->getPlayer()->getEyeHeight(), 0),
			$this->player->getPlayer()->getDirectionVector()->multiply(3),
			$this->player->getPlayer()->getYaw(),
			$this->player->getPlayer()->getPitch(),
			255,
			255,
			0,
			true
		);

		$arrow->namedtag["ArcheryDamage"] = new Double("ArcheryDamage", ((
				$this->player->getCurrentJob()->getAdditionalBaseDamage($this->player) +
				$this->player->getCurrentJob()->getBaseDamage($this->player)
			) *
			((1 / 2) + ($this->level / 10))));
		$arrow->namedtag["Custom"] = new Int("Custom", 1);
		$this->player->getPlayer()->getLevel()->addEntity($arrow);
		$arrow->spawnToAll();

		if(($currentTick - $this->startTick) > $this->duration){
			$this->getHandler()->cancel();
		}
	}
}
