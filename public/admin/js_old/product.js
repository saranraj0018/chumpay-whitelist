$(document).ready(function () {
    let primaryOptions = "";
    let secondaryOptions = "";
    let variantIndex = 1;

    $("#createProductBtn").click(function () {
        $("#productModal").removeClass("hidden");

        $("#product_label").text("Add Product");
        $("#save_product").text("Save");
        resetProductForm();
    });

    // Modal close
    $("#closeProductModal").click(function () {
        $("#productModal").addClass("hidden");
    });

    let step = 1;

    /* STEP NAVIGATION */

    $("#nextBtn").click(function () {
        $(".step").addClass("hidden");

        step++;

        $(".step-" + step).removeClass("hidden");

        $("#prevBtn").removeClass("hidden");

        if (step == 3) {
            $("#nextBtn").hide();
            $("#save_product").removeClass("hidden");
        }
    });

    // $(document).on("change", ".primaryVariant", function () {
    //     let id = $(this).val();
    //     let valueSelect = $(this).closest(".variant-row").find(".primaryValue");
    //     $.get("/get-variant-values/" + id, function (res) {
    //         let html = '<option value="">Select</option>';
    //         res.forEach((v) => {
    //             html += `<option value="${v.id}">${v.value}</option>`;
    //         });
    //         valueSelect.html(html);
    //     });
    // });

    $(document).on("change", ".secondaryVariant", function () {
        let id = $(this).val();
        let valueSelect = $(this)
            .closest(".variant-row")
            .find(".secondaryValue");
        $.get("/get-variant-values/" + id, function (res) {
            let html = '<option value="">Select</option>';
            res.forEach((v) => {
                html += `<option value="${v.id}">${v.value}</option>`;
            });
            valueSelect.html(html);
        });
    });

    $("#prevBtn").click(function () {
        $(".step").addClass("hidden");
        step--;
        $(".step-" + step).removeClass("hidden");
        $("#nextBtn").show();
        $("#save_product").addClass("hidden");
        if (step == 1) {
            $("#prevBtn").addClass("hidden");
        }
    });

    /* PRODUCT TYPE */

    $("#product_type").change(function () {
        let type = $(this).val();
        $("#singleFields,#variantFields,#bulkFields").hide();
        if (type == "single") $("#singleFields").show();
        if (type == "variant") $("#variantFields").show();
        if (type == "bulk") $("#bulkFields").show();
    });

    /* IMAGE PICK */

    $(document).on("click", ".choose-image-btn", function () {
        $(this).siblings("input[type=file]").click();
    });

    /* IMAGE PREVIEW */

    $(document).on("change", "input[type=file]", function () {
        let preview = $(this).closest("div").find(".image-preview");
        let reader = new FileReader();
        reader.onload = function (e) {
            preview.removeClass("hidden");

            preview.find("img").attr("src", e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });

    /* REMOVE IMAGE */

    $(document).on("click", ".remove-image-btn", function () {
        let wrapper = $(this).closest(".image-preview");
        wrapper.addClass("hidden");
        wrapper.find("img").attr("src", "");
        wrapper.closest("div").find("input[type=file]").val("");
    });

    /* ADD VARIANT */

    $("#addVariant").click(function () {
        let attribute_id = $("#primary_variant").val();
        if (!attribute_id) {
            showToast("Please select primary attribute first!", "error", 2000);
            return;
        }
        let row = $(`
<div class="variant-row grid grid-cols-7 gap-3 mb-4">

<div>
<select name="variants[${variantIndex}][primary_value]"
class="border rounded-xl px-3 py-2 w-full primaryValue">
${primaryOptions}
</select>
</div>

<div>
<select name="variants[${variantIndex}][secondary_value]"
class="border rounded-xl px-3 py-2 w-full secondaryValue">
${secondaryOptions}
</select>
</div>

<div>
<input type="number"
name="variants[${variantIndex}][regular_price]"
placeholder="Regular Price"
class="regular_price border rounded-xl px-3 py-2 w-full">
</div>

<div>
<input type="number"
name="variants[${variantIndex}][sale_price]"
placeholder="Sale Price"
class="sale_price border rounded-xl px-3 py-2 w-full">
</div>

<div>
<input type="number"
name="variants[${variantIndex}][stock]"
placeholder="Stock"
class="stock border rounded-xl px-3 py-2 w-full">
</div>

<div>
<input type="file"
name="variants[${variantIndex}][image]"
class="variantImage border rounded-xl px-3 py-2 w-full">

<img class="imagePreview mt-2 w-16 h-16 object-cover rounded hidden">
</div>

<div>
<button type="button"
class="removeVariant bg-red-500 text-white px-3 py-2 rounded">
Remove
</button>
</div>

</div>
`);

        $("#variantWrapper").append(row);
        loadVariantValues(attribute_id, row);
        variantIndex++;
    });

    /* REMOVE VARIANT */

    $(document).on("click", ".removeVariant", function () {
        $(this).closest(".variant-row").remove();
    });

    /* IMAGE PREVIEW */

    $(document).on("change", ".variantImage", function () {
        let input = this;
        let preview = $(this).siblings(".imagePreview");
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                preview.attr("src", e.target.result);
                preview.removeClass("hidden");
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    /* LOAD VARIANT VALUES */

    $(document).on("change", ".primaryVariant", function () {
        let id = $(this).val();
        let primaryValue = $(".primaryValue");
        let secondaryValue = $(".secondaryValue");
        primaryValue.html("<option>Loading...</option>");
        secondaryValue.html("<option>Loading...</option>");
        $.get("/admin/get-variant-values/" + id, function (res) {
            let html = '<option value="">Select</option>';
            res.forEach(function (v) {
                html += `<option value="${v.id}">${v.value}</option>`;
            });
            primaryValue.html(html);
        });

        $.get("/admin/get-secondary-values/" + id, function (res) {
            let html = '<option value="">Select</option>';
            res.forEach(function (v) {
                html += `<option value="${v.id}">${v.value}</option>`;
            });
            secondaryValue.html(html);
        });
    });

    let bulkIndex = 1;

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

    let galleryFiles = new DataTransfer();

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

    $(document).on("change", "#category_id", function () {
        var category_id = $(this).val();
        if (category_id) {
            $.ajax({
                url: "/admin/product-list",
                type: "GET",
                dataType: "json",
                data: {
                    category_id: category_id,
                    get_sub_category: true,
                },
                success: function (response) {
                    $("#sub_category_id").empty();
                    $("#sub_category_id").append(
                        '<option value="">Select Sub Category</option>',
                    );
                    if (response.success && response.sub_category.length > 0) {
                        var subcategories = response.sub_category;
                        $("#sub_category_id").html(
                            '<option value="">Select Sub Category</option>',
                        );
                        subcategories.forEach(function (subcategory) {
                            $("#sub_category_id").append(
                                '<option value="' +
                                    subcategory.id +
                                    '">' +
                                    subcategory.name +
                                    "</option>",
                            );
                        });
                    }
                },
                error: function () {
                    showToast("Unable to fetch sub category!", "error", 2000);
                },
            });
        } else {
            $("#sub_category_id").empty();
            $("#sub_category_id").append(
                '<option value="">Select Sub Category</option>',
            );
        }
    });

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

        if ($("#product_type").val() == "") {
            showToast("Please select product type", "error", 2000);
        }

        if ($("#product_name").val() == "") {
            showToast("Please enter product name", "error", 2000);
        }

        if ($("#category_id").val() == "") {
            showToast("Please select category", "error", 2000);
        }

        if ($("#product_code").val() == "") {
            showToast("Please enter your product code", "error", 2000);
        }

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

        if ($("#product_type").val() === "variant") {
            const primaryVariant = $(this).find(".primaryVariant");
            if ($("#primary_variant").val() == "") {
                showToast("Primary Variant is required", "error", 2000);
                showError(primaryVariant, "Primary Variant is required");
                isValid = false;
            }
            $("#variantWrapper .grid").each(function () {
                const primaryValue = $(this).find(".primaryValue");
                const regularPrice = $(this).find(".regular_price");
                const salePrice = $(this).find(".sale_price");
                const stock = $(this).find(".stock");
                const variantImage = $(this).find(".variantImage");
                const existingImage = $(this).find(".existingVariantImage");
                if ($.trim(primaryValue.val()) === "") {
                    showToast("Primary Value is required", "error", 2000);
                    showError(primaryValue, "Primary Value is required");
                    isValid = false;
                } else clearError(primaryValue);
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
            });
        }

        if ($("#product_type").val() === "bulk") {
            $("#bulkWrapper .grid").each(function () {
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

        /* ---------------- SINGLE IMAGE VALIDATIONS ---------------- */

        validateSingleImage(
            "#mainImage",
            "#existing_main",
            "Main image is required",
        );

        const newFiles = $("#galleryImage")[0]?.files.length || 0;
        let existingCount = 0;

        $(".existing_gallery").each(function () {
            if ($.trim($(this).val()) !== "") {
                existingCount++;
            }
        });

        if (newFiles === 0 && existingCount === 0) {
            showToast("At least one gallery image is required", "error", 2000);
            $("#galleryImage")
                .closest(".image-wrapper")
                .append(
                    `<div class="image-error text-red-500 text-sm mt-2">At least one gallery image is required </div>`,
                );
            isValid = false;
        }

        /* ---------------- HIGHLIGHT IMAGES (MULTIPLE) ---------------- */
        $(".existing_gallery").each(function () {
            if ($.trim($(this).val()) !== "") {
                existingCount++;
            }
        });

        if (!isValid) return;
        let formData = new FormData(this);
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
        $("#sub_category_id").val(product.sub_category_id);
        $("#product_type").val(product.product_type);
        $("textarea[name='description']").val(product.description);
        $("#product_label").text("Edit Product");
        $("#save_product").text("Update");
        /* MAIN IMAGE */

        if (product.main_image) {
            $("#existing_main").val(product.main_image);
            $(".image-preview")
                .removeClass("hidden")
                .find("img")
                .attr("src", "/storage/" + product.main_image);
        }

        /* SINGLE PRODUCT */

        if (product.product_type === "single") {
            $("#singleFields").removeClass("hidden");
            $(".single_regular_price").val(product.regular_price);
            $(".single_sale_price").val(product.sale_price);
            $(".single_stock").val(product.stock);
        }

        /* VARIANT PRODUCT */

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
                        product.product_variant.forEach((v, index) => {
                            let primaryId = "";
                            let secondaryId = "";
                            if (v.variant_values) {
                                v.variant_values.forEach((val) => {
                                    if (primaryId === "") {
                                        primaryId = val.attribute_value_id;
                                    } else {
                                        secondaryId = val.attribute_value_id;
                                    }
                                });
                            }

                            let html = `
                <div class="variant-row grid grid-cols-7 gap-3 mb-4">

                <input type="hidden" name="variant_id[]" value="${v.id}">

                <div>
                <select name="variants[${index}][primary_value]"
                class="border rounded-xl px-3 py-2 w-full primaryValue">
                ${primaryOptions}
                </select>
                </div>

                <div>
                <select name="variants[${index}][secondary_value]"
                class="border rounded-xl px-3 py-2 w-full secondaryValue">
                ${secondaryOptions}
                </select>
                </div>
                <div>
                <input type="number"
                name="variants[${index}][regular_price]"
                value="${v.regular_price}"
                class="border rounded-xl px-3 py-2 w-full regular_price">
                </div>
                <div>
                <input type="number"
                name="variants[${index}][sale_price]"
                value="${v.sale_price}"
                class="border rounded-xl px-3 py-2 w-full sale_price">
                </div>
                <div>
                <input type="number"
                name="variants[${index}][stock]"
                value="${v.stock}"
                class="border rounded-xl px-3 py-2 w-full stock">
                </div>
                <div>
                <input type="hidden"
                name="variants[${index}][existing_image]"
                value="${v.cover_image ?? ""}" class="existingVariantImage">
                <input type="file"
                name="variants[${index}][image]"
                class="variantImage border rounded-xl px-3 py-2 w-full">
                <img class="imagePreview mt-2 w-16 h-16 object-cover rounded hidden">
                ${
                    v.cover_image
                        ? `<img src="/storage/${v.cover_image}"
                        class="w-16 h-16 mt-2 rounded object-cover">`
                        : ""
                }

                </div>

                <div>
                ${
                    index !== 0
                        ? `<button type="button"
                        class="removeVariant bg-red-600 text-white px-2 py-1">
                        Remove
                        </button>`
                        : ""
                }
                </div>

                </div>
                `;

                            $("#variantWrapper").append(html);

                            // set selected values AFTER append
                            $(
                                `select[name="variants[${index}][primary_value]"]`,
                            ).val(primaryId);
                            $(
                                `select[name="variants[${index}][secondary_value]"]`,
                            ).val(secondaryId);
                        });

                        variantIndex = product.product_variant.length;
                    },
                );
            }
        }

        /* BULK PRODUCT */

        if (product.product_type === "bulk") {
            $("#bulkFields").removeClass("hidden");
            let allAttributes = {};
            product.bulk_product.forEach((b, index) => {
                $("#bulkWrapper").append(`
            <div class="bulk-row grid grid-cols-5 gap-3 mb-3">
                <input type="hidden" name="bulk_id[]" value="${b.id}">
                <div>
                <input type="number"
                name="bulk[${index}][minimum]"
                value="${b.minimum}"
                class="border rounded-xl px-3 py-2 minimum">
                </div>
                <div>
                <input type="number"
                name="bulk[${index}][maximum]"
                value="${b.maximum}"
                class="border rounded-xl px-3 py-2 maximum">
                </div>
                <div>
                <input type="number"
                name="bulk[${index}][regular_price]"
                value="${b.regular_price}"
                class="border rounded-xl px-3 py-2 bulk_regular_price">
                </div>
                <div>
                <input type="number"
                name="bulk[${index}][sale_price]"
                value="${b.sale_price}"
                class="border rounded-xl px-3 py-2 bulk_sale_price">
                </div>
                <div>
                ${
                    index !== 0
                        ? `<button type="button"
                        class="removeBulk bg-red-500 text-white px-3 py-2 rounded">
                        Remove
                        </button>`
                        : ""
                }
                </div>
            </div>
            `);

                if (b.bulk_product_variants) {
                    b.bulk_product_variants.forEach((v) => {
                        if (!allAttributes[v.attribute_id]) {
                            allAttributes[v.attribute_id] = [];
                        }

                        allAttributes[v.attribute_id].push(
                            v.attribute_value_id,
                        );
                    });
                }
            });

            /* -------- APPLY SELECTED VALUES -------- */
            Object.keys(allAttributes).forEach((attrId) => {
                let values = allAttributes[attrId];
                let selector = $(`select[name="bulk_attributes[${attrId}][]"]`);
                selector.val(values).trigger("change");
            });
        }

        /* GALLERY IMAGES */

        if (product.product_gallery_image.length) {
            product.product_gallery_image.forEach((g) => {
                $(".gallery-preview").append(`
            <div class="relative gallery-item">
                <input type="hidden"
                name="existing_gallery[]"
                value="${g.image_path}" class="existing_gallery">
                <img src="/storage/${g.image_path}"
                class="h-20 w-20 rounded object-cover border">
                <button type="button"
                class="removeGallery absolute -top-2 -right-2
                bg-red-600 text-white rounded-full px-2">
                X
                </button>
            </div>
            `);
            });
        }
    });

    function loadVariantValues(attribute_id, row = null, callback = null) {
        let attribute = variantValues.find((v) => v.id == attribute_id);
        if (!attribute) return;

        let primaryOptions = '<option value="">Select</option>';
        attribute.get_variant_value.forEach((val) => {
            primaryOptions += `<option value="${val.id}">${val.value}</option>`;
        });

        $.get("/admin/get-secondary-values/" + attribute_id, function (data) {
            let secondaryOptions = '<option value="">Select</option>';
            data.forEach((attr) => {
                secondaryOptions += `<option value="${attr.id}">${attr.value}</option>`;
            });
            if (row) {
                row.find(".primaryValue").html(primaryOptions);
                row.find(".secondaryValue").html(secondaryOptions);
            } else {
                $(".primaryValue").html(primaryOptions);
                $(".secondaryValue").html(secondaryOptions);
            }
            if (callback) callback(primaryOptions, secondaryOptions);
        });
    }

    $(document).on("change", "#product_type", function () {
        let type = $(this).val();
        if (type === "variant") {
            $("#variantFields").removeClass("hidden");
            $("#variantWrapper").html("");
            variantIndex = 0;
            let primaryAttribute = $("#primary_variant").val();
            if (primaryAttribute) {
                loadVariantValues(primaryAttribute);
            }
        } else {
            $("#variantFields").addClass("hidden");
            $("#variantWrapper").html("");
            variantIndex = 0;
        }
    });

    $(document).on("click", ".removeGallery", function () {
        $(this).closest(".gallery-item").remove();
    });

    $(document).on("click", ".removeVariant", function () {
        $(this).closest(".variant-row").remove();
    });

    function resetProductForm() {
        // reset native form fields
        $("#productForm")[0].reset();
        $("input[name='product_id']").val("");
        $("#existing_main").val("");
        $("#variantWrapper").html("");
        $("#bulkWrapper").html("");
        $(".gallery-preview").html("");
        $("#variantFields").addClass("hidden");
        $("#singleFields").addClass("hidden");
        $("#bulkFields").addClass("hidden");
        $(".image-preview").addClass("hidden").find("img").attr("src", "");
        $("#productForm").find("input[type='file']").val("");
        $("#productForm").find(".error-text").text(""); // if using span/div for errors
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
        // reset step if needed
        currentStep = 1;
    }

    function confirmDeleteProduct(productId, productName) {
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
    }

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

$(document).ready(function () {
    $(".select2").select2({
        placeholder: "Select values",
        allowClear: true,
        width: "100%",
    });
});

function confirmDeleteProduct(productId, productName) {
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
        confirmButtonText: '<i class="ti ti-trash"></i> Yes, delete product',
        cancelButtonText: "Cancel",
        width: "480px",
    }).then((result) => {
        if (result.isConfirmed) {
            deleteProduct(productId);
        }
    });
}

function deleteProduct(productId) {
    $.ajax({
        url: "/admin/delete-product",
        method: "POST",
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
