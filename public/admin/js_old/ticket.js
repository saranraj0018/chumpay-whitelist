$(function () {
    $(document).on("click", ".editstatusSave", function () {
        let ticketlist = {
            id: $(this).data("id"),
            status: $(this).data("status"),
            image: $(this).data("image"),
        };
        // Show modal
        $("#editTicketStatusModal").css("display", "flex");
        let modal = document.getElementById("editTicketStatusModal");
        let alpine = modal.__x.$data;
        alpine.open = true;
        alpine.form.ticket_id = ticketlist.id || "";
        alpine.form.status = ticketlist.status || "";
        alpine.previewUrl = ticketlist.image || "";
    });

    $(document).on("submit", "#ticketstatusChangeForm", function (e) {
        e.preventDefault();
        let isValid = true;
        let $saveBtn = $("#ticket");
        const fields = [
            {
                id: "#status",
                condition: (val) => val === "",
                message: "Status is required",
            },
        ];

        fields.forEach((field) => {
            if (!validateField(field)) isValid = false;
        });

        if (!isValid) return;
        $saveBtn
            .prop("disabled", true)
            .removeClass("opacity-50 cursor-not-allowed")
            .text("Saving....");
        let formData = new FormData(this);
        showLoader();
        sendRequest(
            "/admin/ticket-save",
            formData,
            "POST",
            function (res) {
                hideLoader();
                if (res.success) {
                    showToast(
                        "Ticket Status saved successfully!",
                        "success",
                        2000,
                    );
                    setTimeout(() => {
                        let modalScope = document.querySelector(
                            "#editTicketStatusModal",
                        ).__x.$data;
                        if (modalScope.hasOwnProperty("open")) {
                            modalScope.open = false;
                        }
                        window.location.reload();
                        document
                            .getElementById("ticketstatusChangeForm")
                            .reset();
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
