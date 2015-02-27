if (window.MozWebSocket) {
  window.WebSocket = window.MozWebSocket;
}               

function openConnection() {
  // uses global 'conn' object
  if (conn.readyState === undefined || conn.readyState > 1) { 
    conn = new WebSocket(window.socket_server);                          //DEFINED AT init_main_functions.js
    conn.onopen = function () {
      //console.log('sockes.js -> onopen(): socket started');
      //state.className = 'success';
      //state.innerHTML = 'Socket open';
    };
    conn.onmessage = function (event) {
      var message = event.data;          
      if(message.indexOf("managerOnline")!=-1) {
        //connected.innerHTML = "is online";
        //stateM.className = 'online';
        return;
      } else if(message.indexOf("managerOffline")!=-1) {
        //connected.innerHTML = "is offline...";
        //stateM.className = 'offline';
        return;
      } else if(message.indexOf("new_ticket_added")!=-1) {
        //console.log('sockes.js -> onmessage(): new_ticket_added');
        socket_info("new_ticket_added");
      }         
      //log.innerHTML = '<li class="them">' + message.replace(/"/g,'') + '</li>' + log.innerHTML;
    };
    
    conn.onclose = function (event) {
      //state.className = 'fail';
      //state.innerHTML = 'Socket closed';
    };
  }
}

var //connected = document.getElementById('connected'),
    //log = document.getElementById('log'),
    //chat = document.getElementById('chat'),
    //form = chat.form,
    conn = {},
    //state = document.getElementById('status'),
    //stateM = document.getElementById('statusM'),    
    entities = {
      '<' : '&lt;',
      '>' : '&gt;',
      '&' : '&amp;'
    };

if (window.WebSocket === undefined) {
  //state.innerHTML = 'Sockets not supported';
  //state.className = 'fail';
} else {
  /*state.onclick = function () {
    if (conn.readyState !== 1) {
      conn.close();
      setTimeout(function () {
        openConnection();
      }, 250);
    }
  };  
  addEvent(form, 'submit', function (event) {
    event.preventDefault();
    if (conn.readyState === 1) {
      conn.send(JSON.stringify(chat.value));
      log.innerHTML = '<li class="you">' + chat.value.replace(/[<>&]/g, function (m) { return entities[m]; }) + '</li>' + log.innerHTML;
      chat.value = '';
    }
  });
  */
  if(window.use_socket==1) {
    openConnection()
  }  
}
