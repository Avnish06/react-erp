<?php
$file = 'routes/web.php';
$content = file_get_contents($file);

$search = "    Route::post('/workspaces', [SuperAdminController::class, 'storeWorkspace'])->name('workspaces.store');
    Route::get('/workspaces/{company}', [SuperAdminController::class, 'workspacesShow'])->name('workspaces.show');
    Route::put('/workspaces/{company}', [SuperAdminController::class, 'updateWorkspace'])->name('workspaces.update');
    Route::delete('/workspaces/{company}', [SuperAdminController::class, 'destroyWorkspace'])->name('workspaces.destroy');
    Route::get('/users', [SuperAdminController::class, 'usersIndex'])->name('users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}', [SuperAdminController::class, 'usersShow'])->name('users.show');
    Route::get('/impersonate/{user}', [SuperAdminController::class, 'impersonate'])->name('impersonate');
    Route::post('/users/{user}/suspend', [SuperAdminController::class, 'suspendUser'])->name('users.suspend');
    Route::get('/reports', [SuperAdminController::class, 'reportsIndex'])->name('reports');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');

    // SuperAdmin Routes for Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'superadminIndex'])->name('announcements.index');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // SuperAdmin Routes for Queries
    Route::get('/queries', [\App\Http\Controllers\QueryController::class, 'superadminIndex'])->name('queries.index');
    Route::post('/queries/{query}/resolve', [\App\Http\Controllers\QueryController::class, 'resolve'])->name('queries.resolve');
    Route::post('/queries/{query}/comment', [\App\Http\Controllers\QueryController::class, 'addComment'])->name('queries.comment');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile-status', [AdminController::class, 'profileStatus'])->name('profile-status');";

$replace = "    Route::post('/workspaces', [SuperAdminController::class, 'workspacesStore'])->name('workspaces.store');
    Route::put('/workspaces/{company}', [SuperAdminController::class, 'workspacesUpdate'])->name('workspaces.update');
    Route::get('/workspaces/{company}', [SuperAdminController::class, 'workspacesShow'])->name('workspaces.show');
    Route::patch('/workspaces/{company}/toggle', [SuperAdminController::class, 'workspacesToggle'])->name('workspaces.toggle');
    Route::delete('/workspaces/{company}', [SuperAdminController::class, 'workspacesDelete'])->name('workspaces.delete');
    Route::get('/users', [SuperAdminController::class, 'usersIndex'])->name('users');
    Route::post('/users', [SuperAdminController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{user}', [SuperAdminController::class, 'usersShow'])->name('users.show');
    Route::get('/impersonate/{user}', [SuperAdminController::class, 'impersonate'])->name('impersonate');
    Route::patch('/users/{user}/role', [SuperAdminController::class, 'usersUpdateRole'])->name('users.role');
    Route::get('/reports', [SuperAdminController::class, 'reportsIndex'])->name('reports');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'superadminIndex'])->name('announcements.index');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Queries
    Route::get('/queries', [\App\Http\Controllers\QueryController::class, 'superadminIndex'])->name('queries.index');
    Route::patch('/queries/{query}/assign', [\App\Http\Controllers\QueryController::class, 'superadminAssign'])->name('queries.assign');
    Route::patch('/queries/{query}/status', [\App\Http\Controllers\QueryController::class, 'superadminUpdateStatus'])->name('queries.status');
});

// Admin Routes
Route::middleware(['auth', 'role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile-status', [AdminController::class, 'profileStatus'])->name('profile-status');";

// Also add superadmin profile status route if not present
$search2 = "Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');";
$replace2 = "Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile-status', [SuperAdminController::class, 'profileStatus'])->name('profile-status');";

// normalize endings
$content = str_replace("\r\n", "\n", $content);
$search = str_replace("\r\n", "\n", $search);
$replace = str_replace("\r\n", "\n", $replace);

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
}
if (strpos($content, str_replace("\r\n", "\n", $search2)) !== false) {
    $content = str_replace(str_replace("\r\n", "\n", $search2), str_replace("\r\n", "\n", $replace2), $content);
}

file_put_contents($file, $content);
echo "Restored and updated routes/web.php\n";
