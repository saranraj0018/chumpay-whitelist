$(document).ready(function () {
    let groupIndex = 0;
    let bulkIndex = 1;
    let step = 1;
    let galleryFiles = new DataTransfer();

    /* ============================================================
       GALLERY SECTION TOGGLE (hide for variant products)
    ============================================================ */
    function toggleGallerySection(type) {
        if (type === "variant") {
            $("#productGallerySection").addClass("hidden");
            // also clear any staged gallery files/previews so they don't get submitted
            galleryFiles = new DataTransfer();
            $("#galleryImage")[0].files = galleryFiles.files;
            $(".gallery-preview").html("");
        } else {
            $("#productGallerySection").removeClass("hidden");
        }
    }

    /* ============================================================
       MODAL OPEN / CLOSE
    ============================================================ */
    $("#createProductBtn").click(function () {
        $("#productModal").removeClass("hidden");
        $("#product_label").text("Add Product");
        $("#save_product").text("Save");
        resetProductForm();
    });

    $("#closeProductModal").click(function () {
        $("#productModal").addClass("hidden");
    });

    /* ============================================================
       STEP NAVIGATION
    ============================================================ */
    $("#nextBtn").click(function () {
        // Variant products have no product-level gallery (each variant has
        // its own images), so skip step 3 entirely and go straight to Save/Update.
        if (step === 2 && $("#product_type").val() === "variant") {
            $("#nextBtn").hide();
            $("#save_product").removeClass("hidden");
            $("#prevBtn").removeClass("hidden");
            return;
        }

        $(".step").addClass("hidden");
        step++;
        $(".step-" + step).removeClass("hidden");
        $("#prevBtn").removeClass("hidden");
        if (step == 3) {
            $("#nextBtn").hide();
            $("#save_product").removeClass("hidden");
            // re-apply gallery visibility every time step 3 is reached
            toggleGallerySection($("#product_type").val());
        }
    });

    $("#prevBtn").click(function () {
        // If we're sitting on step 2 with the Save/Update button showing
        // (variant shortcut from step 3), just revert to the normal step 2
        // view instead of decrementing past it.
        if (step === 2 && !$("#save_product").hasClass("hidden")) {
            $("#save_product").addClass("hidden");
            $("#nextBtn").show();
            return;
        }

        $(".step").addClass("hidden");
        step--;
        $(".step-" + step).removeClass("hidden");
        $("#nextBtn").show();
        $("#save_product").addClass("hidden");
        if (step == 1) {
            $("#prevBtn").addClass("hidden");
        }
    });

    /* ============================================================
       PRODUCT TYPE TOGGLE
    ============================================================ */
    $(document).on("change", "#product_type", function () {
        let type = $(this).val();
        $("#singleFields,#variantFields,#bulkFields").addClass("hidden");
        if (type == "single") $("#singleFields").removeClass("hidden");
        if (type == "variant") $("#variantFields").removeClass("hidden");
        if (type == "bulk") $("#bulkFields").removeClass("hidden");

        if (type === "variant") {
            $("#variantWrapper").html("");
            groupIndex = 0;
        }

        toggleGallerySection(type);
    });

    /* ============================================================
       MAIN IMAGE
    ============================================================ */
    $(document).on("click", ".choose-image-btn", function () {
        $(this).siblings("input[type=file]").click();
    });

    $(document).on("change", "input[type=file]", function () {
        let preview = $(this).closest("div").find(".image-preview");
        if (!preview.length || !this.files[0]) return;
        let reader = new FileReader();
        reader.onload = function (e) {
            preview.removeClass("hidden");
            preview.find("img").attr("src", e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });

    $(document).on("click", ".remove-image-btn", function () {
        let wrapper = $(this).closest(".image-preview");
        wrapper.addClass("hidden");
        wrapper.find("img").attr("src", "");
        wrapper.closest("div").find("input[type=file]").val("");
        $("#existing_main").val("");
    });

    /* ============================================================
       VARIANT GROUP BUILDERS
       Structure:
       variants[g][primary_value]
       variants[g][image]
       variants[g][existing_image]
       variants[g][gallery_images][]
       variants[g][existing_gallery][]
       variants[g][prices][p][secondary_value]
       variants[g][prices][p][regular_price]
       variants[g][prices][p][sale_price]
       variants[g][prices][p][stock]
       variants[g][prices][p][variant_id]   (hidden, for edit mode)
    ============================================================ */

    function buildPriceRow(gIdx, pIdx, secondaryOptions, data = null) {
        return $(`
        <div class="price-row grid grid-cols-5 gap-3 mb-2 items-center" data-price-index="${pIdx}">
            <input type="hidden" name="variants[${gIdx}][prices][${pIdx}][variant_id]" class="priceVariantId" value="${data?.id ?? ""}">
            <div>
                <select name="variants[${gIdx}][prices][${pIdx}][secondary_value]"
                    class="border rounded-xl px-3 py-2 w-full secondaryValue">
                    ${secondaryOptions}
                </select>
            </div>
            <div>
                <input type="number" step="0.01" name="variants[${gIdx}][prices][${pIdx}][regular_price]"
                    placeholder="Regular Price" value="${data?.regular_price ?? ""}"
                    class="border rounded-xl px-3 py-2 w-full regular_price">
            </div>
            <div>
                <input type="number" step="0.01" name="variants[${gIdx}][prices][${pIdx}][sale_price]"
                    placeholder="Sale Price" value="${data?.sale_price ?? ""}"
                    class="border rounded-xl px-3 py-2 w-full sale_price">
            </div>
            <div>
                <input type="number" name="variants[${gIdx}][prices][${pIdx}][stock]"
                    placeholder="Stock" value="${data?.stock ?? ""}"
                    class="border rounded-xl px-3 py-2 w-full stock">
            </div>
            <div>
                <button type="button" class="removePrice bg-red-500 text-white px-3 py-2 rounded-xl text-sm">Remove</button>
            </div>
        </div>
        `);
    }

    function buildVariantGroup(
        gIdx,
        primaryOptions,
        secondaryOptions,
        primaryId = "",
    ) {
        const $group = $(`
        <div class="variant-group border border-gray-200 rounded-xl p-4 mb-4 shadow-sm" data-group-index="${gIdx}">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Primary Value</label>
                    <select name="variants[${gIdx}][primary_value]"
                        class="border rounded-xl px-3 py-2 w-full primaryValue">
                        ${primaryOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cover Image</label>
                    <input type="hidden" name="variants[${gIdx}][existing_image]" class="existingVariantImage" value="">
                    <input type="file" name="variants[${gIdx}][image]" class="variantImage border rounded-xl px-3 py-2 w-full">
                    <img class="imagePreview mt-2 w-14 h-14 object-cover rounded hidden">
                    <div class="existingImageWrap mt-2"></div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Gallery Images</label>
                    <input type="file" name="variants[${gIdx}][gallery_images][]" multiple
                        class="variantGalleryInput border rounded-xl px-3 py-2 w-full">
                    <div class="variantGalleryPreview flex flex-wrap gap-2 mt-2"></div>
                    <div class="variant-existing-gallery flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>

            <label class="block text-xs font-semibold text-gray-500 mb-2">Prices</label>
            <div class="grid grid-cols-5 gap-3 font-semibold text-xs text-gray-500 mb-1 px-1">
                <div>Secondary Value</div>
                <div>Regular Price</div>
                <div>Sale Price</div>
                <div>Stock</div>
                <div></div>
            </div>
            <div class="pricesWrapper"></div>

            <div class="flex justify-between mt-3">
                <button type="button" class="addPrice bg-blue-600 text-white px-4 py-2 rounded-xl text-sm">
                    Add Price
                </button>
                <button type="button" class="removeVariantGroup bg-red-500 text-white px-4 py-2 rounded-xl text-sm">
                    Remove Variant
                </button>
            </div>
        </div>
        `);

        if (primaryId) {
            $group.find(".primaryValue").val(primaryId);
        }

        const $prices = $group.find(".pricesWrapper");
        $prices.append(buildPriceRow(gIdx, 0, secondaryOptions));
        $group.data("priceIndex", 1);

        return $group;
    }

    /* ---------- ADD VARIANT GROUP ---------- */
    $("#addVariant").click(function () {
        let attribute_id = $("#primary_variant").val();
        if (!attribute_id) {
            showToast("Please select primary attribute first!", "error", 2000);
            return;
        }
        loadVariantValues(
            attribute_id,
            null,
            function (primaryOptions, secondaryOptions) {
                const $group = buildVariantGroup(
                    groupIndex,
                    primaryOptions,
                    secondaryOptions,
                );
                $("#variantWrapper").append($group);
                groupIndex++;
            },
        );
    });

    /* ---------- ADD PRICE ROW ---------- */
    $(document).on("click", ".addPrice", function () {
        const $group = $(this).closest(".variant-group");
        const gIdx = $group.data("group-index");
        const pIdx = $group.data("priceIndex");
        const secondaryOptions =
            $group.find(".secondaryValue").first().html() ||
            '<option value="">Select</option>';
        $group
            .find(".pricesWrapper")
            .append(buildPriceRow(gIdx, pIdx, secondaryOptions));
        $group.data("priceIndex", pIdx + 1);
    });

    /* ---------- REMOVE PRICE ROW ---------- */
    $(document).on("click", ".removePrice", function () {
        const $group = $(this).closest(".variant-group");
        if ($group.find(".price-row").length <= 1) {
            showToast("At least one price row is required", "error", 2000);
            return;
        }
        $(this).closest(".price-row").remove();
    });

    /* ---------- REMOVE VARIANT GROUP ---------- */
    $(document).on("click", ".removeVariantGroup", function () {
        $(this).closest(".variant-group").remove();
    });

    /* ---------- PRIMARY ATTRIBUTE CHANGE: refresh options in all existing groups ---------- */
    $(document).on("change", ".primaryVariant", function () {
        let id = $(this).val();
        if (!id) return;
        $.get("/admin/get-variant-values/" + id, function (res) {
            let html = '<option value="">Select</option>';
            res.forEach(
                (v) => (html += `<option value="${v.id}">${v.value}</option>`),
            );
            $(".primaryValue").html(html);
        });
        $.get("/admin/get-secondary-values/" + id, function (res) {
            let html = '<option value="">Select</option>';
            res.forEach(
                (v) => (html += `<option value="${v.id}">${v.value}</option>`),
            );
            $(".secondaryValue").html(html);
        });
    });

    /* ---------- COVER IMAGE PREVIEW ---------- */
    $(document).on("change", ".variantImage", function () {
        let preview = $(this).siblings(".imagePreview");
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                preview.attr("src", e.target.result).removeClass("hidden");
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    /* ---------- GALLERY PREVIEW (per group, persistent via DataTransfer) ---------- */
    $(document).on("change", ".variantGalleryInput", function () {
        let group = $(this).closest(".variant-group");
        let preview = group.find(".variantGalleryPreview");

        let dt = group.data("galleryFiles") || new DataTransfer();
        for (let i = 0; i < this.files.length; i++) {
            dt.items.add(this.files[i]);
        }
        group.data("galleryFiles", dt);
        this.files = dt.files;

        preview.html("");
        for (let i = 0; i < dt.files.length; i++) {
            let file = dt.files[i];
            let reader = new FileReader();
            reader.onload = function (e) {
                preview.append(`
                    <div class="relative variant-gallery-item" data-index="${i}">
                        <img src="${e.target.result}" class="w-14 h-14 object-cover rounded border">
                        <button type="button"
                            class="removeVariantGalleryImg absolute -top-2 -right-2 bg-red-500 text-white w-5 h-5 rounded-full text-xs">×</button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on("click", ".removeVariantGalleryImg", function () {
        let group = $(this).closest(".variant-group");
        let box = $(this).closest(".variant-gallery-item");
        let index = box.data("index");
        let dt = group.data("galleryFiles");
        if (!dt) return;

        let newDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) newDt.items.add(dt.files[i]);
        }
        group.data("galleryFiles", newDt);
        group.find(".variantGalleryInput")[0].files = newDt.files;
        box.remove();
    });

    $(document).on("click", ".removeExistingVariantGalleryImg", function () {
        $(this).closest(".variant-existing-gallery-item").remove();
    });

    /* ---------- loadVariantValues ---------- */
    function loadVariantValues(attribute_id, row = null, callback = null) {
        $.get(
            "/admin/get-variant-values/" + attribute_id,
            function (primaryRes) {
                let primaryOptions = '<option value="">Select</option>';
                primaryRes.forEach(
                    (v) =>
                        (primaryOptions += `<option value="${v.id}">${v.value}</option>`),
                );

                $.get(
                    "/admin/get-secondary-values/" + attribute_id,
                    function (secRes) {
                        let secondaryOptions =
                            '<option value="">Select</option>';
                        secRes.forEach(
                            (v) =>
                                (secondaryOptions += `<option value="${v.id}">${v.value}</option>`),
                        );

                        if (row) {
                            row.find(".primaryValue").html(primaryOptions);
                            row.find(".secondaryValue").html(secondaryOptions);
                        }
                        if (callback)
                            callback(primaryOptions, secondaryOptions);
                    },
                );
            },
        );
    }

    /* ============================================================
       BULK
    ============================================================ */
    $("#addBulk").click(function () {
        let html = `
<div class="bulk-row grid grid-cols-5 gap-3 mb-3">
<div>
<input type="number" name="bulk[${bulkIndex}][minimum]" placeholder="Minimum" class="minimum border rounded-xl px-3 py-2">
</div>
<div>
<input type="number" name="bulk[${bulkIndex}][maximum]" placeholder="Maximum" class="maximum border rounded-xl px-3 py-2">
</div>
<div>
<input type="number" name="bulk[${bulkIndex}][regular_price]" placeholder="Regular Price" class="bulk_regular_price border rounded-xl px-3 py-2">
</div>
<div>
<input type="number" name="bulk[${bulkIndex}][sale_price]" placeholder="Sale Price" class="bulk_sale_price border rounded-xl px-3 py-2">
</div>
<div>
<button type="button" class="removeBulk bg-red-500 text-white px-3 py-2 rounded">
Remove
</button>
</div>
</div>
`;
        $("#bulkWrapper").append(html);
        bulkIndex++;
    });

    $(document).on("click", ".removeBulk", function () {
        $(this).closest(".bulk-row").remove();
    });

    /* ============================================================
       PRODUCT-LEVEL GALLERY (STEP 3)
    ============================================================ */
    $(document).on("change", ".gallery-images-input", function () {
        let preview = $(this).siblings(".gallery-preview");
        for (let i = 0; i < this.files.length; i++) {
            galleryFiles.items.add(this.files[i]);
            let fileIndex = galleryFiles.items.length - 1;
            let reader = new FileReader();
            reader.onload = function (e) {
                preview.append(`
                <div class="relative image-box" data-index="${fileIndex}">
                    <img src="${e.target.result}"
                         class="w-24 h-24 object-cover rounded border">
                    <button type="button"
                        class="remove-image absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full text-xs">
                        ×
                    </button>
                </div>
            `);
            };
            reader.readAsDataURL(this.files[i]);
        }
        this.files = galleryFiles.files;
    });

    $(document).on("click", ".remove-image", function () {
        let box = $(this).closest(".image-box");
        let index = box.data("index");
        let dt = new DataTransfer();
        for (let i = 0; i < galleryFiles.files.length; i++) {
            if (i !== index) {
                dt.items.add(galleryFiles.files[i]);
            }
        }
        galleryFiles = dt;
        $(".gallery-images-input")[0].files = galleryFiles.files;
        box.remove();
    });

    /* ============================================================
       SUB CATEGORY
    ============================================================ */
    function loadSubCategories(category_id, selected_id) {
        if (!category_id) {
            $("#sub_category_id").html(
                '<option value="">Select Sub Category</option>',
            );
            return;
        }
        $.ajax({
            url: "/admin/product-list",
            type: "GET",
            dataType: "json",
            data: {
                category_id: category_id,
                get_sub_category: true,
            },
            success: function (response) {
                let html = '<option value="">Select Sub Category</option>';
                if (response.success && response.sub_category.length > 0) {
                    response.sub_category.forEach(function (subcategory) {
                        html += `<option value="${subcategory.id}">${subcategory.name}</option>`;
                    });
                }
                $("#sub_category_id").html(html);
                if (selected_id) {
                    $("#sub_category_id").val(selected_id);
                }
            },
            error: function () {
                showToast("Unable to fetch sub category!", "error", 2000);
            },
        });
    }

    $(document).on("change", "#category_id", function () {
        loadSubCategories($(this).val());
    });

    /* ============================================================
       SUBMIT
    ============================================================ */
    $(document).on("submit", "#productForm", function (e) {
        e.preventDefault();
        let isValid = true;
        $(".error-message, .image-error").remove();

        const fields = [
            {
                id: "#product_name",
                condition: (val) => val === "",
                message: "Product Name is required",
            },
            {
                id: "#category_id",
                condition: (val) => val === "",
                message: "Please select category",
            },
            {
                id: "#product_code",
                condition: (val) => val === "",
                message: "Product Code is required",
            },
            {
                id: "#product_type",
                condition: (val) => val === "",
                message: "Product Type is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if ($("#product_type").val() == "")
            showToast("Please select product type", "error", 2000);
        if ($("#product_name").val() == "")
            showToast("Please enter product name", "error", 2000);
        if ($("#category_id").val() == "")
            showToast("Please select category", "error", 2000);
        if ($("#product_code").val() == "")
            showToast("Please enter your product code", "error", 2000);

        /* ---- SINGLE ---- */
        if ($("#product_type").val() === "single") {
            const single_stock = $(this).find(".single_stock");
            const single_sale_price = $(this).find(".single_sale_price");
            const single_regular_price = $(this).find(".single_regular_price");
            if ($.trim(single_stock.val()) === "") {
                showToast("Stock is required", "error", 2000);
                showError(single_stock, "Stock is required");
                isValid = false;
            } else clearError(single_stock);
            if ($.trim(single_sale_price.val()) === "") {
                showToast("Sale Price is required", "error", 2000);
                showError(single_sale_price, "Sale Price is required");
                isValid = false;
            } else clearError(single_sale_price);
            if ($.trim(single_regular_price.val()) === "") {
                showToast("Regular Price is required", "error", 2000);
                showError(single_regular_price, "Regular Price is required");
                isValid = false;
            } else clearError(single_regular_price);
        }

        /* ---- VARIANT (grouped) ---- */
        if ($("#product_type").val() === "variant") {
            const primaryVariant = $(this).find(".primaryVariant");
            if ($("#primary_variant").val() == "") {
                showToast("Primary Variant is required", "error", 2000);
                showError(primaryVariant, "Primary Variant is required");
                isValid = false;
            }

            if ($("#variantWrapper .variant-group").length === 0) {
                showToast("Please add at least one variant", "error", 2000);
                isValid = false;
            }

            $("#variantWrapper .variant-group").each(function () {
                const $group = $(this);
                const primaryValue = $group.find(".primaryValue");
                const variantImage = $group.find(".variantImage");
                const existingImage = $group.find(".existingVariantImage");

                if ($.trim(primaryValue.val()) === "") {
                    showToast("Primary Value is required", "error", 2000);
                    showError(primaryValue, "Primary Value is required");
                    isValid = false;
                } else clearError(primaryValue);

                if (
                    $.trim(variantImage.val()) === "" &&
                    $.trim(existingImage.val()) === ""
                ) {
                    showToast("Variant Image is required", "error", 2000);
                    showError(variantImage, "Variant Image is required");
                    isValid = false;
                } else {
                    clearError(variantImage);
                }

                if ($group.find(".price-row").length === 0) {
                    showToast(
                        "Please add at least one price row per variant",
                        "error",
                        2000,
                    );
                    isValid = false;
                }

                $group.find(".price-row").each(function () {
                    const regularPrice = $(this).find(".regular_price");
                    const salePrice = $(this).find(".sale_price");
                    const stock = $(this).find(".stock");

                    if ($.trim(regularPrice.val()) === "") {
                        showToast("Regular Price is required", "error", 2000);
                        showError(regularPrice, "Regular Price is required");
                        isValid = false;
                    } else clearError(regularPrice);

                    if ($.trim(salePrice.val()) === "") {
                        showToast("Sale Price is required", "error", 2000);
                        showError(salePrice, "Sale Price is required");
                        isValid = false;
                    } else clearError(salePrice);

                    if ($.trim(stock.val()) === "") {
                        showToast("Stock is required", "error", 2000);
                        showError(stock, "Stock is required");
                        isValid = false;
                    } else clearError(stock);
                });
                // gallery_images is intentionally NOT validated as required — it's nullable
            });
        }

        /* ---- BULK ---- */
        if ($("#product_type").val() === "bulk") {
            const per_piece_price = $("#per_piece_price").val();
            if ($.trim(per_piece_price) === "") {
                showToast("Per Piece Price is required", "error", 2000);
                showError($("#per_piece_price"), "Per Piece Price is required");
                isValid = false;
            } else clearError($("#per_piece_price"));

            $("#bulkWrapper .bulk-row").each(function () {
                const minimum = $(this).find(".minimum");
                const maximum = $(this).find(".maximum");
                const bulk_regular_price = $(this).find(".bulk_regular_price");
                const bulk_sale_price = $(this).find(".bulk_sale_price");
                if ($.trim(minimum.val()) === "") {
                    showToast("Minimum Quantity is required", "error", 2000);
                    showError(minimum, "Minimum Quantity is required");
                    isValid = false;
                } else clearError(minimum);
                if ($.trim(maximum.val()) === "") {
                    showToast("Maximum Quantity is required", "error", 2000);
                    showError(maximum, "Maximum Quantity is required");
                    isValid = false;
                } else clearError(maximum);
                if ($.trim(bulk_regular_price.val()) === "") {
                    showToast("Bulk Regular Price is required", "error", 2000);
                    showError(
                        bulk_regular_price,
                        "Bulk Regular Price is required",
                    );
                    isValid = false;
                } else clearError(bulk_regular_price);
                if ($.trim(bulk_sale_price.val()) === "") {
                    showToast("Bulk Sale Price is required", "error", 2000);
                    showError(bulk_sale_price, "Bulk Sale Price is required");
                    isValid = false;
                } else clearError(bulk_sale_price);
            });

            $("#bulkAttributesWrapper .validation-error").remove();
            let hasAttributeValue = false;
            $('select[name^="bulk_attributes"]').each(function () {
                if ($(this).val() && $(this).val().length > 0) {
                    hasAttributeValue = true;
                }
            });
            if (!hasAttributeValue) {
                showToast(
                    "Please select at least one attribute value",
                    "error",
                    2000,
                );
                $("#bulkAttributesWrapper").append(
                    `<div class="validation-error text-red-500 text-sm mt-2 col-span-3">Please select at least one attribute value</div>`,
                );
                isValid = false;
            }
        }

        function validateSingleImage(fileSelector, existingSelector, message) {
            const input = $(fileSelector)[0];
            const hasNew = input && input.files.length > 0;
            const hasExisting = $(existingSelector).val() !== "";
            if (!hasNew && !hasExisting) {
                showToast("Main Image is required!", "error", 2000);
                $(fileSelector)
                    .closest(".image-wrapper")
                    .append(
                        `<div class="image-error text-red-500 text-sm mt-2">${message}</div>`,
                    );
                isValid = false;
            }
        }

        validateSingleImage(
            "#mainImage",
            "#existing_main",
            "Main image is required",
        );

        if ($("#product_type").val() !== "variant") {
            const newFiles = $("#galleryImage")[0]?.files.length || 0;
            let existingCount = 0;
            $(".existing_gallery").each(function () {
                if ($.trim($(this).val()) !== "") existingCount++;
            });
            if (newFiles === 0 && existingCount === 0) {
                showToast(
                    "At least one gallery image is required",
                    "error",
                    2000,
                );
                $("#galleryImage")
                    .closest(".image-wrapper")
                    .append(
                        `<div class="image-error text-red-500 text-sm mt-2">At least one gallery image is required</div>`,
                    );
                isValid = false;
            }
        }

        if (!isValid) return;

        let formData = new FormData(this);

        // Strip any leftover product-level gallery data when submitting a variant product
        if ($("#product_type").val() === "variant") {
            formData.delete("gallery_images[]");
            formData.delete("existing_gallery[]");
        }

        showLoader();
        sendRequest(
            "/admin/save-product",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast("Product saved successfully!", "success", 2000);
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(res.message, "error", 2000);
                }
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
            },
        );
    });

    function showError(el, message) {
        el.addClass("border-red-500 ring-1 ring-red-500");
        if (el.next(".error-message").length === 0) {
            el.after(
                `<div class="error-message text-red-500 text-sm mt-1">${message}</div>`,
            );
        }
    }

    function clearError(el) {
        el.removeClass("border-red-500 ring-1 ring-red-500");
        el.next(".error-message").remove();
    }

    /* ============================================================
       EDIT PRODUCT
    ============================================================ */
    $(document).on("click", ".editProductBtn", function () {
        resetProductForm();
        const product = $(this).data("product");
        $("#productModal").removeClass("hidden");
        $("#productForm")[0].reset();
        $("#variantWrapper").html("");
        $("#bulkWrapper").html("");
        $(".gallery-preview").html("");
        $("#variantFields").addClass("hidden");
        $("#singleFields").addClass("hidden");
        $("#bulkFields").addClass("hidden");
        $("input[name='product_id']").val(product.id);
        $("#product_name").val(product.name);
        $("#product_code").val(product.product_code);
        $("#category_id").val(product.category_id);
        loadSubCategories(product.category_id, product.sub_category_id);
        $("#product_type").val(product.product_type);
        $("#per_piece_price").val(product.per_piece_price);
        $("textarea[name='description']").val(product.description);
        $("#product_label").text("Edit Product");
        $("#save_product").text("Update");

        // apply gallery visibility immediately based on this product's type
        toggleGallerySection(product.product_type);

        /* MAIN IMAGE */
        if (product.main_image) {
            $("#existing_main").val(product.main_image);
            $(".image-preview")
                .removeClass("hidden")
                .find("img")
                .attr("src", "/storage/" + product.main_image);
        }

        /* SINGLE */
        if (product.product_type === "single") {
            $("#singleFields").removeClass("hidden");
            $(".single_regular_price").val(product.regular_price);
            $(".single_sale_price").val(product.sale_price);
            $(".single_stock").val(product.stock);
        }

        /* VARIANT — group flat product_variant rows by primary_value */
        if (product.product_type === "variant") {
            $("#variantFields").removeClass("hidden");
            if (product.product_variant.length) {
                let primaryAttribute =
                    product.product_variant[0].pri_attribute_id;
                $("#primary_variant").val(primaryAttribute);

                loadVariantValues(
                    primaryAttribute,
                    null,
                    function (primaryOptions, secondaryOptions) {
                        $("#variantWrapper").html("");
                        groupIndex = 0;

                        // Group flat variants by primary_value_id
                        const groups = {};
                        const order = [];

                        product.product_variant.forEach((v) => {
                            let primaryId = "";
                            let secondaryId = "";
                            if (v.variant_values) {
                                v.variant_values.forEach((val) => {
                                    if (primaryId === "")
                                        primaryId = val.attribute_value_id;
                                    else secondaryId = val.attribute_value_id;
                                });
                            }
                            if (!groups[primaryId]) {
                                groups[primaryId] = {
                                    primaryId,
                                    cover_image: v.cover_image,
                                    gallery_images: v.gallery_images || [],
                                    items: [],
                                };
                                order.push(primaryId);
                            }
                            groups[primaryId].items.push({
                                id: v.id,
                                secondaryId,
                                regular_price: v.regular_price,
                                sale_price: v.sale_price,
                                stock: v.stock,
                            });
                            // merge gallery images from any row that has them
                            if (v.gallery_images && v.gallery_images.length) {
                                groups[primaryId].gallery_images =
                                    v.gallery_images;
                            }
                            if (v.cover_image) {
                                groups[primaryId].cover_image = v.cover_image;
                            }
                        });

                        order.forEach((primaryId) => {
                            const g = groups[primaryId];
                            const $group = buildVariantGroup(
                                groupIndex,
                                primaryOptions,
                                secondaryOptions,
                                primaryId,
                            );

                            // cover image
                            $group
                                .find(".existingVariantImage")
                                .val(g.cover_image || "");
                            if (g.cover_image) {
                                $group
                                    .find(".existingImageWrap")
                                    .html(
                                        `<img src="/storage/${g.cover_image}" class="w-14 h-14 rounded object-cover">`,
                                    );
                            }

                            // existing gallery
                            if (g.gallery_images.length) {
                                let html = "";
                                g.gallery_images.forEach((img) => {
                                    html += `
                                <div class="relative variant-existing-gallery-item inline-block" data-id="${img.id}">
                                    <input type="hidden" name="variants[${groupIndex}][existing_gallery][]" value="${img.id}">
                                    <img src="/storage/${img.image_path}" class="w-12 h-12 object-cover rounded border">
                                    <button type="button"
                                        class="removeExistingVariantGalleryImg absolute -top-2 -right-2 bg-red-500 text-white w-5 h-5 rounded-full text-xs">×</button>
                                </div>`;
                                });
                                $group
                                    .find(".variant-existing-gallery")
                                    .html(html);
                            }

                            // price rows — clear the seeded empty one first
                            $group.find(".pricesWrapper").html("");
                            g.items.forEach((item, pIdx) => {
                                const $row = buildPriceRow(
                                    groupIndex,
                                    pIdx,
                                    secondaryOptions,
                                    {
                                        id: item.id,
                                        regular_price: item.regular_price,
                                        sale_price: item.sale_price,
                                        stock: item.stock,
                                    },
                                );
                                $group.find(".pricesWrapper").append($row);
                                $row.find(".secondaryValue").val(
                                    item.secondaryId,
                                );
                            });
                            $group.data("priceIndex", g.items.length);

                            $("#variantWrapper").append($group);
                            groupIndex++;
                        });
                    },
                );
            }
        }

        /* BULK */
        if (product.product_type === "bulk") {
            $("#bulkFields").removeClass("hidden");
            let allAttributes = {};
            product.bulk_product.forEach((b, index) => {
                $("#bulkWrapper").append(`
            <div class="bulk-row grid grid-cols-5 gap-3 mb-3">
                <input type="hidden" name="bulk_id[]" value="${b.id}">
                <div><input type="number" name="bulk[${index}][minimum]" value="${b.minimum}" class="border rounded-xl px-3 py-2 minimum"></div>
                <div><input type="number" name="bulk[${index}][maximum]" value="${b.maximum}" class="border rounded-xl px-3 py-2 maximum"></div>
                <div><input type="number" name="bulk[${index}][regular_price]" value="${b.regular_price}" class="border rounded-xl px-3 py-2 bulk_regular_price"></div>
                <div><input type="number" name="bulk[${index}][sale_price]" value="${b.sale_price}" class="border rounded-xl px-3 py-2 bulk_sale_price"></div>
                <div>${index !== 0 ? `<button type="button" class="removeBulk bg-red-500 text-white px-3 py-2 rounded">Remove</button>` : ""}</div>
            </div>
            `);

                if (b.bulk_product_variants) {
                    b.bulk_product_variants.forEach((v) => {
                        if (!allAttributes[v.attribute_id])
                            allAttributes[v.attribute_id] = [];
                        allAttributes[v.attribute_id].push(
                            v.attribute_value_id,
                        );
                    });
                }
            });

            Object.keys(allAttributes).forEach((attrId) => {
                let values = allAttributes[attrId];
                let selector = $(`select[name="bulk_attributes[${attrId}][]"]`);
                selector.val(values).trigger("change");
            });
        }

        /* PRODUCT-LEVEL GALLERY (only relevant for non-variant types) */
        if (
            product.product_type !== "variant" &&
            product.product_gallery_image.length
        ) {
            product.product_gallery_image.forEach((g) => {
                $(".gallery-preview").append(`
            <div class="relative gallery-item">
                <input type="hidden" name="existing_gallery[]" value="${g.image_path}" class="existing_gallery">
                <img src="/storage/${g.image_path}" class="h-20 w-20 rounded object-cover border">
                <button type="button" class="removeGallery absolute -top-2 -right-2 bg-red-600 text-white rounded-full px-2">X</button>
            </div>
            `);
            });
        }
    });

    $(document).on("click", ".removeGallery", function () {
        $(this).closest(".gallery-item").remove();
    });

    /* ============================================================
       RESET FORM
    ============================================================ */
    function resetProductForm() {
        $("#productForm")[0].reset();
        $("input[name='product_id']").val("");
        $("#existing_main").val("");
        $("#sub_category_id").html(
            '<option value="">Select Sub Category</option>',
        );
        $("#variantWrapper").html("");
        $("#bulkWrapper").html("");
        $(".gallery-preview").html("");
        $("#variantFields").addClass("hidden");
        $("#singleFields").addClass("hidden");
        $("#bulkFields").addClass("hidden");
        $("#productGallerySection").removeClass("hidden");
        $(".image-preview").addClass("hidden").find("img").attr("src", "");
        $("#productForm").find("input[type='file']").val("");
        $("#productForm").find(".error-text").text("");
        $("#productForm").find(".is-invalid").removeClass("is-invalid");
        $("#productForm").find(".border-red-500").removeClass("border-red-500");
        $("#productForm").find(".text-red-500").text("");
        $(".validation-error").remove();
        $("#productForm")
            .find("input, select, textarea")
            .removeClass(
                "border-red-500 border-red-600 text-red-500 is-invalid",
            );
        $("#productForm").find(".error-message").text("").addClass("hidden");
        $("#productForm")
            .find(".border-red-500, .border-red-600")
            .removeClass("border-red-500 border-red-600");

        groupIndex = 0;
        bulkIndex = 1;
        galleryFiles = new DataTransfer();

        step = 1;
        $(".step").addClass("hidden");
        $(".step-1").removeClass("hidden");
        $("#prevBtn").addClass("hidden");
        $("#nextBtn").show();
        $("#save_product").addClass("hidden");
    }

    /* ============================================================
       DELETE PRODUCT
    ============================================================ */
    window.confirmDeleteProduct = function (productId, productName) {
        const impactRows = `
        <li><i class="ti ti-git-branch"></i> Product variants &amp; variant values</li>
        <li><i class="ti ti-stack"></i> Bulk pricing tiers &amp; bulk variants</li>
        <li><i class="ti ti-photo"></i> Gallery images</li>
        <li><i class="ti ti-heart"></i> Customer wishlists containing this product</li>
        <li><i class="ti ti-star"></i> Customer reviews &amp; ratings</li>
        <li><i class="ti ti-shopping-cart"></i> Order detail records referencing this product</li>
    `;

        Swal.fire({
            title: `Delete "${productName}"?`,
            html: `
            <p style="text-align:left; font-size:14px; color:#555; margin-bottom:10px;">
                This action <strong>cannot be undone</strong>. The following related data will also be permanently deleted:
            </p>
            <ul style="text-align:left; font-size:13px; color:#e53e3e; line-height:2; padding-left:1rem; list-style:none;">
                ${impactRows}
            </ul>
            <p style="text-align:left; font-size:12px; color:#b7791f; background:#fffff0; border:1px solid #f6e05e; padding:8px 12px; border-radius:6px; margin-top:10px;">
                ⚠️ Order history will lose the product reference, but <strong>orders themselves will not be deleted.</strong>
            </p>
        `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e53e3e",
            cancelButtonColor: "#718096",
            confirmButtonText:
                '<i class="ti ti-trash"></i> Yes, delete product',
            cancelButtonText: "Cancel",
            width: "480px",
        }).then((result) => {
            if (result.isConfirmed) {
                deleteProduct(productId);
            }
        });
    };

    function deleteProduct(productId) {
        $.ajax({
            url: "/admin/products/destroy",
            method: "DELETE",
            data: {
                id: productId,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire(
                        "Deleted!",
                        "Product has been deleted.",
                        "success",
                    ).then(() => location.reload());
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            },
            error: function () {
                Swal.fire(
                    "Error",
                    "Something went wrong. Please try again.",
                    "error",
                );
            },
        });
    }
});

/* ============================================================
   SELECT2 INIT
============================================================ */
$(document).ready(function () {
    $(".select2").select2({
        placeholder: "Select values",
        allowClear: true,
        width: "100%",
    });
});
