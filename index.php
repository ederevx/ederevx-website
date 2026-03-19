<!doctype html>
<html lang="en">
<?php include 'src/html-php/head-tag-config.php'; ?>

<?php

/* Include required PHP files */
foreach (glob("src/php/required/*.php") as $filename) {
    require_once $filename;
}

/* Initialize containers based on JSON data */
include 'src/php/init.php';

/* Output the HTML of the root container, which includes 
 * all child containers and their content */
echo Container\GenericContainer::getRootContainer()->getHTML();

?>
</html>