<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2018 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 */

namespace abc\modules\workers;

use abc\commands\BaseCommand;
use Exception;

abstract class ABaseWorker
{
    public $errors = [];
    public $output = [];
    public $hasError;
    protected $reRunIfFailed = false;
    protected $outputType = 'cli';
    protected $EOF = "\n";

    public function __construct()
    {
        $this->outputType = BaseCommand::$outputType;
        $this->EOF = BaseCommand::$EOF;
    }

    abstract public function getModuleMethods();

    /**
     * Starting worker`s method for processing incoming jobs
     *
     * @param string              $method
     * @param array | AMQPMessage $job_params
     *
     * @return bool
     */
    public function runJob($method, $job_params)
    {
        $result = false;
        $this->echoCli('****************************************************************');
        $this->echoCli($this->getTime()."- Starting worker ");

        /**
         * Requesting method to call
         */
        if (method_exists($this, $method) && in_array($method, $this->getModuleMethods())) {
            $this->echoCli('****************************************************************');
            $this->echoCli('Running '.$this->workerName.' with method: '.$method);
            try {
                /** @var boolean $result */
                //run worker
                $result = call_user_func([$this, $method], $job_params);
            } catch (Exception $e) {
                $this->echoCli('!!!!!!!!!!! Exception !!!!!!!!!!!!!');
                $error_message = 'Message: '.$e->getMessage().PHP_EOL.$e->getTraceAsString();
                $this->errors[] = $error_message;
                $this->echoCli($error_message);
            }
            $this->echoCli('****************************************************************');
            if ($result !== true) {
                $this->hasError = true;
            }
            $this->postProcessing();
        }
        return $result;
    }

    /**
     * @return string
     */
    public static function getTime()
    {
        return date("Y-m-d H:i:s", time());
    }

    public function echoCli($text)
    {
        if ($this->outputType == 'cli') {
            echo $text.$this->EOF;
        } else {
            $this->output[] = $text;
        }
    }

    public function isReRunAllowed()
    {
        return $this->reRunIfFailed;
    }

    abstract public function postProcessing();

}