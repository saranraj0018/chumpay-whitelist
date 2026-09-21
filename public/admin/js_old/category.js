$(function () {
    $(document).on("click", "#createCategoryBtn", function () {
        document.getElementById("categoryForm").reset();
        let modal = document.getElementById("categoryModal");
        let alpine = modal.__x.$data;
        alpine.previewUrl = "";
        alpine.exiting_image = "";
        alpine.form.name = "";
        alpine.form.status = 1;
        alpine.form.cat_id = 0;
        $("#categoryModal").css("display", "flex");
        $("#category_label").text("Add Category");
        $("#save_cat").text("Save");
    });

    $(document).on("click", ".editCategoryBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let status = $(this).data("status");
        let gender = $(this).data("gender");
        let parent_id = $(this).data("parent_id");
        let image = $(this).data("image");
        // open modal
        $("#categoryModal").css("display", "flex");
        $("#category_label").text("Edit Category");
        $("#save_cat").text("Update");

        // update Alpine state for preview
        let modal = document.getElementById("categoryModal");
        let alpine = modal.__x.$data;
        alpine.previewUrl = image || "";
        alpine.exiting_image = image || "";
        alpine.form.name = name;
        alpine.form.status = status;
        alpine.form.cat_id = id;
        alpine.form.gender = gender;
        alpine.form.parent_id = parent_id;
    });

    // Use event delegation because #categoryForm may not exist initially
    $(document).on("submit", "#categoryForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#save_cat");
        // Fields to validate
        let fields = [
            {
                id: "#category_name",
                condition: (val) => val === "",
                message: "Category name is required",
            },
            {
                id: "#category_status",
                condition: (val) => val === "",
                message: "Please select status",
            },
            {
                id: "#category_image",
                condition: (val) => val === "",
                message: "Please upload an image",
            },
        ];

        if ($("#exiting_image").val()) {
            fields = fields.filter((field) => field.id !== "#category_image");
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
            .text("Saving...");

        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/category-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#categoryModal").hide();
                        let modal = document.querySelector("#categoryModal");
                        let alpine = modal.__x.$data;
                        alpine.form = { name: "", status: "1" , gender: "", parent_id: "" };
                        alpine.previewUrl = null;
                        document.getElementById("categoryForm").reset();

                        $.get("/admin/category-list", function (html) {
                            let $tbody = $(html)
                                .find("#categoryTableBody")
                                .html();
                            $("#categoryTableBody").html($tbody);
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
    $(document).on("click", ".btnDeleteCategory", function () {
        let id = $(this).data("id");
        let modalScope = document.querySelector("#deleteCategoryModal").__x
            .$data;
        modalScope.deleteId = id;
        modalScope.open = true;
    });

    window.deleteCategory = function (id) {
        showLoader();
        sendRequest(
            "/admin/category-delete",
            { id: id },
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Category deleted successfully!",
                        "success",
                        2000,
                    );
                    reloadCategoryList();
                } else {
                    showToast(res.message, "error", 2000);
                }
                document.querySelector("#deleteCategoryModal").__x.$data.open =
                    false;
            },
            function (err) {
                hideLoader();
                showToast(err.message || "Delete failed", "error", 2000);
                document.querySelector("#deleteCategoryModal").__x.$data.open =
                    false;
            },
        );
    };

    // ===== Helpers =====
    function reloadCategoryList() {
        $.get("/admin/category-list", function (html) {
            let $tbody = $(html).find("#categoryTableBody").html();
            $("#categoryTableBody").html($tbody);
        });
    }
});
