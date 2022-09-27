<?php

namespace addons\cnework\library;

/**
 * Class Tencentsms
 */
class Tencentsms
{
    public $appid = '';
    public $secretId = '';
    public $secretkey = '';
    public $sign = '';
    public $templateid = '';

    public function __construct($config)
    {
        $this->appid = isset($config['sms_appid']) ? $config['sms_appid'] : '';
        $this->secretId = isset($config['sms_secretid']) ? $config['sms_secretid'] : '';
        $this->secretKey = isset($config['sms_secretkey']) ? $config['sms_secretkey'] : '';
        $this->sign = isset($config['sms_sign']) ? $config['sms_sign'] : '';
        $this->templateid = isset($config['sms_templateid']) ? $config['sms_templateid'] : '';
    }

    
    public function send($mobiles, $params = [], $context = '')
    {
        // 密钥参数，云API密匙查询: https://console.cloud.tencent.com/cam/capi
        $secretId = $this->secretId;
        $secretKey = $this->secretKey;
        $host = "sms.tencentcloudapi.com";
        $service = "sms";
        $version = "2021-01-11";
        $action = "SendSms";
        $region = "ap-guangzhou";
        $timestamp = time();
        $algorithm = "TC3-HMAC-SHA256";

        // step 1: build canonical request string
        $httpRequestMethod = "POST";
        $canonicalUri = "/";
        $canonicalQueryString = "";
        $canonicalHeaders = "content-type:application/json; charset=utf-8\n"."host:".$host."\n";
        $signedHeaders = "content-type;host";
        // 实际调用需要更新参数，这里仅作为演示签名验证通过的例子
        $payloadObj = array(
            "SmsSdkAppId" => $this->appid,
            "SignName" => $this->sign,
            "TemplateId" => $this->templateid,
            "PhoneNumberSet" => $mobiles,
            //"TemplateParamSet" => $params,
            //"SessionContext" => "test",
        );

        if ($params) {
            $payloadObj['TemplateParamSet'] = $params;
        }
        $payload = json_encode($payloadObj);
        $hashedRequestPayload = hash("SHA256", $payload);
        $canonicalRequest = $httpRequestMethod."\n"
            .$canonicalUri."\n"
            .$canonicalQueryString."\n"
            .$canonicalHeaders."\n"
            .$signedHeaders."\n"
            .$hashedRequestPayload;

        //echo $canonicalRequest.PHP_EOL;

        // step 2: build string to sign
        $date = gmdate("Y-m-d", $timestamp);
        $credentialScope = $date."/".$service."/tc3_request";
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $stringToSign = $algorithm."\n"
            .$timestamp."\n"
            .$credentialScope."\n"
            .$hashedCanonicalRequest;

        //echo $stringToSign.PHP_EOL;

        // step 3: sign string
        $secretDate = hash_hmac("SHA256", $date, "TC3".$secretKey, true);
        $secretService = hash_hmac("SHA256", $service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);
        //echo $signature.PHP_EOL;

        // step 4: build authorization
        $authorization = $algorithm
            ." Credential=".$secretId."/".$credentialScope
            .", SignedHeaders=content-type;host, Signature=".$signature;
        //echo $authorization.PHP_EOL;

//        $curl = "curl -X POST https://".$host
//            .' -H "Authorization: '.$authorization.'"'
//            .' -H "Content-Type: application/json; charset=utf-8"'
//            .' -H "Host: '.$host.'"'
//            .' -H "X-TC-Action: '.$action.'"'
//            .' -H "X-TC-Timestamp: '.$timestamp.'"'
//            .' -H "X-TC-Version: '.$version.'"'
//            .' -H "X-TC-Region: '.$region.'"'
//            ." -d '".$payload."'";

//        echo $curl.PHP_EOL;

        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: application/json; charset=utf-8',
            'Host: '.$host,
            'X-TC-Action: '.$action,
            'X-TC-Timestamp: '.$timestamp,
            'X-TC-Version: ' . $version,
            'X-TC-Region: ' . $region,
        ];

        $ch = curl_init('https://' . $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $post = is_string($payloadObj) ? $payloadObj : http_build_query($payloadObj);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        $err = curl_error($ch);

        //echo $result;
    }
}