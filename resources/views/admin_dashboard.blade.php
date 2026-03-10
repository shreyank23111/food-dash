@extends('layout')

@section('content')
<div class="flex min-h-screen w-full overflow-hidden bg-slate-50 font-sans">
    
    {{-- 1. LEFT SIDEBAR (Dark Mode) --}}
    <div class="w-64 bg-slate-900 text-white flex-shrink-0 shadow-2xl flex flex-col min-h-screen">
        <div class="p-6 border-b border-slate-800">
            <h2 class="text-2xl font-black text-red-400 tracking-tight">FoodDash</h2>
            <p class="text-xs text-slate-400 mt-1 font-bold uppercase tracking-widest">System Admin</p>
        </div>
        
        <nav class="mt-4 flex-1">
            <a href="/admin/dashboard?tab=overview" class="block px-8 py-4 transition-colors {{ $activeTab == 'overview' ? 'bg-indigo-600 font-bold text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                📊 Overview
            </a>
            
            <a href="/admin/dashboard?tab=requests" class="block px-8 py-4 transition-colors flex justify-between items-center {{ $activeTab == 'requests' ? 'bg-indigo-600 font-bold text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span>📋 Approvals</span>
                @if(isset($pendingRequests) && count($pendingRequests) > 0)
                    <span class="bg-red-500 text-white text-[10px] px-2 py-1 rounded-full font-bold">{{ count($pendingRequests) }}</span>
                @endif
            </a>
            
            <a href="/admin/dashboard?tab=all_restaurants" class="block px-8 py-4 transition-colors {{ $activeTab == 'all_restaurants' ? 'bg-indigo-600 font-bold text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                🏪 Directory
            </a>
            
            <a href="/admin/dashboard?tab=payments" class="block px-8 py-4 transition-colors {{ $activeTab == 'payments' ? 'bg-indigo-600 font-bold text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                💳 Payments
            </a>
            
            <a href="/admin/dashboard?tab=logs" class="block px-8 py-4 transition-colors {{ $activeTab == 'logs' ? 'bg-indigo-600 font-bold text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                📝 Audit Logs
            </a>

            <div class="border-t border-slate-800 mt-6 pt-4">
                <form action="/logout" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full text-left block px-8 py-4 text-red-400 hover:bg-red-900/20 transition-colors font-bold">
                         Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>

    {{-- 2. RIGHT MAIN CONTENT AREA --}}
    <div class="flex-1 overflow-y-auto p-12">
        
        {{-- Page Header --}}
        <header class="mb-10 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Control Panel</h1>
                <p class="text-slate-500 mt-1">Manage platform operations and monitor activities.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-slate-400">Admin ID</p>
                <p class="text-lg font-black text-indigo-600">#{{ Auth::id() ?? session('account_id') }}</p>
            </div>
        </header>

        {{-- System Alerts --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-xl mb-8 font-bold flex items-center gap-3 shadow-sm">
                <span class="text-xl">✅</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-8 font-bold flex items-center gap-3 shadow-sm">
                <span class="text-xl">❌</span> {{ session('error') }}
            </div>
        @endif

        {{-- Dynamic Tab Content --}}
        <div class="bg-white rounded-3xl shadow-sm p-8 border border-slate-200">
            
            {{-- TAB: Overview --}}
            @if($activeTab == 'overview')
                <h2 class="text-xl font-bold mb-6 text-slate-800">System Analytics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="border border-slate-100 p-8 rounded-3xl bg-indigo-50 hover:shadow-md transition">
                        <h3 class="text-sm font-bold text-indigo-400 uppercase tracking-wider mb-2">Pending Requests</h3>
                        <p class="text-5xl font-black text-indigo-700 mb-4">{{ $totalRequests ?? 0 }}</p>
                        <a href="?tab=requests" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl font-bold text-sm transition">Review Queue &rarr;</a>
                    </div>

                    <div class="border border-slate-100 p-8 rounded-3xl bg-slate-50 hover:shadow-md transition">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Active Restaurants</h3>
                        <p class="text-5xl font-black text-slate-800 mb-4">{{ $totalRestaurants ?? 0 }}</p>
                        <a href="?tab=all_restaurants" class="inline-block border border-slate-300 px-6 py-2 rounded-xl font-bold text-sm text-slate-600 hover:bg-slate-100 transition">View Directory &rarr;</a>
                    </div>
                </div>

            {{-- TAB: Pending Requests --}}
            @elseif($activeTab == 'requests')
                <h2 class="text-xl font-bold mb-6 text-slate-800">Pending Approvals</h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Restaurant</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Date Applied</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pendingRequests ?? [] as $req)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800 text-lg">{{ $req->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-1">REQ ID: #{{ $req->request_id }}</p>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 font-medium">
                                        {{ \Carbon\Carbon::parse($req->request_date)->format('d M Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <form action="/admin/approve/{{ $req->request_id }}/{{ $req->restaurant_id }}" method="POST" class="m-0 inline-block">
                                            @csrf
                                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm">
                                                ✔️ Approve
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-12 text-center text-slate-400 font-medium italic">No pending requests at this time.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- TAB: All Restaurants --}}
            @elseif($activeTab == 'all_restaurants')
                <h2 class="text-xl font-bold mb-6 text-slate-800">Platform Directory</h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Restaurant</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Owner Details</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($restaurants ?? [] as $res)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 px-6">
                                        <p class="font-bold text-slate-800 text-base">{{ $res->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-1">RES ID: #{{ $res->restaurant_id }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-slate-700 font-medium">{{ $res->first_name }} {{ $res->last_name }}</p>
                                        <p class="text-xs text-slate-500 mt-1">📞 {{ $res->phone }}</p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $res->status == 'Open' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            ● {{ $res->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-12 text-center text-slate-400 font-medium italic">No restaurants found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- TAB: System Payments --}}
            @elseif($activeTab == 'payments')
                <h2 class="text-xl font-bold mb-6 text-slate-800">Global Payment Ledger</h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Txn ID</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Routing</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments ?? [] as $pay)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 px-6 font-mono text-sm text-slate-500">{{ $pay->transaction_id ?? 'SYS-'.$pay->payment_id }}</td>
                                    <td class="py-4 px-6 font-black text-emerald-600 text-lg">₹{{ number_format($pay->amount, 2) }}</td>
                                    <td class="py-4 px-6">
                                        <p class="text-xs text-slate-500 mb-1">From: <span class="font-bold text-slate-700">{{ $pay->user_fname }} {{ $pay->user_lname }}</span></p>
                                        <p class="text-xs text-slate-500">To: <span class="font-bold text-indigo-600">{{ $pay->restaurant_name }}</span></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $pay->payment_status == 'Success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $pay->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-12 text-center text-slate-400 font-medium italic">No payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            {{-- TAB: Action Logs --}}
            @elseif($activeTab == 'logs')
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-800">System Audit Logs</h2>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full">Req: 1.4i</span>
                </div>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Admin</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Action / Remarks</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs ?? [] as $log)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 px-6 font-bold text-slate-800 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">A</div>
                                        {{ $log->first_name }} {{ $log->last_name }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="bg-slate-200 text-slate-700 px-2 py-1 rounded text-xs font-black mr-2">{{ $log->action }}</span>
                                        <span class="text-sm text-slate-600">{{ $log->remark }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-slate-400 text-right font-mono">
                                        {{ \Carbon\Carbon::parse($log->action_date)->format('d M y, H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-12 text-center text-slate-400 font-medium italic">No audit logs generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection