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
    const ID_DEPTH = 2; /* How many parents' full name included in ID */

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

    /* Try to generate predictable but unique as possible IDs for everyone */
    public function getID() {
        $publicID = "";

        /* Append ID by the parent ID accordingly */
        $ptr = $this;
        $depth = 0;
        while ($ptr->parent && $ptr->parent !== self::$rootContainer) {
            if ($depth < self::ID_DEPTH) {
                $publicID = $ptr->parent->id . "_" . $publicID;
            } else {
                if ($depth == self::ID_DEPTH) {
                    $publicID = "_" . $publicID;
                }
                /* Limit to first character beyond ID_DEPTH */
                $publicID = $ptr->parent->id[0] . $publicID;
            }
            $ptr = $ptr->parent;
            $depth++;
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
            case "href":
                return new HREFContainer($id, $classPreset);
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

    /* Protected functions */
    protected static function functionNotAllowed($function) {
        throw new \Exception("{$function} is not allowed by {$this->getID()}. Abort.");
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
        /* Double containers have one child automatically created */
        $this->createChildDefault(self::CHILD_ID, $element[1], $classPreset[1]);
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        if ($this->hasChildren()) {
            /* Append child ID with new unique child */
            $id = self::CHILD_ID . $id;
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

class HREFContainer extends C\GenericContainer {
    const HREF_ELEMENT = "a";

    public function __construct($id, $classPreset) {
        parent::__construct($id, self::HREF_ELEMENT, $classPreset);
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        return self::functionNotAllowed(__METHOD__);
    }

    public function getHTML() {
        $htmlContent = $this->getHTMLContent();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "href=\"{$htmlContent}\" target=\"blank_\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }
}

/* Three levels deep with three children at the bottom */
class ThreeByThreeContainer extends C\GenericContainer {
    const CHILDREN_LIMIT = 5;
    private $chainLinear = array();

    public function __construct($id, $element, $classPreset) {
        parent::__construct($id, $element[0], $classPreset[0]);

        /* Start with self */
        $this->chainLinear[0] = $this;

        /* Create 3by3 generic children tree and build linear chain */
        $child1 = $this->chainLinear[1] = $this->createChildDefault(0,
                $element[1],
                $classPreset[1]);
        for ($idx = 2; $idx < self::CHILDREN_LIMIT; $idx++) {
            $this->chainLinear[$idx] = $child1->createChildDefault($idx - 1,
                    $element[$idx],
                    $classPreset[$idx]);
        }
    }

    private function getChainLinear($idx) {
        return $this->chainLinear[$idx];
    }

    private function countChainLinear() {
        return count($this->chainLinear);
    }

    public function setContent($contentData) {
        /* Set content of child containers, contentData must count below */
        $contentCount = self::CHILDREN_LIMIT - 2;
        if (!is_array($contentData) || count($contentData) != $contentCount) {
            error_log("Content data array does not have the required entries! Abort.");
            return;
        }

        if ($this->countChainLinear() != self::CHILDREN_LIMIT) {
            error_log("3by3 container does not have " . 
                    self::CHILDREN_LIMIT . " linear children! Abort.");
            return;
        }

        for ($idx = 0; $idx < $contentCount; $idx++) {
            $this->getChainLinear($idx + 2)->setContent($contentData[$idx]);
        }
    }
}

}