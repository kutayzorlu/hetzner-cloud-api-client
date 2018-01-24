<?php
/**
 * @author Timo Förster <tfoerster@webfoersterei.de>
 * @date 23.01.18
 */

namespace Webfoersterei\HetznerCloudApiClient;


use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\SerializerInterface;
use Webfoersterei\HetznerCloudApiClient\Exception\ApiException;
use Webfoersterei\HetznerCloudApiClient\Exception\ErrorResponseException;
use Webfoersterei\HetznerCloudApiClient\Model\Action\GetAllResponse;
use Webfoersterei\HetznerCloudApiClient\Model\Action\GetResponse;
use Webfoersterei\HetznerCloudApiClient\Model\ErrorResponse;

class Client implements ClientInterface
{
    use LoggerAwareTrait;

    public const FORMAT = 'json';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    public function __construct(SerializerInterface $serializer, \GuzzleHttp\ClientInterface $httpClient)
    {
        $this->serializer = $serializer;
        $this->httpClient = $httpClient;

        $this->logger = new NullLogger();
    }

    /**
     * @inheritdoc
     */
    public function actionGetAll(): GetAllResponse
    {
        $this->logger->debug('Sending API-Request to get all actions');

        $request = new Request('GET','actions');
        $httpResponse = $this->processRequest($request);

        $this->logger->debug('Response for all actions request', ['body' => $httpResponse->getBody()]);

        /** @var GetAllResponse $getAllResponse */
        $getAllResponse = $this->serializer->deserialize($httpResponse->getBody(), GetAllResponse::class, static::FORMAT);

        return $getAllResponse;
    }

    /**
     * @inheritdoc
     */
    public function actionGet($id): GetResponse
    {
        $this->logger->debug('Sending API-Request to get a single action', ['action_id' => $id]);

        $request = new Request('GET', sprintf('actions/%d', $id));
        $httpResponse = $this->processRequest($request);

        $this->logger->debug('Response for single action request', ['body' => $httpResponse->getBody()]);

        /** @var GetResponse $getResponse */
        $getResponse = $this->serializer->deserialize($httpResponse->getBody(), GetResponse::class, static::FORMAT);

        return $getResponse;
    }

    /**
     * @param ResponseInterface $response
     * @return ApiException
     */
    private function createExceptionByResponse(ResponseInterface $response): ApiException
    {
        /** @var ErrorResponse $errorResponse */
        $errorResponse = $this->serializer->deserialize($response->getBody(), ErrorResponse::class, static::FORMAT);
        $errorObject = $errorResponse->error;
        $exception = new ErrorResponseException($errorObject->message);
        $exception->setError($errorObject);

        return $exception;
    }

    /**
     * @param RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Webfoersterei\HetznerCloudApiClient\Exception\ApiException
     * @throws GuzzleException
     */
    private function processRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->send($request);
        } catch (ClientException $clientException) {
            $response = $clientException->getResponse();
            if ($response !== null) {
                $exception = $this->createExceptionByResponse($response);
            } else {
                $exception = new ApiException($clientException->getMessage(), $clientException->getCode(), $clientException);
            }

            $exception->setRequest($clientException->getRequest())
                ->setResponse($clientException->getResponse());
            throw $exception;
        }

    }
}