<?php
class mini_http_response extends mini_base_component
{
    /**
     * Body content
     * @var array
     */
    protected $body = array();

    /**
     * Exception stack
     * @var Exception
     */
    protected $exceptions = array();

    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     * @var array
     */
    protected $headers = array();

    /**
     * Array of raw headers. Each header is a single string, the entire header to emit
     * @var array
     */
    protected $headersRaw = array();

    /**
     * HTTP response code to use in headers
     * @var int
     */
    protected $httpResponseCode = 200;

    /**
     * Flag; is this response a redirect?
     * @var boolean
     */
    protected $isRedirect = false;

    /**
     * Whether or not to render exceptions; off by default
     * @var boolean
     */
    protected $renderExceptions = false;

    /**
     * Flag; if true, when header operations are called after headers have been
     * sent, an exception will be raised; otherwise, processing will continue
     * as normal. Defaults to true.
     *
     * @see canSendHeaders()
     * @var boolean
     */
    public $headersSentThrowsException = true;
    public function init(){ }
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return mini_http_response
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->canSendHeaders(true);
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->headers[$key]);
                }
            }
        }

        $this->headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );

        return $this;
    }

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return mini_http_response
     */
    public function setRedirect($url, $code = 302)
    {
        $this->canSendHeaders(true);
        $this->setHeader('Location', $url, true)
             ->setHttpResponseCode($code);

        return $this;
    }
    /**
     * Is this a redirect?
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return $this->isRedirect;
    }

    /**
     * Return array of headers; see {@link $headers} for format
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Clear headers
     *
     * @return mini_http_response
     */
    public function clearHeaders()
    {
        $this->headers = array();

        return $this;
    }

    /**
     * Clears the specified HTTP header
     *
     * @param  string $name
     * @return mini_http_response
     */
    public function clearHeader($name)
    {
        if (! count($this->headers)) {
            return $this;
        }

        foreach ($this->headers as $index => $header) {
            if ($name == $header['name']) {
                unset($this->headers[$index]);
            }
        }

        return $this;
    }

    /**
     * Set raw HTTP header
     *
     * Allows setting non key => value headers, such as status codes
     *
     * @param string $value
     * @return mini_http_response
     */
    public function setRawHeader($value)
    {
        $this->canSendHeaders(true);
        if ('Location' == substr($value, 0, 8)) {
            $this->isRedirect = true;
        }
        $this->headersRaw[] = (string) $value;
        return $this;
    }

    /**
     * Retrieve all {@link setRawHeader() raw HTTP headers}
     *
     * @return array
     */
    public function getRawHeaders()
    {
        return $this->headersRaw;
    }

    /**
     * Clear all {@link setRawHeader() raw HTTP headers}
     *
     * @return mini_http_response
     */
    public function clearRawHeaders()
    {
        $this->headersRaw = array();
        return $this;
    }

    /**
     * Clears the specified raw HTTP header
     *
     * @param  string $headerRaw
     * @return mini_http_response
     */
    public function clearRawHeader($headerRaw)
    {
        if (! count($this->headersRaw)) {
            return $this;
        }

        $key = array_search($headerRaw, $this->headersRaw);
        if ($key !== false) {
            unset($this->headersRaw[$key]);
        }

        return $this;
    }

    /**
     * Clear all headers, normal and raw
     *
     * @return mini_http_response
     */
    public function clearAllHeaders()
    {
        return $this->clearHeaders()
                    ->clearRawHeaders();
    }

    /**
     * Set HTTP response code to use with headers
     *
     * @param int $code
     * @return mini_http_response
     */
    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new Exception('Invalid HTTP response code');
        }

        if ((300 <= $code) && (307 >= $code)) {
            $this->isRedirect = true;
        } else {
            $this->isRedirect = false;
        }

        $this->httpResponseCode = $code;
        return $this;
    }

    /**
     * Retrieve HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * Can we send headers?
     *
     * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
     * @return boolean
     * @throws Zend_Controller_Response_Exception
     */
    public function canSendHeaders($throw = false)
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw && $this->headersSentThrowsException) {
            Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }

        return !$ok;
    }

    /**
     * Send all headers
     *
     * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     *
     * @return mini_http_response
     */
    public function sendHeaders()
    {
        // Only check if we can send headers if we have headers to send
        if (count($this->headersRaw) || count($this->headers) || (200 != $this->httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->httpResponseCode) {
            // Haven't changed the response code, and we have no headers
            return $this;
        }

        $httpCodeSent = false;

        foreach ($this->headersRaw as $header) {
            if (!$httpCodeSent && $this->httpResponseCode) {
                header($header, true, $this->httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header);
            }
        }

        foreach ($this->headers as $header) {
            if (!$httpCodeSent && $this->httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }

        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->httpResponseCode);
            $httpCodeSent = true;
        }

        return $this;
    }

    /**
     * Set body content
     *
     * If $name is not passed, or is not a string, resets the entire body and
     * sets the 'default' key to $content.
     *
     * If $name is a string, sets the named segment in the body array to
     * $content.
     *
     * @param string $content
     * @param null|string $name
     * @return mini_http_response
     */
    public function setBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            $this->body = array('default' => (string) $content);
        } else {
            $this->body[$name] = (string) $content;
        }
        return $this;
    }

    /**
     * Append content to the body content
     *
     * @param string $content
     * @param null|string $name
     * @return mini_http_response
     */
    public function appendBody($content, $name = null)
    {
        if ((null === $name) || !is_string($name)) {
            if (isset($this->body['default'])) {
                $this->body['default'] .= (string) $content;
            } else {
                return $this->append('default', $content);
            }
        } elseif (isset($this->body[$name])) {
            $this->body[$name] .= (string) $content;
        } else {
            return $this->append($name, $content);
        }

        return $this;
    }

    /**
     * Clear body array
     *
     * With no arguments, clears the entire body array. Given a $name, clears
     * just that named segment; if no segment matching $name exists, returns
     * false to indicate an error.
     *
     * @param  string $name Named segment to clear
     * @return boolean
     */
    public function clearBody($name = null)
    {
        if (null !== $name) {
            $name = (string) $name;
            if (isset($this->body[$name])) {
                unset($this->body[$name]);
                return true;
            }

            return false;
        }

        $this->body = array();
        return true;
    }

    /**
     * Return the body content
     *
     * If $spec is false, returns the concatenated values of the body content
     * array. If $spec is boolean true, returns the body content array. If
     * $spec is a string and matches a named segment, returns the contents of
     * that segment; otherwise, returns null.
     *
     * @param boolean $spec
     * @return string|array|null
     */
    public function getBody($spec = false)
    {
        if (false === $spec) {
            ob_start();
            $this->outputBody();
            return ob_get_clean();
        } elseif (true === $spec) {
            return $this->body;
        } elseif (is_string($spec) && isset($this->body[$spec])) {
            return $this->body[$spec];
        }

        return null;
    }

    /**
     * Append a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at end of
     * array.
     *
     * @param string $name
     * @param string $content
     * @return mini_http_response
     */
    public function append($name, $content)
    {
        if (!is_string($name)) {
            Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }
        $this->body[$name] = (string) $content;
        return $this;
    }

    /**
     * Prepend a named body segment to the body content array
     *
     * If segment already exists, replaces with $content and places at top of
     * array.
     *
     * @param string $name
     * @param string $content
     * @return void
     */
    public function prepend($name, $content)
    {
        if (!is_string($name)) {
            Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }

        $new = array($name => (string) $content);
        $this->body = $new + $this->body;

        return $this;
    }

    /**
     * Insert a named segment into the body content array
     *
     * @param  string $name
     * @param  string $content
     * @param  string $parent
     * @param  boolean $before Whether to insert the new segment before or
     * after the parent. Defaults to false (after)
     * @return mini_http_response
     */
    public function insert($name, $content, $parent = null, $before = false)
    {
        if (!is_string($name)) {
            Exception('Invalid body segment key ("' . gettype($name) . '")');
        }

        if ((null !== $parent) && !is_string($parent)) {
            Exception('Invalid body segment parent key ("' . gettype($parent) . '")');
        }

        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }

        if ((null === $parent) || !isset($this->body[$parent])) {
            return $this->append($name, $content);
        }

        $ins  = array($name => (string) $content);
        $keys = array_keys($this->body);
        $loc  = array_search($parent, $keys);
        if (!$before) {
            // Increment location if not inserting before
            ++$loc;
        }

        if (0 === $loc) {
            // If location of key is 0, we're prepending
            $this->body = $ins + $this->body;
        } elseif ($loc >= (count($this->body))) {
            // If location of key is maximal, we're appending
            $this->body = $this->body + $ins;
        } else {
            // Otherwise, insert at location specified
            $pre  = array_slice($this->body, 0, $loc);
            $post = array_slice($this->body, $loc);
            $this->body = $pre + $ins + $post;
        }

        return $this;
    }

    /**
     * Echo the body segments
     *
     * @return void
     */
    public function outputBody()
    {
        $body = implode('', $this->body);
        echo $body;
    }

    /**
     * Register an exception with the response
     *
     * @param Exception $e
     * @return mini_http_response
     */
    public function setException(Exception $e)
    {
        $this->exceptions[] = $e;
        return $this;
    }

    /**
     * Retrieve the exception stack
     *
     * @return array
     */
    public function getException()
    {
        return $this->exceptions;
    }

    /**
     * Has an exception been registered with the response?
     *
     * @return boolean
     */
    public function isException()
    {
        return !empty($this->exceptions);
    }

    /**
     * Does the response object contain an exception of a given type?
     *
     * @param  string $type
     * @return boolean
     */
    public function hasExceptionOfType($type)
    {
        foreach ($this->exceptions as $e) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does the response object contain an exception with a given message?
     *
     * @param  string $message
     * @return boolean
     */
    public function hasExceptionOfMessage($message)
    {
        foreach ($this->exceptions as $e) {
            if ($message == $e->getMessage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does the response object contain an exception with a given code?
     *
     * @param  int $code
     * @return boolean
     */
    public function hasExceptionOfCode($code)
    {
        $code = (int) $code;
        foreach ($this->exceptions as $e) {
            if ($code == $e->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve all exceptions of a given type
     *
     * @param  string $type
     * @return false|array
     */
    public function getExceptionByType($type)
    {
        $exceptions = array();
        foreach ($this->exceptions as $e) {
            if ($e instanceof $type) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * Retrieve all exceptions of a given message
     *
     * @param  string $message
     * @return false|array
     */
    public function getExceptionByMessage($message)
    {
        $exceptions = array();
        foreach ($this->exceptions as $e) {
            if ($message == $e->getMessage()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * Retrieve all exceptions of a given code
     *
     * @param mixed $code
     * @return void
     */
    public function getExceptionByCode($code)
    {
        $code       = (int) $code;
        $exceptions = array();
        foreach ($this->exceptions as $e) {
            if ($code == $e->getCode()) {
                $exceptions[] = $e;
            }
        }

        if (empty($exceptions)) {
            $exceptions = false;
        }

        return $exceptions;
    }

    /**
     * Whether or not to render exceptions (off by default)
     *
     * If called with no arguments or a null argument, returns the value of the
     * flag; otherwise, sets it and returns the current value.
     *
     * @param boolean $flag Optional
     * @return boolean
     */
    public function renderExceptions($flag = null)
    {
        if (null !== $flag) {
            $this->renderExceptions = $flag ? true : false;
        }

        return $this->renderExceptions;
    }

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested.
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->sendHeaders();

        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= $e->__toString() . "\n";
            }
            echo $exceptions;
            return;
        }

        $this->outputBody();
    }

    /**
     * Magic __toString functionality
     *
     * Proxies to {@link sendResponse()} and returns response value as string
     * using output buffering.
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->sendResponse();
        return ob_get_clean();
    }
    
}
?>