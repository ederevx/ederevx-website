<!doctype html>
<html lang="en">
<?php include 'src/html-php/head-tag-config.php'; ?>

<body id="body">
<?php

/* Include necessary PHP files */
require "src/php/containers.php";
require "src/php/global.php";

/* Initialize containers based on JSON data */
include 'src/php/init.php';

/* Output the HTML of the root container, which includes 
 * all child containers and their content */
echo Container\GenericContainer::getRootContainer()->getHTML();

?>
</body>
</html>