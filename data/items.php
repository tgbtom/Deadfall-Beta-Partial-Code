<?php

//Item Name, Category, Weight, Rarity /////////////////////Rarity -> 0 = Common, 1 = Uncommon, 2 = Rare, 3 = Ultra-Rare, 4 = Legendary
$itemsMaster = array(
/*0*/	array("Water Ration", "Consume", 1, 2),
		array("Bits of Food", "Consume", 1, 2),
		array("Wood Board", "Resource", 1, 0),
		array("Sheet Metal", "Resource", 2, 1),
		array("Makeshift Spear", "Weapon", 1, 3),
/*5*/	array("Stone", "Resource", 1, 0),
		array("Bow", "Weapon", 1, 3),
		array("Arrow", "Ammo", 0, 2),
		array("Rope", "Resource", 1, 1),
		array("Battery", "Resource", 1, 1),
/*10*/	array("Brick", "Resource", 1, 0),
		array("Cloth", "Resource", 1, 1),
		array("Grenade", "Weapon", 1, 4),
		array("Pistol", "Weapon", 1, 4),
		array("Slingshot", "Weapon", 1, 3),
/*15*/	array("Rock", "Ammo", 0, 2),
		array("Small Bullet", "Ammo", 0, 3),
		array("Sharp Stick", "Weapon", 1, 2)
	);

//itemID (Primary Key), function (0 -> Eat, 1 -> Drink, 2 -> Use), AP granted 
$itemsConsumable = array(
		array(0, 1, 16),
		array(1, 0, 10)
	);

//itemID (Primary Key), Ammo ID (-1 means none), AP Cost, Min. Kills, Max. Kills, chance to break (out of 100), chance to get injured (out of 100)
$itemsWeapon = array(
		array(4, -1, 1, 0, 1, 20, 5), //MakeShift Spear
		array(6, 7, 1, 0, 1, 5, 2), //Bow
		array(12, -1, 0, 4, 15, 100, 10), //Grenade
		array(13, 16, 0, 1, 3, 1, 1), //Pistol
		array(14, 15, 0, 0, 1, 20, 5), //Slingshot
		array(17, -1, 1, 1, 1, 5, 4) //Sharp Stick
	);

$itemsContainer = array();
		
?>

<script type="text/javascript">

/**********************************************************************************
***********************************************************************************
****JavaScript Array is used to disply the item details on the Outside map*********
***********************************************************************************
**********************************************************************************/

var itemsInfo = [
/*0*/	["Water Ration", "Drink this to quench your thirst."],
		["Bits of Food", "These scraps aren't going to cut it for very long."],
		["Wood Board", "Sturdy wooden board, could be useful for building."],
		["Sheet Metal", "Heavy sheet of metal, could be useful for building."],
		["Makeshift Spear", "Spear handcrafted with a wooden stick and a sharp tip."],
/*5*/	["Stone", "Just a solid stone"],
		["Bow", "A basic longbow, can use arrows to kill zombies."],
		["Arrow", "Ammunition for bows."],
		["Rope", "A length of rope, could be used to tie things together."],
		["Battery", "Drink this to quench your thirst."],
/*10*/	["Brick", "These scraps aren't going to cut it for very long."],
		["Cloth", "Sturdy wooden board, could be useful for building."],
		["Grenade", "Heavy sheet of metal, could be useful for building."],
		["Pistol", "Spear handcrafted with a wooden stick and a sharp tip."],
		["Slingshot", "Just a solid stone"],
/*15*/	["Rock", "A basic longbow, can use arrows to kill zombies."],
		["Small Bullet", "Ammunition for bows."],
		["Sharp Stick", "Ammunition for bows."]
];

</script>