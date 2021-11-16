<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap agoraio" id="agoraio">
  <h1><?php echo esc_html( __( 'Agora Recordings Listing', 'agoraio' ) ); ?></h1>

  <?php 
  $channel = WP_Agora_Channel::get_instance($_GET['id']); 
  if(!$channel){
    echo "<h3>Invalid Channel</h3>";
  } else {
    $channel_name = $channel->title(); ?>
    
    <h3><strong>Channel Name:</strong> <?php echo $channel_name; ?></h3>

    <div class="agora_recordings_filter_section">

      

    <div class="agora_recording_shortcode_section">
        <div id="agora_recording_shortcode"></div>
        <button class="agora-copy-recording-shortcode-btn">Copy</button>
    </div>
</div>

        <div class="agora_recordings_filter_inner_section agora_recording_type_section">
            <label for="agora_recording_type">Recording Type</label>
            <select class="create_recordings_shortcode" name="agora_recording_type" id="agora_recording_type" onchange="updateRecordingTypeOptions(this.value)">
                <option value="">Select</option>
                <option value="composite">Composite</option>
                <option value="individual">Individual</option>
            </select>
        </div>    
        
        <div class="agora_recordings_filter_inner_section agora_recording_from_date_section">
            <label for="agora_recording_from_date">From</label>
            <input type="text" id="agora_recording_from_date" name="agora_recording_from_date" autocomplete="off">
        </div>

        <div class="agora_recordings_filter_inner_section agora_recording_to_date_section">
            <label for="agora_recording_to_date">to</label>
            <input type="text" id="agora_recording_to_date" name="agora_recording_to_date" autocomplete="off">
        </div>

        <div id="agoraio_users_list" class="agora_recordings_filter_inner_section agora_recording_users_list_section"></div>
       
         <div class="agora_recordings_filter_inner_section agora_recording_type_section">
            <button id="generateRecShortcodeBtn">Apply Filter & Generate Shortcode</button>
            </div>


    <div class="agora_recording_videos_section">
        <div class="loader"></div>
    </div>

    <script>

    jQuery( function() {
        var dateFormat = "yy-mm-dd",
        from = jQuery( "#agora_recording_from_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            numberOfMonths: 1
        })
        .on( "change", function() {
            to.datepicker( "option", "minDate", getDate( this ) );
        }),
        to = jQuery( "#agora_recording_to_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            numberOfMonths: 1
        })
        .on( "change", function() {
            from.datepicker( "option", "maxDate", getDate( this ) );
        });
    
        function getDate( element ) {
            var date;
            try {
                date = jQuery.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }
        
            return date;
        }
    } );

    function updateRecordingTypeOptions(recType){
        if(recType=='individual'){
    
            let data = { action: 'get_all_users_list' };

            let ajaxParams = {
                type: 'POST',
                url: ajaxurl, // from wp admin...
                data
            };
        
            jQuery.ajax(ajaxParams).then(function(data) {
                
                let resObj = JSON.parse(data);
                let html = '<label>Users</label> <select name="agora_id_user_id">';                
                jQuery.each( resObj, function( key, value ) {
                    html+='<option value="'+key+'">'+value+'</option>';
                });
                html+='</select>';
                jQuery("body #agoraio_users_list").html(html);
            })
        } else {
            jQuery("body #agoraio_users_list").html('');
        }

    }

    jQuery("body").on("click", "#generateRecShortcodeBtn", function(){
        let channel_id = "<?php echo $_GET['id'] ?>";
        let recording_type = jQuery("body select[name='agora_recording_type']").val();
        let recording_from_date = jQuery("body input[name='agora_recording_from_date']").val();
        let recording_to_date = jQuery("body input[name='agora_recording_to_date']").val(); 

        let user_id = jQuery("body select[name='agora_id_user_id']").val();

        let recording_shortcode = '[agora-recordings channel_id="'+channel_id+'"';

        if(recording_from_date!=''){
            recording_shortcode += ' from_date="'+recording_from_date+'"';
        } 

        if(recording_to_date != ''){
            recording_shortcode += ' to_date="'+recording_to_date+'"';
        }

        if(recording_type!=''){
            recording_shortcode += ' recording_type="'+recording_type+'"';
        }

        if(recording_type == 'individual' && user_id!='' && user_id!=0){
            recording_shortcode += ' user_id = "'+user_id+'"';
        }

        recording_shortcode+=']';

        jQuery("body #agora_recording_shortcode").html(recording_shortcode);
        renderShortCodedata();
    });

    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand("copy");
            var msg = successful ? "successful" : "unsuccessful";
            console.log("Fallback: Copying text command was " + msg);
            if(msg=="successful"){
                jQuery("body .agora-copy-recording-shortcode-btn").text('Copied');
            }
        } catch (err) {
            console.error("Fallback: Oops, unable to copy", err);
        }

        document.body.removeChild(textArea);
    }

    function copyToClipboard (text) {
   
        console.log(text);
        ///let text = 'hello';
        if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
        }
        navigator.clipboard.writeText(text).then(
        function () {
            console.log("Async: Copying to clipboard was successful!");
            jQuery("body .agora-copy-recording-shortcode-btn").text('Copied');
        },
        function (err) {
            console.error("Async: Could not copy text: ", err);
        }
        );
    };

    jQuery("body").on("click", ".agora-copy-recording-shortcode-btn", function(){
        copyToClipboard(jQuery("body #agora_recording_shortcode").text());
    });

    jQuery(document).ready(function(){
        jQuery("body #generateRecShortcodeBtn").trigger('click');
        renderShortCodedata();
    })

    jQuery('body .agora-copy-recording-shortcode-btn').hover(function(){ 
        oldtext = jQuery(this).text(); 
        jQuery(this).text("Copy"); 
    }, function(){ 
        //jQuery(this).text(oldtext) 
    }); 

    function renderShortCodedata(){
        let data = { 
            action: 'run_recordings_shortcode', 
            shortcode: jQuery("body .agora_recording_shortcode_section #agora_recording_shortcode").text() 
        };

        let ajaxParams = {
            type: 'POST',
            url: ajaxurl, // from wp admin...
            data
        };

        jQuery("body .agora_recording_videos_section").html('<div class="loader"></div>');
    
        jQuery.ajax(ajaxParams).then(function(data) {
            console.log("recordingsShotcodeResponse", data);
            jQuery("body .agora_recording_videos_section").html(data);
        })
    }


    </script>
  <?php }
  ?>

</div>
