<?php
/**
 * list_product.php
 *
 * Usage: php list_product.php
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

// 获取实体工厂仓库
$repositoryProduct = $entityManager->getRepository(Product::class);

// 提前所有的实体
$products = $repositoryProduct->findAll();

foreach ($products as $product) {
    echo sprintf("Product ID: %d Name: %s" . PHP_EOL,  $product->getProductID(), $product->getProductName());
}