<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/konto.class.php');
require_once('../../../../include/adresse.class.php');
require_once('../../../../include/kontakt.class.php');

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
	}
}


$adresse = new adresse();
$adresse->load_pers($person_id);
$strasse = isset($adresse->result[0]->strasse)?$adresse->result[0]->strasse:'';
$plz = isset($adresse->result[0]->plz)?$adresse->result[0]->plz:'';
$ort = isset($adresse->result[0]->ort)?$adresse->result[0]->ort:'';
$gemeinde = isset($adresse->result[0]->gemeinde)?$adresse->result[0]->gemeinde:'';
$adr_nation = isset($adresse->result[0]->nation)?$adresse->result[0]->nation:'';
//$adr_nation = 'DE';
//echo $adr_nation;
//die();

$config = new Mpay24Config();

$config->setMerchantID(MPAY24_MERCHANT_ID);
$config->setSoapPassword(MPAY24_SOAP_PASS);



$config->useTestSystem(MPAY24_TEST_SYSTEM);   // true => Use the Test System [DEFAULT], false => use the Live System
$config->setDebug(MPAY24_DEBUG);        // true => Debug Mode On [DEFAULT], false => Debug Mode Off
$config->setVerifyPeer(true);           // true => Verify the Peer  [DEFAULT], false => stop cURL from verifying the peer's certificate
$config->setEnableCurlLog(true);       // false => we do not log Curl comunicatio [DEFAULT], true => we log it to a seperat Log file
$config->setLogFile('mpay24.log');   // default is mpay24.log
$config->setCurlLogFile('curl.log');    // default is curllog.log:w


$mpay24 = new Mpay24($config); // or with soap username, password if not provided in config
$mdxi = new Mpay24Order();
$mdxi->Order->Tid = $tid;
$mdxi->Order->Price = MPAY24_PRICE;
$mdxi->Order->Customer = $person->vorname.' '.$person->nachname; // hier primary key zusaetzlich?
$mdxi->Order->BillingAddr->setMode("ReadWrite");
$mdxi->Order->BillingAddr->Name = $person->vorname.' '.$person->nachname;

$mdxi->Order->BillingAddr->Street = $strasse." ";
//$mdxi->Order->BillingAddr->Street2 = "ii";
$mdxi->Order->BillingAddr->Zip = $plz;
$mdxi->Order->BillingAddr->City = $ort;
$mdxi->Order->BillingAddr->Country = $adr_nation;
//$mdxi->Order->BillingAddr->Country->setCode($adr_nation);
//$mdxi->Order->BillingAddr->Email = "a.b@c.de";


$mdxi->Order->URL->Success      = 'https://abc.de/addons/payment/cis/mpay24/success.php';
$mdxi->Order->URL->Error        = 'https://abc.de/addons/payment/cis/mpay24/error.php';
$mdxi->Order->URL->Confirmation = 'https://abc.de/addons/payment/cis/mpay24/confirmation.php';

$paymentPageURL = $mpay24->paymentPage($mdxi)->getLocation();
header('Location: '.$paymentPageURL);
//header('Location: '.$mpay24->mpay24Sdk->selectPayment($mdxi)->getLocation());
?>
