<?php
/**
 * cli-config.php
 *
 * Console 配置
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);

