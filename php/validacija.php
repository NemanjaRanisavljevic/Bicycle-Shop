<?php
//header('Content-type: application/json');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'php_mailer/src/Exception.php';
require 'php_mailer/src/PHPMailer.php';
require 'php_mailer/src/SMTP.php';
$status =404;
$message=null;

if(isset($_POST['provera']))
{   
    
    $greske = [];
    


    $imePrezime = $_POST['imePrezime'];
    $adresa = $_POST['adresa'];
    $grad = $_POST['grad'];
    $postanskiBroj=$_POST['postanskiBroj'];
    $emailAdresa=$_POST['emailAdresa'];
  
    $sifra =$_POST['sifra'];
    
    $regImePrezime ="/^[A-ZČĆŽŠĐ][a-zčćžšđ]{2,10}(\s[A-ZČĆŽŠĐ][a-zčćžšđ]{2,15})+$/";
    $regAdresa="/^([A-ZČĆŽŠĐ][a-zčćžšđ]{2,15})(\s[A-ZČĆŽŠĐ][a-zčćžšđ]{2,15})*(\s[\d]{1,3})$/";
    $regGrad="/^[A-ZČĆŽŠĐ][a-zčćžšđ]{2,10}(\s[A-ZČĆŽŠĐ][a-zčćžšđ]{2,10})?(\s[A-ZČĆŽŠĐ][a-zčćžšđ]{2,10})?$/";
    $regPostanskiBroj="/^[1 2 3][0-9]{4}$/";
    $regSifra="/^[A-Z][\w\d]{5,}$/";

    $pol = isset($_POST['anketaIzbor'])? $_POST['anketaIzbor'] : "";
    if(empty($pol))
    {
        array_push($greske,"Mora izabrati pol!");
    }

    if(!preg_match($regImePrezime,$imePrezime))
    {
        array_push($greske,"Niste dobro uneli ime i prezime!");
    }
    if(!preg_match($regAdresa,$adresa))
    {
        array_push($greske,"Miste dobro uneli vasu adresu!");
    }
    if(!preg_match($regGrad,$grad))
    {
        array_push($greske,"Niste dobro uneli vas grad!");
    }
    if(!preg_match($regPostanskiBroj,$postanskiBroj))
    {
        array_push($greske,"Niste dobro uneli postanski broj!");
    }
    if(!filter_var($emailAdresa,FILTER_VALIDATE_EMAIL))
    {
        array_push($greske,"Niste u dobrom formatu uneli email!");
    }

    if(!preg_match($regSifra,$sifra))
    {
        array_push($greske,"Niste dobro uneli sifru!");
    }


    
    if(count($greske) >0)
    {   $status=422;
        echo "<ul>";
        foreach($greske as $greska)
        {
            echo "<li>$greska</li>";
        }
        echo "</ul>";
    }else
    {   include "konekcija.php";
        $upit="INSERT INTO korisnik(imePrezime,adresa,grad,postanskiBroj,email,sifra,ulogaID,polID,token)
        values(:imePrezime,:adresa,:grad,:postanskiBroj,:email,:sifra,1,:polID,:token)";
        
        $sifra=md5($_POST['sifra']);

        $rezultat= $kon->prepare($upit);
        $rezultat->bindParam(":imePrezime",$imePrezime);
        $rezultat->bindParam(":adresa",$adresa);
        $rezultat->bindParam(":grad",$grad);
        $rezultat->bindParam(":postanskiBroj",$postanskiBroj);
        $rezultat->bindParam(":email",$emailAdresa);
        $rezultat->bindParam(":sifra",$sifra);
        $rezultat->bindParam(":polID",$pol);
        $token=md5(time().$emailAdresa);
        $rezultat->bindParam(":token",$token);
        
        

        try{
            
            $status = $rezultat->execute() ? 201 : 500;
                       

                       
                       $mail = new PHPMailer(true);
           
                       try {
               //Server settings
               $mail->SMTPDebug = 0;
                $mail->SMTPOptions = array(
               'ssl' => array(
                   'verify_peer' => false,
                   'verify_peer_name' => false,
                   'allow_self_signed' => true
               )
           );                                          // Enable verbose debug output
               $mail->isSMTP();                                      // Set mailer to use SMTP
               $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
               $mail->SMTPAuth = true;                               // Enable SMTP authentication
               $mail->Username = 'nemanjaranisavljevic97@gmail.com';                 // SMTP username
               $mail->Password = 'beka123456';                           // SMTP password
               $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
               $mail->Port = 587;                                    // TCP port to connect to
           
               //Recipients
               $mail->setFrom('crazylordftw@gmail.com', 'Nemanja Ranisavljevic(Bicycles)');
                $mail->addAddress($emailAdresa, 'Nemanja Ranisavljevic');     // Add a recipient
               // $mail->addAddress('ellen@example.com');               // Name is optional
               // $mail->addReplyTo('info@example.com', 'Information');
               // $mail->addCC('cc@example.com');
               // $mail->addBCC('bcc@example.com');
           
               //Attachments
               // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
               // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
           
               //Content
               $mail->isHTML(true);                                  // Set email format to HTML
               $mail->Subject = 'Registracija za sajt Bicycles';
               $mail->Body    = 'Aktivacija vaseg naloga:<a href="https://bicycles-shop.000webhostapp.com/views/aktivacija.php?token='.$token.'">Klikom na link aktiviracete vas nalog</a>';
               // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
               
               $mail->send();
           
              // echo 'Message has been sent';
               $status = 200;
               $message="Uspesno ste se registrovali";
           } catch (Exception $e) {
            $status = 500;
           }
           
         } catch(PDOException $e) {
             $status = 409;
             $message = $e->getMessage();
         }
    }
    
    
}


http_response_code($status);
if($message){
    echo json_encode($message);
}

