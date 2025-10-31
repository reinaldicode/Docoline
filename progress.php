<?php
// progress.php - Progress Monitoring untuk Approver (Per Bawahan) with Charts
include('header.php');
include('koneksi.php');

// ✅ SECURITY CHECK: Hanya Approver yang boleh akses
if (!isset($_SESSION['state']) || $_SESSION['state'] !== 'Approver') {
    echo "<script>alert('Access denied! Only Approvers can access this page.'); window.location='index_login.php';</script>";
    exit;
}

// ✅ GET NRP from session (use 'username' not 'nrp')
$nrp = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// ✅ GET APPROVER INFO (Section & Name)
$sql_approver = "SELECT section, name, email FROM users WHERE username='$nrp' LIMIT 1";
$result_approver = mysqli_query($link, $sql_approver);

// ✅ SAFE CHECK: Pastikan data approver ada
if ($result_approver && mysqli_num_rows($result_approver) > 0) {
    $approver_data = mysqli_fetch_assoc($result_approver);
    $approver_section = isset($approver_data['section']) ? trim($approver_data['section']) : '';
    $approver_name = isset($approver_data['name']) ? trim($approver_data['name']) : '';
} else {
    $approver_section = '';
    $approver_name = 'Unknown';
    error_log("Approver data not found for NRP: " . $nrp);
}

// ✅ NORMALIZE SECTION (remove "Section" suffix)
function normalizeSection($section) {
    if (empty($section)) return $section;
    $section = trim($section);
    $section = preg_replace('/\s+Section$/i', '', $section);
    return $section;
}

$approver_section_normalized = normalizeSection($approver_section);

// ✅ BUILD SECTION VARIANTS - Support all sections with variations
$section_variants = array($approver_section_normalized);

// Add common section variations (with/without "Section" suffix)
if (!empty($approver_section_normalized)) {
    $section_variants[] = $approver_section_normalized . ' Section';
}

// SPECIAL CASES for specific sections based on actual database
switch (strtolower($approver_section_normalized)) {
    case 'qa':
        $section_variants[] = 'QC';
        $section_variants[] = 'QA Section';
        $section_variants[] = 'Quality Assurance';
        break;
    
    case 'qc':
        $section_variants[] = 'QA';
        $section_variants[] = 'QA Section';
        $section_variants[] = 'Quality Control';
        break;
    
    case 'production':
        $section_variants[] = 'Production Section';
        break;
    
    case 'engineering':
        $section_variants[] = 'Engineering Section';
        break;
    
    case 'purchasing and mc':
        $section_variants[] = 'Purchasing and MC Section';
        break;
    
    case 'ga and personnel':
        $section_variants[] = 'GA and Personnel Section';
        break;
    
    case 'management information system':
        $section_variants[] = 'MIS';
        break;
    
    case 'product warehouse control':
        $section_variants[] = 'PWC';
        $section_variants[] = 'Warehouse';
        break;
    
    case 'product innovation':
        $section_variants[] = 'Innovation';
        break;
    
    case 'research and development':
        $section_variants[] = 'RND';
        $section_variants[] = 'R&D';
        break;
    
    case 'business management':
        $section_variants[] = 'Business Mgmt';
        break;
    
    case 'exim control':
        $section_variants[] = 'EXIM';
        break;
    
    case 'production sales control':
        $section_variants[] = 'Production Sales Control Section';
        $section_variants[] = 'PSC';
        break;
    
    case 'maintenance':
        $section_variants[] = 'Maintenance Section';
        $section_variants[] = 'MT';
        break;
    
    case 'accounting':
        $section_variants[] = 'Accounting Section';
        $section_variants[] = 'Finance';
        break;
    
    case 'fcs group':
    case 'fcs':
        $section_variants[] = 'FCS Group';
        $section_variants[] = 'FCS Section';
        break;
    
    case 'job innovation':
        $section_variants[] = 'Job Innovation Section';
        break;
}

// Remove duplicates and empty values
$section_variants = array_unique(array_filter($section_variants));

// Build section condition for SQL
$section_condition = "(";
foreach ($section_variants as $i => $variant) {
    if ($i > 0) $section_condition .= " OR ";
    $section_condition .= "LOWER(TRIM(users.section)) = LOWER('" . mysqli_real_escape_string($link, $variant) . "')";
}
$section_condition .= ")";

// ✅ VALIDATE: Pastikan section tidak kosong
if (empty($approver_section_normalized)) {
    echo "<div class='container-fluid' style='margin-top:100px;'>";
    echo "<div class='alert alert-danger'>";
    echo "<h4><span class='glyphicon glyphicon-exclamation-sign'></span> Error: No Section Data</h4>";
    echo "<p>Your account doesn't have a section assigned. Please contact administrator.</p>";
    echo "<p><a href='index_login.php' class='btn btn-default'>Back to Home</a></p>";
    echo "</div>";
    echo "</div>";
    include('footer.php');
    exit;
}

// ✅ GET LIST BAWAHAN
$sql_bawahan = "SELECT 
                    username,
                    name,
                    email,
                    state,
                    section
                FROM users
                WHERE $section_condition
                AND (LOWER(state) = 'admin' OR LOWER(state) = 'originator')
                ORDER BY state, name";

$result_bawahan = mysqli_query($link, $sql_bawahan);

if (!$result_bawahan) {
    die("Query Error: " . mysqli_error($link));
}

// ✅ Hitung statistik dokumen untuk setiap bawahan
function getDocumentStats($link, $user_id) {
    $sql_stats = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Review' OR status = 'Pending' THEN 1 ELSE 0 END) as review,
                    SUM(CASE WHEN status = 'Secured' THEN 1 ELSE 0 END) as secured,
                    SUM(CASE WHEN status = 'Obsolete' OR status = 'Obsolate' THEN 1 ELSE 0 END) as obsolete
                  FROM docu
                  WHERE user_id = '$user_id'";
    
    $result_stats = mysqli_query($link, $sql_stats);
    return mysqli_fetch_assoc($result_stats);
}

// ✅ GET OVERALL SECTION STATISTICS
$sql_section_stats = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN docu.status = 'Review' OR docu.status = 'Pending' THEN 1 ELSE 0 END) as review,
                        SUM(CASE WHEN docu.status = 'Secured' THEN 1 ELSE 0 END) as secured,
                        SUM(CASE WHEN docu.status = 'Obsolete' OR docu.status = 'Obsolate' THEN 1 ELSE 0 END) as obsolete
                      FROM docu
                      INNER JOIN users ON docu.user_id = users.username
                      WHERE $section_condition
                      AND (LOWER(users.state) = 'admin' OR LOWER(users.state) = 'originator')";

$result_section_stats = mysqli_query($link, $sql_section_stats);
$section_stats = mysqli_fetch_assoc($result_section_stats);

// ✅ Prepare data for charts
$team_members_data = array();
$result_bawahan_data = mysqli_query($link, $sql_bawahan); // Re-query for data
while ($member = mysqli_fetch_assoc($result_bawahan_data)) {
    $stats = getDocumentStats($link, $member['username']);
    $team_members_data[] = array(
        'name' => $member['name'],
        'username' => $member['username'],
        'state' => $member['state'],
        'stats' => $stats
    );
}
?>

<!-- Chart.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<style>
/* ===== SOFT COLOR PALETTE ===== */
:root {
    --soft-blue: #6B9BD1;
    --soft-purple: #9B8FC4;
    --soft-green: #7EC8A3;
    --soft-orange: #F4A261;
    --soft-red: #E76F51;
    --soft-gray: #95A5A6;
    --light-bg: #F8F9FB;
    --card-bg: #FFFFFF;
    --text-primary: #2C3E50;
    --text-secondary: #7F8C8D;
    --deep-blue: #4A7BA7;
    --bright-blue: #5B9FD8;
}

/* ===== CHART CONTAINER ===== */
.chart-container {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 28px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.04);
    position: relative;
    overflow: hidden;
}

.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--soft-blue), var(--soft-purple));
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.chart-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.chart-title i {
    color: var(--soft-blue);
    margin-right: 8px;
}

.chart-canvas-wrapper {
    position: relative;
    height: 300px;
}

.chart-canvas-wrapper.tall {
    height: 400px;
}

/* ===== CARD STYLES - SOFT & MODERN ===== */
.person-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0,0,0,0.04);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.person-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--soft-blue);
    transition: width 0.3s ease;
}

.person-card.admin::before { background: var(--soft-red); }
.person-card.originator::before { background: var(--soft-green); }

.person-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

.person-card:hover::before {
    width: 6px;
}

.person-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.person-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--soft-blue) 0%, var(--soft-purple) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 600;
    margin-right: 16px;
    box-shadow: 0 4px 12px rgba(107, 155, 209, 0.3);
}

.person-card.admin .person-avatar {
    background: linear-gradient(135deg, var(--soft-red) 0%, #E89E88 100%);
    box-shadow: 0 4px 12px rgba(231, 111, 81, 0.3);
}

.person-card.originator .person-avatar {
    background: linear-gradient(135deg, var(--soft-green) 0%, #A8DBC0 100%);
    box-shadow: 0 4px 12px rgba(126, 200, 163, 0.3);
}

.person-info h4 {
    margin: 0 0 6px 0;
    color: var(--text-primary);
    font-size: 18px;
    font-weight: 600;
}

.person-info .role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-badge.admin { background: var(--soft-red); }
.role-badge.originator { background: var(--soft-green); }

.person-info .email-text {
    font-size: 13px;
    color: var(--text-secondary);
    margin-top: 4px;
}

.person-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
    padding: 14px 8px;
    background: var(--light-bg);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: #EEF1F5;
    transform: scale(1.05);
}

.stat-item .number {
    font-size: 26px;
    font-weight: 700;
    display: block;
    line-height: 1;
    margin-bottom: 4px;
}

.stat-item .label {
    font-size: 11px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.stat-item.total .number { color: var(--soft-blue); }
.stat-item.review .number { color: var(--soft-orange); }
.stat-item.approved .number { color: var(--soft-green); }
.stat-item.pending .number { color: var(--soft-red); }

/* ===== SECTION OVERVIEW - BLUE GRADIENT ===== */
.section-overview {
    background: linear-gradient(135deg, #5B9FD8 0%, #4A7BA7 100%);
    color: white;
    padding: 36px;
    border-radius: 16px;
    margin-bottom: 32px;
    box-shadow: 0 8px 24px rgba(74, 123, 167, 0.35);
    position: relative;
    overflow: hidden;
}

.section-overview::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
}

.section-overview::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 250px;
    height: 250px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.section-overview h2 {
    margin: 0 0 12px 0;
    font-size: 28px;
    font-weight: 700;
    position: relative;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-overview p {
    margin: 0;
    font-size: 15px;
    opacity: 0.95;
    position: relative;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-top: 24px;
    position: relative;
}

.overview-stat {
    text-align: center;
    padding: 20px 16px;
    background: rgba(255,255,255,0.25);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.4);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.overview-stat:hover {
    background: rgba(255,255,255,0.35);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.overview-stat .number {
    font-size: 36px;
    font-weight: 700;
    display: block;
    margin-bottom: 6px;
    line-height: 1;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.overview-stat .label {
    font-size: 13px;
    opacity: 0.95;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.view-details-btn {
    margin-top: 20px;
    width: 100%;
    background: white;
    color: var(--soft-blue);
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.view-details-btn:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    color: var(--soft-blue);
}

.person-card.admin .view-details-btn {
    color: var(--soft-red);
}

.person-card.originator .view-details-btn {
    color: var(--soft-green);
}

/* ===== HEADER STYLING ===== */
.page-title {
    color: var(--text-primary);
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 15px;
    margin-bottom: 24px;
}

.section-divider {
    border: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
    margin: 24px 0;
}

/* ===== EMPTY STATE ===== */
.no-data {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.no-data .icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: var(--light-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-data i {
    font-size: 40px;
    color: var(--soft-gray);
}

.no-data h4 {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 8px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .person-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .overview-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .section-overview {
        padding: 24px;
    }
    
    .chart-canvas-wrapper,
    .chart-canvas-wrapper.tall {
        height: 250px;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.person-card, .chart-container {
    animation: fadeInUp 0.5s ease-out;
}

.person-card:nth-child(1) { animation-delay: 0.1s; }
.person-card:nth-child(2) { animation-delay: 0.2s; }
.person-card:nth-child(3) { animation-delay: 0.3s; }
</style>

<br><br><br>
<div class="container-fluid">
    
    <!-- ✅ SECTION OVERVIEW - BLUE GRADIENT -->
    <div class="section-overview">
        <h2>
            <span class="glyphicon glyphicon-signal"></span> 
            Progress Monitoring - <?php echo htmlspecialchars($approver_section); ?>
        </h2>
        <p>
            <span class="glyphicon glyphicon-user"></span> 
            Approver: <strong><?php echo htmlspecialchars($approver_name ?: 'Unknown'); ?></strong> (<?php echo htmlspecialchars($nrp ?: 'N/A'); ?>)
        </p>
        
        <div class="overview-stats">
            <div class="overview-stat">
                <span class="number"><?php echo $section_stats['total']; ?></span>
                <span class="label">Total Documents</span>
            </div>
            <div class="overview-stat">
                <span class="number"><?php echo $section_stats['review']; ?></span>
                <span class="label">In Review</span>
            </div>
            <div class="overview-stat">
                <span class="number"><?php echo $section_stats['secured']; ?></span>
                <span class="label">Secured</span>
            </div>
            <div class="overview-stat">
                <span class="number"><?php echo $section_stats['obsolete']; ?></span>
                <span class="label">Obsolete</span>
            </div>
        </div>
    </div>

    <!-- ✅ CHARTS SECTION -->
    <div class="row">
        <!-- Pie Chart - Status Distribution -->
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="glyphicon glyphicon-stats"></i>
                        Document Status Distribution
                    </h3>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart - Team Performance -->
        <div class="col-md-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="glyphicon glyphicon-dashboard"></i>
                        Secured Document Rate
                    </h3>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="approvalDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Chart - Team Members Comparison -->
    <div class="row">
        <div class="col-md-12">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">
                        <i class="glyphicon glyphicon-equalizer"></i>
                        Team Members Document Comparison
                    </h3>
                </div>
                <div class="chart-canvas-wrapper tall">
                    <canvas id="teamBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ LIST BAWAHAN HEADER -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-title">
                <span class="glyphicon glyphicon-list"></span> 
                Team Members 
                <span class="badge" style="background: var(--soft-blue); color: white; font-size: 14px; padding: 6px 12px; border-radius: 12px;">
                    <?php echo count($team_members_data); ?>
                </span>
            </h3>
            <p class="page-subtitle">Click on a team member to view their document progress</p>
            <hr class="section-divider">
        </div>
    </div>

    <!-- ✅ LIST BAWAHAN CARDS - SOFT DESIGN -->
    <div class="row">
        <?php if (count($team_members_data) == 0): ?>
            <div class="col-md-12">
                <div class="no-data">
                    <div class="icon-wrapper">
                        <i class="glyphicon glyphicon-inbox"></i>
                    </div>
                    <h4>No Team Members Found</h4>
                    <p>There are no Admin or Originator in <strong><?php echo htmlspecialchars($approver_section); ?></strong> section.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($team_members_data as $bawahan): 
                $stats = $bawahan['stats'];
                $role_class = strtolower($bawahan['state']);
                $initials = strtoupper(substr($bawahan['name'], 0, 2));
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="person-card <?php echo $role_class; ?>" 
                     onclick="window.location.href='progress_detail.php?user_id=<?php echo urlencode($bawahan['username']); ?>'">
                    
                    <div class="person-header">
                        <div class="person-avatar">
                            <?php echo $initials; ?>
                        </div>
                        <div class="person-info">
                            <h4><?php echo htmlspecialchars($bawahan['name']); ?></h4>
                            <span class="role-badge <?php echo $role_class; ?>">
                                <?php echo htmlspecialchars($bawahan['state']); ?>
                            </span>
                            <div class="email-text">
                                <span class="glyphicon glyphicon-envelope"></span> 
                                <?php echo htmlspecialchars($bawahan['username']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="person-stats">
                        <div class="stat-item total">
                            <span class="number"><?php echo $stats['total']; ?></span>
                            <span class="label">Total</span>
                        </div>
                        <div class="stat-item review">
                            <span class="number"><?php echo $stats['review']; ?></span>
                            <span class="label">Review</span>
                        </div>
                        <div class="stat-item approved">
                            <span class="number"><?php echo $stats['secured']; ?></span>
                            <span class="label">Secured</span>
                        </div>
                        <div class="stat-item pending">
                            <span class="number"><?php echo $stats['obsolete']; ?></span>
                            <span class="label">Obsolete</span>
                        </div>
                    </div>

                    <button class="btn view-details-btn" type="button">
                        <span class="glyphicon glyphicon-eye-open"></span> View Documents
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
// Chart.js Configuration
Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
Chart.defaults.color = '#7F8C8D';

// Color Palette
const colors = {
    blue: '#6B9BD1',
    orange: '#F4A261',
    green: '#7EC8A3',
    red: '#E76F51',
    purple: '#9B8FC4',
    gray: '#95A5A6'
};

// 1. Status Pie Chart
const statusPieCtx = document.getElementById('statusPieChart').getContext('2d');
new Chart(statusPieCtx, {
    type: 'pie',
    data: {
        labels: ['In Review', 'Secured', 'Obsolete'],
        datasets: [{
            data: [
                <?php echo $section_stats['review']; ?>,
                <?php echo $section_stats['secured']; ?>,
                <?php echo $section_stats['obsolete']; ?>
            ],
            backgroundColor: [colors.orange, colors.green, colors.gray],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(44, 62, 80, 0.9)',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const total = <?php echo $section_stats['total']; ?>;
                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// 2. Approval Rate Doughnut Chart
const approvalDoughnutCtx = document.getElementById('approvalDoughnutChart').getContext('2d');
const totalDocs = <?php echo $section_stats['total']; ?>;
const securedDocs = <?php echo $section_stats['secured']; ?>;
const securedRate = totalDocs > 0 ? ((securedDocs / totalDocs) * 100).toFixed(1) : 0;

new Chart(approvalDoughnutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Secured', 'Others'],
        datasets: [{
            data: [securedDocs, totalDocs - securedDocs],
            backgroundColor: [colors.green, '#E8F5E9'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(44, 62, 80, 0.9)',
                padding: 12,
                cornerRadius: 8
            }
        }
    },
    plugins: [{
        id: 'centerText',
        beforeDraw: function(chart) {
            const width = chart.width;
            const height = chart.height;
            const ctx = chart.ctx;
            ctx.restore();
            
            const fontSize = (height / 114).toFixed(2);
            ctx.font = 'bold ' + (fontSize * 2) + 'em sans-serif';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = colors.green;
            
            const text = securedRate + '%';
            const textX = Math.round((width - ctx.measureText(text).width) / 2);
            const textY = height / 2 - 10;
            
            ctx.fillText(text, textX, textY);
            
            ctx.font = (fontSize * 0.8) + 'em sans-serif';
            ctx.fillStyle = '#7F8C8D';
            const subText = 'Secured Rate';
            const subTextX = Math.round((width - ctx.measureText(subText).width) / 2);
            const subTextY = height / 2 + 20;
            ctx.fillText(subText, subTextX, subTextY);
            
            ctx.save();
        }
    }]
});

// 3. Team Members Bar Chart
const teamBarCtx = document.getElementById('teamBarChart').getContext('2d');
const teamData = <?php echo json_encode($team_members_data); ?>;

const teamNames = teamData.map(member => {
    const name = member.name.split(' ');
    return name[0] + (name.length > 1 ? ' ' + name[name.length - 1].charAt(0) + '.' : '');
});

const reviewData = teamData.map(member => member.stats.review);
const securedData = teamData.map(member => member.stats.secured);
const obsoleteData = teamData.map(member => member.stats.obsolete);

new Chart(teamBarCtx, {
    type: 'bar',
    data: {
        labels: teamNames,
        datasets: [
            {
                label: 'In Review',
                data: reviewData,
                backgroundColor: colors.orange,
                borderRadius: 8,
                borderSkipped: false
            },
            {
                label: 'Secured',
                data: securedData,
                backgroundColor: colors.green,
                borderRadius: 8,
                borderSkipped: false
            },
            {
                label: 'Obsolete',
                data: obsoleteData,
                backgroundColor: colors.gray,
                borderRadius: 8,
                borderSkipped: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            x: {
                stacked: false,
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            },
            y: {
                stacked: false,
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    stepSize: 1,
                    font: {
                        size: 11
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(44, 62, 80, 0.95)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 13,
                    weight: '600'
                },
                bodyFont: {
                    size: 12
                },
                callbacks: {
                    title: function(context) {
                        const index = context[0].dataIndex;
                        return teamData[index].name;
                    },
                    afterTitle: function(context) {
                        const index = context[0].dataIndex;
                        return 'Role: ' + teamData[index].state;
                    },
                    footer: function(context) {
                        const index = context[0].dataIndex;
                        const total = teamData[index].stats.total;
                        return '\nTotal Documents: ' + total;
                    }
                }
            }
        }
    }
});
</script>