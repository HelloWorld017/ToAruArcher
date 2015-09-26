<?php

namespace Khinenw\Archer;

use pocketmine\event\entity\ProjectileHitEvent;

interface ArrowHandler{
	public function handle(ProjectileHitEvent $event);
}
