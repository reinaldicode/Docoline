<?php
/**
 * ============================================================================
 * PENGATURAN TIPE DOKUMEN TERPUSAT (DOCUMENT CONFIG)
 * ============================================================================
 * * Class ini menangani semua logika untuk memuat, menyimpan (cache), 
 * dan mem-format tipe dokumen dari 'data/document_types.json'.
 *
 * File lain (my_doc.php, edit_doc.php, header.php) HARUS include file ini.
 */

class DocumentConfig {
    
    private static $flatDocTypes = null;     // Cache statis untuk daftar flat (untuk filter)
    private static $structuredDocTypes = null; // Cache statis untuk daftar terstruktur (untuk menu nav)
    
    private static $cacheFile = null;
    private static $jsonFile = null;

    /**
     * Inisialisasi path file
     */
    private static function init() {
        if (self::$cacheFile === null) {
            self::$cacheFile = __DIR__ . '/cache/doc_types.cache';
        }
        if (self::$jsonFile === null) {
            self::$jsonFile = __DIR__ . '/data/document_types.json';
        }
    }

    /**
     * Mendapatkan daftar tipe dokumen yang FLAT (datar) dan unik.
     * Ideal untuk <select> dropdown filter.
     * * @return array Daftar tipe dokumen, e.g., ['WI', 'Procedure', 'Form', 'Sub Tipe A']
     */
    public static function getFlattenedDocumentTypes() {
        self::init();

        // 1. Cek cache statis (jika sudah di-load di request yang sama)
        if (self::$flatDocTypes !== null) {
            return self::$flatDocTypes;
        }

        // 2. Cek file cache (valid 1 jam)
        //    =======================================================================
        //    ===== PERUBAHAN DI SINI: Cache diset 0 detik (selalu kadaluarsa) =====
        //    =======================================================================
        if (file_exists(self::$cacheFile) && (time() - filemtime(self::$cacheFile) < 0)) { // <-- DIUBAH DARI 3600 MENJADI 0
            $cached = @file_get_contents(self::$cacheFile);
            if ($cached) {
                self::$flatDocTypes = json_decode($cached, true);
                if (is_array(self::$flatDocTypes) && !empty(self::$flatDocTypes)) {
                    return self::$flatDocTypes;
                }
            }
        }

        // 3. Load dari JSON jika cache tidak ada atau kadaluarsa
        $docTypes = [];
        $data = self::loadJsonData(); // Load data terstruktur
        
        if (is_array($data)) {
            // Ubah data terstruktur menjadi flat
            $docTypes = self::flattenDocTypesRecursive($data);
        }

        // 4. Fallback jika JSON gagal di-load
        if (empty($docTypes)) {
            $docTypes = ['WI', 'Procedure', 'Form', 'Monitor Sample', 'MSDS', 'Material Spec', 'ROHS'];
        }

        // 5. Pastikan unik dan simpan
        self::$flatDocTypes = array_values(array_unique($docTypes));

        // 6. Simpan ke file cache untuk request berikutnya
        self::saveCache(self::$flatDocTypes);

        return self::$flatDocTypes;
    }

    /**
     * Mendapatkan data terstruktur (Array) langsung dari JSON.
     * Ideal untuk membangun menu navigasi yang memiliki submenu.
     * * @return array|null Data mentah dari JSON
     */
    public static function getStructuredDocumentTypes() {
        self::init();
        
        if (self::$structuredDocTypes !== null) {
            return self::$structuredDocTypes;
        }
        
        // Cek file cache terpisah untuk data terstruktur
        $structuredCacheFile = __DIR__ . '/cache/doc_types_structured.cache';
        
        // =======================================================================
        // ===== PERUBAHAN DI SINI: Cache diset 0 detik (selalu kadaluarsa) =====
        // =======================================================================
        if (file_exists($structuredCacheFile) && (time() - filemtime($structuredCacheFile) < 0)) { // <-- DIUBAH DARI 3600 MENJADI 0
             $cached = @file_get_contents($structuredCacheFile);
             if ($cached) {
                self::$structuredDocTypes = json_decode($cached, true);
                if (is_array(self::$structuredDocTypes)) {
                    return self::$structuredDocTypes;
                }
             }
        }
        
        self::$structuredDocTypes = self::loadJsonData();
        
        // Simpan data terstruktur ke cache-nya sendiri
        $cacheDir = dirname($structuredCacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($structuredCacheFile, json_encode(self::$structuredDocTypes));
        
        return self::$structuredDocTypes;
    }
    
    /**
     * Menghapus file cache.
     * PANGGIL FUNGSI INI dari halaman admin Anda setelah admin
     * berhasil MENYIMPAN perubahan pada 'document_types.json'.
     */
    public static function clearCache() {
        self::init();
        if (file_exists(self::$cacheFile)) {
            @unlink(self::$cacheFile);
        }
        // Hapus cache terstruktur juga
        $structuredCacheFile = __DIR__ . '/cache/doc_types_structured.cache';
         if (file_exists($structuredCacheFile)) {
            @unlink($structuredCacheFile);
        }
        
        self::$flatDocTypes = null; // Hapus cache statis juga
        self::$structuredDocTypes = null;
    }

    /**
     * Helper untuk memuat dan decode JSON
     */
    private static function loadJsonData() {
        self::init();
        if (!file_exists(self::$jsonFile)) {
            error_log("DocumentConfig Error: " . self::$jsonFile . " not found.");
            return null;
        }
        $content = @file_get_contents(self::$jsonFile);
        if (!$content) {
            error_log("DocumentConfig Error: Failed to read " . self::$jsonFile);
            return null;
        }
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             error_log("DocumentConfig Error: Invalid JSON in " . self::$jsonFile . ". " . json_last_error_msg());
             return null;
        }
        return $data;
    }

    /**
     * Helper rekursif untuk mengubah JSON terstruktur menjadi daftar flat
     */
    private static function flattenDocTypesRecursive($data) {
        $result = [];
        if (!is_array($data)) return $result;
        
        foreach ($data as $item) {
            if (is_string($item)) {
                $result[] = $item;
            } elseif (is_array($item)) {
                // Ambil 'name' dari item utama
                if (isset($item['name'])) {
                    $result[] = $item['name'];
                }
                // Rekursif ke dalam 'submenu' jika ada
                if (isset($item['submenu']) && is_array($item['submenu'])) {
                     $result = array_merge($result, self::flattenDocTypesRecursive($item['submenu']));
                }
            }
        }
        return $result;
    }

    /**
     * Helper untuk menyimpan data ke file cache
     */
    private static function saveCache($data) {
        self::init();
        $cacheDir = dirname(self::$cacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents(self::$cacheFile, json_encode($data));
    }
}
?>