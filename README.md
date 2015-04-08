# csv

Simple CSV generation class that is basically wrapper for fputcsv function that supports: 

- change of data encoding while making CSV file
- replacing parts of data with str_replace
- easily to change data delimiter character
- easily to change data enclosure character
- easily setting up header row so csv will be consistent no matter of data order
- support Microsoft Excel (tested on Mac and on Windows)
- supports csv output in browser


### Code sample:

```php
<?php
    $csv = new Robier/CSV();
    $csv->setPath('/tmp/statistics.csv')
        ->setDelimiter(',')
        ->setEnclosure("'")
        ->setHeader(
            [
                'column_1', 'column_2', 'column_3'
            ])
        ->setSeparatorIdentifier(false)
        ->replace('http://', '');
        
    foreach($dataFromDatabase as $row){
        $row['column_1'] = date('d.m.Y. H:i', $row['column_1']);
        $row['column_2'] = strtolower($row['column_2']);
        
        $csv->write($row);
    }
    
    $pathToCsvFile = $csv->getPath();
    
    // or output(download) in the browser
    // $csv->output();
```

### Methods:

`CSV::setPath(string $path)`
- setter for csv file path

`CSV::setDelimiter(string $delimiter = ',')`
- setter for csv data delimiter

`CSV::setEnclosure(string $enclosure = '"')`
- setter for csv data enclosure

`CSV::setHeader(array $headers)`
- setter for csv header keys and labels

`CSV::setEncoding(string $encoding = 'utf-8')`
- setter for csv encoding

`CSV::setRowKeys(array $keys)`
- setter for header keys

`CSV::write(array $row)`
- writes provided line, if we defined header keys then only keys that match header keys
will be written in csv output

`CSV::setSeparatorIdentifier(bool $bool = false)`
- switch for setting `sep=;` fix for Microsoft Excel if we using some unconventional data separator, 
it's not needed if generated csv is not intended to be used in Microsoft Excel

`CSV::replace(string $search, string $replace)`
- setter for replacing data when writing csv

`CSV::isFileEmpty()`
- checks if written file is empty

`CSV::getPath()`
- getter for file path

`CSV::getHandle()`
- getter for csv resource handle

`CSV::output()`
- outputs csv in browser, starts downloading

`CSV::setMicrosoftExcelSettings()`
- setts encoding and `sep=;` fix for Microsoft Excel
