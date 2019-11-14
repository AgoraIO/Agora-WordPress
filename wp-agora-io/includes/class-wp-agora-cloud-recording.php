<?php

/**
 * The Agora Cloud Recording API Client
 *
 * @link       agora.co
 * @since      1.0.0
 *
 * @package    wp-agora-io
 * @subpackage wp-agora-io/includes
 */
define('MIN_RAND_VALUE', 10000000);
define('MAX_RAND_VALUE', 4294967295);

class AgoraCloudRecording {
    private $API_URL = 'https://api.agora.io/v1/apps/';
    private $settings = null;
    private $lang_context = 'agoraio';
    private $authAgoraSDK = '';

    public function __construct($settings, $parent) {
        $this->settings = $settings;
        $this->parent = $parent;

        $this->authAgoraSDK = $settings['customerID'].':'.$settings['customerCertificate'];
        $this->authAgoraSDK_B64 = base64_encode($this->authAgoraSDK);
        
        // expose public ajax endpoints:
        $ajaxAgoraCloudRecording = array($this, 'ajaxAgoraCloudRecording');
        add_action( 'wp_ajax_cloud_record', $ajaxAgoraCloudRecording );
        add_action( 'wp_ajax_nopriv_cloud_record', $ajaxAgoraCloudRecording );
    }

    public function ajaxAgoraCloudRecording() {
        if( !isset($_POST['sdk_action']) ) {
            $out = array('error' => 'Invalid SDK Parameters!');
            echo json_encode($out);
            wp_die();
        }

        $action = $_POST['sdk_action'];
        if ($action==='start-recording') {
            $out = $this->startRecording($_POST);
        } else if ($action==='stop-recording') {
            $out = $this->stopRecording($_POST);
        } else if ($action==='query-recording') {
            $out = $this->queryRecording($_POST);
        } else {
            $out = array('error' => 'SDK Action not defined!');
        }

        if (is_wp_error( $out )) {
            $code = 500;
            if (isset($out->errors['API'])) {
                $errorOut = $out->errors['API'][0];
                if (property_exists($errorOut, 'code')) {
                    if(intval($errorOut->code)>=400) {
                        $code = $errorOut->code;
                    }
                }
            }
            header('HTTP/1.1 '.$code);
        } else {
            header('HTTP/1.1 200 OK');
        }

        header('Content-Type: application/json');
        echo json_encode($out);
        die();
    }

    private function acquire($data) {
        $endpoint = $this->settings['appId']."/cloud_recording/acquire";
        $params = array(
            'cname' => $data['cname'],
            'uid' => $data['uid'],
            'clientRequest' => json_decode("{}")
        );
        return $this->callAPI($endpoint, $params, 'POST');
    }

    private function queryRecording($data) {
        
        if (isset($data['resourceId']) && !empty($data['resourceId'])) {
           $resourceId = $data['resourceId'];
        } else {
            $resource = $this->acquire($data);
            $resourceId = $resource->resourceId;
        }

        if (!isset($data['recordingId']) ) {
            return new WP_Error( 'data', "Incomplete data", $data );
        }

        $sid = $data['recordingId'];
        $endpoint = $this->settings['appId'].'/cloud_recording/resourceid/' . $resourceId . '/sid/' . $sid. '/mode/mix/query';
        
        $params = array(
            'cname' => $data['cname'],
            'uid' => $data['uid'],
            'clientRequest' => json_decode("{}")
        );
        // header('HTTP/1.1 500 Internal Server Error');
        // die("<pre>QUERY:".print_r($endpoint, true)."</pre>");
        return $this->callAPI($endpoint, array(), 'GET');
    }

    private function startRecording($data) {

        $data['uid'] = ''.rand(MIN_RAND_VALUE, MAX_RAND_VALUE);

        $resource = $this->acquire($data);
        // die("<pre>".print_r($resource, true)."</pre>");
        $resourceId = $resource->resourceId;
        
        $channel = WP_Agora_Channel::get_instance($data['cid']);
        $channelSettings    = $channel->get_properties();
        $recordingSettings = $channelSettings['recording'];
        if (!isset($recordingSettings['bucket'])) {
            return new WP_Error( 'data', "Storage Config not finished." );
        }

        // $sid = $data['sid'];
        $endpoint = $this->settings['appId'].'/cloud_recording/resourceid/' . $resourceId . '/mode/mix/start';

        $clientRequest = new stdClass();
        $clientRequest->recordingConfig = new stdClass();
        $clientRequest->recordingConfig->channelType = 1; // 1 = broadcast,  0=Communication

        // $clientRequest->recordingConfig->subscribeVideoUids
        // $clientRequest->recordingConfig->subscribeAudioUids
        $clientRequest->storageConfig = new stdClass();
        $clientRequest->storageConfig->vendor = intval($recordingSettings['vendor']);
        $clientRequest->storageConfig->region = intval($recordingSettings['region']);
        $clientRequest->storageConfig->bucket = $recordingSettings['bucket'];
        $clientRequest->storageConfig->accessKey = $recordingSettings['accessKey'];
        $clientRequest->storageConfig->secretKey = $recordingSettings['secretKey'];

        $t=date('d-m-Y');
        $day = strtolower(date("d", strtotime($t)));
        $month = strtolower(date("m", strtotime($t)));
        $year = strtolower(date("Y", strtotime($t)));
        
        $folderName = $month.$day.$year.preg_replace('/\s+/', '', $channel->title());
        $clientRequest->storageConfig->fileNamePrefix = array( $folderName );
        
        $newToken = $this->parent->generateNewToken($data['cid'], $data['uid']);
        // die("<pre>".print_r($newToken, true)."</pre>");
        $clientRequest->token = $newToken;
        
        $params = array(
            'cname' => $data['cname'],
            'uid' => $data['uid'],
            'clientRequest' => $clientRequest
        );

        // die("<pre>".print_r($params, true)."</pre>");
        $out = $this->callAPI($endpoint, $params, 'POST');
        if (!is_wp_error( $out )) {
            $out->uid = $data['uid'];
        }
        return $out;
    }


    private function stopRecording($data) {
        
        if (isset($data['resourceId']) && !empty($data['resourceId'])) {
           $resourceId = $data['resourceId'];
        } else {
            $resource = $this->acquire($data);
            $resourceId = $resource->resourceId;
        }

        if (!isset($data['cid']) || !isset($data['recordingId']) ) {
            return new WP_Error( 'data', "Incomplete data", $data );
        }

        $sid = $data['recordingId'];
        $endpoint = $this->settings['appId'].'/cloud_recording/resourceid/' . $resourceId . '/sid/' . $sid. '/mode/mix/stop';
        
        $params = array(
            'cname' => $data['cname'],
            'uid' => $data['uid'],
            'clientRequest' => json_decode("{}")
        );
        return $this->callAPI($endpoint, $params, 'POST');
    }


    /**
     * HTTP API Call
     *
     * Remote HTTP Call with cache usage through wp transients.
     **/
    private function callAPI($url=false, $params=array(), $method='GET') {
        if ($url) {

            //open connection
            $ch = curl_init();
            // $ip = $_SERVER['REMOTE_ADDR'];

            if($method==='GET' && !empty($params)) {
                $query_params = http_build_query($params);
                $url = sprintf("%s?%s", $url, $query_params);
            }

            $url = $this->API_URL . $url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(   
                'Content-Type: application/json',
                'Authorization: Basic ' . $this->authAgoraSDK_B64
            ));

            
            if($method==='POST' && !empty($params)) {
                // die("<pre>".print_r(json_encode($params), true)."</pre>");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            //execute post
            $result = curl_exec($ch);
            $err = curl_error($ch);
            // $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result) {
                $res = json_decode($result);
                if (property_exists($res, 'status') && $res->status==='error') {
                    return new WP_Error('API', $res->message, $params);
                }
                if (property_exists($res, 'code')) {
                    return new WP_Error('API', $res, $params);
                }

                return $res;
            } else {
                return new WP_Error( 'data', $err, $params );
            }
        }
        return false;
    }
}