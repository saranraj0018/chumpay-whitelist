$(function () {
    // ===== OPEN VIEW ORDER MODAL =====
    $(document).on("click", ".viewOrderBtn", function () {
        let order = $(this).data("order");
        // Fill order data inside modal
        $("#orderModalTitle").text("Order #" + order.order_id);
        $("#idSet").val(order.id);
        $("#orderCustomerName").text(order.user?.name ?? "Guest");
        $("#orderCustomerEmail").text(order.user?.email ?? "—");
        $("#orderCustomerMobile").text(order.phone ?? "—");
        // BEFORE
        $("#status").val(order.status);
        toggleRefundFields(order.status);
        $("#addressCustomerMobile").text(
            order.user_address?.phone_number ?? "—",
        );
        $("#addressCustomerAddress").text(order.user_address?.address ?? "—");
        $("#addressCustomerAddressType").text(
            order.user_address?.address_type ?? "—",
        );
        $("#addressCustomerCity").text(order.user_address?.city ?? "—");
        $("#addressCustomerState").text(order.user_address?.state ?? "—");
        $("#addressCustomerPincode").text(order.user_address?.pincode ?? "—");
        $("#addressCustomerName").text(order.user_address?.name ?? "—");

        $("#orderSubtotal").text("₹" + (order.net_amount ?? 0));
        $("#orderGST").text("₹" + (order.gst_amount ?? 0));
        $("#orderShipping").text("₹" + (order.shipping_amount ?? 0));
        $("#orderCoupon").text("-₹" + (order.coupon_amount ?? 0));
        $("#orderGrandTotal").text("₹" + (order.gross_amount ?? 0));
        // REPLACE with this
        if (order.refund_image) {
            $("#refundImagePreview")
                .attr("src", "/storage/" + order.refund_image)
                .removeClass("hidden");
        } else {
            $("#refundImagePreview").attr("src", "").addClass("hidden");
        }
        $("#refundNote").val(order.refund_note ?? "");
        // status & date
        let status = parseInt(order.status);
        var get_status = $("#status").val(order.status);
        let statusDate = null;
        switch (status) {
            case 3:
                statusDate = order.shipped_at;
                break;
            case 4:
                statusDate = order.delivered_at;
                break;
            case 5:
                statusDate = order.cancelled_at;
                break;
            case 6:
                statusDate = order.refunded_at;
                break;
        }

        if (statusDate) {
            let dt = new Date(statusDate);
            if (!isNaN(dt.getTime())) {
                let yyyy = dt.getFullYear();
                let mm = String(dt.getMonth() + 1).padStart(2, "0");
                let dd = String(dt.getDate()).padStart(2, "0");

                $("#statusDate").val(`${yyyy}-${mm}-${dd}`);
            } else {
                $("#statusDate").val("");
            }
        } else {
            $("#statusDate").val("");
        }

        let tbody = $("#orderProductsBody");
        tbody.empty(); // clear old data first
        if (order.order_details && order.order_details.length > 0) {
            order.order_details.forEach((item, index) => {
                const netAmount = Number(item.net_amount || 0);
                const quantity = Number(item.quantity || 0);
                const total = quantity * netAmount;
                const isSingle = item.product?.product_type === "single";
                const matrix = Array.isArray(item.matrix) ? item.matrix : [];

                let sizeHtml = "—";
                let colourHtml = "—";
                if (!isSingle) {
                    if (matrix.length > 0) {
                        sizeHtml = matrix
                            .map((c) => `${c.size ?? "-"} x ${c.qty ?? 0}`)
                            .join("<br>");
                        colourHtml = matrix
                            .map((c) => c.color ?? "-")
                            .join("<br>");
                    } else {
                        sizeHtml = item.variant_size_value?.value ?? "—";
                        colourHtml = item.variant_color_value?.value ?? "—";
                    }
                }

                const row = `
        <tr>
            <td class="px-3 py-2">${index + 1}</td>
            <td class="px-3 py-2">${item.product_name ?? "N/A"}</td>
            <td class="px-3 py-2">${sizeHtml}</td>
            <td class="px-3 py-2">${colourHtml}</td>
            <td class="px-3 py-2">${quantity}</td>
            <td class="px-3 py-2">₹${netAmount.toFixed(2)}</td>
            <td class="px-3 py-2">₹${total.toFixed(2)}</td>
        </tr>
        `;

                tbody.append(row);
            });
        } else {
            tbody.append(`
        <tr>
            <td colspan="7" class="text-center py-3 text-gray-500">No products found in this order.</td>
        </tr>
    `);
        }
        // Show modal
        $("#orderModal").css("display", "flex");
    });

    function toggleRefundFields(statusVal) {
        if (parseInt(statusVal) === 6) {
            $("#refundFields").slideDown(200);
        } else {
            $("#refundFields").slideUp(200);
            $("#refundNote").val("");
            $("#refundImage").val("");
            // clear preview too
            $("#refundImagePreview").attr("src", "").addClass("hidden");
        }
    }

    $(document).on("change", "#refundImage", function () {
        const file = this.files[0];

        if (file) {
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                showToast("Image size must be less than 2MB", "error", 2000);
                $(this).val("");
                $("#refundImagePreview").attr("src", "").addClass("hidden");
                return;
            }
            if (!file.type.startsWith("image/")) {
                showToast("Only image files are allowed", "error", 2000);
                $(this).val("");
                $("#refundImagePreview").attr("src", "").addClass("hidden");
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#refundImagePreview")
                    .attr("src", e.target.result)
                    .removeClass("hidden");
            };
            reader.readAsDataURL(file);
        } else {
            $("#refundImagePreview").attr("src", "").addClass("hidden");
        }
    });

    $(document).on("change", "#status", function () {
        toggleRefundFields($(this).val());
    });

    // ===== CLOSE MODAL =====
    $(document).on(
        "click",
        "#closeModalBtn, #cancelModalBtn, #orderModalBackdrop",
        function () {
            $("#orderModal").fadeOut(200);
        },
    );

    // ===== SAVE ORDER STATUS =====
    $(document).on("click", "#saveStatusBtn", function (e) {
        e.preventDefault();
        let orderTitle = $("#orderModalTitle").text();
        let orderId = orderTitle.replace("Order #", "").trim();
        let Id = $("#idSet").val();
        let status = $("#status").val();
        let date = $("#statusDate").val();
        let refundImage = $("#refundImage").val();
        let refundNote = $("#refundNote").val();
        let $saveBtn = $("#saveStatusBtn");

        if (!status || !date) {
            showToast("Please select both status and date", "error", 2000);
            return;
        }

        if (status == 6 && !refundImage) {
            showToast("Please upload a refund image", "error", 2000);
            return;
        }

        if (status == 6 && refundNote == "") {
            showToast("Please enter a refund note", "error", 2000);
            return;
        }

        // Declare formData FIRST before using it
        let formData = new FormData();

        formData.append("order_id", orderId);
        formData.append("status", status);
        formData.append("date", date);
        formData.append("id", Id);
        formData.append("refund_note", refundNote);

        // Handle file append AFTER formData is declared
        const refundFile = $("#refundImage")[0].files[0];
        if (refundFile) {
            formData.append("refund_image", refundFile);
        } else {
            formData.append(
                "existing_image",
                $("#refundImagePreview").attr("src"),
            );
        }

        $saveBtn
            .prop("disabled", true)
            .addClass("opacity-50 cursor-not-allowed")
            .text("Saving....");

        showLoader();

        sendRequest(
            "/admin/orders/update-status",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#orderModal").fadeOut(200);
                        reloadOrderList();
                    }, 500);
                } else {
                    showToast("Something went wrong!", "error", 2000);
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

    // ===== HELPER: RELOAD ORDER LIST =====
    function reloadOrderList() {
        $.get("/admin/orders/list", function (html) {
            let $tbody = $(html).find("#orderTableBody").html();
            $("#orderTableBody").html($tbody);
        });
    }
});
