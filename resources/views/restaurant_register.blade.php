@extends('layout')

@section('content')
<div style="max-width: 900px; margin: 40px auto; font-family: sans-serif; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white;">
    <div style="background-color: #ff7a2d; color: white; padding: 25px; border-radius: 12px 12px 0 0;">
        <h2 style="margin: 0;">Partner with FoodDash</h2>
        <p style="margin: 5px 0 0;">Fill in the details to register your restaurant business.</p>
    </div>

    @if(session('error'))
        <div style="padding: 15px; color: #721c24; background: #f8d7da; border-bottom: 1px solid #f5c6cb;">
            {{ session('error') }}
        </div>
    @endif

   {{-- 1. ADDED enctype="multipart/form-data" HERE --}}
    <form action="/restaurant/register-process" method="POST" enctype="multipart/form-data" style="padding: 40px;">
        @csrf
        
        <h3 style="border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; color: #333;">1. Owner Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Full Name</label>
                <input type="text" name="owner_name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Email Address</label>
                <input type="email" name="email" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Personal Phone</label>
                <input type="text" name="personal_phone" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Create Password</label>
                <input type="password" name="password" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
        </div>

        <h3 style="border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; color: #333; margin-top: 20px;">2. Restaurant Details</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Restaurant Name</label>
                <input type="text" name="restaurant_name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            
            {{-- 2. ADDED THE FILE INPUT HERE --}}
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Restaurant Banner Image (Optional)</label>
                <input type="file" name="restaurant_image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fafafa;">
                <small style="color: #666; display: block; margin-top: 5px;">Upload a high-quality image of your restaurant or signature dish.</small>
            </div>

            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Business Phone</label>
                <input type="text" name="business_phone" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Avg Prep Time (Mins)</label>
                <input type="number" name="prep_time" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Service Pincode</label>
                <input type="text" name="postcode" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Building Name/Shop No.</label>
                <input type="text" name="building_name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Street/Locality</label>
                <input type="text" name="street" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            </div>
        </div>

        <h3 style="border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; color: #333; margin-top: 20px;">3. Cuisines Offered</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; background: #fcfcfc; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
            @foreach(DB::table('CUISINE')->get() as $c)
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="cuisines[]" value="{{ $c->cuisine_id }}">
                    <span style="font-size: 14px; color: #555;">{{ $c->cuisine_name }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" style="width: 100%; padding: 18px; margin-top: 40px; background: #2d3748; color: white; border: none; font-size: 18px; font-weight: bold; border-radius: 8px; cursor: pointer; transition: background 0.3s;">
            Register & Submit for Approval
        </button>
    </form>
</div>
@endsection