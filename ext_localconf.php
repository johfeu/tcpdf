<?php

defined('TYPO3') or die();

\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('pre');
\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('protected');
\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('public');

