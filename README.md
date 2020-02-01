# Photobooth by Andre Rinas
A Photobooth web interface for Raspberry Pi and Windows. I've enhanced the code to meet my needs and added features

## iPad 2 compatibility
I do maintain iPad2 compatibility of the code, in order to be able to use iPad2 on iOS 9.3.5 (latest version available).

## Remote Buzzer / Remote Trigger
Added server side trigger to take a picture based on socket.io. Server runs a websocket socket.io server, for clients to connect. The server will notify clients to trigger a picture (start thrill).

Check admin settings area "Remote Buzzer" for settings.

Hardware Trigger:
- On RaspPi, the server can connect to a GPIO pin and will watch for a PIN_DOWN event (pull to ground). 

Remote Trigger:
- Any websocket client can connect to the server. Send "start" on channel "takepicture" to trigger.

Requires node.js on the photobooth webserver. 

