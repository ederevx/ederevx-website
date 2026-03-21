<?php
namespace Parser {

require_once "src/php/required/containers.php";

use Container as C;

function parseJSONFile($filePath) {
    if (!file_exists($filePath))
        throw new \InvalidArgumentException("File at path {$filePath} does not exist.");
    
    $jsonContent = file_get_contents($filePath);

    $jsonOutput = json_decode($jsonContent, true);
    if (!$jsonOutput)
        throw new \InvalidArgumentException("File at path {$filePath} is not a valid JSON file.");

    return $jsonOutput;
}

function parseContainerGroups($groupData, $parentContainer) {
    if (!$groupData)
        return;

    /* Iterate through the child's properties*/
    foreach ($groupData as $containerName => $containerData) {
        $containerDataParsed = parseContainerDataCommon($containerName, $containerData);
        parseContainerGroupsChild($containerDataParsed, $parentContainer);
    }
}

function parseContainerDataCommon($containerName, $containerData) {
    /* Assign container name data */
    $containerData["name"] = $containerName;

    if (!isset($containerData["idx"]))
        return $containerData;

    /* Parse *PerIdx properties */
    foreach ($containerData as $idx => $value) {
        $strPos = strpos($idx, "PerIdx");
        if (!$strPos)
            continue;

        if (!is_array($value))
            throw new \InvalidArgumentException("{$idx} value is not an array!");

        $targetIdx = str_replace("PerIdx", "", $idx);
        if (!isset($containerData[$targetIdx]))
            $containerData[$targetIdx] = $value[$containerName] ?? null;

        /* Remove the idx to avoid parsing it again in the child */
        unset($containerData[$idx]);
    }

    /* The data is no longer per idx, remove the idx */
    unset($containerData["idx"]);
    return $containerData;
}

function parseContainerGroupsChild($childData, $parentContainer) {
    if (!$childData || !is_array($childData))
        return;

    $name = $childData["name"];
    $children = $childData["children"] ?? null;
    $childrenDir = $childData["childrenDirectory"] ?? null;

    if ($parentContainer === null) {
        /* Create root container */
        $rootContainer = C\BaseContainer::createContainer($childData);
        C\BaseContainer::setRootContainer($rootContainer);
        parseContainerGroups($children, $rootContainer);
    } else {
        /* Create child container with children, if any */
        $childContainer = $parentContainer->createChild($name, $childData);
        if (!$childContainer)
            throw new \Exception("Child container was not created!");
        parseContainerGroupsChildren($children, $childrenDir, $childContainer);
    }
}

function parseContainerGroupsChildren($containerData, $childrenDir, $childContainer) {
    /* Only have either duplicated or normal children groups */
    if ($containerData) {
        if (!isset($containerData["idx"]))
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
    if (!is_array($containerData["idx"])) {
        error_log("The following children, {$containerData["idx"]}, were not processed, define as an array.");
        return;
    }

    /* Handle duplicated children by generating equivalent child containers per id, 
     * duplicated children must have the same type and element */
    foreach ($containerData["idx"] as $childID) {
        $childIDDataParsed = parseContainerDataCommon($childID, $containerData);
        parseContainerGroupsChild($childIDDataParsed, $childContainer);
    }
}

}