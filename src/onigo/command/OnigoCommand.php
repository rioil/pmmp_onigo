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
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\level\Position;

use onigo\Main;

class OnigoCommand extends Command{

    /** TODO:変数の型一覧記載 @var */

    public function __construct(){

        $name = 'onigo';
        $description = 'Onigo Plugin'; //プラグインの説明
        $usageMessage = '/onigo [操作]'; //使い方の説明
        $aliases = array('oni'); //コマンドエイリアス
        parent::__construct($name, $description, $usageMessage, $aliases);

    }

    public function execute(CommandSender $sender, string $label, array $args) : bool {

        //パーミッションチェックをそれぞれ追加
        if(isset($args[0])){

            switch (strtolower($args[0])){

                case 'start':

                //need bug fix

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

                    //鬼が指定されていなければ指定
                    if(empty(Main::getOni()) === true){

                        if(Main::setOni()){
                            $sender->sendMessage('鬼を自動的に指定しました');
                        }
                        else{
                            $sender->sendMessage('オンラインプレイヤーがいません');
                            break;
                        }

                    }

                    //鬼の準備
                    $oni = Main::getOni();
                    $armor = $oni->getArmorInventory();

                    //tp先の準備
                    $pos_array = Main::getTpPosition('player');
                    $pos_player = new Position($pos_array['x'],$pos_array['y'],$pos_array['z'],$pos_array['world']);


                    //全員の持ち物をクリア・ゲームモードをサバイバルに設定→tp
                    foreach(Main::getPlugin()->getServer()->getOnlinePlayers() as $player){

                        $player->getInventory()->clearAll();
                        $player->setGamemode(0);

                        //tp
                        if($player !== $oni){
                            $player->teleport($pos_player);
                        }
                        else{
                            $player->teleport($pos_oni);
                        }
                    }

                    //防具の装着
                    $armor->setHelmet(Item::get('314',0,1)); //帽子
                    $armor->setChestplate(Item::get('315',0,1)); //チェストプレート
                    $armor->setLeggings(Item::get('316',0,1)); //レギンス
                    $armor->setBoots(Item::get('317',0,1)); //靴

                    //武器装備
                    $oni->getInventory()->setItem(0,Item::get('276',0,1));

                    //鬼にメッセージ送信
                    $oni->addTitle('鬼に選ばれました！','',5, 50, 5);


                break;

                case 'stop':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

                break;

                case 'oni':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

                    //鬼を設定
                    if(Main::setOni()){
                        //完了メッセージ
                        $sender->sendMessage('鬼を決定しました');
                    }
                    else{
                        $sender->sendMessage('オンラインプレイヤーがいません');
                    }

                break;

                case 'suniiku':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

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