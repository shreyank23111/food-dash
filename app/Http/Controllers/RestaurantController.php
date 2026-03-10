<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RestaurantController extends Controller
{
    public function showRegisterForm() {
        return view('restaurant_register');
    }

  public function storeRequest(Request $request) {
        // 1. Validate the image upload
        $request->validate([
            'restaurant_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // max 2MB
        ]);

        /* ... Your exact existing storeRequest logic ... */
        $pincodeExists = DB::table('POSTCODE')->where('pincode', $request->postcode)->exists();
        if (!$pincodeExists) return back()->with('error', 'Sorry, our service is not available in area ' . $request->postcode);

        // 2. Send image to Cloudinary BEFORE starting the database transaction
        $imageUrl = null;
        if ($request->hasFile('restaurant_image')) {
            $imageUrl = cloudinary()->upload($request->file('restaurant_image')->getRealPath())->getSecurePath();
        }

        // 3. CRITICAL: Notice we added `$imageUrl` to the `use ($request, $imageUrl)` closure!
        $accountId = DB::transaction(function () use ($request, $imageUrl) {
            $names = explode(' ', $request->owner_name, 2);
            $id = DB::table('accounts')->insertGetId([
                'first_name' => $names[0],
                'last_name' => $names[1] ?? '',
                'email' => $request->email,
                'phone' => $request->personal_phone,
                'password' => Hash::make($request->password),
            ]);

            DB::table('RESTAURANT_OWNER')->insert(['owner_id' => $id]);

            $resId = DB::table('RESTAURANT')->insertGetId([
                'name' => $request->restaurant_name,
                'phone' => $request->business_phone,
                'preparation_time' => $request->prep_time,
                'owner_id' => $id,
                'status' => 'Closed',
                'image_url' => $imageUrl // 4. Save the Cloudinary link to the database here!
            ]);

            DB::table('RESTAURANT_ADDRESS')->insert([
                'restaurant_id' => $resId,
                'building_name' => $request->building_name,
                'street' => $request->street,
                'postcode' => $request->postcode
            ]);

            if ($request->has('cuisines')) {
                foreach ($request->cuisines as $cId) {
                    DB::table('RESTAURANT_CUISINE')->insert(['restaurant_id' => $resId, 'cuisine_id' => $cId]);
                }
            }

            DB::table('restaurant_request')->insert([
                'restaurant_id' => $resId,
                'request_date' => now(),
                'status' => 'Pending'
            ]);
            return $id;
        });

        Auth::loginUsingId($accountId);
        return redirect('/restaurant/dashboard')->with('success', 'Registration submitted! Your restaurant is pending admin approval.');
    }

    public function addFoodItem(Request $request) {
        $request->validate([
            'restaurant_id' => 'required|integer',
            'food_name' => 'required|string',
            'price' => 'required|numeric',
            'is_veg' => 'required|boolean',
        ]);
        $availabilityValue = ($request->availability == 'Available') ? 1 : 0;
        DB::table('FOOD_ITEM')->insert([
            'restaurant_id' => $request->restaurant_id,
            'food_name' => $request->food_name,
            'description' => $request->description,
            'availability' => $availabilityValue,
            'is_veg' => $request->is_veg,
            'price' => $request->price
        ]);
        return redirect('/restaurant/dashboard?tab=add_food')->with('success', 'Food item added!');
    }

    // --- MOVED FROM WEB.PHP ---
    public function dashboard(Request $request) {
        $tab = $request->query('tab', 'restaurants'); 
        $userId = Auth::id();

        $isOwner = DB::table('RESTAURANT_OWNER')->where('owner_id', $userId)->exists();
        if (!$isOwner) {
            return redirect('/dashboard');
        }

        $restaurants = DB::table('RESTAURANT')
            ->leftJoin('restaurant_request', 'RESTAURANT.restaurant_id', '=', 'restaurant_request.restaurant_id')
            ->where('RESTAURANT.owner_id', Auth::id())
            ->select('RESTAURANT.*', 'restaurant_request.status as approval_status')
            ->get();

        $menuItems = collect();
        $incomingOrders = collect();
        $orderHistory = collect();

        if ($tab == 'manage_orders') {
            $incomingOrders = DB::table('orders')
                ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->join('accounts', 'orders.user_id', '=', 'accounts.account_id')
                ->where('RESTAURANT.owner_id', $userId)
                ->select('orders.*', 'RESTAURANT.name as restaurant_name', 'accounts.first_name as customer_name')
                ->orderBy('orders.order_date', 'desc')
                ->get();
        }

       if ($tab == 'view_menu') {
          
            $menuQuery = DB::table('FOOD_ITEM')
                ->join('RESTAURANT', 'FOOD_ITEM.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->where('RESTAURANT.owner_id', $userId)
                ->select('FOOD_ITEM.*', 'RESTAURANT.name as restaurant_name');

            // Filters 
            if ($request->filled('filter')) {
                if ($request->filter == 'veg') $menuQuery->where('FOOD_ITEM.is_veg', 1);
                if ($request->filter == 'non_veg') $menuQuery->where('FOOD_ITEM.is_veg', 0);
                if ($request->filter == 'available') $menuQuery->where('FOOD_ITEM.availability', 1);
                if ($request->filter == 'out_of_stock') $menuQuery->where('FOOD_ITEM.availability', 0);
            }

            //Sorting
            if ($request->filled('sort')) {
                if ($request->sort == 'price_asc') $menuQuery->orderBy('FOOD_ITEM.price', 'asc');
                if ($request->sort == 'price_desc') $menuQuery->orderBy('FOOD_ITEM.price', 'desc');
                if ($request->sort == 'name_asc') $menuQuery->orderBy('FOOD_ITEM.food_name', 'asc');
            } else {
                //sorting (newest items first)
                $menuQuery->orderBy('FOOD_ITEM.food_id', 'desc');
            }

        
            $menuItems = $menuQuery->get();
        }

      // History with Pagination
        if ($tab == 'history') {
            $orderHistory = DB::table('orders')
                ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->join('accounts', 'orders.user_id', '=', 'accounts.account_id')
                ->where('RESTAURANT.owner_id', $userId)
                ->whereIn('orders.status', ['Delivered', 'Cancelled'])
                ->select('orders.*', 'RESTAURANT.name as restaurant_name', 'accounts.first_name as customer_name')
                ->orderBy('orders.order_date', 'desc')
                ->paginate(10) 
                ->appends(['tab' => 'history']); 
        }

        $totalRevenue = DB::table('payment')
            ->join('orders', 'payment.order_id', '=', 'orders.order_id')
            ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
            ->where('RESTAURANT.owner_id', $userId)
            ->where('payment.payment_status', 'Success')
            ->sum('payment.amount');

        // 2. Orders Today (Using Carbon to filter by today's date)
        $ordersToday = DB::table('orders')
            ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
            ->where('RESTAURANT.owner_id', $userId)
            ->whereDate('orders.order_date', \Carbon\Carbon::today())
            ->count();

        // 3. Active Orders (Counting only 'Placed' and 'Preparing' orders)
        $activeOrdersCount = DB::table('orders')
            ->join('RESTAURANT', 'orders.restaurant_id', '=', 'RESTAURANT.restaurant_id')
            ->where('RESTAURANT.owner_id', $userId)
            ->whereIn('orders.status', ['Placed', 'Preparing'])
            ->count();
                
        return view('restaurant_dashboard', [
            'restaurants' => $restaurants, 
            'activeTab' => $tab,
            'menuItems' => $menuItems, 
            'incomingOrders' => $incomingOrders,
            'orderHistory' => $orderHistory,
            'totalRevenue' => $totalRevenue,
            'ordersToday' => $ordersToday,
            'activeOrdersCount' => $activeOrdersCount
        ]);
    }

    public function updateOrderStatus(Request $request, $id) {
        $status = $request->status;
        DB::statement('CALL Update_Order_Status(?, ?)', [$id, $request->status]);
        return redirect('/restaurant/dashboard?tab=manage_orders')->with('success', 'Order status updated!');
    }

    public function toggleMenuStatus($id) {
        $item = DB::table('food_item')->where('food_id', $id)->first();
        if ($item) {
            $newStatus = $item->availability == 1 ? 0 : 1;
            DB::table('food_item')->where('food_id', $id)->update(['availability' => $newStatus]);
        }
        return back()->with('success', 'Availability updated!');
    }

    public function updateMenuPrice(Request $request, $id) {
        $request->validate(['new_price' => 'required|numeric|min:1']);
        DB::table('food_item')->where('food_id', $id)->update(['price' => $request->new_price]);
        return back()->with('success', 'Price updated successfully!');
    }

    // Trigger: res open/close
    public function toggleRestaurantStatus($id) {
        $userId = Auth::id();
        
        // Ensure the restaurant belongs to the logged-in owner
        $restaurant = DB::table('RESTAURANT')
            ->where('restaurant_id', $id)
            ->where('owner_id', $userId)
            ->first();

        if ($restaurant) {
            $newStatus = $restaurant->status === 'Open' ? 'Closed' : 'Open';
            DB::table('RESTAURANT')->where('restaurant_id', $id)->update(['status' => $newStatus]);
            return back()->with('success', 'Restaurant is now ' . $newStatus . '!');
        }

        return back()->with('error', 'Unauthorized action.');
    }

}