<?php

namespace App\Imports;

use App\Models\Contact;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
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
        } catch (Exception $e) {

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

    private function parseExcelDate($value)
    {
        // 1) Excel serial number (only valid for >= 1900)
        if (is_numeric($value) && $value > 0) {
            // Excel's serial date system cannot represent years < 1900
            // So convert only if serial corresponds to >= 1900
            $date = Carbon::createFromDate(1899, 12, 30)->addDays($value);
            if ($date->year >= 1900) {
                return $date;
            }
        }

        // 2) String date in d-m-Y (works for old dates like 1789)
        try {
            return Carbon::createFromFormat('d-m-Y', $value);
        } catch (Exception $e) {
            return null; // invalid date
        }
    }

    /**
     * Make sure index 3 is of Phone number column and 0 for ID
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
            // ignore the headers of sheet
            if ($key == 0)
                continue;

            $id = $row[0]; // A
            $name = $row[1]; // B
            $lastname = $row[2]; // C
            $phone = $row[3]; // D
            $email = $row[4]; // E
            $country = $row[5]; // F
            $company = $row[6]; // G
            $groupIds = $row[7]; // H
            $dob = $row[8]; // I
            $member_uid = $row[9]; // J
            $member_category_name = $row[10]; // K
            $comments = $row[11]; // L

            // clean
            $name = trim($name);
            $name = !empty($name) ? $name : 'NO NAME';
            $phone = $phone ?? '';
            $email = Str::lower($email ?? '');
            $contact_staus = 'PUBLISHED';
            $country = !empty($country) ? strtoupper($country) : 'AU';
            $groupIds = !empty($groupIds) ? explode(',', $groupIds) : [];
            $dob = $this->parseExcelDate($dob);

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
                'email' => $email,
                'dob' => $dob,
                'member_uid' => $member_uid,
                'member_category_name' => $member_category_name,
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
                $old = Contact::select(['id', 'phone'])->where('id', $id)->first();
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
