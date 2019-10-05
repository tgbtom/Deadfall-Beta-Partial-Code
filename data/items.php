<?php

//Item Name, Category, Weight, Rarity /////////////////////Rarity -> 0 = Common, 1 = Uncommon, 2 = Rare, 3 = Ultra-Rare, 4 = Legendary, 5 = SCRAP
$itemsMaster = array(
/*0*/	array("Water Ration", "Consume", 2, 2, "Drink this to quench your thirst. Fully replenishes AP if you have not drank today."),
		array("Bits of Food", "Consume", 1, 2, "These scraps aren't going to cut it for very long. Restores 50% AP if you have not eaten today."),
		array("Wood Board", "Resource", 2, 0, "Sturdy wooden board, could be useful for building."),
		array("Sheet Metal", "Resource", 3, 0, "Spear handcrafted with a wooden stick and a sharp tip."),
		array("Makeshift Spear", "Weapon", 2, 3, "Spear handcrafted with a wooden stick and a sharp tip."),
/*5*/	array("Stone", "Resource", 2, 5, "Just a solid stone"),
		array("Bow", "Weapon", 2, 3, "A basic longbow, can use arrows to kill zombies."),
		array("Arrow", "Ammo", 1, 2, "Ammunition for bows."),
		array("Rope", "Resource", 2, 1, "A length of rope, could be used to tie things together."),
		array("Battery", "Resource", 2, 1, "A basic battery."),
/*10*/	array("Brick", "Resource", 2, 0, "A basic building material."),
		array("Cloth", "Resource", 2, 1, "Piece of cloth material."),
		array("Grenade", "Weapon", 2, 4, "A questionably old explosive."),
		array("Pistol", "Weapon", 2, 4, "A basic handgun, can be used while holding small bullets."),
		array("Slingshot", "Weapon", 2, 3, "Weapon used to propel rocks at unsuspecting targets."),
/*15*/	array("Rock", "Ammo", 1, 2, "A rock, can be launched with a slingshot."),
		array("Small Bullet", "Ammo", 1, 3, "Ammunition for small arms."),
		array("Sharp Stick", "Weapon", 2, 2, "Just a stick that has been sharpened at one end."),
		array("Assault Rifle", "Weapon", 2, 4, "Machine gun, uses medium ammo that has been packed into ammo magazines."),
		array("Bag of Sand", "Resource", 2, 5, "Just a bag of dirt."),
/*20*/	array("Empty Mag", "Ammo", 2, 3, "Empty ammo magazine, can be filled with medium bullets."),
		array("Full Mag", "Ammo", 3, 4, "Full ammo magazine - One time use."),
		array("Medium Bullet", "Ammo", 1, 2, "Ammunition for medium guns."),
		array("Carrot", "Consume", 2, 2, "A fresh picked Carrot. Restores 80% AP if you have not eaten today.")
	);

//itemID (Primary Key), function (0 -> Eat, 1 -> Drink, 2 -> Use), AP granted (percentage)
$itemsConsumable = array(
		array(0, 1, 100), 	/*Water Ration*/
		array(1, 0, 50), 	/*Bits of Food*/
		array(20, 3, 0), 	/*Empty Mag*/
		array(23, 0, 80) 	/*Carrot*/
	);

//itemID (Primary Key), Ammo ID (-1 means none), AP Cost, Min. Kills, Max. Kills, chance to break (out of 100), chance to get injured (out of 100), chance of ammo output, ammo output ID (-1) is for none
$itemsWeapon = array(
		array(4, -1, 1, 1, 3, 20, 5, 0, -1), //MakeShift Spear
		array(6, 7, 1, 0, 1, 5, 2, 40, 7), //Bow (40% chance to return an arrow to inventory)
		array(12, -1, 0, 4, 15, 100, 10, 0, -1), //Grenade
		array(13, 16, 0, 1, 3, 1, 1, 0, -1), //Pistol
		array(14, 15, 1, 0, 1, 20, 5, 50, 15), //Slingshot
		array(17, -1, 1, 0, 1, 5, 4, 0, -1), //Sharp Stick
		array(18, 21, 0, 10, 20, 1, 1, 100, 20) //Assault Rifle
	);

$itemsContainer = array();
		
// echo `<script type="text/javascript">

// /**********************************************************************************
// ***********************************************************************************
// ****JavaScript Array is used to disply the item details on the Outside map*********
// ***********************************************************************************
// **********************************************************************************/
// //ItemName, ItemDesc, Item Weight
// var itemsInfo = [
// /*0*/	["Water Ration", "Drink this to quench your thirst.", 2],
// 		["Bits of Food", "These scraps aren't going to cut it for very long.", 1],
// 		["Wood Board", "Sturdy wooden board, could be useful for building.", 2],
// 		["Makeshift Spear", "Spear handcrafted with a wooden stick and a sharp tip.", 2],
// /*5*/	["Stone", "Just a solid stone", 2],
// 		["Bow", "A basic longbow, can use arrows to kill zombies.", 2],
// 		["Arrow", "Ammunition for bows.", 1],
// 		["Rope", "A length of rope, could be used to tie things together.", 2],
// 		["Battery", "A basic battery.", 2],
// /*10*/	["Brick", "A basic building material.", 2],
// 		["Cloth", "Piece of cloth material.", 2],
// 		["Grenade", "A questionably old explosive.", 2],
// 		["Pistol", "A basic handgun, can be used while holding small bullets.", 2],
// 		["Slingshot", "Weapon used to propel rocks at unsuspecting targets.", 2],
// /*15*/	["Rock", "A rock, can be launched with a slingshot.", 1],
// 		["Small Bullet", "Ammunition for small arms.", 1],
// 		["Sharp Stick", "Just a stick that has been sharpened at one end.", 2],
// 		["Assault Rifle", "Machine gun, uses medium ammo that has been packed into ammo magazines.", 2],
// 		["Bag of Sand", "Just a bag of dirt.", 2],
// /*20*/	["Empty Mag", "Empty ammo magazine, can be filled with medium bullets.", 2],
// 		["Full Mag", "Full ammo magazine - One time use.", 3],
// 		["Medium Bullet", "Ammunition for medium guns.", 1]
// ];

// </script>`;
?>

