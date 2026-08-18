<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Colovo Workspace & HR') }}</title>
    <!-- Boxicons for premium icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Main Style Sheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Glow backgrounds removed for clean theme -->

    <div class="app-container">
        @auth
            <!-- Sidebar Navigation -->
            <aside class="app-sidebar" id="mainSidebar">
                <!-- Toggle Button -->
                <button id="sidebarToggleBtn" style="position: absolute; top: 35px; right: -14px; width: 28px; height: 28px; border-radius: 50%; background: #ffffff; border: 1px solid #e2e8f0; color: #1e293b; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 1002; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <i class='bx bx-chevron-left' style="font-size: 20px;"></i>
                </button>
                <div class="sidebar-logo" style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; overflow: hidden; height: 50px; width: 100%; max-width: 140px; margin-left: auto; margin-right: auto; background: #ffffff; border-radius: 6px; padding: 2px;">
                    @if(auth()->check())
                        @php
                            $sidebarLogo = asset('image/KP.jpeg');
                            if (isset($company) && $company->logo) {
                                $sidebarLogo = asset('storage/' . $company->logo);
                            } elseif (auth()->user()->company && auth()->user()->company->logo) {
                                $sidebarLogo = asset('storage/' . auth()->user()->company->logo);
                            }
                        @endphp
                        <img src="{{ $sidebarLogo }}" alt="Logo" class="sidebar-brand-img" style="height: 100%; width: 100%; object-fit: contain; transform: scale(1.3); transition: all 300ms ease-in-out;">
                    @endif
                </div>

                <ul class="sidebar-menu">
                    @if(auth()->user()->role === 'superadmin')
                        <!-- Super Admin Navigation links -->
                        <div class="sidebar-section-title">System Level</div>
                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.dashboard') }}"><i class='bx bx-globe'></i><span>Global Dashboard</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.workspaces') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.workspaces') }}"><i class='bx bx-buildings'></i><span>Global Workspaces</span></a>
                        </li>

                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.reports') }}"><i class='bx bxs-report'></i><span>Global Reports</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.announcements.*') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.announcements.index') }}"><i class='bx bx-broadcast'></i><span>Announcements</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.queries.*') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.queries.index') }}"><i class='bx bx-question-mark'></i><span>Queries</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                            <a href="{{ route('superadmin.settings') }}"><i class='bx bx-cog'></i><span>System Settings</span></a>
                        </li>

                    @elseif(auth()->user()->role === 'admin')
                        <!-- Admin Navigation links -->
                        <div class="sidebar-section-title">Workspace / ESP Level</div>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}"><i class='bx bx-category'></i><span>Workspace Dashboard</span></a>
                        </li>

                        <li class="sidebar-menu-item {{ request()->routeIs('admin.projects-monitoring.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.projects-monitoring.index') }}"><i class='bx bx-pie-chart-alt-2'></i><span>Project Monitoring</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.directory') ? 'active' : '' }}">
                            <a href="{{ route('admin.directory') }}"><i class='bx bxs-contact'></i><span>Employee Directory</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.performance') ? 'active' : '' }}">
                            <a href="{{ route('admin.performance') }}"><i class='bx bx-medal'></i><span>Performance Hub</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.promotions') ? 'active' : '' }}">
                            <a href="{{ route('admin.promotions') }}"><i class='bx bx-trending-up'></i><span>Growth & Promotions</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                            <a href="{{ route('admin.reports') }}"><i class='bx bxs-report'></i><span>Workspace Reports</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.daily-reports') ? 'active' : '' }}">
                            <a href="{{ route('admin.daily-reports') }}"><i class='bx bx-notepad'></i><span>Daily Reports</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.leaves') ? 'active' : '' }}">
                            <a href="{{ route('admin.leaves') }}"><i class='bx bx-calendar-event'></i><span>Leave Management</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.announcements.index') }}"><i class='bx bx-broadcast'></i><span>Announcements</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.queries.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.queries.index') }}"><i class='bx bx-question-mark'></i><span>Queries</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.documents.index') }}"><i class='bx bx-file-blank'></i><span>Document Generator</span></a>
                        </li>

                        <li class="sidebar-menu-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings') }}"><i class='bx bx-cog'></i><span>Workspace Settings</span></a>
                        </li>

                    @else
                        <!-- Employee Navigation links -->
                        <div class="sidebar-section-title">Individual Level</div>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('employee.dashboard') }}"><i class='bx bx-user-pin'></i><span>My Dashboard</span></a>
                        </li>
                        <!-- <li class="sidebar-menu-item {{ request()->routeIs('employee.tasks') ? 'active' : '' }}">
                            <a href="{{ route('employee.tasks') }}"><i class='bx bx-list-check'></i><span>My Tasks</span></a>
                        </li> -->
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.my-projects.*') ? 'active' : '' }}">
                            <a href="{{ route('employee.my-projects.index') }}"><i class='bx bx-briefcase-alt-2'></i><span>My Assigned Projects</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
                            <a href="{{ route('employee.attendance') }}"><i class='bx bx-time-five'></i><span>My Attendance</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.payslips.*') ? 'active' : '' }}">
                            <a href="{{ route('employee.payslips.index') }}"><i class='bx bx-receipt'></i><span>My Payslips</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.leave.apply') ? 'active' : '' }}">
                            <a href="{{ route('employee.leave.apply') }}"><i class='bx bx-calendar-event'></i><span>Apply Leave</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.daily-report.create') ? 'active' : '' }}">
                            <a href="{{ route('employee.daily-report.create') }}"><i class='bx bx-file'></i><span>Daily Report</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.performance') ? 'active' : '' }}">
                            <a href="{{ route('employee.performance') }}"><i class='bx bx-medal'></i><span>Performance Hub</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.growth') ? 'active' : '' }}">
                            <a href="{{ route('employee.growth') }}"><i class='bx bx-trending-up'></i><span>Growth & Promotions</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.joining-document*') ? 'active' : '' }}">
                            <a href="{{ route('employee.joining-document') }}"><i class='bx bx-file-blank'></i><span>Joining Document</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.announcements.*') ? 'active' : '' }}">
                            <a href="{{ route('employee.announcements.index') }}"><i class='bx bx-broadcast'></i><span>Announcements</span></a>
                        </li>
                        <li class="sidebar-menu-item {{ request()->routeIs('employee.queries.*') ? 'active' : '' }}">
                            <a href="{{ route('employee.queries.index') }}"><i class='bx bx-question-mark'></i><span>Raise Query</span></a>
                        </li>
                    @endif                </ul>

                <!-- Sidebar User Profile & Logout -->
                <div class="sidebar-user">
                    <div class="user-avatar-placeholder">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0; display:flex;">
                        @csrf
                        <button type="submit" class="logout-btn" style="background:none; border:none; padding:0; cursor:pointer;" title="Logout">
                            <i class='bx bx-log-out'></i>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Sidebar Toggle Button (Fixed Position) -->
            <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle Sidebar">
                <i class='bx bx-menu' id="sidebar-toggle-icon"></i>
            </button>
            
            <!-- Mobile Backdrop -->
            <div id="sidebar-backdrop" class="sidebar-backdrop"></div>
        @endauth

        <!-- Main Workspace Area -->
        <main class="app-main" style="{{ !Auth::check() ? 'margin-left: 0; width: 100%;' : '' }}">
            @if(session()->has('impersonated_by'))
                <div class="impersonate-banner" style="background: var(--primary); color: white; padding: 12px; text-align: center; font-weight: 600; display: flex; justify-content: center; align-items: center; gap: 10px; z-index: 999; margin-bottom: 20px; border-radius: 0 0 16px 16px;">
                    <i class='bx bx-mask' style="font-size: 20px;"></i> You are currently viewing as {{ auth()->user()->name }}
                    <a href="{{ route('superadmin.impersonate.leave') }}" class="btn btn-sm" style="background: white; color: var(--primary); border: none; padding: 4px 12px; margin-left: 15px; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none;">Exit View</a>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <!-- Sidebar Toggle Script -->
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inject topbar-right into app-header if it is missing
            const header = document.querySelector('.app-main > .app-header');
            if (header && !header.querySelector('.topbar-right')) {
                const topbarRightHtml = `
                    <div class="topbar-right">
                        <div class="noti-bell">
                            <i class='bx bx-bell'></i>
                            <span class="noti-bell-badge" style="display: none;">3</span>
                        </div>
                        <div class="topbar-user" style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 14px;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                                <div>{{ auth()->user()->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
                            </div>
                            <i class='bx bx-chevron-down' style="color: var(--text-muted);"></i>
                        </div>
                    </div>
                `;
                header.insertAdjacentHTML('beforeend', topbarRightHtml);
            }

            const sidebar = document.querySelector('.app-sidebar');
            const main = document.querySelector('.app-main');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            // Check local storage for saved state
            const isCollapsed = localStorage.getItem('sidebarState') === 'collapsed';
            
            if (isCollapsed && window.innerWidth >= 1100) {
                sidebar.classList.add('collapsed');
                main.classList.add('collapsed');
                toggleBtn.classList.add('collapsed');
                toggleIcon.classList.replace('bx-menu', 'bx-layout');
            }

            function toggleSidebar() {
                const isMobile = window.innerWidth < 1100;
                
                if (isMobile) {
                    sidebar.classList.toggle('mobile-open');
                    backdrop.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    main.classList.toggle('collapsed');
                    toggleBtn.classList.toggle('collapsed');
                    
                    const nowCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarState', nowCollapsed ? 'collapsed' : 'expanded');
                    
                    if (nowCollapsed) {
                        toggleIcon.classList.replace('bx-menu', 'bx-layout');
                    } else {
                        toggleIcon.classList.replace('bx-layout', 'bx-menu');
                    }
                }
            }

            toggleBtn.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', toggleSidebar);

            // Close all profile dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.profile-dropdown');
                dropdowns.forEach(dropdown => {
                    // Check if click was outside the dropdown and its parent toggle element
                    if (!dropdown.parentElement.contains(event.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

            // Dynamically add profile dropdown to the body to avoid CSS clipping/flex issues
            const topbarRight = document.querySelector('.topbar-right');
            if (topbarRight) {
                const userContainer = topbarRight.lastElementChild;
                if (userContainer && userContainer.tagName === 'DIV') {
                    userContainer.style.cursor = 'pointer';
                    
                    // Create dropdown in body
                    if (!document.getElementById('global-profile-dropdown')) {
                        const dropdownHtml = `
                            <div id="global-profile-dropdown" style="display: none; position: absolute; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 180px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 99999; overflow: hidden;">
                                <a href="{{ auth()->user()->role === 'employee' ? route('employee.profile') : (auth()->user()->role === 'admin' ? route('admin.profile') : '#') }}" style="display: block; padding: 12px 15px; color: #0f172a; text-decoration: none; font-size: 13px; border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                                    <i class='bx bx-user' style="margin-right: 8px; font-size: 16px; vertical-align: middle;"></i> <span style="vertical-align: middle;">My Profile</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0; display: block;">
                                    @csrf
                                    <button type="submit" style="width: 100%; text-align: left; background: transparent; border: none; padding: 12px 15px; color: #dc2626; font-size: 13px; cursor: pointer; display: block;">
                                        <i class='bx bx-log-out' style="margin-right: 8px; font-size: 16px; vertical-align: middle;"></i> <span style="vertical-align: middle;">Logout</span>
                                    </button>
                                </form>
                            </div>
                        `;
                        document.body.insertAdjacentHTML('beforeend', dropdownHtml);
                    }
                    
                    const dropdown = document.getElementById('global-profile-dropdown');
                    
                    userContainer.addEventListener('click', function(e) {
                        e.stopPropagation();
                        
                        // Calculate position
                        const rect = userContainer.getBoundingClientRect();
                        dropdown.style.top = (rect.bottom + window.scrollY + 10) + 'px';
                        dropdown.style.left = (rect.right + window.scrollX - 180) + 'px'; // right-aligned
                        
                        // Toggle display
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    });
                    
                    document.addEventListener('click', function(e) {
                        if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                }
            }
        });
    </script>
    @endauth

    <!-- Floating Helpdesk Widget & Chatbot -->
    @auth
        <div id="chatbot-container" class="chatbot-container">
            <div class="chatbot-header">
                <div>
                    <h3 style="margin: 0; font-size: 16px;">HR Support Bot</h3>
                    <span style="font-size: 11px; opacity: 0.8;">Online - Ask me anything</span>
                </div>
                <button id="chatbot-close" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px; padding: 0 5px;">&times;</button>
            </div>
            <div id="chatbot-messages" class="chatbot-messages">
                <div class="chat-message bot">
                    Hello! I'm the HR Support Bot. How can I assist you today? You can ask me about leave policies, salary, holidays, or tasks.
                </div>
            </div>
            <div class="chatbot-input-area">
                <input type="text" id="chatbot-input" placeholder="Type your message..." autocomplete="off">
                <button id="chatbot-send"><i class='bx bx-send'></i></button>
            </div>
        </div>

        <button id="chatbot-toggle" class="floating-helpdesk" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4); z-index: 1000; cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 15px 35px rgba(220, 38, 38, 0.5)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 10px 25px rgba(220, 38, 38, 0.4)';">
            <i class='bx bx-support'></i>
        </button>

        <!-- Chatbot Logic -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatbotToggle = document.getElementById('chatbot-toggle');
                const chatbotContainer = document.getElementById('chatbot-container');
                const chatbotClose = document.getElementById('chatbot-close');
                const chatbotInput = document.getElementById('chatbot-input');
                const chatbotSend = document.getElementById('chatbot-send');
                const chatbotMessages = document.getElementById('chatbot-messages');

                if (chatbotToggle) {
                    chatbotToggle.addEventListener('click', () => {
                        chatbotContainer.classList.toggle('show');
                        if (chatbotContainer.classList.contains('show')) {
                            chatbotInput.focus();
                        }
                    });
                    
                    chatbotClose.addEventListener('click', () => {
                        chatbotContainer.classList.remove('show');
                    });

                    const appendMessage = (text, sender) => {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = `chat-message ${sender}`;
                        msgDiv.textContent = text;
                        chatbotMessages.appendChild(msgDiv);
                        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                    };

                    const sendMessage = () => {
                        const text = chatbotInput.value.trim();
                        if (!text) return;
                        
                        appendMessage(text, 'user');
                        chatbotInput.value = '';

                        // Simple Frontend Bot Logic
                        setTimeout(() => {
                            let lowerText = text.toLowerCase();
                            let botReply = "I'm sorry, I don't understand that. You can ask me about leaves, salary, holidays, or tasks.";
                            
                            if (lowerText.includes('leave') || lowerText.includes('sick') || lowerText.includes('vacation')) {
                                botReply = "To apply for a leave, go to the 'Apply Leave' section from the dashboard. You can choose from sick, casual, or annual leaves.";
                            } else if (lowerText.includes('salary') || lowerText.includes('pay')) {
                                botReply = "Your salary details and payslips can be accessed through the Finance portal. If you have a discrepancy, please contact HR directly.";
                            } else if (lowerText.includes('holiday')) {
                                botReply = "The next upcoming public holiday is on the 15th of next month. You can view the full holiday calendar in the company policies section.";
                            } else if (lowerText.includes('task')) {
                                botReply = "You can view your assigned tasks in the 'My Tasks' page. You can also request new tasks from your manager there.";
                            } else if (lowerText.includes('hello') || lowerText.includes('hi')) {
                                botReply = "Hello there! How can I help you today?";
                            }

                            appendMessage(botReply, 'bot');
                        }, 600);
                    };

                    chatbotSend.addEventListener('click', sendMessage);
                    chatbotInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });
                }
            });
        </script>
    @endauth

    <!-- Clock script for clock-in widget -->
    @if(Auth::check() && auth()->user()->role === 'employee')
    <script>
        function updateTime() {
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
    @endif
    
    <!-- Notification Bell Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notiBells = document.querySelectorAll('.noti-bell');
            if (notiBells.length > 0) {
                // Use relative URL to avoid CORS issues if APP_URL is misconfigured
                fetch('/notifications', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if(!data || !data.notifications) return;
                    
                    const dropdownHtml = `
                        <div id="global-notifications-dropdown" style="display: none; position: absolute; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; width: 320px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 99999; overflow: hidden;">
                            <div style="padding: 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #0f172a;">Notifications</h4>
                                ${(data.unreadCount || 0) > 0 ? `<button id="mark-read-btn" style="background: none; border: none; color: #3b82f6; font-size: 12px; cursor: pointer; padding: 0;">Mark all as read</button>` : ''}
                            </div>
                            <div style="max-height: 350px; overflow-y: auto;">
                                ${(data.notifications || []).length === 0 ? `<div style="padding: 20px; text-align: center; color: #64748b; font-size: 13px;">No notifications yet.</div>` : 
                                  data.notifications.map(notif => {
                                    const notifData = notif.data || {};
                                    return `
                                    <a href="${notifData.url || '#'}" style="display: block; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #f1f5f9; background: ${notif.read_at ? '#ffffff' : '#f8fafc'}; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${notif.read_at ? '#ffffff' : '#f8fafc'}'">
                                        <div style="font-size: 13px; font-weight: 600; color: #0f172a;">${notifData.title || 'Notification'}</div>
                                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">${notifData.message || ''}</div>
                                        <div style="font-size: 10px; color: #94a3b8; margin-top: 6px;">${new Date(notif.created_at).toLocaleString()}</div>
                                    </a>
                                    `
                                  }).join('')}
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', dropdownHtml);
                    const dropdown = document.getElementById('global-notifications-dropdown');
                    
                    notiBells.forEach(bell => {
                        bell.style.cursor = 'pointer';
                        
                        const badge = bell.querySelector('.noti-bell-badge');
                        if (badge) {
                            if (data.unreadCount > 0) {
                                badge.textContent = data.unreadCount;
                                badge.style.display = 'flex';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                        
                        bell.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const rect = bell.getBoundingClientRect();
                            dropdown.style.top = (rect.bottom + window.scrollY + 10) + 'px';
                            dropdown.style.left = (rect.right + window.scrollX - 320) + 'px';
                            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                        });
                    });

                    document.addEventListener('click', function(e) {
                        if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                    
                    const markReadBtn = document.getElementById('mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            fetch('{{ route('notifications.markAllAsRead') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            }).then(() => location.reload());
                        });
                    }
                });
            }
            
            // Sidebar Toggle Logic
            const sidebarBtn = document.getElementById('sidebarToggleBtn');
            const mainSidebar = document.getElementById('mainSidebar');
            const appMain = document.querySelector('.app-main');
            
            if (sidebarBtn && mainSidebar && appMain) {
                // Check local storage for previous state
                const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
                if (isCollapsed) {
                    mainSidebar.classList.add('collapsed');
                    appMain.classList.add('collapsed');
                    sidebarBtn.querySelector('i').classList.replace('bx-chevron-left', 'bx-chevron-right');
                }
                
                sidebarBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    mainSidebar.classList.toggle('collapsed');
                    appMain.classList.toggle('collapsed');
                    
                    const icon = sidebarBtn.querySelector('i');
                    if (mainSidebar.classList.contains('collapsed')) {
                        icon.classList.replace('bx-chevron-left', 'bx-chevron-right');
                        localStorage.setItem('sidebar_collapsed', 'true');
                    } else {
                        icon.classList.replace('bx-chevron-right', 'bx-chevron-left');
                        localStorage.setItem('sidebar_collapsed', 'false');
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
