<?php

namespace App\Recaptcha;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Service de validation de Recaptcha Google
 *
 * Class RecaptchaValidator
 */
class RecaptchaValidator{

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * RecaptchaValidator constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params){
        $this->params = $params;
    }

    /**
     * @param string $recaptchaResponse Code envoyé par le captcha
     * @param string|null $ip Adresse IP client
     * @return bool
     */
    public function verify(string $recaptchaResponse, string $ip = null){

        if(empty($recaptchaResponse)) {
            return false;
        }
        $params = [
            'secret'    => $this->params->get('google_recaptcha.private_key'),
            'response'  => $recaptchaResponse
        ];
        if($ip){
            $params['remoteip'] = $ip;
        }
        $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
        if(function_exists('curl_version')){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
        }else{
            $response = file_get_contents($url);
        }
        if(empty($response) || is_null($response)){
            return false;
        }
        $json = json_decode($response);
        return $json->success;

    }

}