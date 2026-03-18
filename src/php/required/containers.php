<?php
namespace Container {

use Container as C;

class GenericContainer {
    public $id;
    public $element;
    private $class = self::CONTAINER_CLASSES["none"];
    private $content = "";
    private $children = array();
    private $parent = null;
    private static $rootContainer = null;

    const CONTAINER_TYPES = array(
        "generic" => "generic",
        "div" => "div",
        "fluid" => "fluid",
        "main" => "main",
    );

    const CONTAINER_CLASSES = array(
        "fluid" => "container-fluid",
        "inner_nav" => "navbar navbar-expand-lg navbar-light bg-light fixed-top",
        "inner_nav_header" => "px-5 container-fluid",
        "main_section" => "m-2 mt-5 p-3 container-lg",
        "footer_content" => "text-muted text-center",
        "none" => "",
    );

    public function __construct($id, $element, $classPreset) {
        $this->id = $id;
        $this->element = $element;
        $this->class = self::CONTAINER_CLASSES[$classPreset ?? "none"];
    }

    /* Child container functions */

    public function createChild($type, $id, $element = null, $classPreset = null) { 
        $this->children[$id] = self::createGenericContainerType($type, $id, $element, $classPreset);
        $this->children[$id]->parent = $this;
        return $this->children[$id];
    }

    public function getChild($id) {
        if (isset($this->children[$id])) {
            return $this->children[$id];
        }
        throw new \InvalidArgumentException("Child with id {$id} does not exist.");
    }

    public function deleteChild($id) {
        if (isset($this->children[$id])) {
            unset($this->children[$id]);
        } else {
            throw new \InvalidArgumentException("Child with id {$id} does not exist.");
        }
    }
    
    public function hasChildren() {
        return !empty($this->children);
    }

    private function getChildrenHTML() {
        if (!$this->hasChildren()) {
            return "";
        }
        $childrenHTML = "";
        foreach ($this->children as $child) {
            $childrenHTML .= $child->getHTML();
        }
        return $childrenHTML;
    }

    /* Content functions */

    public function setContent($content) {
        $this->content = $content;
    }

    public function setContentPHP() {
        $this->content = include "src/php/{$this->id}.php";
    }

    public function setContentHTMLPHP() {
        $this->content = file_get_contents("src/html-php/contents/{$this->id}.php");
    }

    /* HTML generation function */

    public function getHTML() {
        $htmlContent = $this->content;
        /* If container has children, append their content to the container's content */
        $htmlContent .= $this->getChildrenHTML();
        /* If content is empty, throw an exception */
        if ($htmlContent == "") {
            throw new \InvalidArgumentException("Container with id {$this->id} has no content.");
        }
        return "<{$this->element} id=\"{$this->id}-container\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }

    /* Static functions */

    public static function setRootContainer($container) {
        if (self::$rootContainer !== null) {
            throw new \Exception("Root container is already set.");
        }
        self::$rootContainer = $container;
    }

    public static function getRootContainer() {
        if (self::$rootContainer === null) {
            throw new \Exception("Root container is not initialized.");
        }
        return self::$rootContainer;
    }

    public static function createGenericContainerType($type, $id, $element = null, $classPreset = null) {
        switch ($type) {
            case "generic":
                return new GenericContainer($id, $element, $classPreset);
            case "div":
                return new DivContainer($id, $classPreset);
            case "fluid":
                return new FluidContainer($id, $element);
            case "main":
                return new MainContainer($id);
            default:
                throw new \InvalidArgumentException("Invalid container type: {$type}");
        }
    }
}

class DivContainer extends C\GenericContainer {
    public function __construct($id, $classPreset) {
        parent::__construct($id, "div", $classPreset);
    }
}

class FluidContainer extends C\GenericContainer {
    public function __construct($id, $element) {
        parent::__construct($id, $element, "fluid");
    }
}

class MainContainer extends C\DivContainer {
    public function __construct($id) {
        parent::__construct($id, "main_section");
    }

    public function getHTML() {
        return "<br id=\"{$this->id}\">\n" . parent::getHTML();
    }
}

}