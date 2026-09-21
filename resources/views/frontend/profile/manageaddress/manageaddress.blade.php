@extends('frontend.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 sm:py-8 md:py-10">
    <div class="flex flex-col lg:flex-row gap-5 md:gap-6 lg:gap-[25px]">
        <!-- Sidebar -->
        <div class="w-full lg:w-[25%]">
            @include('frontend.profile.sidebar')
        </div>
        <!-- Main Content -->
        <div class="w-full lg:flex-1">
            <div class="bg-white rounded-2xl p-4 sm:p-6 md:p-8 border border-slate-200/80 shadow-sm">
                @include('frontend.partials.address-manager', [
                    'title' => 'Manage Address',
                    'selectable' => false,
                ])
            </div>
        </div>
    </div>
</div>
@endsection
