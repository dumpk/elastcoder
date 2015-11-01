<?php

namespace Dumpk\Elastcoder;

use Aws\S3\S3Client;
use Aws\ElasticTranscoder\ElasticTranscoderClient;

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

    private function _s3c()
    {
        if (!isset($this->s3c)) {
            $this->s3c = new S3Client($this->_getConfig());
        }

        return $this->s3c;
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

    public function getJob($job_id)
    {
        $result = $this->etc->readJob(array('Id' => $job_id));
        if (isset($result['Job'])) {
            return $result['Job'];
        } else {
            return;
        }
    }

    public function getObject($key, $bucket)
    {
        $s3c = $this->_s3c();
        $object = null;
        if ($s3c->doesObjectExist($bucket, $key)) {
            $object = $s3c->getObject(array('Bucket' => $bucket, 'Key' => $key));
        }

        return $object;
    }

    public function objectExists($key, $bucket)
    {
        $s3c = $this->_s3c();
        if ($s3c->doesObjectExist($bucket, $key)) {
            return true;
        } else {
            return false;
        }
    }

    public function setPublicObject($key, $bucket)
    {
        return $this->setObjectACL($key, $bucket);
    }

    /**
     * setObjectACL
     * Change the access level of an object on S3.
     *
     * @param $key
     * @param $bucket
     * @param $acl (private|public-read|public-read-write|authenticated-read|bucket-owner-read|bucket-owner-full-control)
     */
    public function setObjectACL($key, $bucket, $acl = 'public-read')
    {
        $s3c = $this->_s3c();
        if ($s3c->doesObjectExist($bucket, $key)) {
            $result = $s3c->putObjectAcl(array(
                'ACL' => $acl,
                'Bucket' => $bucket,
                'Key' => $key,
            ));
            if ($result) {
                return true;
            }
        }

        return false;
    }
}
