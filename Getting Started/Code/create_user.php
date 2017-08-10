<?php
/**
 * create_user.php
 *
 * Usage: php create_user.php <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$userName = $argv[1];

$user = new User();
$user->setUserName($userName);

$entityManager->persist($user);
$entityManager->flush();

echo "Created User with ID: " . $user->getUserID() . PHP_EOL;
