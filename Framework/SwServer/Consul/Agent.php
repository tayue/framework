<?php declare(strict_types=1);


namespace Framework\SwServer\Consul;

use Framework\SwServer\Consul\Contract\AgentInterface;
use Framework\SwServer\Consul\Exception\ClientException;
use Framework\SwServer\Consul\Exception\ServerException;
use Framework\SwServer\Consul\Helper\OptionsResolver;
use Framework\Traits\SingletonTrait;

/**
 * Class Agent
 *
 */
class Agent implements AgentInterface
{
    use SingletonTrait;
    public $config;
    private $consul;

    private function __construct($config)
    {
        if ($config['host'] && $config['port'] && $config['timeout']) {
            $this->consul = new Consul($config['host'], $config['port'], $config['timeout']);
        } else {
            $this->consul = new Consul();
        }
        $this->config = $config;
    }

    /**
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function checks(): Response
    {
        return $this->consul->get('/v1/agent/checks');
    }

    /**
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function services(): Response
    {
        return $this->consul->get('/v1/agent/services');
    }

    /**
     * @param array $options
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function members(array $options = []): Response
    {
        $params = [
            'query' => self::resolve($options, ['wan']),
        ];

        return $this->consul->get('/v1/agent/members', $params);
    }

    /**
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function self(): Response
    {
        return $this->consul->get('/v1/agent/self');
    }

    /**
     * @param string $address
     * @param array $options
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function join(string $address, array $options = []): Response
    {
        $params = array(
            'query' => self::resolve($options, ['wan']),
        );

        return $this->consul->get('/v1/agent/join/' . $address, $params);
    }

    /**
     * @param string $node
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function forceLeave(string $node): Response
    {
        return $this->consul->get('/v1/agent/force-leave/' . $node);
    }

    /**
     * @param array $check
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function registerCheck(array $check): Response
    {
        $params = [
            'body' => $check,
        ];

        return $this->consul->put('/v1/agent/check/register', $params);
    }

    /**
     * @param string $checkId
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function deregisterCheck(string $checkId): Response
    {
        return $this->consul->put('/v1/agent/check/deregister/' . $checkId);
    }

    /**
     * @param string $checkId
     * @param array $options
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function passCheck(string $checkId, array $options = []): Response
    {
        $params = [
            'query' => self::resolve($options, ['note']),
        ];

        return $this->consul->put('/v1/agent/check/pass/' . $checkId, $params);
    }

    /**
     * @param string $checkId
     * @param array $options
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function warnCheck(string $checkId, array $options = []): Response
    {
        $params = [
            'query' => self::resolve($options, ['note']),
        ];

        return $this->consul->put('/v1/agent/check/warn/' . $checkId, $params);
    }

    /**
     * @param string $checkId
     * @param array $options
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function failCheck(string $checkId, array $options = []): Response
    {
        $params = [
            'query' => self::resolve($options, ['note']),
        ];

        return $this->consul->put('/v1/agent/check/fail/' . $checkId, $params);
    }

    /**
     * @param array $service
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function registerService(array $service): Response
    {
        $params = [
            'body' => $service,
        ];

        return $this->consul->put('/v1/agent/service/register', $params);
    }

    /**
     * @param string $serviceId
     *
     * @return Response
     * @throws ClientException
     * @throws ServerException
     */
    public function deregisterService(string $serviceId): Response
    {
        return $this->consul->put('/v1/agent/service/deregister/' . $serviceId);
    }

    public static function resolve(array $options, array $availableOptions): array
    {
        return array_intersect_key($options, array_flip($availableOptions));
    }
}