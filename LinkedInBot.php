<?php
/*
     ___________
    / _   _     \
   | | | ' '__   |
   | | | |  _  \ | LINKEDIN BOT v.1.0
   | | | | | | | |    by Samuel Faj
   | |_| |_| |_| |
    \___________/

*/

/* LinkedIn User Configurations */
$username = 'user@email.com';
$password = 'password1234';

/* Bot Configurations */
$maxCycles = 0;                          # 0 = unlimited.
$sleepBetweenCycles = rand(5,30);        # How much to wait, in seconds, between a cycle and another.

/* Cycle Configurations */
$likesOnFeed = 10;                       # 0 = unlimited.
$maxNewConnections = 50;                 # 0 = unlimited.

/* Technicals Configurations */
$host = 'http://localhost:4444/wd/hub';  # Selenium default host

require_once('bot/robot.php');

$robot = new Facebook\WebDriver\LinkedInBot($username,$password,$maxCycles,$sleepBetweenCycles,$likesOnFeed,$maxNewConnections,$host);
$robot->start();