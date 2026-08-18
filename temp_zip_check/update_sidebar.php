<?php
$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

$superadmin_link = '
                          <li class="sidebar-menu-item {{ request()->routeIs(\'superadmin.dashboard\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'superadmin.dashboard\') }}"><i class=\'bx bx-home-alt\'></i><span>Dashboard</span></a>
                          </li>
                          <li class="sidebar-menu-item {{ request()->routeIs(\'superadmin.profile-status\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'superadmin.profile-status\') }}"><i class=\'bx bx-user-check\'></i><span>Profile Status</span></a>
                          </li>
';

$admin_link = '
                          <li class="sidebar-menu-item {{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'admin.dashboard\') }}"><i class=\'bx bx-home-alt\'></i><span>Dashboard</span></a>
                          </li>
                          <li class="sidebar-menu-item {{ request()->routeIs(\'admin.profile-status\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'admin.profile-status\') }}"><i class=\'bx bx-user-check\'></i><span>Profile Status</span></a>
                          </li>
';

$search_sa = '<li class="sidebar-menu-item {{ request()->routeIs(\'superadmin.dashboard\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'superadmin.dashboard\') }}"><i class=\'bx bx-home-alt\'></i><span>Dashboard</span></a>
                          </li>';

$search_a = '<li class="sidebar-menu-item {{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}">
                              <a href="{{ route(\'admin.dashboard\') }}"><i class=\'bx bx-home-alt\'></i><span>Dashboard</span></a>
                          </li>';

// normalize
$content = str_replace("\r\n", "\n", $content);
$search_sa = str_replace("\r\n", "\n", $search_sa);
$search_a = str_replace("\r\n", "\n", $search_a);

if (strpos($content, $search_sa) !== false) {
    $content = str_replace($search_sa, $superadmin_link, $content);
}
if (strpos($content, $search_a) !== false) {
    $content = str_replace($search_a, $admin_link, $content);
}

file_put_contents($file, $content);
echo "Updated sidebar links\n";
