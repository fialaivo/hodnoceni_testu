<?php
session_start();
require "funkce.php";
$chyby = array();
$confirm = false;
if (array_key_exists("zaregistrovat", $_POST)) {  //uživatel vyplnil registrační formulář a odeslal ho
	var_dump($_POST);
	$login = $_POST["login"];
	$_SESSION["login"] = $login;
	$password = $_POST["password"];
	$email = $_POST["email"];
	var_dump(strlen($login));

	// OVĚŘENÍ ÚDAJŮ ZADANÝCH UŽIVATELEM 

	if (strlen($login)<3){    // kontrola délky stringu - přihlašovacího jména
		$chyby[] = "Přihlašovací jméno musí mít alespoň tři znaky";
	}
	$okMail = preg_match("/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/", $email);  // kontrola mailu 
	if (!$okMail){
		 $chyby[] = "Email nebyl zadán ve správném formátu"; 
	}

	$checkExistUser = checkExistUser($login, $email); //kontrola, zda není uživatel již zaregistrovaný (dopracovat - podrobnější výpis pro uživatele - zda se shoduje email nebo login)
	if ($checkExistUser != null){
		$chyby[] = "Uživatel s tímto loginem nebo emailem je již registrován";
	}

	if ((strlen($_POST["password"]) < 6 || strlen($_POST["controlPassword"]) < 6)){  //kontrola délky hesla - minimálně 6 znaků
		$chyby[] = "Heslo musí být dlouhé minimálně šest znaků";
	}
	if ($_POST["password"] !=  $_POST["controlPassword"]) {   //kontrola zda se hesla shodují
		$chyby[] = "Zadaná hesla se neshodují";
	}

	$pin = random_int(1000, 9999);  //generování náhodného pinu
	if (count($chyby) != 0) {		//pokud se v zadaných registračních údajích vyskytují chyby - ruším proměnnou $_POST["zaregistrovat"]
		unset($_POST["zaregistrovat"]);
	}
	else{							//pokud jsou zadané údaje v registračním formuláři v pořádku - ukládám uživatele do databáze a posílám mail s PINem na email
		ulozit_uzivatele($login, $password, $email, $pin); 
		send_mail($email, $pin);
	}
}

if (array_key_exists("odeslat", $_POST)) {
	$inputPin = intval($_POST["pin"]);
	$kontrolaPin = checkPin($inputPin, $_SESSION["login"]);
	var_dump($kontrolaPin);
	$confirm = true;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<title>Hodnocení testů</title>
	<link rel="stylesheet" href="fonts/fontawesome/css/all.min.css">
	<link rel="shortcut icon" href="img/test_icon.png" type="image/x-icon">
	<link rel="stylesheet" href="css/reset.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<header>
		<div class="nadpis">
			<img src="img/form_icon.png" alt="" width="200px" height="200px">
			<h1>Hodnocení <br> testů</h1>
		</div>
	</header>
	<section>
		<div class="container">
			<?php
			if (!(array_key_exists("zaregistrovat", $_POST)) && $confirm == false) {
			?>
				<form method="post">
					<label for="login">Přihlašovací jméno:</label>
					<input type="text" name="login" id="login">
					<label for="email">Email:</label>
					<input type="text" name="email" id="email">
					<label for="password">Heslo:</label>
					<input type="password" name="password" id="password">
					<label for="controlPassword">Kontrola hesla:</label>
					<input type="password" name="controlPassword" id="controlPassword">
					<button name="zaregistrovat">Zaregistrovat</button>
				</form>
				<div class="loginError">
					<?php
					//var_dump($chyby);
					if (count($chyby) != 0) {  //pokud se v pole chyb není prázdné, vypiš chyby
						foreach ($chyby as $chyba) {
						echo "<div><img id='error' src='img/error_icon.png'> $chyba </div> ";
						}
					}
					?>
				</div>
			<?php
			} else if (array_key_exists("zaregistrovat", $_POST) && $confirm == false) {
			?>
				Registrace proběhla úspěšně, zadejte ověřovací PIN z emailu:
				<form method="post">
					<input type="text" name="pin" id="pin">
					<button name="odeslat">Odeslat</button>
				</form>
			<?php
			}
			else{
				echo "Registrace proběhla úspěšně, pokračujte na -> <a href='http://www.spsdarkweb.wz.cz/'> Hodnocení testů </a> ";
			}
			?>
		</div>
	</section>

</body>

</html>