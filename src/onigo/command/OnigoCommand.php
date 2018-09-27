<?php

namespace onigo\command;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;
use onigo\Main;

class TeamCommand extends Command{

    /** TODO:変数の型一覧記載 @var */

    public function __construct(){

        $name = 'onigo';
        $description = 'Onigo Plugin'; //プラグインの説明
        $usageMessage = '/onigo [操作]'; //使い方の説明
        $aliases = array('oni'); //コマンドエイリアス
        parent::__construct($name, $description, $usageMessage, $aliases);

        $permission = 'onigo.command'; //パーミッションノード
        $this->setPermission($permission);
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool {

        if(isset($args[0])){

            switch (strtolower($args[0])){
    
                case 'start':
                    
                break;

                case 'stop':
                    
                break;

                case 'oni':
                    
                break;

                case 'suniiku':
                    
                break;

            }
        }
        else{
            //引数がなかったとき使い方の表示
            $sender->sendMessage('：：：：：使い方：：：：：');
            $sender->sendMessage('start:鬼ごっこ開始');
            $sender->sendMessage('stop:鬼ごっこ強制終了');
            $sender->sendMessage('oni:鬼の数を指定');
            $sender->sendMessage('suniiku:ネームタグを非表示にする');
        }
        

        return true;

    }
}