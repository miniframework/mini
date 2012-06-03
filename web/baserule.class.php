<?php
interface mini_web_baserule
{
    public function parseUrl($urlmanager, $request, $app);
    public function createUrl();
}
?>