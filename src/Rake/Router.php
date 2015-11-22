<?php

namespace Rake;

class Router implements \ArrayAccess
{
    private $_options = [
        'mod_rewrite' => FALSE,
    ];
    private $_root;
    private $_route = '';
    private $_prefix = '';
    private $_query;
    private $_params = [];
    private $_definedLinks = [];
    private $_definedClosures = [];


    public function __construct(array $options = NULL)
    {
        preg_match('#^(.*/)([^/]+\.php)$#', $_SERVER['SCRIPT_NAME'], $m);
        $this->_options = $o = array_merge($this->_options, $options ?: []);
        $this->_root = isset($o['root']) ? $o['root'] : $m[1];
        $this->_prefix = isset($o['prefix']) ? $o['prefix'] : ((FALSE === @$o['mod_rewrite']) ? $m[2] . '/' : '');
        $this->_route = isset($o['route']) ? $o['route'] : trim(@$_SERVER['PATH_INFO'], '/');
        $this->_params = explode('/', $this->_route);
    }


    public function offsetExists($offset)
    {
        return (is_numeric($offset) and $offset = intval($offset) - 1 and isset($this->_params[$offset])) ? TRUE : FALSE;
    }


    public function offsetGet($offset)
    {
        return (is_numeric($offset) and $offset = intval($offset) - 1 and isset($this->_params[$offset])) ? $this->_params[$offset] : NULL;
    }


    public function offsetSet($offset, $value) { return FALSE; }
    public function offsetUnset($offset) { return FALSE; }


    public function getParams()
    {
        return $this->_params;
    }


    public function getRoot($keepPrefix = FALSE, $keepIndex = FALSE)
    {
        return (@$this->_root ?: ($this->relative($this->_route, '.') ?: '.') . '/') . ($keepPrefix ? rtrim(($keepIndex or 'index.php/' != $this->_prefix) ? $this->_prefix : '', '/') : '');
    }


    public function getRoute()
    {
        return $this->_route;
    }


    public function getHome()
    {
        return isset($this->_definedLinks[':home']) ? $this->to($this->_definedLinks[':home']) : ($this->getRoot() . ('index.php/' != $this->_prefix ? rtrim($this->_prefix, '/') : ''));
    }


    public function getSelf()
    {
        return $this->_root . ('index.php/' != $this->_prefix ? rtrim($this->_prefix . $this->_route, '/') : '');
    }


    public function match(...$args)
    {
        // pattern
        $pattern = array_shift($args);
        // masks
        $mask = is_array($args[0]) ? array_shift($args) : [];
        // match
        if ($this->matchPattern($pattern, $mask, $matches)) {
            if (is_callable($args[0]) and FALSE !== ($res = $args[0]($this, $matches))) {
                return $res;
            }
            return TRUE;
        }
    }


    public function redir($link)
    {
        header('Location: ' . $this->to($link));
        exit;
    }


    public function to($link, ...$args)
    {
        // validate link
        while (is_string($link) and ':' == $link{0}) {
            switch ($link) {
                case ':self': return $this->getSelf();
                case ':root': return $this->getRoot();
                case ':home': return $this->getHome();
                default:
                    if (isset($this->_definedLinks[$link])) {
                        $link = $this->_definedLinks[$link];
                    } else {
                        trigger_error(sprintf('Route not defined (%s)', $link));
                        return '#';
                    }
            }
        }

        // chained arguments
        $link = preg_replace_callback('#%(?<from>\d+)(?<sep>.)?\.\.(%?(?<to>\d+))?#', function($m) use ($args) {
            return '%' . join(@$m['sep'] . '%', range($m['from'], @$m['to'] ?: count($args)));
        }, $link);
        
        // match link variables with params
        
        $link = preg_replace_callback('#((?<!\\\)?<keep>@)?({((?<match>[\w\.]+)|%(?<arg>\d+))(=(?<default>\w+))?}|%(?<arg0>\d+))#', function($m) use ($args){
            // argument
            $res = !empty($args) ? $args[(($i = (int)@$m['arg0'] or $i = (int)@$m['arg']) and isset($args[$i - 1])) ? $i - 1 : 0] : '';
            // match
            if (!empty($m['match'])) {
                $_res = $res;
                $_match = explode('.', $m['match']);
                foreach ($_match as $_m) {
                    if (is_array($_res) and isset($_res[$_m])) {
                        $_res = $_res[$_m];
                    } elseif (is_object($_res) and isset($_res->{$_m})) {
                        $_res = $_res->{$_m};
                    } elseif (is_object($_res) and method_exists($_res, 'get' . $_m)) {
                        $_res = $_res->{'get' . $_m}();
                    } else {
                        $_res = '';
                        break;
                    }
                }
                if (is_string($_res) or is_numeric($_res)) {
                    $res = strval($_res);
                } elseif (is_object($_res) and method_exists($_res, '__toString')) {
                    $res = $_res->__toString();
                } else {
                    $res = '';
                }
            }
            // res
            if (is_object($res) and method_exists($res, '__toString')) {
                $res = $res->__toString();
            }
            // default
            if (!empty($m['default']) and $m['default'] == $res) {
                $res = NULL;
            }
            //
            return ((is_string($res) or is_int($res)) and !empty($res)) ? $res : (isset($m['keep']) ? $m['keep'] : '');
        }, $link);
        
        // paste current route params
        if (FALSE !== strpos($link, '@')) {
            $link = explode('/', $link);
            foreach ($link as $i => $_param) {
                if ($_param and '@' == $_param{0}) {
                    $link[$i] = isset($this->_params[$i]) ? $this->_params[$i] : '';
                }
            }
            $link = join('/', $link);
            $link = str_replace('\\@', '@', $link);
        }
        
        //
        
        if ('' == $link or ('/' !== $link{0} and FALSE === strpos($link, '://'))) {
            if (FALSE !== $this->_root) {
                $link = $this->_root . $this->normalize($this->_prefix . rtrim($link, '/'));
            } else {
                $link = $this->relative($this->_route, $this->_prefix . rtrim($link, '/'));
            }
        }
        
        return $link;
    }


    public function normalize($uri)
    {
        $uri = '/' . $uri;
        while (FALSE !== strpos($uri, '/..')) {
            $uri = preg_replace('#/[^/]+/([^/]+\.[^\.]+/)?\.\.#', '', $uri);
        }
        $uri = preg_replace('#/([^/]+\.[^\.]+/)?\.#', '', $uri);
        $uri = str_replace('/./', '/', $uri);
        return substr($uri, 1);
    }


    function relative($from, $link)
    {
        $from = explode('/', $from);
        $link = explode('/', $link);
        array_pop($from);
        while ($link and '.' == $link[0]) {
            array_shift($link);
        }
        while ($from and $link and $from[0] == $link[0]) {
            array_shift($from);
            array_shift($link);
        }
        $link = array_pad($link, -(count($from) + count($link)), '..');
        return join($link, '/');
    }


    protected function matchPattern($pattern, $mask = [], &$matches = [], &$defaults = [])
    {
        // match route
        $pattern = preg_replace('#\)(?!\!)#', ')?', $pattern);
        $pattern = str_replace(')!', ')', $pattern);
        $pattern = str_replace('.', '\.', $pattern);
        $pattern = str_replace('*', '.+', $pattern);
        $pattern = preg_replace_callback('#{(?<name>(\w+))(:(?<pattern>.+)(:(?<length>.+))?)?(=(?<default>.+))?}#U', function($m) use ($mask, &$defaults){
            if (@$m['pattern']) {
                switch ($m['pattern']) {
                    case 'num':      $_pattern = '\d'; break;
                    case 'alpha':    $_pattern = '[A-Za-z\-]'; break;
                    case 'alphanum': $_pattern = '[\w\-]'; break;
                    case 'word':     $_pattern = '[A-Za-z]([\w\-]+)?'; break;
                    case 'hex':      $_pattern = '[0-9A-Za-z]'; break;
                    default:         $_pattern = $m['pattern'];
                }
                $_pattern .= @$m['length'] ? "{{$m['length']}}" : '+';
            } elseif (array_key_exists($m['name'], $mask)) {
                $_pattern = $mask[$m['name']];
            } else {
                $_pattern = '[^/]+';
            }
            if (isset($m['default'])) {
                @$defaults[$m['name']] = $m['default'];
            }
            return "(?<{$m['name']}>{$_pattern})";
        }, $pattern);
        return preg_match("#^{$pattern}$#", $this->_route, $matches) ? TRUE : FALSE;
    }
}