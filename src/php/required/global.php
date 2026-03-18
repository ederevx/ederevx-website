<?php

/* Load containers data */
$jsonFileContainerGrps = file_get_contents("src/json/containerGroups.json");
$containerGrpsData = json_decode($jsonFileContainerGrps, true);