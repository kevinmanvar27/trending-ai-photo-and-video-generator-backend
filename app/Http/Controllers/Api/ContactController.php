<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Store device contacts from Flutter app
     * Accepts bulk contacts and syncs them to database
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone_number' => 'required|string|max:20',
            'contacts.*.email' => 'nullable|email|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $contacts = $request->contacts;
            
            $syncedCount = 0;
            $skippedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($contacts as $index => $contactData) {
                try {
                    // Use updateOrCreate to avoid duplicates
                    Contact::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'phone_number' => $contactData['phone_number'],
                        ],
                        [
                            'name' => $contactData['name'] ?? null,
                            'email' => $contactData['email'] ?? null,
                            'is_synced' => true,
                        ]
                    );
                    
                    $syncedCount++;
                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = [
                        'index' => $index,
                        'phone_number' => $contactData['phone_number'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Contacts synced successfully',
                'data' => [
                    'total_received' => count($contacts),
                    'synced' => $syncedCount,
                    'skipped' => $skippedCount,
                    'errors' => $errors,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all synced contacts for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $contacts = Contact::where('user_id', $user->id)
                ->orderBy('name')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $contacts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all contacts for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAll(Request $request)
    {
        try {
            $user = $request->user();
            
            $deletedCount = Contact::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All contacts deleted successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
