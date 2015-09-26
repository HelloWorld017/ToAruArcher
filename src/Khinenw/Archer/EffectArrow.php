<?php

namespace Khinenw\Archer;

use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\DustParticle;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;

class EffectArrow extends Arrow{

	const NETWORK_ID = 172;

	public function __construct(FullChunk $chunk, Compound $nbt,  $r = 255, $g = 255, $b = 255, Entity $shootingEntity = null, $critical = false){
		parent::__construct($chunk, $nbt, $shootingEntity, $critical);
		if(!isset($this->namedtag["r"])) $this->namedtag["r"] = new Int("r", $r);
		if(!isset($this->namedtag["g"])) $this->namedtag["g"] = new Int("g", $g);
		if(!isset($this->namedtag["b"])) $this->namedtag["b"] = new Int("b", $b);
	}

	public function onUpdate($currentTick){
		parent::onUpdate($currentTick);
		$this->getLevel()->addParticle(new DustParticle($this->getPosition(), $this->namedtag["r"], $this->namedtag["g"], $this->namedtag["b"]));
		/*if($this->onGround){
			$this->kill();
		}*/
	}

	public function canCollideWith(Entity $entity){
		return ($entity instanceof Living) && (!$this->onGround) && ($entity->getId() !== $this->getDataProperty(self::DATA_SHOOTER_ID));
	}
}