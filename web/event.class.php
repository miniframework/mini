<?php
class mini_web_event
{
    public function onbeforeApp($args)
    {
    }
    public  function onendApp($args)
    {
        
    }
    public function onbeforeController($args)
    {
        
    }
    public function onendController($args)
    {
        
    }
    public function onbeforeAction($args)
    {
    }
    public function onendAction($args)
    {
        
    }
    public function onautoSave($args)
    {
        mini_db_unitofwork::getHandle()->commit();
    }
}