<?php
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();

if ($conn === null) {
    header('Location: login.php?db=missing');
    exit();
}

// Get statistics
try {
    // For employees, show only their own data
    // For admins, show company-wide statistics
    
    if (hasPermission('view_analytics')) {
        // ADMIN VIEW - Company-wide statistics
        // Total employees
        $stmt = $conn->query("SELECT COUNT(*) as total FROM employees");
        $totalEmployees = $stmt->fetch()['total'];
        
        // Total departments
        $stmt = $conn->query("SELECT COUNT(*) as total FROM departments");
        $totalDepartments = $stmt->fetch()['total'];
        
        // Pending leave requests
        $stmt = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
        $pendingLeaves = $stmt->fetch()['total'];
        
        // Present today
        $stmt = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'present'");
        $presentToday = $stmt->fetch()['total'];
        
        // Absent today
        $stmt = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'absent'");
        $absentToday = $stmt->fetch()['total'];
        
        // Leave today
        $stmt = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'leave'");
        $leaveToday = $stmt->fetch()['total'];
        
        // Recent employees
        $stmt = $conn->query("SELECT e.*, d.department_name 
                              FROM employees e 
                              LEFT JOIN departments d ON e.department_id = d.department_id 
                              ORDER BY e.created_at DESC 
                              LIMIT 10");
        $recentEmployees = $stmt->fetchAll();
        
        // Department distribution
        $stmt = $conn->query("SELECT d.department_name, COUNT(e.employee_id) as count 
                              FROM departments d 
                              LEFT JOIN employees e ON d.department_id = e.department_id 
                              GROUP BY d.department_id");
        $departmentStats = $stmt->fetchAll();
        
        // Attendance this month (last 30 days)
        $stmt = $conn->query("SELECT DATE(date) as attendance_date, 
                              CASE WHEN status = 'present' THEN COUNT(*) ELSE 0 END as present_count,
                              CASE WHEN status = 'absent' THEN COUNT(*) ELSE 0 END as absent_count,
                              CASE WHEN status = 'leave' THEN COUNT(*) ELSE 0 END as leave_count
                              FROM attendance 
                              WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                              GROUP BY DATE(date), status
                              ORDER BY attendance_date DESC");
        $attendanceData = $stmt->fetchAll();
        
        // Leave statistics
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM leave_requests GROUP BY status");
        $leaveStats = $stmt->fetchAll();
        
        // Weekly attendance chart data
        $stmt = $conn->query("SELECT DATE(date) as attendance_date, COUNT(*) as total FROM attendance 
                              WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                              GROUP BY DATE(date) ORDER BY attendance_date");
        $weeklyAttendance = $stmt->fetchAll();
        
    } else {
        // EMPLOYEE VIEW - Personal data only
        $user = getCurrentUser();
        $employeeId = $user['employee_id'];
        
        // Total employees (for reference)
        $stmt = $conn->query("SELECT COUNT(*) as total FROM employees");
        $totalEmployees = $stmt->fetch()['total'];
        
        // Total departments (for reference)
        $stmt = $conn->query("SELECT COUNT(*) as total FROM departments");
        $totalDepartments = $stmt->fetch()['total'];
        
        // My pending leave requests
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending' AND employee_id = ?");
        $stmt->execute([$employeeId]);
        $pendingLeaves = $stmt->fetch()['total'];
        
        // My attendance today
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND employee_id = ? AND status = 'present'");
        $stmt->execute([$employeeId]);
        $presentToday = $stmt->fetch()['total'];
        
        // My absence today
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND employee_id = ? AND status = 'absent'");
        $stmt->execute([$employeeId]);
        $absentToday = $stmt->fetch()['total'];
        
        // My leave today
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND employee_id = ? AND status = 'leave'");
        $stmt->execute([$employeeId]);
        $leaveToday = $stmt->fetch()['total'];
        
        // Recent employees (empty for regular employees)
        $recentEmployees = [];
        
        // Department distribution (empty for regular employees)
        $departmentStats = [];
        
        // My attendance this month
        $stmt = $conn->prepare("SELECT DATE(date) as attendance_date, 
                              CASE WHEN status = 'present' THEN COUNT(*) ELSE 0 END as present_count,
                              CASE WHEN status = 'absent' THEN COUNT(*) ELSE 0 END as absent_count,
                              CASE WHEN status = 'leave' THEN COUNT(*) ELSE 0 END as leave_count
                              FROM attendance 
                              WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND employee_id = ?
                              GROUP BY DATE(date), status
                              ORDER BY attendance_date DESC");
        $stmt->execute([$employeeId]);
        $attendanceData = $stmt->fetchAll();
        
        // My leave statistics
        $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM leave_requests WHERE employee_id = ? GROUP BY status");
        $stmt->execute([$employeeId]);
        $leaveStats = $stmt->fetchAll();
        
        // My weekly attendance
        $stmt = $conn->prepare("SELECT DATE(date) as attendance_date, COUNT(*) as total FROM attendance 
                              WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND employee_id = ?
                              GROUP BY DATE(date) ORDER BY attendance_date");
        $stmt->execute([$employeeId]);
        $weeklyAttendance = $stmt->fetchAll();
    }
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

$user = getCurrentUser();

// Prepare chart data
$deptNames = [];
$deptCounts = [];
foreach ($departmentStats as $dept) {
    $deptNames[] = $dept['department_name'];
    $deptCounts[] = $dept['count'];
}

$leaveStatusLabels = [];
$leaveStatusCounts = [];
$leaveStatusColors = ['#4CAF50', '#FFC107', '#F44336'];
foreach ($leaveStats as $leave) {
    $leaveStatusLabels[] = ucfirst($leave['status']);
    $leaveStatusCounts[] = $leave['count'];
}

$weekDates = [];
$weekPresent = [];
foreach ($weeklyAttendance as $att) {
    $weekDates[] = date('M d', strtotime($att['attendance_date']));
    $weekPresent[] = $att['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Shebamiles EMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .chart-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-card h3 {
            margin: 0 0 15px 0;
            color: #FF6B35;
            font-size: 16px;
        }

        .chart-canvas {
            max-height: 300px;
        }

        .attendance-today {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .today-stat {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 107, 53, 0.05) 100%);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #FF6B35;
        }

        .today-stat.present {
            border-left-color: #4CAF50;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(76, 175, 80, 0.05) 100%);
        }

        .today-stat.absent {
            border-left-color: #F44336;
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.1) 0%, rgba(244, 67, 54, 0.05) 100%);
        }

        .today-stat.leave {
            border-left-color: #FFC107;
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
        }

        .today-stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .today-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }

            .chart-canvas {
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="topbar">
                <h2>📊 Dashboard</h2>
                <div class="topbar-actions">
                    <?php include 'includes/notification-header.php'; ?>
                    <a href="php/logout.php" class="btn btn-sm btn-outline-orange">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Total Employees</h3>
                        <div class="stat-card-value"><?php echo $totalEmployees; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Departments</h3>
                        <div class="stat-card-value"><?php echo $totalDepartments; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3>Present Today</h3>
                        <div class="stat-card-value"><?php echo $presentToday; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3>Pending Leaves</h3>
                        <div class="stat-card-value"><?php echo $pendingLeaves; ?></div>
                    </div>
                </div>

                <!-- Attendance Today Status -->
                <div class="attendance-today">
                    <div class="today-stat present">
                        <div class="today-stat-label">✓ Present</div>
                        <div class="today-stat-value"><?php echo $presentToday; ?></div>
                    </div>
                    <div class="today-stat absent">
                        <div class="today-stat-label">✗ Absent</div>
                        <div class="today-stat-value"><?php echo $absentToday; ?></div>
                    </div>
                    <div class="today-stat leave">
                        <div class="today-stat-label">🔔 On Leave</div>
                        <div class="today-stat-value"><?php echo $leaveToday; ?></div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <!-- Department Distribution Chart -->
                    <div class="chart-card">
                        <h3>📊 Department Distribution</h3>
                        <canvas id="departmentChart" class="chart-canvas"></canvas>
                    </div>

                    <!-- Leave Requests Chart -->
                    <div class="chart-card">
                        <h3>📅 Leave Requests Status</h3>
                        <canvas id="leaveChart" class="chart-canvas"></canvas>
                    </div>

                    <!-- Weekly Attendance Chart -->
                    <div class="chart-card">
                        <h3>📈 Weekly Attendance Trend</h3>
                        <canvas id="attendanceChart" class="chart-canvas"></canvas>
                    </div>

                    <!-- Attendance Distribution Chart -->
                    <div class="chart-card">
                        <h3>🎯 Today's Attendance Status</h3>
                        <canvas id="todayChart" class="chart-canvas"></canvas>
                    </div>
                </div>
                
                <!-- Recent Employees -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Recent Employees</h3>
                        <a href="employees.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Employee Code</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Employment Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentEmployees)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <h3>No Employees Found</h3>
                                            <p>Start by adding your first employee</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentEmployees as $emp): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($emp['employee_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['position'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo ucfirst(str_replace('-', ' ', $emp['employment_type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="employee-details.php?id=<?php echo $emp['employee_id']; ?>" class="btn-icon" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Department Distribution Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Department Distribution</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employee Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departmentStats)): ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i class="fas fa-building"></i>
                                            <h3>No Departments Found</h3>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departmentStats as $dept): ?>
                                <?php 
                                    $percentage = $totalEmployees > 0 ? round(($dept['count'] / $totalEmployees) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dept['department_name']); ?></strong></td>
                                    <td><?php echo $dept['count']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="flex: 1; background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                                                <div style="width: <?php echo $percentage; ?>%; height: 100%; background: linear-gradient(135deg, var(--primary-orange), var(--primary-orange-dark));"></div>
                                            </div>
                                            <span style="min-width: 50px; text-align: right; font-weight: 600;"><?php echo $percentage; ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/badge.php'; ?>

    <script>
        // Chart configuration
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: "'DM Sans', sans-serif",
                            size: 12
                        },
                        color: '#666',
                        padding: 15
                    }
                }
            }
        };

        // Department Distribution Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($deptNames); ?>,
                datasets: [{
                    data: <?php echo json_encode($deptCounts); ?>,
                    backgroundColor: [
                        '#FF6B35',
                        '#FFB535',
                        '#4CAF50',
                        '#2196F3',
                        '#9C27B0',
                        '#E91E63'
                    ],
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' employees';
                            }
                        }
                    }
                }
            }
        });

        // Leave Requests Chart
        const leaveCtx = document.getElementById('leaveChart').getContext('2d');
        new Chart(leaveCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($leaveStatusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($leaveStatusCounts); ?>,
                    backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: chartConfig
        });

        // Weekly Attendance Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($weekDates); ?>,
                datasets: [{
                    label: 'Present',
                    data: <?php echo json_encode($weekPresent); ?>,
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#FF6B35',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...chartConfig,
                plugins: {
                    ...chartConfig.plugins,
                    filler: {
                        propagate: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: "'DM Sans', sans-serif"
                            },
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "'DM Sans', sans-serif"
                            },
                            color: '#666'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Today's Attendance Status Chart
        const todayCtx = document.getElementById('todayChart').getContext('2d');
        new Chart(todayCtx, {
            type: 'bar',
            data: {
                labels: ['Present', 'Absent', 'On Leave'],
                datasets: [{
                    label: 'Count',
                    data: [<?php echo $presentToday; ?>, <?php echo $absentToday; ?>, <?php echo $leaveToday; ?>],
                    backgroundColor: ['#4CAF50', '#F44336', '#FFC107'],
                    borderColor: ['#45a049', '#da190b', '#ffa500'],
                    borderWidth: 2
                }]
            },
            options: {
                ...chartConfig,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: "'DM Sans', sans-serif"
                            },
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                family: "'DM Sans', sans-serif"
                            },
                            color: '#666'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
