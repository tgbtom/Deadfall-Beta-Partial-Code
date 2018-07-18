<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");
Include ("../data/buildings.php");
Include ("../data/items.php");
require_once ('../functions/queryFunctions.php');

$errorMessage = FILTER_INPUT(INPUT_GET, 'e');
if (isset($errorMessage))
{
	echo "<script type='text/javascript'>alert('$errorMessage');</script>";
}
?>

<html>
<head>

<link rel="stylesheet" type="text/css" href="mainDesignTown.css">

<?php
$playerName = $_SESSION['login'];
$charName = $_SESSION['char'];
$xCo = $_SESSION['x'];
$yCo = $_SESSION['y'];
$reqMet = false;
$itemAmt = 0;

//Set Variables which correspond with the character that is in session (town name, level, class, etc.)
$charDetails = getCharDetails();
$townName = $charDetails['townName'];
$charLevel = $charDetails['level'];
$charClass = $charDetails['class'];

//Determine Items in the town bank (coords 0,0)
$query = 'SELECT * FROM `' . $townName . '` WHERE `x` = 0 AND `y` = 0';
$statement = $dbCon->prepare($query);
$statement->execute();
$result = $statement->fetch();
$statement->closeCursor();

$townBank = explode(',', $result['groundItems']);

?>


	<link rel="stylesheet" type="text/css" href="mainDesignTown.css">
	<style>
	table {width:100%; color:#000000; border: 1px solid #120B06; padding:2px; background-color:#6d5846;}
	input.number {width:40px;}
	.data {width:150px; border: 1px solid black;}
	.newBuild {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6d5846;}
	.alreadyBuilt {color:#111111; border: 1px solid #120B06; border-collapse:collapse; padding:2px; background-color:#6d5846;}
	.buildingHelp {cursor:help;}
	style1 {font-size:0.8em;}
	.head {border: 1px solid black;}
	</style>
</head>

<body>

<div class="Container">

	<?php include("../universal/header.php"); ?>
	
	<div class="centralBox">

		<h3>Construction Sites</h3>
		
		<div class="spoiler">
	
			<table>
			<tr><td class='head'>Building</td><td class='head'>Resources</td><td class='head' colspan='2'>AP</td><td class='head'>Build</td>
			<?php
	
			for ($i = 0; $i < count($buildingsInfo); $i++)
				{
					//  ***Needs to Not Display buldings that are constructed (7/16/2016)
					//If town has req building, draw, otherwise dont
					$buildName = $buildingsInfo[$i][0]; //innerwall
					$buildReq = $buildingsInfo[$i][2]; // outerwall
					$buildApReq = $buildingsInfo[$i][3]; //200
					
					//for loop to determine the required ap for the required building (so we can make sure the previoius building is complete)
					for ($i6 = 0; $i6 < count($buildingsInfo); $i6++)
					{
						if ($buildingsInfo[$i6][0] == $buildReq)
						{
							$preReqNeededAp = $buildingsInfo[$i6][3];
						}
					}
					
					//check database and retrieve the built buildings for this townName
					
					//$query1 = 'SELECT * FROM `towns` WHERE `townName` = :townName';
					//$statement1 = $dbCon->prepare();
					
					$query3 = "SELECT * FROM `towns` WHERE `townName` = '$townName'";
					$query4 = mysqli_query($con, $query3);
					
					while ($row = mysqli_fetch_assoc($query4))
						{
							$currentBuilt = explode (":", $row['buildings']);
							$allBuilt = array();
							$allBuiltAp = array();
							//Loops to create arrays which individually state all partially/fully constructed buildings and the AP which has been spent  on them
								for ($i4 = 0; $i4 < count($currentBuilt); $i4++)
								{
									$nameOfBuilt = explode(".", $currentBuilt[$i4]);
									array_push($allBuilt, $nameOfBuilt[0]);
									array_push($allBuiltAp, $nameOfBuilt[1]);
								}
							
								//check if building requirements are met
								if (in_array($buildReq, $allBuilt))
								{
									$z = array_search($buildReq, $allBuilt);
									$reqBuiltAp = $allBuiltAp[$z];
									//$preReqNeededAp = array_search ();
									
									if ($reqBuiltAp >= $preReqNeededAp)
									{
										$reqMet = true;
									}
									else 
									{
										$reqMet = false;
									}
								}
								
								
									$x = array_search($buildName, $allBuilt);
									$currentBuiltName = $allBuilt[$x];
									$currentBuiltAp = $allBuiltAp[$x];
									
									//if the building is not started already, then continue with the for statement, which adds the building to the construction list.
									if (!(in_array($buildName, $allBuilt)))
									{
										if ($reqMet)
										{
											$bankFull = true;
											$buildCost = explode (":",$buildingsInfo[$i][4]);
											echo '<tr class="newBuild"><td class ="buildingHelp" id="" title="' . $buildingsInfo[$i][6] . '"><p style="font-size:1.1em; font-family:`impact`, charcoal, sans-serif;">' . $buildName . '</p></td>
											<td class="data"><style1>';
											
											for ($i2 = 0; $i2 < count($buildCost); $i2++)
											{
											
												$itemSpec = explode (".", $buildCost[$i2]);
												$itemId = $itemSpec[0];
												
												//check town BANK for how many of the item is stored
												//$townBank = explode(":", $row['itemBank']);
												for ($i5 = 1; $i5 < count($townBank); $i5++)
												{
													$townBank2 = explode(".", $townBank[$i5]);
													if ($townBank2[0] == $itemsMaster[$itemId][0])
													{
														$itemAmt = $townBank2[1];
													}
												}

												$itemImg = '<img title="' . $itemsMaster[$itemId][0] . '" src="../images/' . $itemsMaster[$itemId][0] . '.png">';
												echo $itemImg . $itemAmt . '/' . $itemSpec[1] . ' ';
												if ($itemAmt < $itemSpec[1])
												{$bankFull = false;}
											}
											
											echo '</style1></td>';
											if ($bankFull)
											{
											echo '<td><form action="../functions/build.php" method="post"><input type="number" class="number" value="0" min="0" max="12"></td><td><p style="font-size:0.85em;">' . $currentBuiltAp . '/' . $buildApReq . ' AP</p></td>
											<td><input type="hidden" name="buildingName" value="' . $buildName . '"><input type="image" src="../images/construct.png" style="float:right;" alt="submit"></td></tr>';
											}
										}	
									}
									
									//if the building is there but Has NOT been started yet
									else if ($currentBuiltAp == 0) //If current built AP is 0
									{
										if ($reqMet)
										{
											$buildCost = explode (":",$buildingsInfo[$i][4]);
											echo '<tr class="newBuild"><td class ="buildingHelp" title="' . $buildingsInfo[$i][6] . '"><p style="font-size:1.1em; font-family:`impact`, charcoal, sans-serif;">' . $buildName . '</p></td>
											<td class="data"><style1>';
											$bankFull = true;
											for ($i2 = 0; $i2 < count($buildCost); $i2++)
											{
											
												$itemSpec = explode (".", $buildCost[$i2]);
												$itemId = $itemSpec[0];
												$itemAmt = 0;
												
												//check town BANK for how many of the item is stored
												//$townBank = explode(":", $row['itemBank']);
												for ($i5 = 0; $i5 < sizeOf($townBank); $i5++)
												{
													$townBank2 = explode(".", $townBank[$i5]);
													if ($townBank2[0] == $itemId/*$itemsMaster[$itemId][0]*/)
													{
														$itemAmt = $townBank2[1];
													}

												}

												$itemImg = '<img title="' . $itemsMaster[$itemId][0] . '" src="../images/' . $itemsMaster[$itemId][0] . '.png">';
												echo $itemImg . $itemAmt . '/' . $itemSpec[1] . ' ';
												if ($itemAmt < $itemSpec[1])
												{$bankFull = false;}
											}
											
											echo '</style1></td>';
											if ($bankFull)
											{
											echo '<form action="../functions/build.php" method="post"><td>
											<input type="number" class="number" name="apToAdd" value="0" min="0" max="16"></td><td><p style="font-size:0.85em;">0/' . $buildApReq . ' AP</p></td>
											<td><input type="hidden" name="firstBuild" value="true">
											<input type="hidden" name="buildingName" value="' . $buildName . '">
											<input type="hidden" name="apRequired" value="' . $buildApReq . '">
											<input type="image" src="../images/construct.png" style="float:right;"></td></tr></form>';
											}
										}
									}
									
									//If the building name is there, check if the AP is incomplete. If it is incomplete: do the following
									else if ($currentBuiltAp < $buildApReq) //If current built AP is less than required
									{
										if ($reqMet)
										{
											$buildCost = explode (":",$buildingsInfo[$i][4]);
											echo '<tr class="newBuild"><td class ="buildingHelp" title="' . $buildingsInfo[$i][6] . '"><p style="font-size:1.1em; font-family:`impact`, charcoal, sans-serif;">' . $buildName . '</p></td>
											<td class="data"><style1>';
											$bankFull = true;
											for ($i2 = 0; $i2 < count($buildCost); $i2++)
											{
											
												$itemSpec = explode (".", $buildCost[$i2]);
												$itemId = $itemSpec[0];
												$itemAmt = 0;
												
												//check town BANK for how many of the item is stored
												//$townBank = explode(":", $row['itemBank']);
												for ($i5 = 1; $i5 < sizeOf($townBank); $i5++)
												{
													$townBank2 = explode(".", $townBank[$i5]);
													if ($townBank2[0] == $itemId/*$itemsMaster[$itemId][0]*/)
													{
														$itemAmt = $townBank2[1];
													}

												}
											}
											
											echo 'Construction has Begun!</style1></td>';
											echo '<form action="../functions/build.php" method="post"><td>
											<input type="number" class="number" name="apToAdd" value="0" min="0" max="16"></td><td><p style="font-size:0.85em;">' . $currentBuiltAp . '/' . $buildApReq . ' AP</p></td>
											<td>
											<input type="hidden" name="buildingName" value="' . $buildName . '">
											<input type="hidden" name="apRequired" value="' . ($buildApReq - $currentBuiltAp) . '">
											<input type="image" src="../images/construct.png" style="float:right;"></td></tr></form>';
										}
									}
									
									else
									{
										if ($reqMet)
										{
											$buildCost = explode (":",$buildingsInfo[$i][4]);
											echo '<tr class="alreadyBuilt"><td class ="buildingHelp" title="' . $buildingsInfo[$i][6] . '"><p style="font-size:1.1em; font-family:`impact`, charcoal, sans-serif;">' . $buildingsInfo[$i][0] . '</p></td>
											<td class="data-done data" colspan="4"><style1>';
											
											echo 'Already Completed!';
											
											echo '</style1></td></tr>';
										}
									}
									
								
						}
				}
			echo "</table>";
			?>
		</div>
	</div>
	

		
	
</div>
<?php
	Include ("../universal/hyperlinks.php");
	?>
</body>

</html>