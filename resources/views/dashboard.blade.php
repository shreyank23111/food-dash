@extends('layout')

@section('content')
<div class="flex min-h-screen w-full overflow-hidden">
    <div class="w-64 bg-slate-900 text-white flex-shrink-0 shadow-2xl">
        <div class="p-6">
            <h2 class="text-xl font-bold text-orange-500">FoodDash</h2>
            <p class="text-xs text-slate-400">User Control Panel</p>
        </div>
        <nav class="mt-4">
<a href="{{ url('/dashboard?tab=browse_food') }}" 
   class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'browse_food' ? 'bg-orange-500' : '' }}">
   🍴 Browse Restaurants
</a>
<a href="{{ url('/dashboard?tab=active_orders') }}" 
       class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'active_orders' ? 'bg-orange-500' : '' }}">
       🚚 Active Tracking
</a>

<a href="{{ url('/dashboard?tab=order_history') }}"
    class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'order_history' ? 'bg-orange-500' : '' }}">
    📜 Past Orders
</a>

<a href="/dashboard?tab=cart"
class="block px-8 py-4 hover:bg-slate-800 
   style="text-decoration: none; color: {{ $activeTab == 'cart' ? 'white' : '#94a3b8' }}; 
   padding: 10px; background: {{ $activeTab == 'cart' ? '#1e293b' : 'transparent' }}; 
   display: block; border-radius: 5px; margin-bottom: 5px;">
    🛒 My Cart ({{ count(session('cart', [])) }})
</a>


        
            <a href="?tab=addresses" class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'addresses' ? 'bg-orange-500' : '' }}">My Addresses</a>
            <a href="{{ route('user.dashboard') }}?tab=profile"
   class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'profile' ? 'bg-orange-500' : '' }}">
   👤 Edit Profile
</a>
            <div class="border-t border-slate-800 mt-6">
                <a href="/logout" class="block px-8 py-4 text-red-400 hover:bg-red-900/20">Logout</a>
            </div>
        </nav>
    </div>

    <div class="flex-1 bg-white min-h-screen">

    <div class="p-12 max-w-full">
        <header class="mb-10">
            <h1 class="text-3xl font-bold text-slate-800">Welcome, {{ session('user_name') }}</h1>
            <p class="text-slate-500">Manage your account and track your activity.</p>
        </header>

        <div class="bg-white rounded-3xl shadow-sm p-8 border border-slate-200">
                @if($activeTab == 'profile')
                <div style="max-width: 600px;">
                    <h2 class="text-2xl font-bold mb-6 text-slate-800">👤 Account Details</h2>
                    
                    <form action="/profile/update" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px;">First Name</label>
                                <input type="text" name="first_name" value="{{ $userProfile->first_name ?? '' }}" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px;">Last Name</label>
                                <input type="text" name="last_name" value="{{ $userProfile->last_name ?? '' }}" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px;">Email Address (Read Only)</label>
                            {{-- We disable the email input so they can't change it, as it acts as their login ID --}}
                            <input type="email" value="{{ $userProfile->email ?? '' }}" disabled style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f1f5f9; color: #94a3b8; cursor: not-allowed;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px;">Phone Number</label>
                            <input type="text" name="phone" value="{{ $userProfile->phone ?? '' }}" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;">
                        </div>

                        <button type="submit" style="margin-top: 10px; background: #f97316; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                            💾 Save Changes
                        </button>
                    </form>
                </div>



            @elseif($activeTab == 'addresses')
                @include('partials.address_manager') 

            @elseif($activeTab == 'restaurants')
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">My Restaurants</h2>
                    <a href="/restaurant/register" class="bg-orange-500 text-white px-4 py-2 rounded-lg font-bold">Register New</a>
                </div>
                
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-sm uppercase">
                            <th class="pb-4">Restaurant</th>
                            <th class="pb-4">Approval</th>
                            <th class="pb-4">System Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myRestaurants as $res)
                            <tr class="border-b">
                                <td class="py-4 font-bold">{{ $res->name }}</td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $res->request_status == 'Approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $res->request_status ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-4 text-sm">{{ $res->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 italic text-slate-400">No restaurants registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>


@elseif($activeTab == 'browse_food')
    <div style="padding: 10px;">
        <h2 style="margin-bottom: 20px; color: #1e293b;">Available Restaurants</h2>
        
        {{-- 🔍 THE NEW SEARCH AND FILTER BAR --}}
        <form action="/dashboard" method="GET" style="display: flex; gap: 10px; margin-bottom: 25px; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="browse_food">
            
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by restaurant name or area..." style="flex: 1; min-width: 200px; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 14px;">
            
            <select name="filter" style="padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-weight: bold; color: #475569;">
                <option value="">🍽️ All Restaurants</option>
                <option value="favorites" {{ request('filter') == 'favorites' ? 'selected' : '' }}>❤️ My Favorites Only</option>
            </select>

            <button type="submit" style="background: #1e293b; color: white; border: none; padding: 0 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                Search
            </button>
            <a href="/dashboard?tab=browse_food" style="display: flex; align-items: center; color: #ef4444; text-decoration: none; font-weight: bold; padding: 0 10px;">Clear</a>
        </form>

        {{-- RESTAURANT GRID --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            @forelse($restaurants as $res)
                <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative; opacity: {{ $res->status == 'Closed' ? '0.7' : '1' }};">
                    
                    {{-- ❤️ THE FAVORITE BUTTON --}}
                    <form action="/restaurant/favorite/{{ $res->restaurant_id }}" method="POST" style="position: absolute; top: 12px; right: 12px; z-index: 10; margin: 0;">
                        @csrf
                        <button type="submit" title="Toggle Favorite" style="background: white; border: none; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-size: 16px; transition: transform 0.2s;">
                            {{ in_array($res->restaurant_id, $myFavorites ?? []) ? '❤️' : '🤍' }}
                        </button>
                    </form>

                   {{-- Replace the 🥘 section in the browse_food tab with this: --}}
<div style="height: 140px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
    
    @if($res->image_url)
        {{-- Show Cloudinary Image --}}
        <img src="{{ $res->image_url }}" alt="{{ $res->name }}" style="width: 100%; height: 100%; object-fit: cover;">
    @else
        {{-- Fallback Emoji if no image exists --}}
        <span style="font-size: 40px;">🥘</span>
    @endif

    {{-- Show a 'Closed' banner over the image area if closed --}}
    @if($res->status == 'Closed')
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px; letter-spacing: 2px;">
            TEMPORARILY CLOSED
        </div>
    @endif
</div>

                    <div style="padding: 15px;">
                        <h4 style="margin: 0 0 5px 0; color: #1e293b; font-size: 18px;">{{ $res->name }}</h4>
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            📍 {{ $res->building_name }}, {{ $res->street }}
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <span>
                                @if($res->avg_rating)
                                    <span style="font-size: 13px; color: #555252; font-weight: bold;">⭐ {{ number_format($res->avg_rating, 1) }}</span>
                                @else
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: bold;">New (No ratings)</span>
                                @endif
                            </span>

                            <span style="font-size: 12px; color: {{ $res->status == 'Open' ? '#22c55e' : '#ef4444' }}; font-weight: bold;">
                                ● {{ $res->status }}
                            </span>
                        </div>

                        <div style="display: flex; justify-content: flex-end; align-items: center;">
                            @if($res->status == 'Open')
                                <a href="/dashboard?tab=view_restaurant&res_id={{ $res->restaurant_id }}" 
                                   style="background: #ff7a2d; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: bold; width: 100%; text-align: center; transition: 0.2s;">
                                    View Menu
                                </a>
                            @else
                                <span style="background: #e2e8f0; color: #94a3b8; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: bold; width: 100%; text-align: center; cursor: not-allowed;">
                                    Currently Unavailable
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <p style="color: #64748b; font-size: 16px; font-weight: bold; margin-bottom: 10px;">No restaurants found.</p>
                    <p style="color: #94a3b8; font-size: 13px;">Try adjusting your search or clearing your filters.</p>
                </div>
            @endforelse
        </div>
    </div>

    
@elseif($activeTab == 'view_restaurant')
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); position: relative;">
        
        {{-- Header Section with Navigation --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="/dashboard?tab=browse_food" style="text-decoration: none; color: #64748b; font-weight: bold; font-size: 14px;">
                ← Back to Restaurants
            </a>
            
            {{-- Beautiful Go to Cart Button --}}
            <a href="/dashboard?tab=cart" style="background: #1e293b; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                🛒 View Cart ({{ count(session('cart', [])) }})
            </a>
        </div>
        
        <h2 style="color: #1e293b; margin-bottom: 5px;">{{ $selectedRestaurant->name }}</h2>
        <p style="color: #64748b; margin-bottom: 20px;">{{ $selectedRestaurant->description }}</p>
        <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 25px;">

        {{-- Customer Sorting and Filtering Form --}}
        <form action="/dashboard" method="GET" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; flex-wrap: wrap;">
            
            {{-- CRITICAL: Hidden inputs keep us on this exact restaurant's menu --}}
            <input type="hidden" name="tab" value="view_restaurant">
            <input type="hidden" name="res_id" value="{{ $selectedRestaurant->restaurant_id }}">

            <div>
                <select name="filter" style="padding: 10px 15px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; color: #475569; font-weight: bold;">
                    <option value="">🍽️ All Items</option>
                    <option value="veg" {{ request('filter') == 'veg' ? 'selected' : '' }}>🟢 Pure Veg Only</option>
                    <option value="non_veg" {{ request('filter') == 'non_veg' ? 'selected' : '' }}>🔺 Non-Veg Only</option>
                    <option value="available" {{ request('filter') == 'available' ? 'selected' : '' }}>✅ Available Now</option>
                </select>
            </div>

            <div>
                <select name="sort" style="padding: 10px 15px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; color: #475569; font-weight: bold;">
                    <option value="">↕️ Default Sorting</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" style="background: #1e293b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                    Apply Filters
                </button>
                <a href="/dashboard?tab=view_restaurant&res_id={{ $selectedRestaurant->restaurant_id }}" style="color: #ef4444; text-decoration: none; font-size: 13px; font-weight: bold;">
                    Clear
                </a>
            </div>
        </form>

        <div style="display: grid; gap: 15px;">
            @forelse($menuItems as $item)
                @php 
                    $isAvailable = $item->availability == 1; // Check DB status
                @endphp
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; transition: 0.3s; @if(!$isAvailable) opacity: 0.7; background: #fafafa; @endif">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                            <span style="font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 4px; border: 1px solid {{ $item->is_veg ? 'green' : 'red' }}; color: {{ $item->is_veg ? 'green' : 'red' }};">
                                {{ $item->is_veg ? 'VEG' : 'NON-VEG' }}
                            </span>
                            @if(!$isAvailable)
                                <span style="font-size: 10px; font-weight: bold; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px;">
                                    CURRENTLY UNAVAILABLE
                                </span>
                            @endif
                        </div>
                        <h4 style="margin: 0; color: #1e293b; font-size: 16px;">{{ $item->food_name }}</h4>
                        <p style="font-size: 13px; color: #94a3b8; margin: 4px 0 0 0;">{{ $item->description }}</p>
                    </div>

                    <div style="text-align: right;">
                        <p style="font-weight: 800; font-size: 18px; margin-bottom: 10px; color: #1e293b;">₹{{ $item->price }}</p>
                        
                        <form action="/cart/add/{{ $item->food_id }}" method="POST" style="display: flex; gap: 8px; align-items: center; justify-content: flex-end;">
                            @csrf
                            <input type="number" name="quantity" value="1" min="1" max="20" 
                                   @if(!$isAvailable) disabled @endif
                                   style="width: 45px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: center; @if(!$isAvailable) background: #f1f5f9; cursor: not-allowed; @endif">
                            
                            @if($isAvailable)
                                <button type="submit" style="background: #22c55e; color: white; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s;">
                                    Add to Cart
                                </button>
                            @else
                                <button type="button" disabled style="background: #cbd5e1; color: #64748b; border: none; padding: 10px 18px; border-radius: 8px; cursor: not-allowed; font-weight: bold;">
                                    Out of Stock
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <p>No items found in this menu.</p>
                </div>
            @endforelse
        </div>
    </div>


@elseif($activeTab == 'cart')
  <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        @if(count($cartItems) > 0)
            {{-- Professional Header showing Restaurant Name & Clear Button --}}
            <div style="background: #f8fafc; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #f97316; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">Ordering From</p>
                    <h3 style="margin: 5px 0 0 0; color: #1e293b; font-size: 20px;">🏪 {{ $cartRestaurantName }}</h3>
                </div>
                
                <form action="/cart/clear" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold; transition: 0.2s;">
                        🗑️ Clear Cart
                    </button>
                </form>
            </div>
@endif
        
        @if(count($cartItems) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 14px;">
                    <th style="padding: 10px;">Item</th>
                    <th style="padding: 10px;">Price</th>
                    <th style="padding: 10px; width: 120px;">Qty</th>
                    <th style="padding: 10px;">Subtotal</th>
                    <th style="padding: 10px; text-align: center;">Action</th>
                </tr>
                @php $total = 0 @endphp
                @foreach($cartItems as $id => $details)
                    @php $total += $details['price'] * $details['quantity'] @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px 10px; font-weight: bold;">{{ $details['name'] }}</td>
                        <td style="padding: 15px 10px;">₹{{ $details['price'] }}</td>
                        
                        <td style="padding: 15px 10px;">
                            <form action="/cart/update/{{ $id }}" method="POST" style="display: flex; gap: 5px;">
                                @csrf
                                <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1" max="20" 
                                       style="width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                    Update
                                </button>
                            </form>
                        </td>

                        <td style="padding: 15px 10px; font-weight: bold;">₹{{ $details['price'] * $details['quantity'] }}</td>
                        
                        <td style="padding: 15px 10px; text-align: center;">
                            <form action="/cart/remove/{{ $id }}" method="POST">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 18px;" title="Remove Item">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>

            <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                <a href="/dashboard?tab=browse_food" style="color: #3b82f6; text-decoration: none; font-weight: bold;">← Continue Shopping</a>
                <div style="text-align: right;">
                    <h2 style="margin: 0;">Total: ₹{{ $total }}</h2>
                   <form action="/place-order" method="POST">
                    @csrf
              <a href="/dashboard?tab=payment" style="display: inline-block; background: #22c55e; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; text-decoration: none;">
    Proceed to Payment
</a>
                </form>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 50px;">
                <p style="font-size: 50px;">🛒</p>
                <p style="color: #94a3b8; margin-bottom: 20px;">Your cart is currently empty.</p>
                <a href="/dashboard?tab=browse_food" style="background: #ff7a2d; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">Browse Food</a>
            </div>
        @endif
    </div>

@elseif($activeTab == 'active_orders')
    <h2 class="text-xl font-bold mb-6 text-orange-600">🚚 Live Order Tracking</h2>
    <div class="space-y-6">
        @forelse($currentOrders as $order)
            <div class="p-6 border-2 border-orange-100 rounded-2xl bg-white shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order #{{ $order->order_id }}</span>
                        <h3 class="text-lg font-bold text-slate-800">Arriving by {{ date('h:i A', strtotime($order->estimated_time)) }}</h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-600 uppercase">
                        {{ $order->delivery_status }}
                    </span>
                </div>

                {{-- Progress Bar for Requirement (g) --}}
                <div class="w-full bg-slate-100 h-2 rounded-full mb-4">
                    @php
                        $width = '33%'; // Placed
                        if($order->delivery_status == 'Out for Delivery') $width = '66%';
                        if($order->delivery_status == 'Delivered') $width = '100%';
                    @endphp
                    <div style="width: {{ $width }};" class="bg-orange-500 h-2 rounded-full transition-all duration-500"></div>
                </div>

                <p class="text-sm text-slate-500">
                    <i class="far fa-clock"></i> 
                    Ordered at: {{ date('h:i A', strtotime($order->order_date)) }}
                </p>
                {{-- Only show Cancel button if the restaurant hasn't started preparing it yet --}}
        @if($order->status == 'Placed')
            <form action="/order/cancel/{{ $order->order_id }}" method="POST" style="display: inline-block; margin-top: 10px;">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to cancel this order?')" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: bold;">
                    🚫 Cancel Order
                </button>
            </form>
        @else
            <span style="display: inline-block; margin-top: 10px; font-size: 12px; color: #64748b; background: #f1f5f9; padding: 6px 12px; border-radius: 6px;">
                Cancellation Unavailable ({{ $order->status }})
            </span>
        @endif
            </div>
        @empty
            <div class="text-center py-10 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                <p class="text-slate-400">No orders are being delivered right now.</p>
            </div>
        @endforelse
    </div>

{{-- 📜 NEW TAB FOR ORDER HISTORY --}}
@elseif($activeTab == 'order_history')
{{-- User Analytics Cards --}}
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    
    <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; text-align: center;">
        <p style="color: #64748b; font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0;">Orders ({{ now()->format('M') }})</p>
        <h2 style="margin: 10px 0 0 0; color: #1e293b; font-size: 24px;">{{ $monthlyOrders }}</h2>
    </div>

    <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #e2e8f0; text-align: center;">
        <p style="color: #64748b; font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0;">Total Spent</p>
        <h2 style="margin: 10px 0 0 0; color: #16a34a; font-size: 24px;">₹{{ number_format($monthlySpend, 2) }}</h2>
    </div>

    <div style="background: #fff7ed; padding: 20px; border-radius: 15px; border: 1px solid #ffedd5; text-align: center;">
        <p style="color: #c2410c; font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0;">Top Choice 🏆</p>
        <h2 style="margin: 10px 0 0 0; color: #9a3412; font-size: 16px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ $topRestaurant->name ?? 'No Orders' }}
        </h2>
    </div>

</div>
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 class="text-xl font-bold mb-4 text-slate-700">📜 Past History</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9; color: #64748b;">
                    <th style="padding: 10px;">Order ID</th>
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px;">Total</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pastOrders as $order)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px 10px; font-weight: bold;">#{{ $order->order_id }}</td>
                        <td style="padding: 15px 10px;">{{ $order->order_date }}</td>
                        <td style="padding: 15px 10px; text-align: right;">₹{{ $order->total_amount }}</td>
                        <td style="padding: 15px 10px; text-align: center;">
                            @php
                                $color = $order->status == 'Delivered' ? '#166534' : '#991b1b';
                                $bg = $order->status == 'Delivered' ? '#dcfce7' : '#fee2e2';
                            @endphp
                            <span style="padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; background: {{ $bg }}; color: {{ $color }};">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td style="padding: 15px 10px;">
                            @if($order->status == 'Delivered')
                                <a href="{{ url('/dashboard?tab=write_review&order_id='.$order->order_id) }}" 
                                   style="background: #3b82f6; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: bold; display: inline-block;">
                                   Rate Order
                                </a>
                            @else
                                <span style="color: #94a3b8; font-size: 11px;">Not available</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400 italic">No past history found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- User Analytics Cards --}}


</div>
    @elseif($activeTab == 'write_review')
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 class="text-2xl font-bold mb-6">Review Order #{{ $orderId }}</h2>
        
        <form action="/submit-review" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $orderId }}">
            <input type="hidden" name="restaurant_id" value="{{ $orderItems->first()->restaurant_id }}">

            {{-- 1. Restaurant Review --}}
            <div class="mb-8 p-4 bg-slate-50 rounded-xl">
                <h4 class="font-bold mb-2">Overall Restaurant Experience</h4>
                <select name="restaurant_rating" class="p-2 border rounded">
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Good</option>
                    <option value="1">⭐ Poor</option>
                </select>
                <textarea name="restaurant_comment" class="w-full mt-2 p-2 border rounded" placeholder="How was the service?"></textarea>
            </div>

            {{-- 2. Individual Food Items --}}
            <h4 class="font-bold mb-4">Rate the Food:</h4>
            @foreach($orderItems as $item)
                <div class="mb-4 p-4 border rounded-xl flex justify-between items-center">
                    <span>{{ $item->food_name }}</span>
                    <div class="flex gap-4">
                        <select name="item_rating[{{ $item->food_id }}]" class="p-1 border rounded">
                            @for($i=5; $i>=1; $i--) <option value="{{ $i }}">{{ $i }} Stars</option> @endfor
                        </select>
                        <input type="text" name="item_comment[{{ $item->food_id }}]" class="border rounded p-1" placeholder="Taste?">
                    </div>
                </div>
            @endforeach

            <button type="submit" class="w-full bg-orange-500 text-white py-3 rounded-xl font-bold">Submit All Reviews</button>
        </form>
    </div>

@elseif($activeTab == 'payment')
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;">
        
        {{-- Professional Checkout Header --}}
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: #1e293b; margin-bottom: 5px;">Secure Checkout 🔒</h2>
            <p style="color: #64748b; font-size: 14px;">Review your details and complete your order.</p>
        </div>

        {{-- Order Summary Card --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 30px;">
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold;">Order Summary</p>
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #cbd5e1; padding-bottom: 15px; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0; color: #0f172a; font-size: 18px;">🏪 {{ $cartRestaurantName ?? 'Selected Restaurant' }}</h3>
                    <p style="margin: 5px 0 0 0; font-size: 13px; color: #64748b;">{{ count($cartItems) }} Item(s) in cart</p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; font-size: 12px; color: #64748b;">Total to Pay</p>
                    <h2 style="margin: 0; color: #16a34a; font-size: 24px;">₹{{ number_format($total, 2) }}</h2>
                </div>
            </div>

            {{-- Small loop to show what they are buying --}}
            <ul style="list-style-type: none; padding: 0; margin: 0; font-size: 14px; color: #475569;">
                @foreach($cartItems as $item)
                    <li style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>{{ $item['quantity'] }}x {{ $item['name'] }}</span>
                        <span>₹{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- The Payment Form --}}
        <form action="/place-order" method="POST">
            @csrf
            
            {{-- Delivery Address Selection --}}
            <h4 style="color: #1e293b; margin-bottom: 15px;">📍 Select Delivery Address</h4>
            <div style="display: grid; gap: 10px; margin-bottom: 25px;">
                @forelse($addresses as $address)
                    <label style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; align-items: flex-start; gap: 10px; cursor: pointer; transition: 0.2s;">
                        <input type="radio" name="address_id" value="{{ $address->address_id }}" required style="margin-top: 4px;">
                        <div>
                            <strong style="display: block; color: #0f172a;">{{ $address->address_type }}</strong>
                            <span style="font-size: 13px; color: #64748b;">{{ $address->building_name }}, {{ $address->street }}, {{ $address->postcode }}</span>
                        </div>
                    </label>
                @empty
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; font-size: 14px;">
                        No addresses found! Please <a href="/dashboard?tab=addresses" style="color: #b91c1c; font-weight: bold; text-decoration: underline;">add an address</a> first.
                    </div>
                @endforelse
            </div>

            {{-- Payment Method Selection --}}
            <h4 style="color: #1e293b; margin-bottom: 15px;">💳 Payment Method</h4>
            <select name="method" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 30px; font-size: 15px;">
                <option value="UPI">UPI / GPay / PhonePe</option>
                <option value="Card">Credit / Debit Card</option>
                <option value="Cash on Delivery">Cash on Delivery (COD)</option>
            </select>

            {{-- Submit Button --}}
            <button type="submit" @if(count($addresses) == 0) disabled @endif style="width: 100%; background: #f97316; color: white; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: @if(count($addresses) == 0) not-allowed @else pointer @endif; opacity: @if(count($addresses) == 0) 0.5 @else 1 @endif;">
                Place Order (₹{{ number_format($total, 2) }})
            </button>
        </form>
    </div>
@endif
        </div>
    </div>
    </div>
</div>
@endsection