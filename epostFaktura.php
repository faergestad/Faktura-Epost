<?php
include "db.php";
require('fpdf.php');

 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'phpmailer/vendor/autoload.php';


$sql = "SELECT * FROM `project` INNER JOIN services on services.sID = project.serviceId INNER JOIN timesheet on timesheet.pID = project.pID INNER JOIN users on users.uID = timesheet.userID WHERE CURDATE() > expDate GROUP BY project.pID";
$result = $db->query($sql);

$innhold = "SELECT * FROM `project` INNER JOIN services on services.sID = project.serviceId INNER JOIN timesheet on timesheet.pID = project.pID INNER JOIN users on users.uID = timesheet.userID WHERE CURDATE() > expDate";
$innholdResultat = $db->query($innhold);
$innholdArr = array();

while($row2 = $innholdResultat->fetch_assoc()) {
    $innholdArr[] = $row2;
}

if (mysqli_num_rows($result) > 0) {
    while($row = $result->fetch_assoc()) {
        fyllFakturaProsjekt($row['pID'],$row['name'],$row['address'],$row['mail'],$row['description'],$row['startDate'], $innholdArr);
    }
} else {
    echo "Ingen oppdrag skal faktureres";
}

// funksjon som skal lage faktura i pdf-format, og sende ut faktura på mail
function fyllFakturaProsjekt($fPid,$fName,$fAddress,$fMail,$fDesc,$fStart, $fArr) {
    
    // Lag faktura i pdf-format
    $pdf = new FPDF('p', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    // Cell(bredde, høyde, tekst, border, linjeslutt, [align])
    $pdf->Cell(130, 5, 'Bachelorprosjekt AS', 0, 0);
    $pdf->Cell(59 , 5, 'Faktura', 0, 1); // Linjeslutt
    
    // tom celle for mellomrom
    $pdf->Cell(189, 5, '', 0, 1);
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(130, 5, '', 0, 0);
    $pdf->Cell(59, 5, '', 0, 1); // Linjeslutt
    
    $pdf->Cell(130, 5, 'Gullbringvegen 36,', 0, 0);
    $pdf->Cell(25, 5, 'Dato', 0, 0);
    $pdf->Cell(34, 5, date("d/m/Y"), 0, 1); // Linjelutt
    
    $strFra = iconv('UTF-8', 'windows-1252', 'Bø i Telemark');
    $pdf->Cell(130, 5, $strFra, 0, 0);
    $pdf->Cell(25, 5, 'Faktura #', 0, 0);
    $pdf->Cell(34, 5, $fPid, 0, 1); // Linjelutt
    
    $pdf->Cell(130, 5, 'post@gakk.one', 0, 0);
    $pdf->Cell(25, 5, 'KundeID', 0, 0);
    $pdf->Cell(34, 5, '1', 0, 1); // Linjelutt
    
    // Tom celle for å lage mellomrom
    $pdf->Cell(189, 10, '', 0, 1); // Linjelutt
    
    // Fakturaadresse
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(100, 5, 'Faktureringsadresse', 0, 1); // Linjeslutt
    $pdf->SetFont('Arial', '', 12);
    // Celler på begynnelsen av hver linje som padding
    $strTil = iconv('UTF-8', 'windows-1252', $fName);
    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, $strTil, 0, 1);
    
    $strAddr = iconv('UTF-8', 'windows-1252', $fAddress);
    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, $strAddr, 0, 1);
    
    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'Tlf: +4712345678', 0, 1);
    
    // Tom celle for å lage mellomrom
    $pdf->Cell(189, 10, '', 0, 1); // Linjelutt
    
    // Fakturainnhold som beskriver hvilke tjenester som er utført
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 5, 'Beskrivelse', 1, 0);
    $pdf->Cell(30, 5, 'Antall timer', 1, 0);
    $pdf->Cell(29, 5, 'Pris', 1, 1);

    $pdf->SetFont('Arial', '', 12);
    
    foreach($fArr as $item) {
        $strFname = iconv('UTF-8', 'windows-1252', $item['fName']);
        $strLname = iconv('UTF-8', 'windows-1252', $item['lName']);
            if($item['pID'] == $fPid) {
                $pdf->Cell(130, 5, $item['date']." - ".$item['position']." - ".$strFname." ".$strLname, 1, 0);
                $pdf->Cell(30, 5, $item['hours'], 1, 0);
                $pdf->Cell(29, 5, $item['pricePrHour']*$item['hours'], 1, 1, 'R');
            }
        } 
       $sum=0;
    foreach($fArr as $item) {
            if($item['pID'] == $fPid) {
               $sum+=$item['pricePrHour']*$item['hours'];
            }
    }
    // Sum
    $pdf->Cell(130, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Sum', 1, 0);
    $pdf->Cell(29, 5,$sum, 1, 1, 'R');

    $total=$sum*1.25;
    $pdf->Cell(130, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Sum inkl. mva', 1, 0);
    $pdf->Cell(29, 5, $total, 1, 1, 'R');
    
    //Tomme celler for å lage mellomrom
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1); 
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1); 
    $pdf->Cell(189, 10, '', 0, 1); 
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1); 
    $pdf->Cell(189, 10, '', 0, 1); 
    $pdf->Cell(189, 10, '', 0, 1);
    $pdf->Cell(189, 10, '', 0, 1); 
    // Tomme celler for å lage mellomrom 
    
    // De nederste linjene på fakturaen
    $beløpTekst = iconv('UTF-8', 'windows-1252', 'Beløp:  ');
    $pdf->Cell(130, 5, 'Kontonr: 0000.00.00000', 0, 0);
    $pdf->Cell(25, 5, ''.$beløpTekst.$total, 0, 0);
    $pdf->Cell(34, 5, '', 0, 1); // Linjelutt
    
    // Forfallsdato + 1 mnd
    $d=strtotime("+1 month");
    
    $pdf->Cell(130, 5, 'KID: 1400552323478112', 0, 0);
    $pdf->Cell(25, 5, 'Forfallsdato: '.date("d/m/Y", $d), 0, 0);
    $pdf->Cell(34, 5, '', 0, 1); // Linjelutt
    
    // Lager faktura i nettleseren, for testing
    $pdf->Output();
    
    
    // encoder data (puts attachment in proper format)
    $pdfdoc = $pdf->Output("", "S");
    // email stuff
    $mail = new PHPMailer(true);                            // Passing `true` enables exceptions
    try {
        //Server innstillinger
        $mail->SMTPDebug = 0;                               // Enable verbose debug output
        $mail->isSMTP();                                    // Set mailer to use SMTP
        $mail->Host = '';                                   // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                             // Enable SMTP authentication
        $mail->Username = '';                               // SMTP username
        $mail->Password = '';                               // SMTP password
        $mail->SMTPSecure = 'ssl';                          // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                  // TCP port to connect to
    
        //Mottakere
        $mail->setFrom(''); // Bytt ut epost
        // Send til
        //$mail->addAddress('');
        //$mail->addReplyTo('');
        //$mail->addCC('');
        //$mail->addBCC('');
    
        //Vedlegg
        $mail->addStringAttachment($pdfdoc, 'faktura.pdf');
    
        //Epostinnhold
        $mail->isHTML(true);
        $mail->Subject = 'Timeregistrering og fakturahåndtering';
        $mail->Body    = 'Vedlagt finner du din faktura';
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    
        $mail->send();
        echo 'Mailen er sendt <br>';
    } catch (Exception $e) {
        echo $e;
    }
}

?>
