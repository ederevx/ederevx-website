<?php
namespace Parser {

require_once "src/php/required/containers.php";

use Container as C;

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
            "duplicatedChildren" => $containerData["duplicatedChildren"] ?? null
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

    if ($parentContainer === null || $id == "root") {
        /* Create root container */
        $rootContainer = C\GenericContainer::createGenericContainerType($type, $id, $element, $classPreset);
        C\GenericContainer::setRootContainer($rootContainer);
        parseContainerGroups($children, $rootContainer);
    } else if ($content || $children || $dupChildren) {
        /* Create child container with children and/or content */
        $childContainer = $parentContainer->createChild($type, $id, $element, $classPreset);
        parseContainerGroupsContent($content, $childContainer);
        parseContainerGroupsChildren($children, $dupChildren, $childContainer);
    } else {
        throw new Exception("Invalid container data for container with id {$id}.");
    }
}

function parseContainerGroupsChildren($childrenData, $dupChildrenData, $childContainer) {
    /* Allow to have both unique and duplicated children; handle unique first */
    if ($childrenData) {
        parseContainerGroups($childrenData, $childContainer);
    }
    if ($dupChildrenData) {
        /* Handle duplicated children by generating equivalent child containers per id, 
         * duplicated children must have the same type, element, and class preset */
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
                        null
            ));
            parseContainerGroups($dupIdData, $childContainer);
        }
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