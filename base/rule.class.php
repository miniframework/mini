<?php
interface mini_base_rule
{
    public function parseUrl($urlmanager, $request, $app);
    public function createUrl();
}
?>