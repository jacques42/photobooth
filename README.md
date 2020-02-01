# Photobooth by Andre Rinas
A Photobooth web interface for Raspberry Pi and Windows. I've enhanced the code to meet my needs and added features

## iPad 2 compatibility
I do maintain iPad2 compatibility of the code, in order to be able to use iPad2 on iOS 9.3.5 (latest version available).

## Remote Buzzer / Remote Trigger
Added server side (remote) trigger to take a picture. The trigger server will notify clients to take a picture (start thrill). It's using socket.io to maintain state and connectivity

Check admin settings area "Remote Buzzer" for settings. Make sure you set the IP address of the Photobooth web server.

Hardware Trigger:
- If the web server is a RaspberryPi, the trigger server can connect to a GPIO pin and will watch for a PIN_DOWN event (pull to ground). 
- Check https://www.npmjs.com/package/rpio for additional settings required on the Pi

Remote Trigger:
- Any websocket / socket.io client can connect to the trigger server
- Send "start" on channel "takepicture" to take picture.

Requires node.js on the photobooth webserver. 

