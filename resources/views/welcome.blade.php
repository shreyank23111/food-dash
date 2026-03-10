@extends('layout')

@section('content')
<div class="min-h-screen bg-white">
    <nav class="flex items-center justify-between px-10 py-6 bg-white border-b border-slate-100">
        <div class="text-2xl font-black text-orange-500 tracking-tighter">FoodDash</div>
        <div class="space-x-8 font-bold text-slate-600">
            <a href="/login" class="hover:text-orange-500 transition">Login</a>
            <a href="/restaurant/register" class="bg-orange-500 text-white px-6 py-3 rounded-xl hover:bg-slate-800 transition shadow-lg shadow-orange-100">
                Partner with us
            </a>
        </div>
    </nav>

    <div class="flex flex-col items-center justify-center text-center px-6 py-24 bg-slate-50">
        <h1 class="text-6xl md:text-8xl font-black text-slate-900 leading-tight mb-6">
            Hungry? <span class="text-orange-500">Fast.</span>
        </h1>
        <p class="text-xl text-slate-500 max-w-2xl mb-12">
            The best food from your local restaurants, delivered straight to your door. 
            Join the platform that helps local businesses grow.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-4xl">
            <div class="p-10 bg-white border border-slate-100 rounded-3xl shadow-xl hover:-translate-y-2 transition group">
                <div class="text-4xl mb-4 group-hover:scale-125 transition duration-300">🍔</div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Order Food</h3>
                <p class="text-slate-500 mb-8">Discover top-rated restaurants near you and enjoy fresh meals at home.</p>
                <a href="/login" class="inline-block w-full bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-orange-500 transition">
                    Get Started
                </a>
            </div>

            <div class="p-10 bg-white border border-slate-100 rounded-3xl shadow-xl hover:-translate-y-2 transition group border-t-4 border-t-orange-500">
                <div class="text-4xl mb-4 group-hover:scale-125 transition duration-300">🏪</div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Grow Your Business</h3>
                <p class="text-slate-500 mb-8">Register your restaurant and start reaching thousands of customers today.</p>
                <a href="/restaurant/register" class="inline-block w-full bg-orange-500 text-white py-4 rounded-2xl font-bold hover:bg-slate-900 transition shadow-lg shadow-orange-200">
                    Register Restaurant
                </a>
            </div>
        </div>
    </div>

    <div class="py-20 px-10 grid grid-cols-1 md:grid-cols-3 gap-12 text-center max-w-6xl mx-auto">
        <div>
            <h4 class="font-bold text-slate-800 text-lg">Fast Delivery</h4>
            <p class="text-slate-500 text-sm mt-2">Real-time tracking for every order you place.</p>
        </div>
        <div>
            <h4 class="font-bold text-slate-800 text-lg">Verified Areas</h4>
            <p class="text-slate-500 text-sm mt-2">We only serve areas where we can guarantee quality.</p>
        </div>
        <div>
            <h4 class="font-bold text-slate-800 text-lg">Secure Partners</h4>
            <p class="text-slate-500 text-sm mt-2">All restaurants are strictly reviewed by our admins.</p>
        </div>
    </div>
</div>
@endsection