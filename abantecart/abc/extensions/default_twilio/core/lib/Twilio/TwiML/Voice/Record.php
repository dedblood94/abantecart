<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\TwiML\Voice;

use Twilio\TwiML\TwiML;

class Record extends TwiML
{
    /**
     * Record constructor.
     *
     * @param array $attributes Optional attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct('Record', $attributes);
    }

    /**
     * Add Action attribute.
     *
     * @param url $action Action URL
     *
     * @return TwiML $this.
     */
    public function setAction($action)
    {
        return $this->setAttribute('action', $action);
    }

    /**
     * Add Method attribute.
     *
     * @param httpMethod $method Action URL method
     *
     * @return TwiML $this.
     */
    public function setMethod($method)
    {
        return $this->setAttribute('method', $method);
    }

    /**
     * Add Timeout attribute.
     *
     * @param integer $timeout Timeout to begin recording
     *
     * @return TwiML $this.
     */
    public function setTimeout($timeout)
    {
        return $this->setAttribute('timeout', $timeout);
    }

    /**
     * Add FinishOnKey attribute.
     *
     * @param string $finishOnKey Finish recording on key
     *
     * @return TwiML $this.
     */
    public function setFinishOnKey($finishOnKey)
    {
        return $this->setAttribute('finishOnKey', $finishOnKey);
    }

    /**
     * Add MaxLength attribute.
     *
     * @param integer $maxLength Max time to record in seconds
     *
     * @return TwiML $this.
     */
    public function setMaxLength($maxLength)
    {
        return $this->setAttribute('maxLength', $maxLength);
    }

    /**
     * Add PlayBeep attribute.
     *
     * @param boolean $playBeep Play beep
     *
     * @return TwiML $this.
     */
    public function setPlayBeep($playBeep)
    {
        return $this->setAttribute('playBeep', $playBeep);
    }

    /**
     * Add Trim attribute.
     *
     * @param record :Enum:Trim $trim Trim the recording
     *
     * @return TwiML $this.
     */
    public function setTrim($trim)
    {
        return $this->setAttribute('trim', $trim);
    }

    /**
     * Add RecordingStatusCallback attribute.
     *
     * @param url $recordingStatusCallback Status callback URL
     *
     * @return TwiML $this.
     */
    public function setRecordingStatusCallback($recordingStatusCallback)
    {
        return $this->setAttribute('recordingStatusCallback', $recordingStatusCallback);
    }

    /**
     * Add RecordingStatusCallbackMethod attribute.
     *
     * @param httpMethod $recordingStatusCallbackMethod Status callback URL method
     *
     * @return TwiML $this.
     */
    public function setRecordingStatusCallbackMethod($recordingStatusCallbackMethod)
    {
        return $this->setAttribute('recordingStatusCallbackMethod', $recordingStatusCallbackMethod);
    }

    /**
     * Add Transcribe attribute.
     *
     * @param boolean $transcribe Transcribe the recording
     *
     * @return TwiML $this.
     */
    public function setTranscribe($transcribe)
    {
        return $this->setAttribute('transcribe', $transcribe);
    }

    /**
     * Add TranscribeCallback attribute.
     *
     * @param url $transcribeCallback Transcribe callback URL
     *
     * @return TwiML $this.
     */
    public function setTranscribeCallback($transcribeCallback)
    {
        return $this->setAttribute('transcribeCallback', $transcribeCallback);
    }
}