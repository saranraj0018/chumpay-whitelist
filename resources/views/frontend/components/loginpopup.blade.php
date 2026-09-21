<div id="LoginPopup" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center z-[1000]">
    <!-- Modal Panel -->
    <div id="LoginPanel"
        class="
    w-[320px]          /* Mobile */
    sm:w-[320px]       /* Small devices */
    md:w-[420px]       /* Tablet */
    lg:w-[750px]       /* Laptop */
    xl:w-[850px]       /* Desktop */

    max-h-[650px]      /* Fixed height limit */
    sm:max-h-[680px]
    md:max-h-[720px]
    lg:max-h-[760px]

    rounded-2xl
    shadow-2xl

    transform scale-90 opacity-0
    transition-all duration-300 ease-out

    relative overflow-y-auto
    mx-auto
    ">
        <!-- Close Button -->
        <button class="closeLogin absolute top-4 right-5 text-2xl text-slate-400 hover:text-slate-900 transition-colors"> &times; </button>
        <!-- Popup Content -->

        <div class="text-center space-y-4">
            <div class="flex flex-col md:flex-row">

                <div class="hidden lg:flex lg:w-1/2 items-center justify-center">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo"
                        class="max-w-md object-contain w-full h-auto">
                </div>

                <div
                    class="w-full lg:w-1/2
                    px-6 py-10
                    sm:px-10 sm:py-12
                    md:px-16 md:py-16
                    lg:p-[30px]
                    flex flex-col justify-center bg-white">

                    <div id="mobileSection">
                        <div class="flex justify-center mb-6 sm:hidden">
                            <img src="{{ asset('assets/images/popupmobilelogo.png') }}" alt="Chumpay Logo"
                                class="h-8 w-[130px]">
                        </div>


                        <h2 class="text-lg sm:text-2xl font-semibold mb-4 sm:mb-5 text-left">
                            Get Started!
                        </h2>

                        <label class="text-sm font-medium text-gray-600 mb-2 block text-left">
                            Enter Mobile Number
                        </label>

                        <input type="text" id="mobileInput" placeholder="Enter 10 digit mobile number"
                            oninput="clearMobileError()"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl mb-4
                           focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">

                        <p id="mobileError" class="text-red-500 text-sm mb-4 hidden text-left"></p>

                        <button onclick="validateMobile()"
                            class="w-full bg-[#0f172a] text-white py-3 rounded-xl font-semibold
                           hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                            Continue
                        </button>

                    </div>

                    <div id="otpSection" class="hidden">
                        <div class="flex justify-center mb-6 sm:hidden">
                            <img src="{{ asset('assets/images/popupmobilelogo.png') }}" alt="Chumpay Logo"
                                class="h-8 w-[130px]">
                        </div>

                        <h2 class="text-xl sm:text-2xl font-semibold mb-2 text-left">
                            Verify Your Number
                        </h2>

                        <p class="text-gray-500 text-sm mb-6 text-left">
                            Enter OTP sent to <span id="displayNumber"></span> via SMS
                        </p>

                        <div class="flex justify-center sm:justify-start gap-3 sm:gap-4 mb-6">
                            <input type="text" maxlength="1"
                                class="otp-input w-12 h-12 sm:w-14 sm:h-14
                               text-center text-lg sm:text-xl font-bold text-slate-900
                               border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">
                            <input type="text" maxlength="1"
                                class="otp-input w-12 h-12 sm:w-14 sm:h-14
                               text-center text-lg sm:text-xl font-bold text-slate-900
                               border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">
                            <input type="text" maxlength="1"
                                class="otp-input w-12 h-12 sm:w-14 sm:h-14
                               text-center text-lg sm:text-xl font-bold text-slate-900
                               border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">
                            <input type="text" maxlength="1"
                                class="otp-input w-12 h-12 sm:w-14 sm:h-14
                               text-center text-lg sm:text-xl font-bold text-slate-900
                               border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition">
                        </div>
                        <p id="otpError" class="text-red-500 text-sm mb-4 hidden text-left"></p>

                        <button onclick="verifyOTP()"
                            class="w-full bg-[#0f172a] text-white py-3 rounded-xl font-semibold
                           hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                            Continue
                        </button>

                        <p class="text-sm text-gray-500 mt-4 text-left">
                            Didn’t get the code yet?
                            <span class="font-medium">Resend (0:35)</span>
                        </p>

                    </div>


                    <div id="welcomeSection" class="hidden">
                        <div class="flex justify-center mb-6 sm:hidden">
                            <img src="{{ asset('assets/images/popupmobilelogo.png') }}" alt="Chumpay Logo"
                                class="h-8 w-[130px]">
                        </div>

                        <h2 class="text-2xl sm:text-lg font-semibold mb-6 sm:mb-4 text-left">
                            Welcome to Chumpay
                        </h2>

                        <label class="text-sm font-medium text-slate-700 mb-2 block text-left">Enter your name</label>
                        <input type="text" id="regName" placeholder="What should we call you?"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition mb-4">

                        <label class="text-sm font-medium text-slate-700 mb-2 block text-left">Your email address</label>
                        <input type="email" id="regEmail" placeholder="We'll send updates and order details here"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition mb-4">

                        <p id="welcomeError" class="text-red-500 text-sm mb-4 hidden text-left"></p>

                        <button onclick="registerUser()"
                            class="w-full bg-[#0f172a] text-white py-3 rounded-xl font-semibold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                            Continue
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
    let currentMobile = "";

    function validateMobile() {
        const mobile = document.getElementById("mobileInput").value;
        const error = document.getElementById("mobileError");

        if (!/^[0-9]{10}$/.test(mobile)) {
            error.innerText = "Please enter valid 10 digit mobile number";
            error.classList.remove("hidden");
            return;
        }
        error.classList.add("hidden");

        fetch("{{ route('send.otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    mobile
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    currentMobile = mobile;
                    document.getElementById("mobileSection").classList.add("hidden");
                    document.getElementById("otpSection").classList.remove("hidden");
                    document.getElementById("displayNumber").innerText =
                        "+91 " + mobile.slice(0, 6) + "****";
                } else {
                    error.innerText = data.message;
                    error.classList.remove("hidden");
                }
            })
            .catch(() => alert("Something went wrong. Try again."));
    }

    function clearMobileError() {
        document.getElementById("mobileError").classList.add("hidden");
    }

    function verifyOTP() {
        const inputs = document.querySelectorAll(".otp-input");
        let otp = "";
        inputs.forEach(input => otp += input.value.trim());

        // Validation: all 4 digits required and numeric
        if (otp.length !== inputs.length) {
            showOtpError("Please enter the complete " + inputs.length + "-digit OTP");
            return;
        }
        if (!/^[0-9]+$/.test(otp)) {
            showOtpError("OTP must contain only numbers");
            return;
        }
        clearOtpError();

        fetch("{{ route('verify.otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    mobile: currentMobile,
                    otp
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    if (data.is_new) {
                        document.getElementById("otpSection").classList.add("hidden");
                        document.getElementById("welcomeSection").classList.remove("hidden");
                    } else {
                        window.location.reload();
                    }
                } else {
                    showOtpError(data.message || "Invalid OTP");
                }
            })
            .catch(() => showOtpError("Something went wrong. Try again."));
    }

    function showOtpError(msg) {
        const el = document.getElementById("otpError");
        el.innerText = msg;
        el.classList.remove("hidden");
    }

    function clearOtpError() {
        document.getElementById("otpError").classList.add("hidden");
    }

    function registerUser() {
        const name = document.getElementById("regName").value.trim();
        const email = document.getElementById("regEmail").value.trim();
        const errEl = document.getElementById("welcomeError");

        if (!name || !email) {
            errEl.innerText = "Please fill all fields";
            errEl.classList.remove("hidden");
            return;
        }

        fetch("{{ route('register.user') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    mobile: currentMobile,
                    name,
                    email
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    window.location.reload();
                } else {
                    errEl.innerText = data.message;
                    errEl.classList.remove("hidden");
                }
            })
            .catch(() => alert("Something went wrong. Try again."));
    }

    document.querySelectorAll(".otp-input").forEach((input, index, inputs) => {
        input.addEventListener("input", () => {
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
    });
</script>