# Photobooth by Andre Rinas
A Photobooth web interface for Raspberry Pi and Windows. 

Original Repo: https://github.com/andreknieriem/photobooth<br>
Original Readme: https://github.com/andreknieriem/photobooth/blob/master/README.md

# Changes
Based on master version 2.1.0 I have enhanced the code for my needs.

## Hardware Buzzer / Remote Trigger
Implements a server side (remote) trigger mechanism for photobooth. The trigger server will notify photobooth clients to either take a picture or a collage - similar to a button press on the screen or a key press on the client machine attached keyboard. The trigger server uses socket.io to maintain connectivity to the photobooth clients. Requires node.js on the photobooth webserver.

************
Installation
************
```
apt-get install libimage-exiftool-perl
git clone https://github.com/jacques42/photobooth photobooth
git submodule update --init
yarn install
yarn build
```
Please  take care of the webserver setup manually. The `install-raspbian.sh` in this repo currently does not work.

*************
Configuration
*************
Check photobooth admin settings area for subsection "Remote Buzzer" and detailed settings. Set "Raspberry Pi Pin Number" to 0 (zero) for to disable GPIO monitoring functionality.

**Make sure to set the IP address of the Photobooth web server in the admin settings**

For debugging switch on dev settings in photobooth. Server logs will be written to the data-tmp directory of the photobooth installation (i.e. "data/tmp/io_server.log"). Clients will log server communication information to the browser console. 

***************
Hardware Buzzer
***************
If the web server is a RaspberryPi, the trigger server can connect to a GPIO pin and will watch for a PIN_DOWN event (pull to ground). This will initiate a socket.io message to the photobooth client, to trigger the action (thrill).

- Short button press/release (<= 2 sec) will trigger a photo in photobooth
- Long button press/release (> 2 sec) will trigger a collage in photobooth. If collage is configured with interruption, any subsequent button press will trigger the next collage picture. 

Check https://www.npmjs.com/package/rpio for additional settings required on the Pi

**************
Remote Trigger
**************
The trigger server controls and coordinates sending commands via socket.io to the photobooth client. Next to a hardware button, any socket.io client can connect to the trigger server over the network, and send a trigger command. This gives full flexibility to integrate other backend systems for trigger signals.

- Channel: `photobooth-socket`
- Commands: `start-picture`, `start-collage`
- Response: `completed`  will be emitted to the client, once photobooth finished the task

## iPad 2 compatibility
Minor changes for  iPad2 compatibility of the code, in order to be able to use iPad2 on iOS 9.3.5 (latest version). Webkit6 is supported on iOS9.3.5 but on that platform lacks implementation of key word 'let' and arrow functions syntax.

## JPEG meta-data (i.e. EXIF)
During post-processing PHP GD drops all the meta data from the JPEG. Therefore I implemented the use of the perl-based `exiftool`,  to copy JPEG meta (e.g. EXIF) from the original file to the new file, after GD processing.  `exiftool` can be configured through the admin settings. Not tested on Windows. 

## Performance
Changes for slightly better performance on Raspberry Pi in my most common use-cases:
- Configurable on/off picture preview while processing filters. That way on an iPad2 the screen renders faster and the flow seems more smooth, in particular if there is no  post-processing going on. Can be configured in the admin settings via *"Preload and show image during filter processing"*
- Setting the JPEG quality to -1 in the settings and with no post-processing active (e.g. no filters, no frame, no polaroid, no chromakeying), now it will move the original camera file from data/tmp to data/images folder. This operation is much faster on the Pi vs. processing via PHP imagejpeg() and GD. Also avoids processing via `exiftool`, it is not required when the original file is retained.

## Changelog
- 2020-02-09: Retain JPEG meta data (e.g. EXIF)
- 2020-02-07: Small performance improvements for iPad2 / simple use-case scenario
- 2020-02-04: Collage via long button press, robustness
- 2020-02-02: All languages, restart trigger server at config change
- 2020-02-01: Initial version Remote Buzzer
- 2020-01-XX: Pulled updates from 2.1.0 release

## Todo

