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

    public function __construct($id, $element, $classPresetData) {
        $this->setID($id);
        /* If no element property provided, go with default div */
        if ($element) {
            $this->element = $element;
        }
        /* Only configure classpreset if present, if not don't use any at all */
        if ($classPresetData) {
            $classPresetData = explode(" ", $classPresetData);
            foreach ($classPresetData as $classPreset) {
                /* If container class doesn't have the preset, try the preset itself */
                $class = self::getContainerClasses()[$classPreset] ?? $classPreset;
                if ($this->class !== "") {
                    $this->class .= " ";
                }
                $this->class .= $class;
            }
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

    public function parseContent($contentData) {
        switch ($contentData) {
            case "contentHTMLPHP":
                return file_get_contents("src/html-php/contents/{$this->getID()}.php");
            default:
                return $contentData;
        }
    }

    public function setContent($contentData) {
        $this->content = $this->parseContent($contentData);
    }

    public function getContent() {
        return $this->content;
    }

    /* HTML generation function */

    public function getHTML() {
        $htmlContent = $this->getHTMLContent();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }

    public function getHTMLContent() {
        $htmlContent = $this->getChildrenHTML();
        /* Append container's raw content to the children's content */
        $htmlContent .= $this->content;
        /* If content is empty, throw an exception */
        if ($htmlContent == "") {
            error_log("Container with id {$this->getID()} has no content.");
        }
        return $htmlContent;
    }

    /* Static functions */

    public static function setRootContainer($container) {
        if (self::$rootContainer !== null) {
            error_log("Root container is already set.");
            return;
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

    public static function findContainerFromRootDir($dirString) {
        $dirArray = explode('/', $dirString);

        $ptr = self::getRootContainer();
        foreach ($dirArray as $childName) {
            $ptr = $ptr->getChild($childName);
        }

        return $ptr;
    }

    public static function createGenericContainerType($type, $id, $element = null, $classPreset = null) {
        if (is_array($element) && is_array($classPreset)) {
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
                error_log("Invalid array container type! {$type}");
        }
    }

    /* Protected functions */
    protected static function functionNotAllowed($function) {
        throw new \Exception("{$function} is not allowed by {$this->getID()}. Abort.");
    }
}

class MainContainer extends C\GenericContainer {
    const MAIN_ELEMENT = "div";
    const MAIN_CLASSPRESET = "main";

    public function __construct($id) {
        parent::__construct($id, self::MAIN_ELEMENT, self::MAIN_CLASSPRESET);
    }

    public function getHTML() {
        /* The break header must be the nav target */
        return "<br id=\"{$this->getNavID()}\">\n" . parent::getHTML();
    }

    public function getNavID() {
        return $this->getID() . "_nav";
    }
}

class DoubleContainer extends C\GenericContainer {
    const CHILD_ID = "child";

    public function __construct($id, $element, $classPreset) {
        if (count($element) < 2 || count($classPreset) < 2) {
            error_log("Not enought element or classes defined! Abort.");
            return;
        }
        parent::__construct($id, $element[0], $classPreset[0]);
        /* Double containers have one child automatically created */
        $this->createChildDefault(self::CHILD_ID, $element[1], $classPreset[1]);
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        /* First create the child container if none existent */
        if (!$this->hasChild(self::CHILD_ID)) {
            return parent::createChild($type, self::CHILD_ID, $element, $classPreset);
        }
        /* Then, create the child of the child container if requested again */
        return $this->getChild(self::CHILD_ID)->createChild($type, $id, $element, $classPreset);
    }

    public function getContent() {
        /*Get content of child container instead of the container itself */
        return $this->getChild(self::CHILD_ID)->getContent();
    }

    public function setContent($contentData) {
        /* Set content of child container instead of the container itself */
        $this->getChild(self::CHILD_ID)->setContent($contentData);
    }
}

class ButtonContainer extends C\GenericContainer {
    const TARGET_SIBLING_ID = "content";
    const BUTTON_ELEMENT = "button";

    public function __construct($id, $classPreset) {
        parent::__construct($id, self::BUTTON_ELEMENT, $classPreset);
    }

    public function getHTML() {
        $htmlContent = $this->getHTMLContent();
        $targetID = $this->parent->getChild(self::TARGET_SIBLING_ID)->getID();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "type=\"button\" data-bs-toggle=\"collapse\" " . 
               "data-bs-target=\"#{$targetID}\" aria-expanded=\"false\" " .
               "aria-controls=\"{$targetID}\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }
}

class HREFContainer extends C\GenericContainer {
    const HREF_ELEMENT = "a";
    protected $hrefContent = "#";
    protected $targetContent = "blank_";
    protected $storedContentData = array();
    protected $isDeffered = false;

    public function __construct($id, $classPreset) {
        parent::__construct($id, self::HREF_ELEMENT, $classPreset);
    }

    public function createChild($type, $id, $element = null, $classPreset = null) {
        return self::functionNotAllowed(__METHOD__);
    }

    public function setContent($contentData) {
        if (!is_array($contentData)) {
            /* Reconstruct into array if not array and perform normal parsing */
            $contentData = array("default", $contentData);
        }
        $contentCount = count($contentData);

        /* Parse classification first, then configure as necessary */
        switch ($contentData[0]) {
            case "hrefFind":
                /* We can't do this right now, defer it to getHTML() */
                $this->storedContentData = $contentData;
                $this->isDeffered = true;
                break;
            case "hrefRaw":
                if ($contentCount < 3) {
                    error_log("Missing content entries! Abort.");
                    return;
                }
                /* Configure content first then href and target */
                $this->content = $contentData[1];
                $this->hrefContent = $contentData[2];
                $this->targetContent = $contentData[3] ?? "blank_";
                break;
            case "default":
            default:
                $this->content = parent::parseContent($contentData[1]);
                $this->hrefContent = $this->content;
                break;
        }
    }

    protected function setDefferedHREFContent($contentData) {
        if (!$this->isDeffered) {
            /* Not needed */
            return;
        }

        if (!is_array($contentData)) {
            /* Reconstruct into array if not array and perform normal parsing */
            $contentData = array("default", $contentData);
        }
        $contentCount = count($contentData);

        /* Parse classification first, then configure as necessary */
        switch ($contentData[0]) {
            case "hrefFind":
                if ($contentCount < 2) {
                    error_log("Missing local directory! Abort.");
                    return;
                }
                /* This should be doable now after being deferred */
                $this->setLocalHREFContent($contentData);
                break;
            default:
                break;
        }
    }

    protected function setLocalHREFContent($contentData) {
        /* Target must be found and must have navID */
        $targetPtr = self::findContainerFromRootDir($contentData[1]);
        if (!$targetPtr || !method_exists($targetPtr, "getNavID")) {
            error_log("Invalid nav target! Abort.");
            return;
        }
        $this->hrefContent = "#{$targetPtr->getNavID()}";

        /* The target must have a title child */
        $targetTitlePtr = $targetPtr->getChild("title");
        if (!$targetTitlePtr) {
            error_log("Invalid nav target! Abort.");
            return;
        }
        /* Get content from title */
        $this->content = $targetTitlePtr->getContent();

        $this->targetContent = $contentData[2] ?? "";
        $this->isDeferred = false;
    }

    public function getHTML() {
        /* Set deferred HREF content here after everything has been initialized */
        $this->setDefferedHREFContent($this->storedContentData);
        $htmlContent = $this->getHTMLContent();
        return "<{$this->element} id=\"{$this->getID()}\" " .
               "href=\"{$this->hrefContent}\" target=\"{$this->targetContent}\" " .
               "class=\"{$this->class}\">\n\t{$htmlContent}\n</{$this->element}>";
    }
}

/* Three levels deep with three children at the bottom */
class ThreeByThreeContainer extends C\GenericContainer {
    const CHILDREN_LIMIT = 5;
    protected $chainLinear = array();

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

    public function getContent() {
        $contentCount = self::CHILDREN_LIMIT - 2;

        $content = array();
        for ($idx = 0; $idx < $contentCount; $idx++) {
            $content[$idx] = $this->getChainLinear($idx + 2)->getContent();
        }

        /* Return content of the linear children containers */
        return $content;
    }
}

}