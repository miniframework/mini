<?php
class mini_base_event extends mini_base_component
{
    
    private $e = null;
    private $dir = "events";
    public function init()
    {
        $this->e = new mini_struct_list();
        $this->getEvents();
    }
    public function addEvent($e)
    {
        $this->e->add($e);
    }
    public function getEvents()
    {
      $config =  mini::getConfig();
      $home = $config->home;
      $eventPath = $home."/".$this->dir;
      $e = $config->event['e'];
      if(!empty($e))
      {
          if(!is_array($e)) $e = array($e);
          foreach($e as $event)
          {
              $eventfile = $eventPath."/".$event.".class.php";
              if(file_exists($eventfile))
              {
                  include $eventfile;
                  $class = $event."Event";
                  if(class_exists($class))
                  {
                     $this->e->add(new $class());
                  }
              }
          }
      }
      
     
    }
    public function __call($name,$args)
    {
           foreach($this->e as $k => $e)
           {
               if(method_exists($e, $name))
               {
                 return  $e->$name($args);
               }
           }
        
      
    }
}