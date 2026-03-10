@extends('layout')

@section('content')
<div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-8 border border-slate-100 my-10">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Create Account</h1>
        <p class="text-slate-500 mt-2">Join our food delivery network</p>
    </div>

    <form action="/signup-process" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">First Name</label>
            <input type="text" name="first_name" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none" required>
        </div>
        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Last Name</label>
            <input type="text" name="last_name" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none" required>
        </div>
        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none" required>
        </div>
        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Phone</label>
            <input type="text" name="phone" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Date of Birth</label>
            <input type="date" name="dob" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 outline-none" required>
        </div>
        
        <div class="md:col-span-2 mt-4">
            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-4 rounded-xl transition transform active:scale-95 shadow-xl">
                Get Started
            </button>
        </div>
    </form>

    <div class="mt-8 text-center text-sm text-slate-600">
        Already registered? <a href="/login" class="text-orange-500 font-semibold hover:underline">Login here</a>
    </div>
</div>
@endsection