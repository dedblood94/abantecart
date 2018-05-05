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

namespace abc\commands;

include_once ("ABCExecInterface.php");

/**
 * BaseCommand abstract class for the console commands for AbanteCart.
 * Implements common methods.
 *
 */
class BaseCommand implements ABCExecInterface
{
    /**
     * show start time before execution of a command
     *
     * @var bool
     */
    public $printStartTime = true;

    /**
     * show end time after execution of a command
     *
     * @var bool
     */
    public $printEndTime = true;

    /**
     * @var boolean Property that specifies to print or not. By default, no output.
     */
    public $verbose = 0;

    public $EOF = "\n";

    /**
     * @var array
     */
    protected $output = [];

    public function __construct()
    {
        $this->output = [];
    }

    public function run(string $action, array $options)
    {
        if ($this->printStartTime) {
            $this->write('Start Time: ' .date('m/d/Y h:i:s a', time()));
            $this->write('Action: ' . $action);
            if (!empty($options)) {
                $this->write('Params: ' . var_export($options, true));
            }
            $this->write('******************');
        }
    }

    public function finish(string $action, array $options)
    {
        if ($this->printEndTime) {
            $this->write('End Time: ' . date('m/d/Y h:i:s a', time()));
            $this->write('******************');
        }
    }

    public function validate(string $action, array $options)
    {
    }

    public function help($options = [])
    {
        return $this->getOptionList();
    }

    protected function getOptionList()
    {
        return [];
    }

    protected function write($output)
    {
        $this->output[] = $output;
    }

    public function getOutput()
    {
        return implode($this->EOF, $this->output);
    }
}
