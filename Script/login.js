$(document).ready(function() {
    $(".login-btn").click(function () {
        if($("#username").val() != '' && $("#password").val() != ''){
            sessionStorage.setItem('key', $("#username").val());
            window.location.href='./index.html';
        }
        else{
            alert("Username and password are required")
        }
    });
});