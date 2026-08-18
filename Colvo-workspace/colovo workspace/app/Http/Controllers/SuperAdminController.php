<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Company;
use App\Models\User;
use App\Models\FinancialRecord;
use App\Models\Project;
use App\Models\Task;

class SuperAdminController extends Controller
{

    public function profileStatus()
    {
        $employees = \App\Models\User::where('role', 'employee')
            ->with(['detail', 'company'])
            ->get();
        return view('admin.employee_profiles', compact('employees'));
    }

    public function dashboard()
    {
        $companiesCount = Company::count();
        $usersCount = User::count();
        $projectsCount = Project::count();

        return view('superadmin.dashboard', compact('companiesCount', 'usersCount', 'projectsCount'));
    }

    // Workspaces (Companies)
    public function workspacesIndex(Request $request)
    {
        $query = Company::withCount(['users', 'projects']);
        
        if ($request->filled('company_id')) {
            $query->where('id', $request->company_id);
        }
        
        $companies = $query->get();
        $allCompanies = Company::select('id', 'name', 'logo')->orderBy('name')->get();
        
        return view('superadmin.workspaces', compact('companies', 'allCompanies'));
    }

    public function workspacesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
        ]);
        
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('company_logos', 'public');
        }
        
        $data['status'] = 'active';
        Company::create($data);
        
        return back()->with('success', 'Workspace created successfully.');
    }

    public function workspacesShow(Company $company)
    {
        // Load all users for this company, along with their assigned tasks
        $company->load(['users' => function($query) {
            $query->where('role', '!=', 'superadmin')->orderBy('role', 'asc'); // admin first, then employee
        }, 'users.tasks']);

        return view('superadmin.workspaces_show', compact('company'));
    }

    public function workspacesToggle(Company $company)
    {
        $company->status = $company->status === 'active' ? 'inactive' : 'active';
        $company->save();
        return back()->with('success', 'Workspace status updated.');
    }

    public function workspacesUpdate(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo && \Storage::disk('public')->exists($company->logo)) {
                \Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company_logos', 'public');
        }

        $company->update($data);
        return back()->with('success', 'Workspace updated successfully.');
    }

    public function workspacesDelete(Company $company)
    {
        // Delete associated logo if it exists
        if ($company->logo && \Storage::disk('public')->exists($company->logo)) {
            \Storage::disk('public')->delete($company->logo);
        }
        
        // Delete the company
        $company->delete();
        
        return back()->with('success', 'Workspace deleted successfully.');
    }

    // Users
    public function usersIndex(Request $request)
    {
        $query = User::with('company')->where('role', '!=', 'superadmin');
        
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        
        $users = $query->get();
        $companies = Company::all();
        return view('superadmin.users', compact('users', 'companies'));
    }

    public function usersStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'role' => 'required|string',
            'password' => 'required|string|min:6',
            'company_id' => 'required|exists:companies,id'
        ]);

        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        User::create($data);
        return back()->with('success', 'User added to workspace successfully.');
    }

    public function usersShow(\App\Models\User $user)
    {
        $user->load(['company', 'tasks.project', 'attendances' => function($q) {
            $q->orderBy('date', 'desc')->take(5);
        }, 'leaves' => function($q) {
            $q->orderBy('start_date', 'desc')->take(5);
        }, 'payrolls' => function($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }]);
        
        return view('superadmin.users_show', compact('user'));
    }

    public function usersUpdateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => 'required|string']);
        $user->update(['role' => $data['role']]);
        return back()->with('success', 'User role updated.');
    }


    public function impersonate(\App\Models\User $user)
    {
        // Store current superadmin id in session
        session(['impersonated_by' => auth()->id()]);
        
        // Login as the selected user
        \Illuminate\Support\Facades\Auth::login($user);
        
        // Redirect to their dashboard based on role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'You are now viewing as ' . $user->name);
        } else {
            return redirect()->route('employee.dashboard')->with('success', 'You are now viewing as ' . $user->name);
        }
    }

    public function leaveImpersonate()
    {
        if (session()->has('impersonated_by')) {
            $superAdminId = session('impersonated_by');
            // Log back in as superadmin
            \Illuminate\Support\Facades\Auth::loginUsingId($superAdminId);
            // Forget session
            session()->forget('impersonated_by');
            
            return redirect()->route('superadmin.dashboard')->with('success', 'Returned to Super Admin panel.');
        }
        
        return redirect()->back();
    }

    // Reports
    public function reportsIndex()
    {
        $companies = Company::withCount(['users', 'projects', 'tasks'])->get();
        return view('superadmin.reports', compact('companies'));
    }

    // Settings
    public function settings()
    {
        return view('superadmin.settings');
    }

    public function updateSettings(Request $request)
    {
        // For demonstration, we just flash a success message.
        // In a real application, you'd save these to a settings table or .env file.
        return back()->with('success', 'System settings have been successfully updated.');
    }
}
