<?php

use Container as C;
use Parser as P;

function initialize() {
    global $containerGrpsData;
    if (!$containerGrpsData) {
        throw new Exception("Container groups data is not available.");
    }

    P\parseContainerGroups($containerGrpsData, null);

    /* Check if root container is initialized */
    if (C\GenericContainer::getRootContainer() === null) {
        throw new Exception("Root container is not initialized.");    
    } else {
        return 1;
    }
}

return initialize();