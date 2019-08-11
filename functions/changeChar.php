<?php 
Include ("verifyLogin.php");
Include ("../connect.php");
Include("../data/buildings.php");
Include("../data/items.php");

$user = $_SESSION['login'];
$char = $_SESSION['char'];

$newCharId = FILTER_INPUT(INPUT_GET, 'change');

//$newChar = $_REQUEST['change'];

//Check if current user has control over character with given ID... IF so, switch the Char session
$query1 = 'SELECT * from `characters` WHERE `username` = :username AND `id` = :id';
$statement1 = $dbCon->prepare($query1);
$statement1->bindValue(':username', $user);
$statement1->bindValue(':id', $newCharId);
$statement1->execute();
$result1 = $statement1->fetch();
$statement1->closeCursor();

if ($result1)
{
	$_SESSION['char_id'] = $newCharId;
	$newCharObject = new Character($newCharId);
	$_SESSION['char'] = $newCharObject->character;


	$townId = $result1['town_id'];
	$townTableName = Towns::getTownTableName($townId);
	
	//find new character's x and Y co-ords and update SESSIONS [X and Y]
	$query2 = 'SELECT * FROM `' . $townTableName . '`';
	$statement2 = $dbCon->prepare($query2);
	$statement2->execute();
	$result2 = $statement2->fetchAll();
	$statement2->closeCursor();
	
	foreach ($result2 as $result)
	{
		$charsHere = explode('.', $result['charactersHere']);
		for ($i = 0; $i < sizeOf($charsHere); $i++)
		{
			if ($charsHere[$i] == $newCharId)
			{
				$_SESSION['x'] = $result['x'];
				$_SESSION['y'] = $result['y'];
			}
		}
	}
}




?>
</body>
</html>
