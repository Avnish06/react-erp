<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/face/enroll', function (Request $request) {
    $request->validate([
        'descriptor' => 'required|array',
    ]);

    $user = $request->user();
    $user->face_descriptor = json_encode($request->descriptor);
    $user->save();

    return response()->json(['success' => true, 'message' => 'Face enrolled successfully!']);
});

Route::middleware('auth:sanctum')->get('/face/descriptor', function (Request $request) {
    $user = $request->user();
    $enrolled = !empty($user->face_descriptor);
    return response()->json([
        'success' => true,
        'enrolled' => $enrolled,
        'descriptor' => $enrolled ? json_decode($user->face_descriptor) : null
    ]);
});

// Endpoint for ERP to push employees securely
Route::post('/sync-employee', [\App\Http\Controllers\ApiSyncController::class, 'storeEmployee']);

// Endpoints for ERP to push projects and tasks
Route::post('/sync-project', [\App\Http\Controllers\ApiSyncController::class, 'storeProject']);
Route::post('/sync-task', [\App\Http\Controllers\ApiSyncController::class, 'storeTask']);

// Endpoint for ERP to push announcements securely
Route::post('/sync-announcement', function (Request $request) {
    // Require a shared secret to ensure only the ERP can push here
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    // Ensure a default company exists or is created to associate the announcement with
    $company = \App\Models\Company::firstOrCreate(
        ['name' => 'Hatbaliya technology'],
        ['email' => 'admin@hatbaliya.com', 'address' => 'HQ']
    );

    \App\Models\Announcement::create([
        'company_id' => $company->id,
        'title' => $request->title,
        'description' => $request->content, // Map ERP 'content' to Workspace 'description'
    ]);

    return response()->json(['success' => true, 'message' => 'Announcement synced!']);
});

// Endpoint for ERP to pull all attendance records securely
Route::get('/sync-attendance', function (Request $request) {
    // Require shared secret
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Fetch all attendance records with user details
    $query = \App\Models\Attendance::with('user');
    
    if ($request->has('date')) {
        $query->where('date', $request->date);
    }
    
    $attendances = $query->orderBy('date', 'desc')->orderBy('clock_in', 'desc')->get();

    // Map to a unified format expected by the ERP Node frontend
    $mapped = $attendances->map(function($record) {
        
        // Fetch matching Daily Report if it exists
        $dailyReport = \App\Models\DailyReport::where('user_id', $record->user_id)
            ->where('report_date', $record->date)
            ->first();

        return [
            'id' => $record->id,
            'user_id' => $record->user_id,
            'name' => $record->user ? $record->user->name : 'Unknown',
            'employee_name' => $record->user ? $record->user->name : 'Unknown',
            'role_id' => ($record->user && $record->user->role === 'admin') ? 2 : 3,
            'email' => $record->user ? $record->user->email : '',
            'date' => $record->date,
            'clock_in' => $record->clock_in,
            'clock_out' => $record->clock_out,
            'status' => $record->status,
            'company_id' => $record->company_id,
            // Include daily report data if present
            'has_report' => $dailyReport ? true : false,
            'tasks_completed' => $dailyReport ? $dailyReport->tasks_completed : null,
            'challenges' => $dailyReport ? $dailyReport->challenges : null,
            'plan_tomorrow' => $dailyReport ? $dailyReport->plan_tomorrow : null,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $mapped
    ]);
});
// ============================================================
// Endpoint for ERP to push a payroll/payslip record to Colovo
// ============================================================
Route::post('/sync-payroll', function (Request $request) {
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'employee_email' => 'required|email',
        'month'          => 'required|string|max:50',
        'salary'         => 'required|numeric',
        'net_salary'     => 'required|numeric',
        'bonus'          => 'nullable|numeric',
        'deductions'     => 'nullable|numeric',
        'status'         => 'nullable|string|max:50',
    ]);

    $user = \App\Models\User::where('email', $request->employee_email)->first();
    if (!$user) {
        return response()->json(['error' => 'Employee not found in Colovo Workspace'], 404);
    }

    $payroll = \App\Models\Payroll::updateOrCreate(
        ['user_id' => $user->id, 'month' => $request->month],
        [
            'salary'      => $request->salary,
            'bonus'       => $request->bonus       ?? 0,
            'deductions'  => $request->deductions  ?? 0,
            'net_salary'  => $request->net_salary,
            'status'      => $request->status      ?? 'paid',
            'company_id'  => $user->company_id,
        ]
    );

    // Notify the employee
    $user->notify(new \App\Notifications\GeneralNotification([
        'title'   => 'Payslip Generated',
        'message' => 'Your payslip for ' . $request->month . ' (Net: ₹' . number_format($request->net_salary, 2) . ') has been generated.',
        'type'    => 'payroll',
        'url'     => '/employee/payslips',
    ]));

    return response()->json(['success' => true, 'payroll_id' => $payroll->id, 'message' => 'Payslip synced to Colovo Workspace!']);
});

// ============================================================
// Endpoint for ERP to update leave status in Colovo Workspace
// ============================================================
Route::post('/sync-leave-status', function (Request $request) {
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'employee_email' => 'required|email',
        'leave_id'       => 'nullable|integer',
        'status'         => 'required|in:approved,rejected,pending',
        'reason'         => 'nullable|string',
    ]);

    $user = \App\Models\User::where('email', $request->employee_email)->first();
    if (!$user) {
        return response()->json(['error' => 'Employee not found in Colovo Workspace'], 404);
    }

    // If a specific leave_id is provided, update it
    if ($request->leave_id) {
        $leave = \App\Models\Leave::where('id', $request->leave_id)->where('user_id', $user->id)->first();
        if ($leave) {
            $leave->update(['status' => $request->status]);
        }
    }

    // Always send a notification
    $statusLabel = ucfirst($request->status);
    $user->notify(new \App\Notifications\GeneralNotification([
        'title'   => 'Leave Request ' . $statusLabel,
        'message' => 'Your leave request has been ' . $request->status . '.' . ($request->reason ? ' Reason: ' . $request->reason : ''),
        'type'    => 'leave_status',
        'url'     => '/employee/leave/apply',
    ]));

    return response()->json(['success' => true, 'message' => 'Leave status synced to Colovo Workspace!']);
});

// ============================================================
// Endpoint for ERP to mark a joining document step as done
// ============================================================
Route::post('/sync-onboarding-step', function (Request $request) {
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'employee_email' => 'required|email',
        'document_type'  => 'required|string', // e.g. offer_letter, nda, appointment_letter
    ]);

    $user = \App\Models\User::where('email', $request->employee_email)->first();
    if (!$user) {
        return response()->json(['error' => 'Employee not found in Colovo Workspace'], 404);
    }

    $user->notify(new \App\Notifications\GeneralNotification([
        'title'   => 'Document Ready',
        'message' => 'Your ' . str_replace('_', ' ', $request->document_type) . ' has been generated and is ready for download.',
        'type'    => 'document',
        'url'     => '/employee/joining-document',
    ]));

    return response()->json(['success' => true, 'message' => 'Onboarding step synced!']);
});

// ============================================================
// Endpoint for ERP to push profile updates to Colovo Workspace
// ============================================================
Route::post('/sync-profile', function (Request $request) {
    if ($request->header('X-ERP-SECRET') !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'employee_email' => 'required|email',
        'name'           => 'nullable|string|max:255',
        'department'     => 'nullable|string|max:255',
        'position'       => 'nullable|string|max:255',
        'salary'         => 'nullable|numeric',
        'status'         => 'nullable|string|max:50',
    ]);

    $user = \App\Models\User::where('email', $request->employee_email)->first();
    if (!$user) {
        return response()->json(['error' => 'Employee not found in Colovo Workspace'], 404);
    }

    $updateData = array_filter([
        'name'       => $request->name,
        'department' => $request->department,
        'position'   => $request->position,
        'salary'     => $request->salary,
    ], fn($val) => !is_null($val));

    if (!empty($updateData)) {
        $user->update($updateData);
    }

    return response()->json(['success' => true, 'message' => 'Profile synced to Colovo Workspace!', 'updated_fields' => array_keys($updateData)]);
});

// ============================================================
// Endpoint for Colovo to get employee profile data for ERP
// ============================================================
Route::get('/employee-profile/{email}', function ($email) {
    // Open — used by ERP to read current Colovo profile
    $user = \App\Models\User::where('email', $email)->with('detail')->first();
    if (!$user) {
        return response()->json(['error' => 'Not found'], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'department' => $user->department,
            'position'   => $user->position,
            'salary'     => $user->salary,
            'role'       => $user->role,
        ]
    ]);
});
