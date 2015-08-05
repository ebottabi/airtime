$(document).ready(function() {

    /*$.ajax({
     type: "GET",
     dataType: "jsonp",
     crossDomain: true,
     headers: {
     "Accept" : "application/json; charset=utf-8",
     "Content-Type": "application/javascript; charset=utf-8",
     "Access-Control-Allow-Origin" : "*"
     },
     url: "https://www.facebook.com/dialog/oauth?client_id=1667975616771598&redirect_url=http://localhost/embeddablewidgets&scope=manage_pages%2Cpublish_stream&state=success",
     success: function (data) {
     console.log("hello");
     console.log(data);
     },
     error: function(data) {
     console.log("error");
     console.log(data);
     }
     });*/
    // get access token
    /*$.ajax({
        type: "GET",
        url: "https://graph.facebook.com/oauth/user_access_token?client_id=1667975616771598&redirect_uri=&scope=manage_pages%2Cpublish_stream&state=success",
        success: function(data) {
            console.log(data);
            var split = data.split("=");
            console.log(split);
            $.post("https://graph.facebook.com/851304201652055/tabs",
                {
                    "app_id": "1667975616771598",
                    "access_token": split[1]
                }, function (data) {
                    console.log(data);
                }
            );
        }
    })*/



   /*$.ajax({
        type: "GET",
        dataType: "jsonp",
        crossDomain: true,
        headers: {
            "Accept" : "application/json; charset=utf-8",
            "Content-Type": "application/javascript; charset=utf-8",
            "Access-Control-Allow-Origin" : "*",
            "X-Content-Type-Options": "nosniff"
        },
        url: "https://www.facebook.com/dialog/oauth?client_id=1667975616771598&redirect_url=&scope=manage_pages%2Cpublish_stream&scope=success",
        success: function (data) {
            console.log(data);
        }
    });*/

    /*$.post("https://graph.facebook.com/<page_id>/tabs",
        {
            "app_id": "<your_app_id>",
            "access_token": "<previously_fetched_access_token_for_page_id>"
        }, function () {

        }
    );*/
});
