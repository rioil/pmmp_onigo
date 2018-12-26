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

    //TODO 生成処理　プラグインの設定ファイル
    private $config;

    //設定項目の配列
    private static $worlds = array('home','onigo','athletic');
    private static $default_worlds = array('world','onigo','athletic');

    private static $positions = array('home_tp','player_tp','oni_tp');
    private static $default_positions = array(array('x' => 0,'y' => 70,'z' => 0),array('x' => 0,'y' => 70,'z' => 0),array('x' => 30,'y' => 70,'z' => 30));

    //plugin読み込み時に実行
    public function onLoad(){

        //設定ファイル保存場所作成
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }

        $this->getLogger()->info('Checking Config!');
        //設定ファイルの作成
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        var_dump($this->config);
        //コンフィグのチェック
        $this->checkConfig();

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

    //TODO config check !debug!
    private function checkConfig(){

        //tp先ワールド名のチェック
        foreach (self::$worlds as $key => $item) {

            var_dump($item);
            if(!$this->config->exists($item) || (trim($this->config->get($item)) === '')){

                $default = self::$default_worlds[$key];
                $this->config->set($item, $default);

            }
        }

        foreach (self::$positions as $key => $item) {

            //各tp地点の座標チェック
            if($this->config->exists($item)){

                $this->vector = array('x','y','z');

                foreach($this->vector as $xyz){

                    //xyzを順番に調べる
                    if(isset($item[$xyz])){

                        //座標が正しく指定されていることを確認
                        if(!preg_match("/^[0-9]+$/",$item[$xyz])){

                            //不正な値であればデフォルト値をセット
                            $this->config->set($item, self::$default_positions[$key]);
                            //修正したらチェック終了
                            break;
                        }
                    }
                }
            }
            else{
                //項目が存在しなければデフォルト値をセット
                $this->config->set($item, self::$default_positions[$key]);
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

    //tp先の取得
    public static function getTpPosition(string $group)
    {
        switch ($group){

            case 'player':
                $pos_array = self::$config->get('player_tp');
                $pos_array['world'] = self::$config->get('onigo');
                return $pos_array;

            case 'oni':
                $pos_array = self::$config->get('oni_tp');
                $pos_array['world'] = self::$config->get('onigo');
                return $pos_array;

            default:
                return false;
        }
    }
}