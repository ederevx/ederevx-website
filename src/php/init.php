<?php

use Container as C;
use Parser as P;

function initialize() {
    global $containerTplDir;
    if (!$containerTplDir) {
        throw new Exception("Container root directory is not set.");
    }
    C\BaseContainer::setcontainerTplDirectory($containerTplDir);

    global $rootJSONContainerGrpsDir;
    if (!$rootJSONContainerGrpsDir) {
        throw new Exception("Root JSON container groups directory is not set.");
    }
    $rootContainerGrpsData = P\parseJSONFile($rootJSONContainerGrpsDir);
    if (!$rootContainerGrpsData) {
        throw new Exception("Root container groups data is not available.");
    }
    P\parseContainerGroups($rootContainerGrpsData, null);

    /* Check if root container is initialized */
    if (C\BaseContainer::getRootContainer() === null) {
        throw new Exception("Root container is not initialized.");    
    } else {
        return 1;
    }
}

return initialize();