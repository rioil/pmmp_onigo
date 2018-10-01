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

    //鬼プレイヤーの配列
    private static $oni; 

    //プラグインの設定ファイル
    private $config;

    //設定項目の配列
    private static $settings = array('home_world','onigo_world','athletic_world');
    private static $default_value = array('world','onigo','athletic');

    //plugin読み込み時に実行
    public function onLoad(){

        //設定ファイル保存場所作成
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }

        //設定ファイルの作成
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        //初期化
        $this->initializeConfig();
        
        //コマンド処理クラスの指定
        $class = '\\onigo\\command\\OnigoCommand'; //作成したクラスの場所(srcディレクトリより相対)
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

    //configセットアップ
    public function initializeConfig(){

        //項目が正しく設定されていなければ初期値をセット
        foreach ($this->settings as $key => $item) {

            if(!$this->config->exists($item) || ($this->config->get($item) == NULL)){
                $this->config->set($item, $this->default_value[$key]);
            }
        }
        
        return true;
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

    //鬼を設定
    public static function setOni() :bool{

        //オンラインプレイヤーの配列取得
        $players = self::getPlugin()->getServer()->getOnlinePlayers();

        //人数をカウント
        $population = count($players);

        if($population !== 0){
            //配列の何番目のプレイヤーを鬼にするか決める
            $n = random_int(0,$population - 1);
            var_dump($n);
            self::$oni = current(array_slice($players, $n, 1, true));
            var_dump(self::$oni);
        
            return true;
        }
        else return false;
    }

    //鬼を取得
    public static function getOni(){
            
        return self::$oni;
        
    }
}