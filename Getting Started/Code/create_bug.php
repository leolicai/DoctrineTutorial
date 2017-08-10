<?php
/**
 * create_bug.php
 *
 * Usage: php create_bug.php <reporter-id> <engineer-id> <product-ids>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$reporterID = $argv[1];
$engineerID = $argv[2];
$productIds = explode(",", $argv[3]);

$reporter = $entityManager->find(User::class, $reporterID);
$engineer = $entityManager->find(User::class, $engineerID);

if (!$reporter instanceof User || !$engineer instanceof User) {
    echo "No reporter and/or engineer found for the given id" . PHP_EOL;
    exit(1);
}

$bug = new Bug();
$bug->setReporter($reporter);
$bug->setEngineer($engineer);
$bug->setBugCreated(new DateTime("now"));
$bug->setBugStatus(1);

$description = sprintf("Something does not work! %s has assigned to %s", $reporter->getUserName(), $engineer->getUserName());

foreach ($productIds as $productId) {
    $product = $entityManager->find(Product::class, (int)$productId);
    if ($product instanceof Product) {
        $bug->assignToProduct($product);
        $description .= ". occurred product: " . $product->getProductName();
    }
}

$bug->setBugDescription($description);

$entityManager->persist($bug);
$entityManager->flush();

echo "Created new Bug with ID: " . $bug->getBugID() . PHP_EOL;
