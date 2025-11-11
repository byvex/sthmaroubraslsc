<?php

namespace App\Http\Controllers;

use App\Enums\ModelStatusEnum;
use App\Exports\ContactGroupsExport;
use App\Models\Contact;
use App\Models\ContactGroup;
// use App\Services\SMSApi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Log;

class ContactGroupController extends Controller
{
    protected $smsApi;

    function __construct(
        // SMSApi $smsApi
    )
    {
        // $this->smsApi = $smsApi;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $current_user = $req->user();
        $profile_id = $current_user->getActiveProfile();
        $profileIds = $current_user->allProfileIds();

        if(!in_array($current_user->id, $profileIds)) {
            $profileIds[] = $current_user->id;
        }

        if ($req->ajax()) {
            $keyword = $req->keyword;
            $need_contacts = $req->need_contacts;
            $query = ContactGroup::query();
            $query = $query->select([
                'id',
                'user_id',
                'profile_id',
                'name',
                'created_at',
            ]);

            if(!$current_user->isSuperAdmin()){
                $query = $query->whereIn('profile_id', $profileIds);
            }

            if (!empty($keyword)) {
                $query = $query->where('name', 'like', '%' . $keyword . '%');
            }
            $data = $query->with('author:id,name')
                ->with('profile:id,name,company')->orderBy('name', 'asc')
                ->paginate(25);
            $items = [];
            $perPage = $data->perPage();
            $totalPages = $data->lastPage();
            $totalRows = $data->total();
            $page = $data->currentPage();
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            if (!empty($data->items())) {
                foreach ($data->items() as $item) {
                    $items[] = [
                        'id' => $item->id,
                        'total' => $item?->contacts()->count(),
                        'uid' => $item->id,
                        'name' => $item->name,
                        'createdBy' => 'Created by ' . $item->author->name . ' ' . $item->author->lastname,
                        'createdOn' => 'Created on ' . $item->created_at->format('jS \of F Y'),
                        'profile' => $item->profile?->company . ' ' . $item->profile?->name,
                    ];
                }
            }

            $contacts = [];
            if (!empty($need_contacts)) {
                $contacts_obs_q = Contact::query();
                if(!$current_user->isSuperAdmin()) {
                    $contacts_obs_q = $contacts_obs_q->whereIn('profile_id', $profileIds);
                }
                $contacts_obs = $contacts_obs_q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('lastname', 'like', '%' . $keyword . '%')
                    ->orWhere('company', 'like', '%' . $keyword . '%')
                    ->get(['id', 'name', 'lastname', 'profile_id', 'company', 'phone'])->take(25);
                if (!empty($contacts_obs)) {
                    foreach ($contacts_obs as $cnt) {
                        $cnt_name = $cnt->name . ' ' . $cnt->lastname;
                        if (!empty($cnt->company)) {
                            $cnt_name .= ' (' . $cnt->company . ')';
                        }
                        if(!$current_user->isSuperAdmin()){
                            if(!in_array($cnt->profile_id, $profileIds) ) {
                                continue;
                            }
                        }
                        $contacts[] = [
                            'id' => $cnt->id,
                            'name' => $cnt_name,
                            '_' => $cnt->profile_id,
                            'phone' => $cnt->phone,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalRows' => $totalRows,
                'contacts' => $contacts,
                // 'contacts_obs' => $contacts_obs,
                'profileIds' => $profileIds,
            ]);
        } else {
            return view('contact-groups.index', []);
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
        $user = $req->user();
        $input = $req->validate([
            'name' => ['required', 'string', 'max:255']
        ], [
            'name.required' => 'Group name is required'
        ]);
        $input['user_id'] = $user->id;
        $input['status'] = ModelStatusEnum::PUBLISHED;
        $id = $req->id;
        $name = $input['name'];
        $is_updating = false;
        $item = null;
        if (!empty($id)) {
            $is_updating = true;
            $item = ContactGroup::where('id', $id)->first();
        } else {
            $duplicateItem = ContactGroup::where('name', $name)->first();
            if (!empty($duplicateItem)) {
                return response()->json(['message' => 'Name already exists'], 422);
            }
        }
        $msg = 'Created new group';

        try {
            if ($is_updating && !empty($item)) {
                $item->name = $name;
                $item->update();
                $msg = 'Updated group';
            } else {
                // add contact group to SMS API
                // $res = $this->smsApi->add_list($name);
                // if (!empty($res['id'])) {
                // $input['id'] = $res['id'];
                // $input['uid'] = $res['id'];
                $profile_id = $user->getActiveProfile();
                if (empty($profile_id)) {
                    $profile_id = $user->id;
                }
                $input['profile_id'] = $profile_id;
                $item = ContactGroup::create($input);
                $item->uid = $item->id;
                $item->save();
                // } else {
                // if (!empty($res['error']['description'])) {
                // return response()->json([
                // 'message' => $res['error']['description'],
                // ], 500);
                // } else {
                // return response()->json(['message' => 'Something went wrong'], 500);
                // }
                // }
            }
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'uid' => $item->id,
                    'name' => $item->name,
                    'createdBy' => 'Created by ' . $user->name . ' ' . $user->lastname,
                    'createdOn' => 'Created on ' . $item->created_at->format('jS \of F Y'),
                    'profile' => '',
                ],
                'reset' => true,
                'message' => $msg,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactGroup $contatGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactGroup $contatGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactGroup $contatGroup)
    {
        //
    }

    public function exportDownload(Request $req)
    {
        $user = $req->user();
        $filename = 'contact-groups-' . date('Y-m-d-H-i-s') . '.xlsx';
        $query = ContactGroup::query();
        $query = $query->withTrashed()->with('author:id,username')->orderBy('uid');
        return (new ContactGroupsExport($query))->download($filename);
    }

    public function delete(Request $req)
    {
        $id = $req->id;
        try {
            $item = ContactGroup::where('id', $id)->first();
            if ($item) {
                // $res = $this->smsApi->remove_list($item->uid);
                // if (!empty($res['error']) && !empty($res['code']) && $res['code'] !== 'SUCCESS') {
                //     $msg = $res['error'];
                //     return response()->json([
                //         'message' => !empty($msg['description']) ? $msg['description'] : 'Something went wrong',
                //     ], 500);
                // }
                $item->forceDelete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Deleted Contact Group',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactGroup $contatGroup)
    {
        //
    }
}
