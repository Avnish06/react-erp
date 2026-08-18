<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\FinancialRecord;
use App\Models\PerformanceReview;
use App\Models\PromotionRecognition;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Admin
        $admin = User::create([
            'name' => 'ESP Admin',
            'email' => 'admin@colovo.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department' => 'Management',
            'position' => 'HR Director',
            'salary' => 120000.00,
        ]);

        // 2. Create Employees
        $employees = [
            [
                'name' => 'John Doe',
                'email' => 'john@colovo.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Development',
                'position' => 'Senior Developer',
                'salary' => 75000.00,
            ],
            [
                'name' => 'Sarah Connor',
                'email' => 'sarah@colovo.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'salary' => 50000.00,
            ],
            [
                'name' => 'Alex Mercer',
                'email' => 'alex@colovo.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Development',
                'position' => 'Junior Developer',
                'salary' => 45000.00,
            ],
            [
                'name' => 'Emma Watson',
                'email' => 'emma@colovo.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'HR',
                'position' => 'HR Recruiter',
                'salary' => 55000.00,
            ],
            [
                'name' => 'David Miller',
                'email' => 'david@colovo.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Sales',
                'position' => 'Sales Lead',
                'salary' => 65000.00,
            ]
        ];

        $employeeModels = [];
        foreach ($employees as $emp) {
            $employeeModels[] = User::create($emp);
        }

        // 3. Create Projects
        $proj1 = Project::create([
            'title' => 'Workspace Portal v2.0',
            'description' => 'Upgrading the core client collaborative hub and dashboards.',
            'status' => 'active',
            'budget' => 120000.00,
        ]);

        $proj2 = Project::create([
            'title' => 'Financial Reporting Dashboard',
            'description' => 'Automated generation of yearly earnings, expenditures and performance analysis.',
            'status' => 'planning',
            'budget' => 85000.00,
        ]);

        $proj3 = Project::create([
            'title' => 'HR Automation System',
            'description' => 'Legacy project for automating attendance, evaluations and promotion requests.',
            'status' => 'completed',
            'budget' => 95000.00,
        ]);

        // 4. Create Tasks
        Task::create([
            'project_id' => $proj1->id,
            'assigned_to' => $employeeModels[0]->id, // John
            'title' => 'Setup SQLite Database & Models',
            'description' => 'Create all base databases schemas and models migrations.',
            'status' => 'completed',
            'priority' => 'high',
            'due_date' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(2),
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'assigned_to' => $employeeModels[2]->id, // Alex
            'title' => 'Design Dashboard Grid Layout',
            'description' => 'Implement modern glassmorphic look with sidebar navigation.',
            'status' => 'in_progress',
            'priority' => 'medium',
            'due_date' => Carbon::now()->addDays(5),
        ]);

        Task::create([
            'project_id' => $proj2->id,
            'assigned_to' => $employeeModels[1]->id, // Sarah
            'title' => 'Market Positioning & Launch Strategy',
            'description' => 'Draft copy, media layouts and launch schedules for the finance board.',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::now()->addDays(15),
        ]);

        Task::create([
            'project_id' => $proj1->id,
            'assigned_to' => $employeeModels[0]->id, // John
            'title' => 'Connect Backend Auth Middleware',
            'description' => 'Ensure proper role guards for admin (ESP Admin Panel) and employee viewings.',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => Carbon::now()->addDays(3),
        ]);

        Task::create([
            'project_id' => $proj3->id,
            'assigned_to' => $employeeModels[3]->id, // Emma
            'title' => 'Audit HR Records & Onboarding Logs',
            'description' => 'Verify compliance files for all newly hired devs in workspace.',
            'status' => 'completed',
            'priority' => 'low',
            'due_date' => Carbon::now()->subDays(10),
            'completed_at' => Carbon::now()->subDays(11),
        ]);

        Task::create([
            'project_id' => $proj2->id,
            'assigned_to' => $employeeModels[4]->id, // David
            'title' => 'Configure Sales Projections',
            'description' => 'Aggregate Q3 revenue data and import into financial summaries.',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => Carbon::now()->addDays(10),
        ]);

        // 5. Create Attendance logs (Past 5 days)
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            
            foreach ($employeeModels as $index => $emp) {
                // Let's add some variety (present, late, absent)
                $status = 'present';
                $clockIn = '10:00:00';
                $clockOut = '18:00:00';
                
                // Randomize a bit
                if ($i == 1 && $index == 2) {
                    $status = 'late';
                    $clockIn = '11:15:00';
                } elseif ($i == 2 && $index == 1) {
                    $status = 'absent';
                    $clockIn = null;
                    $clockOut = null;
                } elseif ($i == 3 && $index == 4) {
                    $status = 'late';
                    $clockIn = '10:45:00';
                }

                Attendance::create([
                    'user_id' => $emp->id,
                    'date' => $date,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                ]);
            }
        }

        // 6. Create Financial Records (Last 3 years)
        FinancialRecord::create([
            'year' => 2024,
            'earnings' => 1200000.00,
            'expenditures' => 850000.00,
            'summary' => 'Initial development launch, scaling infrastructure, and hiring core developers.',
        ]);

        FinancialRecord::create([
            'year' => 2025,
            'earnings' => 1850000.00,
            'expenditures' => 1150000.00,
            'summary' => 'Increased marketing and acquisition. Expanded development department with additional junior positions.',
        ]);

        FinancialRecord::create([
            'year' => 2026,
            'earnings' => 2400000.00,
            'expenditures' => 1400000.00,
            'summary' => 'Current fiscal year tracking. Strong organic growth from product revisions and workspace efficiency improvements.',
        ]);

        // 7. Create Performance Reviews
        // John Review
        PerformanceReview::create([
            'user_id' => $employeeModels[0]->id, // John
            'reviewer_id' => $admin->id,
            'review_period' => 'Yearly 2026',
            'score' => 9,
            'evaluation' => 'John has exceeded expectations in setting up key infrastructure and leading core codebase refactoring. Communication is excellent, and task completions are high quality.',
            'classification' => 'high_performer',
            'action_plan' => 'Promote to Technical Lead, grant a 10% salary hike, and award formal recognition.',
        ]);

        // Sarah Review
        PerformanceReview::create([
            'user_id' => $employeeModels[1]->id, // Sarah
            'reviewer_id' => $admin->id,
            'review_period' => 'Yearly 2026',
            'score' => 8,
            'evaluation' => 'Sarah has executed highly effective campaigns. The launch positioning is structured and on schedule. Teamwork is outstanding.',
            'classification' => 'high_performer',
            'action_plan' => 'Issue Employee of the Month award and discuss salary adjustment next quarter.',
        ]);

        // Alex Review
        PerformanceReview::create([
            'user_id' => $employeeModels[2]->id, // Alex
            'reviewer_id' => $admin->id,
            'review_period' => 'Yearly 2026',
            'score' => 4,
            'evaluation' => 'Alex struggles with timeline estimates and has missed several milestones on frontend tasks. Coding standards need alignment with guidelines.',
            'classification' => 'low_performer',
            'action_plan' => 'Enlist in a 30-day performance improvement plan with weekly developer mentoring.',
        ]);

        // 8. Create Promotions and Recognition records
        PromotionRecognition::create([
            'user_id' => $employeeModels[0]->id, // John
            'type' => 'promotion',
            'title' => 'Promoted to Technical Lead',
            'detail' => 'Promoted from Senior Developer to Technical Lead in recognition of exceptional service and systems architecture delivery.',
            'amount' => null,
            'date_awarded' => Carbon::now()->toDateString(),
        ]);

        PromotionRecognition::create([
            'user_id' => $employeeModels[0]->id, // John
            'type' => 'salary_hike',
            'title' => '10% Salary Adjustment',
            'detail' => 'Salary hiked by $7,500.00 annually matching Tech Lead scaling.',
            'amount' => 7500.00,
            'date_awarded' => Carbon::now()->toDateString(),
        ]);

        PromotionRecognition::create([
            'user_id' => $employeeModels[1]->id, // Sarah
            'type' => 'recognition',
            'title' => 'Employee of the Month (June)',
            'detail' => 'Awarded for driving workspace marketing and design coordination successfully.',
            'amount' => null,
            'date_awarded' => Carbon::now()->subDays(15)->toDateString(),
        ]);
    }
}

