<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Exception;
use App\Models\LoyaltyMember;
use App\Http\Resources\Users\LoyaltyMemberResource;
use App\Http\Requests\Users\LoyaltyMemberRequest;

class LoyaltyMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = LoyaltyMember::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
        
        $loyalty_members = $query->orderBy('name')->paginate(20);

        return inertia('app/users/loyalty_members/Index', [
            'loyalty_members' => LoyaltyMemberResource::collection($loyalty_members),
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        return inertia('app/users/loyalty_members/Create');
    }

    public function store(LoyaltyMemberRequest $request)
    {
        try {
            DB::beginTransaction();

            LoyaltyMember::create($request->validated());

            DB::commit();

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Member created successfully"
            ]);

            return to_route('loyalty-members.index');
        } catch (Exception $e) {
            DB::rollBack();

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => "Failed to create member: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    public function edit(LoyaltyMember $loyalty_member)
    {
        return inertia('app/users/loyalty_members/Edit', [
            'loyalty_member' => $loyalty_member
        ]);
    }

    public function update(LoyaltyMemberRequest $request, LoyaltyMember $loyalty_member)
    {
        try {
            DB::beginTransaction();

            $loyalty_member->update($request->validated());

            DB::commit();

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "Member updated successfully"
            ]);

            return to_route('loyalty-members.index');
        } catch (Exception $e) {
            DB::rollBack();

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => "Failed to update member: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    public function destroy(LoyaltyMember $loyalty_member)
    {
        try {
            $loyalty_member->delete();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Member deleted successfully"
            ]);

            return to_route('loyalty-members.index');
        } catch (Exception $e) {
            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to delete member: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }
}
