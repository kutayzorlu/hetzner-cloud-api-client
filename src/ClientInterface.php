<?php
/**
 * @author Timo Förster <tfoerster@webfoersterei.de>
 * @date 23.01.18
 */

namespace Webfoersterei\HetznerCloudApiClient;


use Webfoersterei\HetznerCloudApiClient\Model\Action\GetAllResponse as GetAllActionsResponse;
use Webfoersterei\HetznerCloudApiClient\Model\Action\GetResponse as GetActionResponse;
use Webfoersterei\HetznerCloudApiClient\Model\Server\CreateRequest;
use Webfoersterei\HetznerCloudApiClient\Model\Server\CreateResponse;
use Webfoersterei\HetznerCloudApiClient\Model\Server\GetAllResponse as GetAllServersResponse;
use Webfoersterei\HetznerCloudApiClient\Model\Server\GetResponse as GetServerResponse;

interface ClientInterface
{

    /**
     * @return GetAllActionsResponse
     */
    public function getActions(): GetAllActionsResponse;

    /**
     * @param int $id
     * @return GetActionResponse
     */
    public function getAction(int $id): GetActionResponse;

    /**
     * @return GetAllServersResponse
     */
    public function getServers(): GetAllServersResponse;

    /**
     * @param int $id
     * @return GetServerResponse
     */
    public function getServer(int $id): GetServerResponse;

    /**
     * @param CreateRequest $createRequest
     * @return CreateResponse
     */
    public function createServer(CreateRequest $createRequest): CreateResponse;

}