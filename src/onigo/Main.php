<?php

namespace onigo;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;

class Main extends PluginBase implements Listener{

    //このクラスを格納
    private static $plugin;


    //plugin読み込み時に実行
    public function onLoad(){

        //設定ファイル保存場所作成
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
        //プレイヤーファイルの保存場所作成
        if(!file_exists($this->getDataFolder() . 'players')){
            @mkdir($this->getDataFolder() . 'players');
        }
        //チームファイルの保存場所作成
        if(!file_exists($this->getDataFolder() . 'teams')){
            @mkdir($this->getDataFolder() . 'teams');
        }
        
        //コマンド処理クラスの指定
        $class = '\\teamcolor\\command\\OnigoCommand'; //作成したクラスの場所(srcディレクトリより相対)
        $this->getServer()->getCommandMap()->register('OnigoCommand', new $class);

        //コマンドクラスでgetDatafolderを使うため
        self::$plugin = $this;

        $this->getLogger()->info('Loaded!');
    }

    //pluginが有効になった時に実行
    public function onEnable(){

        $this->getServer()->getPluginManager()->registerEvents($this,$this); //イベント登録
        $this->getLogger()->info('Ready!');
    }

    //プレイヤーが入ったらコンフィグの生成
    public function onPlayerJoin(PlayerJoinEvent $event){

    }

    //プレイヤーが鯖から抜けた時にチーム人数に反映
    public function onPlayerQuit(PlayerQuitEvent $event){

        //抜けたプレイヤーを取得
        $player = $event->getPlayer();  

    }

    //このクラスのインスタンス取得
    public static function getPlugin(){
        return self::$plugin;
    }

    //TODO チームプレイヤーにメッセージ送信
    public static function sendMessageTeamPlayer(string $team, string $message){

        if($team != NULL){

            foreach(self::getPlugin()->getServer()->getOnlinePlayers() as $players){

                $players_config = self::getPlayerConfig($players->getName());
                $players_team = $players_config->get('team');

                if($players_team === $team){
                    //メッセージを送信
                    $players->sendMessage($message);
                }
            }
        }
    }
}