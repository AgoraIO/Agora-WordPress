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
class AgoraCloudRecording {
    private $API_URL = 'https://api.agora.io/v1/apps/';
    private $settings = null;
    private $lang_context = 'agoraio';
    private $authAgoraSDK = '';

    public function __construct($settings) {
        $this->settings = $settings;

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
        } else {
            $out = array('error' => 'SDK Action not defined!');
        }

        header('Content-Type: application/json');
        echo json_encode($out);
        wp_die();
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

    private function startRecording($data) {

        // $resource = $this->acquire($data);
        // $resourceId = $resource['resourceId'];
        $resourceId = '--';
        
        $channel = WP_Agora_Channel::get_instance($data['cid']);
        $channelSettings    = $channel->get_properties();
        $recordingSettings = $channelSettings['recording'];
        if (!isset($recordingSettings['bucket'])) {
            return new WP_Error( 'data', "Storage Config not finished." );
        }

        $sid = $data['sid'];
        $endpoint = $this->settings['appId'].'/cloud_recording/resourceid/' . $resourceId . '/mode/mix/start';

        $clientRequest = new stdClass();
        $clientRequest->recordingConfig = new stdClass();
        $clientRequest->recordingConfig->channelType = 1; // 1 = broadcast,  0=Communication
        $clientRequest->storageConfig = new stdClass();
        $clientRequest->storageConfig->vendor = $recordingSettings['vendor'];
        $clientRequest->storageConfig->region = $recordingSettings['region'];
        $clientRequest->storageConfig->bucket = $recordingSettings['bucket'];
        $clientRequest->storageConfig->accessKey = $recordingSettings['accessKey'];
        $clientRequest->storageConfig->secretKey = $recordingSettings['secretKey'];
        
        $params = array(
            'cname' => $data['cname'],
            'uid' => $data['uid'],
            'clientRequest' => $clientRequest
        );
        if (isset($data['token'])) {
            $params['token'] = $data['token'];
        }
        return $this->callAPI($endpoint, $params, 'POST');
    }


    private function stopRecording($data) {
        // $p = $this->callAPI($endpoint, $params);
        if (isset($data['resourceId'])) {
            $resourceId = $data['resourceId'];
        } else {
            $resource = $this->acquire($data);
            $resourceId = $resource['resourceId'];
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
    private function callAPI($url=false, $params=array(), $method='GET', $force_save_trans=false) {
        if ($url) {

            //open connection
            $ch = curl_init();
            // $ip = $_SERVER['REMOTE_ADDR'];

            if($method==='GET') {
                $query_params = http_build_query($params);
                $url = sprintf("%s?%s", $url, $query_params);
            }

            $url = $this->API_URL . $url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if($method==='POST' && !empty($params)) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(   
                // 'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $this->authAgoraSDK_B64
            ));

            //execute post
            $result = curl_exec($ch);
            $err = curl_error($ch);
            $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result) {
                $res = json_decode($result);
                if (property_exists($res, 'status') && $res->status==='error') {
                    return new WP_Error('API', $res->message, $params);
                }

                return $res;
            } else {
                return new WP_Error( 'data', $err, $params );
            }
        }
        return false;
    }
}