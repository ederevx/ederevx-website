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
        $containerDataParsed = parseContainerDataCommon($containerName, $containerData);
        parseContainerGroupsChild($containerDataParsed, $parentContainer);
    }
}

function parseContainerDataCommon($containerName, $containerData) {
    /* Generate all needed data by parseContainerGroupsChild */
    return array(
        "id" => $containerName,
        "containerType" => $containerData["containerType"] ?? null,
        "element" => $containerData["element"] ?? null,
        "classPreset" => 
                $containerData["classPreset"] ?? 
                $containerData["classPresetPerID"][$containerName] ?? 
                null,
        "content" => 
                $containerData["content"] ?? 
                $containerData["contentPerID"][$containerName] ?? 
                null,
        "children" => 
                $containerData["children"] ?? 
                $containerData["childrenPerID"][$containerName] ?? 
                null,
        "childrenDirectory" => 
                $containerData["childrenDirectory"] ?? 
                $containerData["childrenDirectoryPerID"][$containerName] ?? 
                null
    );
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

function parseContainerGroupsChildren($containerData, $childrenDir, $childContainer) {
    /* Only have either duplicated or normal children groups */
    if ($containerData) {
        if (!isset($containerData["ids"]))
            parseContainerGroups($containerData, $childContainer);
        else
            parseDuplicatedContainerData($containerData, $childContainer);
    }

    /* Handle children directory by reading the JSON file in the directory and parsing it as children */
    if ($childrenDir) {
        $childrenDirData = parseJSONFile($childrenDir);
        parseContainerGroupsChildren($childrenDirData, null, $childContainer);
    }
}

function parseDuplicatedContainerData($containerData, $childContainer) {
    if (!is_array($containerData["ids"])) {
        error_log("The following children, {$containerData["ids"]}, were not processed, define as an array.");
        return;
    }

    /* Handle duplicated children by generating equivalent child containers per id, 
     * duplicated children must have the same type and element */
    foreach ($containerData["ids"] as $childID) {
        $childIDDataParsed = parseContainerDataCommon($childID, $containerData);
        parseContainerGroupsChild($childIDDataParsed, $childContainer);
    }
}

}