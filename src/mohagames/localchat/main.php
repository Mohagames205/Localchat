<?php

namespace mohagames\localchat;

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

    public $near;

    public function onLoad(): void
    {
        $this->getLogger()->info(TextFormat::WHITE . "I've been loaded!");
    }

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::DARK_GREEN . "biep boep");
    }

    public function bericht(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $near = [];
        foreach($event->getRecipients() as $pr){
            if ($pr instanceof Player) {
                $dist = $player->distance($pr);
                $this->near = 15;
                if($dist <= $this->near AND $pr->getLevel() === $player->getLevel()){
                    $near[] = $pr;
                }
            }
        }

        if(count($near) == 0){
            $event->setCancelled();
        }
        else{
            $event->setRecipients($near);
        }
    }
}