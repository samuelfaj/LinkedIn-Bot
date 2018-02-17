<?php
/*
     ___________
    / _   _     \
   | | | ' '__   |
   | | | |  _  \ |     LINKEDIN BOT v.1.0
   | | | | | | | |        by Samuel Faj
   | |_| |_| |_| |
    \___________/

*/

/* LinkedIn User Configurations */
$username = 'user@email.com.br';
$password = 'mysecretpass';

/* Bot Configurations */
$maxCycles = 0;                                     # 0 = unlimited.
$sleepBetweenCycles = rand(5 * 60, 10 * 60);        # How much to wait, in seconds, between a cycle and another.

/* Cycle Configurations */
// For each cycle the script will do:
$autolike = true;                        # Automatically likes posts on feed.
$likesOnFeed = 30;                       # 0 = unlimited

$autoInvite = true;                      # Automatically invite people to your network.
$maxNewConnections = 60;                 # 0 = unlimited

$autoAdd = true;                         # Automatically accept invites from users.

/* Technicals Configurations */
$host = 'http://localhost:4444/wd/hub';  # Selenium default host

require_once('bot/robot.php');

$robot = new Facebook\WebDriver\LinkedInBot($username,$password,$maxCycles,$sleepBetweenCycles,$likesOnFeed,$maxNewConnections,$host,$autolike,$autoInvite,$autoAdd);
$robot->start();