@extends('frontend.app')
@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 py-10">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4 shrink-0">
            @include('frontend.profile.sidebar')
        </div>
        <!-- Main Content -->
        <div class="w-full md:w-3/4 min-w-0">
            <h4 class="text-xl pl-2 font-bold text-slate-900 mb-4 tracking-tight">Orders</h4>
            @include('frontend.components.orderscard')
        </div>
    </div>
</div>
@endsection
