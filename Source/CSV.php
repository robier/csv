<?php
namespace Robier;

/**
 * Class CSV
 * @package Robier
 */
class CSV
{

    protected $filePath = null;
    protected $fileHandle = null;
    protected $headers = null;
    protected $headerKeys = null;

    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $escape = '\\';
    protected $encoding = 'utf-8';

    protected $separatorIdentifier = false;
    protected $replace = [];
    protected $lock = false;

    /**
     * Setting file path
     *
     * @param null $filePath
     */
    public function __construct($filePath = null)
    {
        $this->filePath = $filePath;
    }

    /**
     * Setting file path
     *
     * @param string $filePath
     * @return $this
     */
    public function setPath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Setter for csv delimiter, only one character
     *
     * @param $delimiter
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDelimiter($delimiter = ',')
    {
        if(strlen($delimiter) != 1){
            throw new \InvalidArgumentException('Method CSV::setDelimiter() can only receive string with 1 character in length');
        }
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Setter for csv enclosure, only one character
     *
     * @param $enclosure
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setEnclosure($enclosure = '"')
    {
        if(strlen($enclosure) != 1){
            throw new \InvalidArgumentException('Method CSV::setEnclosure() can only receive string with 1 character in length');
        }
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * Setter escape character for csv data
     *
     * @param string $escape
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setEscape($escape = '\\')
    {
        if(strlen($escape) != 1){
            throw new \InvalidArgumentException('Method CSV::setEscape() can only receive string with 1 character in length');
        }
        $this->escape = $escape;
        return $this;
    }

    /**
     * Setting first row in csv and saving header keys for associative rows
     *
     * @param array $headers
     * @return $this
     */
    public function setHeader(array $headers)
    {
        $this->headers = $headers;
        $this->setRowKeys(array_keys($headers));
        return $this;
    }

    /**
     * Set csv encoding
     *
     * @param $encoding
     * @return $this
     */
    public function setEncoding($encoding = 'utf-8')
    {
        $this->encoding = (string)$encoding;
        return $this;
    }

    /**
     * Setting row keys for csv, so we don't need to be careful when giving row data
     *
     * @param array $keys
     * @return $this
     */
    public function setRowKeys(array $keys)
    {
        $this->headerKeys = array_fill_keys($keys, '');
        return $this;
    }

    /**
     * If headerKeys are set it will make intersection of header keys with row keys so they needs
     * to match, otherwise keys that do not exist in header will not be in csv file.
     * If headerKeys are not set it will write all data provided.
     *
     * @see setRowKeys
     * @see setHeader
     *
     * @param array $row
     * @return $this
     * @throws \LogicException
     */
    public function write(array $row)
    {
        if ($this->lock) {
            throw new \LogicException('Can not write to file after outputting content!');
        }

        if (!is_resource($this->fileHandle)) {
            $this->openFile();
            if ($this->separatorIdentifier && $this->delimiter != ',') {
                fwrite($this->fileHandle, 'sep=' . $this->delimiter . PHP_EOL);
            }
            if (is_array($this->headers) && !empty($this->headers)) {
                fputcsv($this->fileHandle, $this->prepareRow($this->headers), $this->delimiter, $this->enclosure, $this->escape);
            }
        }
        if (is_array($this->headerKeys) && !empty($this->headerKeys)) {
            $row = array_intersect_key(array_merge($this->headerKeys, $row), $this->headerKeys);
        }

        fputcsv($this->fileHandle, $this->prepareRow($row), $this->delimiter, $this->enclosure, $this->escape);

        return $this;
    }

    /**
     * Opens file for writing
     *
     * @throws \LogicException
     */
    protected function openFile()
    {
        if (empty($this->filePath) && !is_dir(dirname($this->filePath))) {
            throw new \LogicException('File path not set!');
        }
        $this->fileHandle = fopen($this->filePath, 'w+');
    }

    /**
     * Switch for setting first row as separator identifier
     *
     * @param bool $bool
     * @return $this
     */
    public function setSeparatorIdentifier($bool = false)
    {
        $this->separatorIdentifier = $bool;
        return $this;
    }

    /**
     * Preparing row ie. encoding it to windows-1252 encoding
     *
     * @param array $row
     * @return array
     */
    protected function prepareRow(array $row)
    {
        $newRow = [];
        foreach ($row as $key => $rowItem) {
            if (!empty($this->replace)) {
                $newRow[$key] = mb_convert_encoding(str_replace(array_keys($this->replace), $this->replace, $rowItem), $this->encoding);
            } else {
                $newRow[$key] = mb_convert_encoding($rowItem, $this->encoding);
            }
        }
        return $newRow;
    }

    /**
     * Replaces $search with $replace in output csv file
     *
     * @param string $search
     * @param string $replace
     * @return $this
     */
    public function replace($search, $replace)
    {
        if (!isset($this->replace[$search])) {
            $this->replace[$search] = $replace;
        } else {
            throw new \InvalidArgumentException('Filter with key ' . $search . ' already exists');
        }
        return $this;
    }

    /**
     * Checking if file is empty
     *
     * @return bool
     */
    public function isFileEmpty()
    {
        if (!file_exists($this->filePath) || filesize($this->filePath) == 0) {
            return true;
        }
        return false;
    }

    /**
     * Get csv file path
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->filePath;
    }

    /**
     * Get file handle
     *
     * @return resource|null
     */
    public function getHandle()
    {
        return $this->fileHandle;
    }

    /**
     * Outputting headers for csv, close csv file and locks this object for writing any more
     */
    public function output()
    {
        $this->lock = true;
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        } else {
            $this->openFile();
            fclose($this->fileHandle);
        }

        header('Content-Encoding: ' . $this->encoding);
        header('Content-type: text/csv, ' . $this->encoding);
        header('Content-Disposition: attachment; filename="' . basename($this->filePath) . '"');
        header('Pragma: no-cache');

        readfile($this->filePath);
    }

    /**
     * Closes csv file
     */
    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }

    /**
     * Setts needed settings so there is no broken data in microsoft excel
     *
     * @return $this
     */
    public function setMicrosoftExcelSettings()
    {
        $this->setSeparatorIdentifier(true);
        $this->setEncoding('windows-1252');
        return $this;
    }
}