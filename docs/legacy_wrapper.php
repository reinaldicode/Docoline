<?php
// legacy_wrapper.php - Universal wrapper untuk semua legacy files
// Cukup maintain 1 file ini saja!

class LegacyFilterWrapper {
    private $docType;
    private $filterConfig;
    private $globalFilters;
    private $link; // database connection
    
    public function __construct($docTypeName, $dbConnection) {
        $this->docType = $docTypeName;
        $this->link = $dbConnection;
        $this->loadConfigs();
    }
    
    private function loadConfigs() {
        $jsonFile = __DIR__ . '/data/document_types.json';
        $filterOptionsFile = __DIR__ . '/data/filter_options.json';
        
        // Load document types
        if (file_exists($jsonFile)) {
            $docTypes = json_decode(file_get_contents($jsonFile), true);
            
            foreach ($docTypes as $dt) {
                if (strcasecmp($dt['name'], $this->docType) === 0) {
                    $this->filterConfig = isset($dt['filter_config']) ? $dt['filter_config'] : [];
                    break;
                }
            }
        }
        
        // Load global filters
        if (file_exists($filterOptionsFile)) {
            $this->globalFilters = json_decode(file_get_contents($filterOptionsFile), true);
        }
        
        // Default fallback
        if (empty($this->filterConfig)) {
            $this->filterConfig = [
                'section_dept' => true,
                'status' => true
            ];
        }
    }
    
    public function renderFilterForm($additionalHiddenFields = []) {
        // Check if cascade dropdown needed
        $useCascade = isset($this->filterConfig['section_prod']) && 
                     $this->filterConfig['section_prod'] === true &&
                     isset($this->filterConfig['device']) && 
                     $this->filterConfig['device'] === true;
        
        ?>
        <div class="row">
            <div class="col-xs-4 well well-lg">
                <h2>Filter Document - <?php echo htmlspecialchars($this->docType); ?></h2>
                
                <form action="" method="GET">
                    <?php 
                    // Render hidden fields
                    foreach ($additionalHiddenFields as $key => $value) {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                    }
                    ?>
                    
                    <table>
                        <?php $this->renderFilters($useCascade); ?>
                        
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="hidden" name="by" value="no_drf">
                                <input type="submit" value="Show" name="submit" class="btn btn-info">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <?php
    }
    
    private function renderFilters($useCascade) {
        // Order filters properly
        $orderedFilters = [];
        
        if ($useCascade) {
            $orderedFilters = ['section_prod', 'device', 'process'];
            foreach ($this->filterConfig as $key => $enabled) {
                if ($enabled && !in_array($key, $orderedFilters)) {
                    $orderedFilters[] = $key;
                }
            }
        } else {
            foreach ($this->filterConfig as $key => $enabled) {
                if ($enabled) $orderedFilters[] = $key;
            }
        }
        
        // Render each filter
        foreach ($orderedFilters as $filterKey) {
            if (!$this->filterConfig[$filterKey]) continue;
            
            $filterData = $this->getFilterData($filterKey);
            $paramName = $this->getParamName($filterKey);
            
            if ($useCascade && $filterKey === 'section_prod') {
                $this->renderCascadeSection($filterData['label'], $paramName);
            } elseif ($useCascade && $filterKey === 'device') {
                $this->renderCascadeDevice();
            } elseif ($useCascade && $filterKey === 'process') {
                $this->renderCascadeProcess();
            } else {
                $this->renderStandardDropdown($filterKey, $filterData, $paramName);
            }
        }
    }
    
    private function renderCascadeSection($label, $paramName) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($label); ?></td>
            <td>:</td>
            <td>
                <?php include('func.php'); ?>
                <select name="<?php echo htmlspecialchars($paramName); ?>" id="section" class="form-control">
                    <option value="">Select <?php echo htmlspecialchars($label); ?></option>
                    <?php getTierOne(); ?>
                </select>
            </td>
        </tr>
        <?php
    }
    
    private function renderCascadeDevice() {
        ?>
        <tr>
            <td>Device</td>
            <td>:</td>
            <td>
                <span id="wait_1" style="display: none;"><img alt="Please Wait" src="images/wait.gif"/></span>
                <span id="result_1" style="display: none;"></span>
            </td>
        </tr>
        <?php
    }
    
    private function renderCascadeProcess() {
        ?>
        <tr>
            <td>Process</td>
            <td>:</td>
            <td>
                <span id="wait_2" style="display: none;"><img alt="Please Wait" src="images/wait.gif"/></span>
                <span id="result_2" style="display: none;"></span>
            </td>
        </tr>
        <?php
    }
    
    private function renderStandardDropdown($filterKey, $filterData, $paramName) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($filterData['label']); ?></td>
            <td>:</td>
            <td>
                <select name="<?php echo htmlspecialchars($paramName); ?>" class="form-control">
                    <option value="">--- Select <?php echo htmlspecialchars($filterData['label']); ?> ---</option>
                    <?php 
                    foreach ($filterData['options'] as $opt) {
                        $selected = (isset($_GET[$paramName]) && $_GET[$paramName] == $opt) ? 'selected' : '';
                        
                        $displayText = $opt;
                        if ($filterKey === 'status' && $opt === 'Secured') {
                            $displayText = 'Approved';
                        }
                        
                        echo '<option value="' . htmlspecialchars($opt) . '" ' . $selected . '>' . htmlspecialchars($displayText) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
    
    public function buildQuery() {
        $whereConditions = [];
        $needJoin = false;
        $sectionProdValue = '';
        
        // SPECIAL HANDLING: Untuk Obsolate, jangan filter doc_type
        if ($this->docType !== 'Obsolate' && $this->docType !== 'MS & ROHS') {
            $whereConditions[] = "docu.doc_type = '" . mysqli_real_escape_string($this->link, $this->docType) . "'";
        }
        
        // Untuk Obsolate, WAJIB filter status = 'Obsolate'
        if ($this->docType === 'Obsolate') {
            $whereConditions[] = "docu.status = 'Obsolate'";
        }
        
        // Untuk MS & ROHS
        if ($this->docType === 'MS & ROHS') {
            $typeParam = $this->getParamName('type');
            if (!isset($_GET[$typeParam]) || trim($_GET[$typeParam]) === '') {
                $whereConditions[] = "(docu.doc_type = 'Material Spec.' OR docu.doc_type = 'ROHS')";
            }
        }
        
        foreach ($this->filterConfig as $filterKey => $enabled) {
            if (!$enabled) continue;
            
            $paramName = $this->getParamName($filterKey);
            
            if ($filterKey === 'section_prod') {
                if (isset($_GET[$paramName]) && trim($_GET[$paramName]) !== '') {
                    $needJoin = true;
                    $sectionProdValue = mysqli_real_escape_string($this->link, $_GET[$paramName]);
                }
                continue;
            }
            
            $dbColumn = $this->getDbColumn($filterKey);
            
            if (isset($_GET[$paramName]) && trim($_GET[$paramName]) !== '') {
                $whereConditions[] = "docu.$dbColumn = '" . mysqli_real_escape_string($this->link, $_GET[$paramName]) . "'";
            }
        }
        
        // Pastikan ada minimal 1 kondisi WHERE
        if (empty($whereConditions)) {
            $whereConditions[] = "1=1";
        }
        
        $by = isset($_GET['by']) ? mysqli_real_escape_string($this->link, $_GET['by']) : 'no_drf';
        
        if ($needJoin) {
            $sql = "SELECT docu.* FROM docu 
                    LEFT JOIN device ON docu.device = device.name 
                    WHERE " . implode(' AND ', $whereConditions);
            
            if (!empty($sectionProdValue)) {
                $sql .= " AND device.group_dev = '$sectionProdValue'";
            }
            
            $sql .= " ORDER BY docu.$by DESC";
        } else {
            $sql = "SELECT * FROM docu WHERE " . implode(' AND ', $whereConditions) . " ORDER BY $by DESC";
        }
        
        return $sql;
    }
    
    private function getFilterData($filterKey) {
        if (isset($this->globalFilters[$filterKey])) {
            $filterData = $this->globalFilters[$filterKey];
            
            // DYNAMIC OPTIONS based on document type
            if (isset($filterData['dynamic']) && $filterData['dynamic'] === true) {
                if (isset($filterData['options_by_doctype'][$this->docType])) {
                    return [
                        'label' => $filterData['label'],
                        'options' => $filterData['options_by_doctype'][$this->docType]
                    ];
                }
            }
            
            return [
                'label' => $filterData['label'],
                'options' => $filterData['options']
            ];
        }
        
        return [
            'label' => ucfirst(str_replace('_', ' ', $filterKey)),
            'options' => []
        ];
    }
    
    private function getParamName($filterKey) {
        $mapping = [
            'section_dept' => 'section',
            'section_prod' => 'section',
            'process' => 'proc',
            'category' => 'cat',
            'device' => 'device',
            'status' => 'status',
            'type' => 'tipe',
            'language' => 'lang'  // ✅ TAMBAHKAN INI untuk language filter
        ];
        
        return isset($mapping[$filterKey]) ? $mapping[$filterKey] : $filterKey;
    }
    
    private function getDbColumn($filterKey) {
        if ($filterKey === 'section_dept') return 'section';
        if ($filterKey === 'section_prod') return 'section_prod';
        if ($filterKey === 'process') return 'process';
        if ($filterKey === 'category') return 'category';
        if ($filterKey === 'type') return 'doc_type';
        if ($filterKey === 'language') return 'language';  // ✅ TAMBAHKAN INI
        return $filterKey;
    }
    
    public function needsCascadeScript() {
        return isset($this->filterConfig['section_prod']) && 
               $this->filterConfig['section_prod'] === true &&
               isset($this->filterConfig['device']) && 
               $this->filterConfig['device'] === true;
    }
    
    public function getFilterInfo() {
        $info = [];
        
        // Document type
        $info['doc_type'] = $this->docType;
        
        // Loop through all filters
        foreach ($this->filterConfig as $filterKey => $enabled) {
            if (!$enabled) continue;
            
            $paramName = $this->getParamName($filterKey);
            
            // Check if filter has value
            if (isset($_GET[$paramName]) && trim($_GET[$paramName]) !== '') {
                $value = $_GET[$paramName];
                $info[$paramName] = $value;
                
                // Get readable name/label
                if ($filterKey === 'section_dept' || $filterKey === 'section_prod') {
                    $sql = "SELECT sect_name FROM section WHERE id_section = '" 
                           . mysqli_real_escape_string($this->link, $value) . "'";
                    $result = mysqli_query($this->link, $sql);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        $info['section_name'] = $row['sect_name'];
                    } else {
                        $info['section_name'] = $value;
                    }
                    
                } elseif ($filterKey === 'device') {
                    $sql = "SELECT name FROM device WHERE id_device = '" 
                           . mysqli_real_escape_string($this->link, $value) . "'";
                    $result = mysqli_query($this->link, $sql);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        $info['device_name'] = $row['name'];
                    } else {
                        $info['device_name'] = $value;
                    }
                    
                } elseif ($filterKey === 'process') {
                    $info['process'] = $value;
                    $info['process_name'] = $value;
                    
                } elseif ($filterKey === 'category') {
                    $info['category'] = $value;
                    $info['category_name'] = $value;
                    
                } elseif ($filterKey === 'status') {
                    $info['status'] = $value;
                    $info['status_name'] = ($value === 'Secured') ? 'Approved' : $value;
                    
                } elseif ($filterKey === 'type') {
                    $info['tipe'] = $value;
                    $info['type_name'] = $value;
                    
                } elseif ($filterKey === 'language') {  // ✅ TAMBAHKAN INI
                    $info['lang'] = $value;
                    $info['language_name'] = $value;
                    
                } else {
                    $info[$filterKey] = $value;
                    $info[$filterKey . '_name'] = $value;
                }
            }
        }
        
        // Fallback values
        if (!isset($info['tipe']) && isset($_GET['tipe'])) {
            $info['tipe'] = $_GET['tipe'];
        }
        if (!isset($info['section_name'])) {
            $info['section_name'] = 'All';
        }
        if (!isset($info['category'])) {
            $info['category'] = 'All';
        }
        if (!isset($info['tipe'])) {
            $info['tipe'] = 'Document';
        }
        
        return $info;
    }
}
?>