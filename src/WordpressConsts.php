<?php

namespace williamsdb\php2micropost;

class WordpressConsts
{
    // don't change these unless Wordpress changes the limits
    const MAX_IMAGE_UPLOAD_SIZE = 1000000;
    const MAX_IMAGE_UPLOAD = 1;
    const FILE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/jpg',
    ];
}
