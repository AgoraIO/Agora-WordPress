<?php 

 require __DIR__.'/../third-party/amazonAWSLibrary/aws-autoloader.php';
 use Aws\S3\S3Client;
 use Aws\S3\Exception\S3Exception;

 require __DIR__.'/../third-party/alibabaLibrary/autoload.php';
 use OSS\OssClient;
 use OSS\Core\OssException;

function getDatesFromRange($start, $end, $format = 'Y-m-d') {
      
    // Declare an empty array
    $array = array();
      
    // Variable that store the date interval
    // of period 1 day
    $interval = new DateInterval('P1D');
  
    $realEnd = new DateTime($end);
    $realEnd->add($interval);
  
    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
  
    // Use loop to store date into array
    foreach($period as $date) {                 
        $array[] = $date->format($format); 
    }
  
    // Return the array elements
    return $array;
}

function getRecordingListArray($keyname, $bucketregionIndex, $accessKey, $secretKey, $bucket, $recordingSettings, $recordings_regions, $date, $recording_type, $user_id, $allFiles){
    if($recordingSettings['vendor'] == 1){ //Amazon S3

        //$recordings_regions['aws'][$region];

        $bucketregion = str_replace("_","-",strtolower($recordings_regions['aws'][$bucketregionIndex]));

        $s3 = S3Client::factory([
            'version'     => 'latest',
            //'region'      => 'us-east-1',
            'region'      => $bucketregion,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ]
        ]);

        $marker = null;
        do {
            $result = $s3->listObjects(array(
                'Bucket'    => $bucket,
                'Prefix'    => $keyname,
                'Marker'    => $marker,
            ));
            $files = $result->getPath('Contents') ?: array();

            //$dirs = $result->getPath('CommonPrefixes/*/Prefix') ?: array();
            $marker = $result['IsTruncated'] ? end($files) : null;
            // DO WHATEVER YOU WANT WITH $files AND $dirs
        } while ($marker);

        $result = $s3->listObjects(array('Bucket' => $bucket, 'Prefix' => $keyname));

        $files = $result->getPath('Contents');
    

        //$files = $result->getPath('Contents');

        if($files!=""){
            foreach ($files as $file) {
                
                $filename = $file['Key'];
                $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                $amazonawsURL = 'http://%s.s3.amazonaws.com/%s';

                if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
                    $amazonawsURL = 'https://%s.s3.amazonaws.com/%s';
                }

                    $fileDir = pathinfo($filename);
                    
                    $fileDir = basename($fileDir['dirname']);
                    
                    if($fileDir == 'individual'){
                        if($fileExt == "mp4"){
                            if($recording_type == '' || $recording_type == $fileDir){
                                //echo "individual ".$filename;
                                if($user_id!=''){
                                    if (strpos($filename, 'uid_s_'.$user_id.'__') !== false){
                                        $allFiles[] = sprintf($amazonawsURL, $bucket, $filename); 
                                    }
                                } else {
                                    $allFiles[] = sprintf($amazonawsURL, $bucket, $filename); 
                                }
                            }
                        }
                    } else if($fileExt == "m3u8") {
                        if($recording_type == '' || $recording_type == $fileDir){
                            $allFiles[] = sprintf($amazonawsURL, $bucket, $filename); 
                        }
                    }
                
            }
        }

    } else if($recordingSettings['vendor'] == 2){ //Alibaba Cloud

        $endpointregion = str_replace("_","-",strtolower($recordings_regions['alibaba'][$bucketregionIndex]));

        //$endpoint = "oss-us-east-1.aliyuncs.com";
        $endpoint = "oss-".$endpointregion.".aliyuncs.com";

        try {
            $ossClient = new OssClient($accessKey, $secretKey, $endpoint);

            $nextMarker = '';

            while (true) {
                try {
                    $options = array(
                        'prefix' => $keyname,
                        'delimiter' => '',
                        'marker' => $nextMarker,
                    );
                    $listObjectInfo = $ossClient->listObjects($bucket, $options);
                } catch (OssException $e) {
                    printf(__FUNCTION__ . ": FAILED\n");
                    printf($e->getMessage() . "\n");
                    return;
                }
                // Obtain nextMarker to list the remaining objects. The reading starts with the object next to the last object previously obtained through the ListObjects API operation.
                $nextMarker = $listObjectInfo->getNextMarker();
                $listObject = $listObjectInfo->getObjectList();
                $listPrefix = $listObjectInfo->getPrefixList();

                if (! empty($listObject)) {
                    //print("objectList:\n");
                    foreach ($listObject as $objectInfo) {

                        $filename = $objectInfo->getKey();

                        $fileExt = pathinfo($objectInfo->getKey(), PATHINFO_EXTENSION);
                        $fileDir = pathinfo($filename);
                    
                        if($fileDir == 'individual'){
                            if($fileExt == "mp4"){
                                if($recording_type == '' || $recording_type == $fileDir){
                                    //echo "individual ".$filename;
                                    if($user_id!=''){
                                        if (strpos($filename, 'uid_s_'.$user_id.'__') !== false){
                                            $allFiles[] = 'https://'.$bucket.'.'.$endpoint.'/'.$objectInfo->getKey(); 
                                        }
                                    } else {
                                        $allFiles[] = 'https://'.$bucket.'.'.$endpoint.'/'.$objectInfo->getKey(); 
                                    }
                                }
                            }
                        } else if($fileExt == "m3u8") {
                            if($recording_type == '' || $recording_type == $fileDir){
                                $allFiles[] = 'https://'.$bucket.'.'.$endpoint.'/'.$objectInfo->getKey();
                            }
                        }
                    }
                }

                if ($listObjectInfo->getIsTruncated() !== "true") {
                    break;
                }
            }

        
            
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    return $allFiles;
}

function getRecordingsList($atts) {

    $output = ''; $recording_type = ''; $from_date = ''; $to_date = ''; $user_id='';

    if(isset($atts['recording_type'])){
        $recording_type = $atts['recording_type'];
    }

    if(isset($atts['from_date'])){
        $from_date = $atts['from_date'];
    }

    if(isset($atts['user_id'])){
        $user_id = $atts['user_id'];
    }

    if(isset($atts['to_date'])){
        $to_date = $atts['to_date'];
    }

    if(isset($atts['channel_id']) ){

        $channel_id = $atts['channel_id'];
        try{
            $channel = WP_Agora_Channel::get_instance($channel_id);

            if($channel == ''){
                return;
            }
        
            $channelSettings    = $channel->get_properties();
            $recordingSettings = $channelSettings['recording'];

            $bucket = $recordingSettings['bucket'];
            $accessKey = $recordingSettings['accessKey'];
            $secretKey = $recordingSettings['secretKey'];

            $bucketregionIndex = $recordingSettings['region'];

            $recordings_regions = WP_Agora_Public::$recordings_regions;


            $allFiles = array();

            $keyname = $channel_id.'/';

            if($from_date == ''){
             $allFiles = getRecordingListArray($keyname, $bucketregionIndex, $accessKey, $secretKey, $bucket, $recordingSettings, $recordings_regions, $from_date, $recording_type, $user_id, $allFiles);
            } else{
                if($to_date==''){
                    $to_date = $from_date;
                }
                
                $recordingsFilterDates = getDatesFromRange($from_date, $to_date);
                
                foreach($recordingsFilterDates as $date){
                    $keyname = $channel_id.'/';
                    $keyname=$keyname.str_replace("-","",$date).'/';
                    // if($recording_type!=''){
                    //     $keyname = $keyname.$recording_type.'/';
                    // }
                   
                    $allFiles = getRecordingListArray($keyname, $bucketregionIndex, $accessKey, $secretKey, $bucket, $recordingSettings, $recordings_regions, $from_date, $recording_type, $user_id, $allFiles);
                }
            }
            ?>
            <?php

            if(count($allFiles)>0){ 
                $i=0;
                $output.= '<div class="agora_io_video_recording_container">';

                if(isset($recording_type) && ($recording_type == 'individual')){
                    $output.= '<div class="recording_tooltip">If your recording is not available right now, please try after some time as it may take some time to process ..</div>';
                }

                foreach($allFiles as $file){ 

                    $output.='<div class="agora_io_video_container"><video id="video-'.$i.'" controls></video></div>';

                    $output .= '<script>';
                    $output .= "var video = document.getElementById('video-".$i."');";
                    $output .= "if(Hls.isSupported()){";
                    $output .= "var hls = new Hls();";
                    $output .= "hls.loadSource('".$file."');";
                    $output .= "hls.attachMedia(video);";
                    $output .= "}";

                    $output .= "else if (video.canPlayType('application/vnd.apple.mpegurl')){";
                    $output .= "video.src = '".$file."';";
                    $output .= "}";
                    $output .= " else {";
                    $output.= "var source = document.createElement('source');";
                    $output.= "source.src = '".$file."';";
                    $output.= "source.type = 'video';";
                    $output.= "video.appendChild(source);";
                    $output .= "}";
                    $output .= '</script>';
                    $i++; 
                } 
                $output.= '</div>';
            }
        } catch(Exception $e) {
            print_r($e);
            return;
        }
    }
    return $output;
}
?>