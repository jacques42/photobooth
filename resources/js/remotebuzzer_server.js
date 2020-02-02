/* PARSE COMMAND LINE */
var myArgs = process.argv.slice(2);

var socketPort = myArgs[0],
    pinNum = myArgs[1],
    $myPIDFileFolder = myArgs[2];

var myPid = process.pid;

console.log('socket.io server [',myPid,']: Requested to start on port ',socketPort, ", Pin ", pinNum);


/* WRITE PROCESS PID FILE */
const fs = require('fs');
var $filename = $myPIDFileFolder+"/remotebuzzer_server.pid";

fs.writeFile($filename, myPid, function(err) {
    if(err) {
	console.log('socket.io server [',myPid,']: Unable to write PID file ', $filename, ' Error:', err.message);
	process.exit();
    } else
    {
	console.log('socket.io server [',myPid,']: PID file created ', $filename);
    }
});

/* HANDLE EXCEPTIONS */
process.on('uncaughtException', (err, origin) => {
    console.log('socket.io server [',myPid,']: uncaught error: ', err.message);

    fs.unlink($filename, function(err) {
	if (err) {
	    console.log('socket.io server [',myPid,']: Error while trying to delete PID file ', err.message);
	} else {
	    console.log('socket.io server [',myPid,']: Removed PID file ',$filename);
	}
    }); 
    
    console.log('socket.io server [',myPid,']: Exiting process');
    process.exit();
});

/* START WEBSOCKET SERVER */
const io_server = require('socket.io')(socketPort);

io_server.on('connection', function (client) {
    console.log('socket.io server [',myPid,']: New client connected - ID', client.id);

    client.on('takepicture', function(data){
	console.log('socket.io server [',myPid,']: Data from client ID ',client.id,': [ takepicture ] =>  [',data,']');

	switch (data) {
	case 'completed':
	    trigger_armed = true;
	    trigger_clientid = false;
	    break;
	case 'in progress':
	    trigger_armed = false;
	    trigger_clientid = client.id;
	    break;
	case 'start':
	    trigger_armed = false;
	    io_server.emit('takepicture', 'start');
	    break;	    
	}
    });
    
    client.on('disconnect', function() {
	if (!trigger_armed && trigger_clientid == client.id)
	{
	    console.log('socket.io server [',myPid,']: Active client disconnected - ID ',client.id, ' - removing lock and arming trigger' );
	    trigger_armed = true;
	    trigger_clientid = false;
	} else
	{
	    console.log('socket.io server [',myPid,']: Inactive client disconnected - ID ',client.id);
	}
    });
});

console.log('socket.io server [',myPid,']: socket.io server started');

/* LISTEN TO GPIO STATUS https://www.npmjs.com/package/rpio */
if (pinNum >= 1 && pinNum <= 40)
{
    console.log('socket.io server [',myPid,']: Connecting to Raspberry pin P', pinNum);
    
    var rpio = require('rpio');
    var trigger_armed = true,
	trigger_clientid = false;
    
    rpio.open(pinNum, rpio.INPUT, rpio.PULL_UP);
    
    function pollcb(pin)
    {
	/* Hysteresis to filter false positives */
	rpio.msleep(20); 
	if (rpio.read(pin))
            return;
	
	/* notify clients */
	if (trigger_armed)
	{
	    console.log('socket.io server [',myPid,']: Button pressed on pin P', pin);
	    console.log('socket.io server [',myPid,']: Notify all clients to start Thrill');
	    io_server.emit('takepicture', 'start');
	    
	    trigger_armed = false;
	}
	
    }
    
    rpio.poll(pinNum, pollcb, rpio.POLL_LOW);
}
