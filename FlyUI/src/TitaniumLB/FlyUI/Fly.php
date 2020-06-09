<?php

/*  
 * / === \  (_)  / === \    /\     |\   |
 *   | |    | |    | |     /  \    | \  |
 *   | |    | |    | |    /====\   |  \ |
 *   | |    | |    | |   /      \  |   \|
 * ©Plugin made by TitaniumLB
 */

namespace TitaniumLB\FlyUI;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Fly extends PluginBase implements Listener
{
    public function onLoad() : void
    {
        $this->getLogger()->info("Loading FlyUI");
    }

    public function onEnable() : void
    {
        $this->getLogger()->info("FlyUI Enabled");
    }

    public function onDisable() : void
    {
        $this->getLogger()->info("Disabled FlyUI");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
        	case "flyui":
        		$sender->sendMessage("§8----------------------------------\n§8»§6This plugin was made by TitaniumLB\n§8»§6Subscribe to my YT channel ;D\n§8----------------------------------");
        		break;
            case "fly":
                if ($sender instanceof Player) {
                    if ($sender->hasPermission("flyui.use")) {
                        $this->openMyForm($sender);
                    } else {
                        $sender->sendMessage("§cFly§8» §4You do not have permission to use this command.");
                        return true;
                    }
                    break;
                }
        }
        return true;
    }

     function openMyForm($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null){
                return true;
            }
            switch ($result){
                case 0:
                    $player->setAllowFlight(true);
                    $player->sendMessage("§cFly§8» §aEnabled");
                break;

                case 1:
                    $player->setAllowFlight(false);
                    $player->sendMessage("§cFly§8» §4Disabled");
                break;
            }
            return true;
        });
        $form->setTitle("§8»§cFlyUI§8«§r");
        $form->setContent("§8» §aEnable §ror §cdisable §rflight.");
        $form->addButton("§aEnable");
        $form->addButton("§cDisable");
        $form->sendToPlayer($player);
        return $form;
    }
}