<?php
namespace Parser {

require_once "src/php/required/containers.php";

use Container as C;

function parseJSONFile($filePath) {
    if (!file_exists($filePath)) {
        throw new \InvalidArgumentException("File at path {$filePath} does not exist.");
    }
    $jsonContent = file_get_contents($filePath);

    $jsonOutput = json_decode($jsonContent, true);
    if (!$jsonOutput) {
        throw new \InvalidArgumentException("File at path {$filePath} is not a valid JSON file.");
    }
    return $jsonOutput;
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
    $childrenDir = $childData["childrenDirectory"];

    if ($parentContainer === null || $id == "root") {
        /* Create root container */
        $rootContainer = C\GenericContainer::createGenericContainerType($type, $id, $element, $classPreset);
        C\GenericContainer::setRootContainer($rootContainer);
        parseContainerGroups($children, $rootContainer);
    } else if ($content || $children || $childrenDir) {
        /* Create child container with children and/or content */
        $childContainer = $parentContainer->createChild($type, $id, $element, $classPreset);
        $childContainer->setContent($content);
        parseContainerGroupsChildren($children, $childrenDir, $childContainer);
    } else {
        throw new \Exception("Invalid container data for container with id {$id}.");
    }
}

function parseContainerGroupsChildren($childrenData, $childrenDir, $childContainer) {
    /* Only have either duplicated or normal children groups */
    if ($childrenData) {
        if (!isset($childrenData["ids"]))
            parseContainerGroups($childrenData, $childContainer);
        else
            parseDuplicatedChildrenData($childrenData, $childContainer);
    }

    /* Handle children directory by reading the JSON file in the directory and parsing it as children */
    if ($childrenDir) {
        $childrenDirData = parseJSONFile($childrenDir);
        parseContainerGroupsChildren($childrenDirData, null, $childContainer);
    }
}

function parseDuplicatedChildrenData($childrenData, $childContainer) {
    if (!is_array($childrenData["ids"])) {
        error_log("The following children, {$childrenData["ids"]}, were not processed, define as an array.");
        return;
    }

    /* Handle duplicated children by generating equivalent child containers per id, 
     * duplicated children must have the same type and element */
    $childrenIDData = array();
    foreach ($childrenData["ids"] as $childID) {
        $childrenIDData[$childID] = array(
            "containerType" => $childrenData["containerType"] ?? null,
            "element" => $childrenData["element"] ?? null,
            "classPreset" => 
                    $childrenData["classPreset"] ?? 
                    $childrenData["classPresetPerID"][$childID] ?? 
                    null,
            "content" => 
                    $childrenData["content"] ?? 
                    $childrenData["contentPerID"][$childID] ?? 
                    null,
            "children" => 
                    $childrenData["children"] ?? 
                    $childrenData["childrenPerID"][$childID] ?? 
                    null,
            "childrenDirectory" => 
                    $childrenData["childrenDirectory"] ?? 
                    $childrenData["childrenDirectoryPerID"][$childID] ?? 
                    null
        );
    }

    /* Add the duplicated children to the container */
    parseContainerGroups($childrenIDData, $childContainer);
}

}