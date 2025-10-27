<?php

    include "koneksi.php";
    extract($_REQUEST);
    
    // Memuat kelas PHPMailer
    require 'PHPMailer/PHPMailerAutoload.php';

    $mail = new PHPMailer();

    // Konfigurasi SMTP
    $mail->IsSMTP();
    // Baris ini akan memuat konfigurasi SMTP Anda dari file smtp.php
    // Pastikan file smtp.php berisi detail seperti $mail->Host, $mail->Port, dll.
    include "smtp.php"; 
    
    // Informasi Pengirim
    $mail->From      = "dc_admin@ssi.sharp-world.com";
    $mail->FromName  = "Admin Document Online System";

    // Query untuk mendapatkan daftar penerima email
    $sql2 = "SELECT DISTINCT users.name, users.email FROM rev_doc, users WHERE (rev_doc.id_doc ='$drf' AND users.username = rev_doc.nrp) and rev_doc.status='Review'";
    $res2 = mysqli_query($link, $sql2) or die(mysqli_error($link));
    
    // =================================================================
    // INI BAGIAN YANG DIPERBARUI
    // =================================================================
    // 1. Hitung jumlah baris (penerima) yang ditemukan dari query
    $jumlah_penerima = mysqli_num_rows($res2);

    // 2. Cek apakah ada penerima yang ditemukan (jumlah baris > 0)
    if ($jumlah_penerima > 0) {
        
        // Loop untuk menambahkan semua alamat email penerima
        while ($data2 = mysqli_fetch_row($res2)) {
            // $data2[1] berisi email, $data2[0] berisi nama
            $mail->AddAddress($data2[1], $data2[0]);
        }
        
        // Konfigurasi konten email
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        
        $mail->Subject  = "Reminder";
        $mail->Body     = "Attention Mr./Mrs. : Reviewer <br /> This following <span style='color:green'>".$type."</span> 
                         Document need to be <span style='color:blue'>reviewed</span> <br /> No. Document : ".$nodoc."<br /> Document Title : 
                         ".$title."<br />Please Login into <a href='192.168.132.34/newdocument'>Document Online System</a>, Thank You";
        
        // Kirim email
        if (!$mail->Send()) {
            echo "Message was not sent <p>";
            echo "Mailer Error: " . $mail->ErrorInfo;
            echo $sql2;
            exit;
        }

        // Jika email berhasil dikirim, update database
        $sql_upd = "update docu set reminder=reminder+1 where no_drf=$drf";
        $res_upd = mysqli_query($link, $sql_upd);
        
        // Tampilkan pesan sukses dan redirect
        echo "<script language='javascript'>
                  alert('Reminder sent');
                  document.location='my_doc.php';
              </script>";

    } else {
        // Blok ini akan dieksekusi jika tidak ada reviewer yang ditemukan
        echo "<script language='javascript'>
                  alert('Reminder failed. No reviewers found for this document.');
                  document.location='my_doc.php';
              </script>";
    }

?>