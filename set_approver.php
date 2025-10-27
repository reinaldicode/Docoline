<?php
include('header.php');
include('koneksi.php');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * ============================================================================
 * SECTION NORMALIZATION FUNCTION
 * ============================================================================
 * Menghilangkan suffix " Section" dari input section
 */
function normalizeSection($section) {
    if (empty($section)) {
        return $section;
    }
    
    // Trim whitespace
    $section = trim($section);
    
    // Remove " Section" suffix (case-insensitive)
    $section = preg_replace('/\s+Section$/i', '', $section);
    
    return $section;
}

// Get parameters from URL with proper sanitization
$type    = isset($_GET['type']) ? mysqli_real_escape_string($link, $_GET['type']) : '';
$section = isset($_GET['section']) ? mysqli_real_escape_string($link, $_GET['section']) : '';
$id_doc  = isset($_GET['id_doc']) ? mysqli_real_escape_string($link, $_GET['id_doc']) : '';
$iso     = isset($_GET['iso']) ? mysqli_real_escape_string($link, $_GET['iso']) : '';
$nodoc   = isset($_GET['nodoc']) ? mysqli_real_escape_string($link, $_GET['nodoc']) : '';
$title   = isset($_GET['title']) ? mysqli_real_escape_string($link, $_GET['title']) : '';

// NORMALIZE SECTION - Hilangkan " Section" suffix
$original_section = $section;
$section = normalizeSection($section);

// Debug: Log received parameters
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div class='alert alert-info'><strong>DEBUG - Received Parameters:</strong><br>";
    echo "Type: " . htmlspecialchars($type) . "<br>";
    echo "Original Section: " . htmlspecialchars($original_section) . "<br>";
    echo "Normalized Section: " . htmlspecialchars($section) . "<br>";
    echo "ID Doc: " . htmlspecialchars($id_doc) . "<br>";
    echo "ISO: " . htmlspecialchars($iso) . "<br>";
    echo "No Doc: " . htmlspecialchars($nodoc) . "<br>";
    echo "Title: " . htmlspecialchars($title) . "</div>";
}
?>

<div class="row">
    <div class="col-xs-1"></div>
    <div class="col-xs-6 well well-lg">
        <h2>Select Reviewer</h2>

        <form action="" method="post" enctype="multipart/form-data">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">Select</th>
                        <th width="35%">Name</th>
                        <th width="30%">Section</th>
                        <th width="30%">Email</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if (!empty($section)) {
                    // ✅ QUERY BERDASARKAN id_section (yang sudah normalized)
                    // Karena struktur tabel section:
                    // - id_section = "Accounting" (normalized)
                    // - sect_name = "Accounting Section" (dengan suffix)
                    $sql = "SELECT * FROM section WHERE id_section='" . mysqli_real_escape_string($link, $section) . "'";
                    $q   = mysqli_query($link, $sql);

                    if (!$q) {
                        die("Query error: " . mysqli_error($link));
                    }

                    if (mysqli_num_rows($q) > 0) {
                        $row = mysqli_fetch_array($q);
                        $se  = isset($row['id_section']) ? $row['id_section'] : null;
                        
                        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                            echo "<tr><td colspan='4'><div class='alert alert-success'>✓ Section found: id_section='" . htmlspecialchars($section) . "', sect_name='" . htmlspecialchars($row['sect_name']) . "'</div></td></tr>";
                        }
                    } else {
                        $se = null;
                        echo "<tr><td colspan='4'><div class='alert alert-danger'>Section '" . htmlspecialchars($section) . "' tidak ditemukan di database (checked id_section column).</div></td></tr>";
                        
                        // Debug: show available sections
                        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                            $debug_sql = "SELECT id_section, sect_name FROM section ORDER BY id_section";
                            $debug_result = mysqli_query($link, $debug_sql);
                            echo "<tr><td colspan='4'><div class='alert alert-info'><strong>Available sections in database:</strong><br>";
                            while ($debug_row = mysqli_fetch_array($debug_result)) {
                                echo "- id_section: '" . htmlspecialchars($debug_row['id_section']) . "' → sect_name: '" . htmlspecialchars($debug_row['sect_name']) . "'<br>";
                            }
                            echo "</div></td></tr>";
                        }
                    }
                }

                // Query to get all approvers
                $sql2 = "SELECT * FROM users WHERE state='approver' ORDER BY section, name";
                $q2   = mysqli_query($link, $sql2);

                if ($q2) {
                    $total_approvers = mysqli_num_rows($q2);
                    
                    // Debug: Show total approvers found
                    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                        echo "<tr><td colspan='4'><div class='alert alert-info'>Total Approvers Found: " . $total_approvers . "</div></td></tr>";
                    }
                    
                    if ($total_approvers == 0) {
                        echo "<tr><td colspan='4'><div class='alert alert-warning'>No approvers found in the system.</div></td></tr>";
                    }
                    
                    while ($row2 = mysqli_fetch_array($q2)) { 
                        // NORMALIZE section dari database users juga
                        $user_section_normalized = normalizeSection($row2['section']);
                        
                        $value = htmlspecialchars($row2['username']) . "|" . 
                                htmlspecialchars($row2['name']) . "|" . 
                                htmlspecialchars($row2['email']) . "|" . 
                                htmlspecialchars($user_section_normalized); // Gunakan normalized section
                        ?>
                        <tr>
                            <td>
                                <input type='checkbox' name='item[]' value='<?php echo $value; ?>'>
                            </td>
                            <td><?php echo htmlspecialchars($row2['name']); ?></td>
                            <td><?php echo htmlspecialchars($user_section_normalized); ?></td>
                            <td><?php echo htmlspecialchars($row2['email']); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            
            <input type='hidden' name='id_doc' value='<?php echo htmlspecialchars($id_doc); ?>'>
            <input type='hidden' name='type' value='<?php echo htmlspecialchars($type); ?>'>
            <input type='hidden' name='iso' value='<?php echo htmlspecialchars($iso); ?>'>
            <input type='hidden' name='nodoc' value='<?php echo htmlspecialchars($nodoc); ?>'>
            <input type='hidden' name='title' value='<?php echo htmlspecialchars($title); ?>'>
            <input type='hidden' name='section' value='<?php echo htmlspecialchars($section); ?>'>
            
            <input type="submit" value="Save" class="btn btn-success" name="save">
            <input type="submit" value="Skip" class="btn btn-info" name="skip">
        </form>
    </div>
</div>

<?php 
// ========================================================================
// PROSES SAVE - DENGAN SECTION NORMALIZATION
// ========================================================================
if (isset($_POST['save'])){
    
    // Debug log
    $debug_log = array();
    $debug_log[] = "=== START SAVE PROCESS (SET APPROVER) ===";
    $debug_log[] = "Time: " . date('Y-m-d H:i:s');
    
    // Cek apakah ada item yang dipilih
    if (isset($_POST["item"]) && is_array($_POST["item"]) && count($_POST["item"]) > 0) {
        $jumlah = count($_POST["item"]);
        $debug_log[] = "Total selected reviewers: $jumlah";

        // Get form data
        $id_doc = isset($_POST['id_doc']) ? mysqli_real_escape_string($link, $_POST['id_doc']) : '';
        $type   = isset($_POST['type']) ? mysqli_real_escape_string($link, $_POST['type']) : '';
        $iso    = isset($_POST['iso']) ? mysqli_real_escape_string($link, $_POST['iso']) : '';
        $nodoc  = isset($_POST['nodoc']) ? mysqli_real_escape_string($link, $_POST['nodoc']) : '';
        $title  = isset($_POST['title']) ? mysqli_real_escape_string($link, $_POST['title']) : '';
        $section = isset($_POST['section']) ? mysqli_real_escape_string($link, $_POST['section']) : '';

        // NORMALIZE section jika ada
        $section = normalizeSection($section);

        $debug_log[] = "Document Info - ID: $id_doc, Type: $type, ISO: $iso, No: $nodoc";
        $debug_log[] = "Section (normalized): $section";
        
        $debug_log[] = "=== Processing Selected Reviewers ===";
        
        // Counter untuk tracking
        $insert_success_count = 0;
        $insert_failed_count = 0;
        $duplicate_count = 0;
        $all_recipients = array(); // Array untuk menyimpan semua email dan nama
        
        // Loop untuk setiap reviewer yang dipilih
        for ($i=0; $i < $jumlah; $i++) {
            if (isset($_POST["item"][$i])) {
                $id    = $_POST["item"][$i];
                $pecah = explode('|', $id);

                $debug_log[] = "Processing item $i: " . print_r($pecah, true);

                if (count($pecah) >= 4) {
                    $id_user = mysqli_real_escape_string($link, $pecah[0]);
                    $user_name = mysqli_real_escape_string($link, $pecah[1]);
                    $user_email = mysqli_real_escape_string($link, $pecah[2]);
                    $user_section = mysqli_real_escape_string($link, $pecah[3]);
                    
                    // NORMALIZE user section (sudah normalized dari form, tapi double-check)
                    $user_section = normalizeSection($user_section);
                    
                    $debug_log[] = "Reviewer: $user_name ($user_email) - Section: $user_section";
                    
                    // CEK DUPLIKASI - Cek apakah sudah ada di database
                    $check_sql = "SELECT COUNT(*) as total FROM rev_doc WHERE id_doc='$id_doc' AND nrp='$id_user'";
                    $check_result = mysqli_query($link, $check_sql);
                    
                    if ($check_result) {
                        $check_data = mysqli_fetch_array($check_result);
                        
                        if ($check_data['total'] > 0) {
                            $duplicate_count++;
                            $debug_log[] = "⚠ Duplicate: $user_name ($id_user) already exists in database, skipping...";
                            continue; // Skip ke iterasi berikutnya
                        }
                    }
                    
                    // Insert ke database dengan section yang sudah dinormalisasi
                    $sql_in  = "INSERT INTO rev_doc(id_doc, nrp, reviewer_name, reviewer_section, status, tgl_approve, reason) 
                                VALUES ('$id_doc', '$id_user', '$user_name', '$user_section', 'Review', '-', '')";
                    
                    if (mysqli_query($link, $sql_in)) {
                        $insert_success_count++;
                        $debug_log[] = "✓ Database insert successful for $user_name (section: $user_section)";
                        
                        // Simpan email dan nama ke array untuk dikirim nanti
                        $all_recipients[] = array(
                            'email' => $user_email,
                            'name' => $user_name
                        );
                        $debug_log[] = "✓ Added to recipients list: $user_email";
                    } else {
                        $insert_failed_count++;
                        $debug_log[] = "✗ Database insert FAILED for $user_name: " . mysqli_error($link);
                    }
                } else {
                    $debug_log[] = "✗ Invalid data format for item $i";
                }
            }
        }

        $debug_log[] = "=== Database Insert Summary ===";
        $debug_log[] = "Successfully inserted: $insert_success_count";
        $debug_log[] = "Failed to insert: $insert_failed_count";
        $debug_log[] = "Duplicates skipped: $duplicate_count";
        $debug_log[] = "Total recipients for email: " . count($all_recipients);
        
        // KIRIM EMAIL KE SEMUA RECIPIENTS SEKALIGUS
        if (count($all_recipients) > 0) {
            $debug_log[] = "=== Email Preparation ===";
            
            // Initialize PHPMailer
            require 'PHPMailer/PHPMailerAutoload.php';
            $mail = new PHPMailer();
            $mail->IsSMTP();
            
            // Include SMTP configuration
            if (file_exists('smtp.php')) {
                include "smtp.php";
                $debug_log[] = "SMTP config loaded from smtp.php";
            } else {
                // Fallback manual configuration
                $mail->Host     = "relay.sharp.co.jp";
                $mail->Port     = 25;
                $mail->SMTPAuth = false;
                $debug_log[] = "Using manual SMTP config (smtp.php not found)";
            }
            
            $mail->setFrom('dc_admin@ssi.sharp-world.com', 'Admin Document Online System');
            $mail->WordWrap = 50;
            $mail->IsHTML(true);
            
            // Enable SMTP debugging untuk troubleshooting
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) use (&$debug_log) {
                $debug_log[] = "SMTP Debug: $str";
            };

            // Set email subject dan body
            $mail->Subject  = "Document to Review";
            $mail->Body     = "Attention Mr./Mrs. : Reviewer <br /> 
                              This following <span style='color:green'>".htmlspecialchars($type)."</span> Document need to be 
                              <span style='color:blue'>reviewed</span> <br /> 
                              No. Document : ".htmlspecialchars($nodoc)."<br /> 
                              Document Title : ".htmlspecialchars($title)."<br />
                              Please Login into <a href='http://192.168.132.34/newdocument'>Document Online System</a>, Thank You";
            $mail->AltBody  = "Document to Review - No: ".htmlspecialchars($nodoc)." - Title: ".htmlspecialchars($title);
            
            // TAMBAHKAN SEMUA RECIPIENTS KE EMAIL
            $email_added_count = 0;
            foreach ($all_recipients as $recipient) {
                try {
                    $mail->AddAddress($recipient['email'], $recipient['name']);
                    $email_added_count++;
                    $debug_log[] = "✓ Email address added: " . $recipient['email'] . " (" . $recipient['name'] . ")";
                } catch (Exception $e) {
                    $debug_log[] = "✗ Failed to add email: " . $recipient['email'] . " - Error: " . $e->getMessage();
                }
            }
            
            $debug_log[] = "=== Email Recipients Summary ===";
            $debug_log[] = "Total email addresses added to mail: $email_added_count";
            
            // Get all recipient addresses for debugging
            $mail_recipients = $mail->getAllRecipientAddresses();
            $debug_log[] = "PHPMailer recipients list: " . print_r($mail_recipients, true);

            $debug_log[] = "=== Attempting to Send Email ===";
            
            // Kirim email SATU KALI untuk SEMUA RECIPIENTS
            if(!$mail->Send()){
                $debug_log[] = "✗✗✗ EMAIL SEND FAILED ✗✗✗";
                $debug_log[] = "Mailer Error: " . $mail->ErrorInfo;
                
                // Simpan log ke file
                file_put_contents('email_debug_log.txt', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
                
                echo "<script>
                          alert('Reviewers saved to database!\\n\\nInserted: $insert_success_count\\nDuplicates skipped: $duplicate_count\\n\\nHowever, Email FAILED to send!\\n\\nError: " . addslashes($mail->ErrorInfo) . "\\n\\nCheck email_debug_log.txt for details');
                          document.location='index_login.php';
                      </script>";
            } else {
                $debug_log[] = "✓✓✓ EMAIL SENT SUCCESSFULLY ✓✓✓";
                $debug_log[] = "Total recipients: $email_added_count";
                
                // Simpan log ke file
                file_put_contents('email_debug_log.txt', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
                
                $success_message = "Success!\\n\\n";
                $success_message .= "Reviewers Inserted: $insert_success_count\\n";
                if ($duplicate_count > 0) {
                    $success_message .= "Duplicates Skipped: $duplicate_count\\n";
                }
                $success_message .= "Emails Sent: $email_added_count";
                
                echo "<script>
                          alert('$success_message');
                          document.location='index_login.php';
                      </script>";
            }
        } else {
            $debug_log[] = "✗ No recipients to send email to!";
            $debug_log[] = "Possible reason: All selected reviewers were duplicates or database insert failed";
            file_put_contents('email_debug_log.txt', implode("\n", $debug_log) . "\n\n", FILE_APPEND);
            
            $message = "Process completed.\\n\\n";
            $message .= "Reviewers Inserted: $insert_success_count\\n";
            $message .= "Duplicates Skipped: $duplicate_count\\n";
            $message .= "Failed: $insert_failed_count\\n\\n";
            
            if ($insert_success_count == 0) {
                $message .= "No new reviewers were added!\\n";
                $message .= "All selected reviewers might already exist in the database.";
            } else {
                $message .= "No email sent (no valid recipients).";
            }
            
            echo "<script>
                      alert('$message');
                      document.location='index_login.php';
                  </script>";
        }
        
        $debug_log[] = "=== END SAVE PROCESS ===\n\n";
        
    } else {
        // Tidak ada item yang dipilih
        echo "<script>
                  alert('No reviewer selected!\\n\\nPlease select at least one reviewer.');
                  history.back();
              </script>";
    }
}

// ========================================================================
// PROSES SKIP
// ========================================================================
if (isset($_POST['skip'])){
    echo "<script>document.location='upload.php';</script>";
}
?>

<?php 
// Check if footer.php exists before including it
if (file_exists('footer.php')) {
    include('footer.php');
} else {
    // Fallback: Close HTML tags manually if footer doesn't exist
    echo '</div>'; // Close any open container divs
    echo '</body>';
    echo '</html>';
}
?>