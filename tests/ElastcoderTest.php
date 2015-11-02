<?php
use Dumpk\Elastcoder\ElastcoderAWS;

class ElastcoderTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{

	}

	public function testConnection()
	{
        $elastcoder = new ElastcoderAWS();

        /*$list = $elastcoder->listJobs(getenv('PIPELINE_ID'));
        $this->assertTrue(is_array($list['Jobs']));
        foreach($list['Jobs'] as $job) {
            $this->_checkJobStructure($job);
        }*/
        $destination_key = time().'-'.getenv('DESTINATION_KEY');
        // if ($elastcoder->objectExists(getenv('KEY'), getenv('BUCKET'))) {
            // $elastcoder->deleteObject(getenv('KEY'), getenv('BUCKET'));
        // }
        if ($elastcoder->objectExists($destination_key, getenv('DESTINATION_BUCKET'))) {
            $elastcoder->deleteObject($destination_key, getenv('DESTINATION_BUCKET'));
        }
        $uploadResult = $elastcoder->uploadFile(getenv('LOCAL_FILE'), getenv('KEY'), getenv('BUCKET'));

        $this->assertTrue(isset($uploadResult['ObjectURL']), 'Upload file object URL result');
        $objectURL = $uploadResult['ObjectURL'];
        $config = [
			'type'	=> 3,
            'PresetId' => getenv('PRESET_ID'),
            'width'  => 1280,
            'height' => 720,
            'aspect' => '16:9',
			'ext'	 => 'mp4',
			'PipelineId' => getenv('PIPELINE_ID'),
        ];
        $job = $elastcoder->transcodeVideo(getenv('KEY'), $destination_key,  $config);
        $this->_checkJobStructure($job);
        $count = 0;
        do {
            sleep(5);
            $job = $elastcoder->getJob($job['Id']);
            $count++;
        } while($job['Status'] == 'Submitted' || $count < 5);
        $jobOK = FALSE;
        if (strtolower($job['Status']) == 'complete') {
            $elastcoder->setPublicObject($destination_key, getenv('DESTINATION_BUCKET'));
            $jobOK = TRUE;
        }
        $this->assertTrue($jobOK, 'Transcoding job completed');
        $object = $elastcoder->getObject($destination_key, getenv('DESTINATION_BUCKET'));
        echo $object['@metadata']['effectiveUri'];
	}

    private function _checkJobStructure($job) {
        $this->assertTrue(isset($job['Id']));
        $this->assertTrue(is_array($job['Output']));
        $this->assertTrue(is_array($job['Outputs']));
        $this->assertTrue(is_array($job['Input']));
    }
}
