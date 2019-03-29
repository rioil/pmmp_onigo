<?php
namespace onigo\Task;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\level\Position;
use pocketmine\item\Item;

use onigo\Main;

class onigoTimeManageTask extends Task{
  private $owner;
  private $pos_home;

  public function __construct(Plugin $owner, Position $pos_home) {
    $this->owner = $owner;
    $this->pos_home = $pos_home;
  }

  public function onRun(int $ticks) {

    $this->getHandler()->cancel(); //タスクを終了(スケジューラーを止める)
    $this->owner->getServer()->getOnlinePlayers(); //サーバーインスタンスを利用する場合は、$this->owner変数から、getServer()を行なってください

    //全員をHOMEにtp
        foreach(Main::getPlugin()->getServer()->getOnlinePlayers() as $this->player){

            $this->player->getInventory()->clearAll();
            $this->player->setGamemode(1);
            $this->player->getInventory()->setItem(1,Item::get('322',0,1));

            $this->player->teleport($this->pos_home);

            $this->player->addTitle('試合終了！','',5, 50, 5);
        }
    }
}
