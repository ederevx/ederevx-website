<?php

use Container as C;
use Parser as P;

function initialize() {
    global $containerClassesDir;
    if ($containerClassesDir) {
        $containerClassesData = P\parseJSONFile($containerClassesDir);
        C\GenericContainer::setContainerClasses($containerClassesData);
    } else {
        error_log("There are no container classes defined!");
    }

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
    if (C\GenericContainer::getRootContainer() === null) {
        throw new Exception("Root container is not initialized.");    
    } else {
        return 1;
    }
}

return initialize();