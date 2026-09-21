@extends('frontend.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-10">

    <h4 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-6 tracking-tight">Wishlist</h4>

    @if($products->count() > 0)

        <!-- PRODUCT GRID -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('frontend.components.productcard', ['product' => $product])
            @endforeach
        </div>

        <!-- PAGINATION -->
        <div id="pagination" class="flex justify-center mt-10 gap-2 flex-wrap"></div>

    @else

        <!-- EMPTY WISHLIST -->
        <div class="flex items-center justify-center px-4 py-12">
            <div class="max-w-4xl w-full bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/80 shadow-xs md:flex md:items-center md:justify-between gap-8">
                <div class="flex justify-center mb-6 md:mb-0 w-full md:w-[50%]">
                    <img src="{{ asset('assets/images/whistlist.svg') }}" alt="No wishlist" class="w-full max-w-xs">
                </div>
                <div class="text-center md:text-left w-full md:w-[50%]">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-3 tracking-tight">No items in wishlist</h2>
                    <p class="text-slate-500 text-sm mb-1">Your favorites live here.</p>
                    <p class="text-slate-500 text-sm mb-6">Like and collect the items you love while shopping.</p>
                    <a href="{{ url('/shop') }}"
                        class="inline-block bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white px-8 py-3.5 rounded-xl font-semibold text-sm transition-all duration-200 shadow-md cursor-pointer">
                        Start Shopping
                    </a>
                </div>
            </div>
        </div>

    @endif

</div>

{{-- keep your existing pagination <script> here, unchanged --}}

@endsection
