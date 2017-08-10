<?php
/**
 * update_product.php
 *
 * Usage: php update_product.php <id> <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$productID = $argv[1];
$newName = $argv[2];

$product = $entityManager->find(Product::class, $productID);

if (!$product instanceof Product) {
    echo "Product ID: $productID does not exist." . PHP_EOL;
    exit(1);
}

$product->setProductName($newName);

$entityManager->flush();

echo "Product name updated to: " . $newName . PHP_EOL;
