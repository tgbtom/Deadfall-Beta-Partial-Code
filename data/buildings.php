<?php
//Building Name, Building Type, Required Building, AP cost, Item#.ItemAmount:Item2#.Item2Amount, Defence Given, Maximum Level, Building Description
/*When a new building is added, it needs to be added to every town in databases as well
['buildingName.(1=Base Starting building|0=Buildings with prereq's).buildingLevel']*/

$buildingsInfo = array(
array("Defence", "Defence", "Defence", "1", "", "0", "1", "Defensive constructions that help to defend from the horde.", "0"),
array("Perimeter Fence", "Defence", "Defence", "45", "2.10:8.2", "50", "5", "Build a fence establishing a perimeter for the settlement. Will Keep some zeds out.", ""),
array("Wooden Wall", "Defence", "Perimeter Fence", "80", "2.20:8.5", "100", "10", "Improve the perimeter around the settlement with a large wooden wall.", "&nbsp→"),
array("Inner Wall", "Defence", "Wooden Wall", "90", "2.5:10.15", "75", "10", "Create a secondary wall, just in case there is a breach in the main wall.", "&nbsp&nbsp→"),
array("Trenches", "Defence", "Inner Wall", "100", "2.2:3.2:10.2", "40", "15", "Dig out trenches between the outer and inner wall. Great place to set traps.", "&nbsp&nbsp&nbsp→"),
array("Spike Pits", "Defence", "Inner Wall", "35", "17.3:8.6:3.5", "65", "5", "Set up sharp spikes in some areas of the trenches to impale falling zeds.", "&nbsp&nbsp&nbsp→"),
array("Wooden Support", "Defence", "Wooden Wall", "40", "2.15", "30", "5", "Add structural support the walls.", "&nbsp&nbsp→"),
array("Metal Patching", "Defence", "Wooden Wall", "85", "3.15", "115", "5", "Reinforce the outer wall with metal.", "&nbsp&nbsp→"),
array("Sentry Tower", "Defence", "Wooden Wall", "120", "3.25:2.5:10.5", "85", "4", "Each level will build a sentry tower on a different section of the wall. Used as a lookout.", "&nbsp&nbsp→"),
array("MG Nest", "Defence", "Wooden Wall", "50", "18.1:21.3:19.10", "250", "2", "Set up machine gun nests near the perimeter.", "&nbsp&nbsp→"),
array("Supply", "Supply", "Supply", "1", "", "0", "1", "Constructions that revolve around supplies/service.", "0"),
array("Water Reserve", "Supply", "Supply", "50", "5.10:0.5:3.2", "0", "1", "Establish a holding area for all clean water reserves in the settlement. Once built; provides 2-4 water rations per day.", ""),
array("Vegetable Garden", "Supply", "Supply", "85", "0.10", "0", "1", "A small plot to grow vegetables. Generates food overnight. Grows 1-4 food per night.", ""),
array("Production", "Production", "Production", "1", "", "0", "1", "Constructions that Correspond with Production Tasks.", "0"),
array("Fabrikator Workshop", "Production", "Production", "45", "2.10:3.10:8.10", "0", "1", "Allows the conversion of basic resources at a 3:1 Ratio.", "")
);
//Compiled Database Text Array below !*!*!*!*[ADD TO THIS EVERYTIME A BUILDING IS ADDED]*!*!*!*!
// Defence.0.1:Perimeter Fence.0.0:Wooden Wall.0.0:Inner Wall.0.0:Trenches.0.0:Spike Pits.0.0:Wooden Support.0.0:Metal Patching.0.0:Sentry Tower.0.0:MG Nest.0.0:Supply.0.1:Water Reserve.0.0:Vegetable Garden.0.0:Production.0.1:Fabrikator Workshop.0.0

?>
