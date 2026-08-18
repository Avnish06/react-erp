<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ApiSyncController extends Controller
{
    public function storeEmployee(Request $request)
    {
        // Simple API Key Verification
        $apiKey = $request->header('X-ERP-SECRET');
        $expectedKey = env('ERP_SHARED_SECRET', 'default-erp-secret-12345');

        if (!$apiKey || $apiKey !== $expectedKey) {
            Log::warning('Unauthorized employee sync attempt from API.', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string',
            'company_name' => 'nullable|string'
        ]);

        // Check if user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json(['success' => false, 'message' => 'User already exists in workspace.'], 409);
        }

        // Try to match the company name, fallback to 1
        $companyId = 1;
        if ($request->filled('company_name')) {
            $companyName = $request->company_name;
            $company = Company::where('name', 'like', "%{$companyName}%")->first();
            if ($company) {
                $companyId = $company->id;
            } else {
                $company = Company::create([
                    'name' => $companyName,
                    'email' => 'contact@' . strtolower(str_replace(' ', '', $companyName)) . '.com',
                    'address' => 'Unknown',
                ]);
                $companyId = $company->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'department' => $request->department ?? 'General',
            'position' => $request->position ?? 'Employee',
            'salary' => $request->salary ?? 0,
            'company_id' => $companyId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee synced successfully.',
            'data' => [
                'id' => $user->id,
                'email' => $user->email
            ]
        ], 201);
    }

    public function storeProject(Request $request)
    {
        $apiKey = $request->header('X-ERP-SECRET');
        if (!$apiKey || $apiKey !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_email' => 'nullable|email'
        ]);

        $project = \App\Models\Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'active',
            'company_id' => 1
        ]);

        if ($request->filled('assigned_email')) {
            $user = User::where('email', $request->assigned_email)->first();
            if ($user) {
                $project->users()->attach($user->id);
                $user->notify(new \App\Notifications\GeneralNotification([
                    'title' => 'New Project Assigned',
                    'message' => 'You have been assigned to project: ' . $project->title,
                    'type' => 'project_assignment',
                    'url' => '/employee/my-projects'
                ]));
            }
        }

        return response()->json(['success' => true, 'project_id' => $project->id], 201);
    }

    public function storeTask(Request $request)
    {
        $apiKey = $request->header('X-ERP-SECRET');
        if (!$apiKey || $apiKey !== env('ERP_SHARED_SECRET', 'default-erp-secret-12345')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'project_title' => 'required|string',
            'assigned_email' => 'required|email',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date'
        ]);

        // Find project by title (since ERP only knows the title)
        $project = \App\Models\Project::where('title', $request->project_title)->first();
        $user = User::where('email', $request->assigned_email)->first();

        if ($project && $user) {
            $task = \App\Models\Task::create([
                'project_id' => $project->id,
                'assigned_to' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => 'pending',
                'company_id' => 1
            ]);

            $user->notify(new \App\Notifications\GeneralNotification([
                'title' => 'New Task Assigned',
                'message' => 'You have been assigned a new task: ' . $task->title,
                'type' => 'task_assignment',
                'url' => '/employee/tasks'
            ]));

            return response()->json(['success' => true, 'task_id' => $task->id], 201);
        }

        return response()->json(['success' => false, 'message' => 'Project or User not found'], 404);
    }
}
