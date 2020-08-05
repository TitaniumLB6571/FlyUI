<?php


namespace TitaniumLB\TitanFly;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat;
use TitaniumLB\TitanFly\libs\jojoe77777\FormAPI\SimpleForm;
use TitaniumLB\TitanFly\libs\JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Fly extends PluginBase implements Listener
{
    /** @var Config */
    public $myConfig;

    /** @var MessageManager */
    private $messageManager = null;

    /** @var int[] */
    public $flyingPlayers = [];



    public function onEnable() : void
    {
        $this->getLogger()->info("TitanFly Enabled");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new FlyListener($this), $this);


        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->myConfig = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $configversion = $this->myConfig->get("config-version");

        if($configversion < "1"){
            $this->getLogger()->warning("Your config is outdated! Save your messages then delete your old config to get the latest features!");
            $this->getLogger()->warning("After deleting the old config just replace your messages again.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
    }

    public function onPlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        if($this->getConfig()->get("onjoin-disable-fly") === true){
            if($player->getGamemode() === Player::CREATIVE) return;
            if($player->getAllowFlight() === true){
                $player->setFlying(false);
                $player->setAllowFlight(false);
                $player->sendMessage($this->getConfig()->get("onjoin-disable-fly-message"));
                return;
            }
        }
    }
    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        if ($this->getConfig()->get("disable-fly-on-death") === true){
            if ($player->getAllowFlight() === true){
                $player->setFlying(false);
                $player->setAllowFlight(false);
                $player->sendMessage("flight-disabled-cause-death");
            }
        }
    }

    /**
     * Set the message manager
     */
    public function setMessageManager() {
        $this->messageManager = new MessageManager($this->getSettingsProperty("messages", []));
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $prefix = $this->getConfig()->get("prefix");
        $permUserName = $sender->getName();
        $enabledFromPermUser = str_replace("{perm_user}", $permUserName, $this->getConfig()->get("Flight-Enabled-By"));
        $disabledFromPermUser = str_replace("{perm_user}", $permUserName, $this->getConfig()->get("Flight-Disabled-By"));
        switch ($command->getName()) {
            case "fly":
                if ($sender instanceof Player) {
                    if ($sender->hasPermission("fly.use.command")) {
                        if (isset($args[0])) {
                            $target = $sender->getServer()->getPlayer($args[0]);
                            if (!$target instanceof Player) {
                                $sender->sendMessage($prefix . " " . $this->getConfig()->get("Player-Not-Found"));
                                return false;
                            }
                            if ($target->getAllowFlight()) {
                                $target->setFlying(true);
                                $target->setAllowFlight(true);
                                $target->sendMessage($prefix . " " . $enabledFromPermUser);
                                $sender->sendMessage(TextFormat::RED . "Flight for " . TextFormat::GREEN . $target->getName() . TextFormat::RED . " has been enabled.");
                            } else {
                                $target->setAllowFlight(false);
                                $target->setFlying(false);
                                $target->sendMessage($prefix . " " . $disabledFromPermUser);
                                $sender->sendMessage(TextFormat::RED . "Flight for " . TextFormat::GREEN . $target->getName() . TextFormat::RED . " has been disabled.");
                                $effect = new EffectInstance(Effect::getEffect(Effect::DAMAGE_RESISTANCE) , 250 , 100 , false);
                                $target->addEffect($effect);
                            }
                            return false;
                        }
                        if ($sender->getAllowFlight()) {
                            $sender->setFlying(true);
                            $sender->setAllowFlight(true);
                            $sender->sendMessage($prefix . " " . $this->getConfig()->get("fly-enabled-message"));
                        } else {
                            $sender->setAllowFlight(false);
                            $sender->setFlying(false);
                            $sender->sendMessage($prefix . " " . $this->getConfig()->get("fly-disabled-message"));
                            $effect = new EffectInstance(Effect::getEffect(Effect::DAMAGE_RESISTANCE) , 250 , 100 , false);
                            $sender->addEffect($effect);
                        }
                        return false;

                    } else {
                        $sender->sendMessage($prefix . " " . $this->getConfig()->get("no-permission"));
                        return true;
                    }
                    break;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage("You are only allowed to use this command in-game!");
                    return false;
                }
                if ($sender instanceof Player) {
                    if($this->getConfig()->get("flyUI-enable") === true){
                        if ($sender->hasPermission("flyui.use")) {
                            $this->flyUI($sender);
                            return false;
                        }
                    } else {
                        $sender->sendMessage($prefix . " " . $this->getConfig()->get("no-permission"));
                        return true;
                    }
                    break;
                }
        }
        return true;
    }

     function flyUI($player){
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null){
                return true;
            }
            switch ($result){
                case 0:
                    $prefix = $this->getConfig()->get("prefix");
                    $player->setAllowFlight(true);
                    $player->sendMessage($prefix . " " . $this->getConfig()->get("fly-enabled-message"));
                break;

                case 1:
                    $prefix = $this->getConfig()->get("prefix");
                    $player->setAllowFlight(false);
                    $player->sendMessage($prefix . " " . $this->getConfig()->get("fly-disabled-message"));
                    $effect = new EffectInstance(Effect::getEffect(Effect::DAMAGE_RESISTANCE) , 250 , 100 , false);
                    $player->addEffect($effect);
                break;
            }
            return true;
        });
        $form->setTitle($this->getConfig()->get("title"));
        $form->setContent($this->getConfig()->get("description"));
        $form->addButton($this->getConfig()->get("button1"));
        $form->addButton($this->getConfig()->get("button2"));
        $form->sendToPlayer($player);
        return $form;
    }

	public function onDamage(EntityDamageByEntityEvent $event) : void{

        $prefix = $this->getConfig()->get("prefix");
        $entity = $event->getEntity();
		if($this->getConfig()->get("disable-fly-when-combat-tagged") === true){
			if($event instanceof EntityDamageByEntityEvent){
				if($entity instanceof Player){
					$damager = $event->getDamager();
					if(!$damager instanceof Player) return;
					if($damager->isCreative()) return;
					if($damager->getAllowFlight() === true){
						$damager->sendMessage($prefix . " " . $this->getConfig()->get("fly-in-combat-message"));
						$damager->setAllowFlight(false);
						$damager->setFlying(false);
					}
				}
			}
		}
	}

    /**
     * @param Player|string $player
     *
     * @return bool
     */
    public function isFlying($player) {
        if($player instanceof Player) $player = $player->getName();
        return isset($this->flyingPlayers[$player]);
    }

    /**
     * @return MessageManager
     */
    public function getMessageManager() {
        return $this->messageManager;
    }

    /**
     * @param string $nested
     * @param array $default
     *
     * @return mixed
     */

    public function getSettingsProperty(string $nested, $default = []) {
        return $this->getConfig()->getNested($nested, $default);
    }
}