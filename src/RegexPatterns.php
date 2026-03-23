<?php

namespace williamsdb\php2micropost;

class RegexPatterns
{
    const URL_REGEX = '/(https?:\/\/[^\s,)\.]+(?:\.[^\s,)\.]+)*)(?<![\.,:;!?])/i';
}
