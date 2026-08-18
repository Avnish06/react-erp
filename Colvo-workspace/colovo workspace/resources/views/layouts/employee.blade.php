<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Workspace Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --brand-color: #2563eb;
            --bg-color: #f8fafc;
        }

        [data-bs-theme="dark"] {
            --bg-color: #0f172a;
            --bs-body-bg: #0f172a;
            --bs-body-color: #f8fafc;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bs-body-bg);
            border-right: 1px solid var(--bs-border-color);
            z-index: 1000;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--brand-color);
            border-bottom: 1px solid var(--bs-border-color);
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
            list-style: none;
            margin: 0;
        }

        .menu-item {
            padding: 0.5rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 8px;
            color: var(--bs-body-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .menu-item:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--brand-color);
        }

        .menu-item.active {
            background: var(--brand-color);
            color: white;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-area {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--bs-border-color);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--bs-gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--bs-gray-600);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class='bx bx-hive fs-4 me-2'></i> Workspace
        </div>
        
        <ul class="sidebar-menu">
            <li class="px-4 text-uppercase text-muted fw-bold" style="font-size: 0.75rem; margin-top: 1rem; margin-bottom: 0.5rem;">Main Navigation</li>
            <li>
                <a href="{{ route('employee.dashboard') }}" class="menu-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <i class='bx bx-grid-alt fs-5'></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('employee.tasks') }}" class="menu-item {{ request()->routeIs('employee.tasks') ? 'active' : '' }}">
                    <i class='bx bx-task fs-5'></i> My Tasks
                </a>
            </li>
            <li>
                <a href="{{ route('employee.attendance') }}" class="menu-item {{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
                    <i class='bx bx-time-five fs-5'></i> My Attendance
                </a>
            </li>
            <li>
                <a href="{{ route('employee.performance') }}" class="menu-item {{ request()->routeIs('employee.performance') ? 'active' : '' }}">
                    <i class='bx bx-line-chart fs-5'></i> My Performance
                </a>
            </li>
            <li>
                <a href="{{ route('employee.growth') }}" class="menu-item {{ request()->routeIs('employee.growth') ? 'active' : '' }}">
                    <i class='bx bx-trending-up fs-5'></i> My Growth
                </a>
            </li>
            <li>
                <a href="{{ route('employee.joining-document') }}" class="menu-item {{ request()->routeIs('employee.joining-document*') ? 'active' : '' }}">
                    <i class='bx bx-file-blank fs-5'></i> Joining Document
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebarToggle">
                    <i class='bx bx-menu fs-4'></i>
                </button>
                <div class="input-group d-none d-md-flex" style="width: 300px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class='bx bx-search'></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Search...">
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon btn-light rounded-circle" id="themeToggle">
                    <i class='bx bx-moon fs-5'></i>
                </button>
                
                <div class="position-relative">
                    <button class="btn btn-icon btn-light rounded-circle">
                        <i class='bx bx-bell fs-5'></i>
                    </button>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        3
                    </span>
                </div>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar me-2">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="d-none d-md-block text-body">
                            <div class="fw-bold" style="font-size: 0.875rem;">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->role }}</div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item" href="{{ route('employee.profile') }}"><i class='bx bx-user me-2'></i> View Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class='bx bx-cog me-2'></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class='bx bx-log-out me-2'></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Theme Toggle
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            this.innerHTML = newTheme === 'dark' ? "<i class='bx bx-sun fs-5'></i>" : "<i class='bx bx-moon fs-5'></i>";
        });
    </script>
    @stack('scripts')
</body>
</html>
