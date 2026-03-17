<?php

function generate_footer_content() {
    global $footer_containers;

    /* Generate footer container contents */
    $footer_containers["footer_content"]->setContent(
        "Copyright (C) 2026, Edrick Sinsuan");
    return generate_container_group($footer_containers);
}

/* Output the footer container content included in root */
return generate_footer_content();
