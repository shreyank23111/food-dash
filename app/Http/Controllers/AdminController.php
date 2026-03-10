<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\RestaurantApprovedMail;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id() ?? session('account_id');

        // Admin Security Check
        $isAdmin = DB::table('admin')->where('admin_id', $userId)->exists();
        if (!$isAdmin) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        $tab = $request->query('tab', 'overview');
        $data = ['activeTab' => $tab];

        if ($tab == 'overview') {
            $data['totalRequests'] = DB::table('restaurant_request')->where('status', 'Pending')->count();
            $data['totalRestaurants'] = DB::table('RESTAURANT')->count(); // Changed from totalUsers
        } 
        elseif ($tab == 'requests') {
            $data['pendingRequests'] = DB::table('restaurant_request')
                ->join('RESTAURANT', 'restaurant_request.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->where('restaurant_request.status', 'Pending')
                ->select('restaurant_request.*', 'RESTAURANT.name')
                ->get();
        } 
        elseif ($tab == 'all_restaurants') {
            // NEW: Fetch all restaurants and join the accounts table to get the Owner's Name
            $data['restaurants'] = DB::table('RESTAURANT')
                ->join('accounts', 'RESTAURANT.owner_id', '=', 'accounts.account_id')
                ->select('RESTAURANT.*', 'accounts.first_name', 'accounts.last_name')
                ->get();
        } 
        elseif ($tab == 'payments') {
            // NEW: Massive Database Join to get From (Customer) and To (Restaurant)
            $data['payments'] = DB::table('payment')
                ->join('orders', 'payment.order_id', '=', 'orders.order_id')
                ->join('accounts', 'orders.user_id', '=', 'accounts.account_id')
                ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->select(
                    'payment.*', 
                    'accounts.first_name as user_fname', 
                    'accounts.last_name as user_lname',
                    'RESTAURANT.name as restaurant_name'
                )
                ->orderBy('payment.payment_date', 'desc')
                ->get();
        } 
        elseif ($tab == 'logs') {
            $data['logs'] = DB::table('action_log')
                ->join('accounts', 'action_log.admin_id', '=', 'accounts.account_id')
                ->select('action_log.*', 'accounts.first_name', 'accounts.last_name')
                ->orderBy('action_date', 'desc')
                ->get();
        }

        return view('admin_dashboard', $data);
    }

    // Approval of restaurant
    public function approveRestaurant($reqId, $resId) 
    {
        $currentAdminId = Auth::id() ?? session('account_id');

        // 1. stored procedure to update database
        DB::statement('CALL Admin_Approve_Restaurant(?, ?, ?)', [$reqId, $resId, $currentAdminId]);

        // restaurant Owner's email and name using a DB Join
        $ownerData = DB::table('RESTAURANT')
            ->join('accounts', 'RESTAURANT.owner_id', '=', 'accounts.account_id')
            ->where('RESTAURANT.restaurant_id', $resId)
            ->select('accounts.email', 'accounts.first_name', 'RESTAURANT.name as restaurant_name')
            ->first();

        // approval email
        if ($ownerData && $ownerData->email) {
            Mail::to($ownerData->email)->send(new RestaurantApprovedMail($ownerData->first_name, $ownerData->restaurant_name));
        }

        // 4. Redirect back to the requests tab
        return redirect('/admin/dashboard?tab=requests')->with('success', 'Restaurant approved and notification email sent!');
    }
}