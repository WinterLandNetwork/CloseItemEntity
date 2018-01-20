<?php

namespace CloseItemEntity;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\scheduler\Task;

use pocketmine\entity\Item as ItemEntity;
use pocketmine\level\sound\GenericSound;

class CloseItemEntity extends PluginBase implements Listener{
 
 public $closeTime = 300;
 
 public function onEnable()
{
  
  @mkdir($this->getDataFolder());
  
  $this->config = new Config($this->getDataFolder()."config.yml",Config::YAML,[
   "clean-period" => 300, //In seconds
   "avoid-worlds" => ["spawn"] //Configure worlds that can't be cleaned
  ]);
  
  $this->closeTime = (int)$this->config->get("clean-period");
  
  $this->getServer()->getScheduler()->scheduleRepeatingTask(new CloseTask($this), 20);
  
  $this->getLogger()->info("Load successfully!");
 }
 
 public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
  
  $this->closeTime = 10;

  return true;
 }
 
 public function onClose(){
  
  $number = 0;
  
  foreach($this->getServer()->getLevels() as $level){
   
   if(!in_array($level->getName(), $this->config->get("avoid-worlds"))){
   
    foreach($level->getEntities() as $entity){
    
     if($entity instanceof ItemEntity){
     
      $entity->close();
     
      $number ++;
     }
    }
    unset($entity);
    
    foreach($level->getChunks() as $chunk){
    
     foreach($chunk->getEntities() as $entity){
    
      if($entity instanceof ItemEntity){
     
       $entity->close();
      
       $number ++;
      }
     }
     unset($entity);
    }
    unset($chunk);
   }
  }
  
  $this->getServer()->broadcastTip("§l§bTotally cleaned §6{$number} §bdrop items");
 }
 
 public function addSound($sound = 3500){
  
  foreach($this->getServer()->getOnlinePlayers() as $player){
   
   $player->level->addSound(new GenericSound($player, $sound));
  }
  
  unset($sound);
 }
 
 //Indev
 /*public function joinServer(\pocketmine\event\player\PlayerJoinEvent $ev){
  
  $player = $ev->getPlayer();
  $text = new \pocketmine\level\particle\FloatingTextParticle($player, "Test");
  
  $player->level->addParticle($text);
 }*/

}

class CloseTask extends Task{
 
 public $plugin;
 
 public function __construct(CloseItemEntity $plugin){
 
  $this->plugin = $plugin;
 }
 
 public function onRun($currentTick){
  
  $this->plugin->closeTime --;
  
  if($this->plugin->closeTime <= 10 || in_array($this->plugin->closeTime, [30, 15])){
   
   if($this->plugin->closeTime <= 0){
    
    $this->plugin->onClose();
    $this->plugin->addSound(1004);
    $this->plugin->closeTime = (int)$this->plugin->config->get("clean-period");
    
   }else{
    
    $this->plugin->addSound(1030);
    $this->plugin->getServer()->broadcastTip("§l§7Will clean drop items after §e{$this->plugin->closeTime} §7seconds");
   }
   
  }
 }
 
}