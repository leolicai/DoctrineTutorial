<?php
/**
 * create_product.php
 *
 * Usage: php create_product.php <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

// 提取输入的产品名称
$newProductName = $argv[1];

// 创建实体对象并设置属性
$entityProduct = new Product();
$entityProduct->setProductName($newProductName);

// 数据持久化
$entityManager->persist($entityProduct);
$entityManager->flush();

echo 'Created Product with ID: ' . $entityProduct->getProductID() . PHP_EOL;