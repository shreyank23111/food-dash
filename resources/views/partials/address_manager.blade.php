<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
        <h3 class="text-lg font-bold mb-4">Saved Locations</h3>
        @forelse($addresses as $addr)
            <div class="p-4 border rounded-2xl mb-4 bg-slate-50">
                <span class="text-xs font-black uppercase text-orange-500">{{ $addr->address_type }}</span>
                <p class="font-bold">{{ $addr->building_name }}, {{ $addr->street }}</p>
                <p class="text-slate-500 text-sm">{{ $addr->city }}, {{ $addr->state }}</p>
            </div>
        @empty
            <p class="text-slate-400 italic">No addresses added yet.</p>
        @endforelse
    </div>

    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
        <h3 class="text-lg font-bold mb-4">Add New Address</h3>
        <form action="/add-address" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="building_name" placeholder="Flat/Building" class="w-full p-3 border rounded-xl" required>
            <input type="text" name="street" placeholder="Street" class="w-full p-3 border rounded-xl" required>
            <input type="text" name="postcode" placeholder="Pincode" class="w-full p-3 border rounded-xl" required>
            <select name="address_type" class="w-full p-3 border rounded-xl">
                <option value="Home">Home</option>
                <option value="Work">Work</option>
            </select>
            <button class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold">Save</button>
        </form>
    </div>
</div>