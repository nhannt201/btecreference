<?php
require_once 'mimini.php';
$browser=Mimini::open();
if (isset($_GET['action'])) {
  $action = trim($_GET['action']);
  if ($action == "home") {
    $browser->get("https://btecreferences.herokuapp.com/follow_home.html");
  }
   if ($action == "create") {
    $browser->get("https://btecreferences.herokuapp.com/follow_create.html");
  }
   if ($action == "search") {
    $browser->get("https://btecreferences.herokuapp.com/follow_search.html");
  }
  
  echo $browser->getContent();
}

