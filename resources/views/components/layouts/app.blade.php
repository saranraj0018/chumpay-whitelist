<!DOCTYPE html>
<html lang="en">
<head>
    <x-partials.header />
</head>

<body class="bg-gray-100 overflow-x-hidden">

<div class="flex min-h-screen">
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-[#FFFFFF]
           transition-all duration-300 z-40 overflow-hidden" style="background:#363636">
        <x-partials.sidebar />
    </aside>

    <!-- ================= RIGHT SECTION ================= -->
    <div class="flex flex-col flex-1">
        <!-- ================= NAVBAR ================= -->
        <div id="navbarWrapper"
            class="fixed top-0 left-64 right-0 transition-all duration-300 z-30">
            <x-partials.navbar />
        </div>

        <!-- ================= MAIN CONTENT ================= -->
        <main id="mainContent"
            class="ml-64 mt-10 p-6 transition-all duration-300 min-h-screen bg-gray-100">
            {{ $slot }}
        </main>

    </div>
     <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>
</div>

<x-partials.scripts />
</body>
</html>
