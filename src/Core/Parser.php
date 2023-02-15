<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FnacSdk\Core;

/**
 * Class Parser to parse
 */
class Parser // Parser class
{
    /**
     * @var \DOMDocument|null
     */
    public $dom = null;

    /**
     * @var \DOMDocument
     */
    public $currentDom;

    /**
     * @var array
     */
    public $content = [];

    /**
     * @var boolean
     */
    public $errorHandlerIsActive = 0;

    /**
     * Public function Construct
     */
    public function __construct() {
        $this->dom = new \DOMDocument();
        $this->currentDom = $this->dom;
        return $this;
    }

    /**
     * Initializes error handler
     *
     * @return void
     */
    public function initErrorHandler()
    {
        $this->errorHandlerIsActive = true;
    }

    /**
     * @return \DOMDocument|null
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @param \DOMDocument $node
     * @return $this
     */
    protected function _setCurrentDom($node)
    {
        $this->currentDom = $node;
        return $this;
    }

    /**
     * @return \DOMDocument
     */
    protected function _getCurrentDom()
    {
        return $this->currentDom;
    }

    /**
     * Public function XmlToArray
     *
     * @return array
     */
    public function xmlToArray()
    {
        $this->content = $this->_xmlToArray();
        return $this->content;
    }

    /**
     * Function _xmlToArray
     *
     * @param bool $currentNode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _xmlToArray($currentNode = 0)
    {
        if (!$currentNode) {
            $currentNode = $this->getDom();
        }
        $content = '';
        foreach ($currentNode->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $content = $content ?: [];

                    $value = null;
                    if ($node->hasChildNodes()) {
                        $value = $this->_xmlToArray($node);
                    }
                    $attributes = [];
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attribute) {
                            $attributes += [$attribute->name => $attribute->value];
                        }
                        $value = ['_value' => $value, '_attribute' => $attributes];
                    }
                    if (isset($content[$node->nodeName])) {
                        if (!isset($content[$node->nodeName][0]) || !is_array($content[$node->nodeName][0])) {
                            $oldValue = $content[$node->nodeName];
                            $content[$node->nodeName] = [];
                            $content[$node->nodeName][] = $oldValue;
                        }
                        $content[$node->nodeName][] = $value;
                    } else {
                        $content[$node->nodeName] = $value;
                    }
                    break;
                case XML_CDATA_SECTION_NODE:
                    $content = $node->nodeValue;
                    break;
                case XML_TEXT_NODE:
                    if (trim($node->nodeValue) !== '') {
                        $content = $node->nodeValue;
                    }
                    break;
            }
        }
        return $content;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function load($file)
    {
        $this->getDom()->load($file);
        return $this;
    }

    /**
     * @param string $string
     * @return $this
     * @throws \Exception
     */
    public function loadXML($string)
    {
        if ($this->errorHandlerIsActive) {
            set_error_handler([$this, 'errorHandler']);
        }

        try {
            $this->getDom()->loadXML($string);
        } catch (\Exception $e) {
            restore_error_handler();
        }

        if ($this->errorHandlerIsActive) {
            restore_error_handler();
        }

        return $this;
    }

    /**
     * Custom XML lib error handler
     *
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param int $errorLine
     * @throws \Exception
     * @return void
     */
    public function errorHandler($errorNo, $errorStr, $errorFile, $errorLine)
    {
        if ($errorNo != 0) {
            $message = "{$errorStr} in {$errorFile} on line {$errorLine}";
            throw new \Exception ($message);
        }
    }
}

