<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

requireLogin();
requirePermission('view_documents');

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Allowed file types and max size
$allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
$max_file_size = 10 * 1024 * 1024; // 10MB
$upload_dir = 'uploads/documents/';

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    try {
        $file = $_FILES['document_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }
        
        $file_size = filesize($file['tmp_name']);
        if ($file_size > $max_file_size) {
            throw new Exception('File size exceeds maximum allowed size (10MB)');
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowed_types));
        }
        
        // Create unique filename
        $unique_name = uniqid('doc_') . '.' . $file_ext;
        $file_path = $upload_dir . $unique_name;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Save to database
        $db = new Database();
        $query = "INSERT INTO documents (title, description, file_path, file_name, file_size, file_type, upload_by, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->conn->prepare($query);
        $stmt->execute([
            $_POST['document_title'] ?? 'Untitled Document',
            $_POST['document_description'] ?? '',
            $file_path,
            $file['name'],
            $file_size,
            $file_ext,
            $user_id
        ]);
        
        logActivity($user_id, 'UPLOAD_DOCUMENT', 'documents', $db->conn->lastInsertId(), 
                   'Uploaded document: ' . $file['name']);
        
        $success_msg = 'Document uploaded successfully!';
    } catch (Exception $e) {
        $error_msg = 'Upload error: ' . $e->getMessage();
    }
}

// Handle document deletion
if (isset($_GET['delete']) && isset($_GET['token'])) {
    // Simple token validation (should use CSRF token in production)
    if (!hasPermission('delete_document')) {
        $error_msg = 'You do not have permission to delete documents.';
    } else {
        try {
            $db = new Database();
            $query = "SELECT * FROM documents WHERE id = ? AND upload_by = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->execute([$_GET['delete'], $user_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doc) {
            // Delete file
            if (file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            
            // Delete from database
            $query = "DELETE FROM documents WHERE id = ? AND upload_by = ?";
            $stmt = $db->conn->prepare($query);
            $stmt->execute([$_GET['delete'], $user_id]);
            
            logActivity($user_id, 'DELETE_DOCUMENT', 'documents', $_GET['delete'], 
                       'Deleted document: ' . $doc['file_name']);
            
            $success_msg = 'Document deleted successfully!';
        }
    } catch (PDOException $e) {
        $error_msg = 'Error deleting document: ' . $e->getMessage();
    }
    }
}

// Get user's role to determine visibility
$user = getCurrentUser();
$is_admin = hasPermission('view_all_documents');

// Get documents
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$documents = [];
$total_docs = 0;

try {
    $db = new Database();
    
    // Build query based on user role
    if ($is_admin) {
        // Admins see all documents
        $query = "SELECT COUNT(*) as total FROM documents";
        $stmt = $db->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_docs = $result['total'] ?? 0;
        
        $query = "SELECT d.*, u.full_name FROM documents d 
                  LEFT JOIN users u ON d.upload_by = u.user_id
                  ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $db->conn->prepare($query);
        $stmt->execute([$per_page, $offset]);
    } else {
        // Regular users see only their documents
        $query = "SELECT COUNT(*) as total FROM documents WHERE upload_by = ?";
        $stmt = $db->conn->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_docs = $result['total'] ?? 0;
        
        $query = "SELECT * FROM documents WHERE upload_by = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $db->conn->prepare($query);
        $stmt->execute([$user_id, $per_page, $offset]);
    }
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = 'Failed to load documents: ' . $e->getMessage();
}

$total_pages = ceil($total_docs / $per_page);

// Get file icon based on type
function getFileIcon($file_type) {
    $type = strtolower($file_type);
    return match($type) {
        'pdf' => '📄',
        'doc', 'docx' => '📝',
        'xls', 'xlsx' => '📊',
        'ppt', 'pptx' => '📽️',
        'txt' => '📃',
        'jpg', 'jpeg', 'png', 'gif' => '🖼️',
        default => '📎'
    };
}

// Format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - Shebamiles EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .documents-container {
            padding: 20px 0;
        }

        .upload-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .upload-section h3 {
            color: #FF6B35;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .upload-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: block;
            padding: 40px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 107, 53, 0.05) 100%);
            border: 2px dashed #FF6B35;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #FF6B35;
        }

        .file-input-label:hover {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.15) 0%, rgba(255, 107, 53, 0.1) 100%);
        }

        .file-input-wrapper input[type=file]:focus + .file-input-label {
            border-color: #e55a25;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2) 0%, rgba(255, 107, 53, 0.15) 100%);
        }

        .file-name-display {
            margin-top: 10px;
            font-size: 13px;
            color: #666;
        }

        .upload-button {
            grid-column: 1 / -1;
            padding: 12px 24px;
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .upload-button:hover {
            background-color: #e55a25;
        }

        .upload-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .document-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .document-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .document-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px);
        }

        .document-icon {
            font-size: 48px;
            margin-bottom: 15px;
            text-align: center;
        }

        .document-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            word-break: break-word;
            font-size: 14px;
        }

        .document-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }

        .document-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .document-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .document-btn-download {
            background-color: #FF6B35;
            color: white;
        }

        .document-btn-download:hover {
            background-color: #e55a25;
        }

        .document-btn-delete {
            background-color: #f0f0f0;
            color: #dc3545;
        }

        .document-btn-delete:hover {
            background-color: #e0e0e0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #666;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #FF6B35;
            color: white;
            border-color: #FF6B35;
        }

        .pagination .current {
            background-color: #FF6B35;
            color: white;
            border-color: #FF6B35;
        }

        @media (max-width: 768px) {
            .upload-form {
                grid-template-columns: 1fr;
            }

            .documents-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .upload-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/badge.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>📂 Document Management</h1>
            <p>Upload and manage your important documents</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="documents-container">
            <!-- Upload Section -->
            <div class="upload-section">
                <h3>📤 Upload New Document</h3>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="document_title">Document Title</label>
                        <input type="text" id="document_title" name="document_title" 
                               placeholder="Enter document title" required>
                    </div>

                    <div class="form-group">
                        <label for="document_description">Description</label>
                        <input type="text" id="document_description" name="document_description" 
                               placeholder="Optional description">
                    </div>

                    <div class="form-group form-group-full">
                        <label>Select File</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="document_file" name="document_file" 
                                   onchange="updateFileName(this)" required>
                            <label for="document_file" class="file-input-label">
                                📁 Click to select or drag & drop file here
                            </label>
                        </div>
                        <div class="file-name-display" id="file-name-display"></div>
                        <div class="document-info">
                            Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, PNG, GIF (Max 10MB)
                        </div>
                    </div>

                    <button type="submit" class="upload-button" onclick="this.disabled=true; this.textContent='Uploading...'; setTimeout(() => { this.disabled=false; this.textContent='📤 Upload Document'; }, 3000);">
                        📤 Upload Document
                    </button>
                </form>
            </div>

            <!-- Documents List -->
            <?php if (empty($documents)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-title">No Documents Yet</div>
                    <p>Start by uploading your first document using the form above.</p>
                </div>
            <?php else: ?>
                <div class="documents-grid">
                    <?php foreach ($documents as $doc): 
                        $icon = getFileIcon($doc['file_type']);
                        $size = formatFileSize($doc['file_size']);
                        $date = date('M d, Y H:i', strtotime($doc['created_at']));
                    ?>
                        <div class="document-card">
                            <div class="document-icon"><?php echo $icon; ?></div>
                            <div class="document-title"><?php echo htmlspecialchars($doc['title']); ?></div>
                            <div class="document-meta">
                                <div><?php echo htmlspecialchars($doc['file_name']); ?></div>
                                <div><?php echo $size; ?></div>
                                <div><?php echo $date; ?></div>
                                <?php if ($is_admin && isset($doc['full_name'])): ?>
                                    <div>By: <?php echo htmlspecialchars($doc['full_name']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="document-actions">
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                   download class="document-btn document-btn-download">
                                    ⬇️ Download
                                </a>
                                <?php if ($doc['upload_by'] === $user_id || $is_admin): ?>
                                    <a href="?delete=<?php echo $doc['id']; ?>&token=1" 
                                       onclick="return confirm('Delete this document?');"
                                       class="document-btn document-btn-delete">
                                        🗑️ Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1">« First</a>
                            <a href="?page=<?php echo $page - 1; ?>">‹ Previous</a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1) echo '<span>...</span>';
                        
                        for ($i = $start; $i <= $end; $i++) {
                            if ($i === $page) {
                                echo '<span class="current">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . '">' . $i . '</a>';
                            }
                        }
                        
                        if ($end < $total_pages) echo '<span>...</span>';
                        ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">Next ›</a>
                            <a href="?page=<?php echo $total_pages; ?>">Last »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const display = document.getElementById('file-name-display');
            if (input.files && input.files[0]) {
                display.textContent = '✓ Selected: ' + input.files[0].name;
            }
        }

        // Drag and drop support
        const fileInput = document.getElementById('document_file');
        const dropZone = document.querySelector('.file-input-label');

        if (dropZone) {
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.backgroundColor = 'rgba(255, 107, 53, 0.2)';
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.style.backgroundColor = '';
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.style.backgroundColor = '';
                fileInput.files = e.dataTransfer.files;
                updateFileName(fileInput);
            });
        }
    </script>
</body>
</html>
