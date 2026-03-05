<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Store contacts from user's device
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeContacts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'contacts' => 'required|array',
                'contacts.*.name' => 'nullable|string|max:255',
                'contacts.*.phone_number' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $contacts = $request->input('contacts');
            
            $savedCount = 0;
            
            foreach ($contacts as $contact) {
                // Normalize phone number (remove spaces, dashes, etc.)
                $normalizedPhone = preg_replace('/[^0-9+]/', '', $contact['phone_number']);
                
                // Store contact in database
                DB::table('user_contacts')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'phone_number' => $normalizedPhone
                    ],
                    [
                        'name' => $contact['name'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                
                $savedCount++;
            }
            
            // Update sync status
            DB::table('user_contact_sync')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'last_synced_at' => now(),
                    'total_contacts' => $savedCount,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Contacts saved successfully',
                'data' => [
                    'saved_count' => $savedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
