<?php
namespace FnacSdk\Core;

class Generator
{
    /**
     * This value is used to replace numeric keys while formatting data for xml output.
     */
    const DEFAULT_ENTITY_ITEM_NAME = 'itemIndexError';

    /**
     * @var \DOMDocument|null
     */
    protected $_dom;

    /**
     * @var string $_defaultIndexedArrayItemName
     */
    public $_defaultIndexedArrayItemName;

    /**
     * @var \DOMDocument $_currentDom
     */
    public $_currentDom;

    /**
     * Public function construct
     */
    public function __construct() {
        $this->_dom = new \DOMDocument('1.0');
        // DOM element
        $this->_dom->formatOutput = true;
        $this->_currentDom = $this->_dom;
        // return 
        return $this;
    }

    /**
     * Public function GetCurrentDOM
     *
     * @return \DOMDocument
     */
    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    /**
     * Public function GetDOM
     *
     * @return \DOMDocument|null
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * Function Array to xml
     *
     * @param array $contentq
     * @return $this
     * @throws \DOMException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function arrayToXml($contentq, $val = [])
    {
        $parentNode = $this->_getCurrentDom();
        if (!$contentq || !count($contentq)) {
            return $this;
        }
        foreach ($contentq as $_key => $_item) {
            $node1 = $this->getDom()->createElement(preg_replace('/[^\w-]/i', '', $_key));
            $parentNode->appendChild($node1);
            if (is_array($_item) && isset($_item['_attribute'])) {
                if (is_array($_item['_value'])) {
                    if (isset($_item['_value'][0])) {
                        foreach ($_item['_value'] as $_v) {
                            $this->_setCurrentDom($node1)->arrayToXml($_v);
                        }
                    } else {
                        $this->_setCurrentDom($node1)->arrayToXml($_item['_value']);
                    }
                } else {
                    $child = $this->getDom()->createTextNode($_item['_value']);
                    $node1->appendChild($child);
                }
                foreach ($_item['_attribute'] as $_attributeKey => $_attributeValue) {
                    $node1->setAttribute($_attributeKey, $_attributeValue);
                }
            } elseif (is_string($_item)) {
                $text = $this->getDom()->createTextNode($_item);
                $node1->appendChild($text);
            } elseif (is_array($_item) && !isset($_item[0])) {
                $this->_setCurrentDom($node1)->arrayToXml($_item);
            } elseif (is_array($_item) && isset($_item[0])) {
                foreach ($_item as $v) {
                    $this->_setCurrentDom($node1)->arrayToXml([$this->_getIndexedArrayItemName() => $v]);
                }
            }
        }
        return $this;
    }
    
    /**
     * @param \DOMDocument $nodes
     * @return $this | arr
     */
    protected function _setCurrentDom($nodes, $val = null)
    {
        $this->_currentDom = $nodes; // set value
        return $this;
        // return val
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getDom()->saveXML();
    }

    /**
     * @param string $file
     * @return $this
     */
    public function save($file)
    {
        $this->getDom()->save($file);
        return $this;
    }

    /**
     * Set xml node name to use instead of numeric index during numeric arrays conversion.
     *
     * @param string $name
     * @return $this
     */
    public function setIndexedArrayItemName($name)
    {
        $this->_defaultIndexedArrayItemName = $name;
        return $this;
    }

    /**
     * Get xml node name to use instead of numeric index during numeric arrays conversion.
     *
     * @return string
     */
    protected function _getIndexedArrayItemName()
    {
        return isset($this->_defaultIndexedArrayItemName)
            ? $this->_defaultIndexedArrayItemName
            : self::DEFAULT_ENTITY_ITEM_NAME;
    }
}

