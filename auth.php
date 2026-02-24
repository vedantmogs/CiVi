
Auth.php
<?php
// CiVi Authentication Module

include 'config.php';

class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // User registration
    public function register($username, $email, $password, $user_type) {
        $username = sanitize($username);
        $email = sanitize($email);
        
        // Validate email
        if (!validate_email($email)) {
            return array('success' => false, 'message' => 'Invalid email format');
        }
        
        // Check if user already exists
        $check_query = "SELECT id FROM users WHERE email = ? OR username = ?";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return array('success' => false, 'message' => 'User already exists');
        }
        
        // Hash password
        $password_hash = hash_password($password);
        
        // Insert new user
        $insert_query = "INSERT INTO users (username, email, password_hash, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insert_query);
        $stmt->bind_param("ssss", $username, $email, $password_hash, $user_type);
        
        if ($stmt->execute()) {
            return array('success' => true, 'message' => 'Registration successful', 'user_id' => $this->conn->insert_id);
        } else {
            return array('success' => false, 'message' => 'Registration failed');
        }
    }
    
    // User login
    public function login($email, $password) {
        $email = sanitize($email);
        
        // Fetch user
        $query = "SELECT id, password_hash, user_type FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return array('success' => false, 'message' => 'User not found');
        }
        
        $user = $result->fetch_assoc();
        
        // Verify password
        if (verify_password($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            return array('success' => true, 'message' => 'Login successful', 'user_type' => $user['user_type']);
        } else {
            return array('success' => false, 'message' => 'Invalid password');
        }
    }
    
    // User logout
    public function logout() {
        session_destroy();
        return array('success' => true, 'message' => 'Logged out successfully');
    }
}

?>
