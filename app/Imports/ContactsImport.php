<?php

namespace App\Imports;

use App\Models\Contact;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class ContactsImport implements ToCollection
{
    public $duplicates = [];
    public $hasNewPhoneNumbers = [];
    public $totalContactsImported = 0;
    public $totalContactsUpdated = 0;
    protected $newPhoneNumbersAction = 'unknown';
    public $selectedGroupId = 0;
    public $profile_id = 1;

    public function __construct($selectedGroupId, $newPhoneNumbersAction)
    {
        $this->selectedGroupId = $selectedGroupId;
        $this->newPhoneNumbersAction = $newPhoneNumbersAction;
        try {
            $current_user = auth()->user();
            $this->profile_id = $current_user->getActiveProfile();
        } catch(Exception $e) {

        }
    }

    public function importContact($data, $oldData = [], $groupIds)
    {
        if (!empty($data)) {
            if (empty($oldData)) {
                $data['profile_id'] = $this->profile_id;
                $item = Contact::create($data);
                $item->groups()->sync($groupIds);
                $this->totalContactsImported = $this->totalContactsImported + 1;
            } else {
                $item = Contact::updateOrCreate($oldData, $data);
                $item->groups()->sync($groupIds);
                $this->totalContactsUpdated = $this->totalContactsUpdated + 1;
            }
        }
    }

    /**
     * Make sure index 3 is of Phone number column and 0 for ID
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            // ignore the headers of sheet
            if ($key == 0) continue;

            $id = $row[0];
            $name = $row[1];
            $name = trim($name);
            $name = !empty($name) ? $name : 'NO NAME';
            $lastname = $row[2];
            $phone = $row[3];
            $country = $row[4];
            $company = $row[5];
            $groupIds = $row[6];
            $comments = $row[7];
            $country = !empty($country) ? strtoupper($country) : 'AU';
            $contact_staus = 'PUBLISHED';
            $groupIds = !empty($groupIds) ? explode(',', $groupIds) : [];

            if (empty($groupIds) && !empty($this->selectedGroupId)) {
                $groupIds = [$this->selectedGroupId];
            }

            if (!empty($groupIds) && is_array($groupIds)) {
                $tempGroupIds = $groupIds;
                $groupIds = [];
                foreach ($tempGroupIds as $gId) {
                    $groupIds[] = trim($gId);
                }
            }

            $commonData = [
                'name' => $name,
                'lastname' => $lastname,
                'country' => $country,
                'company' => $company,
                'comments' => $comments,
                'status' => $contact_staus,
            ];
            $commonDataWithPhone = $commonData;
            $commonDataWithPhone['phone'] = $phone;


            if (!empty($id)) {
                // if ID is not empty
                $old = Contact::select(['id','phone'])->where('id', $id)->first();
                // then check if phone is different
                if (!empty($old)) {
                    $id = $old->id;
                    if ($old->phone != $phone) {
                        if ($this->newPhoneNumbersAction == 'unknown') {
                            // if decision is not made then collect ids
                            $this->hasNewPhoneNumbers[] = $id;
                        } elseif ($this->newPhoneNumbersAction == 'update') {
                            // update phone number with other details
                            $this->importContact($commonDataWithPhone, ['id' => $id], $groupIds);
                        } elseif ($this->newPhoneNumbersAction == 'ignore') {
                            // ignore phone number, update other columns
                            $this->importContact($commonData, ['id' => $id], $groupIds);
                        }
                    } else {
                        // same phone, update common data
                        if (in_array($this->newPhoneNumbersAction, ['update', 'ignore'])) {
                            $this->importContact($commonData, ['id' => $id], $groupIds);
                        }
                    }
                } else {
                    // do not exist in db, so create new
                    if (in_array($this->newPhoneNumbersAction, ['update', 'ignore'])) {
                        $this->importContact($commonDataWithPhone, [], $groupIds);
                    }
                }
            } else {
                if (in_array($this->newPhoneNumbersAction, ['update', 'ignore'])) {
                    // if empty ID then create new with all data
                    $this->importContact($commonDataWithPhone, [], $groupIds);
                }
            }
        }
    }
}
