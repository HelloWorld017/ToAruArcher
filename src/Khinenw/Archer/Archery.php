<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\JobManager;
use Khinenw\AruPG\SkillManager;
use Khinenw\AruPG\ToAruPG;
use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\level\Explosion;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Archery extends PluginBase implements Listener{

	//´Œ!
	const ARCHER_ID_BASE = 72;

	private static $instance;

	public function onEnable(){
		JobManager::registerJob(new JobArcher());
		SkillManager::registerSkill(new SkillArrowRepeat());
		SkillManager::registerSkill(new SkillDash());
		SkillManager::registerSkill(new SkillDualArrow());
		SkillManager::registerSkill(new SkillExplosionArrow());
		SkillManager::registerSkill(new SkillArrowMastery());
		SkillManager::registerSkill(new SkillSplitShot());
		SkillManager::registerSkill(new SkillQuickShot());
		SkillManager::registerSkill(new SkillManaIncrease());
		SkillManager::registerSkill(new SkillBowMastery());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		Entity::registerEntity("Khinenw\\Archer\\EffectArrow");
		ToAruPG::addAllTranslation($this->getResource("translation.yml"));
		self::$instance = $this;
	}

	public static function getInstance(){
		return self::$instance;
	}

	public function onEntityHitByProjectile(EntityDamageEvent $event){
		if(!($event instanceof EntityDamageByEntityEvent)) return;

		$damager = $event->getDamager();
		if(!($damager instanceof Projectile)) return;

		if(isset($damager->namedtag["ArcheryDamage"])){
			$event->setDamage($damager->namedtag["ArcheryDamage"]);
		}
	}

	public function onProjectileHit(ProjectileHitEvent $event){
		if(isset($event->getEntity()->namedtag["ExplosionDamage"])){
			(new Explosion($event->getEntity()->getPosition(), $event->getEntity()->namedtag["ExplosionDamage"]))->explodeB();
		}

		if(isset($event->getEntity()->namedtag["Custom"]) && $event->getEntity()->namedtag["Custom"] === 1){
			$event->getEntity()->kill();
		}
	}

	public static function createEffectArrow(Player $player, Vector3 $position, Vector3 $speed, $yaw, $pitch, $r, $g, $b, $critical){
		$nbtTag = new Compound("", [
			"Pos" => new Enum("Pos", [new Double("", $position->getX()), new Double("", $position->getY()), new Double("", $position->getZ())]),
			"Rotation" => new Enum("Rotation", [new Float("", $yaw), new Float("", $pitch)])
		]);

		$arrow = new EffectArrow($player->chunk, $nbtTag, $r, $g, $b, $player, $critical);
		$arrow->setMotion($speed);

		$launchEvent = new ProjectileLaunchEvent($arrow);
		Server::getInstance()->getPluginManager()->callEvent($launchEvent);

		if ($launchEvent->isCancelled()) {
			$arrow->kill();
			return null;
		}else{
			return $arrow;
		}
	}
}