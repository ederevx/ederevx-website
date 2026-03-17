<?php

function generate_main_content() {
    global $main_containers;

    /* Generate main container contents */
    set_container_group_content_html_php($main_containers);
    return generate_container_group($main_containers);
}

/* Output the main container content included in root */
return generate_main_content();