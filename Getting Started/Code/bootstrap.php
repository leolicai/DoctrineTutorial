<?php
/**
 * bootstrap.php
 *
 * ORM 启动配置
 *
 * @author: Leo
 * @version: 1.0
 */

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// Autoload 配置
require_once "vendor/autoload.php";

// ORM 配置
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/src"], $isDevMode);

// 数据库连接配置
$dbConfig = [
	'driver' => 'pdo_mysql',
    'host' => 'localhost',
	'user' => 'root',
    'password' => 'root',
    'port' => 3306,
    'dbname' => 'test',
    'charset' => 'utf8mb4',
    'defaultTableOptions' => [
        'collate' => 'utf8mb4_unicode_ci',
        'charset' => 'utf8mb4',
        'engine' => 'InnoDB',
    ],
];

// 创建实体管理器
$entityManager = EntityManager::create($dbConfig, $config);