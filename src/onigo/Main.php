<?php

namespace onigo;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener{

    //このクラスを格納
    private static $plugin;

    //プレイヤーの配列(Player Object Array)
    private static $playing;

    //鬼プレイヤーの配列(Player Object)
    private static $oni;

    //鬼ごっこ実行中フラグ
    private static $playing_flag;

    //プラグインの設定ファイル
    private static $config;

    //設定項目の配列
    private static $worlds = array('home','onigo','athletic');
    private static $default_worlds = array('world','onigo','athletic');

    private static $positions = array('home_tp','player_tp','oni_tp','athletic_tp');
    private static $default_positions = array(array('x' => 0,'y' => 5,'z' => 0),array('x' => 0,'y' => 5,'z' => 0),array('x' => 100,'y' => 5,'z' => 100),array('x' => 100,'y' => 5,'z' => 100));

    //tp先ポジション
    private static $pos = array();

    //plugin読み込み時に実行
    public function onLoad(){

        //設定ファイル保存場所作成
        if(!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }

        //設定ファイルの作成
        self::$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        \var_dump(self::$config);

        //コンフィグのチェック
        $this->getLogger()->info('Checking Config!');
        $this->checkConfig();
        $this->getLogger()->info('Config check completed!');

        //コマンド処理クラスの指定
        $this->class = '\\onigo\\command\\OnigoCommand'; //作成したクラスの場所(srcディレクトリより相対)
        $this->getServer()->getCommandMap()->register('OnigoCommand', new $this->class);

        //コマンドクラスでgetDatafolderを使うため
        self::$plugin = $this;

        //試合状態判定フラグ初期化
        self::$playing_flag = false;

        $this->getLogger()->info('Loaded!');
    }

    //pluginが有効になった時に実行
    public function onEnable(){

        $this->getServer()->getPluginManager()->registerEvents($this,$this); //イベント登録

        //ワールド読み込み
        foreach (self::$worlds as $this->world) {
          $this->load_world = trim(self::$config->get($this->world));
          $this->getServer()->loadLevel($this->load_world);
        }

        //TODO tp先ポジションの作成・べた書きどうにかならないかな

          //HOME
          $pos_array = self::$config->get('home_tp');
          $pos_array['world'] = self::$config->get('home');
          $tp_world = $this->getServer()->getLevelByName($pos_array['world']);
          self::$pos['home'] = new Position($pos_array['x'],$pos_array['y'],$pos_array['z'],$tp_world);

          //PLAYER
          $pos_array = self::$config->get('player_tp');
          $pos_array['world'] = self::$config->get('onigo');
          $tp_world = $this->getServer()->getLevelByName($pos_array['world']);
          self::$pos['player'] = new Position($pos_array['x'],$pos_array['y'],$pos_array['z'],$tp_world);

          //ONI
          $pos_array = self::$config->get('oni_tp');
          $pos_array['world'] = self::$config->get('onigo');
          $tp_world = $this->getServer()->getLevelByName($pos_array['world']);
          self::$pos['oni'] = new Position($pos_array['x'],$pos_array['y'],$pos_array['z'],$tp_world);

          //ATHLETIC
          $pos_array = self::$config->get('athletic_tp');
          $pos_array['world'] = self::$config->get('athletic');
          $tp_world = $this->getServer()->getLevelByName($pos_array['world']);
          self::$pos['athletic'] = new Position($pos_array['x'],$pos_array['y'],$pos_array['z'],$tp_world);

        $this->getLogger()->info('Ready!');
    }

    //プレイヤーが入ったらコンフィグの生成
    public function onPlayerJoin(PlayerJoinEvent $event){

    }

    //プレイヤーが鯖から抜けた時にチーム人数に反映
    public function onPlayerQuit(PlayerQuitEvent $event){

        //抜けたプレイヤーを取得
        $this->player = $event->getPlayer();

    }

    //このクラスのインスタンス取得
    public static function getPlugin(){
        return self::$plugin;
    }

    private function checkConfig(){

        //tp先ワールド名のチェック
        foreach (self::$worlds as $this->key => $this->tp_dest) {

            \var_dump($this->key);
            \var_dump($this->tp_dest);
            //ワールド名が不正でないか確認
            if(!self::$config->exists($this->tp_dest) || (trim(self::$config->get($this->tp_dest)) == null)){

                $this->default = self::$default_worlds[$this->key];
                self::$config->set($this->tp_dest, $this->default);
                self::$config->save();

            }

            //ワールドが存在しなければ生成
            $this->checking_world = trim(self::$config->get($this->tp_dest));
            if(!$this->getServer()->isLevelGenerated($this->checking_world)){

                $this->getServer()->generateLevel($this->checking_world, null, "pocketmine\level\generator\Flat", ["preset" => "2;7,2x3,2;1;"]);
                $this->getLogger()->info('ワールド' . $this->checking_world . 'を新たに生成しました');
            }
        }

        //各tp地点の座標チェック
        //TODO 地面に埋まることを回避,大きすぎる値・負数を自動修正する
        $this->vector = array('x','y','z');

        foreach (self::$positions as $this->key => $this->tp_dest) {

            if(self::$config->exists($this->tp_dest)){

                $check_coordinates = self::$config->get($this->tp_dest);

                foreach($this->vector as $this->xyz){

                    //xyzを順番に調べる
                    if(isset($check_coordinates[$this->xyz])){

                        //座標が正しく指定されていることを確認
                        if(!is_int($check_coordinates[$this->xyz])){

                            //不正な値であればデフォルト値をセット
                            $this->getLogger()->info($this->tp_dest . 'の' . $this->xyz . 'は不正です1');
                            self::$config->set($this->tp_dest, self::$default_positions[$this->key]);
                            self::$config->save();
                            \var_dump($this->key);
                            //修正したらチェック終了
                            break;
                        }
                        $this->getLogger()->info($this->tp_dest . 'の' . $this->xyz . 'は通過です1');
                    }
                    else{

                        //設定されていなければデフォルト値をセット
                        self::$config->set($this->tp_dest, self::$default_positions[$this->key]);
                        self::$config->save();
                        \var_dump($this->key);
                        $this->getLogger()->info($this->tp_dest . 'の' . $this->xyz . 'は不正です2');
                        break;
                    }
                }
            }
            else{
                //項目が存在しなければデフォルト値をセット
                self::$config->set($this->tp_dest, self::$default_positions[$this->key]);
                self::$config->save();
                $this->getLogger()->info($this->tp_dest . 'は不正です3');
            }

            //TODO ネームタグ設定
            if(self::$config->exists('nametag')){
                
            }
        }

        return true;
    }

    //TODO チームプレイヤーにメッセージ送信
    public static function sendMessageTeamPlayer(string $team, string $message){

        if($team != NULL){

            foreach(self::getPlugin()->getServer()->getOnlinePlayers() as $players){

                //TODO 未実装の関数　self::getPlayerConfig
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
        self::$playing = self::getPlugin()->getServer()->getOnlinePlayers();

        //人数をカウント
        $population = count($players);

        if($population !== 0){
            //配列の何番目のプレイヤーを鬼にするか決める
            $n = random_int(0,$population - 1);
            \var_dump($n);
            self::$oni = current(array_slice($players, $n, 1, true));
            \var_dump(self::$oni);

            return true;
        }
        else return false;
    }

    //鬼ごっこ参加者を取得
    public static function getPlaying(){

        return self::$playing;

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
          case 'oni':
          case 'home':
          case 'athletic':

            return self::$pos[$group];

          default:

            return false;
        }
    }

    //試合中に設定
    public static function setFlag(bool $flag){
        self::$playing_flag = $flag;
    }

    //試合中か確認
    public static function getFlag(){
        return self::$playing_flag;
    }

    //試合終了処理
    public static function stopMatch(){

        $pos_home = self::getTpPosition('home');

        //全員をHOMEにtp
        foreach(self::getPlaying() as $player){

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
            $player->teleport($pos_home);

            $player->addTitle('試合終了！','',5, 50, 5);
        }
    }

    //pocketmine-multitp-pluginのコピペ・要修正
    public function playerBlockTouch(PlayerInteractEvent $event){

      //タッチされたものがダイヤブロックか確認
      if($event->getBlock()->getID() == 57){

        //$tp_world = Main::getPlugin()->getServer()->getLevelByName($pos_array_player['world']);
        //self::$pos_player = new Position($pos_array_player['x'],$pos_array_player['y'],$pos_array_player['z'],$this->tp_world);
        $event->getPlayer()->teleport();
        $event->getPlayer()->sendMessage("Teleporting...");
      }
    }
}