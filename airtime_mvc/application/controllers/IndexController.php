<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {

    }

    public function indexAction()
    {
        $CC_CONFIG = Config::getConfig();
        $baseUrl = Application_Common_OsPath::getBaseDir();
        $this->view->headLink()->setStylesheet($baseUrl.'css/radio-page/radio-page.css?'.$CC_CONFIG['airtime_version']);
        $this->view->headLink()->appendStylesheet($baseUrl.'css/embed/weekly-schedule-widget.css?'.$CC_CONFIG['airtime_version']);

        $this->_helper->layout->setLayout('radio-page');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getParams();
            Logging::info($params);

            $signed_request = $params["signed_request"];
            list($encoded_sig, $payload) = explode('.', $signed_request, 2);

            $secret = $_SERVER["FACEBOOK_APP_SECRET"];

            // decode the data
            $sig = $this->base64_url_decode($encoded_sig);
            $data = json_decode($this->base64_url_decode($payload), true);

            // confirm the signature
            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig) {
                error_log('Bad Signed JSON signature!');
                return null;
            }

            Logging::info($data);

            //TODO: check page id and redirect to corresponding station's radio page


        }

        $this->view->stationLogo = Application_Model_Preference::GetStationLogo();

        $stationName = Application_Model_Preference::GetStationName();
        $this->view->stationName = $stationName;

        $stationDescription = Application_Model_Preference::GetStationDescription();
        $this->view->stationDescription = $stationDescription;

        $this->view->stationUrl = Application_Common_HTTPHelper::getStationUrl();

        $displayRadioPageLoginButtonValue = Application_Model_Preference::getRadioPageDisplayLoginButton();
        if ($displayRadioPageLoginButtonValue == "") {
            $displayRadioPageLoginButtonValue = true;
        }
        $this->view->displayLoginButton = $displayRadioPageLoginButtonValue;

    }

    public function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public function mainAction()
    {
        $this->_helper->layout->setLayout('layout');
    }

    public function maintenanceAction()
    {
        $this->getResponse()->setHttpResponseCode(503);
    }

}
