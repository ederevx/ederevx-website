<?php

require 'containers.php';

/* Container groups */

$inner_nav_containers = array(
    "inner_nav_header" => new GenericContainer("inner_nav_header", "header", "inner_nav_header"),
);

$nav_containers = array(
    "inner_nav" => new GenericContainer("inner_nav", "nav", "inner_nav"),
);

$main_containers = array(
    "about" => new MainContainer("about"),
    "projects" => new MainContainer("projects"),
    "contact" => new MainContainer("contact"),
);

$footer_containers = array(
    "footer_content" => new GenericContainer("footer_content", "p", "footer_content"),
);

$root_containers = array(
    "nav" => new FluidContainer("nav", "nav"),    
    "main" => new FluidContainer("main", "main"),
    "footer" => new FluidContainer("footer", "footer"),
);

$root_container = new FluidContainer("root", "div");