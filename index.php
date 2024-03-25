<?php
session_start();
//zpracování odhlašovacího formuláře

if (array_key_exists("odhlasit", $_GET)) {
	unset($_SESSION["prihlasenyUzivatel"]);
	header("Location: ?");
}
require "funkce.php";
$errorLogin = null;
$errorInput = null;
$nazevTestu = null;
$trida = null;
$datum = null;
//zpracování přihlašovacího formuláře
if (array_key_exists("prihlasit", $_POST)) {
	$_GET["nazevTestu"] = null;
	$_GET["trida"] = null;
	$_GET["datum"] = null;
	$jmeno = $_POST["jmeno"];
	$heslo = $_POST["heslo"];
	$_SESSION["prihlasenyUzivatel"] = checkUser($jmeno, $heslo);
	//var_dump($_SESSION["prihlasenyUzivatel"]);
	if ($_SESSION["prihlasenyUzivatel"]==null) {
		unset($_SESSION["prihlasenyUzivatel"]);
		$errorLogin = "Nesprávné přihlašovací údaje";
	}
}

$znamkyPopis = ["1", "1-", "2", "2-", "3", "3-", "4", "4-", "5"];
$reverseBodoveIntervaly = [];
$bodoveIntervaly = [];
$bodoveIntervalyFinal = [];
$celkovyPocetBodu = null;
$procentniHranice = null;
$opakuj = true;   
if (array_key_exists("smazat", $_GET)) {
	smazat($_GET["id"]);
}
if (array_key_exists("zobraz_historie", $_GET)) {
	$historie = zobraz_historie($_GET["id"]);
	$celkovyPocetBodu = $historie["celkovy_pocet_bodu"];
	$procentniHranice = $historie["procentni_hranice"];
	$trida = $historie["trida"];
	$datum = $historie["datum"];
	$nazevTestu = $historie["nazev_testu"];

}
if (array_key_exists("zobraz", $_GET)) {
	//var_dump($_GET);
	if (empty($_GET["celkovyPocetBodu"]) || empty($_GET["procentniHranice"])) {
		$errorInput = "Není zadán celkový počet bodů nebo procentní hranice";
	} else {
		if (array_key_exists("prihlasenyUzivatel", $_SESSION) == true) {
			$nazevTestu = $_GET["nazevTestu"];
			$trida = $_GET["trida"];
			;
			$datum = $_GET["datum"];
		}
		$celkovyPocetBodu = $_GET["celkovyPocetBodu"];
		$procentniHranice = $_GET["procentniHranice"];

		$bodoveIntervaly[0] = ($celkovyPocetBodu / 100) * $procentniHranice;

		$zbytek = $celkovyPocetBodu - $bodoveIntervaly[0];
		$rozdeleni = $zbytek / 8;
		//var_dump($rozdeleni);

		for ($i = 1; $i <= 8; $i++) {
			$bodoveIntervaly[$i] = $bodoveIntervaly[0] + ($rozdeleni * $i);
		}

		//var_dump($bodoveIntervaly);
		$bodoveIntervalyFinal[0] = 0.0;

		$j = 0;
		for ($i = 1; $i < 17; $i = $i + 2) {
			$bodoveIntervalyFinal[$i] = zaokrouhlitDoluNaPul($bodoveIntervaly[$j]);
			$bodoveIntervalyFinal[$i + 1] = $bodoveIntervalyFinal[$i] + 0.5;
			$j++;
		}
		$bodoveIntervalyFinal[17] = floatval($celkovyPocetBodu);

		//var_dump($bodoveIntervalyFinal);
		$reverseBodoveIntervaly = array_reverse($bodoveIntervalyFinal);
	}
}
if (array_key_exists("uloz", $_GET)) {
	//var_dump($_GET);
	$nazevTestu = $_GET["nazevTestu"];
	$trida = $_GET["trida"];
	$datum = $_GET["datum"];
	$celkovyPocetBodu = $_GET["celkovyPocetBodu"];
	$procentniHranice = $_GET["procentniHranice"];
	ulozit($nazevTestu, $trida, $datum, $celkovyPocetBodu, $procentniHranice, $_SESSION["prihlasenyUzivatel"]);
}


function zaokrouhlitDoluNaPul($cislo)
{
	return floor($cislo * 2) / 2;
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
		<div class="prihlaseni">
			<div class="prihlaseniContainer">
				<div class="formularPrihlaseni">
					<?php
					if (array_key_exists("prihlasenyUzivatel", $_SESSION) == false) {
					//if ($_SESSION["prihlasenyUzivatel"]==null){
						//sekce pro nepřihlášené uživatele
						?>
						<form action="" class="prihlasovaciFormular" method="post">
							<div class="jmenoHeslo">
								<label for="jmeno">Jméno:&nbsp; </label>
								<input type="text" class="inputUser" id="jmeno" name="jmeno">
								&nbsp;&nbsp; <label for="heslo"> Heslo:&nbsp; </label>
								<input type="password" class="inputUser" id="heslo" name="heslo">&nbsp;&nbsp;
							</div>
							<div class="prihlasitRegistrace">
								<button name="prihlasit"> Přihlásit</button>
								<a href="registrace.php" id="registrace"> Registrace </a>
							</div>						
						</form>
						<?php
					}
					//sekce pro přihlášené užiovatele
					else {
						echo "<div class='odhlasit'> Uživatel: {$_SESSION["prihlasenyUzivatel"]} &nbsp;&nbsp;</div>";
						echo "<form method='get'>  <button name='odhlasit'> Odhlasit </button>  </form> </div>";
					}
					?>
				</div>
			</div>
			
		</div>
		<div class="container">
			<form method="get">
				<?php
				if (array_key_exists("prihlasenyUzivatel", $_SESSION) == true) {
				//if ($_SESSION["prihlasenyUzivatel"]!=null){
					?>
					<div class="udajeDoDatabaze">
						<label for="nazevTestu">Název testu:</label>
						<input type="text" name="nazevTestu" id="nazevTestu"  value="<?php echo $nazevTestu; ?>" >
						<label for="trida">Třída:</label>
						<input type="text" name="trida" id="trida" value="<?php echo $trida; ?>">
						<label for="datum">Datum:</label>
						<input type="date" name="datum" id="datum" value="<?php echo $datum; ?>">
					</div>

					<?php
				}
				?>
				<div class="loginError">
				
					<?php
						if (array_key_exists("prihlasit", $_POST) && ($errorLogin != null))
							{
								echo "<div><img id='error' src='img/error_icon.png'> $errorLogin</div>";
							}
						else if (array_key_exists("zobraz", $_GET) && ($errorInput != null))
							{
								echo "<div><img id='error' src='img/error_icon.png'> $errorInput</div>";
							}
					?>
				</div>	
				<div class="celkovyPocetBodu">
					<label for="celkovyPocetBodu"> Celkový počet bodů testu:</label> 
					<input type="number" name="celkovyPocetBodu" id="celkovyPocetBodu" min="10" value="<?php echo $celkovyPocetBodu; ?>"><br>
				</div>
				<div class="procentniHranice">
					<label for="procentniHranice"> Procentní hranice známky nedostatečná (5):</label> 
					<input type="number" name="procentniHranice" id="procentniHranice" min="0" max="60"
						value="<?php echo $procentniHranice ?>"><br>
				</div>
				<div class="tlacitka">
					<button name="zobraz">Zobraz hodnocení</button>
					<?php
					if (array_key_exists("prihlasenyUzivatel", $_SESSION) == true) {
					//if ($_SESSION["prihlasenyUzivatel"]!=null){
						?>
						<button name="uloz">Ulož do databáze</button>
						<?php
					}
					?>
				</div>
			</form>
			<div class="vyhodnoceni">
				<?php
				if ($reverseBodoveIntervaly != null) {
					echo "<table>";
					echo "<tr><th> známka </th><th> body </th></tr>";

					for ($i = 0; $i < count($znamkyPopis); $i++) {
						$j = $i * 2;
						$k = $j + 1;
						echo "<tr> <td> $znamkyPopis[$i] </td> <td>  $reverseBodoveIntervaly[$j] - $reverseBodoveIntervaly[$k] </td></tr>";
					}
					echo "</table>";
				}
				?>
			</div>

			<?php
			if (array_key_exists("prihlasenyUzivatel", $_SESSION) == true) {
				echo "<div class='databaze'>";
				$vysledek = getObsah($_SESSION["prihlasenyUzivatel"]);
				//var_dump($vysledek);
				if ($vysledek != null) {   //pokud přihlášený uživatel má nějaká data v databázi, tak je zobraz
					echo "<table>";
					echo "<tr><th>název testu</th><th>třída</th><th class=hide-on-mobile>datum</th><th class=hide-on-mobile>počet bodů</th><th class=hide-on-mobile>procentní hranice</th><th>smaž</th><th>zobraz</th></tr>";
					foreach ($vysledek as $test => $udaj) {
						echo "<tr> <td>{$udaj['nazev_testu']}</td> 
									<td>{$udaj['trida']}</td>
									<td class=hide-on-mobile>{$udaj['datum']}</td>
									<td class=hide-on-mobile>{$udaj['celkovy_pocet_bodu']}</td>
									<td class=hide-on-mobile>{$udaj['procentni_hranice']}</td>
									<td class='odkaz'><a href='?id={$udaj['id']}&smazat'><i class='fa-solid fa-trash-can'></i></a></td>
									<td class='odkaz'><a href='?id={$udaj['id']}&zobraz_historie'><i class='fa-regular fa-eye'></a></i></td></tr>";
					}
					echo "</table>";
				}
			}
			?>
		</div>

		</div>
	</section>
	<footer>
		<div class="footer">
			&copy; Ivo Fiala
		</div>
	</footer>
</body>


</html>