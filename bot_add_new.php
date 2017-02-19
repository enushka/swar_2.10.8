<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2012
 */

define('INSIDE'  ,  TRUE);
define('INSTALL' , FALSE);
define('LOGIN'   ,  TRUE);
define('XGP_ROOT',	'./');

$InLogin = TRUE;

include(XGP_ROOT . 'global.php');

includeLang('PUBLIC');

$parse = $lang;

	$errors = 0;
	$errorlist = "";


function generate_login($number)
{
$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','x','y','z','1','2','3','4','5','6','7','8','9','0');
$pass = "";

for($i = 0; $i < $number; $i++)
{
$index = rand(0, count($arr) - 1);
$pass .= $arr[$index];
}
return $pass;
}

    $rand_numb_nick = rand(4,15);
	$newbotname = generate_login($rand_numb_nick);

	$ExistUser = doquery("SELECT `username` FROM {{table}} WHERE `username` = '".$newbotname."' LIMIT 1;", 'users', TRUE);


		$newpass	= time();
		$UserName 	= $newbotname;
		$UserEmail 	= "";
		$md5newpass = md5($newpass);

		$QryInsertUser = "INSERT INTO {{table}} SET ";
		$QryInsertUser .= "`username` = '".$UserName."', ";
		$QryInsertUser .= "`ip_at_reg` = '" . $_SERVER["REMOTE_ADDR"] . "', ";
		$QryInsertAdm  .= "`user_agent` = '', ";
		$QryInsertUser .= "`id_planet` = '0', ";
		$QryInsertUser .= "`register_time` = '" . time() . "', ";
		$QryInsertUser .= "`password`='" . $md5newpass . "';";
		doquery($QryInsertUser, 'users');

		$NewUser = doquery("SELECT `id` FROM {{table}} WHERE `username` = '" . mysql_escape_string($newbotname) . "' LIMIT 1;", 'users', TRUE);

		$LastSettedGalaxyPos = read_config ( 'lastsettedgalaxypos' );
		$LastSettedSystemPos = read_config ( 'lastsettedsystempos' );
		$LastSettedPlanetPos = read_config ( 'lastsettedplanetpos' );

		while (!isset($newpos_checked))
		{
			for ($Galaxy = $LastSettedGalaxyPos; $Galaxy <= MAX_GALAXY_IN_WORLD; $Galaxy++)
			{
				for ($System = $LastSettedSystemPos; $System <= MAX_SYSTEM_IN_GALAXY; $System++)
				{
					for ($Posit = $LastSettedPlanetPos; $Posit <= 4; $Posit++)
					{
						$Planet = round (rand (4, 12));

						switch ($LastSettedPlanetPos)
						{
							case 1:
								$LastSettedPlanetPos += 1;
							break;
							case 2:
								$LastSettedPlanetPos += 1;
							break;
							case 3:
								if ($LastSettedSystemPos == MAX_SYSTEM_IN_GALAXY)
								{
									$LastSettedGalaxyPos += 1;
									$LastSettedSystemPos = 1;
									$LastSettedPlanetPos = 1;
									break;
								}
								else
								{
									$LastSettedPlanetPos = 1;
								}

								$LastSettedSystemPos += 1;
							break;
						}
						break;
					}
					break;
				}
				break;
			}

			$QrySelectGalaxy = "SELECT * ";
			$QrySelectGalaxy .= "FROM {{table}} ";
			$QrySelectGalaxy .= "WHERE ";
			$QrySelectGalaxy .= "`galaxy` = '" . $Galaxy . "' AND ";
			$QrySelectGalaxy .= "`system` = '" . $System . "' AND ";
			$QrySelectGalaxy .= "`planet` = '" . $Planet . "' ";
			$QrySelectGalaxy .= "LIMIT 1;";
			$GalaxyRow = doquery($QrySelectGalaxy, 'galaxy', TRUE);
			



			if ($GalaxyRow["id_planet"] == "0")
				$newpos_checked = TRUE;

			if (!$GalaxyRow)
			{
				CreateOnePlanetRecord ($Galaxy, $System, $Planet, $NewUser['id'], $UserPlanet, TRUE);
				$newpos_checked = TRUE;
			}
			if ($newpos_checked)
			{
				update_config ( 'lastsettedgalaxypos' , $LastSettedGalaxyPos );
				update_config ( 'lastsettedsystempos' , $LastSettedSystemPos );
				update_config ( 'lastsettedplanetpos' , $LastSettedPlanetPos );
			}
		}
		$PlanetID = doquery("SELECT `id` FROM {{table}} WHERE `id_owner` = '". $NewUser['id'] ."' LIMIT 1;" , 'planets', TRUE);

		$QryUpdateUser = "UPDATE {{table}} SET ";
		$QryUpdateUser .= "`id_planet` = '" . $PlanetID['id'] . "', ";
		$QryUpdateUser .= "`current_planet` = '" . $PlanetID['id'] . "', ";
		$QryUpdateUser .= "`galaxy` = '" . $Galaxy . "', ";
		$QryUpdateUser .= "`system` = '" . $System . "', ";
		$QryUpdateUser .= "`planet` = '" . $Planet . "' ";
		$QryUpdateUser .= "WHERE ";
		$QryUpdateUser .= "`id` = '" . $NewUser['id'] . "' ";
		$QryUpdateUser .= "LIMIT 1;";
		doquery($QryUpdateUser, 'users');
		
		$QryInsertUser = "INSERT INTO {{table}} SET ";
		$QryInsertUser .= "`player` = '".$NewUser['id']."', ";
		$QryInsertUser .= "`last_time` = '0', ";
		$QryInsertUser .= "`every_time` = '".rand(60,604800)."', ";
		$QryInsertUser .= "`last_planet` = '0', ";
		$QryInsertUser .= "`type` = '0';";
		doquery($QryInsertUser, 'bots');

		update_config ( 'users_amount' , read_config ( 'users_amount' ) + 1 );

		@include('config.php');
		$cookie = $NewUser['id'] . "/%/" . $UserName . "/%/" . md5($md5newpass . "--" . $dbsettings["secretword"]) . "/%/" . 0;
		setcookie(read_config ( 'cookie_name' ), $cookie, 0, "/", "", 0);

		unset($dbsettings);

		header("location:game.php?page=overview");
	  


?>