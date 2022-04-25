$(document).ready(function () {

    let data = sessionStorage.getItem('key');
    if (data != null) {
      $(".login-link").html("Log Out");
      $(".name-link").html(data);
      $(".name-link").show();
    } else {
      $(".name-link").hide();
    }
  
    $(".login-link").click(function () {
      $(".login-link").html("Log In");
      sessionStorage.removeItem('key');
    });
  });