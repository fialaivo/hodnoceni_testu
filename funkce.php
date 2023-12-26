<?php
	$db = new PDO(
			// parametry pripojeni
			"mysql:host=localhost;dbname=hodnocenitestu;charset=utf8",
			"root", // prihlasovaci jmeno
			"", // heslo
			array(
				// v pripade sql chyby chceme aby to vyhazovalo vyjimky
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			),
		);
	
	function ulozit($nazevTestu, $trida, $datum, $celkovyPocetBodu, $procentniHranice, $uzivatel)
	{
		global $db;
		$dotaz = $db->prepare("SELECT trida FROM hodnoceni_testu");
		// odeslat dotaz do databaze
		$dotaz->execute();

		// vycist vysledek select dotazu
		// $dotaz->fetch - vrati jednu radku ve forme pole
		$vysledek = $dotaz->fetch();
		//var_dump($vysledek);

		$dotaz = $db -> prepare("INSERT INTO hodnoceni_testu SET nazev_testu = ?, trida = ?, datum = ?, celkovy_pocet_bodu = ?, procentni_hranice = ?, uzivatel = ? ");
		$dotaz ->execute([$nazevTestu, $trida, $datum, $celkovyPocetBodu, $procentniHranice, $uzivatel]);
	} 

	function ulozit_uzivatele($login, $password, $email, $pin)
	{
		global $db;

		$dotaz = $db -> prepare("INSERT INTO seznam_uzivatelu SET login = ?, password = ?, email = ?, pin = ?, confirm = 0");
		$dotaz ->execute([$login, $password, $email, $pin]);
		
	} 

	function send_mail($email, $pin)
	{
		mb_send_mail(
			"$email",
			"Registrace - Hodnocení testů",
			"Děkujeme za registraci na doméně Hodnocení testů, pro dokončení registrace prosím zadejte PIN: $pin",
			["From" => "Tým Hodnocení testů <fiala.ivos@gmail.com>"]
		);
	}

	function checkUser($jmeno, $heslo){
		global $db;

		$dotaz = $db -> prepare("SELECT * FROM seznam_uzivatelu where login = ? and password = ? and confirm = ?");
		$dotaz -> execute([$jmeno, $heslo, 1]);
		$vysledek = $dotaz -> fetch();
		if ($vysledek != null){
			return $jmeno;
		}
	}

	function checkPin($inputPin, $login)
	{
		global $db;
		$dotaz = $db -> prepare("SELECT pin FROM seznam_uzivatelu WHERE login = ?");
		$dotaz -> execute([$login]);
		$vysledek = $dotaz -> fetch();
		if ($vysledek["pin"] == $inputPin)
		{
			$dotaz = $db -> prepare("UPDATE seznam_uzivatelu SET confirm = 1 WHERE login = ?");
			$dotaz ->execute([$login]);
			return true;
		}
		else{
			return false;
		}
	}

	function getObsah($uzivatel)
    {
        
        // nacteni obsahu stranky z databaze
        global $db;

        $dotaz = $db->prepare("SELECT nazev_testu, trida, datum, celkovy_pocet_bodu, procentni_hranice, id FROM hodnoceni_testu WHERE uzivatel = ?");
        $dotaz->execute([$uzivatel]);

        $vysledek = $dotaz->fetchAll();

        // pokud by databaze nic nevratila, tak vratime prazdny obsah
        if ($vysledek == false)
        {
            return "";
        }
        else
        {
            return $vysledek;
        }
    }
	function smazat($id)
    {
        // nacteni obsahu stranky z databaze
        global $db;

		//var_dump($id);
		$dotaz = $db->prepare("DELETE FROM hodnoceni_testu WHERE id = ?");
        $dotaz->execute([$id]);

	}
	function zobraz_historie($id)
    {
        // nacteni obsahu stranky z databaze
        global $db;

		$dotaz = $db->prepare("SELECT nazev_testu, trida, datum, celkovy_pocet_bodu, procentni_hranice FROM hodnoceni_testu WHERE id = ?");
        $dotaz->execute([$id]);

		$historie = $dotaz->fetch();
        return $historie;
        
	}

?>

