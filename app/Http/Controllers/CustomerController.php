<?php

namespace App\Http\Controllers;

use App\Contracts\CustomerServiceInterface;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerServiceInterface $customerService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/customer/",
     *     summary="List info customer",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data of customer",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile with favorites successfully obtained"),
     *             @OA\Property(property="customer", type="object"),
     *             @OA\Property(property="favorites", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function profile(): JsonResponse
    {
        try {
            $customer = $this->customerService->getProfile();

            return response()->json([
                'customer' => $customer['customer'],
                'favorites' => $customer['favorites'],
                'message' => 'Profile with favorites successfully obtained'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/customer",
     *     summary="Update consumer",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profile updated successfull",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile updated successfull"),
     *             @OA\Property(property="customer", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $customer = $this->customerService->updateProfile($request->validated());

            return response()->json([
                'message' => 'Profile updated successfully',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 
     *   @OA\Get(
     *     path="/api/customer/all",
     *     summary="Get all customer",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data of customer",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile with favorites successfully obtained"),
     *             @OA\Property(property="customers", type="object"),
     *             @OA\Property(property="total", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $customers = $this->customerService->getAllCustomers();

            return response()->json([
                'customers' => $customers,
                'total' => count($customers),
                'message' => 'Customers successfully listed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error listing customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** 
     *  @OA\Delete(
     *     path="/api/customer/{id}",
     *     summary="Delete customer",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer 'Marica Consuelos' successfully deleted. 0 favorites removed."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ) 
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $response = $this->customerService->deleteCustomer($id);

            return response()->json($response->toArray());
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Customer not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}