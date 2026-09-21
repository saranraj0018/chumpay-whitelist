@extends('frontend.app')
@section('content')
<div class="w-full pt-[50px] hidden lg:block ">
    <div class="max-w-4xl mx-auto flex items-center justify-between">
        <a href="{{ route('shop') }}" class="flex items-center gap-2 cursor-pointer pr-[50px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-sm font-medium">Back</span>
        </a>
        <!-- Step 1 -->
        <a href="{{ route('shop.cart') }}" class="flex items-center">
            <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 transition-all
                {{ $step >= 1 ? 'border-amber-500 bg-amber-50/50 text-amber-600 shadow-sm' : 'border-slate-200 text-slate-400' }}">
                <i class="fa-solid fa-cart-shopping text-sm
                    {{ $step >= 1 ? 'text-amber-600' : 'text-slate-400' }}"></i>
            </div>
            <span class="ml-2.5 text-sm
                {{ $step >= 1 ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
                Cart
            </span>
        </a>
        <!-- Line 1 -->
        <div class="flex-1 h-[2px] mx-3 rounded-full transition-all
            {{ $step >= 2 ? 'bg-amber-500' : 'bg-slate-200' }}">
        </div>
        <!-- Step 2 -->
        <a class="flex items-center">
            <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 transition-all
                {{ $step >= 2 ? 'border-amber-500 bg-amber-50/50 text-amber-600 shadow-sm' : 'border-slate-200 text-slate-400' }}">
                <i class="fa-solid fa-location-dot text-sm
                    {{ $step >= 2 ? 'text-amber-600' : 'text-slate-400' }}"></i>
            </div>
            <span class="ml-2.5 text-sm
                {{ $step >= 2 ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
                Delivery Info
            </span>
        </a>
        <!-- Line 2 -->
        <div class="flex-1 h-[2px] mx-3 rounded-full transition-all
            {{ $step >= 3 ? 'bg-amber-500' : 'bg-slate-200' }}">
        </div>
        <!-- Step 3 -->
        <div class="flex items-center">
            <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 transition-all
                {{ $step >= 3 ? 'border-amber-500 bg-amber-50/50 text-amber-600 shadow-sm' : 'border-slate-200 text-slate-400' }}">
                <i class="fa-solid fa-credit-card text-sm
                    {{ $step >= 3 ? 'text-amber-600' : 'text-slate-400' }}"></i>
            </div>
            <span class="ml-2.5 text-sm
                {{ $step >= 3 ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
                Payment
            </span>
        </div>
        <!-- Line 3 -->
        <div class="flex-1 h-[2px] mx-3 rounded-full transition-all
            {{ $step >= 4 ? 'bg-amber-500' : 'bg-slate-200' }}">
        </div>
        <!-- Step 4 -->
        <div class="flex items-center">
            <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 transition-all
                {{ $step >= 4 ? 'border-amber-500 bg-amber-50/50 text-amber-600 shadow-sm' : 'border-slate-200 text-slate-400' }}">
                <i class="fa-solid fa-check text-sm
                    {{ $step >= 4 ? 'text-amber-600' : 'text-slate-400' }}"></i>
            </div>
            <span class="ml-2.5 text-sm
                {{ $step >= 4 ? 'text-slate-900 font-bold' : 'text-slate-400' }}">
                Success
            </span>
        </div>
    </div>
</div>
@if($step == 1)
@include('frontend.cart.cartdetail')
@endif

@if($step == 2)
@include('frontend.cart.deliveryinfo')
@endif

@if($step == 4)
@include('frontend.cart.success')
@endif
@endsection
