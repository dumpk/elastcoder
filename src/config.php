<?php

return [
    'encodings' => [
        'example' => [
            'type' => 1,
            'PresetId' => '',
            'width' => 1920,
            'height' => 1080,
            'aspect' => '16:9',
            'ext' => 'mp4',
            'PipelineId' => '1434059450461-nfuqq4',
            'Watermarks' => [[
                    'PresetWatermarkId' => 'BottomRight',
                    'InputKey' => '1080.png',
            ]],
        ],
    ],

    /*
  |--------------------------------------------------------------------------
  | Configuration for audio transcoding
  |--------------------------------------------------------------------------
  */
    'audio' =>[
        'PipelineId' => '',
        'PresetId' => '',
        'StartTime'=> '', //leave empty if you don't intend clipping |HH:mm:ss.SSS
        'Duration'=> '',//leave empty if you don't intend clipping | HH:mm:ss.SSS
        'container'=> 'mp3',
        'OutputKeyPrefix' => '',
        'AlbumArtMerge' =>'Fallback', //Replace|Prepend|Append|Fallback
        'AlbumArtMaxWidth' =>'',
        'AlbumArtMaxHeight' => '',
        'AlbumArtSizingPolicy' => '', //AlbumArtSizingPolicy":"Fit|Fill|Stretch|Keep|ShrinkToFit|ShrinkToFill
        'AlbumArtFormat' => '',//jpg|png

    ]

];
