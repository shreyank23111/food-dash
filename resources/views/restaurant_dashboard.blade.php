@extends('layout')

@section('content')
<div style="display: flex; min-height: 100vh; background: #f1f5f9; font-family: sans-serif;">
    
    <div style="width: 250px; background: #0f172a; color: white; padding: 25px;">
        <h2 style="color: #fb923c; margin-bottom: 30px;">Partner Panel</h2>
        <nav style="display: flex; flex-direction: column; gap: 10px;">
            <a href="/restaurant/dashboard?tab=restaurants" style="text-decoration: none; color: {{ $activeTab == 'restaurants' ? 'white' : '#94a3b8' }}; padding: 10px; background: {{ $activeTab == 'restaurants' ? '#1e293b' : 'transparent' }}; border-radius: 5px;">🏠 My Restaurants</a>
            <a href="/restaurant/dashboard?tab=manage_orders" 
   class="block px-8 py-4 hover:bg-slate-800 {{ $activeTab == 'manage_orders' ? 'bg-orange-500' : '' }}">
   📦 Incoming Orders
</a>
            <a href="/restaurant/dashboard?tab=add_food" style="text-decoration: none; color: {{ $activeTab == 'add_food' ? 'white' : '#94a3b8' }}; padding: 10px; background: {{ $activeTab == 'add_food' ? '#1e293b' : 'transparent' }}; border-radius: 5px;">➕ Add Food Items</a>
            <a href="/restaurant/dashboard?tab=view_menu" style="text-decoration: none; color: {{ $activeTab == 'view_menu' ? 'white' : '#94a3b8' }}; padding: 10px; background: {{ $activeTab == 'view_menu' ? '#1e293b' : 'transparent' }}; border-radius: 5px;">📖 View Menu</a>
            <a href="/restaurant/dashboard?tab=history" style="text-decoration: none; color: {{ $activeTab == 'history' ? 'white' : '#94a3b8' }}; padding: 10px; background: {{ $activeTab == 'history' ? '#1e293b' : 'transparent' }}; border-radius: 5px;">📜 Order History</a>
            <hr style="border-color: #334155;">
            <a href="/logout" style="text-decoration: none; color: #f87171; padding: 10px;">🚪 Logout</a>
        </nav>
    </div>

    <div style="flex: 1; padding: 40px;">
        <h1 style="margin-bottom: 10px;">Welcome, {{ Auth::user()->first_name }}</h1>

        {{-- Business Analytics Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; margin-top: 20px;">
            
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 5px solid #10b981;">
                <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Lifetime Revenue</p>
                <h2 style="margin: 10px 0 0 0; color: #1e293b; font-size: 28px;">₹{{ number_format($totalRevenue, 2) }}</h2>
            </div>

            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 5px solid #3b82f6;">
                <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Orders Today</p>
                <h2 style="margin: 10px 0 0 0; color: #1e293b; font-size: 28px;">{{ $ordersToday }}</h2>
            </div>

            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 5px solid #f59e0b;">
                <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Active Orders</p>
                <h2 style="margin: 10px 0 0 0; color: #1e293b; font-size: 28px;">{{ $activeOrdersCount }}</h2>
            </div>

        </div>
        
@if($activeTab == 'restaurants')
            <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3>My Outlets</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr style="text-align: left; border-bottom: 1px solid #eee;">
                        <th style="padding: 10px;">Restaurant Name</th>
                        <th style="padding: 10px;">Approval Status</th>
                        <th style="padding: 10px;">Store Status</th>
                        <th style="padding: 10px;">Quick Actions</th> {{-- Added a new column header --}}
                    </tr>
                    
                    @foreach($restaurants as $res) {{-- The variable is $res --}}
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 15px 10px;">{{ $res->name }}</td>
                        <td style="padding: 15px 10px;">
                            <span style="background: {{ $res->approval_status == 'Approved' ? '#dcfce7' : '#fef3c7' }}; color: {{ $res->approval_status == 'Approved' ? '#166534' : '#92400e' }}; padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                {{ $res->approval_status ?? 'Pending' }}
                            </span>
                        </td>
                        <td style="padding: 15px 10px; color: {{ $res->status == 'Open' ? 'green' : 'red' }}; font-weight: bold;">
                            {{ $res->status }}
                        </td>
                        
                        {{-- Put the button neatly inside a table cell using $res --}}
                        <td style="padding: 15px 10px;">
                            @if($res->approval_status == 'Approved')
                                <form action="/restaurant/toggle/{{ $res->restaurant_id }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="background: {{ $res->status == 'Open' ? '#f59e0b' : '#10b981' }}; color: white; border: none; padding: 8px 15px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer;">
                                        {{ $res->status == 'Open' ? '⏸️ Close' : '▶️ Open' }}
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 13px; color: #f59e0b; font-weight: bold;">⏳ Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>


       @elseif($activeTab == 'add_food')
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 25px; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Add New Menu Item</h3>
        
        <form action="/restaurant/add-food" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div style="grid-column: span 2;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Select Restaurant</label>
                    <select name="restaurant_id" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" required>
                        @foreach($restaurants as $res)
                            @if($res->approval_status == 'Approved')
                                <option value="{{ $res->restaurant_id }}">{{ $res->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Food Name</label>
                    <input type="text" name="food_name" placeholder="e.g., Paneer Tikka" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" required>
                </div>

                <div>
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Price (₹)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" required>
                </div>

                <div>
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Food Type</label>
                    <select name="is_veg" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" required>
                        <option value="1">Veg</option>
                        <option value="0">Non-Veg</option>
                    </select>
                </div>

                <div>
    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Availability</label>
    <select name="availability" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;" required>
        <option value="Available" selected>Available</option> <option value="Out of Stock">Out of Stock</option>
    </select>
</div>

                <div style="grid-column: span 2;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the ingredients or taste..." style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px;"></textarea>
                </div>
            </div>

            <button type="submit" style="margin-top: 30px; width: 100%; background: #0f172a; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer;">
                Save Item to Menu
            </button>
        </form>
    </div>

@elseif($activeTab == 'view_menu')
    <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px;">Inventory Management</h3>
        {{-- Sorting and Filtering Form --}}
        <form action="/restaurant/dashboard" method="GET" style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
            
            {{-- Hidden input to keep us on the view_menu tab --}}
            <input type="hidden" name="tab" value="view_menu">

            <div>
                <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Filter By</label>
                <select name="filter" style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; min-width: 150px;">
                    <option value="">All Items</option>
                    <option value="veg" {{ request('filter') == 'veg' ? 'selected' : '' }}>🟢 Pure Veg</option>
                    <option value="non_veg" {{ request('filter') == 'non_veg' ? 'selected' : '' }}>🔺 Non-Veg</option>
                    <option value="available" {{ request('filter') == 'available' ? 'selected' : '' }}>✅ Available Only</option>
                    <option value="out_of_stock" {{ request('filter') == 'out_of_stock' ? 'selected' : '' }}>❌ Out of Stock</option>
                </select>
            </div>

            <div>
                <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Sort By</label>
                <select name="sort" style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; min-width: 150px;">
                    <option value="">Default (Newest First)</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                </select>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 9px 20px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer;">
                    Apply Filters
                </button>
                <a href="/restaurant/dashboard?tab=view_menu" style="color: #64748b; text-decoration: none; font-size: 13px; margin-left: 10px; font-weight: bold;">Clear</a>
            </div>
        </form>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9; color: #64748b;">
                    <th style="padding: 12px;">Item Name</th>
                    <th style="padding: 12px;">Description</th>
                    <th style="padding: 12px;">Price</th>
                    <th style="padding: 12px;">Current Status</th>
                    <th style="padding: 12px;">Action</th>
                         <th style="padding: 12px;">Update Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px; font-weight: bold;">{{ $item->food_name }}</td>
                    <td style="padding: 12px;">{{ $item->description }}</td>
                    <td style="padding: 12px;">₹{{ $item->price }}</td>
                    <td style="padding: 12px;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; background: {{ $item->availability == 1 ? '#dcfce7' : '#fee2e2' }}; color: {{ $item->availability == 1 ? '#166534' : '#991b1b' }};">
                            {{ $item->availability == 1 ? 'Available' : 'Sold Out' }}
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <form action="/restaurant/menu/toggle-status/{{ $item->food_id }}" method="POST">
                            @csrf
                            <button type="submit" style="background: {{ $item->availability == 1 ? '#ef4444' : '#22c55e' }}; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                {{ $item->availability == 1 ? 'Mark Unavailable' : 'Mark Available' }}
                            </button>
                        </form>
                    </td>
                     <td style="padding: 12px;">
                        <form action="/restaurant/menu/update-price/{{ $item->food_id }}" method="POST" style="display: flex; gap: 5px;">
                            @csrf
                            <input type="number" name="new_price" value="{{ $item->price }}" step="0.01" 
                                   style="width: 80px; padding: 5px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 13px;">
                            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                Save
                            </button>
                        </form>
                    </td
                </tr>
                @empty
                <tr><td colspan="5" style="padding: 30px; text-align: center;">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @elseif($activeTab == 'manage_orders')
    <h2 class="text-2xl font-bold mb-4">Incoming Orders</h2>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="text-slate-400 text-sm uppercase border-b">
                <th class="pb-4">Order ID</th>
                <th class="pb-4">Customer</th>
                <th class="pb-4">Restaurant</th>
                <th class="pb-4">Total</th>
                <th class="pb-4">Status</th>
                <th class="pb-4 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomingOrders as $order)
                <tr class="border-b">
                    <td class="py-4 font-bold">#{{ $order->order_id }}</td>
                    <td class="py-4">{{ $order->customer_name }}</td>
                    <td class="py-4 text-sm">{{ $order->restaurant_name }}</td>
                    <td class="py-4">₹{{ $order->total_amount }}</td>
                    <td class="py-4">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            {{ $order->status == 'Placed' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $order->status }}
                        </span>
                    </td>
                   <td class="py-4 text-center">
    {{-- 1. If Delivered or Cancelled, hide the form entirely (Final states) --}}
    @if($order->status == 'Delivered' || $order->status == 'Cancelled')
        <span class="text-slate-400 text-xs italic">No actions available</span>
    
    @else
        {{-- 2. Otherwise, show the update form --}}
        <form action="/restaurant/order/update/{{ $order->order_id }}" method="POST">
            @csrf
            <select name="status" class="text-xs border rounded p-1">
                {{-- If status is Placed, show everything --}}
                @if($order->status == 'Placed')
                    <option value="Preparing">Preparing</option>
                    <option value="Cancelled">Cancel Order</option>
                
                {{-- 3. If already Preparing, hide the 'Cancel' option --}}
                @elseif($order->status == 'Preparing')
                    <option value="Delivered">Mark Delivered</option>
                @endif
            </select>
            <button type="submit" class="bg-slate-800 text-white px-2 py-1 rounded text-xs">Update</button>
        </form>
    @endif
</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400 italic">No orders received yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @elseif($activeTab == 'history')
    <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px;">Past Order History</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9; color: #64748b;">
                    <th style="padding: 12px;">Order ID</th>
                    <th style="padding: 12px;">Restaurant</th>
                    <th style="padding: 12px;">Customer</th>
                    <th style="padding: 12px;">Amount</th>
                    <th style="padding: 12px;">Status</th>
                    <th style="padding: 12px;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orderHistory as $order)
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px; font-weight: bold;">#{{ $order->order_id }}</td>
                    <td style="padding: 12px;">{{ $order->restaurant_name }}</td>
                    <td style="padding: 12px;">{{ $order->customer_name }}</td>
                    <td style="padding: 12px; font-weight: bold;">₹{{ $order->total_amount }}</td>
                    <td style="padding: 12px;">
                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: {{ $order->status == 'Delivered' ? '#dcfce7' : '#fee2e2' }}; color: {{ $order->status == 'Delivered' ? '#166534' : '#991b1b' }};">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 13px; color: #64748b;">
                        {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8; font-style: italic;">No past orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 25px; display: flex; justify-content: center;">
            {{ $orderHistory->links() }}
        </div>
    </div>
@endif



    </div>
</div>
@endsection