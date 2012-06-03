<?php
interface mini_db_interface
{
    public function query($sql);
    public function unbuffer($sql, $unbuffer);
    public function affected();
    public function find($sql);
    public function findObj($sql);
    public function findAll($sql);
    public function findObjAll($sql);
}
?>