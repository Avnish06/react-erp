<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeQuery;
use App\Models\User;

class QueryController extends Controller
{
    // --- Employee Methods ---
    public function employeeIndex()
    {
        $companyId = auth()->user()->company_id;
        // Queries raised by me
        $myQueries = EmployeeQuery::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
        // Queries assigned to me
        $assignedQueries = EmployeeQuery::where('assigned_to', auth()->id())->orderBy('created_at', 'desc')->get();
        
        return view('employee.queries.index', compact('myQueries', 'assignedQueries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        EmployeeQuery::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Query raised successfully!');
    }

    public function resolve(EmployeeQuery $query)
    {
        if ($query->assigned_to == auth()->id()) {
            $query->update(['status' => 'resolved']);
            return back()->with('success', 'Query marked as resolved.');
        }
        return back()->with('error', 'Unauthorized.');
    }

    // --- Admin Methods ---
    public function adminIndex()
    {
        $companyId = auth()->user()->company_id;
        $queries = EmployeeQuery::with(['user', 'assignee'])->where('company_id', $companyId)->orderBy('created_at', 'desc')->get();
        $employees = User::where('company_id', $companyId)->where('role', 'employee')->get();
        
        return view('admin.queries.index', compact('queries', 'employees'));
    }

    public function adminAssign(Request $request, EmployeeQuery $query)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        $query->update(['assigned_to' => $request->assigned_to, 'status' => 'in-progress']);
        return back()->with('success', 'Query assigned successfully.');
    }

    public function adminUpdateStatus(Request $request, EmployeeQuery $query)
    {
        $request->validate(['status' => 'required|in:pending,in-progress,resolved']);
        $query->update(['status' => $request->status]);
        return back()->with('success', 'Query status updated.');
    }

    // --- Super Admin Methods ---
    public function superadminIndex()
    {
        // For superadmin, they see queries from all companies, but we'll show them in a list.
        $queries = EmployeeQuery::with(['user', 'assignee', 'company'])->orderBy('created_at', 'desc')->get();
        $allEmployees = User::where('role', 'employee')->get();
        return view('superadmin.queries.index', compact('queries', 'allEmployees'));
    }

    public function superadminAssign(Request $request, EmployeeQuery $query)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        // Ensure assigned user is in the same company
        $user = User::findOrFail($request->assigned_to);
        if ($user->company_id !== $query->company_id) {
            return back()->with('error', 'Employee must belong to the same company as the query.');
        }
        $query->update(['assigned_to' => $request->assigned_to, 'status' => 'in-progress']);
        return back()->with('success', 'Query assigned successfully.');
    }

    public function superadminUpdateStatus(Request $request, EmployeeQuery $query)
    {
        $request->validate(['status' => 'required|in:pending,in-progress,resolved']);
        $query->update(['status' => $request->status]);
        return back()->with('success', 'Query status updated.');
    }
}
