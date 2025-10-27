<?php
//**************************************
//  Section Department (from docu table)
//**************************************
function getSectionDept()
{
    include "koneksi.php";
    $result = mysqli_query($link, "SELECT DISTINCT section FROM docu WHERE section IS NOT NULL AND section != '' ORDER BY section") 
    or die(mysqli_error($link));

    while($row = mysqli_fetch_array($result)) {
        echo '<option value="'.htmlspecialchars($row['section']).'">'.htmlspecialchars($row['section']).'</option>';
    }
}

//**************************************
//  Section Production (from device table)
//**************************************
function getSectionProd()
{
    include "koneksi.php";
    $result = mysqli_query($link, "SELECT DISTINCT group_dev FROM device WHERE group_dev IS NOT NULL AND group_dev != '' ORDER BY group_dev") 
    or die(mysqli_error($link));

    while($row = mysqli_fetch_array($result)) {
        echo '<option value="'.htmlspecialchars($row['group_dev']).'">'.htmlspecialchars($row['group_dev']).'</option>';
    }
}

//**************************************
// LEGACY: getTierOne (backward compatibility)
//**************************************
function getTierOne()
{
    getSectionProd();
}

//**************************************
// AJAX Handler: Section Production → Device
//**************************************
if (isset($_GET['func']) && $_GET['func'] == "section_prod") { 
    if (isset($_GET['drop_var'])) {
        getSectionProdDevices($_GET['drop_var']); 
        exit; // ✅ PENTING: Stop execution
    }
}

// Alias untuk backward compatibility
if (isset($_GET['func']) && $_GET['func'] == "section") { 
    if (isset($_GET['drop_var'])) {
        getSectionProdDevices($_GET['drop_var']); 
        exit; // ✅ PENTING: Stop execution
    }
}

function getSectionProdDevices($section_prod)
{  
    include "koneksi.php";
    $section_prod = mysqli_real_escape_string($link, $section_prod);
    
    $result = mysqli_query($link, "SELECT id_device, name, kode FROM device WHERE group_dev='$section_prod' ORDER BY name") 
    or die(mysqli_error($link));
    
    echo '<select name="device" id="device" class="form-control">
          <option value="" selected="selected">--- Select Device ---</option>
          <option value="General production">General production</option>
          <option value="General PC">General PC</option>';

    while($row = mysqli_fetch_array($result)) {
        echo '<option value="'.htmlspecialchars($row['name']).'">'.htmlspecialchars($row['name']).'</option>';
    }
    
    echo '</select>';
}

//**************************************
// AJAX Handler: Device → Process
//**************************************
if (isset($_GET['func']) && $_GET['func'] == "device") { 
    if (isset($_GET['drop_var2'])) {
        getDeviceProcesses($_GET['drop_var2']); 
        exit; // ✅ PENTING: Stop execution
    }
}

function getDeviceProcesses($device)
{  
    include "koneksi.php";
    $device = mysqli_real_escape_string($link, $device);
    
    // Query UNIVERSAL - ambil semua process untuk device ini (apapun doc_type-nya)
    $result = mysqli_query($link, "SELECT DISTINCT process FROM docu 
                                   WHERE device = '$device' 
                                   AND process <> '0' 
                                   AND process <> '' 
                                   AND TRIM(process) <> ''
                                   ORDER BY process") 
    or die(mysqli_error($link));
    
    echo '<select name="proc" id="proc" class="form-control">
          <option value="" selected="selected">--- Select Process ---</option>
          <option value="General Process">General Process</option>';
    
    $count = 0;
    while($row = mysqli_fetch_array($result)) {
        $count++;
        echo '<option value="'.htmlspecialchars($row['process']).'">'.htmlspecialchars($row['process']).'</option>';
    }
    
    // Debug: jika tidak ada data
    if ($count == 0) {
        echo '<option disabled style="color:red;">No process found for this device</option>';
    }
    
    echo '</select>';
}

//**************************************
// Helper: Get all Status options
//**************************************
function getStatusOptions()
{  
    include "koneksi.php";
    $result = mysqli_query($link, "SELECT DISTINCT status FROM docu WHERE status IS NOT NULL AND status != '' ORDER BY status") 
    or die(mysqli_error($link));

    while($row = mysqli_fetch_array($result)) {
        echo '<option value="'.htmlspecialchars($row['status']).'">'.htmlspecialchars($row['status']).'</option>';
    }
}
?>