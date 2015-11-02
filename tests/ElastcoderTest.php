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

        if ($elastcoder->objectExists(getenv('KEY'), getenv('BUCKET'))) {
            $elastcoder->deleteObject(getenv('KEY'), getenv('BUCKET'));
        }
        if ($elastcoder->objectExists(getenv('DESTINATION_KEY'), getenv('DESTINATION_BUCKET'))) {
            $elastcoder->deleteObject(getenv('DESTINATION_KEY'), getenv('DESTINATION_BUCKET'));
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
        $job = $elastcoder->transcodeVideo(getenv('KEY'), getenv('DESTINATION_KEY'),  $config);
        $this->_checkJobStructure($job);
        $count = 0;
        do {
            sleep(5);
            $job = $elastcoder->getJob($job['Id']);
            $count++;
        } while($job['Status'] == 'Submitted' || $count < 5);
        $jobOK = FALSE;
        if (strtolower($job['Status']) == 'complete') {
            $elastcoder->setPublicObject(getenv('DESTINATION_KEY'), getenv('DESTINATION_BUCKET'));
            $jobOK = TRUE;
        }
        $this->assertTrue($jobOK, 'Transcoding job completed');
        $object = $elastcoder->getObject(getenv('DESTINATION_KEY'), getenv('DESTINATION_BUCKET'));
        var_dump($object['@metadata']['effectiveUri']);
	}

    private function _checkJobStructure($job) {
        $this->assertTrue(isset($job['Id']));
        $this->assertTrue(is_array($job['Output']));
        $this->assertTrue(is_array($job['Outputs']));
        $this->assertTrue(is_array($job['Input']));
    }
}
