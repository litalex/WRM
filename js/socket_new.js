          var mwsUri = "ADDRESS"
          
          function sendMessage(message, receivers) {  
              $.ajax({
                  url: mwsUri + "/messages",
                  type: "POST",
                  contentType: "application/json; charset=utf-8",
                  dataType: "json",
                  data: JSON.stringify({data: message, receivers: receivers}),
                  success: function(response) {
                      //console.log("Message sent", response)
                  },
                  error: function(response) {
                      //console.log("Ooops! Something went wrong!", response.responseText)
                      $("#socket_stat").html('');
                  }
              })
          }
          
          function connectToWebsocket(options) {
              $.ajax({
                  url: mwsUri + "/websocket_uri",
                  success: function(response) {
                      // connect to websocket using returned uri
                      var ws = new WebSocket('ADDRESS');
                      ws.onopen = options.onopen;
                      ws.onmessage = options.onmessage;
                      //alert("CONNECTING");
                  },
                  error: function(response) {
                      //alert("Ooops! Something went wrong!", response.responseText)
                      $("#socket_stat").html('');
                  }
              });
          }
          
