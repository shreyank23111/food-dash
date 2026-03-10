<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::get('/', function () { return view('welcome'); });

// Authentication
Route::get('/signup', [AuthController::class, 'showSignup']);
Route::post('/signup-process', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login-process', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

// Restaurant Registration (Partner with Us)
Route::get('/restaurant/register', [RestaurantController::class, 'showRegisterForm']);
Route::post('/restaurant/register-process', [RestaurantController::class, 'storeRequest']);

// --- Authenticated Routes (Protected by Auth Middleware) ---
Route::middleware(['auth'])->group(function () {

    // 1. ADMIN ROUTES
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/requests', [AdminController::class, 'showRequests']);
        Route::post('/approve/{reqId}/{resId}', [AdminController::class, 'approveRestaurant']);
        Route::get('/logs', [AdminController::class, 'showLogs'])->name('admin.logs');
    });

    // 2. RESTAURANT OWNER ROUTES
    Route::prefix('restaurant')->group(function () {
        Route::get('/dashboard', [RestaurantController::class, 'dashboard'])->name('restaurant.dashboard');
        Route::post('/add-food', [RestaurantController::class, 'addFoodItem'])->name('food.store');
        Route::post('/order/update/{id}', [RestaurantController::class, 'updateOrderStatus']);
        Route::post('/menu/toggle-status/{id}', [RestaurantController::class, 'toggleMenuStatus']);
        Route::post('/menu/update-price/{id}', [RestaurantController::class, 'updateMenuPrice']);
        Route::post('/toggle/{id}', [RestaurantController::class, 'toggleRestaurantStatus']);
    });

    // 3. CUSTOMER DASHBOARD & ACTIONS
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/my-addresses', [UserController::class, 'showAddresses'])->name('user.addresses');
    Route::post('/add-address', [UserController::class, 'storeAddress'])->name('address.store');
    Route::post('/order/cancel/{id}', [UserController::class, 'cancelOrder']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    
    // Cart & Orders
    Route::post('/cart/add/{id}', [UserController::class, 'addToCart']);
    Route::post('/cart/remove/{id}', [UserController::class, 'removeFromCart']);
    Route::post('/cart/update/{id}', [UserController::class, 'updateCart']);
    Route::post('/cart/clear', [UserController::class, 'clearCart']);
    Route::post('/place-order', [UserController::class, 'placeOrder']);
    Route::post('/submit-review', [UserController::class, 'submitReview']);
    Route::post('/order/reorder/{id}', [UserController::class, 'reorder']);
    Route::post('/restaurant/favorite/{id}', [UserController::class, 'toggleFavorite']);
});