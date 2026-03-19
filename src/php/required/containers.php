<?php
namespace Container {

use Container as C;

class GenericContainer {
    protected $id = "";
    protected $element = "div";
    protected $class = "";
    protected $content = "";
    protected $children = array();
    protected $parent = null;
    private static $rootContainer = null;
    private static $containerClasses = null;

    public function __construct($id, $element, $classPreset) {
        $this->setID($id);
        /* If no element property provided, go with default div */
        if ($element) {
            $this->element = $element;
        }
        /* Only configure classpreset if present, if not don't use any at all */
        if ($classPreset && self::getContainerClasses()) {
            $this->class = self::getContainerClasses()[$classPreset];
        }
    }

    /* ID functions */

    protected function setID($id) {
        $this->id = "{$id}";
    }

    public function getID() {
        $publicID = "";

        /* Append ID by the parent IDs' first letter for public ID generation */
        $pointer = $this;
        while ($pointer->parent && $pointer->parent !== self::$rootContainer) {
            $publicID = $pointer->parent->id[0] . $publicID; /* Only get raw ID first char */
            $pointer = $pointer->parent;
        }
        if ($publicID !== "") {
            $publicID .= "_";
        }

        $publicID .= $this->id;
        return $publicID;
    }

    /* Child container functions */

    public function createChild($type, $id, $element = null, $classPreset = null) { 
        $this->children[$id] = self::createGenericContainerType($type, $id, $element, $classPreset);
        $this->children[$id]->parent = $this;
        return $this->children[$id];
    }

    public function createChildDefault($id, $element, $classPreset) {
        return $this->createChild(null, $id, $element, $classPreset);
    }

    public function getChild($id) {
        if ($this->hasChild($id)) {
            return $this->children[$id];
        }
        throw new \InvalidArgumentException("Child with id {$id} does not exist.");
    }

    public function hasChild($id) {
        return isset($this->children[$id]);
    }

    public function deleteChild($id) {
        if ($this->hasChild($id)) {
            unset($this->children[$id]);
        } else {
            error_log("Child with id {$id} does not exist.");
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

    public function setContent($contentData) {
        if ($contentData === "contentHTMLPHP") {
            $this->content = file_get_contents("src/html-php/contents/{$this->getID()}.php");
        } else if ($contentData === "contentPHP") {
            $this->content = include "src/php/{$this->getID()}.php";
        } else {
            $this->content = $contentData;
        }
    }

    /* HTML generation function */

    public function getHTML() {
        $htmlContent = $this->getHTMLContent();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }

    public function getHTMLContent() {
        $htmlContent = $this->getChildrenHTML();
        /* Append container content to the children's content */
        $htmlContent .= $this->content;
        /* If content is empty, throw an exception */
        if ($htmlContent == "") {
            throw new \InvalidArgumentException("Container with id {$this->getID()} has no content.");
        }
        return $htmlContent;
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

    public static function setContainerClasses($containerClassesData) {
        if (self::$containerClasses !== null) {
            error_log("Container classes are already set.");
            return;
        }
        self::$containerClasses = $containerClassesData;
    }

    public static function getContainerClasses() {
        if (self::$containerClasses === null) {
            error_log("Container classes are not initialized.");
            return null;
        }
        return self::$containerClasses;
    }

    public static function createGenericContainerType($type, $id, $element = null, $classPreset = null) {
        if (is_array($element) || is_array($classPreset)) {
            return self::createArrayContainerType($type, $id, $element, $classPreset);
        }

        switch ($type) {
            case "main":
                return new MainContainer($id);
            case "button":
                return new ButtonContainer($id, $classPreset);
            case "generic":
            default:
                return new GenericContainer($id, $element, $classPreset);
        }
    }

    private static function createArrayContainerType($type, $id, $element = null, $classPreset = null) {
        switch ($type) {
            case "3by3":
                return new ThreeByThreeContainer($id, $element, $classPreset);
            case "double":
                return new DoubleContainer($id, $element, $classPreset);
            default:
                throw new \InvalidArgumentException("Invalid array container type! {$type}");
        }
    }
}

class MainContainer extends C\GenericContainer {
    const MAIN_ELEMENT = "div";
    const MAIN_CLASSPRESET = "main_section";

    public function __construct($id) {
        parent::__construct($id, self::MAIN_ELEMENT, self::MAIN_CLASSPRESET);
    }

    public function getHTML() {
        return "<br id=\"{$this->getID()}\">\n" . parent::getHTML();
    }
}

class DoubleContainer extends C\GenericContainer {
    const CHILD_ID = "child";

    public function __construct($id, $element, $classPreset) {
        parent::__construct($id, $element[0], $classPreset[0]);
        /* Double containers have a one and only child */
        $this->createChildDefault(self::CHILD_ID, $element[1], $classPreset[1]);
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        if ($this->hasChildren()) {
            error_log("Creating another child is forbidden for double containers");
            return null;
        }
        return parent::createChild($type, $id, $element, $classPreset);
    }

    public function setContent($contentData) {
        /* Set content of child container instead of the container itself */
        $this->getChild(self::CHILD_ID)->setContent($contentData);
    }
}

class ButtonContainer extends C\GenericContainer {
    const TARGET_CHILD_ID = "content";
    const BUTTON_ELEMENT = "button";

    public function __construct($id, $classPreset) {
        parent::__construct($id, self::BUTTON_ELEMENT, $classPreset);
    }

    public function getHTML() {
        $htmlContent = $this->getHTMLContent();
        $targetID = $this->parent->getChild(self::TARGET_CHILD_ID)->getID();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "type=\"button\" data-bs-toggle=\"collapse\" " . 
               "data-bs-target=\"#{$targetID}\" aria-expanded=\"false\" " .
               "aria-controls=\"{$targetID}\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }
}

/* Three levels deep with three children at the bottom */
class ThreeByThreeContainer extends C\GenericContainer {
    private $chainLinear = array(); /* Target count: 5 */

    public function __construct($id, $element, $classPreset) {
        parent::__construct($id, $element[0], $classPreset[0]);

        /* Start with self */
        $this->chainLinear[0] = $this;

        /* Create 3by3 generic children tree and build linear chain */
        $child1 = $this->chainLinear[1] = $this->createChildDefault(0,
                $element[1],
                $classPreset[1]);
        for ($idx = 2; $idx < 5; $idx++) {
            $this->chainLinear[$idx] = $child1->createChildDefault($idx - 1,
                    $element[$idx],
                    $classPreset[$idx]);
        }
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        if ($this->countChainLinear() == 5) {
            error_log("Creating more than 5 linear children is forbidden for 3by3 containers");
            return null;
        }
        return parent::createChild($type, $id, $element, $classPreset);
    }

    private function getChainLinear($idx) {
        return $this->chainLinear[$idx];
    }

    private function countChainLinear() {
        return count($this->chainLinear);
    }

    public function setContent($contentData) {
        /* Set content of child containers 3 to 5, contentData must count 3 */
        if (!is_array($contentData) || count($contentData) != 3) {
            error_log("Content data array does not have 3 entries! Abort.");
            return;
        }

        if ($this->countChainLinear() != 5) {
            error_log("3by3 container does not have 5 linear children! Abort.");
            return;
        }

        for ($idx = 0; $idx < 3; $idx++) {
            $this->getChainLinear($idx + 2)->setContent($contentData[$idx]);
        }
    }
}

}