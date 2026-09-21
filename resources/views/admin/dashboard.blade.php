<x-layouts.app>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-5">
<div class="max-w-7xl mx-auto space-y-5">
<h1 class="text-3xl font-semibold text-slate-800">Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-[#BDE3C3] rounded-2xl shadow-md p-6 hover:scale-105 transition">
        <p class="text-[#14532D] text-sm font-medium">Total Customers</p>
        <h2 class="text-3xl font-bold text-[#064E3B] mt-2">567,899</h2>
        <p class="text-green-700 text-sm mt-1 font-medium">↑ 2.5%</p>
    </div>
    <div class="bg-[#E5BEB5] rounded-2xl shadow-md p-6 hover:scale-105 transition">
        <p class="text-[#7C2D12] text-sm font-medium">Revenue</p>
        <h2 class="text-3xl font-bold text-[#431407] mt-2">$3,465M</h2>
        <p class="text-green-700 text-sm mt-1 font-medium">↑ 1.2%</p>
    </div>
    <div class="bg-[#91ADC8] rounded-2xl shadow-md p-6 hover:scale-105 transition">
        <p class="text-[#0C4A6E] text-sm font-medium">Orders</p>
        <h2 class="text-3xl font-bold text-[#082F49] mt-2">1,136</h2>
        <p class="text-red-600 text-sm mt-1 font-medium">↓ 0.3%</p>
    </div>
    <div class="bg-[#FEEBF6] rounded-2xl shadow-md p-6 hover:scale-105 transition">
        <p class="text-[#831843] text-sm font-medium">Pending</p>
        <h2 class="text-3xl font-bold text-[#4C0519] mt-2">320</h2>
        <p class="text-red-600 text-sm mt-1 font-medium">↓ 1.1%</p>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="chart-card"><h2>Bar Chart</h2><div id="barChart"></div></div>
    <div class="chart-card"><h2>Pie Chart</h2><div id="pieChart"></div></div>
</div>
</div>
</div>
</x-layouts.app>
