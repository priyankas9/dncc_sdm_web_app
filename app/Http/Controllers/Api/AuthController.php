<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Login The User
     * @param Request $request
     * @return JsonResponse
     */

     public function login(Request $request)
     {
         try {
             // Validate the request
             $validateUser = Validator::make($request->all(), [
                 'email' => 'required|email',
                 'password' => 'required'
             ]);
     
             if ($validateUser->fails()) {
                 return response()->json([
                     'status' => false,
                     'message' => 'Validation Error',
                     'errors' => $validateUser->errors()
                 ], 401);
             }
     
             // Normalize email to lowercase
             $credentials = [
                 'email' => strtolower($request->input('email')),
                 'password' => $request->input('password')
             ];
     
             // Attempt authentication
             if (!Auth::attempt($credentials)) {
                 return response()->json([
                     'status' => false,
                     'message' => 'Email & Password do not match our records.',
                 ], 401);
             }
     
             // Get the authenticated user
             $user = Auth::user();
     
             // Define the roles to check
             $allowedRoles = [
                 'Super Admin',
                 'Municipality - Super Admin',
                 'Municipality - Building Surveyor',
                 'Municipality - Infrastructure Department',
                 'Service Provider - Emptying Operator',
                 'Municipality - Supervisory Assessment'
             ];
     
             // Check if the user has any of the allowed roles
             if (!$user->hasAnyRole($allowedRoles)) {
                 return response()->json([
                     'status' => false,
                     'message' => 'Unauthorized: You do not have the required role to log in.',
                 ], 403);
             }
     
             // Generate the token and return success response
             return response()->json([
                 'status' => true,
                 'message' => 'User Logged In Successfully.',
                 'token' => $user->createToken("API TOKEN")->plainTextToken,
                 'data' => [
                     "name" => $user->name,
                     "gender" => $user->gender,
                     "treatment_plant" => $user->treatment_plant->name ?? null,
                     "help_desk" => $user->help_desk->name ?? null,
                     "service_provider" => $user->service_provider->company_name ?? null,
                     "permissions" => [
                         "building-survey" => (bool)$user->can('Access Building Survey API'),
                         "save-emptying-service" => (bool)$user->can('Access Emptying Service API'),
                         "sewer-connection" => (bool)$user->can('Access Sewer Connection API'),
                        "supervisory-assessment" => (bool)$user->can('Access Supervisory Assessment API'),
                     ],
                 ]
             ]);
         } catch (\Throwable $th) {
             return response()->json([
                 'status' => false,
                 'message' => $th->getMessage()
             ], 500);
         }
     }
     

    public function logout()
    {
        try {
            Auth::user()->tokens()->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }
}
