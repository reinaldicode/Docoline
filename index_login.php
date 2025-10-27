<?php
include('header.php');
// extract($_REQUEST); // Sebaiknya tidak digunakan, lebih aman memanggil variabel session secara langsung.
?>
<!DOCTYPE html>
<head>
    <script src="js/highcharts.js"></script>
    <script src="js/exporting.js"></script>
    </head>
<body> <br /><br /><br /><br />

<div id="profile">
    <div class="alert alert-info" role="alert">
        <b id="welcome">Welcome : <i><?php echo htmlspecialchars($name); ?>, anda login sebagai <?php echo htmlspecialchars($state);?></i></b>
    </div>
</div>

<div class="well well-lg">
    <img src="images/stamped.jpg" class="img-responsive" alt="Responsive image">
    <div class="row">
        <div class="col-xs-offset-2 col-xs-8">
            <div class="alert alert-warning" role="alert" style="margin-top:-110px;">
                <b id="welcome">Bapak / Ibu sekalian harap untuk memastikan kembali dokumen (prosedur, instruksi kerja, Formulir, atau check sheet) yang disimpan atau digunakan di area kerja bapak dan ibu telah memiliki stempel dari pengendali dokumen (document control).</b>
            </div>
        </div>
    </div>
</div>
    
<script type="text/javascript">
$(function () { // Cara penulisan $(document).ready() yang lebih singkat
    var chart1 = new Highcharts.Chart({
        chart: {
            renderTo: 'container',
            type: 'column'
        },
        title: {
            text: 'Grafik Jumlah Dokumen Aktif'
        },
        xAxis: {
            categories: ['Section']
        },
        yAxis: {
            title: {
                text: 'Jumlah Dokumen'
            }
        },
        series: [
        <?php
        include 'koneksi.php';
        $seriesData = []; // Tampung data dalam array
        $sql = "SELECT DISTINCT section FROM docu ORDER BY section"; 
        $query = mysqli_query($link, $sql) or die(mysqli_error($link));

        while ($ret = mysqli_fetch_array($query)) {
            $section = $ret['section'];
            // Perbaiki query untuk menghitung jumlah dan ejaan 'Obsolete'
            $sql_jumlah = "SELECT COUNT(section) AS jumlah FROM docu WHERE section='" . mysqli_real_escape_string($link, $section) . "' AND section <> 'Manual' AND status <> 'Obsolete'";
            $query_jumlah = mysqli_query($link, $sql_jumlah) or die(mysqli_error($link));
            $data = mysqli_fetch_array($query_jumlah);
            $jumlah = $data['jumlah'] ?? 0; // Default ke 0 jika null

            // Tambahkan data ke array
            $seriesData[] = "{ name: '" . addslashes($section) . "', data: [" . $jumlah . "] }";
        }
        // Gabungkan data dengan koma, ini secara otomatis menangani koma terakhir
        echo implode(",", $seriesData); 
        ?>
        ]
    });
});
</script>

<div class="row">
    <div id='container' class="col-xs-12 well well-lg"></div>
</div>

<div class="">
    <div class="row">
        <div class="col-xs-12 well well-lg">
            Latest Uploaded Document:
            <table class="table">
                <tbody>
                    <tr>
                        <?php 
                        // Perbaiki pemanggilan mysqli_error
                        $query_proc = "SELECT no_doc, file, tgl_upload FROM docu WHERE doc_type = 'Procedure' AND STATUS = 'Review' ORDER BY no_drf DESC LIMIT 3";
                        $res_proc = mysqli_query($link, $query_proc) or die(mysqli_error($link));
                        ?>
                        <td>Procedure</td><td>:</td>
                        <td>
                            <?php while ($data = mysqli_fetch_array($res_proc)) { ?>
                                <a href="Procedure/<?php echo htmlspecialchars($data['file']); ?>" title="<?php echo htmlspecialchars($data['tgl_upload']); ?>">
                                    <?php echo htmlspecialchars($data['no_doc']);?>
                                </a>,
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <?php 
                        $query_wi = "SELECT no_doc, file, tgl_upload FROM docu WHERE doc_type = 'WI' AND STATUS = 'Review' ORDER BY no_drf DESC LIMIT 3";
                        $res_wi = mysqli_query($link, $query_wi) or die(mysqli_error($link));
                        ?>
                        <td>Work Instruction</td><td>:</td>
                        <td>
                            <?php while ($data = mysqli_fetch_array($res_wi)) { ?>
                                <a href="WI/<?php echo htmlspecialchars($data['file']); ?>" title="<?php echo htmlspecialchars($data['tgl_upload']); ?>">
                                    <?php echo htmlspecialchars($data['no_doc']);?>
                                </a>,
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <?php 
                        $query_form = "SELECT no_doc, file, tgl_upload FROM docu WHERE doc_type = 'Form' AND STATUS = 'Review' ORDER BY no_drf DESC LIMIT 3";
                        $res_form = mysqli_query($link, $query_form) or die(mysqli_error($link));
                        ?>
                        <td>Form</td><td>:</td>
                        <td>
                            <?php while ($data = mysqli_fetch_array($res_form)) { ?>
                                <a href="Form/<?php echo htmlspecialchars($data['file']); ?>" title="<?php echo htmlspecialchars($data['tgl_upload']); ?>">
                                    <?php echo htmlspecialchars($data['no_doc']); ?>
                                </a>,
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>