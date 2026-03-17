<?php

use Container as C;

function initialize() {
    global $containerGrpsData;
    if (!$containerGrpsData) {
        return throw new Exception("Container groups data is not available.");
    }

    parseContainerGroups($containerGrpsData, null);

    /* Check if root container is initialized */
    if (C\GenericContainer::getRootContainer() === null) {
        return throw new Exception("Root container is not initialized.");    
    } else {
        return 1;
    }
}

function parseContainerGroups($groupData, $parentContainer) {
    if (!$groupData) {
        return;
    }

    foreach ($groupData as $containerName => $containerData) {
        $id = $containerName;
        $type = $containerData["containerType"];
        $element = $containerData["element"] ?? null;
        $classPreset = $containerData["classPreset"] ?? null;
        $content = $containerData["content"] ?? null;
        $children = $containerData["children"] ?? [];

        if ($parentContainer === null) {
            /* Create root container */
            $rootContainer = C\GenericContainer::createGenericContainerType($type, $id, $element, $classPreset);
            C\GenericContainer::setRootContainer($rootContainer);
            parseContainerGroups($children, $rootContainer);
        } else if ($content || $children) {
            /* Create child container with child and/or content */
            $childContainer = $parentContainer->createChild($type, $id, $element, $classPreset);
            if ($content) {
                if ($content === "php") {
                    $childContainer->setContentPHP();
                } else if ($content === "html-php") {
                    $childContainer->setContentHTMLPHP();
                } else {
                    $childContainer->setContent($content);
                }
            }
            if ($children) {
                parseContainerGroups($children, $childContainer);
            }
        } else {
            throw new Exception("Invalid container data for container with id {$id}.");
        }
    }
}

return initialize();