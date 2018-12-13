$(document).ready(function () {
    $(".comment-form").on('submit', function (e) {
        var fd = new FormData(this);
        $.ajax({
            url: "./process/add-comment.php",
            method: "POST",
            data: fd,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.result == "true") {
                    location.reload();
                } else {
                    $(
                        '<div class="col-lg-4"><div class="alert alert-danger" role="alert">' + data.msg + "</div></div>"
                    ).insertAfter("nav");
                }
            },
            error: function (e) {
                console.log(e.status);
            }
        });
        e.preventDefault();
    });
    $(".deletePost").on("click", deletePost);
    $("#postForm").keypress(function (e) {
        if (e.which == 13) {
            data = new Object();
            data.wallId = $(this)[0][0].value;
            data.authorId = $(this)[0][1].value;
            data.content = $(this)[0][2].value;
            data.img = $("input[name='img']")[0].value.replace(/^.*\\/, "");
            data.video = $("input[name='video']")[0].value;
            $.ajax({
                url: "./process/add-post.php",
                method: "POST",
                data: data,
                success: function (data) {
                    data = JSON.parse(data);
                    console.log(data);
                    if (data.result == "true") {
                        location.reload();
                    } else {
                        $(
                            '<div class="col-lg-4"><div class="alert alert-danger" role="alert">' + data.msg + '</div></div>'
                        ).insertAfter("nav");
                        window.setTimeout(function () {
                            location.reload();
                        }, 5000);
                    }
                },
                error: function (e) {
                    console.log(e.status);
                }

            });
        }
    })

    $(".comment-wrapper").on("click", toggleComments);
});

function deletePost(e) {
    val = $(this).children("form")[0][0].value;
    data = new Object();
    data.postId = val;
    $.ajax({
        url: "./process/delete-post.php",
        method: "POST",
        data: data,
        success: function (e) {
            data = JSON.parse(e);
            if (data.result == "true") {
                location.reload();
            } else {
                $(
                    '<div class="col-lg-4"><div class="alert alert-warning" role="alert">' + data.msg + "</div></div>"
                ).insertAfter("nav");
            }
        },
        error: function (e) {
            console.log(e.status);
            $(
                '<div class="col-lg-4"><div class="alert alert-warning" role="alert">' + data.msg + "</div></div>"
            ).insertAfter("nav");
        }
    });
}

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function toggleComments(e) {
    $(this).nextAll(".comment-container").toggleClass("hidden");
}

function formhash(form, password) {
    // Create a new element input, this will be our hashed password field.
    var p = document.createElement("input");

    // Add the new element to our form.
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);

    // Make sure the plaintext password doesn't get sent.
    password.value = "";

    // Finally submit the form.
    form.submit();
}

function regformhash(form, username, email, password, conf, file) {
    // Check each field has a value
    if (
        username.value == "" ||
        email.value == "" ||
        password.value == "" ||
        conf.value == "" ||
        file.value == ""
    ) {
        alert("You must provide all the requested details. Please prout");
        return false;
    }

    // Check the username

    re = /^\w+$/;
    if (!re.test(form.username.value)) {
        alert(
            "Username must contain only letters, numbers and underscores. Please try again"
        );
        form.username.focus();
        return false;
    }

    // Check that the password is sufficiently long (min 6 chars)
    // The check is duplicated below, but this is included to give more
    // specific guidance to the user
    if (password.value.length < 6) {
        alert("Passwords must be at least 6 characters long.  Please try again");
        form.password.focus();
        return false;
    }

    // At least one number, one lowercase and one uppercase letter
    // At least six characters

    var re = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/;
    if (!re.test(password.value)) {
        alert(
            "Passwords must contain at least one number, one lowercase and one uppercase letter.  Please try again"
        );
        return false;
    }

    // Check password and confirmation are the same
    if (password.value != conf.value) {
        alert("Your password and confirmation do not match. Please prout again.");
        form.password.focus();
        return false;
    }

    // Create a new element input, this will be our hashed password field.
    var p = document.createElement("input");

    // Add the new element to our form.
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);

    // Make sure the plaintext password doesn't get sent.
    password.value = "#";
    conf.value = "#";

    // Finally submit the form.
    form.submit();
}
