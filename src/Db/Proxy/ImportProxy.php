<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ImportProxy extends CommandProxy
{
    /**
     * Get data for import
     *
     * @param string $database      The database name
     *
     * @return array
     */
    public function getImportOptions(string $database)
    {
        global $adminer;

        $contents = [];
        $message = [];
        // From sql.inc.php
        $gz = \extension_loaded('zlib') ? '[.gz]' : '';
        // ignore post_max_size because it is for all form fields
        // together and bytes computing would be necessary.
        if(\adminer\ini_bool('file_uploads'))
        {
            $contents['upload'] = "SQL$gz (&lt; " . \ini_get('upload_max_filesize') . 'B)';
        }
        else
        {
            $contents['upload_disabled'] = \adminer\lang('File uploads are disabled.');
        }

        $importServerPath = $adminer->importServerPath();
        if(($importServerPath))
        {
            $contents['path'] = \adminer\h($importServerPath) . $gz;
        }

        $labels = [
            'path' => \adminer\lang('Webserver file %s', ''),
            'file_upload' => \adminer\lang('File upload'),
            'from_server' => \adminer\lang('From server'),
            'execute' => \adminer\lang('Execute'),
            'run_file' => \adminer\lang('Run file'),
            'select' => \adminer\lang('Select'),
            'error_stops' => \adminer\lang('Stop on error'),
            'only_errors' => \adminer\lang('Show only errors'),
        ];

        return \compact('contents', 'labels');
    }

    /** Get file contents from $_FILES
     *  From the get_file() function in functions.inc.php
     *
     * @param array $files
     * @param bool $decompress
     *
     * @return mixed int for error, string otherwise
     */
    protected function readFiles(array $files, $decompress = false)
    {
        $return = '';
        foreach($files as $name)
        {
            $compressed = \preg_match('~\.gz$~', $name);
            $content = \file_get_contents($decompress && $compressed
                ? "compress.zlib://$name"
                : $name
            ); //! may not be reachable because of open_basedir
            if($decompress && $compressed)
            {
                $start = \substr($content, 0, 3);
                if(\function_exists("iconv") && \preg_match("~^\xFE\xFF|^\xFF\xFE~", $start, $regs))
                {
                    // not ternary operator to save memory
                    $content = \iconv("utf-16", "utf-8", $content);
                }
                elseif($start == "\xEF\xBB\xBF")
                {
                    // UTF-8 BOM
                    $content = \substr($content, 3);
                }
                $return .= $content . "\n\n";
            }
            else
            {
                $return .= $content;
            }
        }
        //! support SQL files not ending with semicolon
        return $return;
    }

    /**
     * Run queries from uploaded files
     *
     * @param array  $files         The uploaded files
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     *
     * @return array
     */
    public function executeSqlFiles(array $files, bool $errorStops, bool $onlyErrors)
    {
        $queries = $this->readFiles($files);
        return $this->executeCommands($queries, 0, $errorStops, $onlyErrors);
    }
}
