<?php

namespace onigo\command;

use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use onigo\Task\onigoTimeManageTask;

//use pocketmine\level\LevelManager;

use onigo\Main;

class OnigoCommand extends Command{

    /** TODO 変数の型一覧記載 @var */

    public function __construct(){

        $this->name = 'onigo';
        $this->description = 'Onigo Plugin'; //プラグインの説明
        $this->usageMessage = '/onigo [操作]'; //使い方の説明
        $this->aliases = array('oni'); //コマンドエイリアス
        parent::__construct($this->name, $this->description, $this->usageMessage, $this->aliases);

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
                    $this->oni = Main::getOni();
                    $this->armor = $this->oni->getArmorInventory();

                    //tp先の準備
                    $this->pos_array_player = Main::getTpPosition('player');

                    \var_dump($this->pos_array_player['world']);
                    $this->tp_world = Main::getPlugin()->getServer()->getLevelByName($this->pos_array_player['world']);
                    $this->pos_player = new Position($this->pos_array_player['x'],$this->pos_array_player['y'],$this->pos_array_player['z'],$this->tp_world);

                    $this->pos_array_oni = Main::getTpPosition('oni');
                    $this->pos_oni = new Position($this->pos_array_oni['x'],$this->pos_array_oni['y'],$this->pos_array_oni['z'],$this->tp_world);


                    //全員の持ち物をクリア・ゲームモードをサバイバルに設定→tp
                    foreach(Main::getPlugin()->getServer()->getOnlinePlayers() as $this->player){

                        $this->player->getInventory()->clearAll();
                        $this->player->setGamemode(0);
                        $this->player->getInventory()->setItem(1,Item::get('320',0,64));

                        //tp
                        if($this->player !== $this->oni){
                            $this->player->teleport($this->pos_player);
                        }
                        else{
                            $this->player->teleport($this->pos_oni);
                        }
                    }

                    //防具の装着
                    $this->armor->setHelmet(Item::get('314',0,1)); //帽子
                    $this->armor->setChestplate(Item::get('315',0,1)); //チェストプレート
                    $this->armor->setLeggings(Item::get('316',0,1)); //レギンス
                    $this->armor->setBoots(Item::get('317',0,1)); //靴

                    //武器装備
                    $this->oni->getInventory()->setItem(0,Item::get('276',0,1));

                    //鬼にメッセージ送信
                    $this->oni->addTitle('鬼に選ばれました！','',5, 50, 5);

                    //時間管理処理
                    $this->pos_array_home = Main::getTpPosition('home');
                    $this->home_world = Main::getPlugin()->getServer()->getLevelByName($this->pos_array_home['world']);;
                    $pos_home = new Position($this->pos_array_home['x'],$this->pos_array_home['y'],$this->pos_array_home['z'],$this->home_world);
                    $task = new onigoTimeManageTask(Main::getPlugin(),$pos_home);
                    $time = 30 * 20; //TODO 30秒後の処理になります(完成時に変更)
                    Main::getPlugin()->getScheduler()->scheduleDelayedTask($task, $time);

                    $sender->sendMessage('処理完了！！');


                break;

                case 'stop':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }
                    else{

                        $this->pos_array_home = Main::getTpPosition('home');
                        $this->home_world = Main::getPlugin()->getServer()->getLevelByName($this->pos_array_home['world']);
                        $this->pos_home = new Position($this->pos_array_home['x'],$this->pos_array_home['y'],$this->pos_array_home['z'],$this->home_world);
                        $sender->getPlayer()->teleport($this->pos_home);
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