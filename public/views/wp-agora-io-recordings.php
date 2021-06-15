<?php 

require __DIR__.'/../third-party/amazonAWSLibrary/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

function getRecordingsList($atts) {

    if(isset($atts['channel_id'])){

        $uniqueKey = '121';

        $channel = WP_Agora_Channel::get_instance($atts['channel_id']);

        $channelSettings    = $channel->get_properties();
        $recordingSettings = $channelSettings['recording']; ?>
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <?php 
        // echo $recordingSettings['region'];
        // die;

        $bucket = $recordingSettings['bucket'];

        $s3 = S3Client::factory([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'credentials' => [
                'key'    => $recordingSettings['accessKey'],
                'secret' => $recordingSettings['secretKey'],
            ]
        ]);
		
		//$keyname = 'input/'.$uniqueKey.'/';
		$keyname = $uniqueKey.'/';
		
		$response = array();
		
		$result = $s3->listObjects(array('Bucket' => $bucket, 'Prefix' => $keyname));
		
		$files = $result->getPath('Contents');
		if($files!=""){
			foreach ($files as $file) {
                
				$filename = $file['Key'];
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$amazonawsURL = 'http://%s.s3.amazonaws.com/%s';

                $cmd = $s3->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $filename
                ]);
        
                $request = $s3->createPresignedRequest($cmd, '+20 minutes');
                $presignedUrl = (string)$request->getUri();

				if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
					$amazonawsURL = 'https://%s.s3.amazonaws.com/%s';
				}
				if($fileExt == "m3u8"){
                    //     echo "<pre>";
                    //     print_r($file);
                    // die;

                    echo " filename ".$filename;

            

                        $response[] = 
                        // array(
                        // 	'url' => sprintf($amazonawsURL, $bucket, $filename)
                        // );
                        //sprintf($amazonawsURL, $bucket, $filename); 
                        $presignedUrl;
				}
			}
		} 
        if(count($response)>0){ 
           $i=0;
           foreach($response as $file){ 
               echo $file;
               ?>
            <video id="video-<?php echo $i; ?>" controls></video>
            <script>
            if(Hls.isSupported())
            {
                var video = document.getElementById('video-<?php echo $i; ?>');
                var hls = new Hls();
                hls.loadSource("<?php echo $file; ?>");
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED,function()
                {
                    //video.play();
                });
            }
            else if (video.canPlayType('application/vnd.apple.mpegurl'))
            {
                video.src = "<?php echo $file; ?>";
                video.addEventListener('canplay',function()
                {
                    //video.play();
                });
            }
            </script>
        <?php $i++; } }
    }
}
?>