$(function () {
    $(document).on("click", "#createAttributeBtn", function () {
        document.getElementById("attributeForm").reset();
        let modal = document.getElementById("attributeModal");
        let alpine = modal.__x.$data;
        alpine.form.attribute = "";
        alpine.form.attribute_value = "";
        $("#attributeModal").css("display", "flex");
        $("#attribute_label").text("Add Attribute Values");
        $("#save_attribute").text("Save");
    });

    $(document).on("click", ".editAttributeBtn", function () {
        let id = $(this).data("id");
        let attribute = $(this).data("id");
        let attribute_value = $(this).data("attribute_value");

        // open modal
        $("#attributeModal").css("display", "flex");
        $("#attribute_label").text("Edit Attribute Value");
        $("#save_attribute").text("Update");

        // update Alpine state for preview
        let modal = document.getElementById("attributeModal");
        let alpine = modal.__x.$data;
        alpine.form.attribute = attribute;
        alpine.form.attribute_value = attribute_value;
        alpine.form.attribute_id = attribute;
    });

    $(document).on("submit", "#attributeForm", function (e) {
        e.preventDefault();
        let $saveBtn = $("#save_attribute");
        let attributeValue = $("#attribute_value").val().trim();
        let fields = [
            {
                id: "#attribute",
                condition: (val) => val === "",
                message: "Attribute is required",
            },
            {
                id: "#attribute_value",
                condition: (val) => val === "",
                message: "Attribute Value is required",
            },
        ];

        let isValid = true;

        for (const field of fields) {
            const result = validateField(field);
            if (!result) isValid = false;
        }
        $("#attribute_value").next(".error-text").remove();
 let values = attributeValue
     .split(",")
     .map((v) => v.trim())
     .filter((v) => v !== "");

 if (values.length === 0) {
     $("#attribute_value").after(
         '<span class="error-text text-red-500 text-sm">Enter valid values</span>',
     );
     isValid = false;
 }else{
    $("#attribute_value").remove("error-text");
 }

 // Optional: prevent duplicates
 let uniqueValues = [...new Set(values)];
 if (uniqueValues.length !== values.length) {
     $("#attribute_value").after(
         '<span class="error-text text-red-500 text-sm">Duplicate values not allowed</span>',
     );
     isValid = false;
 }
        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving...");

        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/attribute-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(res.message, "success", 2000);
                    setTimeout(() => {
                        $("#attributeModal").hide();
                        let modal = document.querySelector("#attributeModal");
                        let alpine = modal.__x.$data;
                        alpine.form = {
                            attribute: "",
                            attribute_value: "",
                        };
                        document.getElementById("attributeForm").reset();

                        $.get("/admin/attribute-list", function (html) {
                            let $tbody = $(html)
                                .find("#attributeTableBody")
                                .html();
                            $("#attributeTableBody").html($tbody);
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
});
