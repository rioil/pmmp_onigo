<?php
namespace onigo\Task;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

use onigo\Main;

class onigoTimeManageTask extends Task{
  private $owner;

  public function __construct(Plugin $owner) {
    $this->owner = $owner;
  }

  public function onRun(int $ticks) {

    $this->getHandler()->cancel(); //タスクを終了(スケジューラーを止める)
    Main::stopMatch(); //試合終了処理

  }
}
