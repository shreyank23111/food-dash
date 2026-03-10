<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // display address
    public function showAddresses() {
        $addresses = DB::table('user_address')
            ->join('postcode', 'user_address.postcode', '=', 'postcode.pincode')
            ->where('user_id', session('account_id'))
            ->get();
        return view('user_addresses', compact('addresses'));
    }
    
    // store new user address
    public function storeAddress(Request $request) {
        $userId = Auth::id() ?: session('account_id');
        if (!$userId) return back()->with('error', 'Session expired. Please login again.');

        $pincodeExists = DB::table('postcode')->where('pincode', $request->postcode)->exists();
        if (!$pincodeExists) return back()->with('error', 'Sorry, we currently do not provide delivery at this location (Pincode: ' . $request->postcode . ').');

        DB::table('user_address')->insert([
            'user_id' => $userId, 
            'building_name' => $request->building_name,
            'street' => $request->street,
            'postcode' => $request->postcode,
            'address_type' => $request->address_type ?? 'Home'
        ]);
        return back()->with('success', 'Address saved successfully!');
    }

        // user dashboard
    public function dashboard(Request $request) {
        $userId = Auth::id() ?? session('account_id');
        // $tab = $request->query('tab', 'profile');
        $tab = $request->query('tab', 'browse_food');

        if (DB::table('admin')->where('admin_id', $userId)->exists()) return redirect('/admin/dashboard');
        if (session('user_role') === 'owner' || DB::table('RESTAURANT_OWNER')->where('owner_id', $userId)->exists()) return redirect('/restaurant/dashboard');

        // $data = [
        //     'activeTab' => $tab,
        //     'restaurants' => collect(),
        //     'menuItems' => collect(),
        //     'cartItems' => session()->get('cart', []),
        //     'currentOrders' => collect(), 
        //     'pastOrders' => collect(),
        //     'addresses' => collect(),
        //     'myRestaurants' => collect(),
        //     'total' => 0,
        //     'selectedRestaurant' => null
        // ];

        $data = [
            'activeTab' => $tab,
            'userProfile' => DB::table('accounts')->where('account_id', $userId)->first(), 
            'restaurants' => collect(),
            'menuItems' => collect(),
            'cartItems' => session()->get('cart', []),
            'currentOrders' => collect(), 
            'pastOrders' => collect(),
            'addresses' => collect(),
            'myRestaurants' => collect(),
            'total' => 0,
            'selectedRestaurant' => null
        ];

        if ($tab == 'cart' || $tab == 'payment') {
            foreach ($data['cartItems'] as $item) {
                $data['total'] += $item['price'] * $item['quantity'];
            }

            if (count($data['cartItems']) > 0) {
                $firstItem = reset($data['cartItems']);
                $restaurant = DB::table('RESTAURANT')
                    ->where('restaurant_id', $firstItem['restaurant_id'])
                    ->select('name')
                    ->first();
                
                $data['cartRestaurantName'] = $restaurant ? $restaurant->name : 'Selected Restaurant';
            } else {
                $data['cartRestaurantName'] = null;
            }
        }

        // browse food with ratings and address

       // browse food with Search and Session-Based Favorites
        if ($tab == 'browse_food') {
            
            // 🚨 THE FIX: Join the main RESTAURANT table to grab the newly added image_url column!
            $query = DB::table('View_Available_Restaurants')
                ->join('RESTAURANT', 'View_Available_Restaurants.restaurant_id', '=', 'RESTAURANT.restaurant_id')
                ->select('View_Available_Restaurants.*', 'RESTAURANT.image_url');

            // --- 1. THE GLOBAL SEARCH LOGIC ---
            if ($request->filled('search')) {
                // We specify 'View_Available_Restaurants.name' so MySQL doesn't get confused about which table to search
                $query->where(function($q) use ($request) {
                    $q->where('View_Available_Restaurants.name', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('View_Available_Restaurants.street', 'LIKE', '%' . $request->search . '%');
                });
            }

            // Fetch the results
            $allRestaurants = $query->get();
            
            // Pass the session array to the view so it knows which hearts to color red
            $data['myFavorites'] = session()->get('favorites', []);

            // --- 2. THE FAVORITES FILTER LOGIC ---
            if ($request->filled('filter') && $request->filter == 'favorites') {
                // Filter the results in PHP instead of SQL
                $data['restaurants'] = $allRestaurants->whereIn('restaurant_id', $data['myFavorites']);
            } else {
                $data['restaurants'] = $allRestaurants;
            }
        }

        // active and previous orders
        if ($tab == 'active_orders' || $tab == 'order_history') {
            $currentMonth = now()->month;
            $currentYear = now()->year;

        // view: user stats

            $stats = DB::table('View_User_Monthly_Stats')
                ->where('user_id', $userId)
                ->where('month_num', $currentMonth)
                ->where('year_num', $currentYear)
                ->first();

            $data['monthlyOrders'] = $stats ? $stats->total_orders : 0;
            $data['monthlySpend'] = $stats ? $stats->total_spent : 0;
            
            // top res
            $data['topRestaurant'] = DB::table('orders')
                ->join('restaurant', 'orders.restaurant_id', '=', 'restaurant.restaurant_id')
                ->where('orders.user_id', $userId)
                ->select('restaurant.name', DB::raw('COUNT(orders.order_id) as freq'))
                ->groupBy('restaurant.restaurant_id', 'restaurant.name')
                ->orderBy('freq', 'desc')
                ->first();


            if ($tab == 'active_orders') {
                $data['currentOrders'] = DB::table('Active_Delivery_Tracking')
                    ->where('user_id', $userId)
                    ->get();
            } else {
                $data['pastOrders'] = DB::table('orders')
                    ->where('user_id', $userId)
                    ->whereIn('status', ['Delivered', 'Cancelled'])
                    ->orderBy('order_date', 'desc')
                    ->get();
            }
        }

        elseif ($tab == 'payment') {
       
            $data['addresses'] = DB::table('user_address')->join('postcode', 'user_address.postcode', '=', 'postcode.pincode')->where('user_id', $userId)->get();
        }

        // reviews
        elseif ($tab == 'write_review') {
            $orderId = $request->query('order_id');
            $data['orderItems'] = DB::table('order_item')->join('food_item', 'order_item.food_id', '=', 'food_item.food_id')->where('order_item.order_id', $orderId)->get();
            $data['orderId'] = $orderId;
        }


        elseif ($tab == 'addresses') {
            $data['addresses'] = DB::table('user_address')->join('postcode', 'user_address.postcode', '=', 'postcode.pincode')->where('user_id', $userId)->get();
        } 

        //display restaurants with sorting
        elseif ($tab == 'view_restaurant') {
            $resId = $request->query('res_id');
            $restaurant = DB::table('RESTAURANT')->where('restaurant_id', $resId)->first();
            
            // check
            if (!$restaurant) return redirect('/dashboard?tab=browse_food')->with('error', 'Restaurant not found.');
            
            $data['selectedRestaurant'] = $restaurant;

      
            $menuQuery = DB::table('FOOD_ITEM')->where('restaurant_id', $resId);

            // Filters (Veg, Non-Veg, Available)
            if ($request->filled('filter')) {
                if ($request->filter == 'veg') $menuQuery->where('is_veg', 1);
                if ($request->filter == 'non_veg') $menuQuery->where('is_veg', 0);
                if ($request->filter == 'available') $menuQuery->where('availability', 1);
            }

            // Sorting
            if ($request->filled('sort')) {
                if ($request->sort == 'price_asc') $menuQuery->orderBy('price', 'asc');
                if ($request->sort == 'price_desc') $menuQuery->orderBy('price', 'desc');
                if ($request->sort == 'name_asc') $menuQuery->orderBy('food_name', 'asc');
            } else {
                $menuQuery->orderBy('food_id', 'desc'); // Default fallback
            }

            
            $data['menuItems'] = $menuQuery->get();
        }
        return view('dashboard', $data);
    }

    // items to food cart
   public function addToCart(Request $request, $id) {
        $qty = $request->input('quantity', 1);
        $food = DB::table('FOOD_ITEM')->where('food_id', $id)->first();

        if (!$food) return redirect()->back()->with('error', 'Item not found!');
        
        $cart = session()->get('cart', []);

        // order from single restaurant check
        if (!empty($cart)) {
           
            $firstItem = reset($cart); 
            $currentCartRestaurantId = $firstItem['restaurant_id'];

        
            if ($currentCartRestaurantId != $food->restaurant_id) {
                return redirect()->back()->with('error', 'You can only order from one restaurant at a time. Please clear your cart first!');
            }
        }

        // adding to cart
        if(isset($cart[$id])) {
            $cart[$id]['quantity'] += $qty;
        } else {
            $cart[$id] = [
                "name" => $food->food_name,
                "quantity" => $qty,
                "price" => $food->price,
                "restaurant_id" => $food->restaurant_id
            ];
        }
        session()->put('cart', $cart);
        return redirect()->back()->with('success', $qty . ' item(s) added to cart!');
    }

    // deleting from cart
    public function removeFromCart($id) {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Item removed from cart!');
    }

// updating cart
    public function updateCart(Request $request, $id) {
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Quantity updated!');
    }

    // clear existing cart
    public function clearCart() {
        session()->forget('cart');
        return redirect()->back()->with('success', 'Cart has been cleared! You can now order from a new restaurant.');
    }

// place order
    public function placeOrder(Request $request) {
        $cart = session()->get('cart', []);
        if (empty($cart)) return back()->with('error', 'Cart is empty');
        $userId = Auth::id() ?? session('account_id');

      try {
            DB::beginTransaction();
            $totalAmount = 0;
            foreach ($cart as $item) { $totalAmount += $item['price'] * $item['quantity']; }
            
            $deliveryFee = 40;
            $grandTotal = $totalAmount + $deliveryFee;
            $restaurantId = reset($cart)['restaurant_id'];

            // 1. Insert Order
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'restaurant_id' => $restaurantId,
                'address_id' => $request->address_id,
                'order_date' => now(),
                'total_amount' => $grandTotal,
                'status' => 'Placed'
            ]);

            // used trigger for delivery

            //  Insert Order Items
            foreach ($cart as $foodId => $details) {
                DB::table('order_item')->insert([
                    'order_id' => $orderId,
                    'food_id' => $foodId,
                    'quantity' => $details['quantity'],
                    'price' => $details['price']
                ]);
            }

            // Insert Payment
            DB::table('payment')->insert([
                'order_id' => $orderId,
                'amount' => $grandTotal,
                'payment_mode' => $request->input('method', 'UPI'),
                'payment_status' => 'Success',
                'transaction_id' => 'TXN-' . strtoupper(bin2hex(random_bytes(6))),
                'payment_date' => now()
            ]);

            DB::commit();
            session()->forget('cart');
            return redirect('/dashboard?tab=active_orders')->with('success', 'Order Placed Successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return "DATABASE ERROR: " . $e->getMessage();
        }
    }

    // review for restaurant and order item
    public function submitReview(Request $request) {
        DB::transaction(function () use ($request) {
            $reviewId = DB::table('review')->insertGetId([
                'order_id' => $request->order_id,
                'review_date' => now()
            ]);

            DB::table('review_item')->insert([
                'review_id' => $reviewId,
                'item_type' => 'Restaurant',
                'item_id' => $request->restaurant_id,
                'rating' => $request->restaurant_rating,
                'comment' => $request->restaurant_comment
            ]);

            foreach ($request->item_rating as $foodId => $rating) {
                DB::table('review_item')->insert([
                    'review_id' => $reviewId,
                    'item_type' => 'Food',
                    'item_id' => $foodId,
                    'rating' => $rating,
                    'comment' => $request->item_comment[$foodId]
                ]);
            }
        });
        return redirect('/dashboard?tab=order_history')->with('success', 'Thank you for your feedback!');
    }

    // cancel order
    public function cancelOrder($id) {
        $userId = Auth::id() ?? session('account_id');
        
        try {
            // Stored Procedure: for canceling
            DB::statement('CALL Safe_Customer_Cancel(?, ?)', [$id, $userId]);
            return redirect()->back()->with('success', 'Your order was successfully cancelled and refunded.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot cancel order. It may already be preparing.');
        }
    }

    //update user profile
    public function updateProfile(Request $request) {
        $userId = Auth::id() ?? session('account_id');

        // validate 
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:15',
        ]);

        // update the database
        DB::table('accounts')->where('account_id', $userId)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
        ]);

        session(['user_name' => $request->first_name . ' ' . $request->last_name]);

        return redirect('/dashboard?tab=profile')->with('success', 'Profile updated successfully!');
    }

    // toggle favorite Restaurant 
    public function toggleFavorite($resId) {
        // existing favorites 
        $favorites = session()->get('favorites', []);
        
        if (in_array($resId, $favorites)) {
            // if it's already a favorite, remove it
            $favorites = array_diff($favorites, [$resId]);
            $msg = 'Removed from favorites 💔';
        } else {
            // If it's not a favorite, add it
            $favorites[] = $resId;
            $msg = 'Added to favorites ❤️';
        }

        session()->put('favorites', $favorites);
        return back()->with('success', $msg);
    }

    // reorder
    // public function reorder($orderId) {
    //     $userId = Auth::id() ?? session('account_id');

    //     // 1. Verify the order belongs to this user
    //     $order = DB::table('orders')
    //         ->where('order_id', $orderId)
    //         ->where('user_id', $userId)
    //         ->first();

    //     if (!$order) return back()->with('error', 'Order not found.');

    //     // 2. Fetch the items from this past order, joined with the LIVE FOOD_ITEM table
    //     $orderItems = DB::table('order_item')
    //         ->join('FOOD_ITEM', 'order_item.food_id', '=', 'FOOD_ITEM.food_id')
    //         ->where('order_item.order_id', $orderId)
    //         ->select('order_item.quantity', 'FOOD_ITEM.*')
    //         ->get();

    //     if ($orderItems->isEmpty()) {
    //         return back()->with('error', 'Could not find items for this order.');
    //     }

    //     // 3. Rebuild the cart array
    //     $cart = [];
    //     $unavailableItems = false;

    //     foreach ($orderItems as $item) {
    //         // add items that are still available
    //         if ($item->availability == 1) {
    //             $cart[$item->food_id] = [
    //                 "name" => $item->food_name,
    //                 "quantity" => $item->quantity,
    //                 "price" => $item->price, //  current price, not the old price
    //                 "restaurant_id" => $item->restaurant_id
    //             ];
    //         } else {
    //             $unavailableItems = true;
    //         }
    //     }

    //     // if items ordered is now out of stock
    //     if (empty($cart)) {
    //         return back()->with('error', 'Sorry! All items from this past order are currently out of stock.');
    //     }

    //     session()->put('cart', $cart);

        
    //     $msg = 'Cart updated with your past order!';
    //     if ($unavailableItems) {
    //         $msg .= ' (Note: Some items were out of stock and skipped).';
    //     }

        
    //     return redirect('/dashboard?tab=cart')->with('success', $msg);
    // }
}