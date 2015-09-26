<?php

namespace Khinenw\Archer;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use Khinenw\AruPG\ToAruPG;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\Player;

class SkillLightningShot implements Skill{

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		$this->player = $player;
		$this->level = 1;
	}

	public static function __init(){
		Archery::$arrowHandlers[HandleLightning::HANDLER_NAME] = new HandleLightning();
	}

	public static function canBeAcquired(RPGPlayer $player){
		return (($player->getCurrentJob()->getId() === JobArcher::getId()) && ($player->getStatus()->level >= 40));
	}

	public function canInvestSP($sp){
		if($this->level + $sp <= 5) return true;

		return false;
	}

	public static function getId(){
		return Archery::ARCHER_ID_BASE + 9;
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
			0,
			255,
			255,
			false
		);

		$arrow->namedtag["Handler"] = new String("Handler", HandleLightning::HANDLER_NAME);
		$arrow->namedtag["ArcheryDamage"] = new Double("ArcheryDamage", 0);
		$arrow->namedtag["Custom"] = new Int("Custom", 2);
		$event->getPlayer()->getLevel()->addEntity($arrow);
		$arrow->spawnToAll();

		return true;
	}

	public function getRequiredMana(){
		return 250 + $this->level * 5;
	}

	public static function getRequiredLevel(){
		return 80;
	}

	public static function getName(){
		return "LIGHTNING_ARROW";
	}

	public static function getItem(){
		return Item::get(Item::REDSTONE, 0, 1);
	}

	public function getLevel(){
		return $this->level;
	}

	public function investSP($sp){
		$this->level += $sp;
	}

	public function getDamage(){
		return ($this->player->getCurrentJob()->getAdditionalBaseDamage($this->player) +
				$this->player->getCurrentJob()->getBaseDamage($this->player)) *
				(3 + ($this->level / 10));
	}

	public function getFireTick(){
		return $this->level * 100;
	}

	public function getSkillDescription(){
		$text = ToAruPG::getTranslation("LIGHTNING_ARROW_DESC") . "\n" .
			ToAruPG::getTranslation("CURRENT_LEVEL") . "\n" .
			ToAruPG::getTranslation("LIGHTNING_DAMAGE", "3" . $this->level . "0%") . "\n" .
			ToAruPG::getTranslation("LIGHTNING_FIRE_TERM", 5 * $this->level) . "\n" .
			ToAruPG::getTranslation("MANA_USE", (250 + (($this->getLevel()) * 5))) . "\n";

		if($this->canInvestSP(1)){
			$text .= ToAruPG::getTranslation("NEXT_LEVEL"). ":" . "\n" .
				ToAruPG::getTranslation("LIGHTNING_DAMAGE", "3" . ($this->level + 1) . "0%") . "\n" .
				ToAruPG::getTranslation("LIGHTNING_FIRE_TERM", 5 * ($this->level + 1)) . "\n" .
				ToAruPG::getTranslation("MANA_USE", (250 + (($this->getLevel() + 1) * 5)));
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

class HandleLightning implements ArrowHandler{

	const EXPLOSION_SIZE = 5;
	const HANDLER_NAME = "LightningHandler";

	public function handle(ProjectileHitEvent $event){
		$shootingEntity = $event->getEntity()->shootingEntity;
		if(!$shootingEntity instanceof Player) return;

		$rpgPlayer = ToAruPG::getInstance()->getRPGPlayerByName($shootingEntity->getName());
		if($rpgPlayer === null) return;

		$skill = $rpgPlayer->getSkillById(SkillLightningShot::getId());
		if(!$skill instanceof SkillLightningShot) return;

		$pos = $event->getEntity();

		$aedPk = new AddEntityPacket();
		$aedPk->eid = Entity::$entityCount++;
		$aedPk->type = 93;
		$aedPk->x = $pos->getX();
		$aedPk->y = $pos->getY();
		$aedPk->z = $pos->getZ();
		$aedPk->speedX = 0;
		$aedPk->speedY = 0;
		$aedPk->speedZ = 0;
		$aedPk->yaw = 0;
		$aedPk->pitch = 0;
		$aedPk->metadata = [];

		$expPk = new ExplodePacket();
		$expPk->x = $pos->getX();
		$expPk->y = $pos->getY();
		$expPk->z = $pos->getZ();
		$expPk->radius = self::EXPLOSION_SIZE;

		$pos->getLevel()->addChunkPacket($pos->chunk->getX(), $pos->chunk->getZ(), $aedPk);
		$pos->getLevel()->addChunkPacket($pos->chunk->getX(), $pos->chunk->getZ(), $expPk);


		$aabb = new AxisAlignedBB(
			$pos->getX() - self::EXPLOSION_SIZE - 1,
			$pos->getY() - self::EXPLOSION_SIZE - 1,
			$pos->getZ() - self::EXPLOSION_SIZE - 1,
			$pos->getX() + self::EXPLOSION_SIZE + 1,
			$pos->getY() + self::EXPLOSION_SIZE + 1,
			$pos->getZ() + self::EXPLOSION_SIZE + 1
		);

		foreach($pos->getLevel()->getNearbyEntities($aabb, $shootingEntity) as $e){
			if($e instanceof Player && !ToAruPG::$pvpEnabled) continue;
			$e->attack($skill->getDamage(), new EntityDamageByEntityEvent($shootingEntity, $e, EntityDamageByEntityEvent::CAUSE_CUSTOM, $skill->getDamage()));
			$e->setOnFire($skill->getFireTick());
		}
	}
}
