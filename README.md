# Photobooth by Andre Rinas
A Photobooth web interface for Raspberry Pi and Windows. I've enhanced the code to meet my needs and added features

## iPad 2 compatibility
Minor changes for  iPad2 compatibility of the code, in order to be able to use iPad2 on iOS 9.3.5 (latest version). Webkit6 is supported on iOS9.3.5 but on that platform lacks implementation of key word 'let' and arrow functions syntax.

## Hardware Buzzer / Remote Trigger
Added server side (remote) trigger to take a picture. The trigger server will notify clients to either take a picture or a collage. It's using socket.io to maintain state and connectivity. Requires node.js on the photobooth webserver.

************
Installation
************
If installing over an existing version, you need to run "yarn install" after download - this will install the socket.io* and rpio* dependencies.

*************
Configuration
*************
Check photobooth admin settings area for subsection "Remote Buzzer" and detailed settings. Make sure to set the IP address of the Photobooth web server.

For debugging switch on dev settings in photobooth. Logs will be written to "data/tmp/io_server.log"  directory for photobooth. Clients will log to browser console. 

***************
Hardware Buzzer
***************
If the web server is a RaspberryPi, the trigger server can connect to a GPIO pin and will watch for a PIN_DOWN event (pull to ground). This will initiate a socket.io message to the photobooth client, to trigger the action (thrill).

- Short button press (<= 2000ms) will trigger a photo in photobooth
- Long button press (> 2000 ms) will trigger a collage in photobooth. If configured with interruption, any subsequent button press will trigger the next collage picture. 

Check https://www.npmjs.com/package/rpio for additional settings required on the Pi

In the admin settings, set "Raspberry Pi Pin Number" to 0 (zero) for to disable.

**************
Remote Trigger
**************
The trigger server controls and coordinates sending commands via socket.io to the photobooth client. Next to a hardware button, any socket.io client can connect to the trigger server over the network, and send a trigger command. This gives full flexibility to integrate other backend systems for trigger sinals.

- Channel: "photobooth-socket"
- Commands: "start-picture" or "start-collage"
- Response: "completed"  will be emitted to the client, once photobooth finished the task


## Changelog
- 2020-02-04: Collage via long button press, robustness
- 2020-02-02: All languages, restart trigger server at config change
- 2020-02-01: Initial version Remote Buzzer
- 2020-01-XX: Pulled updated from 2.1.0 release

## Todo
