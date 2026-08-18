<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;

use App\Http\Controllers\SuperAdminController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Impersonation Exit Route (Must be outside role:superadmin since role is temporarily changed)
Route::middleware('auth')->group(function () {
    Route::get('/superadmin/impersonate/leave', [SuperAdminController::class, 'leaveImpersonate'])->name('superadmin.impersonate.leave');
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});

// Super Admin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile-status', [SuperAdminController::class, 'profileStatus'])->name('profile-status');
    Route::get('/workspaces', [SuperAdminController::class, 'workspacesIndex'])->name('workspaces');
    Route::post('/workspaces', [SuperAdminController::class, 'workspacesStore'])->name('workspaces.store');
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
    Route::get('/profile-status', [AdminController::class, 'profileStatus'])->name('profile-status');
    Route::get('/tasks', [AdminController::class, 'tasks'])->name('tasks');
    Route::post('/tasks/assign', [AdminController::class, 'assignTask'])->name('tasks.assign');
    Route::patch('/tasks/{task}/status', [AdminController::class, 'updateTaskStatus'])->name('tasks.update-status');
    Route::post('/projects/store', [AdminController::class, 'storeProject'])->name('projects.store');
    Route::get('/directory', [AdminController::class, 'directory'])->name('directory');
    Route::post('/directory/store', [AdminController::class, 'storeEmployee'])->name('directory.store');
    Route::delete('/directory/{employee}', [AdminController::class, 'deleteEmployee'])->name('directory.delete');
    Route::get('/performance', [AdminController::class, 'performance'])->name('performance');
    Route::post('/performance/create', [AdminController::class, 'createReview'])->name('performance.create');
    Route::get('/promotions', [AdminController::class, 'promotions'])->name('promotions');
    Route::post('/promotions/store', [AdminController::class, 'storePromotion'])->name('promotions.store');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/daily-reports', [AdminController::class, 'dailyReports'])->name('daily-reports');
    Route::delete('/daily-reports/{report}', [AdminController::class, 'deleteDailyReport'])->name('daily-reports.delete');
    Route::get('/settings', function() { return 'Workspace Settings'; })->name('settings');
    Route::get('/finance', [AdminController::class, 'finance'])->name('finance');
    Route::post('/finance/store', [AdminController::class, 'storeFinance'])->name('finance.store');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
    Route::get('/leaves', [AdminController::class, 'leaves'])->name('leaves');
    Route::post('/leaves/{leave}/approve', [AdminController::class, 'approveLeave'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [AdminController::class, 'rejectLeave'])->name('leaves.reject');
    
    // Project Monitoring System
    Route::get('/projects-monitoring', [AdminController::class, 'monitoringIndex'])->name('projects-monitoring.index');
    Route::get('/projects-monitoring/create', [AdminController::class, 'monitoringCreate'])->name('projects-monitoring.create');
    Route::post('/projects-monitoring', [AdminController::class, 'monitoringStore'])->name('projects-monitoring.store');
    Route::delete('/projects-monitoring/{project}', [AdminController::class, 'monitoringDestroy'])->name('projects-monitoring.destroy');
    Route::get('/projects-monitoring/{project}', [AdminController::class, 'monitoringShow'])->name('projects-monitoring.show');
    
    // Schedule Management
    Route::get('/schedule', [AdminController::class, 'scheduleIndex'])->name('schedule.index');
    Route::post('/schedule', [AdminController::class, 'scheduleStore'])->name('schedule.store');
    Route::delete('/schedule/{schedule}', [AdminController::class, 'scheduleDestroy'])->name('schedule.destroy');

    // Payroll Management
    Route::get('/payroll', [AdminController::class, 'payrollIndex'])->name('payroll.index');
    Route::post('/payroll', [AdminController::class, 'payrollStore'])->name('payroll.store');
    Route::get('/payroll/{payroll}', [AdminController::class, 'payrollShow'])->name('payroll.show');

    // Workspace Settings
    Route::get('/settings', [AdminController::class, 'settingsIndex'])->name('settings');
    Route::post('/settings', [AdminController::class, 'settingsStore'])->name('settings.store');

    // Document Generator
    Route::get('/documents', [\App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/generate', [\App\Http\Controllers\DocumentController::class, 'generate'])->name('documents.generate');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'adminIndex'])->name('announcements.index');

    // Queries
    Route::get('/queries', [\App\Http\Controllers\QueryController::class, 'adminIndex'])->name('queries.index');
    Route::patch('/queries/{query}/assign', [\App\Http\Controllers\QueryController::class, 'adminAssign'])->name('queries.assign');
    Route::patch('/queries/{query}/status', [\App\Http\Controllers\QueryController::class, 'adminUpdateStatus'])->name('queries.status');
});

// Employee Routes
Route::middleware(['auth', 'role:employee,admin,superadmin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [EmployeeController::class, 'tasks'])->name('tasks');
    Route::post('/tasks/request', [EmployeeController::class, 'requestTask'])->name('tasks.request');
    Route::patch('/tasks/{task}', [EmployeeController::class, 'updateTask'])->name('tasks.update');
    Route::get('/attendance', [EmployeeController::class, 'attendance'])->name('attendance');
    Route::post('/clock-in', [EmployeeController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [EmployeeController::class, 'clockOut'])->name('clock-out');
    Route::get('/performance', [EmployeeController::class, 'performance'])->name('performance');
    Route::get('/growth', [EmployeeController::class, 'growth'])->name('growth');
    Route::get('/learning-portal', [EmployeeController::class, 'learning'])->name('learning');
    Route::get('/leave/apply', [EmployeeController::class, 'applyLeaveForm'])->name('leave.apply');
    Route::post('/leave/request', [EmployeeController::class, 'requestLeave'])->name('leave.request');
    Route::get('/daily-report/create', [EmployeeController::class, 'createDailyReportForm'])->name('daily-report.create');
    Route::post('/daily-report', [EmployeeController::class, 'submitDailyReport'])->name('daily-report');
    Route::get('/profile', [EmployeeController::class, 'profile'])->name('profile');
    Route::post('/profile/password', [EmployeeController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/details', [EmployeeController::class, 'updateDetails'])->name('profile.details');

    // Project Monitoring System
    Route::get('/my-projects', [EmployeeController::class, 'myProjects'])->name('my-projects.index');
    Route::get('/my-projects/{project}', [EmployeeController::class, 'showProject'])->name('my-projects.show');
    Route::post('/tasks/{task}/status', [EmployeeController::class, 'updateTaskStatus'])->name('tasks.status');
    
    // Payslips
    Route::get('/payslips', [EmployeeController::class, 'payslips'])->name('payslips.index');
    Route::get('/payslips/{payroll}', [EmployeeController::class, 'payslipShow'])->name('payslips.show');
    Route::post('/my-projects/tasks/{task}/toggle', [EmployeeController::class, 'toggleTask'])->name('my-projects.tasks.toggle');
    Route::post('/my-projects/{project}/tasks/batch', [EmployeeController::class, 'batchUpdateTasks'])->name('my-projects.tasks.batch');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'employeeIndex'])->name('announcements.index');

    // Queries
    Route::get('/queries', [\App\Http\Controllers\QueryController::class, 'employeeIndex'])->name('queries.index');
    Route::post('/queries', [\App\Http\Controllers\QueryController::class, 'store'])->name('queries.store');
    Route::patch('/queries/{query}/resolve', [\App\Http\Controllers\QueryController::class, 'resolve'])->name('queries.resolve');

    // Joining Document
    Route::get('/joining-document', [EmployeeController::class, 'joiningDocument'])->name('joining-document');
    Route::post('/joining-document/generate', [EmployeeController::class, 'generateJoiningDocument'])->name('joining-document.generate');
});
