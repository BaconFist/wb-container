<?php

namespace Moment;

/**
 * Interface FormatsInterface
 * @package Moment
 */
interface FormatsInterface
{
    /**
     * @param string $format
     *
     * @return FormatsInterface
     */
    public function format($format): string;

    /**
     * @param array $customFormats
     *
     * @return FormatsInterface
     */
    public function setTokens(array $customFormats);
}