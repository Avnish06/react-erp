<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Finance;
use App\Models\Attendance;
use App\Models\FinancialRecord;
use App\Models\PerformanceReview;
use App\Models\PromotionRecognition;
use App\Models\Leave;
use App\Models\DailyReport;
use App\Models\Schedule;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Middleware handled by routes/web.php

    public function dashboard()
    {
        // 1. Projects statistics
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        
        // 2. Tasks statistics
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        
        // 3. Employees count
        $empQuery = User::where('role', 'employee');
        if (auth()->user()->role === 'admin') {
            $empQuery->where('company_id', auth()->user()->company_id);
        }
        $totalEmployees = $empQuery->count();

        // 4. Today's Attendance
        $todayStr = Carbon::today()->toDateString();
        $attendanceToday = Attendance::where('date', $todayStr)->get();
        $presentToday = $attendanceToday->where('status', 'present')->count();
        $lateToday = $attendanceToday->where('status', 'late')->count();
        $absentToday = $attendanceToday->where('status', 'absent')->count();
        
        // If no attendance has been recorded yet today (e.g. weekend or early morning), show seeded past stats
        if ($attendanceToday->count() === 0) {
            // Get latest date from table
            $latestDate = Attendance::max('date') ?: $todayStr;
            $attendanceToday = Attendance::where('date', $latestDate)->get();
            $presentToday = $attendanceToday->where('status', 'present')->count();
            $lateToday = $attendanceToday->where('status', 'late')->count();
            $absentToday = $attendanceToday->where('status', 'absent')->count();
        }

        // 5. Performance Reviews Summary (Seeded High/Low Performers)
        $highPerformers = PerformanceReview::where('classification', 'high_performer')->count();
        $lowPerformers = PerformanceReview::where('classification', 'low_performer')->count();

        // 6. Lists for Reports
        $projects = Project::with(['users', 'tasks'])->orderBy('created_at', 'desc')->get();
        $tasks = Task::with(['project', 'assignee'])->orderBy('due_date', 'asc')->get();
        $employees = $empQuery->withCount('tasks')->get();
        $attendances = Attendance::with('user')->orderBy('date', 'desc')->take(30)->get();
        $reviews = PerformanceReview::with(['user', 'reviewer'])->orderBy('created_at', 'desc')->get();
        $promotions = PromotionRecognition::with('user')->orderBy('date_awarded', 'desc')->get();

        // 7. Finance summary data (for reports/charts)
        $financialRecords = FinancialRecord::orderBy('year', 'asc')->get();

        return view('admin.dashboard', compact(
            'totalProjects', 'activeProjects', 'totalTasks', 'completedTasks', 
            'inProgressTasks', 'pendingTasks', 'totalEmployees', 'presentToday', 
            'lateToday', 'absentToday', 'highPerformers', 'lowPerformers',
            'projects', 'tasks', 'employees', 'attendances', 'reviews', 
            'promotions', 'financialRecords'
        ));
    }

    public function tasks()
    {
        $projectQuery = Project::query();
        $employeeQuery = User::where('role', 'employee');
        
        if (auth()->user()->role === 'admin') {
            $projectQuery->where('company_id', auth()->user()->company_id);
            $employeeQuery->where('company_id', auth()->user()->company_id);
        }
        
        $projects = $projectQuery->get();
        $employees = $employeeQuery->get();
        
        // Scope tasks to these projects
        $projectIds = $projects->pluck('id');
        $tasks = Task::with(['project', 'assignee'])->whereIn('project_id', $projectIds)->orderBy('created_at', 'desc')->get();
        return view('admin.tasks', compact('projects', 'employees', 'tasks'));
    }

    public function assignTask(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date',
        ]);

        Task::create($data + ['status' => 'pending']);

        return redirect()->route('admin.tasks')->with('success', 'Task assigned successfully.');
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
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

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'budget' => $data['budget'] ?? null,
            'status' => 'active',
            'company_id' => auth()->user()->company_id,
        ]);
        
        if (!empty($data['assigned_users'])) {
            $project->users()->sync($data['assigned_users']);
        }

        return redirect()->back()->with('success', 'New project created successfully!');
    }

    public function performance(Request $request)
    {
        $query = User::where('role', 'employee');
        if (auth()->user()->role === 'admin') {
            $query->where('company_id', auth()->user()->company_id);
        }
        $employees = $query->get();
        
        $query = PerformanceReview::with(['user', 'reviewer'])->orderBy('created_at', 'desc');
        if ($request->has('employee_id') && $request->employee_id != '') {
            $query->where('user_id', $request->employee_id);
        }
        $reviews = $query->get();
        
        return view('admin.performance', compact('employees', 'reviews'));
    }

    public function createReview(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'review_period' => 'required|string|max:255',
            'score' => 'required|integer|min:1|max:10',
            'evaluation' => 'required|string',
            'classification' => 'required|in:high_performer,low_performer',
            'action_plan' => 'nullable|string',
        ]);

        PerformanceReview::create($data + ['reviewer_id' => auth()->id()]);

        return redirect()->route('admin.performance')->with('success', 'Performance evaluation logged successfully.');
    }

    public function directory(\Illuminate\Http\Request $request)
    {
        $query = User::where('role', 'employee')->with('detail');
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin') {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            } elseif (auth()->user()->role === 'admin') {
                $query->where('company_id', auth()->user()->company_id);
            }
        }
        $employees = $query->get();
        // Get today's attendance for these employees
        $todayStr = Carbon::today()->toDateString();
        $attendances = Attendance::where('date', $todayStr)->get()->keyBy('user_id');
        $companies = \App\Models\Company::all();

        return view('admin.directory', compact('employees', 'attendances', 'companies'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'department' => 'required|string',
            'position' => 'required|string',
            'salary' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:companies,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'department' => $request->department,
            'position' => $request->position,
            'salary' => $request->salary ?? 0,
            'company_id' => $request->company_id,
        ]);

        return redirect()->back()->with('success', 'Employee added successfully.');
    }

    public function deleteEmployee(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }
        
        $employee->delete();
        return redirect()->back()->with('success', 'Employee deleted successfully.');
    }

    public function reports()
    {
        // Project Stats
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        // Task Stats
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();
        
        // Performance Stats
        $highPerformers = PerformanceReview::where('classification', 'high_performer')->count();
        $lowPerformers = PerformanceReview::where('classification', 'low_performer')->count();

        return view('admin.reports', compact('totalProjects', 'activeProjects', 'completedProjects', 'totalTasks', 'completedTasks', 'highPerformers', 'lowPerformers'));
    }

    public function finance()
    {
        $financialRecords = FinancialRecord::orderBy('year', 'desc')->get();
        return view('admin.finance', compact('financialRecords'));
    }

    public function storeFinance(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer',
            'earnings' => 'required|numeric|min:0',
            'expenditures' => 'required|numeric|min:0',
            'summary' => 'nullable|string',
        ]);

        FinancialRecord::updateOrCreate(
            ['year' => $data['year']],
            [
                'earnings' => $data['earnings'],
                'expenditures' => $data['expenditures'],
                'summary' => $data['summary']
            ]
        );

        return redirect()->route('admin.finance')->with('success', 'Financial record stored successfully.');
    }

    public function promotions(Request $request)
    {
        $employeeQuery = User::where('role', 'employee');
        
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin') {
            if ($request->filled('company_id')) {
                $employeeQuery->where('company_id', $request->company_id);
            } elseif (auth()->user()->role === 'admin') {
                $employeeQuery->where('company_id', auth()->user()->company_id);
            }
        }
        $employees = $employeeQuery->get();
        $employeeIds = $employees->pluck('id');
        
        $query = PromotionRecognition::with('user')->whereIn('user_id', $employeeIds)->orderBy('date_awarded', 'desc');
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        $promotions = $query->get();
        
        $companies = \App\Models\Company::all();
        
        return view('admin.promotions', compact('employees', 'promotions', 'companies'));
    }

    public function storePromotion(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:promotion,salary_hike,recognition,appreciation',
            'title' => 'required|string|max:255',
            'detail' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'date_awarded' => 'required|date',
        ]);

        $promo = PromotionRecognition::create($data);

        // If it is a salary hike, update employee's salary
        if ($data['type'] === 'salary_hike' && isset($data['amount'])) {
            $user = User::find($data['user_id']);
            $user->salary += $data['amount'];
            $user->save();
        }

        // If it is a promotion to a new title, optionally update position
        if ($data['type'] === 'promotion') {
            $user = User::find($data['user_id']);
            // If the title contains "Promoted to X", we can clean it or just set it
            $title = str_replace(['Promoted to ', 'promoted to '], '', $data['title']);
            $user->position = $title;
            $user->save();
        }

        return redirect()->route('admin.promotions')->with('success', 'Promotion / Recognition successfully awarded.');
    }


    public function profileStatus()
    {
        $user = auth()->user();
        $employees = \App\Models\User::where('company_id', $user->company_id)
            ->where('role', 'employee')
            ->with('detail')
            ->get();
        return view('admin.employee_profiles', compact('employees'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    public function leaves()
    {
        $leaves = Leave::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.leaves', compact('leaves'));
    }

    public function approveLeave(Leave $leave)
    {
        $leave->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Leave approved successfully.');
    }

    public function rejectLeave(Leave $leave)
    {
        $leave->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Leave rejected.');
    }

    public function dailyReports()
    {
        $dailyReports = DailyReport::with('user')->orderBy('report_date', 'desc')->get();
        return view('admin.daily-reports', compact('dailyReports'));
    }

    public function deleteDailyReport(DailyReport $report)
    {
        $report->delete();
        return redirect()->back()->with('success', 'Daily report deleted successfully.');
    }

    // Project Monitoring System Methods
    public function monitoringIndex(Request $request)
    {
        $companies = \App\Models\Company::all();
        
        $empQuery = User::where('role', 'employee');
        if (auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin') {
            $empQuery->where('company_id', auth()->user()->company_id);
        } elseif ($request->has('company_id') && $request->company_id != '') {
            $empQuery->where('company_id', $request->company_id);
        }
        $employees = $empQuery->get();
        
        $query = Project::with('users', 'tasks')->orderBy('created_at', 'desc');
        
        if ($request->has('employee_id') && $request->employee_id != '') {
            $query->whereHas('users', function($q) use ($request) {
                $q->where('users.id', $request->employee_id);
            });
        }
        
        $projects = $query->get();
        return view('admin.project-monitoring.index', compact('projects', 'employees', 'companies'));
    }

    public function monitoringCreate()
    {
        $employees = User::where('role', 'employee')->get();
        return view('admin.project-monitoring.create', compact('employees'));
    }

    public function monitoringStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'assigned_user' => 'required|exists:users,id',
            'deadline' => 'nullable|date',
            'tasks' => 'required|array|min:1',
            'tasks.*' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'title' => $data['title'],
            'status' => 'active',
            'deadline' => $data['deadline'] ?? null,
            'company_id' => auth()->user()->company_id,
            'last_activity_at' => Carbon::now(),
        ]);

        $project->users()->attach($data['assigned_user']);

        foreach ($data['tasks'] as $taskName) {
            Task::create([
                'project_id' => $project->id,
                'assigned_to' => $data['assigned_user'],
                'title' => $taskName,
                'status' => 'pending',
                'company_id' => auth()->user()->company_id,
            ]);
        }

        $user = User::find($data['assigned_user']);
        if ($user) {
            $user->notify(new \App\Notifications\ProjectAssigned($project));
        }

        return redirect()->route('admin.projects-monitoring.index')->with('success', 'Project created and tasks assigned successfully!');
    }

    public function monitoringShow(Project $project)
    {
        $project->load('users', 'tasks');
        return view('admin.project-monitoring.show', compact('project'));
    }

    public function monitoringDestroy(Project $project)
    {
        // First delete all tasks associated with the project
        $project->tasks()->delete();
        
        // Delete the project
        $project->delete();
        
        return redirect()->route('admin.projects-monitoring.index')->with('success', 'Project and all associated tasks deleted successfully!');
    }

    public function scheduleIndex()
    {
        $schedules = Schedule::orderBy('time_string')->get();
        return view('admin.schedule', compact('schedules'));
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'time_string' => 'required|string|max:50',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:200',
            'color' => 'required|in:purple,blue,orange,green,red',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        Schedule::create($data);

        return back()->with('success', 'Schedule item added successfully!');
    }

    public function scheduleDestroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Schedule item deleted successfully!');
    }


    // Payroll Management
    public function payrollIndex(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('F Y'));
        $employees = User::where('role', 'employee')->where('company_id', auth()->user()->company_id)->get();
        
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
        $totalDays = $endOfMonth->day;
        
        // If the month is current month, we still calculate total days of the whole month.
        $workingDays = 0;
        $sundays = 0;
        for ($date = clone $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            if (!$date->isSunday()) {
                $workingDays++;
            } else {
                $sundays++;
            }
        }

        foreach ($employees as $employee) {
            $fullDays = Attendance::where('user_id', $employee->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            $halfDays = Attendance::where('user_id', $employee->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->where('status', 'half_day')
                ->count();

            $presentDays = $fullDays + ($halfDays * 0.5);
            
            $baseSalary = $employee->salary;
            
            $calculatedSalary = 0;
            if ($workingDays > 0) {
                $calculatedSalary = ($baseSalary / $workingDays) * $presentDays;
            }
            
            $employee->working_days = $workingDays;
            $employee->present_days = $presentDays;
            $employee->calculated_salary = round($calculatedSalary, 2);
            $employee->payroll = Payroll::where('user_id', $employee->id)->where('month', $month)->first();
        }

        return view('admin.payroll.index', compact('employees', 'month', 'totalDays', 'sundays'));
    }

    public function payrollStore(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|string',
            'salary' => 'required|numeric',
            'bonus' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'net_salary' => 'required|numeric',
        ]);

        $calculatedSalary = $data['net_salary'];
        $bonus = $data['bonus'] ?? 0;
        $deductions = $data['deductions'] ?? 0;
        $finalNetSalary = $calculatedSalary + $bonus - $deductions;

        $payroll = Payroll::updateOrCreate(
            ['user_id' => $data['user_id'], 'month' => $data['month']],
            [
                'salary' => $data['salary'],
                'bonus' => $bonus,
                'deductions' => $deductions,
                'net_salary' => $finalNetSalary,
                'status' => 'paid',
                'company_id' => auth()->user()->company_id,
            ]
        );

        try {
            $user = User::find($data['user_id']);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PayslipGenerated($user, $payroll));
            $msg = 'Payslip generated and email sent successfully.';
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send payslip email: ' . $e->getMessage());
            $msg = 'Payslip generated successfully, but email could not be sent (check mail config).';
        }

        return back()->with('success', $msg);
    }
    
    public function payrollShow(Payroll $payroll)
    {
        if ($payroll->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        return view('admin.payroll.show', compact('payroll'));
    }
    public function settingsIndex()
    {
        $company = \App\Models\Company::with('attendanceSetting')->find(auth()->user()->company_id);
        return view('admin.settings', compact('company'));
    }

    public function settingsStore(Request $request)
    {
        $request->validate([
            'shift_start' => 'nullable|string',
            'shift_end' => 'nullable|string',
            'lunch_start' => 'nullable|string',
            'lunch_end' => 'nullable|string',
            'office_latitude' => 'nullable|numeric',
            'office_longitude' => 'nullable|numeric',
            'allowed_radius' => 'nullable|integer',
        ]);

        $company = \App\Models\Company::find(auth()->user()->company_id);
        
        $settings = $company->settings ?? [];
        $settings['shift_start'] = $request->shift_start;
        $settings['shift_end'] = $request->shift_end;
        $settings['lunch_start'] = $request->lunch_start;
        $settings['lunch_end'] = $request->lunch_end;
        
        $company->settings = $settings;
        $company->save();

        if ($request->filled('office_latitude') && $request->filled('office_longitude')) {
            \App\Models\CompanyAttendanceSetting::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'office_latitude' => $request->office_latitude,
                    'office_longitude' => $request->office_longitude,
                    'allowed_radius' => $request->allowed_radius ?? 100,
                ]
            );
        }

        return back()->with('success', 'Workspace settings updated successfully.');
    }
}
