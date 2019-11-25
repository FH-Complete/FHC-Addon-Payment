<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/konto.class.php');

require_once("mpay24-php/bootstrap.php");
//include_once("lib/Mpay24.php");
//include_once("./lib/Mpay24Config.php");
//include_once("./lib/Mpay24Sdk.php");
//include_once("lib/Mpay24Order.php");
//include_once("lib/Mpay24Config.php");
//include_once("lib/Mpay24Confirmation.php");
//include_once("lib/Mpay24FlexLink.php");

//use \Mpay24\Mpay24;
use Mpay24\Mpay24;
use Mpay24\Mpay24Order;
use Mpay24\Mpay24Config;

$person_id = $_SESSION['bewerbung/personId'];

$person = new person();

if(!$person->load($person_id))
{
	die($p->t('global/fehlerBeimLadenDesDatensatzes'));
}
$konto = new konto();
$konto->getBuchungen($person_id, $filter='offene');
foreach ($konto->result as $buchung){
	if ($buchung['parent']->buchungstyp_kurzbz == 'Aufnahmegebuehr')	
	{
		$tid = $buchung['parent']->zahlungsreferenz;
		$buchungsnummer = $buchung['parent']->buchungsnr;
	}
}


$config = new Mpay24Config();

$config->setMerchantID(MPAY24_MERCHANT_ID);
$config->setSoapPassword(MPAY24_SOAP_PASS);


$mpay24 = new Mpay24($config); // or with soap username, password if not provided in config

/* pull test, ob wirklich alles gut gegangen ist */
$paymentStatus = $mpay24->paymentStatusByTID($tid);

$tid_request = $_GET["TID"];
if ($tid_request == $tid)
{
		$checkkonto = new konto();
		if ($checkkonto->getDifferenz($buchungsnummer) != 0)
		{
				$gebucht = new konto($buchungsnummer);
				$gebucht->buchungsnr_verweis = $buchungsnummer;
				$gebucht->betrag = 100;
				$gebucht->buchungsdatum = date('Y-m-d');
				$gebucht->save(true);
		}
}
header('Location: https://');



?>
