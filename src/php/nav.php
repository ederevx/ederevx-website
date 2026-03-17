<?php

function generate_nav_content() {
    global $inner_nav_containers, $nav_containers;

    /* Generate inner nav container contents */
    set_container_group_content_html_php($inner_nav_containers);
    $nav_containers["inner_nav"]->setContent(
        generate_container_group($inner_nav_containers));

    return generate_container_group($nav_containers);
}

/* Output the nav container content included in root */
return generate_nav_content();