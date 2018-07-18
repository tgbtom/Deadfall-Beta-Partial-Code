<?php
//Building Name, Building Type, Required Building, AP cost, Item#.ItemAmount:Item2#.Item2Amount, Defence Given, Building Description
/*When a new building is added, it needs to be added to every town in databases as well
['buildingName.(1=Base Starting building|0=Buildings with prereq's)']*/
$buildingsInfo = array(
array("Defence", "Defence", "Defence", "1", "", "0", "Base requirement for all further Defence constructions."),
array("Outer Wall", "Defence", "Defence", "180", "2.15:8.3", "25", "Establish a perimeter around the settlement with large wooden boards."),
array("Inner Wall", "Defence", "Outer Wall", "100", "2.10:6.1", "10", "Create a secondary wall, just in case there is a breach in the main wall."),
array("Wall Upgrade 1", "Defence", "Outer Wall", "110", "2.10:6.1", "10", "Reinforce the outer wall."),
array("Wooden Support", "Defence", "Wall Upgrade 1", "150", "2.25:6.5", "25", "Add structural support the walls."),
array("Supply", "Supply", "Supply", "1", "", "0", "Base requirement for all further Supply Constructions."),
array("Water Reserve", "Supply", "Supply", "50", "5.30:0.5:3.2", "0", "Establish a holding area for all clean water reserves in the settlement. This construction allows collection from a limited storage of water rations.")
);
//Compiled Database Text Array below !*!*!*!*[ADD TO THIS EVERYTIME A BUILDING IS ADDED]*!*!*!*!
//Defence.1:Outer Wall.0:Inner Wall.0:Wall Upgrade 1.0:Wooden Support.0:Supply.1:Water Reserve.0
?>
