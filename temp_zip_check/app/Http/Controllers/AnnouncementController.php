<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Company;

class AnnouncementController extends Controller
{
    // --- Super Admin Methods ---
    public function superadminIndex()
    {
        $companies = Company::all();
        $announcements = Announcement::with('company')->orderBy('created_at', 'desc')->get();
        return view('superadmin.announcements.index', compact('announcements', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Announcement::create([
            'company_id' => $request->company_id,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Announcement created successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement deleted successfully!');
    }

    // --- Admin Methods ---
    public function adminIndex()
    {
        $companyId = auth()->user()->company_id;
        $announcements = Announcement::where('company_id', $companyId)->orderBy('created_at', 'desc')->get();
        return view('admin.announcements.index', compact('announcements'));
    }

    // --- Employee Methods ---
    public function employeeIndex()
    {
        $companyId = auth()->user()->company_id;
        $announcements = Announcement::where('company_id', $companyId)->orderBy('created_at', 'desc')->get();
        return view('employee.announcements.index', compact('announcements'));
    }
}
