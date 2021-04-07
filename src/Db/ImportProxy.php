<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ImportProxy
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
}
