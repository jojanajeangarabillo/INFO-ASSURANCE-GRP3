<?php
// config/site_settings.php
class SiteSettings {
    private $conn;
    private $settings = [];
    private static $instance = null;
    
    private function __construct($conn) {
        $this->conn = $conn;
        $this->loadAllSettings();
    }
    
    public static function getInstance($conn) {
        if (self::$instance === null) {
            self::$instance = new self($conn);
        }
        return self::$instance;
    }
    
    private function loadAllSettings() {
        $sql = "SELECT setting_key, setting_value, setting_type FROM site_settings";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['setting_key'];
                $value = $row['setting_value'];
                $type = $row['setting_type'];
                
                switch ($type) {
                    case 'json':
                    case 'array':
                        $this->settings[$key] = json_decode($value, true);
                        break;
                    case 'image':
                        $this->settings[$key] = !empty($value) ? $value : 'default-placeholder.png';
                        break;
                    default:
                        $this->settings[$key] = $value;
                }
            }
        }
    }
    
    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    public function getAll() {
        return $this->settings;
    }
    
    public function update($key, $value, $type = 'text') {
        $stmt = $this->conn->prepare("UPDATE site_settings SET setting_value = ?, setting_type = ?, updated_at = NOW() WHERE setting_key = ?");
        $stmt->bind_param("sss", $value, $type, $key);
        return $stmt->execute();
    }
    
    public function updateMultiple($settingsArray) {
        $success = true;
        foreach ($settingsArray as $key => $value) {
            $stmt = $this->conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            if (!$stmt->execute()) {
                $success = false;
            }
        }
        return $success;
    }
    
    public function getCategories() {
        $categories = $this->get('categories', ['All', 'Women', 'Men']);
        return is_array($categories) ? $categories : ['All', 'Women', 'Men'];
    }
    
    public function getFeatures() {
        $features = $this->get('features', []);
        return is_array($features) ? $features : [];
    }
    
    public function getWhyChooseUs() {
        $reasons = $this->get('why_choose_us', []);
        return is_array($reasons) ? $reasons : [];
    }
}
?>