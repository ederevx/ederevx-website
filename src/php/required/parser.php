<?php
namespace Parser {

require_once "src/php/required/containers.php";

use Container as C;

function parseJSONFile($filePath) {
    if (!file_exists($filePath)) {
        throw new \InvalidArgumentException("File at path {$filePath} does not exist.");
    }
    $jsonContent = file_get_contents($filePath);
    return json_decode($jsonContent, true);
}

function parseContainerGroups($groupData, $parentContainer) {
    if (!$groupData) {
        return;
    }

    /* Iterate through the child's properties*/
    foreach ($groupData as $containerName => $containerData) {
        /* Parse the container data from JSON terms into local variables */
        $containerDataParsed = array(
            "id" => $containerName,
            "containerType" => $containerData["containerType"] ?? null,
            "element" => $containerData["element"] ?? null,
            "classPreset" => $containerData["classPreset"] ?? null,
            "content" => $containerData["content"] ?? null,
            "children" => $containerData["children"] ?? null,
            "duplicatedChildren" => $containerData["duplicatedChildren"] ?? null,
            "childrenDirectory" => $containerData["childrenDirectory"] ?? null,
        );
        parseContainerGroupsChild($containerDataParsed, $parentContainer);
    }
}

function parseContainerGroupsChild($childData, $parentContainer) {
    if (!$childData) {
        return;
    }

    $id = $childData["id"];
    $type = $childData["containerType"];
    $element = $childData["element"];
    $classPreset = $childData["classPreset"];
    $content = $childData["content"];
    $children = $childData["children"];
    $dupChildren = $childData["duplicatedChildren"];
    $childrenDir = $childData["childrenDirectory"];

    if ($parentContainer === null || $id == "root") {
        /* Create root container */
        $rootContainer = C\GenericContainer::createGenericContainerType($type, $id, $element, $classPreset);
        C\GenericContainer::setRootContainer($rootContainer);
        parseContainerGroups($children, $rootContainer);
    } else if ($content || $children || $dupChildren || $childrenDir) {
        /* Create child container with children and/or content */
        $childContainer = $parentContainer->createChild($type, $id, $element, $classPreset);
        parseContainerGroupsContent($content, $childContainer);
        parseContainerGroupsChildren($children, $dupChildren, $childrenDir, $childContainer);
    } else {
        throw new \Exception("Invalid container data for container with id {$id}.");
    }
}

function parseContainerGroupsChildren($childrenData, $dupChildrenData, $childrenDir, $childContainer) {
    /* Allow to have both unique and duplicated children; handle unique first */
    if ($childrenData) {
        parseContainerGroups($childrenData, $childContainer);
    }
    /* Handle duplicated children by generating equivalent child containers per id, 
     * duplicated children must have the same type, element, and class preset */
    if ($dupChildrenData) {
        foreach ($dupChildrenData["ids"] as $dupId) {
            $dupIdData = array("{$dupId}" => array(
                "containerType" => $dupChildrenData["containerType"] ?? null,
                "element" => $dupChildrenData["element"] ?? null,
                "classPreset" => $dupChildrenData["classPreset"] ?? null,
                "content" => 
                        $dupChildrenData["content"] ?? 
                        $dupChildrenData["contentPerId"][$dupId] ?? 
                        null,
                "children" => 
                        $dupChildrenData["children"] ?? 
                        $dupChildrenData["childrenPerId"][$dupId] ?? 
                        null,
                "duplicatedChildren" => 
                        $dupChildrenData["duplicatedChildren"] ?? 
                        $dupChildrenData["duplicatedChildrenPerId"][$dupId] ?? 
                        null,
                "childrenDirectory" => 
                        $dupChildrenData["childrenDirectory"] ?? 
                        $dupChildrenData["childrenDirectoryPerId"][$dupId] ?? 
                        null
            ));
            parseContainerGroups($dupIdData, $childContainer);
        }
    }
    /* Handle children directory by reading the JSON file in the directory and parsing it as children */
    if ($childrenDir) {
        $childrenDirData = parseJSONFile($childrenDir);
        parseContainerGroups($childrenDirData, $childContainer);
    }
}

function parseContainerGroupsContent($contentData, $childContainer) {
    /* This is fine, container likely has a child; otherwise, exception is thrown later */
    if (!$contentData) {
        return;
    }

    if ($contentData === "php") {
        $childContainer->setContentPHP();
    } else if ($contentData === "html-php") {
        $childContainer->setContentHTMLPHP();
    } else {
        $childContainer->setContent($contentData);
    }
}

}