<?php
require_once ("../connect.php");
require_once ("../functions/verifyLogin.php");

$locat = filter_input(INPUT_GET, 'locat');
if ($locat == NULL)
{
	$locat = 'browseChars';
}

switch ($locat)
{
	case 'browseChars':
	include ('characterBrowse.php');
	break;
	
	case 'construction':
	include ('construction.php');
	break;
	
	case 'construction':
	include ('construction.php');
	break;
	
	case 'warehouse':
	include ('warehouse.php');
	break;
	
	case 'citizens':
	include ('citizens.php');
	break;

	case 'outside':
	include ('outside.php');
	break;	
	
	case 'character':
	include ('character.php');
	break;
	
	case 'join':
	include ('joinTown.php');
	break;
	
	case 'inTown':
	include ('inTown.php');
	break;

	case 'special':
	include ('specialStructures.php');
	break;
	
	default: header ("Location: ../?error=Must Log In to Access the Requested Page");
}
?>