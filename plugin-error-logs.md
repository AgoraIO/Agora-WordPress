# WordPress Error logs, what causes them and what do they mean. #
The purpose of this document is to list all of the errors and warnings that can be displayed when using the Agora Video for WordPress plugin. 

## Basic Setup Errors ##

  1. _"Please configure your *Agora App ID* before use this shortcode"_  
  This error is dispalyed when the app id is not properly configured within the plugin's settings page. If this value was set but it fails to save, this is an indication of a plugin conflict.

  2. _"Please define the *channel_id* attribute to use on this shortcode"_  
  This error is dispalyed when the channel id is not properly configured within the plugin's shorcode that is embedded the page. To troublshoot, make sure to copy the entire shortcode from the Agora Channels page.

  3. Plugin Conflict  
   In the event of a plugin conflict, user's should disable all plugins except for the Agora Video for WordPress plugin. Once all other plugins are deactivate, save your Agora credentials and then re-activate the other plugins. 

## Javascript Errors and Warnings ##
  1. _"[ERROR] : AgoraRTC client init failed"_  
    This error is thrown when the Agora RTC client fails to initialize. 

  2. _"Live streaming failed"_  
    This message is displayed when the live streaming fails.

  3. _"Agora Exception:"_  
    This message is displayed when exception events occur in the channel. Exceptions are not errors, but usually mean quality issues. This callback also reports recovery from an exception. Each exception event has a corresponding recovery event, see the table below for details:
    ![Excepction Events](https://web-cdn.agora.io/docs-files/1547180053430)
    > Note: this callback is currently only supported by the Chrome Browser.

  4. _"Stream undefined cannot be removed"_  
    This is warning that is displayed when an error occurs within the `'peer-leave'` event. As a safety, the function checks that the event passed into the callback is a non `null` value and contains a stream with a non `null` value. If either the event or the stream values are `null`, a warning will be displayed in the logs and the function will return without completing any other steps. 

  5. Any log event follow _"Starting rec..."_  
    These errors occur because of an issue with the starting of the cloud recording service. Please refere to the [Common Errors](https://docs.agora.io/en/cloud-recording/common_errors?platform=All%20Platforms) section of the offical Agora.io Cloud Recording documentation.

  6. Any log event following _"Stoping rec..."_  
    These errors occur because of an issue with the stopping of the cloud recording service. Please refer to the [Common Errors](https://docs.agora.io/en/cloud-recording/common_errors?platform=All%20Platforms) section of the official Agora.io Cloud Recording documentation.

  7. Any log event prefixed _"API Error:"_  
    These errors occur because of an issue with the querying of the cloud recording service. Please refer to the [Common Errors](https://docs.agora.io/en/cloud-recording/common_errors?platform=All%20Platforms) section of the offical Agora.io Cloud Recording documentation.


  8. Any log event prefixed: _"Avatar not available:"_  
    This warning log is displayed when there is an error retrieving the user's Gravatar from the user's WordPress profile.


## PHP Errors and Warnings ##

  ### Cloud Recording ###
  1. _"Invalid SDK Parameters!"_  
    This error occurs when the WP client sends a malformed request to generate a POST request for Cloud Recording functionality. For some reason the request received by the backend does not set valid values within the `sdk_action` parameters of the request.
  
  2. _"SDK Action not defined!"_  
    This error occurs when the WP client sends a malformed request to generate a POST request for Cloud Recording functionality. In this instance the desired cloud recording action is within the expected list of actions. Expected "actions" are `start-recording`, `query-recording` and `stop-recording`.
  
  3. _"Incomplete data"_  
    This error occurs when the WP client sends a malformed request to generate a POST request for Cloud Recording functionality. In this instance the `recordingId` is missing from the input parameters.
  
  4. _"Storage Config not finished."_  
    This error occurs when the WP client sends a request to start Cloud Recording but the administrator has not properly configured the storage bucket within the Channel settings.

  5. _"No response from server"_  
    This error occurs when the WP client sends a request to the Agora Cloud Recording service but the Cloud Recording server fails to respond.

  ### Token Errors ###

  1. _"Token Server not configured!"_  
  This error occurs when the WP client attempts to generate a token but the administrator has not properly configured the app certificate within the plugin's settings page.

  2.  _"404 Channel Not Found"_, _"Undefined channel!"_, or '_"Channel not found"_  
  This error is displayed when the plugin attempts to generate a token using the token server functionality but there is  an issue with the channel name. 

  3. Any messge ending in _" check failed, should be a non-empty string"_  
    When the plugin attempts any token related functionality it first checks to ensure `appID`, `appCertificate`, `channelName`, and `token` are not empty values. 
  
  4. _"invalid version "_  
    When the plugin attempts any token related functionality it first checks to ensure the token version is valid.
