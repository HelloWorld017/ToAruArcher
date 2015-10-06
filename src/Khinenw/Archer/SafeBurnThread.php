<?php

namespace Khinenw\Archer;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class SafeBurnThread extends PluginTask{

	/**
	 * @var Player
	 */
	private $whose;
	private $target;
	private $tick;
	private $firstTick = -1;

	public function __construct(Entity $target, Player $whose, $tick){
		parent::__construct(Archery::getInstance());
		$this->tick = $tick;
		$this->whose = $whose;
		$this->target = $target;
	}

	public function onRun($currentTick){
		if($this->firstTick === -1){
			$flags = (int) $this->target->getDataProperty(Player::DATA_FLAGS);
			$flags ^= 1 << Player::DATA_FLAG_ONFIRE;
			$dataProperty = [Player::DATA_FLAGS => [Player::DATA_TYPE_BYTE, $flags]];

			$pk = new SetEntityDataPacket();
			$pk->eid = $this->target->getId();
			$pk->metadata = $dataProperty;

			Server::broadcastPacket($this->whose->getLevel()->getPlayers(), $pk);

			$this->firstTick = $currentTick;
		}

		if($this->firstTick - $this->tick <= 0){
			$this->getHandler()->cancel();
		}

		$this->target->attack(2, new EntityDamageByEntityEvent($this->whose, $this->target, EntityDamageByEntityEvent::CAUSE_FIRE_TICK, 2, 0));
	}

	public function onCancel(){
		$flags = (int) $this->target->getDataProperty(Player::DATA_FLAGS);
		$dataProperty = [Player::DATA_FLAGS => [Player::DATA_TYPE_BYTE, $flags]];

		$pk = new SetEntityDataPacket();
		$pk->eid = $this->target->getId();
		$pk->metadata = $dataProperty;

		Server::broadcastPacket($this->target->getLevel()->getPlayers(), $pk);
	}
}
