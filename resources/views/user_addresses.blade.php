@extends('layout')

@section('content')
<div class="w-full max-w-5xl bg-white shadow-2xl rounded-3xl overflow-hidden">
    <div class="bg-orange-500 p-8 text-white">
        <h1 class="text-2xl font-bold">Your Saved Addresses</h1>
        <p class="text-orange-100">Requirement 1.4a: Support for multiple delivery locations</p>
    </div>

    <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-lg font-bold text-slate-700">Address Book</h3>
            @forelse($addresses as $addr)
            <div class="p-6 border border-slate-100 rounded-2xl bg-slate-50 hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <span class="px-3 py-1 bg-orange-100 text-orange-600 text-xs font-bold rounded-full uppercase">
                        {{ $addr->address_type }}
                    </span>
                </div>
                <p class="mt-3 font-bold text-slate-800">{{ $addr->building_name }}</p>
                <p class="text-slate-600">{{ $addr->street }}</p>
                <p class="text-slate-400 text-sm italic">{{ $addr->city }}, {{ $addr->state }} - {{ $addr->postcode }}</p>
            </div>
            @empty
            <p class="text-slate-400 italic">No addresses saved yet.</p>
            @endforelse
        </div>

        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
            <h3 class="text-lg font-bold text-slate-700 mb-6">Add New Address</h3>
            <form action="{{ route('address.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="text" name="building_name" placeholder="Building/Flat Name" class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-orange-500" required>
                <input type="text" name="street" placeholder="Street/Area" class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-orange-500" required>
                <input type="text" name="postcode" placeholder="Pincode" class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-orange-500" required>
                <select name="address_type" class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="Home">Home</option>
                    <option value="Work">Work</option>
                    <option value="Other">Other</option>
                </select>
                <button type="submit" class="w-full bg-slate-800 text-white font-bold py-3 rounded-xl hover:bg-slate-900 transition shadow-lg">
                    Save Address
                </button>
            </form>
        </div>
    </div>
    <div class="p-8 bg-slate-50 border-t border-slate-100">
        <a href="/dashboard" class="text-slate-500 font-bold hover:text-orange-600">&larr; Back to Dashboard</a>
    </div>
</div>
@endsection