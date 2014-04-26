<?php

/**
 * VendAPI
 *
 * An api for communicating with vend pos software - http://www.vendhq.com
 *
 * Requires php 5.3
 *
 * @package    VendAPI
 * @author     Bruce Aldridge <bruce@incode.co.nz>
 * @copyright  2012-2013 Bruce Aldridge
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @link       https://github.com/brucealdridge/vendapi
 */

namespace VendAPI;

class VendRequest
{
    private $url;
    private $curl;
    private $curl_debug;
    private $debug;
    private $cookie;
    private $http_header;
    private $http_body;

    /**
     * set to false to stop cURL from verifying the peer's certificate
     * @var boolean
     */
    protected $verifyPeer = true;

    /**
     * set to 1 to check the existence of a common name in the SSL peer
     * certificate
     * set to 2 to check the existence of a common name and also verify
     * that it matches the hostname provided.
     * In production environments the value of this option should
     * be kept at 2 (default value).
     * @var integer
     */
    protected $verifyHost = 2;

    /**
     * The name of a file holding one or more certificates to verify
     * the peer with. This only makes sense when used in combination
     * with CURLOPT_SSL_VERIFYPEER
     * @var string
     */
    protected $certificatePath;

    public function __construct($url, $username, $password, $options = array()
    {

        $this->curl = curl_init();

        // trim trailing slash for niceness
        $this->url = rtrim($url, '/');

        if (array_key_exists('CURLOPT_SSL_VERIFYHOST', $options)) {
            $this->verifyHost = $options['CURLOPT_SSL_VERIFYHOST'];
        }

        if (array_key_exists('CURLOPT_SSL_VERIFYPEER', $options)) {
            $this->verifyPeer = $options['CURLOPT_SSL_VERIFYPEER'];
        }

        // setup default curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => $username.':'.$password,
            CURLOPT_HTTPHEADER,array('Accept: application/json','Content-Type: application/json'),
            CURLOPT_HEADER => 1,
            CURLOPT_USERAGENT => 'brucealdridge/VendAPI',
            CURLOPT_SSL_VERIFYHOST => $this->verifyHost
        );

        $this->setOpt($options);

        if ($this->verifyPeer === false) {
            $this->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        } else {

            // @see http://curl.haxx.se/docs/caextract.html

            if (!file_exists($this->certificatePath)) {
                throw new \RuntimeException('cacert.pem file not found');
            }

            $this->setOpt(CURLOPT_CAINFO, $this->certificatePath);

        }

    }
    public function __destruct()
    {
        // close curl nicely
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * set option for request, also accepts an array of key/value pairs for the first param
     * @param string $name  option name to set
     * @param misc $value value
     */
    public function setOpt($name, $value = false)
    {
        if (is_array($name)) {
            curl_setopt_array($this->curl, $name);
            return;
        }
        if ($name == 'debug') {
            curl_setopt($this->curl, CURLINFO_HEADER_OUT, (int) $value);
            curl_setopt($this->curl, CURLOPT_VERBOSE, (boolean) $value);
            $this->debug = $value;
        } else {
            curl_setopt($this->curl, $name, $value);
        }
    }
    public function post($path, $rawdata)
    {
        $this->setOpt(
            array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $rawdata,
                CURLOPT_CUSTOMREQUEST => 'POST'
            )
        );
        $this->posted = $rawdata;
        return $this->_request($path, 'post');
    }
    public function get($path)
    {
        $this->setOpt(
            array(
                CURLOPT_HTTPGET => 1,
                CURLOPT_POSTFIELDS => null,
                CURLOPT_CUSTOMREQUEST => 'GET'
            )
        );
        $this->posted = '';
        return $this->_request($path, 'get');
    }
    private function _request($path, $type)
    {
        $this->setOpt(CURLOPT_URL, $this->url.$path);

        $this->response = $response = curl_exec($this->curl);

        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $cookie = strrpos($response, 'Set-Cookie: ');
        $break = strpos($response, "\r\n\r\n", $cookie);

        if ($cookie !== false && $break !== false) {
            $break = $break + strlen("\r\n\r\n");
            $this->http_header = substr($response, 0, $break);
            $this->http_body = substr($response, $break);
        }else{
            $this->http_header = substr($response, 0, $header_size);
            $this->http_body = substr($response, $header_size);
        }
        if (!$this->cookie) {
            if (preg_match_all('/(?:Set-Cookie: )([^; ]*)/', $this->http_header, $cookie)) {
                $this->cookie = $cookie[1][count($cookie[1]) - 1];
                $this->setOpt(CURLOPT_COOKIE, $this->cookie);
            }
        }

        if ($this->debug) {
            $this->curl_debug = curl_getinfo($this->curl);
            $head = $foot = "\n";
            if (php_sapi_name() !== 'cli') {
                $head = '<pre>';
                $foot = '</pre>';
            }
            echo $head.$this->curl_debug['request_header'].$foot.
                 ($this->posted ? $head.$this->posted.$foot : '').
                 $head.$this->http_header.$foot.
                 $head.$this->http_body.$foot;
        }
        return $this->http_body;
    }
}
