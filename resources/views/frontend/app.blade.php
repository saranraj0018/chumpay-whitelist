<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chumpay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/common.css') }}">
</head>

<body class="bg-white font-sans antialiased text-[#0F172A]">
    @include('frontend.components.navbar')
    <main>
        @yield('content')
    </main>
    <div id="toastContainer" class="fixed top-5 right-5 z-[2000] space-y-2"></div>
    @include('frontend.components.footer')

    <script>
        // Auth state exposed to JS
        window.isLoggedIn = @json(auth()->check());

        // Simple toast
        function showToast(message, type = "info") {
            const container = document.getElementById("toastContainer");
            if (!container) return;

            const toast = document.createElement("div");
            const colors = {
                info: "bg-[#0f172a] border border-slate-700 shadow-slate-900/30",
                success: "bg-emerald-600 border border-emerald-500 shadow-emerald-900/30",
                error: "bg-rose-600 border border-rose-500 shadow-rose-900/30",
            };
            toast.className =
                `${colors[type] || colors.info} text-white px-5 py-3.5 rounded-xl shadow-xl text-xs sm:text-sm font-medium transition-all duration-300 transform translate-x-4 opacity-0 border`;
            toast.innerText = message;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove("translate-x-4", "opacity-0");
            });

            setTimeout(() => {
                toast.classList.add("translate-x-4", "opacity-0");
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        function toggleHeart(e, btn) {
            e.preventDefault();
            e.stopPropagation();

            if (!window.isLoggedIn) {
                showToast("Please log in to add wishlist", "error");
                const loginBtn = document.getElementById("LoginBtn");
                if (loginBtn) loginBtn.click();
                return;
            }

            const productId = btn.dataset.productId;
            const icon = btn.querySelector("i");

            // Safe CSRF token read
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : "{{ csrf_token() }}";

            fetch("{{ route('wishlist.toggle') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        product_id: productId
                    }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        if (data.wishlisted) {
                            icon.classList.replace("text-gray-400", "text-red-500");
                            btn.classList.replace("bg-white", "bg-red-50");
                        } else {
                            icon.classList.replace("text-red-500", "text-gray-400");
                            btn.classList.replace("bg-red-50", "bg-white");
                        }
                        showToast(data.message, "success");
                    } else {
                        showToast(data.message || "Something went wrong", "error");
                    }
                })
                .catch(() => showToast("Something went wrong. Try again.", "error"));
        }
    </script>
</body>

</html>