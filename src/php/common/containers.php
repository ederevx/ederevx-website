<?php

$container_classes = array(
    "fluid" => "container-fluid",
    "inner_nav" => "navbar navbar-expand-lg navbar-light bg-light fixed-top",
    "inner_nav_header" => "px-5 container-fluid",
    "main_section" => "m-2 mt-5 p-3 container-lg",
    "footer_content" => "text-muted text-center",
);

class GenericContainer {
    public $id;
    public $element;
    private $class;
    private $content = "";

    public function __construct($id, $element, $class_preset) {
        global $container_classes;

        $this->id = $id;
        $this->element = $element;
        $this->class = $container_classes[$class_preset];
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getHTML() {
        /* If content is empty, return empty string instead of container with no content */
        if ($this->content == "") {
            return "";
        }
        return "<{$this->element} id=\"{$this->id}-container\" class=\"{$this->class}\">{$this->content}</{$this->element}>";
    }
}

class DivContainer extends GenericContainer {
    public function __construct($id, $class_preset) {
        parent::__construct($id, "div", $class_preset);
    }
}

class FluidContainer extends GenericContainer {
    public function __construct($id, $element) {
        parent::__construct($id, $element, "fluid");
    }
}

class MainContainer extends DivContainer {
    public function __construct($id) {
        parent::__construct($id, "main_section");
    }

    public function getHTML() {
        return "<br id=\"{$this->id}\">" . parent::getHTML();
    }
}

/* Container group functions */

function generate_container_group($container_group) {
    $content = "";
    foreach ($container_group as $container) {
        $content .= $container->getHTML();
    }
    return $content;
}

function set_container_group_content_php($container_group) {
    foreach ($container_group as $container) {
        $container->setContent(
            include "src/php/{$container->id}.php");
    }
}

function set_container_group_content_html_php($container_group) {
    foreach ($container_group as $container) {
        $container->setContent(
            file_get_contents(
                "src/html-php/contents/{$container->id}.php"));
    }
}