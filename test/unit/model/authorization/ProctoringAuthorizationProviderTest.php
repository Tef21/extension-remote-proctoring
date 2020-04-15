<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\remoteProctoring\test\unit\model\authorization;

use oat\generis\test\TestCase;
use oat\remoteProctoring\model\authorization\ProctoringAuthorizationProvider;
use oat\oatbox\user\User;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\oatbox\session\SessionService;
use Prophecy\Argument;
use oat\remoteProctoring\model\ProctorioApiService;
use oat\remoteProctoring\model\response\ProctorioResponse;
use oat\taoDelivery\model\authorization\UnAuthorizedException;
use oat\remoteProctoring\model\authorization\RemoteProcotoredSessionContext;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProctoringAuthorizationProviderTest extends TestCase
{
    public function testStart(): void
    {
        $user = $this->prophesize(User::class)->reveal();
        $authorization = new ProctoringAuthorizationProvider();
        $authorization->verifyStartAuthorization('fakeId', $user);
        $this->assertTrue(true, 'No exception should be thrown and end of function reached');
    }

    public function testUnauthorized(): void
    {
        $this->expectException(UnAuthorizedException::class);
        $user = $this->prophesize(User::class);
        $deliveryExecution = $this->prophesize(DeliveryExecutionInterface::class);
        $authorization = new ProctoringAuthorizationProvider();
        $authorization->setServiceLocator($this->getMocks());
        $authorization->verifyResumeAuthorization($deliveryExecution->reveal(), $user->reveal());
    }

    public function testAuthorized(): void
    {
        $user = $this->prophesize(User::class);
        $deliveryExecution = $this->prophesize(DeliveryExecutionInterface::class);
        $deliveryExecution->getIdentifier()->willReturn('123456');
        $context = $this->prophesize(RemoteProcotoredSessionContext::class);
        $context->getDeliveryExecutionId()->willReturn('123456');
        $authorization = new ProctoringAuthorizationProvider();
        $authorization->setServiceLocator($this->getMocks([$context->reveal()]));
        $authorization->verifyResumeAuthorization($deliveryExecution->reveal(), $user->reveal());
        $this->assertTrue(true, 'No exception should be thrown and end of function reached');
    }

    protected function getMocks(array $sessionContexts = [], $url = 'http://fakeUrl'): ServiceLocatorInterface
    {
        return $this->getServiceLocatorMock([
            SessionService::SERVICE_ID => $this->getSessionMock($sessionContexts),
            ProctorioApiService::SERVICE_ID => $this->getProctorioApiMock($url)
        ]);
    }

    protected function getSessionMock(array $sessionContexts): SessionService
    {
        $session = $this->prophesize(\common_session_Session::class);
        $session->getContexts(Argument::any())->willReturn($sessionContexts);
        $sessionService = $this->prophesize(SessionService::class);
        $sessionService->getCurrentSession()->willReturn($session->reveal());
        return $sessionService->reveal();
    }

    protected function getProctorioApiMock($url): ProctorioApiService
    {
        $response = $this->prophesize(ProctorioResponse::class);
        $response->getTestTakerUrl()->willReturn($url);
        $proctorioApiService = $this->prophesize(ProctorioApiService::class);
        $proctorioApiService->getProctorioUrl(Argument::any())->willReturn($response->reveal());
        return $proctorioApiService->reveal();
    }
}
