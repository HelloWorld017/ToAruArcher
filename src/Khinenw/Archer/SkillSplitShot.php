<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Int;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class SkillSplitShot implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level >= 40));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 5) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 5;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	public function onPassiveInit(){
		//$this->player->getPlayer()->addEffect(Effect::getEffect(Effect::STRENGTH)->setDuration(PHP_INT_MAX)->setAmplifier(3));
	}

	public function onActiveUse(PlayerInteractEvent $event){
		$this->player->getPlayer()->setMotion(new Vector3(0, 1.5, 0));
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new SplitShotTask(Archery::getInstance(), $this->player, $this->level), 10);
		return true;
	}

	public function getRequiredMana(){
		return 115 + $this->level * 5;
	}

	public static function getRequiredLevel(){
		return 40;
	}

	public static function getName(){
		return "SPLIT_SHOT";
	}

	public static function getItem(){
		return Item::get(Item::FLINT, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("SPLIT_SHOT_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("ARROW_DAMAGE", "2" . ($this->level) . "0%") . "\n" .
			ToAruPG::getTranslation("MANA_USE", (115 + (($this->getLevel()) * 5))) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("ARROW_DAMAGE", "2" . ($this->level + 1) . "0%") . "\n" .
				ToAruPG::getTranslation("MANA_USE", (115 + (($this->getLevel() + 1) * 5)));
		}

		return $text;
	}

	public function setLevel($level){
		$this->level = $level;
	}
}

class SplitShotTask extends PluginTask{

	private $player;
	private $level;

	public function __construct(Plugin $plugin, RPGPlayer $player, $level){
		parent::__construct($plugin);
		$this->player = $player;
		$this->level = $level;
	}

	public function onRun($currentTick){
		if(!ToAruPG::getInstance()->isValidPlayer($this->player->getPlayer())) return;
		$pos = $this->player->getPlayer()->getPosition();

		for($i = 0; $i < 20; $i++){
			$arrow = Archery::createEffectArrow(
				$this->player->getPlayer(),
				$pos,
				new Vector3((mt_rand(-5, 5) / 5), -1, (mt_rand(-5, 5) / 5)),
				0,
				-90,
				255,
				0,
				120,
				false
			);

			$arrow->namedtag["ArcheryDamage"] = new Double("ArcheryDamage", ((
					$this->player->getCurrentJob()->getArmorBaseDamage($this->player) +
					$this->player->getCurrentJob()->getBaseDamage($this->player)
				) *
				(2 + ($this->level / 10))));
			$arrow->namedtag["Custom"] = new Int("Custom", 1);
			$this->player->getPlayer()->getLevel()->addEntity($arrow);
			$arrow->spawnToAll();
		}
	}
}
