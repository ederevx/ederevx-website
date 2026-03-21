<!doctype html>
<html lang="en">

<?php

/* Include required PHP files */
foreach (glob("src/php/required/*.php") as $filename) {
    require_once $filename;
}

include 'src/templates/head-tag-config.php';

/* Initialize containers based on JSON data */
include 'src/php/init.php';

/* Output the HTML of the root container, which includes 
 * all child containers and their content */
echo Container\BaseContainer::getRootContainer()->render();

?>
</html>