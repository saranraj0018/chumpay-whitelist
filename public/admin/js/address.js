// public/js/address-manager.js


    document.addEventListener('DOMContentLoaded', () => {
    window.AddressManager.init({
        onChange() {
            if (typeof window.onAddressListChanged === 'function') {
                window.onAddressListChanged(
                    window.AddressManager.getAddresses(),
                    window.AddressManager.getSelectedId()
                );
            }
        },
        selectable: window.ADDRESS_CONFIG?.selectable ?? false
    });
});


window.AddressManager = (function () {
    let addresses = [];
    let selectedAddressId = null;
    let pendingDeleteId = null;
    let opts = { onChange: null, selectable: false };
    let initialized = false; // prevents init() from running more than once

    /* ---------------- helpers ---------------- */

    function csrf() {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    }

    function showToast(message, type) {
        if (typeof window.showToast === "function") {
            window.showToast(message, type);
        } else if (typeof window._globalShowToast === "function") {
            window._globalShowToast(message, type);
        } else {
            alert(message);
        }
    }

    function setFormMessage(message, type) {
        const box = document.getElementById("addressFormMessage");
        if (!box) return;
        box.textContent = message;
        box.classList.toggle("hidden", !message);
        box.classList.toggle("text-red-500", type === "error");
        box.classList.toggle("text-green-600", type === "success");
    }

    /* ---------------- modal ---------------- */

    function openModal(addr = null) {
        const form = document.getElementById("addressForm");
        if (!form) return;

        form.reset();
        document.getElementById("addressId").value = "";
        document.getElementById("addressModalTitle").textContent =
            "Add Address";
        setFormMessage("", "");

        if (addr) {
            document.getElementById("addressModalTitle").textContent =
                "Edit Address";
            document.getElementById("addressId").value = addr.id;
            form.elements["name"].value = addr.name ?? "";
            form.elements["phone_number"].value = addr.phone_number ?? "";
            form.elements["address_type"].value = addr.address_type ?? "";
            form.elements["state"].value = addr.state ?? "";
            form.elements["address"].value = addr.address ?? "";
            form.elements["city"].value = addr.city ?? "";
            form.elements["pincode"].value = addr.pincode ?? "";
            form.elements["landmark"].value = addr.landmark ?? "";
        }
        document.getElementById("addressModal").classList.remove("hidden");
    }

    function closeModal() {
        document.getElementById("addressModal")?.classList.add("hidden");
        setFormMessage("", "");
    }

    function editAddress(id) {
        const addr = addresses.find((a) => String(a.id) === String(id));
        if (addr) openModal(addr);
    }

    /* ---------------- load / render ---------------- */

    async function loadAddresses(preselectId = null) {
        try {
            const res = await fetch(window.ADDRESS_ROUTES.list, {
                method: "GET",
                headers: { Accept: "application/json", "X-CSRF-TOKEN": csrf() },
            });
            const data = await res.json();
            addresses = data.addresses || [];

            if (preselectId && addresses.some((a) => a.id === preselectId)) {
                selectedAddressId = preselectId;
            } else if (
                selectedAddressId &&
                addresses.some((a) => a.id === selectedAddressId)
            ) {
                // keep current selection
            } else {
                const def = addresses.find((a) => a.is_default == 1);
                selectedAddressId = def ? def.id : (addresses[0]?.id ?? null);
            }

            render();
            if (typeof opts.onChange === "function") opts.onChange();
        } catch (e) {
            const box = document.getElementById("addressList");
            if (box)
                box.innerHTML = `<p class="text-sm text-red-500">Failed to load addresses.</p>`;
        }
    }

    function render() {
        const box = document.getElementById("addressList");
        if (!box) return;

        if (!addresses.length) {
            box.innerHTML = `
                <div class="bg-white p-6 rounded-xl border text-center">
                    <p class="text-sm text-gray-500">No address found.</p>
                    <button onclick="AddressManager.openModal()" type="button"
                        class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-black hover:text-white transition">
                        ADD ADDRESS
                    </button>
                </div>`;
            return;
        }

        box.innerHTML = addresses
            .map((a) => {
                const isSelected = a.id === selectedAddressId;
                const isDefault = a.is_default == 1;
                const radio = opts.selectable
                    ? `<input type="radio" name="selectedAddress" class="mt-1" ${isSelected ? "checked" : ""}
                       onchange="AddressManager.selectAddress(${a.id})">`
                    : "";

                return `
            <div class="bg-white p-4 rounded-xl border ${isSelected && opts.selectable ? "border-black ring-1 ring-black" : "border-gray-200"}">
                <div class="flex items-start gap-3">
                    ${radio}
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium">${a.name ?? ""}</h3>
                            <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">${a.address_type ?? ""}</span>
                            ${isDefault ? `<span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-green-100 text-green-700">Default</span>` : ""}
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            ${[a.address, a.landmark, a.city, a.state, a.pincode].filter(Boolean).join(", ")}
                        </p>
                        <p class="text-sm mt-1">Mobile: <span class="font-medium">${a.phone_number ?? ""}</span></p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button type="button" onclick="AddressManager.editAddress(${a.id})"
                                class="text-xs border border-gray-300 rounded-md px-3 py-1.5 hover:bg-gray-100">Edit</button>
                            ${
                                isDefault
                                    ? ""
                                    : `
                            <button type="button" onclick="AddressManager.makeDefault(${a.id})"
                                class="text-xs border border-gray-300 rounded-md px-3 py-1.5 hover:bg-gray-100">Set as Default</button>
                            <button type="button" onclick="AddressManager.deleteAddress(${a.id})"
                                class="text-xs border border-red-300 text-white rounded-md px-3 py-1.5 hover:bg-red-400 bg-red-600">Delete</button>`
                            }
                        </div>
                    </div>
                </div>
            </div>`;
            })
            .join("");
    }

    function selectAddress(id) {
        selectedAddressId = id;
        render();
        if (typeof opts.onChange === "function") opts.onChange();
    }

    /* ---------------- set default ---------------- */

    async function makeDefault(id) {
        try {
            const res = await fetch(window.ADDRESS_ROUTES.setDefault, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf(),
                },
                body: JSON.stringify({ address_id: id }),
            });
            const data = await res.json();
            if (data.status === 200) {
                selectedAddressId = id;
                showToast("Default address updated.", "success");
                await loadAddresses(id);
            } else {
                showToast(
                    data.message || "Could not set default address.",
                    "error",
                );
            }
        } catch (e) {
            showToast("Failed to set default address.", "error");
        }
    }

    /* ---------------- delete ---------------- */

    function deleteAddress(id) {
        pendingDeleteId = id;
        document.getElementById("deleteModal")?.classList.remove("hidden");
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        document.getElementById("deleteModal")?.classList.add("hidden");
    }

    async function confirmDelete() {
        if (!pendingDeleteId) return;
        const id = pendingDeleteId;
        const btn = document.getElementById("confirmDeleteBtn");
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.textContent = "Deleting...";
        try {
            const res = await fetch(window.ADDRESS_ROUTES.delete, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf(),
                },
                body: JSON.stringify({ address_id: id }),
            });
            const data = await res.json();
            if (data.status !== 200) {
                showToast(data.message || "Could not delete address.", "error");
            } else {
                showToast("Address deleted successfully!", "success");
            }
            if (selectedAddressId === id) selectedAddressId = null;
            closeDeleteModal();
            await loadAddresses();
        } catch (e) {
            showToast("Failed to delete address. Please try again.", "error");
        } finally {
            btn.disabled = false;
            btn.textContent = "Delete";
        }
    }

    /* ---------------- save (add / edit) ---------------- */

    /* ---------------- validation ---------------- */

    const VALIDATORS = {
        name: {
            regex: /^[A-Za-z\s.'-]{2,50}$/,
            message: "Enter a valid name (letters only, 2-50 characters).",
        },
        phone_number: {
            regex: /^[6-9]\d{9}$/,
            message: "Enter a valid 10-digit mobile number.",
        },
        address_type: {
            regex: /^(home|work|other)$/,
            message: "Please select an address type.",
        },
        state: {
            regex: /^[A-Za-z\s]{2,50}$/,
            message: "Enter a valid state name (letters only).",
        },
        address: {
            regex: /^.{5,150}$/,
            message: "Address must be between 5 and 150 characters.",
        },
        city: {
            regex: /^[A-Za-z\s]{2,50}$/,
            message: "Enter a valid city name (letters only).",
        },
        pincode: {
            regex: /^[1-9][0-9]{5}$/,
            message: "Enter a valid 6-digit pincode.",
        },
        landmark: {
            regex: /^.{0,100}$/,
            message: "Landmark is too long.",
        },
    };

    function validateField(fieldName, value) {
        const rule = VALIDATORS[fieldName];
        if (!rule) return null;
        const trimmed = (value ?? "").trim();
        if (!rule.regex.test(trimmed)) return rule.message;
        return null;
    }

    function validateForm(payload) {
        // landmark is optional — skip empty
        for (const field of Object.keys(VALIDATORS)) {
            if (field === "landmark" && !payload.landmark) continue;
            const error = validateField(field, payload[field]);
            if (error) return { valid: false, field, message: error };
        }
        return { valid: true };
    }

    function restrictNumericInputs(form) {
        const phoneInput = form.elements["phone_number"];
        const pincodeInput = form.elements["pincode"];

        if (phoneInput) {
            phoneInput.setAttribute("inputmode", "numeric");
            phoneInput.setAttribute("maxlength", "10");
            phoneInput.addEventListener("input", function () {
                this.value = this.value.replace(/\D/g, "").slice(0, 10);
            });
        }

        if (pincodeInput) {
            pincodeInput.setAttribute("inputmode", "numeric");
            pincodeInput.setAttribute("maxlength", "6");
            pincodeInput.addEventListener("input", function () {
                this.value = this.value.replace(/\D/g, "").slice(0, 6);
            });
        }
    }

    function bindFormSubmit() {
        const form = document.getElementById("addressForm");
        if (!form) return;

        const freshForm = form.cloneNode(true);
        form.parentNode.replaceChild(freshForm, form);

        restrictNumericInputs(freshForm); // <-- add this line

        freshForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            const btn = document.getElementById("addressSubmitBtn");
            if (!btn || btn.disabled) return;

            const payload = Object.fromEntries(new FormData(this).entries());

            // ---- custom validation ----
            const result = validateForm(payload);
            if (!result.valid) {
                setFormMessage(result.message, "error");
                const el = this.elements[result.field];
                if (el) el.focus();
                return;
            }
            // ----------------------------

            btn.disabled = true;
            btn.textContent = "Saving...";
            setFormMessage("", "");

            try {
                const res = await fetch(window.ADDRESS_ROUTES.save, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrf(),
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (!res.ok || data.status !== 200) {
                    showToast(
                        data.message || "Please fill all required fields.",
                        "error",
                    );
                    return;
                }

                showToast(data.message || "Saved.", "success");
                await loadAddresses(data.address?.id ?? selectedAddressId);
                setTimeout(closeModal, 400);
            } catch (err) {
                showToast("Failed to save address. Please try again.", "error");
            } finally {
                btn.disabled = false;
                btn.textContent = "Save Address";
            }
        });
    }

    /* ---------------- init ---------------- */

    function init(initOpts = {}) {
        if (initialized) return; // hard guard: never bind twice
        initialized = true;

        opts = { ...opts, ...initOpts };

        document
            .getElementById("addressModal")
            ?.addEventListener("click", function (e) {
                if (e.target === this) closeModal();
            });

        document
            .getElementById("deleteModal")
            ?.addEventListener("click", function (e) {
                if (e.target === this) closeDeleteModal();
            });

        document
            .getElementById("confirmDeleteBtn")
            ?.addEventListener("click", confirmDelete);

        bindFormSubmit();
        loadAddresses();
    }

    /* ---------------- public API ---------------- */

    return {
        init,
        openModal,
        closeModal,
        editAddress,
        selectAddress,
        makeDefault,
        deleteAddress,
        closeDeleteModal,
        getAddresses: () => addresses,
        getSelectedId: () => selectedAddressId,
    };
})();
