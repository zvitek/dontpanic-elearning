<?php

namespace zvitek\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * Class DateHandlerCustom
 *
 * @package kytart\Serializer
 */
class DateHandlerCustom implements SubscribingHandlerInterface
{

    private $defaultFormat;
    private $defaultTimezone;
    private $xmlCData;

    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC', $xmlCData = true)
    {
        $this->defaultFormat   = $defaultFormat;
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);
        $this->xmlCData        = $xmlCData;
    }

    public static function getSubscribingMethods()
    {
        $methods = [ ];
        $types   = [ 'DateTime', 'DateInterval' ];

        foreach ([ 'json', 'xml', 'yml' ] as $format) {
            $methods[] = [
                'type'      => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => $format,
            ];

            foreach ($types as $type) {
                $methods[] = [
                    'type'      => $type,
                    'format'    => $format,
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'method'    => 'serialize' . $type,
                ];
            }
        }

        return $methods;
    }

    public function serializeDateTime(VisitorInterface $visitor, \DateTime $date, array $type, Context $context)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        if ($visitor instanceof XmlSerializationVisitor && false === $this->xmlCData) {
            return $visitor->visitSimpleString($date->format($this->getFormat($type)), $type, $context);
        }

        return $visitor->visitString($date->format($this->getFormat($type)), $type, $context);
    }

    /**
     * @return string
     *
     * @param array $type
     */
    private function getFormat(array $type)
    {
        return isset($type['params'][0]) ? $type['params'][0] : $this->defaultFormat;
    }

    public function serializeDateInterval(VisitorInterface $visitor, \DateInterval $date, array $type, Context $context)
    {
        $iso8601DateIntervalString = $this->format($date);

        if ($visitor instanceof XmlSerializationVisitor && false === $this->xmlCData) {
            return $visitor->visitSimpleString($iso8601DateIntervalString, $type, $context);
        }

        return $visitor->visitString($iso8601DateIntervalString, $type, $context);
    }

    /**
     * @param \DateInterval $dateInterval
     *
     * @return string
     */
    public function format(\DateInterval $dateInterval)
    {
        $format = 'P';

        if (0 < $dateInterval->y) {
            $format .= $dateInterval->y . 'Y';
        }

        if (0 < $dateInterval->m) {
            $format .= $dateInterval->m . 'M';
        }

        if (0 < $dateInterval->d) {
            $format .= $dateInterval->d . 'D';
        }

        if (0 < $dateInterval->h || 0 < $dateInterval->i || 0 < $dateInterval->s) {
            $format .= 'T';
        }

        if (0 < $dateInterval->h) {
            $format .= $dateInterval->h . 'H';
        }

        if (0 < $dateInterval->i) {
            $format .= $dateInterval->i . 'M';
        }

        if (0 < $dateInterval->s) {
            $format .= $dateInterval->s . 'S';
        }

        return $format;
    }

    public function deserializeDateTimeFromXml(XmlDeserializationVisitor $visitor, $data, array $type)
    {
        $attributes = $data->attributes('xsi', true);
        if (isset($attributes['nil'][0]) && (string) $attributes['nil'][0] === 'true') {
            return null;
        }

        return $this->parseDateTime($data, $type);
    }

    private function parseDateTime($data, array $type)
    {
        $timezone = isset($type['params'][1]) ? new \DateTimeZone($type['params'][1]) : $this->defaultTimezone;
        $format   = $this->getFormat($type);
        //hack
        $data     = preg_replace('/\.\d{3}Z/', '.000Z', (string) $data);
        $datetime = \DateTime::createFromFormat($format, (string) $data, $timezone);
        if (false === $datetime) {
            throw new RuntimeException(sprintf('Invalid datetime "%s", expected format %s.', $data, $format));
        }

        return $datetime;
    }

    public function deserializeDateTimeFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        if (null === $data) {
            return null;
        }

        $dt = $this->parseDateTime($data, $type)->setTimezone(new \DateTimeZone('Europe/Prague'));

        return $dt;
    }
}