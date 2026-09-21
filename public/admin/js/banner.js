$(function () {
    // ==== CREATE BANNER ====
    $(document).on("click", "#createBannerBtn", function () {
        let modal = document.getElementById("bannerModal");
        let alpine = modal.__x.$data;

        document.getElementById("bannerForm").reset();
        alpine.previewUrl = null;
        alpine.existing_image = "";
        alpine.form = { banner_id: 0 };

        $("#bannerModal").css("display", "flex");
        $("#banner_label").text("Add Banner");
        $("#save_banner").text("Save");
    });

    // ==== EDIT BANNER ====
    $(document).on("click", ".editBannerBtn", function () {
        let modal = document.getElementById("bannerModal");
        let alpine = modal.__x.$data;

        alpine.form.banner_id = $(this).data("id");
        alpine.previewUrl = $(this).data("image") || null;
        alpine.existing_image = $(this).data("image") || "";

        $("#bannerModal").css("display", "flex");
        $("#banner_label").text("Edit Banner");
        $("#save_banner").text("Update");
    });

    // ==== SAVE BANNER ====
    $(document).on("submit", "#bannerForm", function (e) {
        e.preventDefault();
        let alpine = document.getElementById("bannerModal").__x.$data;

        if (!$("#banner_image").val() && !alpine.existing_image) {
            showToast("Please upload a banner image", "error");
            return;
        }

        let formData = new FormData(this);

        sendRequest(
            "/admin/banner-save",
            formData,
            "POST",
            function (res) {
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#bannerModal").hide();
                        alpine.form = { banner_id: 0 };
                        alpine.previewUrl = null;
                        alpine.existing_image = "";
                        document.getElementById("bannerForm").reset();
                        reloadBannerList();
                    }, 500);
                } else {
                    showToast(res.message || "Something went wrong!", "error");
                }
            },
            function (err) {
                if (err.errors) {
                    let msg = "";
                    $.each(err.errors, function (k, v) {
                        msg += v[0] + "<br>";
                    });
                    showToast(msg, "error");
                } else {
                    showToast(err.message || "Unexpected error", "error");
                }
            },
        );
    });

    // ==== DELETE BANNER ====
    $(document).on("click", ".btnDeleteBanner", function () {
        let modalScope = document.querySelector("#deleteBannerModal").__x.$data;
        modalScope.deleteId = $(this).data("id");
        modalScope.open = true;
    });

    window.deleteBanner = function (id) {
        sendRequest(
            "/admin/banner-delete",
            { id: id },
            "POST",
            function (res) {
                if (res.success) {
                    showToast("Banner deleted successfully!", "success");
                    reloadBannerList();
                } else {
                    showToast(res.message || "Delete failed", "error");
                }
                document.querySelector("#deleteBannerModal").__x.$data.open =
                    false;
            },
            function (err) {
                showToast(err.message || "Delete failed", "error");
                document.querySelector("#deleteBannerModal").__x.$data.open =
                    false;
            },
        );
    };

    function reloadBannerList() {
        $.get("/admin/banner-list", function (html) {
            let $tbody = $(html).find("#bannerTableBody").html();
            $("#bannerTableBody").html($tbody);
        });
    }
});
