<?php

use Google\ApiCore\Testing\MessageAwareArrayComparator;
use Google\ApiCore\Testing\ProtobufMessageComparator;
use Google\ApiCore\Testing\ProtobufGPBEmptyComparator;

date_default_timezone_set('UTC');
\SebastianBergmann\Comparator\Factory::getInstance()->register(new MessageAwareArrayComparator());
\SebastianBergmann\Comparator\Factory::getInstance()->register(new ProtobufMessageComparator());
\SebastianBergmann\Comparator\Factory::getInstance()->register(new ProtobufGPBEmptyComparator());
