$(function () {
    $(document).on("click", "#createWalletBtn", function () {
        document.getElementById("walletForm").reset();
        let modal = document.getElementById("walletModal");
        let alpine = modal.__x.$data;
        alpine.form.name = "";
        alpine.form.status = 1;
        alpine.form.cat_id = "";
        alpine.form.maximum_amount = "";
        alpine.form.minimum_amount = "";
        alpine.form.bonus_amount = "";
        alpine.form.item_amount = "";
        alpine.form.valid_days = "";
        $("#walletModal").css("display", "flex");
        $("#wallet_label").text("Add Wallet Bonus");
        $("#save_wallet_form").text("Save");
    });

    $(document).on("click", ".editWalletBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let status = $(this).data("status");
        let minimum_amount = $(this).data("minimum_amount");
        let maximum_amount = $(this).data("maximum_amount");
        let item_amount = $(this).data("item_amount");
        let bonus_amount = $(this).data("bonus_amount");
        let valid_days = $(this).data("valid_days");

         // open modal
        $("#walletModal").css("display", "flex");
        $("#wallet_label").text("Edit Wallet Bonus");
        $("#save_wallet_form").text("Update");

        // update Alpine state for preview
        let modal = document.getElementById("walletModal");
        let alpine = modal.__x.$data;
        alpine.form.name = name;
        alpine.form.status = status;
        alpine.form.bonus_id = id;
        alpine.form.minimum_amount = minimum_amount;
        alpine.form.maximum_amount = maximum_amount;
        alpine.form.item_amount = item_amount;
        alpine.form.bonus_amount = bonus_amount;
        alpine.form.valid_days = valid_days;
    });

    // Use event delegation because #categoryForm may not exist initially
    $(document).on("submit", "#walletForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#save_wallet_form");
        // Fields to validate
        let fields = [
            {
                id: "#maximum_amount",
                condition: (val) => val === "",
                message: "Maximum amount is required",
            },
            {
                id: "#bonus_amount",
                condition: (val) => val === "",
                message: "Bonus amount is required",
            },
            {
                id: "#item_amount",
                condition: (val) => val === "",
                message: "Item amount is required",
            },
            {
                id: "#valid_days",
                condition: (val) => val === "",
                message: "Valid days is required",
            }
        ];

        let isValid = true;
        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }

        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving...");

        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/wallet-bonus-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#walletModal").hide();
                        let modal = document.querySelector("#walletModal");
                        let alpine = modal.__x.$data;
                        alpine.form = {
                            name: "",
                            status: "1",
                            minimum_amount: "",
                            maximum_amount: "",
                            bonus_amount: "",
                            item_amount: "",
                            valid_days: ""
                        };
                        alpine.previewUrl = null;
                        document.getElementById("walletForm").reset();

                        $.get("/admin/wallet-bonus-list", function (html) {
                            let $tbody = $(html)
                                .find("#walletTableBody")
                                .html();
                            $("#walletTableBody").html($tbody);
                        });
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

    // ==== DELETE =====
    $(document).on("click", ".btnDeleteWallet", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteWalletModal").__x
            .$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteWalletBonus = function (id) {
        showLoader();
        sendRequest(
            "/admin/wallet-bonus-delete",
            { id: id },
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Wallet bonus deleted successfully!",
                        "success",
                        2000,
                    );
                    reloadCategoryList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector("#deleteWalletModal").__x.$data.open =
                    false;
            },
            function (err) {
                hideLoader();
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector("#deleteWalletModal").__x.$data.open =
                    false;
            },
        );
    };

    // ===== Helpers =====
    function reloadCategoryList() {
        $.get("/admin/wallet-bonus-list", function (html) {
            let $tbody = $(html).find("#walletTableBody").html();
            $("#walletTableBody").html($tbody);
        });
    }
});
