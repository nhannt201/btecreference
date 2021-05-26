<?php
require_once 'mimini.php';
$browser=Mimini::open();
$browser->get("https://btecreferences.herokuapp.com/follow_home.html");
echo $browser->getContent();
