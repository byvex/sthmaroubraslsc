<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\SenderNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SenderNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $current_user = $req->user();
        $query = SenderNumber::query();
        $items = $query->paginate();

        return view('sender-numbers.index', [
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
        return view('sender-numbers.create', [
            'current_user' => $current_user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $currentUser = $req->user();
        if ($currentUser->isUser()) {
            return response()->json(['message' => 'You are not authorized to create sender number'], 403);
        }

        $input = $req->validate([
            'phone' => ['required', 'numeric', Rule::unique(SenderNumber::class)],
        ]);

        try {
            $item = SenderNumber::create($input);
            return response()->json([
                'success'=> true,
                'redirect' => route('sender-numbers.edit', $item->id),
                'message' => 'Sender number created',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SenderNumber $senderNumber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SenderNumber $senderNumber, Request $req)
    {
        $current_user = $req->user();
        return view('sender-numbers.edit', [
            'item' => $senderNumber,
            'current_user' => $current_user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req, SenderNumber $senderNumber)
    {
        $currentUser = $req->user();
        if ($currentUser->isUser()) {
            return response()->json(['message' => 'You are not authorized to create sender number'], 403);
        }

        $input = $req->validate([
            'phone' => ['required', 'numeric', Rule::unique(SenderNumber::class)],
        ]);

        try {
            $senderNumber->update($input);
            return response()->json([
                'success'=> true,
                'message' => 'Sender number updated',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SenderNumber $senderNumber)
    {
        //
    }
}
