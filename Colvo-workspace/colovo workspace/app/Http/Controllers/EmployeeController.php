<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\PerformanceReview;
use App\Models\PromotionRecognition;
use App\Models\Leave;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\Payroll;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    // Middleware handled by routes/web.php

    public function dashboard()
    {
        $user = Auth::user();

        // 1. Fetch assigned tasks
        $tasks = Task::where('assigned_to', $user->id)
            ->orderBy('due_date', 'asc')
            ->get();

        // 2. Fetch today's attendance record (use app timezone to avoid UTC day-mismatch)
        $todayStr = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->toDateString();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $todayStr)
            ->first();

        // 3. Fetch past attendance logs (Limit to 15)
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(15)
            ->get();

        // 4. Fetch performance evaluations
        $reviews = PerformanceReview::where('user_id', $user->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get();

        // 5. Fetch promotions and awards
        $promotions = PromotionRecognition::where('user_id', $user->id)
            ->orderBy('date_awarded', 'desc')
            ->get();

        // 6. Fetch schedules
        $schedules = Schedule::orderBy('time_string')->get();
        $attendanceSetting = $user->company ? $user->company->attendanceSetting : null;

        return view('employee.dashboard', compact(
            'user', 'tasks', 'todayAttendance', 'attendances', 'reviews', 'promotions', 'schedules', 'attendanceSetting'
        ));
    }


    public function tasks(Request $request)
    {
        $user = Auth::user();
        
        // Get all tasks for the metric counts
        $allTasks = Task::where('assigned_to', $user->id)->get();
        
        // Build query for the filtered list
        $query = Task::where('assigned_to', $user->id);
        
        $filter = $request->input('filter', 'all');
        if ($filter === 'today') {
            $query->whereDate('due_date', Carbon::today());
        } elseif ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($filter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($filter === 'previous') {
            $query->whereDate('due_date', '<', Carbon::today())->where('status', '!=', 'completed');
        }
        
        $tasks = $query->orderBy('due_date', 'asc')->get();
            
        // Fetch projects for the task request form
        $projects = auth()->user()->projects;
            
        return view('employee.tasks', compact('tasks', 'allTasks', 'projects', 'filter'));
    }

    public function requestTask(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date'
        ]);

        Task::create([
            'project_id' => $data['project_id'],
            'assigned_to' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'pending', // Default to pending
            'priority' => $data['priority'],
            'due_date' => $data['due_date'],
        ]);

        return redirect()->back()->with('success', 'Task requested successfully.');
    }

    public function attendance()
    {
        $user = Auth::user();
        $todayStr = Carbon::today()->toDateString();
        
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $todayStr)
            ->first();

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $company = $user->company;
        $settings = $company ? $company->settings : [];
        $shiftStart = $settings['shift_start'] ?? '10:10 AM';
        $shiftEnd = $settings['shift_end'] ?? '06:10 PM';
        $lunchStart = $settings['lunch_start'] ?? '02:00 PM';
        $lunchEnd = $settings['lunch_end'] ?? '03:00 PM';

        // Calculate working days in current month excluding Sundays
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();
        $workingDays = 0;
        
        for ($date = clone $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            if (!$date->isSunday()) {
                $workingDays++;
            }
        }

        $presentDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->count();

        $absentDays = max(0, $workingDays - $presentDays);

        return view('employee.attendance', compact(
            'todayAttendance', 'attendances', 
            'shiftStart', 'shiftEnd', 'lunchStart', 'lunchEnd',
            'presentDays', 'absentDays'
        ));
    }

    public function applyLeaveForm()
    {
        $leaves = Leave::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('employee.leave-apply', compact('leaves'));
    }

    public function requestLeave(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:sick,casual,annual,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $endDate = $data['end_date'] ?? $data['start_date'];

        Leave::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $endDate,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave request submitted successfully. Pending admin approval.');
    }

    public function performance()
    {
        $user = Auth::user();
        $reviews = PerformanceReview::where('user_id', $user->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('employee.performance', compact('reviews'));
    }

    public function growth()
    {
        $user = Auth::user();
        
        // 1. Promotions & Achievements
        $promotions = PromotionRecognition::where('user_id', $user->id)
            ->orderBy('date_awarded', 'desc')
            ->get();
            
        // 2. Tasks Summary
        $totalTasks = Task::where('assigned_to', $user->id)->count();
        $completedTasks = Task::where('assigned_to', $user->id)->where('status', 'completed')->count();
        $taskCompletionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        // 2.5 Projects Summary
        $myProjects = Project::whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('tasks')->get();
        
        $completedProjects = $myProjects->filter(function($project) {
            return $project->progress == 100;
        })->count();
        
        $totalProjects = $myProjects->count();
        
        // 3. Attendance Summary (Current Month)
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();
        $daysPresent = $monthlyAttendances->where('status', 'present')->count();
        $daysLate = $monthlyAttendances->where('status', 'late')->count();
        // Assuming 22 working days in a month for calculation
        $attendanceRate = round((($daysPresent + $daysLate) / 22) * 100);
        if($attendanceRate > 100) $attendanceRate = 100;

        // 4. Leave Summary (Current Year)
        $leavesTaken = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum(\DB::raw('DATEDIFF(end_date, start_date) + 1'));

        // 5. Performance Reviews
        $latestReview = PerformanceReview::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        $averageKpi = PerformanceReview::where('user_id', $user->id)->avg('score');
        $averageKpi = $averageKpi ? round($averageKpi * 20) : 0; // Assuming score is 1-5

        // 6. Overall Growth Score Formula (Mock logic)
        $growthScore = round(($taskCompletionRate * 0.4) + ($attendanceRate * 0.3) + ($averageKpi * 0.3));
        
        // 7. Calculate previous month score for comparison (Mock data for now, -2%)
        $prevGrowthScore = $growthScore - 2;

        return view('employee.growth', compact(
            'promotions', 'totalTasks', 'completedTasks', 'taskCompletionRate',
            'completedProjects', 'totalProjects',
            'attendanceRate', 'leavesTaken', 'latestReview', 'averageKpi',
            'growthScore', 'prevGrowthScore'
        ));
    }

    public function learning()
    {
        // For now, we will return a static beautiful view for the learning portal.
        // In the future, this can pull from a Courses table.
        return view('employee.learning');
    }

    public function updateTask(Request $request, Task $task)
    {
        // Ensure user owns this task
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $updateData = ['status' => $data['status']];
        if ($data['status'] === 'completed') {
            $updateData['completed_at'] = Carbon::now();
        } else {
            $updateData['completed_at'] = null;
        }

        $task->update($updateData);

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    public function createDailyReportForm()
    {
        return view('employee.daily-report-create');
    }

    public function submitDailyReport(Request $request)
    {
        $data = $request->validate([
            'tasks_completed' => 'required|string',
            'challenges' => 'nullable|string',
            'plan_tomorrow' => 'required|string'
        ]);

        $userId = Auth::id();
        $todayStr = Carbon::today()->toDateString();

        // Check if report already submitted for today
        $exists = DailyReport::where('user_id', $userId)
            ->where('report_date', $todayStr)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You have already submitted a daily report for today.');
        }

        DailyReport::create([
            'user_id' => $userId,
            'report_date' => $todayStr,
            'tasks_completed' => $data['tasks_completed'],
            'challenges' => $data['challenges'],
            'plan_tomorrow' => $data['plan_tomorrow'],
        ]);

        // Sync directly to ERP Database API
        try {
            \Illuminate\Support\Facades\DB::connection('erp_db')->table('daily_reports')->insert([
                'user_id'         => $userId,
                'work_summary'    => $data['tasks_completed'],
                'tasks_completed' => $data['tasks_completed'],
                'challenges'      => $data['challenges'],
                'plan_tomorrow'   => $data['plan_tomorrow'],
                'created_at'      => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ERP Daily Report Sync Failed: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Daily report submitted successfully. Great job today!');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('employee.profile', compact('user'));
    }


    public function updateDetails(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:255',
            'bank_ifsc' => 'nullable|string|max:255',
            'marksheet_10th' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'marksheet_12th' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'passport_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $detail = $user->detail()->firstOrCreate(['user_id' => $user->id]);

        $data = $request->only([
            'father_name', 'mother_name', 'father_occupation',
            'bank_name', 'bank_account_no', 'bank_ifsc'
        ]);

        if ($request->hasFile('marksheet_10th')) {
            $data['marksheet_10th_path'] = $request->file('marksheet_10th')->store('employee_docs', 'public');
        }
        if ($request->hasFile('marksheet_12th')) {
            $data['marksheet_12th_path'] = $request->file('marksheet_12th')->store('employee_docs', 'public');
        }
        if ($request->hasFile('passport_photo')) {
            $data['passport_photo_path'] = $request->file('passport_photo')->store('employee_docs', 'public');
        }

        $detail->update($data);

        // --- Sync profile update to ERP ---
        // Push the employee's name and other updated fields to the ERP system
        try {
            $erpUrl    = config('services.erp.url', env('ERP_URL', 'http://127.0.0.1:5000'));
            $erpSecret = env('ERP_SHARED_SECRET', 'default-erp-secret-12345');
            Http::withHeaders(['X-ERP-SECRET' => $erpSecret])
                ->timeout(5)
                ->post("{$erpUrl}/api/internal/sync-colovo-profile", [
                    'employee_email' => $user->email,
                    'name'           => $user->name,
                    'department'     => $user->department,
                    'position'       => $user->position,
                ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to push profile to ERP: ' . $e->getMessage());
        }
        // ----------------------------------

        return redirect()->back()->with('success', 'Personal details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    // Project Monitoring System Methods
    private function syncProjectsAndTasksFromErp()
    {
        try {
            $user = Auth::user();
            if (!$user) return;
            $email = $user->email;

            // 1. Get ERP User ID
            $erpUserId = \DB::connection('erp_db')->table('users')->where('email', $email)->value('id');
            if (!$erpUserId) return;

            // 2. Fetch all ERP projects assigned to this user OR containing tasks assigned to this user
            $erpProjects = \DB::connection('erp_db')->table('projects')
                ->where('assigned_to', $erpUserId)
                ->orWhereIn('id', function($q) use ($erpUserId) {
                    $q->select('project_id')->from('tasks')->where('assigned_to', $erpUserId);
                })
                ->get();

            foreach ($erpProjects as $ep) {
                // Find or create local project
                $project = \App\Models\Project::updateOrCreate(
                    ['title' => $ep->name],
                    [
                        'description' => $ep->description,
                        'status' => strtolower($ep->status) === 'completed' ? 'completed' : 'active',
                        'company_id' => $user->company_id ?: 1,
                        'deadline' => $ep->deadline,
                        'erp_project_id' => $ep->id,
                    ]
                );

                // Ensure user is attached to the project
                if (!$project->users()->where('user_id', $user->id)->exists()) {
                    $project->users()->attach($user->id);
                }

                // 3. Fetch ERP tasks for this project assigned to this user
                $erpTasks = \DB::connection('erp_db')->table('tasks')
                    ->where('project_id', $ep->id)
                    ->where('assigned_to', $erpUserId)
                    ->get();

                foreach ($erpTasks as $et) {
                    $mappedStatus = (in_array(strtolower($et->status), ['done', 'completed'])) ? 'completed' : 'pending';

                    \App\Models\Task::updateOrCreate(
                        ['project_id' => $project->id, 'title' => $et->title],
                        [
                            'assigned_to' => $user->id,
                            'description' => $et->description,
                            'status' => $mappedStatus,
                            'due_date' => $et->deadline,
                            'company_id' => $user->company_id ?: 1,
                            'erp_task_id' => $et->id,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('ERP Sync Error: ' . $e->getMessage());
        }
    }

    public function myProjects()
    {
        $this->syncProjectsAndTasksFromErp();

        $userId = Auth::id();
        $projects = Project::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('tasks')->orderBy('created_at', 'desc')->get();

        return view('employee.projects.index', compact('projects'));
    }

    public function showProject(Project $project)
    {
        $this->syncProjectsAndTasksFromErp();

        $userId = Auth::id();
        if (!$project->users()->where('user_id', $userId)->exists()) {
            abort(403);
        }

        $project->load('tasks');
        return view('employee.projects.show', compact('project'));
    }

    public function toggleTask(Request $request, Task $task)
    {
        $userId = Auth::id();
        
        $project = $task->project;
        if (!$project || !$project->users()->where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:pending,completed'
        ]);

        $updateData = ['status' => $data['status']];
        if ($data['status'] === 'completed') {
            $updateData['completed_at'] = Carbon::now();
        } else {
            $updateData['completed_at'] = null;
        }

        $task->update($updateData);

        // Sync back to ERP if mapped
        if ($task->erp_task_id) {
            try {
                $erpStatus = $data['status'] === 'completed' ? 'Done' : 'In Progress';
                \DB::connection('erp_db')->table('tasks')
                    ->where('id', $task->erp_task_id)
                    ->update(['status' => $erpStatus]);
            } catch (\Exception $e) {
                \Log::error('Failed to sync task status to ERP: ' . $e->getMessage());
            }
        }

        // Update project last_activity_at
        $project->update(['last_activity_at' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'progress' => $project->progress,
            'calculated_status' => $project->calculated_status,
            'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $project->tasks()->where('status', 'pending')->count(),
        ]);
    }

    public function batchUpdateTasks(Request $request, Project $project)
    {
        $userId = Auth::id();
        
        if (!$project || !$project->users()->where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.status' => 'required|in:pending,completed'
        ]);

        foreach ($data['tasks'] as $taskData) {
            $task = $project->tasks()->find($taskData['id']);
            if ($task) {
                $updateData = ['status' => $taskData['status']];
                if ($taskData['status'] === 'completed') {
                    if ($task->status !== 'completed') {
                        $updateData['completed_at'] = \Carbon\Carbon::now();
                    }
                } else {
                    $updateData['completed_at'] = null;
                }
                $task->update($updateData);

                // Sync back to ERP
                if ($task->erp_task_id) {
                    try {
                        $erpStatus = $taskData['status'] === 'completed' ? 'Done' : 'In Progress';
                        \DB::connection('erp_db')->table('tasks')
                            ->where('id', $task->erp_task_id)
                            ->update(['status' => $erpStatus]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to sync batch task status to ERP: ' . $e->getMessage());
                    }
                }
            }
        }

        $project->update(['last_activity_at' => \Carbon\Carbon::now()]);
        $project->refresh();

        return response()->json([
            'success' => true,
            'progress' => $project->progress,
            'calculated_status' => $project->calculated_status,
            'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $project->tasks()->where('status', 'pending')->count(),
        ]);
    }

    // Payslips
    public function payslips()
    {
        $payslips = Payroll::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
        return view('employee.payslips.index', compact('payslips'));
    }

    public function payslipShow(Payroll $payroll)
    {
        if ($payroll->user_id !== auth()->id()) {
            abort(403);
        }
        return view('employee.payslips.show', compact('payroll'));
    }

    // Joining Document
    public function joiningDocument()
    {
        $user    = auth()->user();
        $company = $user->company;
        return view('employee.joining-document', compact('user', 'company'));
    }

    public function generateJoiningDocument(\Illuminate\Http\Request $request)
    {
        $user    = auth()->user();
        $company = $user->company;
        $today   = Carbon::today()->format('Y-m-d');

        $data = [
            'company_name'                   => $company->name    ?? '{{company_name}}',
            'company_address'                => $company->address ?? '{{company_address}}',
            'company_logo'                   => $company->logo    ? asset('storage/' . $company->logo) : '',
            'signatory_name'                 => $request->signatory_name    ?? '{{signatory_name}}',
            'signatory_title'                => $request->signatory_title   ?? 'HR Manager',
            'employee_name'                  => $user->name,
            'designation'                    => $user->position               ?? $request->designation ?? '{{designation}}',
            'department'                     => $user->department              ?? $request->department  ?? '{{department}}',
            'employment_type'                => $user->employment_type         ?? 'Full-time',
            'start_date'                     => $user->created_at->format('d F Y'),
            'reporting_manager'              => $user->reporting_manager       ?? $request->reporting_manager ?? '{{reporting_manager}}',
            'work_location'                  => $user->work_location           ?? $request->work_location ?? '{{work_location}}',
            'base_salary'                    => number_format($user->salary ?? 0, 2),
            'currency'                       => $user->currency                ?? 'INR',
            'pay_frequency'                  => $user->pay_frequency           ?? 'Monthly',
            'bonus_structure'                => $user->bonus_structure         ?? $request->bonus_structure ?? 'As per company policy',
            'probation_period_months'        => $user->probation_period_months ?? 3,
            'notice_period_days'             => $user->notice_period_days      ?? 30,
            'non_compete_duration_months'    => $user->non_compete_duration_months ?? 12,
            'non_compete_geographic_scope'   => $user->non_compete_geographic_scope ?? 'India',
            'confidentiality_duration_years' => $user->confidentiality_duration_years ?? 2,
            'jurisdiction'                   => 'India',
            'generated_date'                 => $today,
            'leave_types'                    => [],
        ];

        $documentType = $request->document_type ?? 'offer_letter';
        
        $methodMap = [
            'offer_letter' => 'generateOfferLetter',
            'appointment_letter' => 'generateAppointmentLetter',
            'employment_contract' => 'generateEmploymentContract',
            'nda' => 'generateNDA',
            'leave_policy' => 'generateLeavePolicy',
        ];
        
        $documentController = new \App\Http\Controllers\DocumentController();
        $documents = [];

        if ($documentType === 'all') {
            foreach ($methodMap as $key => $method) {
                $reflection = new \ReflectionMethod($documentController, $method);
                $reflection->setAccessible(true);
                $documents[] = $reflection->invoke($documentController, $data);
            }
        } else {
            $methodToCall = $methodMap[$documentType] ?? 'generateOfferLetter';
            $reflection = new \ReflectionMethod($documentController, $methodToCall);
            $reflection->setAccessible(true);
            $documents[] = $reflection->invoke($documentController, $data);
        }

        // --- Notify ERP that a joining document was generated ---
        try {
            $erpUrl    = config('services.erp.url', env('ERP_URL', 'http://127.0.0.1:5000'));
            $erpSecret = env('ERP_SHARED_SECRET', 'default-erp-secret-12345');
            Http::withHeaders(['X-ERP-SECRET' => $erpSecret])
                ->timeout(5)
                ->post("{$erpUrl}/api/internal/sync-colovo-profile", [
                    'employee_email' => $user->email,
                    'document_type'  => $documentType,
                    'action'         => 'document_generated',
                ]);
        } catch (\Exception $e) {
            // Non-critical: don't break the response
        }
        // -------------------------------------------------------

        return view('admin.documents.preview', compact('documents', 'data'));
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        $attendanceSetting = $company->attendanceSetting;

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string',
        ]);

        if ($attendanceSetting && $attendanceSetting->office_latitude && $attendanceSetting->office_longitude) {
            $distance = $this->calculateDistance(
                $request->latitude, 
                $request->longitude, 
                $attendanceSetting->office_latitude, 
                $attendanceSetting->office_longitude
            );

            $allowedRadius = $attendanceSetting->allowed_radius ?? 100;

            if ($distance > $allowedRadius) {
                return redirect()->back()->with('error', 'You are not within the allowed office location radius ('. round($distance) .'m away).');
            }
        } else {
            $distance = null;
        }

        $photoData = $request->photo;
        $photoPath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return redirect()->back()->with('error', 'Invalid image type');
            }
            $photoData = base64_decode($photoData);
            if ($photoData === false) {
                return redirect()->back()->with('error', 'Base64 decode failed');
            }
            $photoName = 'attendance/in_' . time() . '_' . $user->id . '.' . $type;
            \Illuminate\Support\Facades\Storage::disk('public')->put($photoName, $photoData);
            $photoPath = $photoName;
        }

        $today = Carbon::today()->toDateString();
        
        $settings = $company ? $company->settings : [];
        $shiftStart = $settings['shift_start'] ?? '10:00 AM';
        $shiftStartCarbon = Carbon::parse($today . ' ' . $shiftStart);
        $currentTime = Carbon::now();

        $status = 'present';
        if ($currentTime->gt($shiftStartCarbon->addMinutes(15))) {
            $status = 'late';
        }

        Attendance::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'date' => $today,
            'clock_in' => $currentTime->toTimeString(),
            'status' => $status,
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'check_in_distance' => $distance,
            'check_in_photo' => $photoPath,
        ]);

        // Sync directly to ERP Database API
        try {
            \Illuminate\Support\Facades\DB::connection('erp_db')->table('attendance')->insert([
                'user_id'       => $user->id,
                'employee_name' => $user->name,
                'date'          => $today,
                'clock_in'      => $currentTime->toTimeString(),
                'status'        => ucfirst($status),
                'image_url'     => $photoPath ? asset('storage/' . $photoPath) : null,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ERP Attendance Sync Failed (Clock In): " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Clocked in successfully!');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        $attendanceSetting = $company->attendanceSetting;

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string',
        ]);

        if ($attendanceSetting && $attendanceSetting->office_latitude && $attendanceSetting->office_longitude) {
            $distance = $this->calculateDistance(
                $request->latitude, 
                $request->longitude, 
                $attendanceSetting->office_latitude, 
                $attendanceSetting->office_longitude
            );

            $allowedRadius = $attendanceSetting->allowed_radius ?? 100;

            if ($distance > $allowedRadius) {
                return redirect()->back()->with('error', 'You are not within the allowed office location radius ('. round($distance) .'m away).');
            }
        } else {
            $distance = null;
        }

        $photoData = $request->photo;
        $photoPath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $photoData = substr($photoData, strpos($photoData, ',') + 1);
            $type = strtolower($type[1]);
            $photoData = base64_decode($photoData);
            $photoName = 'attendance/out_' . time() . '_' . $user->id . '.' . $type;
            \Illuminate\Support\Facades\Storage::disk('public')->put($photoName, $photoData);
            $photoPath = $photoName;
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            $clockInTime = Carbon::parse($attendance->clock_in);
            $clockOutTime = Carbon::now();
            $workingHours = $clockInTime->diffInMinutes($clockOutTime) / 60;

            $attendance->update([
                'clock_out' => $clockOutTime->toTimeString(),
                'check_out_latitude' => $request->latitude,
                'check_out_longitude' => $request->longitude,
                'check_out_distance' => $distance,
                'check_out_photo' => $photoPath,
                'working_hours' => $workingHours,
            ]);

            // Sync directly to ERP Database API
            try {
                \Illuminate\Support\Facades\DB::connection('erp_db')->table('attendance')
                    ->where('user_id', $user->id)
                    ->where('date', $today)
                    ->update([
                        'clock_out'           => $clockOutTime->toTimeString(),
                        'clock_out_image_url' => $photoPath ? asset('storage/' . $photoPath) : null,
                    ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("ERP Attendance Sync Failed (Clock Out): " . $e->getMessage());
            }

            return redirect()->back()->with('success', 'Clocked out successfully!');
        }

        return redirect()->back()->with('error', 'No clock-in record found for today.');
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|string',
        ]);

        $user = Auth::user();
        $user->update([
            'face_descriptor' => $request->face_descriptor
        ]);

        return response()->json(['success' => true, 'message' => 'Face registered successfully!']);
    }
}
