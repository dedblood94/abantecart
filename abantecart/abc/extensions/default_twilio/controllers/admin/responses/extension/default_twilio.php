<?php

namespace abc\controllers\admin;

/*------------------------------------------------------------------------------
   $Id$

   AbanteCart, Ideal OpenSource Ecommerce Solution
   http://www.AbanteCart.com

   Copyright © 2011-2018 Belavier Commerce LLC

   This source file is subject to Open Software License (OSL 3.0)
   Licence details is bundled with this package in the file LICENSE.txt.
   It is also available at this URL:
   <http://www.opensource.org/licenses/OSL-3.0>

  UPGRADE NOTE:
	Do not edit or add to this file if you wish to upgrade AbanteCart to newer
	versions in the future. If you wish to customize AbanteCart for your
	needs please refer to http://www.AbanteCart.com for more information.
 ------------------------------------------------------------------------------*/

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\lib\AConfig;
use abc\core\lib\AJson;
use abc\core\lib\ARequest;
use abc\core\lib\ASession;
use abc\models\admin\ModelSettingSetting;

/**
 * Class ControllerResponsesExtensionDefaultTwilio
 *
 * @package abc\controllers\admin
 *
 * @property ModelSettingSetting $model_setting_setting
 */
class ControllerResponsesExtensionDefaultTwilio extends AController
{

    public $data = [];

    public function test()
    {
        $this->registry->set('force_skip_errors', true);
        $this->loadLanguage('default_twilio/default_twilio');
        $this->loadModel('setting/setting');
        require_once (ABC::env('DIR_APP_EXTENSIONS').'default_twilio'.DS.'core'.DS.'lib'.DS.'Services'.DS.'Twilio.php');
        require_once(ABC::env('DIR_APP_EXTENSIONS').'default_twilio'.DS.'core'.DS.'lib'.DS.'Twilio'.DS.'autoload.php');

        $cfg = $this->model_setting_setting->getSetting(
            'default_twilio',
            (int)$this->session->data['current_store_id']
        );
        $AccountSid = $cfg['default_twilio_username'];
        $AuthToken = $cfg['default_twilio_token'];

        $sender = new \Twilio\Rest\Client($AccountSid, $AuthToken);
        $to = preg_replace('/[^0-9\+]/', '', $this->request->get['to']);

        $from = $this->config->get('default_twilio_sender_phone');
        $from = $from ? '+'.ltrim($from, '+') : '';

        $error_message = '';
        try {
            $sender->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => 'test message',
                ]
            );

        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $this->registry->set('force_skip_errors', false);
        $json = [];

        if (!$error_message) {
            $json['message'] = $this->language->get('text_connection_success');
            $json['error'] = false;
        } else {
            $json['message'] = "Connection to Twilio server can not be established.<br>"
                .$error_message.".<br>Check your server configuration or contact your hosting provider.";
            $json['error'] = true;
        }

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($json));

    }

}