// alert('ok');
$(document).ready(function () {
    var scheme_id = $("#scheme_id").val();
    var type = $("#type").val();
    var error_personal = 0;
    // if (scheme_id == 5 || scheme_id == 6 || scheme_id == 7 || scheme_id == 17) {
    //   $("#caste_certificate_no_section").hide(); // Hide the section if condition is met
    // } else {
    //   $("#caste_certificate_no_section").show(); // Show the section otherwise
    // }

    // Function to handle the caste category change event
    function toggleCasteCertificateSection() {
        const casteCategory = $("#caste_category").val();
        if (casteCategory === "SC" || casteCategory === "ST") {
            $("#caste_certificate_no_section").show();
        } else {
            $("#caste_certificate_no_section").hide();
        }
    }

    // Initialize the visibility of the caste certificate section on page load
    toggleCasteCertificateSection();

    // Attach event listener to the dropdown
    $("#caste_category").on("change", toggleCasteCertificateSection);

    var entry_type = $("#entry_type").val();
    if (entry_type == "Form through Duare Sarkar camp") {
        $(".duareSarkar").show();
    } else {
        $(".duareSarkar").hide();
    }

    var marital_status = $("#marital_status").val();
    if (marital_status == "Married") {
        $("#spouse_section").show();
    } else {
        $("#spouse_section").hide();
    }

    $("#entry_type").on("change", function () {
        var entry_type = $(this).val(); // Use `this` for better context and readability
        if (entry_type === "Form through Duare Sarkar camp") {
            $(".duareSarkar").show();
        } else {
            $(".duareSarkar").hide();
        }
    });
    $("#spouse_section").hide();
    $("#marital_status").on("change", function () {
        var marital_status = $("#marital_status").val();
        if (marital_status == "Married") {
            $("#spouse_section").show();
        } else {
            $("#spouse_section").hide();
        }
    });

    // console.log(error_personal);
    $("#dob").on("blur", function () {
        var today = new Date();
        // alert(today);
        var birthDate = new Date($("#dob").val());

        var diff_ms = today.getTime() - birthDate.getTime();
        var age_dt = new Date(diff_ms);
        var age = Math.ceil(age_dt.getUTCFullYear() - 1970);
        // alert(age_dt);

        if (isNaN(age)) {
            age = 0;
        }
        // $("#hidden_age").val(age);
        $("#txt_age").val(age);
        // console.log(age);
    });

    $("#btn_personal_details").click(function () {
        //alert(type);
        if ($.trim($("#application_date").val()).length == 0 && type == 1) {
            error_personal = 1;
            $("#error_application_date").text("Application Date is required");
            $("#application_date").addClass("is-invalid");
        } else {
            $("#error_application_date").text("");
            $("#application_date").removeClass("is-invalid");
        }

        if ($.trim($("#entry_type").val()).length == 0) {
            error_personal = 1;
            $("#error_entry_type").text("Please Select Application Type");
            $("#entry_type").addClass("is-invalid");
        } else {
            $("#error_entry_type").text("");
            $("#entry_type").removeClass("is-invalid");
        }
        if ($("#entry_type").val() == "Form through Duare Sarkar camp") {
            // console.log('ds');
            if ($.trim($("#ds_registration_no").val()).length == 0) {
                error_personal = 1;
                $("#error_ds_registration_no").text(
                    "Duare Sarkar Registration no. is required"
                );
                $("#ds_registration_no").addClass("is-invalid");
            } else {
                $("#error_ds_registration_no").text("");
                $("#ds_registration_no").removeClass("is-invalid");
            }
            if ($.trim($("#ds_date").val()).length == 0) {
                error_personal = 1;
                $("#error_ds_date").text("Duare Sarkar Date is required");
                $("#ds_date").addClass("is-invalid");
            } else {
                $("#error_ds_date").text("");
                $("#ds_date").removeClass("is-invalid");
            }
        }

        if ($.trim($("#first_name").val()).length == 0) {
            error_personal = 1;
            $("#error_first_name").text("First Name is required");
            $("#first_name").addClass("is-invalid");
        } else {
            $("#error_first_name").text("");
            $("#first_name").removeClass("is-invalid");
        }

        if ($.trim($("#last_name").val()).length == 0) {
            error_personal = 1;
            $("#error_last_name").text("Last Name is required");
            $("#last_name").addClass("is-invalid");
        } else {
            $("#error_last_name").text("");
            $("#last_name").removeClass("is-invalid");
        }

        if ($.trim($("#gender").val()).length == 0) {
            error_personal = 1;
            $("#error_gender").text("Gender is required");
            $("#gender").addClass("is-invalid");
        } else {
            $("#error_gender").text("");
            $("#gender").removeClass("is-invalid");
        }

        if (scheme_id == 11) {
            if ($("#gender").val() != "Female") {
                error_personal = 1;
                $("#error_gender").text(" Gender should be Female");
                $("#gender").addClass("is-invalid");
            } else {
                $("#error_gender").text("");
                $("#gender").removeClass("is-invalid");
            }
        }

        if ($.trim($("#dob").val()).length == 0) {
            error_personal = 1;
            $("#error_dob").text("Date of Birth is Required");
            $("#dob").addClass("is-invalid");
        } else {
            $("#error_dob").text("");
            $("#dob").removeClass("is-invalid");
        }

        var age_checker = dob_checker(scheme_id, $("#dob").val());
        if (age_checker == false) {
            error_personal = 1;
        } else {
            error_personal = 0;
        }

        if ($.trim($("#father_first_name").val()).length == 0) {
            error_personal = 1;
            $("#error_father_first_name").text("First Name is required");
            $("#father_first_name").addClass("is-invalid");
        } else {
            $("#error_father_first_name").text("");
            $("#father_first_name").removeClass("is-invalid");
        }

        if ($.trim($("#father_last_name").val()).length == 0) {
            error_personal = 1;
            $("#error_father_last_name").text("Last Name is required");
            $("#father_last_name").addClass("is-invalid");
        } else {
            $("#error_father_last_name").text("");
            $("#father_last_name").removeClass("is-invalid");
        }

        if ($.trim($("#mother_first_name").val()).length == 0) {
            error_personal = 1;
            $("#error_mother_first_name").text("First Name is required");
            $("#mother_first_name").addClass("is-invalid");
        } else {
            $("#error_mother_first_name").text("");
            $("#mother_first_name").removeClass("is-invalid");
        }

        if ($.trim($("#mother_last_name").val()).length == 0) {
            error_personal = 1;
            $("#error_mother_last_name").text("Last Name is required");
            $("#mother_last_name").addClass("is-invalid");
        } else {
            $("#error_mother_last_name").text("");
            $("#mother_last_name").removeClass("is-invalid");
        }

        if ($.trim($("#caste_category").val()).length == 0) {
            error_personal = 1;
            $("#error_caste_category").text("Caste is required");
            $("#caste_category").addClass("is-invalid");
        } else {
            $("#error_caste_category").text("");
            $("#caste_category").removeClass("is-invalid");
        }

        $("#caste_category").change(function () {
            if (
                $("#caste_category").val() === "SC" ||
                $("#caste_category").val() === "ST"
            ) {
                $("#caste_certificate_no_section").show();
                if ($.trim($("#caste_certificate_no").val()).length == 0) {
                    error_personal = 1;
                    $("#error_caste_certificate_no").text(
                        "Caste Certificate is required"
                    );
                    $("#caste_certificate_no").addClass("is-invalid");
                } else {
                    $("#error_caste_certificate_no").text("");
                    $("#caste_certificate_no").removeClass("is-invalid");
                }
            } else {
                $("#caste_certificate_no_section").hide();
                $("#error_caste_certificate_no").text("");
                $("#caste_certificate_no").removeClass("is-invalid");
            }
        });

        if ($.trim($("#marital_status").val()).length == 0) {
            error_personal = 1;
            $("#error_marital_status").text("Marital Status is required");
            $("#marital_status").addClass("is-invalid");
        } else {
            $("#error_marital_status").text("");
            $("#marital_status").removeClass("is-invalid");
        }

        if ($.trim($("#monthly_income").val()).length == 0) {
            error_personal = 1;
            $("#error_monthly_income").text(
                "Monthly Family Income is required"
            );
            $("#monthly_income").addClass("is-invalid");
        } else {
            $("#error_monthly_income").text("");
            $("#monthly_income").removeClass("is-invalid");
        }
        if (scheme_id == 17) {
            if ($.trim($("#app_phase").val()).length == 0) {
                error_personal = 1;
                $("#error_app_phase").text("Application Phase is required");
                $("#app_phase").addClass("is-invalid");
            } else {
                $("#error_app_phase").text("");
                $("#app_phase").removeClass("is-invalid");
            }

            if ($.trim($("#temple_type").val()).length == 0) {
                error_personal = 1;
                $("#error_temple_type").text("Temple Type is required");
                $("#temple_type").addClass("is-invalid");
            } else {
                $("#error_temple_type").text("");
                $("#temple_type").removeClass("is-invalid");
            }
        }
        if (scheme_id == 2) {
            if ($.trim($("#disablity_type").val()).length == 0) {
                error_personal = 1;
                $("#error_disablity_type").text("Disability Type is required");
                $("#disablity_type").addClass("is-invalid");
            } else {
                $("#error_disablity_type").text("");
                $("#disablity_type").removeClass("is-invalid");
            }

            if ($.trim($("#disablity_type_percentage").val()).length == 0) {
                error_personal = 1;
                $("#error_disablity_type_percentage").text(
                    "Disability Type Percentage is required"
                );
                $("#disablity_type_percentage").addClass("is-invalid");
            } else {
                var val = $("#disablity_type_percentage").val();
                var regex = /^((0|[1-9]\d?)(\.\d{1,2})?|100(\.00?)?)$/;

                if (!val.match(regex)) {
                    error_personal = 1;
                    $("#error_disablity_type_percentage").text(
                        "Disability Type Percentage must be a valid number between 0 and 100"
                    );
                    $("#disablity_type_percentage").addClass("is-invalid");
                } else if (parseFloat(val) < 40) {
                    error_personal = 1;
                    $("#error_disablity_type_percentage").text(
                        "Disability Percentage should be >= 40"
                    );
                    $("#disablity_type_percentage").addClass("is-invalid");
                } else {
                    $("#error_disablity_type_percentage").text("");
                    $("#disablity_type_percentage").removeClass("is-invalid");
                }
            }

            if ($.trim($("#disablity_type_authority").val()).length == 0) {
                error_personal = 1;
                $("#error_disablity_type_authority").text(
                    "Certifying Authority is required"
                );
                $("#disablity_type_authority").addClass("is-invalid");
            } else {
                $("#error_disablity_type_authority").text("");
                $("#disablity_type_authority").removeClass("is-invalid");
            }

            if ($.trim($("#disability_designation").val()).length == 0) {
                error_personal = 1;
                $("#error_disability_designation").text(
                    "Designation name is required"
                );
                $("#disability_designation").addClass("is-invalid");
            } else {
                $("#error_disability_designation").text("");
                $("#disability_designation").removeClass("is-invalid");
            }
        }

        if (scheme_id == 11) {
            if ($.trim($("#husband_first_name").val()).length == 0) {
                error_personal = 1;
                $("#error_husband_first_name").text(
                    "Husband's First Name is required"
                );
                $("#husband_first_name").addClass("is-invalid");
            } else {
                $("#error_husband_first_name").text("");
                $("#husband_first_name").removeClass("is-invalid");
            }

            if ($.trim($("#husband_last_name").val()).length == 0) {
                error_personal = 1;
                $("#error_husband_last_name").text(
                    "Husband's Last Name is required"
                );
                $("#husband_last_name").addClass("is-invalid");
            } else {
                $("#error_husband_last_name").text("");
                $("#husband_last_name").removeClass("is-invalid");
            }
        }
        if (scheme_id == 1 || scheme_id == 3) {
            if ($.trim($("#caste_certificate_no").val()).length == 0) {
                error_personal = 1;
                $("#error_caste_certificate_no").text(
                    "Caste Certificate No is required"
                );
                $("#caste_certificate_no").addClass("is-invalid");
            } else {
                $("#error_caste_certificate_no").text("");
                $("#caste_certificate_no").removeClass("is-invalid");
            }
        }
        
        error_personal = 0;
        if (error_personal == 1) {
            error_personal = 0;
            return false;
        } else {
            /*******SD**********/
            $("#list_personal_details").removeClass("active active_tab1");
            $("#list_personal_details").removeAttr("href data-toggle");
            $("#list_personal_details").addClass("inactive_tab1");

            $("#personal_details").removeClass("active show");

            $("#list_id_details").removeClass("inactive_tab1");
            $("#list_id_details").addClass("active_tab1 active");
            $("#list_id_details").attr("href", "#id_details");
            $("#list_id_details").attr("data-toggle", "tab");

            $("#id_details").addClass("active show"); 
            // $("#id_details").removeClass("fade");
            /*******************/
        }
    });
    $("#previous_btn_id_details").click(function () {
        $("#list_id_details").removeClass("active active_tab1");
        $("#list_id_details").removeAttr("href data-toggle");
        $("#id_details").removeClass("active show");
        $("#list_id_details").addClass("inactive_tab1");
        $("#list_personal_details").removeClass("inactive_tab1");
        $("#list_personal_details").addClass("active_tab1 active");
        $("#list_personal_details").attr("href", "#personal_details");
        $("#list_personal_details").attr("data-toggle", "tab");
        $("#personal_details").addClass("active show");
    });
});

function dob_checker(scheme_id, dob) {
    var age = parseInt($("#txt_age").val());
    // alert(age);

    if (scheme_id == 2 || scheme_id == 11 || scheme_id == 17) {
        $("#error_txt_age").text("");
        $("#txt_age").removeClass("is-invalid");
        return true;
    } else if (scheme_id == 8) {
        if (age < 18 || age > 60) {
            $("#error_txt_age").text("Age range is not properly");
            $("#txt_age").addClass("is-invalid");
            return false;
        } else {
            $("#error_txt_age").text("");
            $("#txt_age").removeClass("is-invalid");
            return true;
        }
    } else if (scheme_id == 9) {
        if (age < 60) {
            $("#error_txt_age").text("Age range is not properly");
            $("#txt_age").addClass("is-invalid");
            return false;
        } else {
            $("#error_txt_age").text("");
            $("#txt_age").removeClass("is-invalid");
            return true;
        }
    } else if (scheme_id == 5) {
        var phy_hnd = $.trim($("#phy_hadi_status").val());
        var marital_status = $.trim($("#marital_status").val());
        // console.log( marital_status);

        if (marital_status == "Widow") {
            $("#error_txt_age").text("");
            $("#txt_age").removeClass("is-invalid");
            // alert(marital_status );
            return true;
        } else {
            //alert(phy_hnd);
            if (phy_hnd == "Yes") {
                if (age < 55) {
                    $("#error_txt_age").text("Age range is not properly");
                    $("#txt_age").addClass("is-invalid");
                    return false;
                } else {
                    $("#error_txt_age").text("");
                    $("#txt_age").removeClass("is-invalid");
                    return true;
                }
            } else {
                if (age < 60) {
                    $("#error_txt_age").text("Age range is not properly");
                    $("#txt_age").addClass("is-invalid");
                    return false;
                } else {
                    $("#error_txt_age").text("");
                    $("#txt_age").removeClass("is-invalid");
                    return true;
                }
            }
        }
    } else if (scheme_id == 13) {
        // alert(age);
        var marital_status = $.trim($("#marital_status").val());
        if (marital_status == "Widow") {
            // alert('ok');
            $("#error_txt_age").text("");
            $("#txt_age").removeClass("is-invalid");
            return true;
        } else {
            if (age > 60) {
                alert("ok1");
                $("#error_txt_age").text("");
                $("#txt_age").removeClass("is-invalid");
                return true;
            } else {
                $("#error_txt_age").text("Age range is not properly");
                $("#txt_age").addClass("is-invalid");
                return false;
            }
        }
    } else {
        // alert('ok');
        if (age < 60) {
            $("#error_txt_age").text("Age range is not properly");
            $("#txt_age").addClass("is-invalid");
            return false;
        } else {
            $("#error_txt_age").text("");
            $("#txt_age").removeClass("is-invalid");
            return true;
        }
    }
}

// function age_checker(age, scheme_id)
