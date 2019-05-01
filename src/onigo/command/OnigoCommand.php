<?php

namespace onigo\command;

use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use onigo\Task\onigoTimeManageTask;
use pocketmine\Player;

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

                    //鬼を決定
                    if(!Main::getFlag()){

                        if(Main::setOni()){
                            $sender->sendMessage('鬼を決定しました');
                        }
                        else{
                            $sender->sendMessage('オンラインプレイヤーがいません');
                            break;
                        }
                    }
                    else{
                        $sender->sendMessage("【試合中】\n新しく試合を開始する前に\n現在の試合を終了する必要があります");
                        break;
                    }

                    //鬼の準備
                    $this->oni = Main::getOni();

                    //tp先の準備
                    $this->pos_player = Main::getTpPosition('player');
                    $this->pos_oni = Main::getTpPosition('oni');

                    //全員の持ち物をクリア・ゲームモードをサバイバルに設定→tp
                    foreach (Main::getPlaying() as $this->player){

                        $this->player->getInventory()->clearAll();
                        $this->player->setGamemode(0);
                        $this->player->getInventory()->setItem(1,Item::get('320',0,64));
                        $this->player->setSpawn(Main::getTpPosition('athletic'));

                        //tp
                        if(in_array($this->player,$this->oni)){
                            $this->player->teleport($this->pos_oni);
                        }
                        else{
                            $this->player->teleport($this->pos_player);
                        }
                    }

                    //鬼の準備
                    foreach($this->oni as $oni){

                        Main::initOni($oni);
                    }

                    //時間管理処理
                    $task = new onigoTimeManageTask(Main::getPlugin());
                    $time = 90 * 20; //TODO 60秒後の処理になります(完成時に変更)
                    Main::getPlugin()->getScheduler()->scheduleDelayedTask($task, $time);

                    $sender->sendMessage('処理完了！！');


                break;

                case 'stop':

                    if(!$sender->hasPermission('onigo.command.manage')){

                      $sender->sendMessage('コマンドの実行権限がありません');
                      break;
                    }
                    else{

                        if(Main::getFlag()){

                            Main::stopMatch();
                        }
                        else{
                            $sender->sendMessage('現在試合は行われていません');
                        }
                    }

                break;

                case 'oni':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

                    //鬼を設定
                    /* if(Main::setOni()){
                        //完了メッセージ
                        $sender->sendMessage('鬼を決定しました');
                    }
                    else{
                        $sender->sendMessage('オンラインプレイヤーがいません');
                    } */
                    $sender->sendMessage('無効化中');

                break;

                case 'suniiku':

                    if(!$sender->hasPermission('onigo.command.manage')){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }
                    else{

                        //可視・不可視のトグル切り替え
                        if(Main::getNametagStatus()){

                            $new_status = false;
                            $sender->sendMessage('ネームタグを不可視化しました');
                        }
                        else{

                            $new_status = true;
                            $sender->sendMessage('ネームタグを可視化しました');
                        }

                        Main::setNametagStatus($new_status);

                        //nametagの可視・不可視切り替え
                        if($new_status){

                            foreach (Main::getPlugin()->getServer()->getOnlinePlayers() as $player){

                                $player->setNameTag($player->getName());
                            }
                        }
                        else{

                            foreach (Main::getPlugin()->getServer()->getOnlinePlayers() as $player){

                                $player->setNameTag('');
                            }
                        }
                    }

                break;

                case 'ridatu':

                    if(!$sender->hasPermission('onigo.command.play') || !($sender instanceof Player)){
                        $sender->sendMessage('コマンドの実行権限がありません');
                        break;
                    }

                    //鬼の離脱を禁止
                    $player = $sender->getPlayer();
                    if(in_array($player,Main::getOni())){

                        $sender->sendMessage('鬼は離脱できません');
                        break;
                    }

                    //持ち物をクリアしクリエイティブモードに変更
                    $player->getInventory()->clearAll();
                    $armor = $player->getArmorInventory();
                    $armor->clearAll();
                    $player->setGamemode(1);
                    //金リンゴを付与
                    $player->getInventory()->setItem(1,Item::get('322',0,1));

                    //effectをすべて除去
                    $player->removeAllEffects();

                    //tp
                    $player->teleport(Main::getTpPosition('home'));
                    $sender->sendMessage('離脱しました');

                break;

                default:
                    $sender->sendMessage("引数が不正です\n/onigoで使い方を確認できます");
                break;

            }
        }
        else{
            //引数がなかったとき使い方の表示
            $sender->sendMessage('：：：：：使い方：：：：：');
            $sender->sendMessage('start:鬼ごっこ開始');
            $sender->sendMessage('stop:鬼ごっこ強制終了');
            $sender->sendMessage('未実装　oni:鬼の数を指定');
            $sender->sendMessage('suniiku:ネームタグを非表示にする');
        }


        return true;

    }
}