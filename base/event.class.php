<?php
class mini_base_event
{
    
    /**
     * 
     * @var mini_struct_list
     */
    private $e = null;
    /**
     * default dir name
     * @var string 
     */
    private $dir = "events";
    public function __construct()
    {
        $this->e = new mini_struct_list();
        $this->getEvents();
    }
    /**
     * add event to list
     * @param string $e
     */
    public function addEvent($e)
    {
        $this->e->add($e);
    }
    /**
     * get event from user config
     */
    public function getEvents()
    {
      $runPath = mini::getRunPath();
      $config = mini::getConfig();
      $eventPath = $runPath."/".$this->dir;
      $event = $config->event;
      if(empty($event)) return ;
      $e = $event['e'];
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
              else
              {
                  mini::e("event file {event} not exists", array('{event}'=>$eventfile));
              }
          }
      }
     
    }
    /**
     * class user define event function
     * @param string $name
     * @param array $args
     */
    public function __call($name,$args)
    {
           foreach($this->e as $k => $e)
           {
               if(method_exists($e, $name))
               {
                   if($e->$name($args))
                   { return 1;}
               }
           }
        
      
    }
}