# Photobooth by Andre Rinas
A Photobooth web interface for Raspberry Pi and Windows. Based on master version 2.1.0 

## Hardware Buzzer / Remote Trigger
Implements a server side (remote) trigger mechanism for photobooth. The trigger server will notify photobooth clients to either take a picture or a collage - similar to a button press on the screen or a key press on the client machine attached keyboard. The trigger server uses socket.io to maintain connectivity to the photobooth clients. Requires node.js on the photobooth webserver.

************
Installation
************
```
git clone https://github.com/jacques42/photobooth photobooth
git checkout socketio-HW-button
git submodule update --init
yarn install
yarn build
```
Please  take care of the webserver setup manually.

*************
Configuration
*************
Check photobooth admin settings area for subsection "Remote Buzzer" and detailed settings. Make sure to set the IP address of the Photobooth web server.

For debugging switch on dev settings in photobooth. Server logs will be written to the data-tmp directory of the photobooth installation (i.e. "data/tmp/io_server.log"). Clients will log server communication information to the browser console. 

***************
Hardware Buzzer
***************
If the web server is a RaspberryPi, the trigger server can connect to a GPIO pin and will watch for a PIN_DOWN event (pull to ground). This will initiate a socket.io message to the photobooth client, to trigger the action (thrill).

- Short button press (<= 2 sec) will trigger a photo in photobooth
- Long button press (> 2 sec) will trigger a collage in photobooth. If in photobooth collage is configured with interruption, any subsequent button press will trigger the next collage picture. 

The button is disabled while photobooth executes a task. In the admin settings, set "Raspberry Pi Pin Number" to 0 (zero) for to disable this whole functionality.

Check https://www.npmjs.com/package/rpio for additional settings required on the Pi

**************
Remote Trigger
**************
The trigger server controls and coordinates sending commands via socket.io to the photobooth client. Next to a hardware button, any socket.io client can connect to the trigger server over the network, and send a trigger command. This gives full flexibility to integrate other backend systems for trigger sinals.

- Channel: "photobooth-socket"
- Commands: "start-picture" or "start-collage"
- Response: "completed"  will be emitted to the client, once photobooth finished the task

## iPad 2 compatibility
Minor changes for  iPad2 compatibility of the code, in order to be able to use iPad2 on iOS 9.3.5 (latest version). Webkit6 is supported on iOS9.3.5 but on that platform lacks implementation of key word 'let' and arrow functions syntax.

## Changelog
- 2020-02-04: Collage via long button press, robustness
- 2020-02-02: All languages, restart trigger server at config change
- 2020-02-01: Initial version Remote Buzzer
- 2020-01-XX: Pulled updates from 2.1.0 release

## Todo

