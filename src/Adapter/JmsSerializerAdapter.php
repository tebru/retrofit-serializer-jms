<?php
/**
 * File JmsSerializerAdapter.php
 */

namespace Tebru\RetrofitSerializer\Adapter;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Tebru\Retrofit\Adapter\DeserializerAdapter;
use Tebru\Retrofit\Adapter\SerializerAdapter;

/**
 * Class JmsSerializerAdapter
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JmsSerializerAdapter implements SerializerAdapter, DeserializerAdapter
{
    /**
     * JMS Serializer
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Format that data should be serialized to
     *
     * @var string
     */
    private $serializeTo = 'json';

    /**
     * Format that data is being serialized from
     *
     * @var string
     */
    private $deserializeFrom = 'json';

    /**
     * Serialization context
     *
     * @var array
     */
    private $defaultSerializationContext = [];

    /**
     * Deserialization context
     *
     * @var array
     */
    private $defaultDeserializationContext = [];

    /**
     * Constructor
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serialize an object to a string
     *
     * @param mixed $data The data that should be serialized
     * @param array $context Serializer specific context
     * @return string
     */
    public function serialize($data, array $context = [])
    {
        $context = array_merge($this->defaultSerializationContext, $context);

        return $this->serializer->serialize($data, $this->serializeTo, $this->createContext($context));
    }

    /**
     * Serialize an object to an array
     *
     * @param mixed $data The data that should be serialized
     * @param array $context Serializer specific context
     * @return array
     */
    public function toArray($data, array $context = [])
    {
        $context = array_merge($this->defaultSerializationContext, $context);

        return $this->serializer->toArray($data, $this->createContext($context));
    }

    /**
     * Set the format that data should be serialized to
     *
     * @param string $serializeTo
     */
    public function setSerializeTo($serializeTo)
    {
        $this->serializeTo = $serializeTo;
    }

    /**
     * Set the default serialization context
     *
     * @param array $context
     */
    public function setDefaultSerializationContext(array $context)
    {
        $this->defaultSerializationContext = $context;
    }

    /**
     * Deserialize a formatted string to a specific type
     *
     * @param string $data Data represented as a string
     * @param string $deserializeTo What data should be deserialized to
     * @param array $context Deserializer specific context
     * @return mixed
     */
    public function deserialize($data, $deserializeTo, array $context = [])
    {
        $context = array_merge($this->defaultDeserializationContext, $context);

        return $this->serializer->deserialize($data, $deserializeTo, $this->deserializeFrom, $this->createContext($context, false));
    }

    /**
     * Deserialize an array to a specific type
     *
     * @param array $data Data represented as an array
     * @param string $deserializeTo What data should be deserialized to
     * @param array $context Deserializer specific context
     * @return mixed
     */
    public function fromArray(array $data, $deserializeTo, array $context = [])
    {
        $context = array_merge($this->defaultDeserializationContext, $context);

        return $this->serializer->fromArray($data, $deserializeTo, $this->createContext($context, false));
    }

    /**
     * The current format data is serialized as
     *
     * @param string $deserializeFrom
     */
    public function setDeserializeFrom($deserializeFrom)
    {
        $this->deserializeFrom = $deserializeFrom;
    }

    /**
     * Set the default serialization context
     *
     * @param array $context
     */
    public function setDefaultDeserializationContext(array $context)
    {
        $this->defaultDeserializationContext = $context;
    }

    /**
     * Create serialization/deserialization context
     *
     * @param array $context
     * @param bool $serialize
     * @return DeserializationContext|SerializationContext
     */
    private function createContext(array $context, $serialize = true)
    {
        $jmsContext = true === $serialize ? SerializationContext::create() : DeserializationContext::create();

        if (!empty($context['groups'])) {
            $jmsContext->setGroups($context['groups']);
            unset($context['groups']);
        }

        if (!empty($context['version'])) {
            $jmsContext->setVersion((int) $context['version']);
            unset($context['version']);
        }

        if (!empty($context['serializeNull'])) {
            $jmsContext->setSerializeNull((bool) $context['serializeNull']);
            unset($context['serializeNull']);
        }

        if (!empty($context['enableMaxDepthChecks'])) {
            $jmsContext->enableMaxDepthChecks();
            unset($context['enableMaxDepthChecks']);
        }

        if ($jmsContext instanceof DeserializationContext && !empty($context['depth'])) {
            $contextDepth = (int) $context['depth'];
            while ($jmsContext->getDepth() < $contextDepth) {
                $jmsContext->increaseDepth();
            }

            while ($jmsContext->getDepth() > $contextDepth) {
                $jmsContext->decreaseDepth();
            }

            unset($context['depth']);
        }

        foreach ($context as $key => $value) {
            $jmsContext->setAttribute($key, $value);
        }

        return $jmsContext;
    }
}
