<?php
namespace Container {

use Container as C;

class BaseContainer {
    protected $data = array(
        /* Default/base template data */
        "container" => "base",
        "id" => "",
        "class" => "",
        "element" => "div",
        "content" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
    private static $containerTplDirectory = null;
    private static $rootContainer = null;

    public function __construct($data) {
        if (!$this->isValidData($data))
            return;

        /* Set the raw data directly, have the parser format it accordingly 
           as an array */
        foreach ($data as $idx => $value) {
           /* Only construct data that exists in template */
            if ($this->hasDataVal($idx))
                $this->setDataVal($idx, $value);
        }
    }

    /* Data functions */

    protected function isValidData($data) {
        if (!is_array($data)) {
            error_log("The data sent to the container is not an array!");
            return false;
        }

        if (!count($data)) {
            error_log("There is no data in the data array!");
            return false;
        }

        return true;
    }

    public function setDataVal($idx, $value) {
        $this->data[$idx] = $value;
    }

    public function getDataVal($idx) {
        if (!$this->hasDataVal($idx))
            return null;
        return $this->data[$idx];
    }

    public function hasDataVal($idx) {
        return isset($this->data[$idx]);
    }

    /* Children container functions */

    protected function isValidChildren($children) {
        if (!is_array($children)) {
            error_log("The children data is not valid!");
            return false;
        }
        return true;
    }

    protected function getChildren() {
        $children = $this->getDataVal("childrenArray");
        if (!$this->isValidChildren($children))
            return null;
        return $children;
    }

    public function createChild($idx, $data) {
        $children = $this->getChildren();
        if (!isset($children))
            return null;

        $idx = $this->generateChildIdx($idx, $children);

        /* Store the pointer and data in the copy children */
        $children[$idx] = self::createContainer($data);
        $children[$idx]->setDataVal("parentPtr", $this);

        /* Update the parent's children then return the child ptr */
        $this->setDataVal("childrenArray", $children);
        return $this->getChild($idx);
    }

    protected function generateChildIdx($idx, $children) {
        $val = 0;
        while (isset($children[$idx])) {
            $idx .= "{$val}";
            $val++;
        }
        return $idx;
    }

    public function getChild($idx) {
        $children = $this->getChildren();
        if (!$children)
            return null;

        if (isset($children[$idx]))
            return $children[$idx];

        error_log("Child with id {$id} does not exist.");
        return null;
    }

    public function deleteChild($id) {
        $children = $this->getChildren();
        if (!$children)
            return null;

        if (isset($children[$idx]))
            unset($children[$idx]);
        else
            error_log("Child {$idx} does not exist.");

        $this->setDataVal("childrenArray", $children);
    }

    /* HTML render function */

    public function render() {
        $this->setDataVal("htmlContent", $this->getHTMLContent());

        /* Make the data visible to the container template */
        extract($this->data);

        /* Capture template output */
        ob_start();
        include $this->getcontainerTplDirectory();
        $output = ob_get_clean();

        /* Return the resulting HTML output */
        return $output;
    }

    public function getHTMLContent() {
        $htmlContent = "";
        /* Append children's content to raw content */
        $htmlContent = $this->getDataVal("content") ?? "";
        $htmlContent .= $this->renderChildren();
        return $htmlContent;
    }

    private function renderChildren() {
        $childrenOutput = "\n";
        $children = $this->getChildren();
        if ($children) {
            foreach ($children as $child) {
                $childrenOutput .= $child->render();
            }
        }
        return $childrenOutput;
    }

    /* Container Template functions */

    public static function setcontainerTplDirectory($directory) {
        if (self::$containerTplDirectory !== null) {
            error_log("Container template directory is already set.");
            return;
        }
        self::$containerTplDirectory = $directory;
    }

    public static function getcontainerTplDirectory() {
        if (self::$containerTplDirectory === null) {
            throw new \Exception("Container template directory is not initialized.");
        }
        return self::$containerTplDirectory;
    }

    /* Root container functions */

    public static function setRootContainer($container) {
        if (self::$rootContainer !== null) {
            error_log("Root container is already set.");
            return;
        }
        self::$rootContainer = $container;
    }

    public static function getRootContainer() {
        return self::$rootContainer;
    }

    /* Global container creator */

    public static function createContainer($data) {
        $container = $data["container"] ?? "";
        switch ($container) {
            case "main":
                return new MainContainer($data);
            case "double":
                return new DoubleContainer($data);
            case "button":
                return new ButtonContainer($data);
            case "href":
                return new HREFContainer($data);
            case "card":
                return new CardContainer($data);
            default:
                return new BaseContainer($data);
        }
        return null;
    }
}

class MainContainer extends C\BaseContainer {
    protected $data = array(
        /* Main template data */
        "container" => "main",
        "id" => "",
        "navId" => "",
        "class" => "",
        "element" => "div",
        "content" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
}

class DoubleContainer extends C\BaseContainer {
    protected $data = array(
        /* Double template data */
        "container" => "double",
        "id" => "",
        "childId" => "",
        "class" => "",
        "class2" => "",
        "element" => "div",
        "element2" => "div",
        "content" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
}

class ButtonContainer extends C\BaseContainer {
    protected $data = array(
        /* Button template data */
        "container" => "button",
        "id" => "",
        "class" => "",
        "content" => "",

        "target" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
}

class HREFContainer extends C\BaseContainer {
    protected $data = array(
        /* Button template data */
        "container" => "href",
        "id" => "",
        "class" => "",
        "content" => "",

        "href" => "#",
        "target" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
}

class CardContainer extends C\BaseContainer {
    protected $data = array(
        /* Card template data */
        "container" => "card",
        "id" => "",
        "bodyId" => "",
        "titleId" => "",
        "subtitleId" => "",
        "textId" => "",

        "class" => "",
        "bodyClass" => "",
        "titleClass" => "",
        "subtitleClass" => "",
        "textClass" => "",

        "content" => "",
        "titleContent" => "",
        "subtitleContent" => "",
        "textContent" => "",

        /* Container data */
        "childrenArray" => array(),
        "parentPtr" => null,
    );
}

}