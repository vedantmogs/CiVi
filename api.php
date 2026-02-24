
<?php
// CiVi API Endpoints

include 'config.php';
include 'auth.php';
include 'projects.php';

header('Content-Type: application/json');

// Get request method and action
$request_method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';

$auth = new Auth($conn);
$projects = new Projects($conn);

// API Routes
switch ($action) {
    // Authentication endpoints
    case 'register':
        if ($request_method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $auth->register(
                $data['username'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['user_type'] ?? 'customer'
            );
            echo json_encode($result);
        }
        break;
    
    case 'login':
        if ($request_method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $auth->login(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );
            echo json_encode($result);
        }
        break;
    
    case 'logout':
        $result = $auth->logout();
        echo json_encode($result);
        break;
    
    // Project endpoints
    case 'get_projects':
        $result = $projects->get_all_projects();
        echo json_encode(array('success' => true, 'data' => $result));
        break;
    
    case 'get_project':
        $project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $result = $projects->get_project($project_id);
        if ($result) {
            echo json_encode(array('success' => true, 'data' => $result));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Project not found'));
        }
        break;
    
    case 'create_project':
        if ($request_method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $projects->create_project(
                $data['customer_id'] ?? 0,
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['category'] ?? '',
                $data['budget'] ?? 0,
                $data['start_date'] ?? '',
                $data['end_date'] ?? ''
            );
            echo json_encode($result);
        }
        break;
    
    default:
        echo json_encode(array('success' => false, 'message' => 'Invalid action'));
        break;
}

?>
