<?php
/**
 * for mini_db_mysql->unbuffer() callback.
 * @author wzb
 * @data 2012-05-08 add
 */
interface mini_db_unbuffer
{
    public function callback($row);
}
?>