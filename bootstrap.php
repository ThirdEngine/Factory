<?php

$loader = include ('vendor/autoload.php');
$loader->register();

spl_autoload_register(function ($class) {
  include 'src/' . $class . '.php';
});