<?php
/* push notification - sicherheit, falls der Kunde nicht auf weiter klickt */
/*http:www.hotelmuster.com/confirmation.php?OPERATION=CONFIRMATION&TID=9275&*/
  //STATUS=RESERVED&PRICE=1000&CURRENCY=EUR&P_TYPE=CC&BRAND=VISA&MPAYTID=1682984&
  //USER_FIELD=&ORDERDESC=Example+order&CUSTOMER=9275&CUSTOMER_EMAIL=&LANGUAGE=DE&
  /*CUSTOMER_ID=&PROFILE_STATUS=IGNORED&FILTER_STATUS=OK&APPR_CODE=%2Dtest%2D,cu*/

/*rauslesen: 
 * status muesste wohl "BILLED" sein
 * TID und CUSTOMER_EMAIL muessen uebereinstimmen
 * MPAYTID in die DB
 */

/* hier die IP ueberpruefen - kommt sie wirklich von MPAY24 */
/* Kontrolle, ob nicht schon durch success.php reingeschrieben */



/*The merchant confirms the receipt of the transaction notification with either OK or ERROR (status depends if the confirmation could successfully update the merchant's system).*/
/* also echo 'OK' oder 'ERROR'*/


//session_start();

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/konto.class.php');

$tid_request = $_GET["TID"];
$status = $_GET["STATUS"];
$konto = new konto();
if ($konto->loadFromZahlungsreferenz($tid_request) && ($_SERVER['REMOTE_ADDR'] == '111.222.333.44' || $_SERVER['REMOTE_ADDR'] == '111.222.33.444') && $status == 'BILLED')
{
	$buchungsnummer = $konto->buchungsnr;
	$checkkonto = new konto();
	if ($checkkonto->getDifferenz($buchungsnummer) != 0)
	{
		$konto->buchungsnr_verweis = $buchungsnummer;
		$konto->betrag = 100;
		$konto->buchungsdatum= date('Y-m-d');
		$konto->save(true);
	}
	echo 'OK';
}
else
	echo 'ERROR';


?>
