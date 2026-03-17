<?php

require 'src/php/common/globals.php';

function generate_root_content() {
    global $root_containers;

    /* Generate root container contents */
    set_container_group_content_php($root_containers);
    return generate_container_group($root_containers);
}

echo generate_root_content();