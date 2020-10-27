# Agora Video for WordPress #  
**Contributors:** jalamprea, digitallysavvy  
**Tags:** live streaming, video streaming, video call, video conference  
**Requires at least:** 5.0.0   
**Tested up to:** 5.5.1   
**Requires PHP:** 7.1   
**Stable tag:** 2.0.0  
**License:** CC BY-ND 4.0   
**License URI:** https://creativecommons.org/licenses/by-nd/4.0  
**GitHub:** https://github.com/AgoraIO/Agora-Word-Press  

Easily add live streaming and video conferencing functionality into your Wordpress posts and page.

## Description ##
The [Agora Video for WordPress](https://wordpress.org/plugins/wp-agora-io) plugin lets you easily add live streaming or video conferencing functionality to your WordPress posts and pages. You can easily create and configure streaming or communication channels through the WordPress Admin dashboard and embed real time communications without any coding required!

Features Include:
-  Live video streaming with Host UI template
-  Video calling with group video chat UI template
-  Cloud recording directly to Amazon S3
-  Screen sharing support
-  RTMP support
-  Customizable UI
-  Customizable configurations (credentials, video profile, Codec, etc.) and much more!

Agora is a leading video, voice and live interactive streaming platform, helping developers deliver rich in-app experiences—including embedded voice and video chat, real-time recording, interactive live streaming, and real-time messaging.

With over 200+ data centers globally, the Agora’s Software-Defined Real-time Network (SD-RTN™) is trusted and  widely used by the world’s biggest brands and provides ease of scaling for up to 1 million peak concurrent users (PCU) in a single session with unmatched quality of experience.  
Agora  fully supports a range of development environments, making it easy to deliver deep integration of high-quality, extreme low-latency video calling across all platforms and channels.

Get started for free! The first 10,000 minutes each month are free, including free starter support through Slack. Agora offer a simple and affordable pricing model including volume discounts. See pricing details: [https://www.agora.io/pricing](https://www.agora.io/pricing)

- Terms of Service: [https://www.agora.io/en/terms-of-service](https://www.agora.io/en/terms-of-service)
- Privacy Policy: [https://www.agora.io/en/privacy-policy](https://www.agora.io/en/privacy-policy)
- Acceptable Use Policy: [https://www.agora.io/en/acceptable-use-policy](https://www.agora.io/en/acceptable-use-policy)
- Compliance Policy: [https://www.agora.io/en/compliance](https://www.agora.io/en/compliance)

## Features ##
-  Live video streaming with Host UI template
-  Video calling with group video chat UI template
-  Cloud recording directly to Amazon S3
-  Screen sharing support
-  RTMP support
-  Customizable UI
-  Customizable configurations (credentials, video profile, Codec, etc.) and much more!

### One-to-One Video Calls ###
Dramatically increase user engagement with Agora Video calling by delivering high quality, low-latency, one-to-one video call capabilities directly into your own applications. 

### Group Chat Video Calling ###
Group chat calls scales to include up to 17 participants – ensuring that you can handle all the use-cases you will ever need.

### Live Streaming ###
Agora’s flexible APIs enable deep integration of high quality, low latency – live video streaming experiences


## Quick Start Guide ##
 [https://medium.com/agora-io/agora-video-for-wordpress-plugin-quickstart-guide-b8838abd4245](https://medium.com/agora-io/agora-video-for-wordpress-plugin-quickstart-guide-b8838abd4245)

## Installation ##
This section describes how to install the plugin and get it working.

# Plugin Installation
### WP Plugins
1.  Go to the Add New section of the Plugins section of your WordPress Admin Dashboard.
2.  Search for Agora.io 
3.  Click the Install Now button 
4.  Once the plugin status changes to “Installed”, the Active button will appear. Click the Activate button.
![WP Plugins Installation](/assets/plugins.gif?raw=true "WP Plugins Installation")

### Zip Upload
1.  Visit the [Agora Video for WordPress plugin](https://wordpress.org/plugins/wp-agora-io/) page and download the plugin zip.
2.  Go the Plugins section of your WordPress Admin Dashboard
3.  Click the "Upload" button and then upload the wp-agora-io.zip through the WordPress Admin Dashboard.
4.  The plugin upload screen will load. Once "Plugin installed successfully..." click the Activate button.
![Zip Upload Installation](/assets/upload.gif?raw=true "Zip Upload Installation")

### FTP
1.  Visit the [Agora Video for WordPress plugin](https://wordpress.org/plugins/wp-agora-io/) page and download the plugin zip.
2.  Upload the `Agora-Word-Press` folder to the `/wp-content/plugins/` directory
3.  Activate the plugin through the 'Plugins' menu in WordPress Admin Dashboard
![FTP Installation](/assets/ftp.gif?raw=true "FTP Installation")

### Agora setup (basic)
1.  Create or Log into an Agora developer account (https://console.agora.io)
2.  Create a project and take note of the `App ID` and `App certificate` keys
3.  Navigate to the plugin's Settings page and input `App ID` and `App certificate` keys

### Agora setup (advanced)
1.  Create or Log into an Agora developer account (https://console.agora.io)
2.  Create a project and take note of the `App ID` and `App certificate` keys
3.  Navigate to "Products and Usage" page. Enable `RTMP` and `Cloud Recording`.
4.  Navigate to the Dashboard API section of your Agora Dashboard (https://console.agora.io/restfulApi) and take note of your `Customer ID` and `Customer Certificate` keys.
5.  Navigate to the plugin's page and input `App ID`, `App certificate`, `Customer ID`, and `Customer Certificate` keys

# Screen Shots
![Channels List](/assets/screenshot-1.png?raw=true "Channels List")
![Broadcast Channel Settings](/assets/screenshot-2.png?raw=true "Broadcast Channel Settings")
![WP Agora.io Settings Page](/assets/screenshot-3.png?raw=true "WP Agora.io Settings Page")

## Frequently Asked Questions ##
1.  *Q:* Why don't my project credentials (App ID and App Certificate) get saved when I input them on the Settings tab? 

  *A:* This could be occurring for a few different reasons, one of the more common issues is conflicts with plugins. To test if it is a plugin conflict, please disable all plugins except for Agora. Then, try to save your AppID and App Certificate. 
  
  > If this does not resolve the issue and your App ID and App Certificate are still not getting stored, please file a support ticket: https://agora-ticket.agora.io

2.  *Q:* What is the purpose of the RTMP and external URL buttons?

  *A:* The option for RTMP is for the purpose of pushing your Agora stream to a 3rd party service such as Vimeo or YouTube as a way to leverage 3rd party streaming services to increase your reach/visibility. This is an optional feature that does not need to be used if you don't want it. 

  The external url is for ingesting a stream from a CDN into the Agora stream. Similar to RTMP, this is an optional feature that does not need to be used if you don't want it. 

3.  *Q:*  How can I hide the RTMP and external URL buttons?

  *A:* You can hide the RTMP and external URL buttons by hiding their container. To do so, add this to your theme's _style.css_:
  ```#lower-ui-bar div { display: none; } #screen-share-btn-container { display: block !important; }```

4.  *Q:*  Does communication mode support cloud recording?

  *A:* Currently only Broadcast mode supports cloud recording, where the broadcaster has to initiate the recording.The decision was made during development for the privacy of users. The plugin allows for setting the cloud recording credentials within the communication channel settings because we want to allow users to extend the plugin based on their needs.

5.  *Q:*  Why doesn't the video work? I see the buttons but I don't get any prompts for video access.

  *A:* Browsers require a secure connection (HTTPS) for accessing a device's microphone and camera. When testing locally, localhost is a whitelisted URL but once you deploy to production you will need to have a secure connection for the plugin to function properly.

## Changelog ##

### 1.0.0 ###
* First version of this plugin.

### 1.1.0 ###
Hotfix - for environments where js global scope is limited to local file. Cleaned up some duplicate functions and ensure explicitly control scope.

### 1.2.0 ###
Hotfix - patch to fix ui button issue caused by UTILS naming collision. 

### 1.3.0 ###
Hotfix - patch to fix audience uid issue, where the audience uid in joinChannel did not match uid in token. 

### 1.4 ###
Updated to Agora WebSDK v3.0.2, fixed a patch for uid's.

### 1.5 ###
Updated to resolves issues with saving Agora Settings that are caused by plugin conflicts. Fixed grammer error. Release resolves issues:
- [43](https://github.com/AgoraIO/Agora-Word-Press/issues/43) 
- [52](https://github.com/AgoraIO/Agora-Word-Press/issues/52) 

### Version 1.5.1 ###
Updated to resolves issues with saving Agora Settings. Release resolves issues:
- [52](https://github.com/AgoraIO/Agora-Word-Press/issues/52) 

### Version 1.5.2 ###
Updated to resolves issues with saving Agora Settings created by previous hotfix. Release resolves issues:
- [52](https://github.com/AgoraIO/Agora-Word-Press/issues/52) 

### Version 1.6.2 ###
Updated to resolves issues with with screen sharing in Chrome browser. Resolves issue with injected stream not displaying in broadcast channel. Release resolves issues:
- [42](https://github.com/AgoraIO/Agora-Word-Press/issues/42) 
- [60](https://github.com/AgoraIO/Agora-Word-Press/issues/60) 

### Version 2.0.0 ###
Updated with a new UI, improved screen sharing, multi-host broadcasts, real-time chat, and other features/improvements.
- [47](https://github.com/AgoraIO/Agora-WordPress/issues/47) 
- [62](https://github.com/AgoraIO/Agora-WordPress/issues/62) 
- [63](https://github.com/AgoraIO/Agora-WordPress/issues/63) 
- [65](https://github.com/AgoraIO/Agora-WordPress/issues/65) 
- [66](https://github.com/AgoraIO/Agora-WordPress/issues/66) 
- [71](https://github.com/AgoraIO/Agora-WordPress/issues/71) 