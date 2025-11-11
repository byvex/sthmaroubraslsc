<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use App\Models\SenderNumber;
use Illuminate\Auth\SessionGuard;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $current_user = $req->user();
        $query = User::query();

        if ($req->ajax()) {
            $excludeId = $req->excludeId;
            $keyword = $req->keyword;
            $parentId = $req->parentId;
            $childId = $req->childId;
            if (!empty($excludeId)) {
                $query = $query->whereNot('id', $excludeId);
            }

            if (!empty($keyword)) {
                $query = $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('company', 'like', '%' . $keyword . '%')
                    ->orWhere('lastname', 'like', '%' . $keyword . '%');
            }

            if (!empty($parentId)) {
                $parent = User::findOrFail($parentId);
                $users = $parent->children;
            } elseif (!empty($childId)) {
                $child = User::findOrFail($childId);
                $users = $child->parents;
            } else {
                $users = $query->get([
                    'id',
                    'name',
                    'lastname',
                    'company',
                    'email'
                ]);
            }

            $items = [];
            if (!empty($users)) {
                foreach ($users as $_user) {
                    $items[] = [
                        'id' => $_user->id,
                        'name' => $_user->name,
                        'lastname' => $_user->lastname ?? '',
                        'company' => $_user->company ?? '',
                        'email' => $_user->email,
                    ];
                }
            }
            return response()->json([
                'items' => $items,
            ]);
        }

        $query = $query->with('sender');
        if ($current_user->isSuperAdmin()) {
            $items = $query->with('children:id,name,email')->paginate(50);
        } else {
            $items = $current_user->children()->paginate(50);
        }
        return view('users.index', [
            'items' => $items,
            'current_user' => $current_user,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $req)
    {
        $current_user = $req->user();
        return view('users.create', [
            'current_user' => $current_user,
            'user_roles' => UserRoleEnum::toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $currentUser = $req->user();
        if ($currentUser->isUser()) {
            return response()->json(['message' => 'You are not authorized to create user'], 403);
        }

        $req->merge(['email' => strtolower($req['email'])]);
        $input = $req->validate([
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'numeric'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'role' => [new Enum(UserRoleEnum::class)],
            'password' => ['required', 'string', 'max:26'],
        ]);

        if ($currentUser->isAdmin() && $input['role'] == UserRoleEnum::SUPERADMIN) {
            $input['role'] = UserRoleEnum::ADMIN;
        }
        if ($currentUser->isUser()) {
            $input['role'] = UserRoleEnum::USER;
        }

        try {
            $item = User::create($input);
            $item->parents()->attach($currentUser->id);
            return response()->json([
                'success' => true,
                'redirect' => route('users.edit', $item->id),
                'message' => 'User created',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $req, string $id)
    {
        $currentUser = $req->user();
        $senderNumbers = SenderNumber::get();
        $item = User::findOrFail($id);
        return view('users.edit', [
            'item' => $item,
            'currentUser' => $currentUser,
            'senderNumbers' => $senderNumbers,
            'user_roles' => UserRoleEnum::toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req, string $id)
    {
        $currentUser = $req->user();
        $user = User::findOrFail($id);

        $req->merge(['email' => strtolower($req['email'])]);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'numeric'],
            'role' => ['nullable', new Enum(UserRoleEnum::class)],
            'children_id' => ['nullable'],
            'children_id.*' => ['nullable', Rule::exists(User::class, 'id')],
            'parent_id' => ['nullable'],
            'parent_id.*' => ['nullable', Rule::exists(User::class, 'id')],
        ];

        if (!$user->isUser()) {
            $rules['sender_number'] = ['required', Rule::exists(SenderNumber::class, 'id')];
        }

        $input = $req->validate($rules);

        if ($currentUser->isUser() && $currentUser->id != $user->id) {
            return response()->json([
                'message' => 'You can only edit your own account',
            ]);
        }
        if ($currentUser->isAdmin() && $user->isSuperAdmin()) {
            return response()->json([
                'message' => 'You can not edit Super Admin',
            ]);
        }

        if (empty($input['children_id'])) {
            $input['children_id'] = [];
        }

        if (empty($input['parent_id'])) {
            $input['parent_id'] = [];
        }
        if (!$currentUser->isSuperAdmin()) {
            if (!in_array($currentUser->id, $input['parent_id'])) {
                $input['parent_id'][] = $currentUser->id;
            }
        }

        try {
            $user->children()->sync($input['children_id']);
            $user->parents()->sync($input['parent_id']);
            $user->update($input);

            if (!empty($req->password)) {
                $user->password = Hash::make($req->password);
                $user->save();
            }

            return response()->json(['message' => 'Updated successfully']);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function setProfile(Request $req)
    {
        $id = $req->id;
        $current_user = $req->user();
        $user = User::find($id);
        if (empty($user)) {
            $id = $current_user->id;
        }
        $current_user->setActiveProfile($id);
        return response()->json(['refresh' => true]);
    }

    public function mimic_login(Request $req)
    {
        // $current_user = $req->user();
        // if(empty($current_user)) {
        //     return response()->json(['message'=> 'Please login']);
        // }

        // if(!$current_user->isSuperAdmin()) {
        //     return response()->json(['message'=> 'Only Super Admins are allowed']);
        // }

        $user_id = $req->id;
        if(empty($user_id)) {
            return response()->json(['message'=> 'User ID is missing']);
        }

        try {
            $user = User::where('id', $user_id)->first();
            Auth::login($user);
            return redirect('/user/profile');
            // return response()->json($user);
        } catch (Exception $e) {
            dd($e);
        }
    }


    public function activityReportSettings(Request $req)
    {
        $user = $req->user();
        if (empty($user)) {
            return response()->json([
                'message' => 'Please login to save settings'
            ], 422);
        }

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only Super Admin can save settings'
            ], 403);
        }

        $emails = $req->activity_report_emails;
        $emails = !empty($emails) ? explode(',', $emails) : [];
        $times = $req->activity_report_time;
        $times = !empty($times) ? $times : [];

        if (!empty($emails) && is_array($emails)) {
            foreach ($emails as $email) {
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'message' => $email . ' is invalid email, please remove it or fix it',
                    ], 422);
                }
            }
        }

        $data = [
            'emails' => $emails,
            'times' => $times,
        ];

        try {
            Setting::updateOrCreate([
                'key' => 'activity-report-settings'
            ], ['value' => json_encode($data), 'tag' => 'sms-activity']);

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function smsRelaySettings(Request $req)
    {
        $user = $req->user();
        if (empty($user)) {
            return response()->json(['message' => 'Please login to save settings'], 422);
        }

        if (!$user->isSuperAdmin()) {
            return response()->json(['message' => 'Only Super Admin can save settings'], 403);
        }

        $emails = $req->relayEmails;
        $emails = !empty($emails) ? explode(',', $emails) : [];

        if (!empty($emails) && is_array($emails)) {
            foreach ($emails as $email) {
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'message' => $email . ' is invalid email, please remove it or fix it',
                    ], 422);
                }
            }
        }

        $data = ['emails' => $emails,];
        try {
            Setting::updateOrCreate([
                'key' => 'sms-relay-settings'
            ], ['value' => json_encode($data), 'tag' => 'sms-relay']);

            return response()->json([
                'success' => true,
                'message' => 'Relay emails saved successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    public function globalRelaySettings(Request $req)
    {
        $input = $req->validate([
            'host' => ['required', 'string'],
            'port' => ['required', 'numeric'],
            'user' => ['required', 'string'],
            'pass' => ['nullable', 'string'],
            'header_name' => ['nullable', 'string'],
            'header_value' => ['nullable', 'string'],
            'from' => ['required', 'string'],
            'to' => ['required', 'string'],
            'rcpt_to' => ['required', 'string'],
            'message_id' => ['nullable', 'string'],
            'subject_prefix' => ['required', 'string'],
            'tls' => ['nullable', 'in:1'],
        ]);
        try {
            if (empty($input['message_id'])) {
                $input['message_id'] = '';
            }
            Setting::updateOrCreate([
                'key' => 'global-relay-settings'
            ], ['value' => json_encode($input), 'tag' => 'global-relay']);
            return response()->json([
                'success' => true,
                'message' => 'Global Relay configurations saved successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'redirect' => route('users.index'),
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
