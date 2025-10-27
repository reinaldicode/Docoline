<?php
// check_notifications.php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if(empty($_SESSION['user_authentication']) || $_SESSION['user_authentication'] != "valid") {
    http_response_code(401);
    exit();
}

// Ambil data user dari session
$nrp = $_SESSION['nrp'] ?? '';
$state = $_SESSION['state'] ?? '';

// Fungsi untuk menghitung notifikasi (sama seperti di file utama)
function getNotificationCounts($link, $nrp, $state) {
    $counts = array('WI' => 0, 'Procedure' => 0, 'Form' => 0, 'total' => 0);
    
    $types = ['WI', 'Procedure', 'Form'];
    
    foreach($types as $type) {
        if($state == 'Admin') {
            $sql = "SELECT COUNT(*) as count FROM docu WHERE doc_type='$type' AND 
                    (status='Review' OR (DATEDIFF(NOW(), tgl_upload) >= 1 AND status='Review'))";
        } elseif($state == 'Originator') {
            $sql = "SELECT COUNT(*) as count FROM docu WHERE doc_type='$type' AND 
                    user_id='$nrp' AND status IN ('Review','Pending','Approved')";
        } elseif($state == 'Approver') {
            $sql = "SELECT COUNT(*) as count FROM docu,rev_doc WHERE docu.doc_type='$type' AND
                    docu.status='Review' AND rev_doc.status='Review' AND 
                    docu.no_drf=rev_doc.id_doc AND rev_doc.nrp='$nrp'";
        }
        
        $result = mysqli_query($link, $sql);
        if($result) {
            $row = mysqli_fetch_assoc($result);
            $counts[$type] = (int)$row['count'];
            $counts['total'] += $counts[$type];
        }
    }
    
    return $counts;
}

// Fungsi untuk mendapatkan notifikasi terbaru
function getRecentNotifications($link, $nrp, $state, $limit = 5) {
    $notifications = array();
    
    if($state == 'Admin') {
        $sql = "SELECT no_drf, no_doc, title, doc_type, status, tgl_upload, 
                DATEDIFF(NOW(), tgl_upload) as days_passed,
                CASE 
                    WHEN DATEDIFF(NOW(), tgl_upload) >= 3 THEN 'overdue'
                    WHEN DATEDIFF(NOW(), tgl_upload) >= 1 THEN 'urgent'
                    ELSE 'normal'
                END as priority
                FROM docu WHERE status IN ('Review','Pending') 
                ORDER BY tgl_upload DESC LIMIT $limit";
    } elseif($state == 'Originator') {
        $sql = "SELECT no_drf, no_doc, title, doc_type, status, tgl_upload,
                DATEDIFF(NOW(), tgl_upload) as days_passed,
                CASE 
                    WHEN DATEDIFF(NOW(), tgl_upload) >= 3 THEN 'overdue'
                    WHEN DATEDIFF(NOW(), tgl_upload) >= 1 THEN 'urgent'
                    ELSE 'normal'
                END as priority
                FROM docu WHERE user_id='$nrp' AND status IN ('Review','Pending','Approved')
                ORDER BY tgl_upload DESC LIMIT $limit";
    } elseif($state == 'Approver') {
        $sql = "SELECT docu.no_drf, docu.no_doc, docu.title, docu.doc_type, docu.status, docu.tgl_upload,
                DATEDIFF(NOW(), docu.tgl_upload) as days_passed,
                CASE 
                    WHEN DATEDIFF(NOW(), docu.tgl_upload) >= 3 THEN 'overdue'
                    WHEN DATEDIFF(NOW(), docu.tgl_upload) >= 1 THEN 'urgent'
                    ELSE 'normal'
                END as priority
                FROM docu,rev_doc WHERE docu.status='Review' AND rev_doc.status='Review' 
                AND docu.no_drf=rev_doc.id_doc AND rev_doc.nrp='$nrp'
                ORDER BY docu.tgl_upload DESC LIMIT $limit";
    }
    
    $result = mysqli_query($link, $sql);
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }
    
    return $notifications;
}

// Ambil data notifikasi saat ini
$currentCounts = getNotificationCounts($link, $nrp, $state);
$recentNotifications = getRecentNotifications($link, $nrp, $state);

// Cek apakah ada update baru (bisa dibandingkan dengan data sebelumnya di session atau cache)
$lastCheckTime = $_SESSION['last_notification_check'] ?? 0;
$currentTime = time();

// Update waktu pengecekan terakhir
$_SESSION['last_notification_check'] = $currentTime;

// Anggap ada update jika lebih dari 60 detik sejak pengecekan terakhir
$hasUpdates = ($currentTime - $lastCheckTime) > 60 && $currentCounts['total'] > 0;

// Format response sebagai JSON
$response = array(
    'hasUpdates' => $hasUpdates,
    'totalCount' => $currentCounts['total'],
    'counts' => $currentCounts,
    'notifications' => $recentNotifications,
    'timestamp' => date('Y-m-d H:i:s')
);

// Set header untuk JSON response
header('Content-Type: application/json');

// Return JSON response
echo json_encode($response);

// Log untuk debugging (opsional)
error_log("Notification check for user: $nrp ($state) - Total: " . $currentCounts['total']);
?>