<?php

namespace Khinenw\Archer;

use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\DustParticle;
use pocketmine\nbt\tag\Compound;

class EffectArrow extends Arrow{
	private $color = [];

	public function __construct(FullChunk $chunk, Compound $nbt,  $r = 255, $g = 255, $b = 255, Entity $shootingEntity = null, $critical = false){
		parent::__construct($chunk, $nbt, $shootingEntity, $critical);
		$this->color["r"] = $r;
		$this->color["g"] = $g;
		$this->color["b"] = $b;
	}

	public function onUpdate($currentTick){
		parent::onUpdate($currentTick);
		$this->getLevel()->addParticle(new DustParticle($this->getPosition(), $this->color["r"], $this->color["g"], $this->color["b"]));
	}
}