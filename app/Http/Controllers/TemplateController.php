<?php

namespace App\Http\Controllers;

use App\Enums\ModelStatusEnum;
use App\Models\Profile;
use App\Models\Template;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $current_user = $req->user();
        $profile_id = $current_user->getActiveProfile();
        $profileIds = $current_user->allProfileIds();

        if($req->ajax()){
            $keyword = $req->keyword;
            $query = Template::query();

            if(!$current_user->isSuperAdmin()){
                $query = $query->whereIn('profile_id', $profileIds);
            }

            if(!empty($keyword)){
                $query = $query->where('name', 'like', '%'. $keyword . '%');
            }
            $items = $query->orderBy('name')->get(['id', 'name', 'profile_id', 'message']);
            return response()->json([
                'success'=> true,
                'items'=> $items,
                'message'=> 'Success'
            ]);
        } else {
            $profiles = $current_user->getProfiles();
            return view('templates.index', [
                'profiles' => $profiles,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $input = $req->validate([
            'id' => ['nullable', 'string', Rule::exists(Template::class, 'id')],
            'profile_id' => ['required', 'string', Rule::exists(User::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:255'],
        ], [
            'profile_id.required' => 'Please select a profile for this template'
        ]);

        $input['status'] = ModelStatusEnum::PUBLISHED;
        $message = 'Saved new template successfully';
        $is_updating = false;

        try {

            if(!empty($input['id'])){
                $is_updating = true;
                $item = Template::findOrFail($input['id']);
                $item->update($input);
                $message = 'Updated template successfully';
            } else {
                $item = Template::create($input);
            }

            return response()->json([
                'success' => true,
                'id' => $item->id,
                'reload'=> true,
                'updating'=> $is_updating,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
        //
    }

    public function delete(Request $req)
    {
        $user = $req->user();
        $id = $req->id;
        if (empty($id)) {
            return response()->json([
                'message' => 'Template ID is missing'
            ], 422);
        }
        $item = Template::findOrFail($id);
        try {
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Deleted template',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        //
    }
}
