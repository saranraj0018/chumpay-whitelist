   {{-- @props(['url' => '/'])
   <a href="{{ $url }}" class="flex items-center gap-3 p-4 border-b border-white/30">
       <img src="/pasumai.png" alt="RYH Textiles Logo" class="h-10 w-10 object-contain">
       <span class="text-lg font-bold text-[#ffffffcc] hover:text-white">RYH Textiles</span>
   </a> --}}
<style>
#sidebar.w-15 a {
    justify-content: center;
}
/* #sidebar.collapsed:hover {
    width: 5rem !important;
} */
</style>
   @props(['url' => '/'])
<a href="{{ $url }}" class="flex items-center gap-3 p-4 border-b border-white/30">
 <img src="{{ asset('assets/images/logo.png') }}" alt="RYH Textiles Logo" class="text-white h-8 w-auto max-h-8 rounded-full shadow-lg object-cover float-left ml-3 mr-2 -mt-1">
    <span class="sidebar-text text-lg font-bold text-white transition-all duration-300">
        RYH Textiles Whitelist
    </span>
</a>
