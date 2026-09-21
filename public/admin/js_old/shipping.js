$(function () {
    // CREATE SHIPPING
    $(document).on("click", "#createShippingBtn", function () {
        let modal = document.getElementById("shippingModal");
        let alpine = modal.__x.$data;
        alpine.form = {
            shipping_id: 0,
            name: "",
            delivery_fee: 0,
            minimum_delivery_amount: 0,
            maximum_delivery_amount: 0,
            status: 1
        };
        $("#shippingModal").css("display", "flex");
        $("#shipping_label").text("Add Shipping Rule");
        $("#save_shipping").text("Save");
    });

    // EDIT SHIPPING
    $(document).on("click", ".editShippingBtn", function () {
        let modal = document.getElementById("shippingModal");
        let alpine = modal.__x.$data;
        alpine.form.shipping_id = $(this).data("id");
        alpine.form.name = $(this).data("name");
        alpine.form.delivery_fee = $(this).data("delivery_fee");
        alpine.form.minimum_delivery_amount = $(this).data("minimum_delivery_amount");
        alpine.form.maximum_delivery_amount = $(this).data("maximum_delivery_amount");
        alpine.form.status = $(this).data("status");
        $("#shippingModal").css("display", "flex");
        $("#shipping_label").text("Edit Shipping Rule");
        $("#save_shipping").text("Update");
    });

    // SAVE SHIPPING
    $(document).on("submit", "#shippingForm", function (e) {
        e.preventDefault();
        let saveBtn = $("#save_shipping");

        let fields = [
            {
                id: "#name",
                condition: (val) => val === "",
                message: "Name is required",
            },
        ];

        let isValid = true;

        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }

        // min/max validation
        const min = $("#minimum_delivery_amount").val();
        const max = $("#maximum_delivery_amount").val();
        if ((min === "" && max === "") || (min === "0" && max === "0")) {
            isValid = false;
            showToast("Enter minimum or maximum amount", "error", 2000);
        }

        if (!isValid) return;

        let formData = new FormData(this);
        let error_msg = "";
        saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving...");
        sendRequest(
            "/admin/shipping/save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    $("#shippingModal").hide();
                    reloadShippingList();
                } else {
                    showToast(
                        res.message ?? "Something went wrong",
                        "error",
                        2000,
                    );
                }
                saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Saving...");
            },
            function (xhr) {
                let message = "Something went wrong";
                if (xhr.message) {
                    message = xhr.message;
                }
                showToast(message, "error", 3000);
                saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Saving...");
            },
        );
    });

    // DELETE SHIPPING
    $(document).on("click", ".btnDeleteShipping", function () {
        let modalScope = document.querySelector("#deleteShippingModal").__x
            .$data;
        modalScope.deleteId = $(this).data("id");
        modalScope.open = true;
    });

    window.deleteShipping = function (id) {
        sendRequest(
            "/admin/shipping/delete",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Deleted Successfully!", "success");
                    reloadShippingList();
                } else {
                    showToast(res.message, "error");
                }
                document.querySelector("#deleteShippingModal").__x.$data.open =
                    false;
            },
        );
    };

    // OPEN TAX MODAL
    $(document).on("click", "#openTaxBtn", function () {
        let modal = document.getElementById("taxModal");
        let alpine = modal.__x.$data;

        $.get("/admin/shipping/tax/get", function (res) {
            alpine.tax.id = res.id;
            alpine.tax.percent = res.percent;
        });

        $("#taxModal").css("display", "flex");
    });

    // OPEN TAX MODAL
    $(document).on("click", "#openShippingBtn", function () {
        let modal = document.getElementById("d_shippingModal");
        let alpine = modal.__x.$data;

        $.get("/admin/shipping/amount/get", function (data) {
            alpine.shipping_amount.id = data.id;
            alpine.shipping_amount.default_shipping = data.default_shipping;
        });

        $("#d_shippingModal").css("display", "flex");
    });

    $(document).on("click", "#openPlatformBtn", function () {
        let modal = document.getElementById("d_platformfeeModal");
        let alpine = modal.__x.$data;
        $.get("/admin/shipping/amount/platform_fee/get", function (data) {
            alpine.platform_fee.id = data.id;
            alpine.platform_fee.platform_fee = data.platform_fee;
        });
        $("#d_platformfeeModal").css("display", "flex");
    });

    // SAVE TAX
    $(document).on("submit", "#taxForm", function (e) {
        e.preventDefault();
        if ($("#percent").val() == "") {
            showToast("Tax is required", "error");
            return;
        }
        let data = new FormData(this);
        sendRequest(
            "/admin/shipping/tax/save",
            data,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Tax Saved Successfully!", "success");
                    $("#taxModal").hide();
                } else {
                    showToast(res.message, "error");
                }
            },
            function () {
                showToast("Something went wrong!", "error");
            },
        );
    });

    $(document).on("submit", "#defaultShippingForm", function (e) {
        e.preventDefault();
        if ($("#shipping_amount").val() == "") {
            showToast("Shipping Amount is required", "error");
            return;
        }
        let data = new FormData(this);
        sendRequest(
            "/admin/shipping/amount/save",
            data,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Shipping Saved Successfully!", "success");
                    $("#d_shippingModal").hide();
                } else {
                    showToast(res.message, "error");
                }
            },
            function () {
                showToast("Something went wrong!", "error");
            },
        );
    });

    $(document).on("submit", "#platformfeeForm", function (e) {
        e.preventDefault();
        if ($("#platform_fee").val() == "") {
            showToast("Platform fee is required", "error");
            return;
        }
        let data = new FormData(this);

        sendRequest(
            "/admin/shipping/amount/platform_fee/save",
            data,
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Platform fee saved successfully!", "success");
                    $("#d_platformfeeModal").hide();
                } else {
                    showToast(res.message, "error");
                }
            },
            function () {
                showToast("Something went wrong!", "error");
            },
        );
    });

    // RELOAD LIST
    function reloadShippingList() {
        $.get("/admin/shipping/list", function (html) {
            let tbody = $(html).find("#shippingTableBody").html();
            $("#shippingTableBody").html(tbody);
        });
    }
});
