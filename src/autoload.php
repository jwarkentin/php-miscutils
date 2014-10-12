<?php

$thisdir = dirname(__FILE__) . '/';

require_once(realpath($thisdir . '/../vendor/autoload.php'));

$loader = new \Aura\Autoload\Loader;
$loader->register();

$loader->addPrefix('CharUtils', $thisdir . 'CharUtils');