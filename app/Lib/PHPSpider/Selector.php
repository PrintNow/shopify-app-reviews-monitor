<?php
/** @noinspection PhpComposerExtensionStubsInspection */

// +----------------------------------------------------------------------
// | PHPSpider [ A PHP Framework For Crawler ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 https://doc.phpspider.org All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Seatle Yang <seatle@foxmail.com>
// +----------------------------------------------------------------------

//----------------------------------
// PHPSpider选择器类文件
//----------------------------------

namespace App\Lib\PHPSpider;

use DOMXPath;
use DOMDocument;

class Selector
{
    /**
     * 版本号
     *
     * @var string
     */
    const VERSION = '1.0.2';
    public static $dom = null;
    public static $dom_auth = '';
    public static $xpath = null;
    public static $error = null;

    public static function select($html, $selector, $selector_type = 'xpath')
    {
        if (empty($html) || empty($selector)) {
            return false;
        }

        $selector_type = strtolower($selector_type);
        if ($selector_type == 'xpath') {
            return self::_xpath_select($html, $selector);
        } elseif ($selector_type == 'regex') {
            return self::_regex_select($html, $selector);
        } elseif ($selector_type == 'css') {
            return self::_css_select($html, $selector);
        }
    }

    public static function remove($html, $selector, $selector_type = 'xpath')
    {
        if (empty($html) || empty($selector)) {
            return false;
        }

        $remove_html = "";
        $selector_type = strtolower($selector_type);
        if ($selector_type == 'xpath') {
            $remove_html = self::_xpath_select($html, $selector, true);
        } elseif ($selector_type == 'regex') {
            $remove_html = self::_regex_select($html, $selector, true);
        } elseif ($selector_type == 'css') {
            $remove_html = self::_css_select($html, $selector, true);
        }
        $html = str_replace($remove_html, "", $html);
        return $html;
    }

    /**
     * xpath选择器
     *
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author  seatle <seatle@foxmail.com>
     * @created time :2016-10-26 12:53
     */
    private static function _xpath_select($html, $selector, $remove = false)
    {
        if (!is_object(self::$dom)) {
            libxml_use_internal_errors(true);
            self::$dom = new DOMDocument();
        }

        // 如果加载的不是之前的HTML内容，替换一下验证标识
        if (self::$dom_auth != md5($html)) {
            self::$dom_auth = md5($html);
            @self::$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            self::$xpath = new DOMXpath(self::$dom);
        }

        //libxml_use_internal_errors(true);
        //self::$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        //$errors = libxml_get_errors();
        //if (!empty($errors))
        //{
        //print_r($errors);
        //exit;
        //}

        $elements = @self::$xpath->query($selector);
        if ($elements === false) {
            self::$error = "the selector in the xpath(\"{$selector}\") syntax errors";
            // 不应该返回false，因为isset(false)为true，更不能通过 !$values 去判断，因为!0为true，所以这里只能返回null
            //return false;
            return null;
        }

        $result = [];
        if (!is_null($elements)) {
            foreach ($elements as $element) {
                // 如果是删除操作，取一整块代码
                if ($remove) {
                    $content = self::$dom->saveXml($element);
                } else {
                    $nodeName = $element->nodeName;
                    $nodeType = $element->nodeType;     // 1.Element 2.Attribute 3.Text
                    //$nodeAttr = $element->getAttribute('src');
                    //$nodes = util::node_to_array(self::$dom, $element);
                    //echo $nodes['@src']."\n";
                    // 如果是img标签，直接取src值
                    if ($nodeType == 1 && in_array($nodeName, ['img'])) {
                        $content = $element->getAttribute('src');
                    } // 如果是标签属性，直接取节点值
                    elseif ($nodeType == 2 || $nodeType == 3 || $nodeType == 4) {
                        $content = $element->nodeValue;
                    } else {
                        // 保留nodeValue里的html符号，给children二次提取
                        $content = self::$dom->saveXml($element);
                        //$content = trim(self::$dom->saveHtml($element));
                        $content = preg_replace(["#^<{$nodeName}.*>#isU", "#</{$nodeName}>$#isU"], ['', ''], $content);
                    }
                }
                $result[] = $content;
            }
        }
        if (empty($result)) {
            return null;
        }
        // 如果只有一个元素就直接返回string，否则返回数组
        return count($result) > 1 ? $result : $result[0];
    }

    /**
     * css选择器
     *
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author  seatle <seatle@foxmail.com>
     * @created time :2016-10-26 12:53
     */
    private static function _css_select($html, $selector, $remove = false)
    {
        $selector = self::css_to_xpath($selector);
        //echo $selector."\n";
        //exit("\n");
        return self::_xpath_select($html, $selector, $remove);
        // 如果加载的不是之前的HTML内容，替换一下验证标识
        //if (self::$dom_auth['css'] != md5($html))
        //{
        //self::$dom_auth['css'] = md5($html);
        //phpQuery::loadDocumentHTML($html);
        //}
        //if ($remove)
        //{
        //return phpQuery::pq($selector)->remove();
        //}
        //else
        //{
        //return phpQuery::pq($selector)->html();
        //}
    }

    /**
     * 正则选择器
     *
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author  seatle <seatle@foxmail.com>
     * @created time :2016-10-26 12:53
     */
    private static function _regex_select($html, $selector, $remove = false)
    {
        if (@preg_match_all($selector, $html, $out) === false) {
            self::$error = "the selector in the regex(\"{$selector}\") syntax errors";
            return null;
        }
        $count = count($out);
        $result = [];
        // 一个都没有匹配到
        if ($count == 0) {
            return null;
        } // 只匹配一个，就是只有一个 ()
        elseif ($count == 2) {
            // 删除的话取匹配到的所有内容
            if ($remove) {
                $result = $out[0];
            } else {
                $result = $out[1];
            }
        } else {
            for ($i = 1; $i < $count; $i++) {
                // 如果只有一个元素，就直接返回好了
                $result[] = count($out[$i]) > 1 ? $out[$i] : $out[$i][0];
            }
        }
        if (empty($result)) {
            return null;
        }

        return count($result) > 1 ? $result : $result[0];
    }

    public static function find_all($html, $selector)
    {
    }


    public static function css_to_xpath($selectors)
    {
        $queries = self::parse_selector($selectors);
        $delimiter_before = false;
        $xquery = '';
        foreach ($queries as $s) {
            // TAG
            $is_tag = preg_match('@^[\w|\||-]+$@', $s) || $s == '*';
            if ($is_tag) {
                $xquery .= $s;
            } // ID
            elseif ($s[0] == '#') {
                if ($delimiter_before) {
                    $xquery .= '*';
                }
                // ID用精确查询
                $xquery .= "[@id='" . substr($s, 1) . "']";
            } // CLASSES
            elseif ($s[0] == '.') {
                if ($delimiter_before) {
                    $xquery .= '*';
                }
                // CLASS用模糊查询
                $xquery .= "[contains(@class,'" . substr($s, 1) . "')]";
            } // ATTRIBUTES
            elseif ($s[0] == '[') {
                if ($delimiter_before) {
                    $xquery .= '*';
                }
                // strip side brackets
                $attr = trim($s, '][');
                // attr with specifed value
                if (mb_strpos($s, '=')) {
                    $value = null;
                    [$attr, $value] = explode('=', $attr);
                    $value = trim($value, "'\"");
                    if (self::is_regexp($attr)) {
                        // cut regexp character
                        $attr = substr($attr, 0, -1);
                        $xquery .= "[@{$attr}]";
                    } else {
                        $xquery .= "[@{$attr}='{$value}']";
                    }
                } // attr without specified value
                else {
                    $xquery .= "[@{$attr}]";
                }
            } // ~ General Sibling Selector
            elseif ($s[0] == '~') {
            } // + Adjacent sibling selectors
            elseif ($s[0] == '+') {
            } // PSEUDO CLASSES
            elseif ($s[0] == ':') {
            } // DIRECT DESCENDANDS
            elseif ($s == '>') {
                $xquery .= '/';
                $delimiter_before = 2;
            } // ALL DESCENDANDS
            elseif ($s == ' ') {
                $xquery .= '//';
                $delimiter_before = 2;
            } // ERRORS
            else {
                exit("Unrecognized token '$s'");
            }
            $delimiter_before = $delimiter_before === 2;
        }
        return $xquery;
    }

    /**
     * @access private
     */
    public static function parse_selector($query)
    {
        $query = trim(preg_replace('@\s+@', ' ', preg_replace('@\s*(>|\\+|~)\s*@', '\\1', $query)));
        $queries = [];
        if (!$query) {
            return $queries;
        }

        $special_chars = ['>', ' '];
        $special_chars_mapping = [];
        $strlen = mb_strlen($query);
        $class_chars = ['.', '-'];
        $pseudo_chars = ['-'];
        $tag_chars = ['*', '|', '-'];
        // split multibyte string
        // http://code.google.com/p/phpquery/issues/detail?id=76
        $_query = [];
        for ($i = 0; $i < $strlen; $i++) {
            $_query[] = mb_substr($query, $i, 1);
        }
        $query = $_query;
        // it works, but i dont like it...
        $i = 0;
        while ($i < $strlen) {
            $c = $query[$i];
            $tmp = '';
            // TAG
            if (self::is_char($c) || in_array($c, $tag_chars)) {
                while (isset($query[$i]) && (self::is_char($query[$i]) || in_array($query[$i], $tag_chars))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $queries[] = $tmp;
            } // IDs
            elseif ($c == '#') {
                $i++;
                while (isset($query[$i]) && (self::is_char($query[$i]) || $query[$i] == '-')) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $queries[] = '#' . $tmp;
            } // SPECIAL CHARS
            elseif (in_array($c, $special_chars)) {
                $queries[] = $c;
                $i++;
                // MAPPED SPECIAL MULTICHARS
                //			} else if ( $c.$query[$i+1] == '//') {
                //				$return[] = ' ';
                //				$i = $i+2;
            } // MAPPED SPECIAL CHARS
            elseif (isset($special_chars_mapping[$c])) {
                $queries[] = $special_chars_mapping[$c];
                $i++;
            } // COMMA
            elseif ($c == ',') {
                $i++;
                while (isset($query[$i]) && $query[$i] == ' ') {
                    $i++;
                }
            } // CLASSES
            elseif ($c == '.') {
                while (isset($query[$i]) && (self::is_char($query[$i]) || in_array($query[$i], $class_chars))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                $queries[] = $tmp;
            } // ~ General Sibling Selector
            elseif ($c == '~') {
                $space_allowed = true;
                $tmp .= $query[$i++];
                while (isset($query[$i])
                    && (
                        self::is_char($query[$i])
                        || in_array($query[$i], $class_chars)
                        || $query[$i] == '*'
                        || ($query[$i] == ' ' && $space_allowed)
                    )) {
                    if ($query[$i] != ' ') {
                        $space_allowed = false;
                    }
                    $tmp .= $query[$i];
                    $i++;
                }
                $queries[] = $tmp;
            } // + Adjacent sibling selectors
            elseif ($c == '+') {
                $space_allowed = true;
                $tmp .= $query[$i++];
                while (isset($query[$i])
                    && (
                        self::is_char($query[$i])
                        || in_array($query[$i], $class_chars)
                        || $query[$i] == '*'
                        || ($space_allowed && $query[$i] == ' ')
                    )) {
                    if ($query[$i] != ' ') {
                        $space_allowed = false;
                    }
                    $tmp .= $query[$i];
                    $i++;
                }
                $queries[] = $tmp;
            } // ATTRS
            elseif ($c == '[') {
                $stack = 1;
                $tmp .= $c;
                while (isset($query[++$i])) {
                    $tmp .= $query[$i];
                    if ($query[$i] == '[') {
                        $stack++;
                    } elseif ($query[$i] == ']') {
                        $stack--;
                        if (!$stack) {
                            break;
                        }
                    }
                }
                $queries[] = $tmp;
                $i++;
            } // PSEUDO CLASSES
            elseif ($c == ':') {
                $stack = 1;
                $tmp .= $query[$i++];
                while (isset($query[$i]) && (self::is_char($query[$i]) || in_array($query[$i], $pseudo_chars))) {
                    $tmp .= $query[$i];
                    $i++;
                }
                // with arguments ?
                if (isset($query[$i]) && $query[$i] == '(') {
                    $tmp .= $query[$i];
                    $stack = 1;
                    while (isset($query[++$i])) {
                        $tmp .= $query[$i];
                        if ($query[$i] == '(') {
                            $stack++;
                        } elseif ($query[$i] == ')') {
                            $stack--;
                            if (!$stack) {
                                break;
                            }
                        }
                    }
                    $queries[] = $tmp;
                    $i++;
                } else {
                    $queries[] = $tmp;
                }
            } else {
                $i++;
            }
        }

        if (isset($queries[0])) {
            if (isset($queries[0][0]) && $queries[0][0] == ':') {
                array_unshift($queries, '*');
            }
            if ($queries[0] != '>') {
                array_unshift($queries, ' ');
            }
        }

        return $queries;
    }

    public static function is_char($char)
    {
        return preg_match('@\w@', $char);
    }

    /**
     * 模糊匹配
     * ^ 前缀字符串
     * * 包含字符串
     * $ 后缀字符串
     *
     * @access private
     */
    protected static function is_regexp($pattern)
    {
        return in_array(
            $pattern[mb_strlen($pattern) - 1],
            ['^', '*', '$']
        );
    }
}
