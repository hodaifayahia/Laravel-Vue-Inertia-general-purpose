<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChildrenController extends Controller
{
    /**
     * Display a listing of children.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // If super-admin or admin, show all children with their partners
        if ($user->hasRole(['super-admin', 'admin'])) {
            $children = Child::with('partner:id,name,email')
                ->latest()
                ->paginate(15);
        } else {
            // Partners see only their own children
            $children = Child::where('partner_id', $user->id)
                ->latest()
                ->paginate(15);
        }

        return Inertia::render('Children/Index', [
            'children' => $children,
            'canManageAll' => $user->hasRole(['super-admin', 'admin']),
        ]);
    }

    /**
     * Show the form for creating a new child.
     */
    public function create(): Response
    {
        return Inertia::render('Children/Create');
    }

    /**
     * Store a newly created child.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'medical_notes' => 'nullable|string|max:1000',
        ]);

        $validated['partner_id'] = auth()->id();

        Child::create($validated);

        return redirect()->route('children.index')
            ->with('success', 'Child added successfully!');
    }

    /**
     * Show the form for editing the specified child.
     */
    public function edit(Child $child): Response
    {
        // Ensure user can only edit their own children (unless admin)
        if (!auth()->user()->hasRole(['super-admin', 'admin']) && $child->partner_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Children/Edit', [
            'child' => $child,
        ]);
    }

    /**
     * Update the specified child.
     */
    public function update(Request $request, Child $child)
    {
        // Ensure user can only update their own children (unless admin)
        if (!auth()->user()->hasRole(['super-admin', 'admin']) && $child->partner_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'medical_notes' => 'nullable|string|max:1000',
        ]);

        $child->update($validated);

        return redirect()->route('children.index')
            ->with('success', 'Child updated successfully!');
    }

    /**
     * Remove the specified child.
     */
    public function destroy(Child $child)
    {
        // Ensure user can only delete their own children (unless admin)
        if (!auth()->user()->hasRole(['super-admin', 'admin']) && $child->partner_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $child->delete();

        return redirect()->route('children.index')
            ->with('success', 'Child deleted successfully!');
    }
}
