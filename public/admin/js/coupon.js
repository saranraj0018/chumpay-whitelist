$(function () {
    // ===== OPEN CREATE COUPON MODAL =====
    $(document).on("click", "#createCouponBtn", function () {
        document.getElementById("couponForm").reset();
        $("#coupon_id").val("");
        $("#coupon_label").text("Add Coupon");
        $("#save_coupon").text("Save");

        let modal = document.getElementById("couponModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
    });

    // ===== OPEN EDIT COUPON MODAL =====
    $(document).on("click", ".editCouponBtn", function () {
        $("#coupon_id").val($(this).data("id"));
        $("#coupon_code").val($(this).data("code"));
        $("#discount_type").val($(this).data("type"));
        $("#discount_value").val($(this).data("value"));
        $("#description").val($(this).data("description"));
        $("#apply_for").val($(this).data("apply"));
        $("#max_price").val($(this).data("max"));
        $("#min_price").val($(this).data("min"));
        $("#order_count").val($(this).data("order"));
        toggleOrderCount();
        $("#expires_at").val($(this).data("expires"));
        $("#status").val($(this).data("status"));

        if ($(this).data("apply") == 2) {
            $("#expires_at").prop("disabled", true);
        } else {
            $("#expires_at").prop("disabled", false);
        }

        $("#coupon_label").text("Edit Coupon");
        $("#save_coupon").text("Update");

        let modal = document.getElementById("couponModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
    });

    function toggleOrderCount() {
        let applyFor = $("#apply_for").val();
        if (applyFor === "2") {
            $("#order_count_tab").show();
        } else {
            $("#order_count_tab").hide();
            $("#order_count").val(""); // clear value when hidden
        }
    }

    $("#apply_for").on("change", function () {
        toggleOrderCount();
    });

    // ===== COUPON FORM SUBMIT =====
    $(document).on("submit", "#couponForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#save_coupon");
        let applyFor = $("#apply_for").val();
        let min_price = Number($("#min_price").val().replace(/,/g, ""));
        let max_price = Number($("#max_price").val().replace(/,/g, ""));

        // validation fields
        let fields = [
            {
                id: "#coupon_code",
                condition: (val) => val === "",
                message: "Coupon code is required",
            },
            {
                id: "#discount_type",
                condition: (val) => val === "",
                message: "Please select discount type",
            },
            {
                id: "#discount_value",
                condition: (val) => val === "" || val <= 0,
                message: "Discount value is required",
            },
            {
                id: "#description",
                condition: (val) => val === "",
                message: "Description is required",
            },
            {
                id: "#apply_for",
                condition: (val) => val === "",
                message: "Please select apply for",
            },
            {
                id: "#status",
                condition: (val) => val === "",
                message: "Please select status",
            },
        ];
        if (applyFor === "2") {
            fields.push({
                id: "#order_count",
                condition: (val) => val === "" || val <= 0,
                message: "Order count is required for this coupon type",
            });
        }

        if (
            !isNaN(min_price) &&
            !isNaN(max_price) &&
            min_price > 0 &&
            max_price > 0
        ) {
            fields.push(
                {
                    id: "#min_price",
                    condition: () => min_price > max_price,
                    message: "Min price cannot be greater than max price",
                },
                {
                    id: "#max_price",
                    condition: () => max_price < min_price,
                    message: "Max price cannot be less than min price",
                },
            );
        }

        let isValid = true;

        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }

        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving....");

        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/coupon-save", // same route add/update handle
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        let modalScope =
                            document.querySelector("#couponModal").__x.$data;
                        modalScope.open = false; // close modal
                        document.getElementById("couponForm").reset();
                        reloadCouponList();
                    }, 500);
                } else {
                    showToast(res.message, "error", 2000);
                }
                $saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Save");
            },
            function (err) {
                hideLoader();
                if (err.errors) {
                    let msg = "";
                    $.each(err.errors, function (k, v) {
                        msg += v[0] + "<br>";
                    });
                    showToast(msg, "error", 2000);
                } else {
                    showToast(err.message || "Unexpected error", "error", 2000);
                }
                $saveBtn
                    .prop("disabled", false)
                    .removeClass("opacity-50 cursor-not-allowed")
                    .text("Save");
            },
        );
    });

    // ===== DELETE COUPON =====
    $(document).on("click", ".btnDeleteCoupon", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteCouponModal").__x.$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteCoupon = function (id) {
        showLoader();
        sendRequest(
            "/admin/coupon-delete",
            { id: id },
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast("Coupon deleted successfully!", "success", 2000);
                    reloadCouponList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector("#deleteCouponModal").__x.$data.open =
                    false;
            },
            function (err) {
                hideLoader();
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector("#deleteCouponModal").__x.$data.open =
                    false;
            },
        );
    };

    // ===== HELPERS =====
    function reloadCouponList() {
        $.get("/admin/coupon-list", function (html) {
            let $tbody = $(html).find("#couponTableBody").html();
            $("#couponTableBody").html($tbody);
        });
    }
});
