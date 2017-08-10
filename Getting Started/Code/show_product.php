<?php
/**
 * show_product.php
 *
 * Usage: php show_product.php <id>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$productID = $argv[1]; //要读取的产品编号
$product = $entityManager->find(Product::class, $productID); // 提取产品数据实例

if(!$product instanceof Product) { //结果检查
    echo 'No product found.' . PHP_EOL;
    exit(1);
}

echo sprintf("Product ID: %d Name: %s" . PHP_EOL,  $product->getProductID(), $product->getProductName());