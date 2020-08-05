<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2020/07/28
 * Time: 21:53
 */

namespace TitaniumLB\TitanFly;

use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class FlyListener implements Listener
{
    /** @var Fly */
    private $plugin = null;


    /** @var array */
    protected $bannedCommands = [];

    public function __construct(Fly $plugin){
        $this->plugin = $plugin;
        $this->bannedCommands = array_map("strtolower", $plugin->getSettingsProperty("banned-commands", []));

    }


    /**
     * @param PlayerCommandPreprocessEvent $event
     *
     * @priority HIGHEST
     *
     * @ignoreCancelled true
     */
    public function onCommandPreProcess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        if($this->plugin->isFlying($player)) {
            $message = $event->getMessage();
            if(strpos($message, "/") === 0) {
                $args = array_map("stripslashes", str_getcsv(substr($message, 1), " "));
                $label = "";
                $target = $this->plugin->getServer()->getCommandMap()->matchCommand($label, $args);
                if($target instanceof Command and in_array(strtolower($label), $this->bannedCommands)) {
                    $event->setCancelled();
                    $player->sendMessage($this->plugin->getMessageManager()->getMessage("player-run-banned-command"));
                }
            }
        }
    }
}