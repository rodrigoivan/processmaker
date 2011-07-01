<?php

/**
 * class.soapNtlm.php
 * @package gulliver.system
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2011 Colosa Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd.,
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 *
 */
/* SOAP NTLM
 * This class is used to extend SoapClient native PHP
 * to allow NTLM authentication throw soap connection
 */

/**
 * @package gulliver.system
 */
class soapNtlm {

  private $path;
  private $mode;
  private $options;
  private $opened_path;
  private $buffer;
  private $pos;

  /**
   * Open the stream
   *
   * @param unknown_type $path
   * @param unknown_type $mode
   * @param unknown_type $options
   * @param unknown_type $opened_path
   * @return unknown
   */
  public function stream_open($path, $mode, $options, $opened_path) {
    //echo "[NTLMStream::stream_open] $path , mode=$mode \n";
    $this->path = $path;
    $this->mode = $mode;
    $this->options = $options;
    $this->opened_path = $opened_path;

    $this->createBuffer($path);

    return true;
  }

  /**
   * Close the stream
   *
   */
  public function stream_close() {
    //echo "[NTLMStream::stream_close] \n";
    curl_close($this->ch);
  }

  /**
   * Read the stream
   *
   * @param int $count number of bytes to read
   * @return content from pos to count
   */
  public function stream_read($count) {
    //echo "[NTLMStream::stream_read] $count \n";
    if (strlen($this->buffer) == 0) {
      return false;
    }

    $read = substr($this->buffer, $this->pos, $count);

    $this->pos += $count;

    return $read;
  }

  /**
   * write the stream
   *
   * @param int $count number of bytes to read
   * @return content from pos to count
   */
  public function stream_write($data) {
    //echo "[NTLMStream::stream_write] \n";
    if (strlen($this->buffer) == 0) {
      return false;
    }
    return true;
  }

  /**
   *
   * @return true if eof else false
   */
  public function stream_eof() {
    //echo "[NTLMStream::stream_eof] ";
    if ($this->pos > strlen($this->buffer)) {
      echo "true \n";
      return true;
    }

    echo "false \n";
    return false;
  }

  /**
   * @return int the position of the current read pointer
   */
  public function stream_tell() {
    echo "[NTLMStream::stream_tell] \n";
    return $this->pos;
  }

  /**
   * Flush stream data
   */
  public function stream_flush() {
    //echo "[NTLMStream::stream_flush] \n";
    $this->buffer = null;
    $this->pos = null;
  }

  /**
   * Stat the file, return only the size of the buffer
   *
   * @return array stat information
   */
  public function stream_stat() {
    //echo "[NTLMStream::stream_stat] \n";

    $this->createBuffer($this->path);
    $stat = array(
        'size' => strlen($this->buffer),
    );

    return $stat;
  }

  /**
   * Stat the url, return only the size of the buffer
   *
   * @return array stat information
   */
  public function url_stat($path, $flags) {
    //echo "[NTLMStream::url_stat] \n";
    $this->createBuffer($path);
    $stat = array(
        'size' => strlen($this->buffer),
    );

    return $stat;
  }

  /**
   * Create the buffer by requesting the url through cURL
   *
   * @param unknown_type $path
   */
  private function createBuffer($path) {
    if ($this->buffer) {
      return;
    }

    //echo "[NTLMStream::createBuffer] create buffer from : $path\n";
    $this->ch = curl_init($path);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
    curl_setopt($this->ch, CURLOPT_USERPWD, $this->user . ':' . $this->password);
    echo $this->buffer = curl_exec($this->ch);

    //echo "[NTLMStream::createBuffer] buffer size : " . strlen($this->buffer) . "bytes\n";
    $this->pos = 0;
  }

}

class NTLMSoapClient extends SoapClient {

  function __doRequest($request, $location, $action, $version) {

    $headers = array(
        'Method: POST',
        'Connection: Keep-Alive',
        'User-Agent: PHP-SOAP-CURL',
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "' . $action . '"',
    );

    $this->__last_request_headers = $headers;
    $ch = curl_init($location);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
    curl_setopt($ch, CURLOPT_USERPWD, $this->user . ':' . $this->password);
    $response = curl_exec($ch);

    return $response;
  }

  function __getLastRequestHeaders() {
    return implode("\n", $this->__last_request_headers) . "\n";
  }

}

class PMServiceNTLMSoapClient extends NTLMSoapClient {

  protected $user;
  protected $password;

}

class PMServiceProviderNTLMStream extends soapNtlm {

  protected $user;
  protected $password;

}