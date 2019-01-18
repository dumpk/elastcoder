<?php

namespace Dumpk\Elastcoder;

use Aws\S3\S3Client;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Dumpk\Esetres\EsetresAWS;

class ElastcoderAWS
{
    private $etc;

    public function __construct()
    {
        $this->etc = new ElasticTranscoderClient($this->_getConfig());
    }

    private function _getConfig()
    {
        $config = array(
            'version' => 'latest',
            'key' => env('AWS_ACCESS_KEY_ID', ''),
            'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            'region' => env('AWS_REGION', ''),
            'timeout' => 3,
        );

        return $config;
    }

    public function listJobs($PipelineId, $page = null)
    {
        return $this->etc->listJobsByPipeline([
            'Ascending' => 'false',
            'PageToken' => $page,
            'PipelineId' => $PipelineId, // REQUIRED
        ]);
    }

    public function transcodeVideo($inputKey, $destinationKey, $config, $thumbPattern = null)
    {
        $presetId = $config['PresetId'];
        $pipelineId = $config['PipelineId'];
        $watermarks = null;

        $input = array(
            'Key' => $inputKey,
        );

        if (isset($config['TimeSpan'])) {
            $input['TimeSpan'] = $config['TimeSpan'];
        }

        $output = array(
            'Key' => $destinationKey,
            'PresetId' => $presetId,
        );

        if ($thumbPattern) {
            $output['ThumbnailPattern'] = $thumbPattern;
        }
        if (isset($config['Watermarks'])) {
            $output['Watermarks'] = $config['Watermarks'];
        }

        $job_config = array(
            'PipelineId' => $pipelineId,
            'Input' => $input,
            'Output' => $output,
        );

        $result = $this->etc->createJob($job_config);

        return $result['Job'];
    }


    /**
     * Transcode Audio files
     * @param $inputFile
     * @param $outputFile
     * @param $artwork
     * @return mixed
     */
    public function transcodeAudio($inputFile, $outputFile, $artwork = '')
    {


        $input = [
            'Key' => $inputFile,
            //for clipping audio file
            'TimeSpan' => [
                'StartTime' => config('elascoder.audio.StartTime'),
                'Duration' => config('elastcoder.audio.Duration')
            ]
        ];

        $output = [
            'Key' => $outputFile, //name of your transcoded file
            'PresetId' => config('elastcoder.audio.PresetId'),
        ];

        $albumArt = [
            'AlbumArtMerge' => config('elastcoder.audio.AlbumArtMerge'),
            'AlbumArtArtwork' =>[
                'AlbumArtInputKey' => $artwork, //image you want to use as album art
                'AlbumArtMaxWidth' => config('elastcoder.audio.AlbumArtMaxWidth'),
                'AlbumArtMaxHeight' => config('elastcoder.audio.AlbumArtMaxHeight'),
                'AlbumArtSizingPolicy' => config('elastcoder.audio.AlbumArtSizingPolicy')
            ]
        ];

        $job = [
            'PipelineId' => config('elastcoder.audio.PipelineId'),
            'Input' => $input,
            'Output' => $output,
            'AlbumArt' => $albumArt
        ];

        $result = $this->etc->createJob($job);

        return $result['Job'];
    }

    public function getJob($job_id)
    {
        $result = $this->etc->readJob(array('Id' => $job_id));
        if (isset($result['Job'])) {
            return $result['Job'];
        } else {
            return;
        }
    }

    /**
    * @deprecated
    */
    public function uploadFile($localPath, $key, $bucket, $acl = 'public-read', $metadata = array(), $cache = 'max-age=3600', $extraOptions = array()) {

        return EsetresAWS::uploadFile($localPath, $key, $bucket, $acl, $metadata, $cache, $extraOptions);
    }

    /**
    * @deprecated
    */
    public function getObject($key, $bucket)
    {
        return EsetresAWS::getObject($key, $bucket);
    }

    /**
    * @deprecated
    */
    public function deleteObject($key, $bucket)
    {
        return EsetresAWS::deleteObject($key, $bucket);
    }

    /**
    * @deprecated
    */
    public function objectExists($key, $bucket)
    {
        return EsetresAWS::objectExists($key, $bucket);
    }

    /**
    * @deprecated
    */
    public function setPublicObject($key, $bucket)
    {
        return EsetresAWS::setPublicObject($key, $bucket);
    }

    /**
    * @deprecated
    */
    public function setObjectACL($key, $bucket, $acl = 'public-read')
    {
        return EsetresAWS::setObjectACL($key, $bucket, $acl);
    }
}
