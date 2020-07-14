<?php

namespace mohagames\localchat;

use _64FF00\PureChat\PureChat;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\utils\Config;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;


class Main extends PluginBase implements Listener{

    public $config;

    public function onEnable(): void
    {
        $this->config = new Config($this->getDataFolder() . "localchat.yml", Config::YAML, array("global-suffix" => "!!","global-prefix" => "[GLOBAL] {player} > {msg}","distance" => 15, "toggle" => false));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()){
            case "localchat":
            case "lc":
                if(isset($args[0])){
                    switch($args[0]){
                        case "setdistance":
                            if($sender->hasPermission("lc.cmd.setdistance")) {
                                if (isset($args[1])) {
                                    $distance = $args[1];
                                    if (is_numeric($distance)) {
                                        $this->config->set("distance", (int)$distance);
                                        $this->config->save();
                                        $sender->sendMessage("§aDe chat afstand is succesvol ingesteld op §2" . $args[1] . " §ablocks.");
                                    } else {
                                        $sender->sendMessage("§4De gegeven afstand is geen nummer.");
                                    }
                                } else {
                                    $sender->sendMessage("§4Gelieve de afstand te specifieren.");
                                }
                            }
                            else{
                                $sender->sendMessage("§4U bent niet bevoegd om deze command te gebruiken.");
                            }
                            break;

                        case "toggle":
                            if($sender->hasPermission("lc.cmd.toggle")) {
                                if ($this->config->get("toggle")) {
                                    $this->config->set("toggle", false);
                                    $this->config->save();
                                    $sender->sendMessage("§aLocalchat is uitgeschakeld.");
                                } else {
                                    $this->config->set("toggle", true);
                                    $this->config->save();
                                    $sender->sendMessage("§aLocalchat is ingeschakeld");
                                }
                            }
                            else{
                                $sender->sendMessage("§4U bent niet bevoegd om deze command te gebruiken.");
                            }

                            break;

                        default:
                            $sender->sendMessage("§4Beschikbare commands:\n§c/lc setdistance §4Stelt de chat afstand in\n§c/lc toggle §4Schakelt localchat in of uit");
                            break;
                    }


                }
                else{
                    $sender->sendMessage("§4Beschikbare commands:\n§c/lc setdistance §4Stelt de chat afstand in\n§c/lc toggle §4Schakelt localchat in of uit");
                }

                return true;


            default:
                return false;

        }
    }

    /**
     * @priority LOW
     * @param PlayerChatEvent $event
     */
    public function bericht(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $near = [];
        if ($this->config->get("toggle")) {
            foreach ($event->getRecipients() as $pr) {
                if ($pr instanceof Player) {
                    $dist = $player->distance($pr);
                    $config_distance = $this->config->get("distance");
                    if ($dist <= $config_distance AND $pr->getLevel() === $player->getLevel()) {
                        $near[] = $pr;
                    }
                }
            }
            $suffix = $this->config->get("global-suffix");
            if($this->startsWith($event->getMessage(), $suffix))
            {
                /** @var ?PureChat $pc  */
                $pc = $this->getServer()->getPluginManager()->getPlugin("PureChat");

                $pc_prefix = !is_null($pc) ? $pc->getPrefix($player) : null;

                $prefix = $this->config->get("global-prefix");
                $message = str_replace(["{msg}", "{player}", "{pc_prefix}"], [$event->getMessage(), $player->getName(), $pc_prefix], $prefix);
                $event->setCancelled();
                $player->getServer()->broadcastMessage($message);
                return;
            }
            if (count($near) == 0) {
                $event->setCancelled();
            } else {
                $event->setRecipients($near);
                $this->getServer()->getLogger()->info("[" . $player->getName() . "] > " . $event->getMessage());
            }
        }
    }


    public function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

}
