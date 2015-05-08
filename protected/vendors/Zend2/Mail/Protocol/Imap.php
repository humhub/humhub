<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol;

use Zend\Stdlib\ErrorHandler;

class Imap
{
    /**
     * Default timeout in seconds for initiating session
     */
    const TIMEOUT_CONNECTION = 30;

    /**
     * socket to imap server
     * @var resource|null
     */
    protected $socket;

    /**
     * counter for request tag
     * @var int
     */
    protected $tagCount = 0;

    /**
     * Public constructor
     *
     * @param  string   $host  hostname or IP address of IMAP server, if given connect() is called
     * @param  int|null $port  port of IMAP server, null for default (143 or 993 for ssl)
     * @param  bool     $ssl   use ssl? 'SSL', 'TLS' or false
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function __construct($host = '', $port = null, $ssl = false)
    {
        if ($host) {
            $this->connect($host, $port, $ssl);
        }
    }

    /**
     * Public destructor
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Open connection to IMAP server
     *
     * @param  string      $host  hostname or IP address of IMAP server
     * @param  int|null    $port  of IMAP server, default is 143 (993 for ssl)
     * @param  string|bool $ssl   use 'SSL', 'TLS' or false
     * @throws Exception\RuntimeException
     * @return string welcome message
     */
    public function connect($host, $port = null, $ssl = false)
    {
        $isTls = false;

        if ($ssl) {
            $ssl = strtolower($ssl);
        }

        switch ($ssl) {
            case 'ssl':
                $host = 'ssl://' . $host;
                if (!$port) {
                    $port = 993;
                }
                break;
            case 'tls':
                $isTls = true;
                // break intentionally omitted
            default:
                if (!$port) {
                    $port = 143;
                }
        }

        ErrorHandler::start();
        $this->socket = fsockopen($host, $port, $errno, $errstr, self::TIMEOUT_CONNECTION);
        $error = ErrorHandler::stop();
        if (!$this->socket) {
            throw new Exception\RuntimeException(sprintf(
                'cannot connect to host %s',
                ($error ? sprintf('; error = %s (errno = %d )', $error->getMessage(), $error->getCode()) : '')
            ), 0, $error);
        }

        if (!$this->_assumedNextLine('* OK')) {
            throw new Exception\RuntimeException('host doesn\'t allow connection');
        }

        if ($isTls) {
            $result = $this->requestAndResponse('STARTTLS');
            $result = $result && stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$result) {
                throw new Exception\RuntimeException('cannot enable TLS');
            }
        }
    }

    /**
     * get the next line from socket with error checking, but nothing else
     *
     * @throws Exception\RuntimeException
     * @return string next line
     */
    protected function _nextLine()
    {
        $line = fgets($this->socket);
        if ($line === false) {
            throw new Exception\RuntimeException('cannot read - connection closed?');
        }

        return $line;
    }

    /**
     * get next line and assume it starts with $start. some requests give a simple
     * feedback so we can quickly check if we can go on.
     *
     * @param  string $start the first bytes we assume to be in the next line
     * @return bool line starts with $start
     */
    protected function _assumedNextLine($start)
    {
        $line = $this->_nextLine();
        return strpos($line, $start) === 0;
    }

    /**
     * get next line and split the tag. that's the normal case for a response line
     *
     * @param  string $tag tag of line is returned by reference
     * @return string next line
     */
    protected function _nextTaggedLine(&$tag)
    {
        $line = $this->_nextLine();

        // separate tag from line
        list($tag, $line) = explode(' ', $line, 2);

        return $line;
    }

    /**
     * split a given line in tokens. a token is literal of any form or a list
     *
     * @param  string $line line to decode
     * @return array tokens, literals are returned as string, lists as array
     */
    protected function _decodeLine($line)
    {
        $tokens = array();
        $stack = array();

        /*
            We start to decode the response here. The understood tokens are:
                literal
                "literal" or also "lit\\er\"al"
                {bytes}<NL>literal
                (literals*)
            All tokens are returned in an array. Literals in braces (the last understood
            token in the list) are returned as an array of tokens. I.e. the following response:
                "foo" baz {3}<NL>bar ("f\\\"oo" bar)
            would be returned as:
                array('foo', 'baz', 'bar', array('f\\\"oo', 'bar'));

            // TODO: add handling of '[' and ']' to parser for easier handling of response text
        */
        //  replace any trailing <NL> including spaces with a single space
        $line = rtrim($line) . ' ';
        while (($pos = strpos($line, ' ')) !== false) {
            $token = substr($line, 0, $pos);
            while ($token[0] == '(') {
                array_push($stack, $tokens);
                $tokens = array();
                $token = substr($token, 1);
            }
            if ($token[0] == '"') {
                if (preg_match('%^\(*"((.|\\\\|\\")*?)" *%', $line, $matches)) {
                    $tokens[] = $matches[1];
                    $line = substr($line, strlen($matches[0]));
                    continue;
                }
            }
            if ($token[0] == '{') {
                $endPos = strpos($token, '}');
                $chars = substr($token, 1, $endPos - 1);
                if (is_numeric($chars)) {
                    $token = '';
                    while (strlen($token) < $chars) {
                        $token .= $this->_nextLine();
                    }
                    $line = '';
                    if (strlen($token) > $chars) {
                        $line = substr($token, $chars);
                        $token = substr($token, 0, $chars);
                    } else {
                        $line .= $this->_nextLine();
                    }
                    $tokens[] = $token;
                    $line = trim($line) . ' ';
                    continue;
                }
            }
            if ($stack && $token[strlen($token) - 1] == ')') {
                // closing braces are not separated by spaces, so we need to count them
                $braces = strlen($token);
                $token = rtrim($token, ')');
                // only count braces if more than one
                $braces -= strlen($token) + 1;
                // only add if token had more than just closing braces
                if (rtrim($token) != '') {
                    $tokens[] = rtrim($token);
                }
                $token = $tokens;
                $tokens = array_pop($stack);
                // special handline if more than one closing brace
                while ($braces-- > 0) {
                    $tokens[] = $token;
                    $token = $tokens;
                    $tokens = array_pop($stack);
                }
            }
            $tokens[] = $token;
            $line = substr($line, $pos + 1);
        }

        // maybe the server forgot to send some closing braces
        while ($stack) {
            $child = $tokens;
            $tokens = array_pop($stack);
            $tokens[] = $child;
        }

        return $tokens;
    }

    /**
     * read a response "line" (could also be more than one real line if response has {..}<NL>)
     * and do a simple decode
     *
     * @param  array|string  $tokens    decoded tokens are returned by reference, if $dontParse
     *                                  is true the unparsed line is returned here
     * @param  string        $wantedTag check for this tag for response code. Default '*' is
     *                                  continuation tag.
     * @param  bool          $dontParse if true only the unparsed line is returned $tokens
     * @return bool if returned tag matches wanted tag
     */
    public function readLine(&$tokens = array(), $wantedTag = '*', $dontParse = false)
    {
        $tag  = null;                         // define $tag variable before first use
        $line = $this->_nextTaggedLine($tag); // get next tag
        if (!$dontParse) {
            $tokens = $this->_decodeLine($line);
        } else {
            $tokens = $line;
        }

        // if tag is wanted tag we might be at the end of a multiline response
        return $tag == $wantedTag;
    }

    /**
     * read all lines of response until given tag is found (last line of response)
     *
     * @param  string       $tag       the tag of your request
     * @param  bool         $dontParse if true every line is returned unparsed instead of
     *                                 the decoded tokens
     * @return null|bool|array tokens if success, false if error, null if bad request
     */
    public function readResponse($tag, $dontParse = false)
    {
        $lines = array();
        $tokens = null; // define $tokens variable before first use
        while (!$this->readLine($tokens, $tag, $dontParse)) {
            $lines[] = $tokens;
        }

        if ($dontParse) {
            // last to chars are still needed for response code
            $tokens = array(substr($tokens, 0, 2));
        }
        // last line has response code
        if ($tokens[0] == 'OK') {
            return $lines ? $lines : true;
        } elseif ($tokens[0] == 'NO') {
            return false;
        }
        return null;
    }

    /**
     * send a request
     *
     * @param  string $command your request command
     * @param  array  $tokens  additional parameters to command, use escapeString() to prepare
     * @param  string $tag     provide a tag otherwise an autogenerated is returned
     * @throws Exception\RuntimeException
     */
    public function sendRequest($command, $tokens = array(), &$tag = null)
    {
        if (!$tag) {
            ++$this->tagCount;
            $tag = 'TAG' . $this->tagCount;
        }

        $line = $tag . ' ' . $command;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (fwrite($this->socket, $line . ' ' . $token[0] . "\r\n") === false) {
                    throw new Exception\RuntimeException('cannot write - connection closed?');
                }
                if (!$this->_assumedNextLine('+ ')) {
                    throw new Exception\RuntimeException('cannot send literal string');
                }
                $line = $token[1];
            } else {
                $line .= ' ' . $token;
            }
        }

        if (fwrite($this->socket, $line . "\r\n") === false) {
            throw new Exception\RuntimeException('cannot write - connection closed?');
        }
    }

    /**
     * send a request and get response at once
     *
     * @param  string $command   command as in sendRequest()
     * @param  array  $tokens    parameters as in sendRequest()
     * @param  bool   $dontParse if true unparsed lines are returned instead of tokens
     * @return mixed response as in readResponse()
     */
    public function requestAndResponse($command, $tokens = array(), $dontParse = false)
    {
        $tag = null; // define $tag variable before first use
        $this->sendRequest($command, $tokens, $tag);
        $response = $this->readResponse($tag, $dontParse);

        return $response;
    }

    /**
     * escape one or more literals i.e. for sendRequest
     *
     * @param  string|array $string the literal/-s
     * @return string|array escape literals, literals with newline ar returned
     *                      as array('{size}', 'string');
     */
    public function escapeString($string)
    {
        if (func_num_args() < 2) {
            if (strpos($string, "\n") !== false) {
                return array('{' . strlen($string) . '}', $string);
            } else {
                return '"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $string) . '"';
            }
        }
        $result = array();
        foreach (func_get_args() as $string) {
            $result[] = $this->escapeString($string);
        }
        return $result;
    }

    /**
     * escape a list with literals or lists
     *
     * @param  array $list list with literals or lists as PHP array
     * @return string escaped list for imap
     */
    public function escapeList($list)
    {
        $result = array();
        foreach ($list as $v) {
            if (!is_array($v)) {
//              $result[] = $this->escapeString($v);
                $result[] = $v;
                continue;
            }
            $result[] = $this->escapeList($v);
        }
        return '(' . implode(' ', $result) . ')';
    }

    /**
     * Login to IMAP server.
     *
     * @param  string $user      username
     * @param  string $password  password
     * @return bool success
     */
    public function login($user, $password)
    {
        return $this->requestAndResponse('LOGIN', $this->escapeString($user, $password), true);
    }

    /**
     * logout of imap server
     *
     * @return bool success
     */
    public function logout()
    {
        $result = false;
        if ($this->socket) {
            try {
                $result = $this->requestAndResponse('LOGOUT', array(), true);
            } catch (Exception\ExceptionInterface $e) {
                // ignoring exception
            }
            fclose($this->socket);
            $this->socket = null;
        }
        return $result;
    }


    /**
     * Get capabilities from IMAP server
     *
     * @return array list of capabilities
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function capability()
    {
        $response = $this->requestAndResponse('CAPABILITY');

        if (!$response) {
            return $response;
        }

        $capabilities = array();
        foreach ($response as $line) {
            $capabilities = array_merge($capabilities, $line);
        }
        return $capabilities;
    }

    /**
     * Examine and select have the same response. The common code for both
     * is in this method
     *
     * @param  string $command can be 'EXAMINE' or 'SELECT' and this is used as command
     * @param  string $box which folder to change to or examine
     * @return bool|array false if error, array with returned information
     *                    otherwise (flags, exists, recent, uidvalidity)
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function examineOrSelect($command = 'EXAMINE', $box = 'INBOX')
    {
        $tag = null; // define $tag variable before first use
        $this->sendRequest($command, array($this->escapeString($box)), $tag);

        $result = array();
        $tokens = null; // define $tokens variable before first use
        while (!$this->readLine($tokens, $tag)) {
            if ($tokens[0] == 'FLAGS') {
                array_shift($tokens);
                $result['flags'] = $tokens;
                continue;
            }
            switch ($tokens[1]) {
                case 'EXISTS':
                case 'RECENT':
                    $result[strtolower($tokens[1])] = $tokens[0];
                    break;
                case '[UIDVALIDITY':
                    $result['uidvalidity'] = (int) $tokens[2];
                    break;
                default:
                    // ignore
            }
        }

        if ($tokens[0] != 'OK') {
            return false;
        }
        return $result;
    }

    /**
     * change folder
     *
     * @param  string $box change to this folder
     * @return bool|array see examineOrselect()
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function select($box = 'INBOX')
    {
        return $this->examineOrSelect('SELECT', $box);
    }

    /**
     * examine folder
     *
     * @param  string $box examine this folder
     * @return bool|array see examineOrselect()
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function examine($box = 'INBOX')
    {
        return $this->examineOrSelect('EXAMINE', $box);
    }

    /**
     * fetch one or more items of one or more messages
     *
     * @param  string|array $items items to fetch from message(s) as string (if only one item)
     *                             or array of strings
     * @param  int|array    $from  message for items or start message if $to !== null
     * @param  int|null     $to    if null only one message ($from) is fetched, else it's the
     *                             last message, INF means last message available
     * @throws Exception\RuntimeException
     * @return string|array if only one item of one message is fetched it's returned as string
     *                      if items of one message are fetched it's returned as (name => value)
     *                      if one items of messages are fetched it's returned as (msgno => value)
     *                      if items of messages are fetched it's returned as (msgno => (name => value))
     */
    public function fetch($items, $from, $to = null)
    {
        if (is_array($from)) {
            $set = implode(',', $from);
        } elseif ($to === null) {
            $set = (int) $from;
        } elseif ($to === INF) {
            $set = (int) $from . ':*';
        } else {
            $set = (int) $from . ':' . (int) $to;
        }

        $items = (array) $items;
        $itemList = $this->escapeList($items);

        $tag = null;  // define $tag variable before first use
        $this->sendRequest('FETCH', array($set, $itemList), $tag);

        $result = array();
        $tokens = null; // define $tokens variable before first use
        while (!$this->readLine($tokens, $tag)) {
            // ignore other responses
            if ($tokens[1] != 'FETCH') {
                continue;
            }
            // ignore other messages
            if ($to === null && !is_array($from) && $tokens[0] != $from) {
                continue;
            }
            // if we only want one item we return that one directly
            if (count($items) == 1) {
                if ($tokens[2][0] == $items[0]) {
                    $data = $tokens[2][1];
                } else {
                    // maybe the server send an other field we didn't wanted
                    $count = count($tokens[2]);
                    // we start with 2, because 0 was already checked
                    for ($i = 2; $i < $count; $i += 2) {
                        if ($tokens[2][$i] != $items[0]) {
                            continue;
                        }
                        $data = $tokens[2][$i + 1];
                        break;
                    }
                }
            } else {
                $data = array();
                while (key($tokens[2]) !== null) {
                    $data[current($tokens[2])] = next($tokens[2]);
                    next($tokens[2]);
                }
            }
            // if we want only one message we can ignore everything else and just return
            if ($to === null && !is_array($from) && $tokens[0] == $from) {
                // we still need to read all lines
                while (!$this->readLine($tokens, $tag));
                return $data;
            }
            $result[$tokens[0]] = $data;
        }

        if ($to === null && !is_array($from)) {
            throw new Exception\RuntimeException('the single id was not found in response');
        }

        return $result;
    }

    /**
     * get mailbox list
     *
     * this method can't be named after the IMAP command 'LIST', as list is a reserved keyword
     *
     * @param  string $reference mailbox reference for list
     * @param  string $mailbox   mailbox name match with wildcards
     * @return array mailboxes that matched $mailbox as array(globalName => array('delim' => .., 'flags' => ..))
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function listMailbox($reference = '', $mailbox = '*')
    {
        $result = array();
        $list = $this->requestAndResponse('LIST', $this->escapeString($reference, $mailbox));
        if (!$list || $list === true) {
            return $result;
        }

        foreach ($list as $item) {
            if (count($item) != 4 || $item[0] != 'LIST') {
                continue;
            }
            $result[$item[3]] = array('delim' => $item[2], 'flags' => $item[1]);
        }

        return $result;
    }

    /**
     * set flags
     *
     * @param  array       $flags  flags to set, add or remove - see $mode
     * @param  int         $from   message for items or start message if $to !== null
     * @param  int|null    $to     if null only one message ($from) is fetched, else it's the
     *                             last message, INF means last message available
     * @param  string|null $mode   '+' to add flags, '-' to remove flags, everything else sets the flags as given
     * @param  bool        $silent if false the return values are the new flags for the wanted messages
     * @return bool|array new flags if $silent is false, else true or false depending on success
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function store(array $flags, $from, $to = null, $mode = null, $silent = true)
    {
        $item = 'FLAGS';
        if ($mode == '+' || $mode == '-') {
            $item = $mode . $item;
        }
        if ($silent) {
            $item .= '.SILENT';
        }

        $flags = $this->escapeList($flags);
        $set = (int) $from;
        if ($to != null) {
            $set .= ':' . ($to == INF ? '*' : (int) $to);
        }

        $result = $this->requestAndResponse('STORE', array($set, $item, $flags), $silent);

        if ($silent) {
            return $result ? true : false;
        }

        $tokens = $result;
        $result = array();
        foreach ($tokens as $token) {
            if ($token[1] != 'FETCH' || $token[2][0] != 'FLAGS') {
                continue;
            }
            $result[$token[0]] = $token[2][1];
        }

        return $result;
    }

    /**
     * append a new message to given folder
     *
     * @param string $folder  name of target folder
     * @param string $message full message content
     * @param array  $flags   flags for new message
     * @param string $date    date for new message
     * @return bool success
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     */
    public function append($folder, $message, $flags = null, $date = null)
    {
        $tokens = array();
        $tokens[] = $this->escapeString($folder);
        if ($flags !== null) {
            $tokens[] = $this->escapeList($flags);
        }
        if ($date !== null) {
            $tokens[] = $this->escapeString($date);
        }
        $tokens[] = $this->escapeString($message);

        return $this->requestAndResponse('APPEND', $tokens, true);
    }

    /**
     * copy message set from current folder to other folder
     *
     * @param string   $folder destination folder
     * @param $from
     * @param int|null $to     if null only one message ($from) is fetched, else it's the
     *                         last message, INF means last message available
     * @return bool success
     */
    public function copy($folder, $from, $to = null)
    {
        $set = (int) $from;
        if ($to != null) {
            $set .= ':' . ($to == INF ? '*' : (int) $to);
        }

        return $this->requestAndResponse('COPY', array($set, $this->escapeString($folder)), true);
    }

    /**
     * create a new folder (and parent folders if needed)
     *
     * @param string $folder folder name
     * @return bool success
     */
    public function create($folder)
    {
        return $this->requestAndResponse('CREATE', array($this->escapeString($folder)), true);
    }

    /**
     * rename an existing folder
     *
     * @param string $old old name
     * @param string $new new name
     * @return bool success
     */
    public function rename($old, $new)
    {
        return $this->requestAndResponse('RENAME', $this->escapeString($old, $new), true);
    }

    /**
     * remove a folder
     *
     * @param string $folder folder name
     * @return bool success
     */
    public function delete($folder)
    {
        return $this->requestAndResponse('DELETE', array($this->escapeString($folder)), true);
    }

    /**
     * permanently remove messages
     *
     * @return bool success
     */
    public function expunge()
    {
        // TODO: parse response?
        return $this->requestAndResponse('EXPUNGE');
    }

    /**
     * send noop
     *
     * @return bool success
     */
    public function noop()
    {
        // TODO: parse response
        return $this->requestAndResponse('NOOP');
    }

    /**
     * do a search request
     *
     * This method is currently marked as internal as the API might change and is not
     * safe if you don't take precautions.
     *
     * @param array $params
     * @return array message ids
     */
    public function search(array $params)
    {
        $response = $this->requestAndResponse('SEARCH', $params);
        if (!$response) {
            return $response;
        }

        foreach ($response as $ids) {
            if ($ids[0] == 'SEARCH') {
                array_shift($ids);
                return $ids;
            }
        }
        return array();
    }
}
