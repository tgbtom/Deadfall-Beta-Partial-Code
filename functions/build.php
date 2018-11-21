<?php

require_once("../model/database.php");
require_once("../model/structures.php");
require_once("../data/buildings.php");
require_once("../functions/queryFunctions.php");

$charDetails = getCharDetails();
$townName = $charDetails["townName"];

$buildName = filter_input(INPUT_POST, "buildName");
$apToAssign = filter_input(INPUT_POST, "apToAssign");

if (isset($buildName) && isset($apToAssign))
{
    //Create an object of the Structure that we are trying to upgrade, and an object of its town-specific stats
    for ($i=0; $i < count($buildingsInfo); $i++)
    {
        if ($buildingsInfo[$i][0] == $buildName)
        {
           $currentBuilding = new Structure($buildingsInfo[$i][0], $buildingsInfo[$i][1], $buildingsInfo[$i][2], $buildingsInfo[$i][3], $buildingsInfo[$i][4], $buildingsInfo[$i][5], $buildingsInfo[$i][6], $buildingsInfo[$i][7], $buildingsInfo[$i][8]);
           $builtDetails = StructuresDB::getBuiltDetails($buildName, $townName);
           //Ensure the building is not maxxed out Level
           //Check to see if it is a partial level (Resources already assigned)
           //Ensure AP to assign is <= what the character has for AP, and if the apToAssign exceeds the remaining ap left for the level, reduce apToAssign to the remaining AP needed
           //If apToAssign exceeds characters current AP, return to construction page with error ("Character no longer has 'x' AP")
            //If a new level is just being started, check again for sufficient resources, then remove them from bank and Add AP to structure.
        }
    }
}
else
{
    //If the page is being visisted without the correct data being transmitted, redirect to construction page
    echo "<script>window.location.href = '../inTown/?locat=construction'</script>";
}


